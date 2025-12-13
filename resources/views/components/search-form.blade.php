@props(['fields', 'url'])

<form action="{{ $url }}" method="GET" class="mb-3">
    <div class="row">
        @foreach($fields as $field)
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
        <div class="col-md-auto">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> {{ trans_common('search') }}
                </button>
                <a href="{{ $url }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> {{ trans_common('reset') }}
                </a>
            </div>
        </div>
    </div>
</form>

