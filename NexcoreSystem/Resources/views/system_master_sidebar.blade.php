{{--
|--------------------------------------------------------------------------
| NexCore System Master Sidebar
|--------------------------------------------------------------------------
|
| Component:     system_sidebar
| Version:       2.1
| Location:      resources/views/nexcore/system_master_sidebar.blade.php
|
| Supports 3-level menu:
|   Level 1: Group headings (MASTERS, MODULES, SYSTEM)
|   Level 2: Menu items (clickable link or expandable parent)
|   Level 3: Child items (shown when parent is expanded)
|
| MENU ITEMS FORMAT:
|   [
|       ['group' => 'Masters', 'items' => [
|           ['icon' => 'fas fa-user-tie', 'text' => 'Directors', 'children' => [
|               ['icon' => 'fas fa-list', 'text' => 'List Directors', 'url' => '#'],
|               ['icon' => 'fas fa-chart-pie', 'text' => 'Shares', 'url' => '#'],
|           ]],
|           ['icon' => 'fas fa-building', 'text' => 'Clients', 'url' => '/nexcore/clients'],
|       ]],
|   ]
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Sidebar CSS --}}
<link href="/public/nexcore/system_sidebar/css/system_master_sidebar.css?v={{ time() }}" rel="stylesheet">

{{-- Sidebar Structure --}}
<aside class="nxsb" id="nxSidebar">
    <div class="nxsb-inner">

        {{-- Brand / Logo Area --}}
        <div class="nxsb-brand">
            <a href="/nexcore/clients" title="NexCore Home">
                <img src="/public/nexcore/branding/nexcore-logo-dark.jpg"
                     alt="NexCore" class="nxsb-brand-logo">
            </a>
            <button class="nxsb-toggle" onclick="NxSidebar.toggle()" title="Toggle Sidebar (Ctrl+B)">
                <i class="fas fa-chevron-left" id="nxSbToggleIcon"></i>
            </button>
        </div>

        {{-- Scrollable Menu Area --}}
        <nav class="nxsb-menu">
            @if(isset($menuItems) && is_array($menuItems))
                @foreach($menuItems as $group)
                    <div class="nxsb-group">
                        @if(!empty($group['group']))
                            <button class="nxsb-group-label" onclick="NxSidebar.toggleGroup(this)">
                                <span class="nxsb-group-label-text">{{ $group['group'] }}</span>
                                <i class="fas fa-chevron-down nxsb-group-arrow"></i>
                            </button>
                        @endif
                        <div class="nxsb-group-items">
                            @if(isset($group['items']) && is_array($group['items']))
                                @foreach($group['items'] as $item)
                                    @if(!empty($item['children']))
                                        {{-- Level 2: Expandable parent with children --}}
                                        <div class="nxsb-section">
                                            <button class="nxsb-parent" onclick="NxSidebar.toggleSection(this)">
                                                <span class="nxsb-parent-left">
                                                    <span class="nxsb-item-icon"><i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i></span>
                                                    <span class="nxsb-item-text">{{ $item['text'] ?? '' }}</span>
                                                </span>
                                                <i class="fas fa-chevron-down nxsb-arrow"></i>
                                            </button>
                                            <div class="nxsb-children">
                                                @foreach($item['children'] as $child)
                                                    <a href="{{ $child['url'] ?? '#' }}"
                                                       class="nxsb-child"
                                                       data-tooltip="{{ $child['text'] ?? '' }}">
                                                        <i class="{{ $child['icon'] ?? 'fas fa-circle' }}"></i>
                                                        <span class="nxsb-item-text">{{ $child['text'] ?? '' }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        {{-- Level 2: Simple link (no children) --}}
                                        <a href="{{ $item['url'] ?? '#' }}"
                                           class="nxsb-item"
                                           data-tooltip="{{ $item['text'] ?? '' }}">
                                            <span class="nxsb-item-icon">
                                                <i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                                            </span>
                                            <span class="nxsb-item-text">{{ $item['text'] ?? '' }}</span>
                                            @if(!empty($item['badge']))
                                                <span class="nxsb-badge">{{ $item['badge'] }}</span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @if(!$loop->last)
                        <div class="nxsb-divider"></div>
                    @endif
                @endforeach
            @endif
        </nav>

        {{-- Footer --}}
        <div class="nxsb-footer">
            <div class="nxsb-footer-text">
                NexCore Africa Proprietary Limited
            </div>
            <div class="nxsb-footer-line"></div>
        </div>

    </div>
</aside>

{{-- Mobile Overlay Backdrop --}}
<div class="nxsb-overlay"></div>

{{-- Sidebar JS --}}
<script src="/public/nexcore/system_sidebar/js/system_master_sidebar.js?v={{ time() }}"></script>
