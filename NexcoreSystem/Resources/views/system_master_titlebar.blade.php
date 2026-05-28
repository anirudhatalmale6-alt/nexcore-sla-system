{{--
|--------------------------------------------------------------------------
| NexCore System Master Title Bar
|--------------------------------------------------------------------------
|
| Component:     system_titlebar
| Version:       1.0
| Location:      resources/views/nexcore/system_master_titlebar.blade.php
|
| The top title bar sits at the very top of the content area (right
| of the sidebar). It shows the module name, breadcrumb page name,
| notification icons, and user avatar.
|
| USAGE:
|   @include('nexcore.system_master_titlebar', [
|       'moduleName' => 'Directors',
|       'pageName'   => 'Master Page',
|       'userInitials' => 'KM',
|   ])
|
| VARIABLES:
|   $moduleName   - (string) The current module name shown in bold
|   $pageName     - (string) Breadcrumb page name shown after separator
|   $userInitials - (string) 2-letter initials for the avatar circle
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Title Bar CSS --}}
<link href="/public/nexcore/system_titlebar/css/system_master_titlebar.css" rel="stylesheet">

{{-- Title Bar Structure --}}
<div class="nxtb">

    {{-- Left: Module name + breadcrumb --}}
    <div class="nxtb-left">

        {{-- Mobile hamburger menu toggle --}}
        <button class="nxtb-mobile-toggle" onclick="NxSidebar.openMobile()" title="Open Menu">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Module name --}}
        <span class="nxtb-module">{{ $moduleName ?? 'NexCore' }}</span>

        {{-- Breadcrumb separator --}}
        <div class="nxtb-sep"></div>

        {{-- Page name --}}
        <span class="nxtb-page">{{ $pageName ?? 'Dashboard' }}</span>
    </div>

    {{-- Right: Notifications + User --}}
    <div class="nxtb-right">

        {{-- Notification bell --}}
        <button class="nxtb-icon-btn" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="nxtb-dot"></span>
        </button>

        {{-- Settings --}}
        <button class="nxtb-icon-btn" title="Settings">
            <i class="fas fa-cog"></i>
        </button>

        {{-- User avatar --}}
        <div class="nxtb-avatar">{{ $userInitials ?? 'NC' }}</div>
    </div>

</div>
