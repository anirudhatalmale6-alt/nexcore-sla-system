<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Import Statement - {{ $client->company_name }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/public/css/nexcore_gl_master.css?v={{ time() }}" rel="stylesheet">

    @include('nx_formkit::formkit-assets')

    <style>
        :root {
            --nx-sidebar-width: 260px;
            --nx-header-height: 60px;
            --nx-toolbar-height: 56px;
            --nx-footer-height: 44px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 16px; -webkit-font-smoothing: antialiased; }
        html, body {
            overflow: visible !important;
            height: auto !important;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: #e2e8f0;
            background: #050810;
            overflow-x: hidden !important;
            min-height: 100vh;
        }

        .nx-cosmos {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: linear-gradient(180deg, #050810 0%, #080c18 40%, #0d1225 70%, #111830 100%);
        }
        .nx-cosmos::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 600px 400px at 15% 20%, rgba(0,212,255,0.07) 0%, transparent 70%),
                radial-gradient(ellipse 500px 500px at 85% 30%, rgba(124,58,237,0.06) 0%, transparent 70%),
                radial-gradient(ellipse 400px 300px at 50% 80%, rgba(16,185,129,0.05) 0%, transparent 70%);
            animation: nx-nebula-shift 30s ease-in-out infinite alternate;
        }
        .nx-cosmos::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,212,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,212,255,0.015) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 20%, transparent 70%);
            -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 20%, transparent 70%);
        }
        @@keyframes nx-nebula-shift {
            0% { opacity: 1; transform: scale(1); }
            100% { opacity: 0.7; transform: scale(1.1) translate(2%, -1%); }
        }

        .nx-app-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
        }

        .nx-main {
            margin-left: var(--nx-sidebar-width);
            padding-top: var(--nx-header-height);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .nx-main-scroll {
            flex: 1;
            padding: 0 28px 28px;
            overflow-y: auto;
        }

        .nx-main-scroll::-webkit-scrollbar { width: 6px; }
        .nx-main-scroll::-webkit-scrollbar-track { background: transparent; }
        .nx-main-scroll::-webkit-scrollbar-thumb { background: rgba(0,212,255,0.15); border-radius: 10px; }

        @@media (max-width: 1024px) {
            .nx-main { margin-left: 0; }
        }

        /* Neon Button Styles */
        .neon-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            font-family: var(--font-body);
            line-height: 1.4;
        }
        .neon-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 6px;
            padding: 2px;
            background: linear-gradient(135deg, var(--neon-from), var(--neon-to));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        .neon-btn::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--neon-from), var(--neon-to));
            opacity: 0;
            filter: blur(12px);
            transition: opacity 0.4s;
            z-index: -1;
        }
        .neon-btn:hover::before { opacity: 1; }
        .neon-btn:hover::after { opacity: 0.4; }
        .neon-btn:hover { transform: translateY(-2px); }
        .neon-btn:active { transform: translateY(0) scale(0.98); }
        .neon-btn i { font-size: 12px; transition: transform 0.3s; }
        .neon-btn:hover i { transform: scale(1.15); }
        .neon-btn-amber { --neon-from: #f59e0b; --neon-to: #d97706; color: #f59e0b; background: rgba(245, 158, 11, 0.08); }
        .neon-btn-amber:hover { color: #fbbf24; background: rgba(245, 158, 11, 0.15); box-shadow: 0 0 20px rgba(245, 158, 11, 0.2), 0 0 40px rgba(245, 158, 11, 0.1); }
        .neon-btn-ghost { --neon-from: rgba(255,255,255,0.3); --neon-to: rgba(255,255,255,0.1); color: var(--text-secondary); background: rgba(255,255,255,0.04); }
        .neon-btn-ghost:hover { color: var(--text-primary); background: rgba(255,255,255,0.08); }
        .neon-btn-green { --neon-from: #10b981; --neon-to: #059669; color: #10b981; background: rgba(16,185,129,0.08); }
        .neon-btn-green:hover { color: #34d399; background: rgba(16,185,129,0.15); box-shadow: 0 0 20px rgba(16,185,129,0.2), 0 0 40px rgba(16,185,129,0.1); }

        @@keyframes neon-pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(245,158,11,0.15); }
            50% { box-shadow: 0 0 16px rgba(245,158,11,0.3), 0 0 32px rgba(245,158,11,0.1); }
        }
        .neon-pulse { animation: neon-pulse-glow 2.5s ease-in-out infinite; }

        /* Bank Import Styles (from original) */
        .bi-tabs { display:flex; gap:0; margin-bottom:24px; border-bottom:2px solid var(--border-subtle); }
        .bi-tab { padding:12px 24px; font-size:14px; font-weight:700; cursor:pointer; border-bottom:2px solid transparent; color:var(--text-muted); transition:all 0.2s; margin-bottom:-2px; display:flex; align-items:center; gap:8px; }
        .bi-tab.active { color:#f59e0b; border-bottom-color:#f59e0b; }
        .bi-tab:hover { color:#f59e0b; }
        .bi-panel { display:none; }
        .bi-panel.active { display:block; }
        .bi-label { display:block; font-size:12px; color:var(--text-secondary); margin-bottom:6px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; }
        .bi-select, .bi-input { width:100%; padding:10px 14px; border:1px solid var(--border-subtle); border-radius:8px; font-size:14px; font-weight:600; color:var(--text-primary); background:var(--bg-deepest); font-family:inherit; transition:all 0.2s; box-sizing:border-box; }
        .bi-select:focus, .bi-input:focus { border-color:rgba(245,158,11,0.5); box-shadow:0 0 0 3px rgba(245,158,11,0.08); outline:none; }
        .bi-select option { background:var(--bg-deepest); color:var(--text-primary); }
        .bi-file-drop { border:2px dashed rgba(245,158,11,0.2); border-radius:14px; padding:40px; text-align:center; cursor:pointer; transition:all 0.2s; background:rgba(245,158,11,0.02); }
        .bi-file-drop:hover, .bi-file-drop.dragover { border-color:rgba(245,158,11,0.5); background:rgba(245,158,11,0.05); }

        .bi-preview-stats { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
        .bi-preview-stat { background:rgba(255,255,255,0.03); border:1px solid var(--border-subtle); border-radius:8px; padding:10px 18px; font-size:13px; font-weight:700; color:var(--text-muted); }
        .bi-preview-stat span { font-weight:800; color:var(--text-primary); }
        .bi-preview-stat .credit { color:var(--accent-green); }
        .bi-preview-stat .debit { color:var(--accent-red); }

        .bi-preview-table { width:100%; border-collapse:collapse; font-size:13px; }
        .bi-preview-table th { padding:10px 14px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); text-align:left; border-bottom:1px solid var(--border-subtle); background:rgba(255,255,255,0.02); }
        .bi-preview-table td { padding:8px 14px; font-weight:600; color:var(--text-primary); border-bottom:1px solid rgba(255,255,255,0.03); }
        .bi-preview-table tbody tr:hover { background:rgba(245,158,11,0.03); }
        .bi-preview-table .credit { color:var(--accent-green); font-weight:800; font-family:var(--font-mono); }
        .bi-preview-table .debit { color:var(--accent-red); font-weight:800; font-family:var(--font-mono); }
        .bi-preview-table td:first-child { color:var(--text-muted); font-family:var(--font-mono); font-size:12px; }
        .bi-preview-table td:nth-child(2) { font-family:var(--font-mono); color:#f59e0b; font-size:12px; }
        .bi-preview-table td:last-child { font-family:var(--font-mono); color:var(--text-secondary); text-align:right; }

        .swal2-container { z-index: 99999 !important; }
        .nx-swal-popup { background:#ffffff !important; border-radius:18px !important; padding:32px 28px 24px !important; box-shadow:0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05) !important; max-width:420px !important; }
        .nx-swal-popup.nx-swal-success { border-top:4px solid #059669 !important; }
        .nx-swal-popup.nx-swal-warning { border-top:4px solid #e11d48 !important; }
        .nx-swal-popup.nx-swal-info { border-top:4px solid #2563eb !important; }
        .nx-swal-popup.nx-swal-confirm { border-top:4px solid #0891b2 !important; }
        .nx-swal-popup.nx-swal-error { border-top:4px solid #d97706 !important; }
        .nx-swal-title { color:#1e293b !important; font-family:'Poppins','Montserrat',sans-serif !important; font-size:20px !important; font-weight:800 !important; letter-spacing:-0.3px !important; padding:0 0 8px !important; }
        .nx-swal-html { color:#64748b !important; font-family:'Poppins','Montserrat',sans-serif !important; font-size:15px !important; font-weight:500 !important; line-height:1.6 !important; }
        .nx-swal-actions .swal2-confirm { font-family:'Poppins','Montserrat',sans-serif !important; font-weight:700 !important; font-size:14px !important; letter-spacing:0.5px !important; border-radius:10px !important; padding:12px 28px !important; }
        .nx-swal-actions .swal2-cancel { font-family:'Poppins','Montserrat',sans-serif !important; font-weight:700 !important; font-size:14px !important; letter-spacing:0.5px !important; border-radius:10px !important; padding:12px 28px !important; background:#f1f5f9 !important; color:#64748b !important; border:none !important; }
        .nx-swal-actions .swal2-cancel:hover { background:#e2e8f0 !important; color:#475569 !important; }
        .swal2-popup .swal2-icon { display:none !important; }
        .nx-swal-popup .swal2-image { margin:0 auto 12px !important; }

        @@media(max-width:768px){
            .bi-tabs { overflow-x:auto; }
        }
    </style>
</head>
<body>

    {{-- Animated Background --}}
    <div class="nx-cosmos"></div>

    <div class="nx-app-wrapper">

        {{-- 1. SIDEBAR --}}
        @include('nx_formkit::components.nx-sidebar', [
            'menuItems' => [
                ['type' => 'label', 'text' => 'nxClients'],
                ['type' => 'single', 'icon' => 'fas fa-arrow-left', 'text' => 'Return to Clients', 'url' => '/nxclients'],

                ['type' => 'label', 'text' => 'Process'],
                ['type' => 'group', 'icon' => 'fas fa-cogs', 'text' => 'Process', 'open' => false, 'children' => [
                    ['icon' => 'fas fa-book', 'text' => 'Journals', 'url' => '/nxclients/' . $client->id . '/accounting/journals'],
                    ['icon' => 'fas fa-plus-circle', 'text' => 'New Journal', 'url' => '/nxclients/' . $client->id . '/accounting/journals/create'],
                ]],


                ['type' => 'label', 'text' => 'Banking'],
                ['type' => 'group', 'icon' => 'fas fa-university', 'text' => 'Banking', 'open' => true, 'children' => [
                    ['icon' => 'fas fa-link', 'text' => 'Link Account', 'url' => '/nxclients/' . $client->id . '/accounting/bank/accounts/create'],
                    ['icon' => 'fas fa-file-import', 'text' => 'Import Statement', 'url' => '/nexcore/clients/' . $client->id . '/accounting/bank/' . $bankAccount->id . '/import'],
                    ['icon' => 'fas fa-random', 'text' => 'Allocate Tx', 'url' => '/nexcore/clients/' . $client->id . '/accounting/bank/' . $bankAccount->id . '/allocate'],
                    ['icon' => 'fas fa-check-double', 'text' => 'Bank Recon', 'url' => '/nxclients/' . $client->id . '/accounting/bank/accounts'],
                    ['icon' => 'fas fa-file-alt', 'text' => 'View Statements', 'url' => '/nexcore/clients/' . $client->id . '/accounting/bank/' . $bankAccount->id . '/statements'],
                ]],

                ['type' => 'label', 'text' => 'Reports'],
                ['type' => 'group', 'icon' => 'fas fa-chart-bar', 'text' => 'Reports', 'open' => false, 'children' => [
                    ['icon' => 'fas fa-balance-scale', 'text' => 'Trial Balance', 'url' => '/nxclients/' . $client->id . '/accounting/trial-balance'],
                    ['icon' => 'fas fa-file-invoice-dollar', 'text' => 'Income Statement', 'url' => '/nxclients/' . $client->id . '/accounting/income-statement'],
                    ['icon' => 'fas fa-chart-pie', 'text' => 'Balance Sheet', 'url' => '/nxclients/' . $client->id . '/accounting/balance-sheet'],
                    ['icon' => 'fas fa-book-open', 'text' => 'General Ledger', 'url' => '/nxclients/' . $client->id . '/accounting/ledger'],
                ]],

                ['type' => 'label', 'text' => 'Maintain'],
                ['type' => 'group', 'icon' => 'fas fa-wrench', 'text' => 'Maintain', 'open' => false, 'children' => [
                    ['icon' => 'fas fa-sitemap', 'text' => 'Chart of Accounts', 'url' => '/nxclients/' . $client->id . '/accounting/accounts'],
                ]],
            ],
            'userInitials' => auth()->check() ? strtoupper(substr(auth()->user()->first_name ?? 'N', 0, 1) . substr(auth()->user()->last_name ?? 'X', 0, 1)) : 'NX',
            'userName' => auth()->check() ? (auth()->user()->first_name . ' ' . auth()->user()->last_name) : 'NexCore User',
            'userRole' => 'Client Manager',
        ])

        {{-- 2. HEADER --}}
        @include('nx_formkit::components.nx-header', [
            'moduleName' => 'nxClients',
            'searchPlaceholder' => 'Search...',
            'userInitials' => auth()->check() ? strtoupper(substr(auth()->user()->first_name ?? 'N', 0, 1) . substr(auth()->user()->last_name ?? 'X', 0, 1)) : 'NX',
            'userName' => auth()->check() ? (auth()->user()->first_name . ' ' . substr(auth()->user()->last_name ?? '', 0, 1) . '.') : 'NexCore',
            'userRole' => 'Client Manager',
        ])

        {{-- MAIN CONTENT AREA --}}
        <div class="nx-main">
            <div class="nx-main-scroll">

                {{-- 3. BREADCRUMB --}}
                @include('nx_formkit::components.nx-breadcrumb', [
                    'breadcrumbs' => [
                        ['text' => 'nxClients', 'url' => '/nxclients'],
                        ['text' => $client->company_name, 'url' => '/nxclients/' . $client->id],
                        ['text' => 'Bank Accounts', 'url' => '/nxclients/' . $client->id . '/accounting/bank/accounts'],
                        ['text' => 'Import Statement'],
                    ]
                ])

                {{-- 4. PAGE HEADER --}}
                @include('nx_formkit::components.nx-page-header', [
                    'title' => 'Import Statement',
                    'subtitle' => $client->company_name,
                ])

                {{-- ═══ ORIGINAL BANK IMPORT CONTENT (exact copy) ═══ --}}

@php
    $bankLower = strtolower($bankAccount->bank_name);
    $bankLogo = ($bankAccount->systemBank && $bankAccount->systemBank->bank_logo) ? $bankAccount->systemBank->bank_logo : null;
@endphp
<div class="sl-animate d1">
    <div class="sl-page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            @if($bankLogo)
            <div style="width:40px; height:40px; border-radius:10px; overflow:hidden; flex-shrink:0; background:#fff; border:1px solid rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center;">
                <img src="/{{ $bankLogo }}" alt="{{ $bankAccount->bank_name }}" style="width:100%; height:100%; object-fit:contain; padding:3px;">
            </div>
            @else
            <div style="width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg, rgba(245,158,11,0.15), rgba(245,158,11,0.05)); border:1px solid rgba(245,158,11,0.3); display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-file-import" style="color:#f59e0b; font-size:16px;"></i>
            </div>
            @endif
            <div>
                <h1 class="sl-page-title" style="margin:0;">Import Bank Statement | {{ $client->company_name }}</h1>
                <span class="sl-page-subtitle">{{ $bankAccount->bank_name }} - {{ $bankAccount->account_number }}</span>
            </div>
        </div>
        <div style="margin-left:auto;">
            <a href="{{ route('nxclients.show.accounting.bank.accounts', $client->id) }}" style="font-size:13px; color:var(--text-muted); text-decoration:none; font-weight:600;">
                <i class="fas fa-arrow-left"></i> Back to Bank Accounts
            </a>
        </div>
    </div>
</div>

@if(session('error'))
<div class="sl-animate d2" style="padding:12px 20px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:10px; color:var(--accent-red); font-size:14px; font-weight:600; margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif
@if(session('success'))
<div class="sl-animate d2" style="padding:12px 20px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.25); border-radius:10px; color:var(--accent-green); font-size:14px; font-weight:600; margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Bank Info --}}
<div class="sl-card sl-animate d2" style="margin-bottom:20px;">
    <div style="padding:18px 24px; display:flex; align-items:center; gap:16px;">
        @if($bankLogo)
        <div style="width:50px; height:50px; border-radius:12px; overflow:hidden; flex-shrink:0; background:#fff; border:1px solid rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center;">
            <img src="/{{ $bankLogo }}" alt="{{ $bankAccount->bank_name }}" style="width:100%; height:100%; object-fit:contain; padding:4px;">
        </div>
        @else
        <div style="width:50px; height:50px; border-radius:12px; background:linear-gradient(135deg, rgba(245,158,11,0.15), rgba(245,158,11,0.05)); border:1px solid rgba(245,158,11,0.25); display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-university" style="color:#f59e0b; font-size:20px;"></i>
        </div>
        @endif
        <div style="flex:1;">
            <div style="font-size:18px; font-weight:800; color:var(--text-primary);">{{ $bankAccount->bank_name }}</div>
            <div style="font-size:14px; font-weight:600; color:#f59e0b; font-family:var(--font-mono);">
                {{ $bankAccount->account_number }}
                @if($bankAccount->branch_code) &middot; {{ $bankAccount->branch_code }}@endif
            </div>
        </div>
    </div>
</div>

{{-- Import Card --}}
<div class="sl-card sl-animate d3">
    <div class="sl-card-header" style="display:flex; align-items:center; justify-content:space-between;">
        <div class="sl-card-title" style="color:#f59e0b;"><i class="fas fa-upload"></i> Upload Statement</div>
    </div>
    <div style="padding:24px;">

        {{-- Tabs --}}
        <div class="bi-tabs">
            <div class="bi-tab active" onclick="switchTab('pdf')"><i class="fas fa-file-pdf"></i> PDF Statement</div>
            <div class="bi-tab" onclick="switchTab('csv')"><i class="fas fa-file-csv"></i> CSV File</div>
        </div>

        {{-- PDF IMPORT PANEL --}}
        <div class="bi-panel active" id="panel-pdf">
            <div style="padding:12px 16px; background:rgba(59,130,246,0.06); border:1px solid rgba(59,130,246,0.15); border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:600; color:var(--accent-blue);">
                <i class="fas fa-info-circle"></i> Upload the PDF bank statement. Select the bank format and the system will extract all transactions.
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label class="bi-label"><i class="fas fa-university" style="margin-right:6px;color:#f59e0b;"></i> Bank Format</label>
                    <select id="pdfBankType" class="bi-select">
                        <option value="fnb" {{ str_contains($bankLower,'fnb')||str_contains($bankLower,'first national')?'selected':'' }}>FNB (First National Bank)</option>
                        <option value="nedbank" {{ str_contains($bankLower,'nedbank')?'selected':'' }}>Nedbank</option>
                        <option value="nedbank_online">Nedbank Online (Statement Enquiry)</option>
                        <option value="absa_transaction_history" {{ str_contains($bankLower,'absa')?'selected':'' }}>ABSA (Transaction History)</option>
                        <option value="absa_bank_statement">ABSA (Bank Statement)</option>
                        <option value="capitec" {{ str_contains($bankLower,'capitec')?'selected':'' }}>Capitec (Mercantile Bank)</option>
                        <option value="capitec_personal">Capitec (Personal Banking)</option>
                        <option value="capitec_business">Capitec (Business Banking)</option>
                        <option value="standard" {{ str_contains($bankLower,'standard')?'selected':'' }}>Standard Bank (Old Format)</option>
                        <option value="standard_new">Standard Bank (New Format)</option>
                    </select>
                </div>
                <div style="display:flex; align-items:flex-end; padding-bottom:6px;">
                    <div style="font-size:13px; color:var(--text-muted); font-weight:600;">
                        <i class="fas fa-check-circle" style="color:var(--accent-green); margin-right:6px;"></i>
                        Supported: FNB, Nedbank (2 types), ABSA (2 types), Capitec (3 types), Standard Bank (2 types)
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="bi-label"><i class="fas fa-file-pdf" style="margin-right:6px;color:var(--accent-red);"></i> PDF Bank Statement</label>
                <div class="bi-file-drop" id="pdfDrop" onclick="document.getElementById('pdfFile').click();">
                    <i class="fas fa-cloud-upload-alt" style="font-size:42px; color:rgba(245,158,11,0.3); margin-bottom:12px; display:block;"></i>
                    <p style="font-size:15px; color:var(--text-secondary); font-weight:700; margin:0;">Click to browse or drag and drop your PDF statement</p>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:6px;">Accepted format: .pdf</div>
                    <div id="pdfFileName" style="display:none; margin-top:12px; font-weight:800; color:var(--accent-green); font-size:14px;"></div>
                </div>
                <input type="file" id="pdfFile" accept=".pdf" style="display:none;" onchange="showPdfName(this)">
            </div>

            <div id="pdfPreview" style="display:none;"></div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:20px; border-top:1px solid var(--border-subtle); margin-top:12px;">
                <a href="{{ route('nxclients.show.accounting.bank.accounts', $client->id) }}" style="font-size:13px; color:var(--text-muted); text-decoration:none; font-weight:600;">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="neon-btn neon-btn-amber" onclick="parsePdf()" id="btnParse" style="display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-search"></i> Extract Transactions
                    </button>
                    <button type="button" class="neon-btn neon-btn-green" onclick="confirmImport()" id="btnConfirm" style="display:none; align-items:center; gap:8px;">
                        <i class="fas fa-file-import"></i> Import <span id="btnConfirmCount"></span> Transactions
                    </button>
                    <button type="button" class="neon-btn" onclick="showRawText()" id="btnRawText" style="display:none; align-items:center; gap:8px; background:rgba(255,165,0,0.15); border:1px solid rgba(255,165,0,0.4); color:#ffa500;">
                        <i class="fas fa-file-alt"></i> View Raw Extracted Text
                    </button>
                    {{-- FNB Fixer button (hidden - kept for emergency use) --}}
                    <button type="button" class="neon-btn" onclick="parsePdfFixer()" id="btnFixer" style="display:none; align-items:center; gap:8px; background:rgba(168,85,247,0.15); border:1px solid rgba(168,85,247,0.4); color:#a855f7;">
                        <i class="fas fa-wrench"></i> FNB Fixer
                    </button>
                </div>
            </div>
        </div>

        {{-- CSV IMPORT PANEL --}}
        <div class="bi-panel" id="panel-csv">
            <div style="padding:12px 16px; background:rgba(59,130,246,0.06); border:1px solid rgba(59,130,246,0.15); border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:600; color:var(--accent-blue);">
                <i class="fas fa-info-circle"></i> Upload a CSV file. Positive amounts = money in (credit), negative = money out (debit).
            </div>

            <form method="POST" action="/cims/accounts/banks/{{ $companyId }}/{{ $bankAccount->id }}/import" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:20px;">
                    <label class="bi-label"><i class="fas fa-file-csv" style="margin-right:6px;color:var(--accent-green);"></i> CSV File</label>
                    <div class="bi-file-drop" id="csvDrop" onclick="document.getElementById('csvFile').click();">
                        <i class="fas fa-cloud-upload-alt" style="font-size:42px; color:rgba(245,158,11,0.3); margin-bottom:12px; display:block;"></i>
                        <p style="font-size:15px; color:var(--text-secondary); font-weight:700; margin:0;">Click to browse or drag and drop your CSV file</p>
                        <div style="font-size:12px; color:var(--text-muted); margin-top:6px;">Accepted formats: .csv, .txt</div>
                        <div id="csvFileName" style="display:none; margin-top:12px; font-weight:800; color:var(--accent-green); font-size:14px;"></div>
                    </div>
                    <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" style="display:none;" onchange="showCsvName(this)">
                </div>

                <div style="background:rgba(255,255,255,0.02); border-radius:12px; padding:20px 24px; margin-bottom:20px; border:1px solid var(--border-subtle);">
                    <div style="font-size:13px; font-weight:800; color:var(--text-primary); margin-bottom:16px; text-transform:uppercase; letter-spacing:0.8px;">
                        <i class="fas fa-columns" style="margin-right:8px; color:#f59e0b;"></i> Column Mapping (0-based index)
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div>
                            <label class="bi-label">Date Column</label>
                            <input type="number" name="date_col" value="0" min="0" max="20" class="bi-input">
                        </div>
                        <div>
                            <label class="bi-label">Description Column</label>
                            <input type="number" name="desc_col" value="1" min="0" max="20" class="bi-input">
                        </div>
                        <div>
                            <label class="bi-label">Amount Column</label>
                            <input type="number" name="amount_col" value="2" min="0" max="20" class="bi-input">
                        </div>
                        <div>
                            <label class="bi-label">Balance Column (-1 if none)</label>
                            <input type="number" name="balance_col" value="-1" min="-1" max="20" class="bi-input">
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:20px; border-top:1px solid var(--border-subtle);">
                    <a href="{{ route('nxclients.show.accounting.bank.accounts', $client->id) }}" style="font-size:13px; color:var(--text-muted); text-decoration:none; font-weight:600;">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="neon-btn neon-btn-green" style="display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-file-import"></i> Import CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Processing Overlay --}}
<div id="overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:linear-gradient(145deg, #1e2235, #161928); border:1px solid rgba(245,158,11,0.15); border-radius:20px; padding:48px 56px; box-shadow:0 25px 80px rgba(0,0,0,0.6); text-align:center; min-width:360px;">
        <i class="fas fa-spinner fa-spin" style="font-size:48px; color:#f59e0b; display:block; margin-bottom:20px;"></i>
        <div style="font-size:18px; font-weight:800; color:#f1f5f9;">Processing Statement</div>
        <div style="font-size:14px; color:#94a3b8; margin-top:8px;" id="overlayStatus">Extracting text from PDF...</div>
    </div>
</div>

                {{-- ═══ END ORIGINAL CONTENT ═══ --}}

            </div>{{-- /nx-main-scroll --}}
        </div>{{-- /nx-main --}}

        @include('nx_formkit::components.nx-footer')
        @include('nx_formkit::components.nx-messages')

    </div>{{-- /nx-app-wrapper --}}

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- pdf.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
{{-- Tesseract.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

{{-- Inlined scripts from original bank import --}}
<script>
var NxAlert = {
    _logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB4PSIyIiB5PSIyIiB3aWR0aD0iMjgiIGhlaWdodD0iMjgiIHJ4PSI2IiBmaWxsPSIjMDU5NjY5Ii8+PHJlY3QgeD0iMzQiIHk9IjIiIHdpZHRoPSIyOCIgaGVpZ2h0PSIyOCIgcng9IjYiIGZpbGw9IiMyNTYzZWIiLz48cmVjdCB4PSIyIiB5PSIzNCIgd2lkdGg9IjI4IiBoZWlnaHQ9IjI4IiByeD0iNiIgZmlsbD0iI2Q5NzcwNiIvPjxyZWN0IHg9IjM0IiB5PSIzNCIgd2lkdGg9IjI4IiBoZWlnaHQ9IjI4IiByeD0iNiIgZmlsbD0iIzdjM2FlZCIvPjwvc3ZnPgo=',
    _colors: {
        success:  { btn: '#059669', pastel: '#d1fae5', cls: 'nx-swal-success' },
        warning:  { btn: '#e11d48', pastel: '#ffe4e6', cls: 'nx-swal-warning' },
        info:     { btn: '#2563eb', pastel: '#dbeafe', cls: 'nx-swal-info' },
        confirm:  { btn: '#0891b2', pastel: '#cffafe', cls: 'nx-swal-confirm' },
        error:    { btn: '#d97706', pastel: '#fef3c7', cls: 'nx-swal-error' }
    },
    _fire: function(type, title, message, opts) {
        var c = this._colors[type] || this._colors.info;
        var config = {
            imageUrl: this._logo,
            imageWidth: 56,
            imageHeight: 56,
            title: title,
            html: message,
            confirmButtonText: (opts && opts.confirmText) || 'OK',
            confirmButtonColor: c.btn,
            background: '#ffffff',
            showCancelButton: !!(opts && opts.showCancel),
            cancelButtonText: (opts && opts.cancelText) || 'CANCEL',
            customClass: {
                popup: 'nx-swal-popup ' + c.cls,
                title: 'nx-swal-title',
                htmlContainer: 'nx-swal-html',
                actions: 'nx-swal-actions'
            }
        };
        return Swal.fire(config);
    },
    success: function(title, message) { return this._fire('success', title, message); },
    warning: function(title, message) { return this._fire('warning', title, message); },
    info: function(title, message) { return this._fire('info', title, message); },
    error: function(title, message) { return this._fire('error', title, message); },
    confirm: function(title, message, confirmText) {
        return this._fire('confirm', title, message, { showCancel: true, confirmText: confirmText || 'YES, CONFIRM', cancelText: 'CANCEL' });
    }
};
</script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

var parsedTransactions = null;
var parsedHeader = null;
var parsedSummary = null;
var parsedRawText = '';
var pdfFileRef = null;
var glAccountCode = '{{ $bankAccount->glAccount ? $bankAccount->glAccount->account_code : "" }}';

function switchTab(tab) {
    document.querySelectorAll('.bi-tab').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.bi-panel').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('panel-' + tab).classList.add('active');
    event.target.closest('.bi-tab').classList.add('active');
}

function showPdfName(input) {
    var el = document.getElementById('pdfFileName');
    if (input.files.length > 0) { el.textContent = input.files[0].name; el.style.display = 'block'; pdfFileRef = input.files[0]; }
}
function showCsvName(input) {
    var el = document.getElementById('csvFileName');
    if (input.files.length > 0) { el.textContent = input.files[0].name; el.style.display = 'block'; }
}

var pdfDrop = document.getElementById('pdfDrop');
pdfDrop.addEventListener('dragover', function(e) { e.preventDefault(); pdfDrop.classList.add('dragover'); });
pdfDrop.addEventListener('dragleave', function() { pdfDrop.classList.remove('dragover'); });
pdfDrop.addEventListener('drop', function(e) {
    e.preventDefault(); pdfDrop.classList.remove('dragover');
    var input = document.getElementById('pdfFile');
    input.files = e.dataTransfer.files;
    showPdfName(input);
});

var csvDrop = document.getElementById('csvDrop');
if (csvDrop) {
    csvDrop.addEventListener('dragover', function(e) { e.preventDefault(); csvDrop.classList.add('dragover'); });
    csvDrop.addEventListener('dragleave', function() { csvDrop.classList.remove('dragover'); });
    csvDrop.addEventListener('drop', function(e) {
        e.preventDefault(); csvDrop.classList.remove('dragover');
        var input = document.getElementById('csvFile');
        input.files = e.dataTransfer.files;
        showCsvName(input);
    });
}

async function parsePdf() {
    if (!pdfFileRef) { NxAlert.warning('No File Selected', 'Please select a PDF file first.'); return; }
    var bankType = document.getElementById('pdfBankType').value;
    var overlay = document.getElementById('overlay');
    var status = document.getElementById('overlayStatus');
    overlay.style.display = 'flex';
    status.textContent = 'Extracting text from PDF...';

    try {
        var arrayBuffer = await pdfFileRef.arrayBuffer();
        var pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        var pages = [];
        var ocrPages = [];
        var needsOcr = (bankType === 'fnb' || bankType === 'absa_bank_statement' || bankType === 'standard_new');

        for (var i = 1; i <= pdf.numPages; i++) {
            status.textContent = 'Reading page ' + i + ' of ' + pdf.numPages + '...';
            var page = await pdf.getPage(i);
            var textContent = await page.getTextContent();
            var items = textContent.items;
            var lineMap = {};

            items.forEach(function(item) {
                var y = Math.round(item.transform[5] / 2) * 2;
                if (!lineMap[y]) lineMap[y] = [];
                lineMap[y].push({ text: item.str, x: item.transform[4], width: item.width });
            });

            var sortedYs = Object.keys(lineMap).sort(function(a, b) { return b - a; });
            var pageText = '';
            sortedYs.forEach(function(y) {
                var lineItems = lineMap[y].sort(function(a, b) { return a.x - b.x; });
                var lineStr = '';
                var lastX = 0;
                lineItems.forEach(function(item, idx) {
                    if (idx > 0) {
                        var gap = item.x - lastX;
                        if (gap > 15) lineStr += '  ';
                        else if (gap > 3) lineStr += ' ';
                    }
                    lineStr += item.text;
                    lastX = item.x + (item.width || 0);
                });
                pageText += lineStr + '\n';
            });
            pages.push(pageText);

            if (needsOcr) {
                status.textContent = 'Scanning page ' + i + ' for image text (OCR)...';
                var scale = (bankType === 'absa_bank_statement') ? 4 : 2;
                var viewport = page.getViewport({ scale: scale });
                var canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                var ctx = canvas.getContext('2d');
                await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                var ocrResult = await Tesseract.recognize(canvas, 'eng', { logger: function(m) { if (m.status === 'recognizing text') status.textContent = 'OCR page ' + i + ': ' + Math.round(m.progress * 100) + '%'; } });
                ocrPages.push(ocrResult.data.text);
                canvas.remove();
            }
        }

        status.textContent = 'Parsing ' + bankType.toUpperCase() + ' statement format...';

        var postBody = { pages: pages, bank_type: bankType };
        if (ocrPages.length > 0) postBody.ocr_pages = ocrPages;

        var response = await fetch('{{ route("nexcore.clients.show.accounting.bank.parse-pdf", [$client->id, $bankAccount->id]) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(postBody)
        });

        var result = await response.json();
        overlay.style.display = 'none';

        if (result.error) { NxAlert.error('Extraction Error', result.error); return; }
        if (!result.transactions || result.transactions.length === 0) { NxAlert.info('No Transactions Found', 'No transactions found in the PDF. Please check the bank format selection.'); return; }

        parsedTransactions = result.transactions;
        parsedHeader = result.header || {};
        parsedSummary = result.summary || {};
        parsedRawText = result.raw_text || '';
        console.log('Parser version:', result._parser_version || 'OLD CODE');
        renderPreview(result);
        document.getElementById('btnConfirm').style.display = 'inline-flex';
        document.getElementById('btnConfirmCount').textContent = result.transactions.length;
        if (parsedRawText) {
            document.getElementById('btnRawText').style.display = 'inline-flex';
        }

    } catch (err) {
        overlay.style.display = 'none';
        NxAlert.error('Processing Error', 'Error processing PDF: ' + err.message);
    }
}

async function parsePdfFixer() {
    if (!pdfFileRef) { NxAlert.warning('No File Selected', 'Please select a PDF file first.'); return; }
    var overlay = document.getElementById('overlay');
    var status = document.getElementById('overlayStatus');
    overlay.style.display = 'flex';
    status.textContent = 'FNB FIXER: Extracting text from PDF...';

    try {
        var arrayBuffer = await pdfFileRef.arrayBuffer();
        var pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        var pages = [];
        var ocrPages = [];

        for (var i = 1; i <= pdf.numPages; i++) {
            status.textContent = 'FNB FIXER: Reading page ' + i + ' of ' + pdf.numPages + '...';
            var page = await pdf.getPage(i);
            var textContent = await page.getTextContent();
            var items = textContent.items;
            var lineMap = {};

            items.forEach(function(item) {
                var y = Math.round(item.transform[5] / 2) * 2;
                if (!lineMap[y]) lineMap[y] = [];
                lineMap[y].push({ text: item.str, x: item.transform[4], width: item.width });
            });

            var sortedYs = Object.keys(lineMap).sort(function(a, b) { return b - a; });
            var pageText = '';
            sortedYs.forEach(function(y) {
                var lineItems = lineMap[y].sort(function(a, b) { return a.x - b.x; });
                var lineStr = '';
                var lastX = 0;
                lineItems.forEach(function(item, idx) {
                    if (idx > 0) {
                        var gap = item.x - lastX;
                        if (gap > 15) lineStr += '  ';
                        else if (gap > 3) lineStr += ' ';
                    }
                    lineStr += item.text;
                    lastX = item.x + (item.width || 0);
                });
                pageText += lineStr + '\n';
            });
            pages.push(pageText);

            status.textContent = 'FNB FIXER: Scanning page ' + i + ' for image text (OCR)...';
            var scale = 2;
            var viewport = page.getViewport({ scale: scale });
            var canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            var ctx = canvas.getContext('2d');
            await page.render({ canvasContext: ctx, viewport: viewport }).promise;
            var ocrResult = await Tesseract.recognize(canvas, 'eng', { logger: function(m) { if (m.status === 'recognizing text') status.textContent = 'FNB FIXER OCR page ' + i + ': ' + Math.round(m.progress * 100) + '%'; } });
            ocrPages.push(ocrResult.data.text);
            canvas.remove();
        }

        status.textContent = 'FNB FIXER: Parsing with ORIGINAL CIMS code...';

        var postBody = { pages: pages };
        if (ocrPages.length > 0) postBody.ocr_pages = ocrPages;

        var response = await fetch('{{ route("nexcore.clients.show.accounting.bank.parse-pdf-fixer", [$client->id, $bankAccount->id]) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(postBody)
        });

        var result = await response.json();
        overlay.style.display = 'none';

        if (result.error) { NxAlert.error('FNB Fixer Error', result.error); return; }
        if (!result.transactions || result.transactions.length === 0) { NxAlert.info('No Transactions Found', 'FNB Fixer found no transactions in the PDF.'); return; }

        parsedTransactions = result.transactions;
        parsedHeader = result.header || {};
        parsedSummary = result.summary || {};
        parsedRawText = '';
        console.log('FNB FIXER parser version:', result._parser_version || 'UNKNOWN');
        renderPreview(result);
        document.getElementById('btnConfirm').style.display = 'inline-flex';
        document.getElementById('btnConfirmCount').textContent = result.transactions.length;
        document.getElementById('btnRawText').style.display = 'none';

    } catch (err) {
        overlay.style.display = 'none';
        NxAlert.error('FNB Fixer Error', 'Error processing PDF: ' + err.message);
    }
}

function showRawText() {
    if (!parsedRawText) return;
    var w = window.open('', '_blank', 'width=1000,height=700,scrollbars=yes');
    w.document.write('<html><head><title>Raw Extracted Text</title></head><body style="background:#0a0e1a;color:#e0e0e0;font-family:Consolas,monospace;font-size:12px;padding:20px;white-space:pre-wrap;word-wrap:break-word;">' + parsedRawText.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</body></html>');
    w.document.close();
}

function buildStatementRef(h) {
    var glCode = glAccountCode || '';
    var glLast4 = glCode.length >= 4 ? glCode.slice(-4) : ('0000' + glCode).slice(-4);
    var stmtNum = h.statement_number || '###';
    var periodPart = '';
    if (h.period_to) {
        var d = new Date(h.period_to);
        var months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
        periodPart = months[d.getMonth()] + d.getFullYear();
    } else {
        var now = new Date();
        var months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
        periodPart = months[now.getMonth()] + now.getFullYear();
    }
    return 'BS' + glLast4 + '/' + stmtNum + '/' + periodPart;
}

function renderPreview(result) {
    var s = result.summary;
    var h = result.header || {};
    var html = '<div style="font-size:15px;font-weight:800;color:var(--text-primary);margin-bottom:14px;display:flex;align-items:center;gap:8px;"><i class="fas fa-check-circle" style="color:var(--accent-green);font-size:18px;"></i> Transactions Extracted</div>';

    if (h.account_number || h.account_holder) {
        html += '<div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:12px 18px;margin-bottom:14px;font-size:13px;font-weight:600;color:var(--text-secondary);display:flex;align-items:center;flex-wrap:wrap;gap:6px;border:1px solid var(--border-subtle);">';
        if (h.account_holder) html += '<strong style="color:var(--text-primary);font-size:14px;">' + h.account_holder + '</strong> <span style="color:var(--text-muted);">&middot;</span> ';
        if (h.account_number) html += 'Acc: <span style="font-weight:800;color:#f59e0b;font-size:14px;">' + h.account_number + '</span>';
        if (h.statement_period) html += ' <span style="color:var(--text-muted);">&middot;</span> Period: <span style="color:var(--text-primary);">' + h.statement_period + '</span>';
        html += '</div>';
    }

    html += '<div class="bi-preview-stats">';
    html += '<div class="bi-preview-stat">Transactions: <span>' + s.transaction_count + '</span></div>';
    var obVal = h.opening_balance ? parseFloat(h.opening_balance).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2}) : '0.00';
    html += '<div class="bi-preview-stat">Opening Bal: <span style="color:#f59e0b;font-weight:900;">R ' + obVal + '</span></div>';
    html += '<div class="bi-preview-stat">Credits: <span class="credit">' + s.credit_count + ' (R ' + s.total_credits.toFixed(2) + ')</span></div>';
    html += '<div class="bi-preview-stat">Debits: <span class="debit">' + s.debit_count + ' (R ' + s.total_debits.toFixed(2) + ')</span></div>';
    var cbVal = h.closing_balance ? parseFloat(h.closing_balance).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2}) : '0.00';
    html += '<div class="bi-preview-stat">Closing Bal: <span style="color:#f59e0b;font-weight:900;">R ' + cbVal + '</span></div>';
    html += '<div class="bi-preview-stat">Balance Match: <span style="color:' + (s.balance_match ? 'var(--accent-green)' : 'var(--accent-red)') + ';font-weight:900;">' + (s.balance_match ? 'Yes' : 'No') + '</span></div>';
    if (h.statement_number) {
        html += '<div class="bi-preview-stat">Stmt No: <span style="color:var(--accent-cyan,#22d3ee);font-weight:900;">' + h.statement_number + '</span></div>';
    }
    var stmtRef = buildStatementRef(h);
    if (stmtRef) {
        html += '<div class="bi-preview-stat">Stmt Ref: <span style="color:var(--accent-cyan,#22d3ee);font-weight:900;">' + stmtRef + '</span></div>';
    }
    html += '</div>';

    html += '<div style="max-height:420px;overflow-y:auto;border:1px solid var(--border-subtle);border-radius:10px;">';
    html += '<table class="bi-preview-table"><thead><tr><th style="width:40px;">#</th><th style="width:130px;white-space:nowrap;">Date</th><th>Description</th><th style="text-align:right;width:130px;">Amount</th><th style="text-align:right;width:130px;">Balance</th><th style="width:50px;text-align:center;"></th></tr></thead><tbody>';

    result.transactions.forEach(function(txn, idx) {
        var cls = txn.amount >= 0 ? 'credit' : 'debit';
        var sign = txn.amount >= 0 ? '+' : '';
        var fmtAmt = sign + Math.abs(txn.amount).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2});
        var fmtBal = txn.balance ? Math.abs(txn.balance).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2}) : '-';
        html += '<tr id="txn-row-' + idx + '"><td>' + (idx + 1) + '</td><td style="white-space:nowrap;">' + txn.date + '</td>';
        html += '<td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;" title="' + txn.description.replace(/"/g, '&quot;') + '">' + txn.description + '</td>';
        html += '<td style="text-align:right;" class="' + cls + '">' + fmtAmt + '</td>';
        html += '<td style="text-align:right;">' + fmtBal + '</td>';
        html += '<td style="text-align:center;"><button type="button" onclick="deleteTransaction(' + idx + ')" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:6px;color:var(--accent-red);cursor:pointer;padding:4px 8px;font-size:12px;transition:all 0.2s;" onmouseover="this.style.background=\'rgba(239,68,68,0.25)\'" onmouseout="this.style.background=\'rgba(239,68,68,0.1)\'"><i class="fas fa-trash-alt"></i></button></td>';
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    document.getElementById('pdfPreview').innerHTML = html;
    document.getElementById('pdfPreview').style.display = 'block';
}

function deleteTransaction(idx) {
    if (!parsedTransactions || idx < 0 || idx >= parsedTransactions.length) return;
    var txn = parsedTransactions[idx];
    var desc = txn.description || '(no description)';
    var amt = txn.amount >= 0 ? '+' : '';
    amt += Math.abs(txn.amount).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2});

    NxAlert.confirm(
        'Delete Transaction',
        'Are you sure you want to remove this transaction?\n\nLine ' + (idx + 1) + ': ' + txn.date + ' - ' + desc + ' (R ' + amt + ')\n\nThis will remove it from the import list. It has NOT been saved to the database yet.',
        'YES, DELETE'
    ).then(function(result) {
        if (!result.isConfirmed) return;

        parsedTransactions.splice(idx, 1);

        if (parsedTransactions.length === 0) {
            document.getElementById('pdfPreview').innerHTML = '';
            document.getElementById('pdfPreview').style.display = 'none';
            document.getElementById('btnConfirm').style.display = 'none';
            NxAlert.info('All Removed', 'All transactions have been removed from the import list.');
            return;
        }

        var newSummary = recalcSummary();
        parsedSummary = newSummary;

        renderPreview({
            header: parsedHeader,
            transactions: parsedTransactions,
            summary: newSummary
        });

        document.getElementById('btnConfirmCount').textContent = parsedTransactions.length;
        NxAlert.success('Deleted', 'Transaction removed. ' + parsedTransactions.length + ' transactions remaining.');
    });
}

function recalcSummary() {
    var totalCredits = 0, totalDebits = 0, creditCount = 0, debitCount = 0;
    parsedTransactions.forEach(function(txn) {
        var amt = parseFloat(txn.amount) || 0;
        if (amt >= 0) { totalCredits += amt; creditCount++; }
        else { totalDebits += Math.abs(amt); debitCount++; }
    });
    var openBal = parsedHeader.opening_balance ? parseFloat(parsedHeader.opening_balance) : 0;
    var closeBal = parsedHeader.closing_balance ? parseFloat(parsedHeader.closing_balance) : 0;
    var calcClosing = Math.round((openBal + totalCredits - totalDebits) * 100) / 100;
    var balMatch = Math.abs(calcClosing - closeBal) < 0.02;
    return {
        transaction_count: parsedTransactions.length,
        credit_count: creditCount,
        debit_count: debitCount,
        total_credits: Math.round(totalCredits * 100) / 100,
        total_debits: Math.round(totalDebits * 100) / 100,
        opening_balance: openBal,
        closing_balance: closeBal,
        calculated_closing: calcClosing,
        balance_match: balMatch
    };
}

async function confirmImport() {
    if (!parsedTransactions || parsedTransactions.length === 0) { NxAlert.warning('No Transactions', 'No transactions to import.'); return; }

    NxAlert.confirm(
        'Confirm Import',
        'You are about to import ' + parsedTransactions.length + ' transactions into ' + '{{ $bankAccount->bank_name }}' + ' ({{ $bankAccount->account_number }}). Continue?',
        'YES, IMPORT'
    ).then(function(result) {
        if (!result.isConfirmed) return;

        var overlay = document.getElementById('overlay');
        var status = document.getElementById('overlayStatus');
        overlay.style.display = 'flex';
        status.textContent = 'Saving ' + parsedTransactions.length + ' transactions...';

        fetch('{{ route("nexcore.clients.show.accounting.bank.import-save", [$client->id, $bankAccount->id]) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ transactions: parsedTransactions, header: parsedHeader, summary: parsedSummary, filename: pdfFileRef ? pdfFileRef.name : '' })
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            overlay.style.display = 'none';
            if (result.error || result.message && !result.success) { NxAlert.error('Import Failed', result.message || result.error || 'Unknown error'); return; }
            NxAlert.success('Import Complete', result.count + ' transactions imported successfully into {{ $bankAccount->bank_name }} ({{ $bankAccount->account_number }}).');
            setTimeout(function() {
                window.location.href = '{{ route("nexcore.clients.show.accounting.bank.accounts", $client->id) }}';
            }, 2000);
        })
        .catch(function(err) {
            overlay.style.display = 'none';
            NxAlert.error('Save Error', 'Error saving: ' + err.message);
        });
    });
}
</script>

</body>
</html>
