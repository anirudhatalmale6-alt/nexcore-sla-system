{{--
|--------------------------------------------------------------------------
| NexCore Address Module Layout
|--------------------------------------------------------------------------
| Uses the NexcoreSystem page shell (sidebar, titlebar, footer).
| Address content loads INSIDE the System Menu - same desk, same sidebar.
| NexcoreFormKit assets are loaded for component styling.
|--------------------------------------------------------------------------
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NexCore - @yield('title', 'Addresses')</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/public/css/nexcore_gl_master.css?v={{ time() }}" rel="stylesheet">

    @include('nexcore_formkit::formkit-assets')

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            background: #0a0e1a;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
        }

        .nxsb-layout {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        .nxsb-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100vh;
            margin-left: 270px;
            transition: margin-left 0.3s ease;
        }

        .nxsb-content-collapsed {
            margin-left: 62px;
        }

        .nx-address-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px;
        }

        @@media (max-width: 768px) {
            .nxsb-content { margin-left: 0; }
            .nxsb-content-collapsed { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

    @include('nexcore_system::system_master_sidebar', [
        'menuItems' => [
            ['group' => 'Menu', 'items' => [
                ['icon' => 'fas fa-solar-panel', 'text' => 'System Menu', 'url' => '/nexcore/menus/system'],
                ['icon' => 'fas fa-briefcase', 'text' => 'Practice Menu', 'url' => '/nexcore/menus/practice'],
                ['icon' => 'fas fa-user-edit', 'text' => 'Clerk Menu', 'url' => '/nexcore/menus/clerk'],
                ['icon' => 'fas fa-sliders-h', 'text' => 'Defaults Menu', 'url' => '/nexcore/menus/defaults'],
            ]],
            ['group' => 'Tenants', 'items' => [
                ['icon' => 'fas fa-city', 'text' => 'Practices', 'children' => [
                    ['icon' => 'fas fa-tachometer-alt', 'text' => 'Manage Practice', 'url' => '/nexcore/practices'],
                    ['icon' => 'fas fa-user-plus', 'text' => 'Add a User', 'url' => '#'],
                ]],
            ]],
            ['group' => 'Masters', 'items' => [
                ['icon' => 'fas fa-user-tie', 'text' => 'Directors', 'children' => [
                    ['icon' => 'fas fa-list', 'text' => 'List Directors', 'url' => '#'],
                    ['icon' => 'fas fa-chart-pie', 'text' => 'Shares', 'url' => '#'],
                    ['icon' => 'fas fa-camera', 'text' => 'Photos', 'url' => '#'],
                    ['icon' => 'fas fa-folder-open', 'text' => 'Documents', 'url' => '#'],
                ]],
                ['icon' => 'fas fa-building', 'text' => 'Clients', 'children' => [
                    ['icon' => 'fas fa-list', 'text' => 'List Clients', 'url' => '/nexcore/clients'],
                    ['icon' => 'fas fa-address-book', 'text' => 'Contacts', 'url' => '#'],
                    ['icon' => 'fas fa-folder-open', 'text' => 'Documents', 'url' => '#'],
                ]],
                ['icon' => 'fas fa-map-marked-alt', 'text' => 'Addresses', 'children' => [
                    ['icon' => 'fas fa-list', 'text' => 'Address Registry', 'url' => '/nexcore/addresses'],
                    ['icon' => 'fas fa-plus', 'text' => 'New Address', 'url' => '/nexcore/addresses/create'],
                ]],
                ['icon' => 'fas fa-truck', 'text' => 'Suppliers', 'children' => [
                    ['icon' => 'fas fa-list', 'text' => 'List Suppliers', 'url' => '#'],
                    ['icon' => 'fas fa-folder-open', 'text' => 'Documents', 'url' => '#'],
                ]],
            ]],
            ['group' => 'Modules', 'items' => [
                ['icon' => 'fas fa-users-cog', 'text' => 'Client Manager', 'url' => '/nexcore/clients'],
                ['icon' => 'fas fa-money-check-alt', 'text' => 'Payroll', 'url' => '#'],
                ['icon' => 'fas fa-calculator', 'text' => 'Accounting', 'url' => '#'],
            ]],
            ['group' => 'System', 'items' => [
                ['icon' => 'fas fa-cog', 'text' => 'Settings', 'url' => '#'],
                ['icon' => 'fas fa-users', 'text' => 'Users', 'url' => '#'],
                ['icon' => 'fas fa-history', 'text' => 'Audit Trail', 'url' => '#'],
            ]],
        ]
    ])

    <div class="nxsb-content" id="nxContent">

        @php $nxPageTitle = trim($__env->yieldContent('title', 'Addresses')); @endphp
        @include('nexcore_system::system_master_titlebar', [
            'moduleName'   => 'NexCore Address',
            'pageName'     => $nxPageTitle,
            'userInitials' => 'YG',
        ])

        <div class="nx-address-body">
            @yield('content')
        </div>

        @include('nexcore_system::system_master_footer')

    </div>

    @include('nexcore_system::system_master_drawer')
    @include('nexcore_system::system_master_messages')

@stack('scripts')
</body>
</html>
