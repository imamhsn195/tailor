<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;
use Spatie\Activitylog\LogOptions;

trait LogsActivity
{
    use SpatieLogsActivity;

    /**
     * Configure activity log options
     * Override this method in your model to customize logging behavior
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLoggableAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName());
    }

    /**
     * Get attributes that should be logged
     * Override this method in your model to specify which attributes to log
     */
    protected function getLoggableAttributes(): array
    {
        return $this->fillable ?? [];
    }

    /**
     * Get log name for this model
     * Override this method in your model to customize log name
     */
    protected function getLogName(): string
    {
        return strtolower(class_basename($this));
    }
}


