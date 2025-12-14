<?php

namespace App\Services;

use Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class SystemLogService
{
    private string $logDirectory;
    
    private array $levels = [
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency',
    ];

    public function __construct(?string $logDirectory = null)
    {
        $this->logDirectory = $logDirectory ?? storage_path('logs');
    }

    public function getEntries(array $filters = [], int $limit = 100): array
    {
        $limit = max(10, min($limit, 500));
        $maxFiles = max(1, min((int) ($filters['max_files'] ?? 3), 20));

        $files = $this->resolveFiles($filters, $maxFiles);
        $entries = collect();
        $perFileLimit = max($limit * 3, 200);

        foreach ($files as $file) {
            $parsedEntries = $this->parseFile($file['path'], $file['channel'], $perFileLimit);

            foreach ($parsedEntries as $entry) {
                if ($this->entryPassesFilters($entry, $filters)) {
                    $entries->push($entry);
                }
            }
        }

        $entries = $entries
            ->sortByDesc(fn (array $entry) => $entry['timestamp']->timestamp)
            ->take($limit)
            ->values();

        return [
            'entries' => $entries,
            'files' => $files,
            'meta' => [
                'files_scanned' => $files->count(),
                'limit' => $limit,
                'filters_applied' => array_filter($filters, fn ($value) => filled($value)),
            ],
        ];
    }

    public function listFiles(?string $channel = null, ?string $date = null): Collection
    {
        if (!File::isDirectory($this->logDirectory)) {
            return collect();
        }

        return collect(File::files($this->logDirectory))
            ->filter(fn (SplFileInfo $file) => Str::endsWith($file->getFilename(), '.log'))
            ->map(fn (SplFileInfo $file) => $this->mapFileInfo($file))
            ->when($channel, fn (Collection $collection) => $collection->where('channel', $channel))
            ->when($date, fn (Collection $collection) => $collection->filter(
                fn (array $file) => Str::contains($file['name'], $date)
            ))
            ->sortByDesc('updated_at')
            ->values();
    }

    public function availableChannels(): Collection
    {
        return $this->listFiles()->pluck('channel')->unique()->filter()->values();
    }

    public function deleteEntry(string $fileName, string $timestamp): bool
    {
        $filePath = $this->logDirectory . DIRECTORY_SEPARATOR . basename($fileName);
        
        // Security: Ensure the file is within the log directory
        $realPath = realpath($filePath);
        $realLogDir = realpath($this->logDirectory);
        
        if (!$realPath || !$realLogDir || !str_starts_with($realPath, $realLogDir)) {
            return false;
        }
        
        if (!Str::endsWith($fileName, '.log')) {
            return false;
        }
        
        if (!File::exists($filePath) || !is_readable($filePath) || !is_writable($filePath)) {
            return false;
        }
        
        try {
            $targetTimestamp = Carbon::parse($timestamp);
            $targetDateStr = $targetTimestamp->format('Y-m-d H:i:s');
            
            $lines = [];
            $currentEntry = [];
            $inTargetEntry = false;
            $entryFound = false;
            
            foreach ($this->readFileLines($filePath) as $line) {
                $matches = [];
                if ($this->isEntryHeader($line, $matches)) {
                    if ($inTargetEntry) {
                        $inTargetEntry = false;
                    }
                    
                    if ($matches['datetime'] === $targetDateStr) {
                        $inTargetEntry = true;
                        $entryFound = true;
                        continue;
                    }
                    
                    if (!empty($currentEntry)) {
                        $lines = array_merge($lines, $currentEntry);
                    }
                    
                    $currentEntry = [$line];
                } else {
                    if (!$inTargetEntry && !empty($currentEntry)) {
                        $currentEntry[] = $line;
                    }
                }
            }
            
            if (!empty($currentEntry) && !$inTargetEntry) {
                $lines = array_merge($lines, $currentEntry);
            }
            
            if (!$entryFound) {
                return false;
            }
            
            $content = !empty($lines) ? implode(PHP_EOL, $lines) . PHP_EOL : '';
            File::put($filePath, $content);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function availableLevels(): array
    {
        return $this->levels;
    }

    private function resolveFiles(array $filters, int $maxFiles): Collection
    {
        $files = $this->listFiles($filters['channel'] ?? null, $filters['date'] ?? null);

        if (!empty($filters['file'])) {
            $files = $files->where('name', $filters['file'])->values();
        }

        if ($files->isEmpty()) {
            $files = $this->listFiles()->take(1);
        }

        return $files->take($maxFiles)->values();
    }

    private function mapFileInfo(SplFileInfo $file): array
    {
        $name = $file->getFilename();
        $channel = $this->inferChannel($name);
        $updatedAt = Carbon::createFromTimestamp($file->getMTime());

        return [
            'name' => $name,
            'channel' => $channel,
            'path' => $file->getRealPath(),
            'size' => (int) $file->getSize(),
            'size_human' => $this->formatBytes((int) $file->getSize()),
            'updated_at' => $updatedAt,
            'updated_for_humans' => $updatedAt->diffForHumans(),
        ];
    }

    private function parseFile(string $path, ?string $channel, int $limit): array
    {
        if (!File::exists($path) || !is_readable($path)) {
            return [];
        }

        $entries = [];
        $current = null;
        $fileName = basename($path);

        foreach ($this->readFileLines($path) as $line) {
            $matches = [];
            if ($this->isEntryHeader($line, $matches)) {
                if ($current) {
                    $entries[] = $current;
                }

                $current = $this->buildEntryFromMatches($matches, $channel, $fileName);
                continue;
            }

            if ($current) {
                $current['message'] .= PHP_EOL . $line;
            }
        }

        if ($current) {
            $entries[] = $current;
        }

        if (count($entries) > $limit) {
            $entries = array_slice($entries, -$limit);
        }

        return $entries;
    }

    private function readFileLines(string $path): Generator
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return;
        }

        try {
            while (($line = fgets($handle)) !== false) {
                yield rtrim($line, "\r\n");
            }
        } finally {
            fclose($handle);
        }
    }

    private function buildEntryFromMatches(array $matches, ?string $channel, string $fileName): array
    {
        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $matches['datetime']);
        [$message, $context] = $this->splitMessageAndContext($matches['body'] ?? '');

        return [
            'timestamp' => $timestamp,
            'environment' => $matches['environment'],
            'level' => strtolower($matches['level']),
            'message' => $message,
            'context' => $context,
            'channel' => $channel ?? $this->inferChannel($fileName),
            'file' => $fileName,
        ];
    }

    private function isEntryHeader(string $line, ?array &$matches = null): bool
    {
        $pattern = '/^\[(?<datetime>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(?<environment>[\w\-.]+)\.(?<level>[A-Z]+):\s(?<body>.*)$/';

        return preg_match($pattern, $line, $matches) === 1;
    }

    private function entryPassesFilters(array $entry, array $filters): bool
    {
        if (!empty($filters['channel']) && $entry['channel'] !== $filters['channel']) {
            return false;
        }

        if (!empty($filters['level']) && $entry['level'] !== strtolower($filters['level'])) {
            return false;
        }

        if (!empty($filters['environment']) && !Str::contains(strtolower($entry['environment']), strtolower($filters['environment']))) {
            return false;
        }

        if (!empty($filters['date']) && $entry['timestamp']->format('Y-m-d') !== $filters['date']) {
            return false;
        }

        if (!empty($filters['search'])) {
            $haystack = strtolower($entry['message'] . ' ' . $this->contextToString($entry['context']));

            if (!Str::contains($haystack, strtolower($filters['search']))) {
                return false;
            }
        }

        return true;
    }

    private function contextToString(?array $context): string
    {
        if (empty($context)) {
            return '';
        }

        return json_encode($context);
    }

    private function splitMessageAndContext(string $body): array
    {
        $body = trim($body);

        if ($body === '') {
            return ['', null];
        }

        $context = null;

        $lastBracePosition = strrpos($body, '{');

        if ($lastBracePosition !== false) {
            $possibleJson = substr($body, $lastBracePosition);
            $decoded = json_decode($possibleJson, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $context = $decoded;
                $body = trim(substr($body, 0, $lastBracePosition));
            }
        }

        return [$body, $context];
    }

    private function inferChannel(string $fileName): string
    {
        $name = Str::replaceLast('.log', '', $fileName);

        if (preg_match('/^(.*)-\d{4}-\d{2}-\d{2}$/', $name, $matches)) {
            return $matches[1];
        }

        return $name;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, 2) . ' ' . $units[$power];
    }
}
