{{--
|--------------------------------------------------------------------------
| NexCore System Master Header
|--------------------------------------------------------------------------
|
| Component:     system_header
| Version:       1.0
| Location:      resources/views/nexcore/system_master_header.blade.php
|
| The header section below the action bar. Displays a large page title,
| description text, and optional stat blocks on the right side.
|
| USAGE:
|   @include('nexcore.system_master_header', [
|       'headerTitle' => 'All Directors',
|       'headerDesc'  => 'Manage all registered directors.',
|       'headerStats' => [
|           ['num' => '48', 'label' => 'Total', 'color' => '#059669'],
|           ['num' => '12', 'label' => 'Active', 'color' => '#2563eb'],
|       ],
|   ])
|
| STAT FORMAT:
|   num   - (string) The number to display
|   label - (string) Short label below the number
|   color - (string) Optional hex colour for the number (default: #059669)
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Header CSS --}}
<link href="/public/nexcore/system_header/css/system_master_header.css" rel="stylesheet">

{{-- Header Structure --}}
<div class="nxhd">
    <div class="nxhd-row">

        {{-- Left: Title + Description --}}
        <div class="nxhd-info">
            <div class="nxhd-title">{{ $headerTitle ?? 'Page Title' }}</div>
            <div class="nxhd-desc">{{ $headerDesc ?? '' }}</div>
        </div>

        {{-- Right: Stat Blocks --}}
        @if(isset($headerStats) && is_array($headerStats))
            <div class="nxhd-stats">
                @foreach($headerStats as $stat)
                    <div class="nxhd-stat">
                        <div class="nxhd-stat-num"
                             style="color: {{ $stat['color'] ?? '#059669' }}">
                            {{ $stat['num'] ?? '0' }}
                        </div>
                        <div class="nxhd-stat-label">{{ $stat['label'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
