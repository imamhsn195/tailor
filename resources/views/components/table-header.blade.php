@props(['title', 'createRoute' => null, 'createPermission' => null, 'filterCount' => 0])

<div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ $title }}</h3>
        <div class="card-tools">
            @if($filterCount > 0)
                <button type="button" class="btn btn-sm btn-info mr-2" id="filterToggleBtn">
                    <i class="fas fa-filter"></i> {{ trans_common('filter') }}
                    @if($filterCount > 0)
                        <span class="badge badge-light">{{ $filterCount }}</span>
                    @endif
                </button>
            @endif
            @if($createRoute && (!$createPermission || auth()->user()->can($createPermission)))
                <a href="{{ $createRoute }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> {{ trans_common('create') }}
                </a>
            @endif
        </div>
    </div>
</div>

@if($filterCount > 0)
    <x-filter-panel :filterCount="$filterCount" />
@endif

