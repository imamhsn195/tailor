@props(['fields' => [], 'url' => null, 'route' => null])

@php
    $formUrl = $url ?? $route ?? request()->url();
    $searchFields = $fields ?? [];
@endphp

<form action="{{ $formUrl }}" method="GET" class="mb-3">
    <div class="row">
        @if(!empty($searchFields))
            @foreach($searchFields as $field)
            <div class="col-md-{{ $field['col'] ?? 3 }}">
                <div class="form-group">
                    @if($field['type'] === 'text' || $field['type'] === 'number' || $field['type'] === 'date')
                        <input 
                            type="{{ $field['type'] }}" 
                            name="{{ $field['name'] }}" 
                            class="form-control" 
                            placeholder="{{ $field['placeholder'] ?? '' }}"
                            value="{{ $field['value'] ?? '' }}"
                        >
                    @elseif($field['type'] === 'select')
                        <select name="{{ $field['name'] }}" class="form-control">
                            @if(isset($field['defaultOption']))
                                <option value="{{ $field['defaultOption']['value'] }}">{{ $field['defaultOption']['label'] }}</option>
                            @endif
                            @if(isset($field['options']))
                                @foreach($field['options'] as $value => $label)
                                    <option value="{{ $value }}" {{ ($field['value'] ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            {{-- Simple search form when no fields provided --}}
            <div class="col-md-4">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="{{ trans_common('search') }}..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>
        @endif
        <div class="col-md-auto">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> {{ trans_common('search') }}
                </button>
                <a href="{{ $formUrl }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> {{ trans_common('reset') }}
                </a>
            </div>
        </div>
    </div>
</form>

