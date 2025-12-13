@props(['filterCount'])

<div id="filterPanel" class="filter-panel" style="display: none;">
    <div class="filter-panel-content">
        <div class="filter-panel-header">
            <h5>{{ trans_common('filter') }}</h5>
            <button type="button" class="close" id="filterCloseBtn">
                <span>&times;</span>
            </button>
        </div>
        <div class="filter-panel-body">
            {{ $slot }}
        </div>
        <div class="filter-panel-footer">
            <button type="button" class="btn btn-primary" id="applyFiltersBtn">
                {{ trans_common('apply') }}
            </button>
            <button type="button" class="btn btn-secondary" id="clearFiltersBtn">
                {{ trans_common('clear') }}
            </button>
        </div>
    </div>
    <div class="filter-panel-overlay" id="filterOverlay"></div>
</div>

@push('styles')
<style>
    .filter-panel {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100vh;
        background: #fff;
        box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        z-index: 1050;
        transition: right 0.3s ease-in-out;
        overflow-y: auto;
    }
    
    .filter-panel.active {
        right: 0;
    }
    
    .filter-panel-content {
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .filter-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }
    
    .filter-panel-body {
        flex: 1;
        overflow-y: auto;
    }
    
    .filter-panel-footer {
        padding-top: 20px;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 10px;
    }
    
    .filter-panel-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1049;
        display: none;
    }
    
    .filter-panel-overlay.active {
        display: block;
    }
    
    @media (max-width: 768px) {
        .filter-panel {
            width: 100%;
            right: -100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterPanel = document.getElementById('filterPanel');
        const filterToggleBtn = document.getElementById('filterToggleBtn');
        const filterCloseBtn = document.getElementById('filterCloseBtn');
        const filterOverlay = document.getElementById('filterOverlay');
        
        if (filterToggleBtn) {
            filterToggleBtn.addEventListener('click', function() {
                filterPanel.classList.add('active');
                filterOverlay.classList.add('active');
            });
        }
        
        if (filterCloseBtn) {
            filterCloseBtn.addEventListener('click', function() {
                filterPanel.classList.remove('active');
                filterOverlay.classList.remove('active');
            });
        }
        
        if (filterOverlay) {
            filterOverlay.addEventListener('click', function() {
                filterPanel.classList.remove('active');
                filterOverlay.classList.remove('active');
            });
        }
    });
</script>
@endpush

