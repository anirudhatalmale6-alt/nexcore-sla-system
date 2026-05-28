{{--
|--------------------------------------------------------------------------
| NexCore System Master Body
|--------------------------------------------------------------------------
|
| Component:     system_body
| Version:       1.0
| Location:      resources/views/nexcore/system_master_body.blade.php
|
| The main content area. This is where tables, forms, cards, dashboards,
| and all primary content is displayed. Grows to fill the remaining
| vertical space between the header and footer.
|
| USAGE:
|   @include('nexcore.system_master_body', [
|       'bodyContent' => '<p>Your HTML content here</p>',
|   ])
|
|   Or use @section/@yield in the parent layout for richer content.
|
| EMPTY STATE:
|   If no $bodyContent is provided, a default "Coming Soon" empty state
|   is displayed with the NexCore gradient styling.
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- Body CSS --}}
<link href="/public/nexcore/system_body/css/system_master_body.css" rel="stylesheet">

{{-- Body Structure --}}
<div class="nxbd">
    <div class="nxbd-inner">

        @if(isset($bodyContent) && !empty($bodyContent))
            {!! $bodyContent !!}
        @else
            {{-- Default Empty / Coming Soon State --}}
            <div class="nxbd-empty">
                <div class="nxbd-empty-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="nxbd-empty-title">Coming Soon</div>
                <div class="nxbd-empty-desc">
                    This section is currently under development.
                    Something awesome is being built here.
                </div>
                <div class="nxbd-gradient-line"></div>
            </div>
        @endif

    </div>
</div>
