{{--
|--------------------------------------------------------------------------
| NexCore System Master Action Bar
|--------------------------------------------------------------------------
|
| Component:     system_actionbar
| Version:       1.0
| Location:      resources/views/nexcore/system_master_actionbar.blade.php
|
| Single-row bar below the title bar. Contains action buttons on the
| left (Add New, Export, etc.) and search/filter controls on the right.
|
| USAGE:
|   @include('nexcore.system_master_actionbar', [
|       'actionButtons'  => [...],
|       'searchPlaceholder' => 'Search records...',
|   ])
|
| ACTION BUTTONS FORMAT:
|   [
|       ['text' => 'Add New',  'icon' => 'fas fa-plus',     'primary' => true, 'onclick' => '...'],
|       ['text' => 'Export',   'icon' => 'fas fa-download',  'onclick' => '...'],
|       ['text' => 'Import',  'icon' => 'fas fa-upload',     'onclick' => '...'],
|   ]
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Action Bar CSS --}}
<link href="/public/nexcore/system_actionbar/css/system_master_actionbar.css" rel="stylesheet">

{{-- Action Bar Structure --}}
<div class="nxab">

    {{-- Left: Action Buttons --}}
    <div class="nxab-left">
        @if(isset($actionButtons) && is_array($actionButtons))
            @foreach($actionButtons as $btn)
                <button class="nxab-btn {{ !empty($btn['primary']) ? 'nxab-btn-primary' : '' }}"
                        {!! !empty($btn['onclick']) ? 'onclick="'.$btn['onclick'].'"' : '' !!}
                        title="{{ $btn['text'] ?? '' }}">
                    @if(!empty($btn['icon']))
                        <i class="{{ $btn['icon'] }}"></i>
                    @endif
                    {{ $btn['text'] ?? '' }}
                </button>
            @endforeach
        @endif
    </div>

    {{-- Right: Search --}}
    <div class="nxab-right">
        <div class="nxab-search-wrap">
            <i class="fas fa-search nxab-search-icon"></i>
            <input type="text" class="nxab-search"
                   placeholder="{{ $searchPlaceholder ?? 'Search...' }}">
        </div>
    </div>

</div>
