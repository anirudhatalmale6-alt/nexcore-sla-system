{{--
|--------------------------------------------------------------------------
| NexCore System Master Drawer
|--------------------------------------------------------------------------
|
| Component:     system_drawer
| Version:       1.0
| Location:      resources/views/nexcore/system_master_drawer.blade.php
|
| Slide-out panel from the right edge of the screen. Used for quick
| detail views, editing forms, or previews without navigating away.
| Hidden by default, opened via JavaScript: NxDrawer.open(title, content)
|
| USAGE:
|   @include('nexcore.system_master_drawer')
|
| JAVASCRIPT API:
|   NxDrawer.open('Record Details', '<p>Content HTML</p>');
|   NxDrawer.close();
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Drawer CSS --}}
<link href="/public/nexcore/system_drawer/css/system_master_drawer.css" rel="stylesheet">

{{-- Drawer Overlay Backdrop --}}
<div class="nxdw-overlay" id="nxDrawerOverlay"></div>

{{-- Drawer Panel --}}
<div class="nxdw" id="nxDrawer">

    {{-- Drawer Header --}}
    <div class="nxdw-header">
        <span class="nxdw-title" id="nxDrawerTitle">Details</span>
        <button class="nxdw-close" onclick="NxDrawer.close()" title="Close Drawer">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Drawer Body (scrollable content) --}}
    <div class="nxdw-body" id="nxDrawerBody">
        {{-- Content injected via NxDrawer.open() --}}
    </div>

    {{-- Drawer Footer (optional action buttons) --}}
    <div class="nxdw-footer" id="nxDrawerFooter">
        {{-- Footer buttons injected dynamically if needed --}}
    </div>

</div>

{{-- Drawer JS --}}
<script src="/public/nexcore/system_drawer/js/system_master_drawer.js"></script>
