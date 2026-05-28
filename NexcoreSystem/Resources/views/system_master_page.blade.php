{{--
|--------------------------------------------------------------------------
| NexCore System Master Page
|--------------------------------------------------------------------------
|
| Module:       NexcoreSystem
| Version:      2.0
| Route:        /nexcore/system/system_master_page
| Location:     NexcoreSystem/Resources/views/system_master_page.blade.php
|
| This is the gold-standard reference page that demonstrates all 7
| independent page sections working together. Each section is loaded
| via @include from its own blade file, keeping everything modular
| and independently manageable.
|
| SECTIONS (7 total):
|   1. Sidebar     - Left navigation panel
|   2. Title Bar   - Top bar with module name, breadcrumbs, user controls
|   3. Action Bar  - Action buttons, search, filters
|   4. Header      - Page title, description, stat blocks
|   5. Body        - Main content area (tables, forms, cards)
|   6. Footer      - Copyright, status, gradient line
|   7. Drawer      - Right-side slide-out panel
|
| ALSO INCLUDES:
|   - Messages component (NxAlert) for SweetAlert2 popups
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NexCore System - Master Page</title>

    {{-- Google Fonts: Montserrat --}}
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    {{-- Page-level styles for the master layout --}}
    <style>
        /* =====================================================
           GLOBAL RESET & BASE
           Dark theme foundation for the entire page.
           ===================================================== */
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

        /* =====================================================
           LAYOUT WRAPPER
           Full viewport flex layout: sidebar + content area.
           ===================================================== */
        .nxsb-layout {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* =====================================================
           CONTENT AREA
           Everything to the right of the sidebar. Flex column
           so sections stack vertically: titlebar, actionbar,
           header, body (grows), footer.
           ===================================================== */
        .nxsb-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100vh;
            margin-left: 270px;
            transition: margin-left 0.3s ease;
        }

        /* When sidebar is collapsed, content shifts left */
        .nxsb-content-collapsed {
            margin-left: 62px;
        }

        /* =====================================================
           MOBILE: Content takes full width
           ===================================================== */
        @@media (max-width: 768px) {
            .nxsb-content {
                margin-left: 0;
            }
            .nxsb-content-collapsed {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    {{-- ============================================================
         SECTION 1: SIDEBAR (Left Navigation)
         Loaded from: /public/nexcore/system_sidebar/
         ============================================================ --}}
    @include('nexcore_system::system_master_sidebar', [
'menuItems' => [
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


    {{-- ============================================================
         CONTENT AREA
         All sections to the right of the sidebar.
         ============================================================ --}}
    <div class="nxsb-content" id="nxContent">

        {{-- ========================================================
             SECTION 2: TITLE BAR (Top Bar)
             Loaded from: /public/nexcore/system_titlebar/
             ======================================================== --}}
        @include('nexcore_system::system_master_titlebar', [
            'moduleName'   => 'NexCore System',
            'pageName'     => 'Master Page',
            'userInitials' => 'KM',
        ])


        {{-- ========================================================
             SECTION 3: ACTION BAR (Buttons + Search)
             Loaded from: /public/nexcore/system_actionbar/
             ======================================================== --}}
        @include('nexcore_system::system_master_actionbar', [
            'actionButtons' => [
                ['text' => 'Add New',   'icon' => 'fas fa-plus',      'primary' => true, 'onclick' => "NxAlert.success('Add New', 'This will open the Add New form.')"],
                ['text' => 'Export',    'icon' => 'fas fa-download',   'onclick' => "NxAlert.info('Export', 'Exporting data to file...')"],
                ['text' => 'Import',   'icon' => 'fas fa-upload',     'onclick' => "NxAlert.warning('Import', 'Import feature coming soon.')"],
            ],
            'searchPlaceholder' => 'Search system...',
        ])


        {{-- ========================================================
             SECTION 4: HEADER (Page Title + Stats)
             Loaded from: /public/nexcore/system_header/
             ======================================================== --}}
        @include('nexcore_system::system_master_header', [
            'headerTitle' => 'System Master Page',
            'headerDesc'  => 'The gold standard reference page demonstrating all 7 independent page sections. Each component is self-contained with its own CSS, JS, and blade file.',
            'headerStats' => [
                ['num' => '7',  'label' => 'Sections',   'color' => '#059669'],
                ['num' => '21', 'label' => 'Files',      'color' => '#2563eb'],
                ['num' => '6',  'label' => 'Messages',   'color' => '#d97706'],
                ['num' => '1',  'label' => 'Sidebar',    'color' => '#7c3aed'],
            ],
        ])


        {{-- ========================================================
             SECTION 5: BODY (Main Content)
             Loaded from: /public/nexcore/system_body/
             ======================================================== --}}
        @include('nexcore_system::system_master_body')


        {{-- ========================================================
             SECTION 6: FOOTER (Copyright + Gradient Line)
             Loaded from: /public/nexcore/system_footer/
             ======================================================== --}}
        @include('nexcore_system::system_master_footer')

    </div>


    {{-- ============================================================
         SECTION 7: DRAWER (Right Slide-Out Panel)
         Loaded from: /public/nexcore/system_drawer/
         ============================================================ --}}
    @include('nexcore_system::system_master_drawer')


    {{-- ============================================================
         MESSAGE BOXES (NxAlert)
         Loaded from: /public/nexcore/system_messages/
         ============================================================ --}}
    @include('nexcore_system::system_master_messages')

</body>
</html>
