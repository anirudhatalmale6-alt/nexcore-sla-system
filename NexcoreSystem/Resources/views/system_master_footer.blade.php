{{--
|--------------------------------------------------------------------------
| NexCore System Master Footer
|--------------------------------------------------------------------------
|
| Component:     system_footer
| Version:       1.0
| Location:      resources/views/nexcore/system_master_footer.blade.php
|
| Sticky footer bar locked to the bottom of the page. Shows copyright
| text, system status, and the NexCore 4-colour gradient line.
|
| USAGE:
|   @include('nexcore.system_master_footer')
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Footer CSS --}}
<link href="/public/nexcore/system_footer/css/system_master_footer.css" rel="stylesheet">

{{-- Footer Structure --}}
<div class="nxft">

    {{-- Left: Copyright --}}
    <div class="nxft-left">
        &copy; {{ date('Y') }} NexCore Africa Proprietary Limited
    </div>

    {{-- Right: Status + Version --}}
    <div class="nxft-right">
        <div class="nxft-status">
            <span class="nxft-dot"></span>
            System Online
        </div>
        <span class="nxft-version">v1.0</span>
    </div>

</div>

{{-- 4-colour gradient line at the very bottom --}}
<div class="nxft-gradient-line"></div>
