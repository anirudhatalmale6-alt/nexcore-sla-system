@extends('layouts.default')

@section('title', 'Management Accounts')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* ================================================================
       MANAGEMENT ACCOUNTS - PREMIUM TEMPLATE
       Dr F B Jeewa (Pty) Ltd
       Design: Big 4 Accounting Standard
       ================================================================ */

    :root {
        --ma-primary: #1a5276;
        --ma-primary-light: #2471a3;
        --ma-primary-dark: #0e2f44;
        --ma-accent: #14b8a6;
        --ma-accent-light: #5eead4;
        --ma-accent-dark: #0d9488;
        --ma-text: #1e293b;
        --ma-text-muted: #64748b;
        --ma-text-light: #94a3b8;
        --ma-border: #e2e8f0;
        --ma-border-light: #f1f5f9;
        --ma-bg: #ffffff;
        --ma-bg-alt: #f8fafc;
        --ma-bg-section: #fafbfd;
        --ma-green: #059669;
        --ma-green-bg: #ecfdf5;
        --ma-red: #dc2626;
        --ma-red-bg: #fef2f2;
        --ma-yellow: #d97706;
        --ma-yellow-bg: #fffbeb;
        --ma-blue: #2563eb;
        --ma-blue-bg: #eff6ff;
    }

    /* ----------------------------------------------------------------
       BASE STYLES
       ---------------------------------------------------------------- */
    .ma-wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--ma-text);
        background: var(--ma-bg);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .ma-wrapper * {
        box-sizing: border-box;
    }

    .ma-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .ma-section {
        padding: 48px 0 32px;
    }

    .ma-section + .ma-section {
        border-top: 1px solid var(--ma-border);
    }

    /* Section header with number */
    .ma-section-header {
        display: flex;
        align-items: baseline;
        gap: 20px;
        margin-bottom: 36px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--ma-primary);
    }

    .ma-section-number {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 42px;
        font-weight: 700;
        color: var(--ma-primary);
        line-height: 1;
        opacity: 0.2;
        min-width: 60px;
    }

    .ma-section-title-group {
        flex: 1;
    }

    .ma-section-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--ma-primary);
        margin: 0 0 2px;
        letter-spacing: -0.3px;
    }

    .ma-section-subtitle {
        font-size: 13px;
        color: var(--ma-text-muted);
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    /* ----------------------------------------------------------------
       COVER PAGE
       ---------------------------------------------------------------- */
    .ma-cover {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
        background: linear-gradient(160deg, var(--ma-primary-dark) 0%, var(--ma-primary) 40%, var(--ma-primary-light) 100%);
        color: #fff;
        padding: 60px 40px;
        margin: -20px -15px 0;
    }

    .ma-cover::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(circle at 20% 80%, rgba(20, 184, 166, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(20, 184, 166, 0.1) 0%, transparent 40%);
        pointer-events: none;
    }

    .ma-cover-content {
        position: relative;
        z-index: 1;
        max-width: 700px;
    }

    .ma-cover-divider {
        width: 80px;
        height: 3px;
        background: var(--ma-accent);
        margin: 0 auto 40px;
    }

    .ma-cover-company {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 40px;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 8px;
        line-height: 1.2;
    }

    .ma-cover-trading {
        font-size: 16px;
        font-weight: 400;
        opacity: 0.7;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 48px;
    }

    .ma-cover-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 28px;
        font-weight: 600;
        letter-spacing: 6px;
        text-transform: uppercase;
        margin-bottom: 12px;
        color: var(--ma-accent-light);
    }

    .ma-cover-period {
        font-size: 18px;
        font-weight: 300;
        opacity: 0.85;
        margin-bottom: 60px;
        letter-spacing: 1px;
    }

    .ma-cover-meta {
        font-size: 13px;
        opacity: 0.6;
        line-height: 2;
    }

    .ma-cover-meta strong {
        font-weight: 500;
        opacity: 1;
    }

    .ma-cover-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.2);
        padding: 20px 40px;
        text-align: center;
        z-index: 1;
    }

    .ma-cover-footer-firm {
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .ma-cover-footer-address {
        font-size: 11px;
        opacity: 0.5;
        margin-bottom: 4px;
    }

    .ma-cover-footer-services {
        font-size: 11px;
        opacity: 0.4;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* ----------------------------------------------------------------
       PERIOD SELECTOR (screen only)
       ---------------------------------------------------------------- */
    .ma-period-selector {
        background: var(--ma-bg-alt);
        border: 1px solid var(--ma-border);
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ma-period-selector label {
        font-size: 13px;
        font-weight: 600;
        color: var(--ma-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .ma-period-selector select,
    .ma-period-selector input {
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        padding: 8px 12px;
        border: 1px solid var(--ma-border);
        border-radius: 6px;
        background: #fff;
        color: var(--ma-text);
        outline: none;
        transition: border-color 0.2s;
    }

    .ma-period-selector select:focus,
    .ma-period-selector input:focus {
        border-color: var(--ma-primary);
        box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.1);
    }

    .ma-period-selector select {
        min-width: 180px;
    }

    .ma-custom-dates {
        display: none;
        align-items: center;
        gap: 10px;
    }

    .ma-custom-dates.active {
        display: flex;
    }

    .ma-custom-dates input {
        width: 150px;
    }

    .ma-custom-dates span {
        color: var(--ma-text-muted);
        font-size: 13px;
    }

    .ma-btn-load {
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 24px;
        background: var(--ma-primary);
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: background 0.2s, transform 0.1s;
    }

    .ma-btn-load:hover {
        background: var(--ma-primary-light);
    }

    .ma-btn-load:active {
        transform: scale(0.98);
    }

    /* ----------------------------------------------------------------
       TABLES
       ---------------------------------------------------------------- */
    .ma-table-container {
        overflow-x: auto;
        margin-bottom: 24px;
    }

    .ma-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .ma-table thead th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--ma-text-muted);
        padding: 12px 16px;
        border-bottom: 2px solid var(--ma-primary);
        text-align: right;
        white-space: nowrap;
    }

    .ma-table thead th:first-child {
        text-align: left;
    }

    .ma-table tbody td {
        padding: 10px 16px;
        border-bottom: 1px solid var(--ma-border-light);
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .ma-table tbody td:first-child {
        text-align: left;
        font-weight: 500;
        color: var(--ma-text);
    }

    .ma-table tbody tr:nth-child(even) {
        background: var(--ma-bg-alt);
    }

    .ma-table tbody tr:hover {
        background: rgba(26, 82, 118, 0.03);
    }

    /* Group header row */
    .ma-table .ma-group-header td {
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--ma-primary);
        padding-top: 18px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--ma-border);
        background: transparent;
    }

    /* Sub-item row (indented) */
    .ma-table .ma-sub-item td:first-child {
        padding-left: 32px;
        font-weight: 400;
        color: var(--ma-text);
    }

    .ma-table .ma-sub-item td:first-child .ma-account-code {
        font-size: 10px;
        color: var(--ma-text-light);
        margin-left: 6px;
    }

    /* Subtotal row */
    .ma-table .ma-subtotal td {
        font-weight: 600;
        border-top: 1px solid var(--ma-border);
        border-bottom: 2px solid var(--ma-border);
        padding-top: 12px;
        padding-bottom: 12px;
        background: transparent;
    }

    /* Grand total / highlight row */
    .ma-table .ma-total-highlight td {
        font-weight: 700;
        font-size: 14px;
        background: var(--ma-primary);
        color: #fff;
        border: none;
        padding: 14px 16px;
    }

    .ma-table .ma-total-highlight td .ma-variance-positive,
    .ma-table .ma-total-highlight td .ma-variance-negative {
        color: #fff;
    }

    .ma-table .ma-total-highlight td .ma-variance-positive {
        opacity: 0.85;
    }

    .ma-table .ma-total-highlight td .ma-variance-negative {
        opacity: 0.85;
    }

    /* Gross profit highlight */
    .ma-table .ma-gross-profit td {
        font-weight: 700;
        font-size: 14px;
        background: var(--ma-accent);
        color: #fff;
        border: none;
        padding: 14px 16px;
    }

    .ma-table .ma-gross-profit td .ma-variance-positive,
    .ma-table .ma-gross-profit td .ma-variance-negative {
        color: #fff;
        opacity: 0.85;
    }

    /* Variance colors */
    .ma-variance-positive {
        color: var(--ma-green);
        font-weight: 600;
    }

    .ma-variance-negative {
        color: var(--ma-red);
        font-weight: 600;
    }

    /* ----------------------------------------------------------------
       RATIO CARDS
       ---------------------------------------------------------------- */
    .ma-ratios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 24px;
    }

    .ma-ratio-card {
        background: var(--ma-bg);
        border: 1px solid var(--ma-border);
        border-radius: 10px;
        padding: 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .ma-ratio-card:hover {
        box-shadow: 0 4px 16px rgba(26, 82, 118, 0.1);
    }

    .ma-ratio-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--ma-primary);
    }

    .ma-ratio-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--ma-text-muted);
        margin-bottom: 12px;
    }

    .ma-ratio-value {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 36px;
        font-weight: 700;
        color: var(--ma-primary);
        line-height: 1;
        margin-bottom: 8px;
    }

    .ma-ratio-comparison {
        font-size: 12px;
        color: var(--ma-text-muted);
        margin-bottom: 12px;
    }

    .ma-ratio-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
    }

    .ma-ratio-trend.up {
        color: var(--ma-green);
        background: var(--ma-green-bg);
    }

    .ma-ratio-trend.down {
        color: var(--ma-red);
        background: var(--ma-red-bg);
    }

    .ma-ratio-trend.neutral {
        color: var(--ma-text-muted);
        background: var(--ma-bg-alt);
    }

    .ma-trend-arrow {
        font-size: 10px;
    }

    /* ----------------------------------------------------------------
       TRANSACTION DRILL-DOWN
       ---------------------------------------------------------------- */
    .ma-sub-item.ma-expandable { cursor: pointer; }
    .ma-sub-item.ma-expandable:hover { background: #f0f9ff; }
    .ma-sub-item.ma-expandable td:first-child::before {
        content: '\f0da';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: 10px;
        color: var(--ma-primary-light);
        margin-right: 8px;
        transition: transform 0.2s;
        display: inline-block;
    }
    .ma-sub-item.ma-expandable.ma-expanded td:first-child::before {
        transform: rotate(90deg);
    }
    .ma-drilldown-row td { padding: 0 !important; border: none !important; }
    .ma-drilldown-panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease;
        background: linear-gradient(135deg, #fff5f0 0%, #fef0e7 100%);
        border-left: 3px solid #f59e6c;
        margin: 0 12px 0 24px;
        border-radius: 0 0 8px 8px;
    }
    .ma-drilldown-panel.open {
        max-height: 2000px;
        overflow: visible;
    }
    .ma-drilldown-panel table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
        margin: 0;
    }
    .ma-drilldown-panel table th {
        background: #f7d9c4;
        color: #7c3a10;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 12px;
        text-align: left;
        border-bottom: 1px solid #f0c5a8;
    }
    .ma-drilldown-panel table td {
        padding: 6px 12px !important;
        border-bottom: 1px solid #fde8d8 !important;
        color: #5a3e2b;
        font-weight: 500;
    }
    .ma-drilldown-panel table tr:last-child td { border-bottom: none !important; }
    .ma-drilldown-panel table tr:hover td { background: #fce8d5; }
    .ma-realloc-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border: 1px solid #e5a07a; border-radius: 4px;
        background: #fff5f0; color: #b45309; font-size: 10px; font-weight: 600;
        cursor: pointer; transition: all 0.15s; white-space: nowrap;
    }
    .ma-realloc-btn:hover { background: #fde8d5; border-color: #b45309; }
    .ma-realloc-btn i { font-size: 9px; }
    .ma-hide-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border: 1px solid #d1d5db; border-radius: 4px;
        background: #f9fafb; color: #6b7280; font-size: 10px; font-weight: 600;
        cursor: pointer; transition: all 0.15s; white-space: nowrap;
    }
    .ma-hide-btn:hover { background: #fee2e2; border-color: #ef4444; color: #dc2626; }
    .ma-hide-btn i { font-size: 9px; }
    .ma-unhide-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border: 1px solid #86efac; border-radius: 4px;
        background: #f0fdf4; color: #16a34a; font-size: 10px; font-weight: 600;
        cursor: pointer; transition: all 0.15s; white-space: nowrap;
    }
    .ma-unhide-btn:hover { background: #dcfce7; border-color: #16a34a; }
    .ma-unhide-btn i { font-size: 9px; }
    .ma-hidden-row td { opacity: 0.45; text-decoration: line-through; }
    .ma-hidden-row td:last-child { opacity: 1; text-decoration: none; }
    .ma-drill-toolbar {
        display: flex; align-items: center; gap: 12px; padding: 6px 12px;
        background: #fef6f0; border-bottom: 1px solid #f5d5c0; font-size: 11px;
    }
    .ma-drill-toolbar label { display: flex; align-items: center; gap: 4px; cursor: pointer; color: #92400e; font-weight: 600; }
    .ma-realloc-wrap { position: relative; display: inline-block; width: 100%; }
    .ma-realloc-search {
        width: 100%; padding: 6px 10px; border: 2px solid #f59e6c; border-radius: 6px;
        font-size: 11px; font-weight: 600; color: #5a3e2b; background: #fff;
        outline: none;
    }
    .ma-realloc-search:focus { border-color: #b45309; box-shadow: 0 0 0 3px rgba(245,158,108,0.2); }
    .ma-realloc-list {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 999;
        max-height: 200px; overflow-y: auto; background: #fff;
        border: 1px solid #f0c5a8; border-top: none; border-radius: 0 0 6px 6px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .ma-realloc-list-item {
        padding: 7px 10px; font-size: 11px; cursor: pointer;
        border-bottom: 1px solid #fef0e7; color: #5a3e2b;
    }
    .ma-realloc-list-item:hover { background: #fff5f0; }
    .ma-realloc-list-item .ma-rl-code { color: #b45309; font-weight: 700; margin-right: 6px; }
    .ma-realloc-list-item .ma-rl-vat { color: #9ca3af; font-size: 10px; margin-left: 4px; }
    .ma-realloc-spinner { color: #b45309; font-size: 11px; padding: 4px 0; }
    .ma-realloc-done { color: #059669; font-weight: 700; font-size: 11px; }
    .ma-realloc-fail { color: #dc2626; font-weight: 600; font-size: 11px; }
    .ma-drilldown-loading {
        padding: 16px;
        text-align: center;
        color: #b45309;
        font-size: 12px;
        font-weight: 600;
    }
    .ma-drilldown-empty {
        padding: 14px;
        text-align: center;
        color: #9ca3af;
        font-size: 12px;
        font-style: italic;
    }

    /* ----------------------------------------------------------------
       BANK RECON STATUS BADGES
       ---------------------------------------------------------------- */
    .ma-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ma-badge-completed {
        background: var(--ma-green-bg);
        color: var(--ma-green);
    }

    .ma-badge-in_progress {
        background: var(--ma-blue-bg);
        color: var(--ma-blue);
    }

    .ma-badge-draft {
        background: var(--ma-yellow-bg);
        color: var(--ma-yellow);
    }

    /* ----------------------------------------------------------------
       BALANCE SHEET CHECK
       ---------------------------------------------------------------- */
    .ma-balance-check {
        margin-top: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ma-balance-check.balanced {
        background: var(--ma-green-bg);
        color: var(--ma-green);
        border: 1px solid rgba(5, 150, 105, 0.2);
    }

    .ma-balance-check.unbalanced {
        background: var(--ma-red-bg);
        color: var(--ma-red);
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .ma-check-icon {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        color: #fff;
    }

    .ma-balance-check.balanced .ma-check-icon {
        background: var(--ma-green);
    }

    .ma-balance-check.unbalanced .ma-check-icon {
        background: var(--ma-red);
    }

    /* ----------------------------------------------------------------
       PRINT STYLES
       ---------------------------------------------------------------- */
    @media print {
        @page {
            size: A4;
            margin: 15mm 18mm;

            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-family: 'Inter', sans-serif;
                font-size: 9px;
                color: #94a3b8;
            }
        }

        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .ma-wrapper {
            font-size: 11px;
        }

        .ma-period-selector {
            display: none !important;
        }

        .ma-cover {
            min-height: auto;
            height: 100vh;
            margin: -15mm -18mm;
            padding: 40px;
            page-break-after: always;
        }

        .ma-section {
            page-break-before: always;
            padding-top: 0;
            border-top: none;
        }

        .ma-section:first-of-type {
            page-break-before: auto;
        }

        .ma-table {
            font-size: 10px;
        }

        .ma-table thead th {
            font-size: 9px;
        }

        .ma-table tbody td {
            padding: 6px 10px;
        }

        .ma-table .ma-total-highlight td {
            background: var(--ma-primary) !important;
            color: #fff !important;
        }

        .ma-table .ma-gross-profit td {
            background: var(--ma-accent) !important;
            color: #fff !important;
        }

        .ma-ratio-card {
            break-inside: avoid;
        }

        .ma-ratio-card::before {
            background: var(--ma-primary) !important;
        }

        .ma-section-header {
            border-bottom-color: var(--ma-primary) !important;
        }

        .ma-section-number {
            color: var(--ma-primary) !important;
        }

        .ma-badge {
            border: 1px solid currentColor;
        }

        .ma-page {
            max-width: none;
            padding: 0;
        }

        .ma-table tbody tr:nth-child(even) {
            background: #f8fafc !important;
        }

        .ma-cover-footer {
            background: rgba(0, 0, 0, 0.2) !important;
        }

        a[href]:after {
            content: none !important;
        }
    }

    /* ----------------------------------------------------------------
       RESPONSIVE
       ---------------------------------------------------------------- */
    @media (max-width: 768px) {
        .ma-cover-company {
            font-size: 28px;
        }

        .ma-cover-title {
            font-size: 20px;
            letter-spacing: 3px;
        }

        .ma-ratios-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .ma-period-selector {
            flex-direction: column;
            align-items: stretch;
        }

        .ma-custom-dates {
            flex-direction: column;
        }

        .ma-custom-dates input {
            width: 100%;
        }

    }
</style>
@endpush

@section('content')
<div class="ma-wrapper">

    {{-- ============================================================
         SECTION 1: COVER PAGE
         ============================================================ --}}
    <div class="ma-cover">
        <div class="ma-cover-content">
            <div class="ma-cover-divider"></div>
            <div class="ma-cover-company">{{ $company->company_name ?? 'Dr F B Jeewa (Pty) Ltd' }}</div>
            @if(!empty($company->trading_name))
                <div class="ma-cover-trading">Trading as {{ $company->trading_name }}</div>
            @else
                <div class="ma-cover-trading">&nbsp;</div>
            @endif

            <div class="ma-cover-title">Management Accounts</div>
            <div class="ma-cover-period">
                For the period {{ $dateFrom }} to {{ $dateTo }}
            </div>

            <div class="ma-cover-meta">
                <div>Prepared by: <strong>{{ $companySettings['settings_company_name'] ?? 'Accounting Taxation and Payroll (Pty) Ltd' }}</strong></div>
                <div>Date: <strong>{{ $preparedDate }}</strong></div>
            </div>
        </div>

        <div class="ma-cover-footer">
            <div class="ma-cover-footer-firm">{{ $companySettings['settings_company_name'] ?? 'Accounting Taxation and Payroll (Pty) Ltd' }}</div>
            <div class="ma-cover-footer-address">{{ $companySettings['settings_address'] ?? '' }}</div>
            <div class="ma-cover-footer-services">Accounting | Taxation | Payroll | Advisory</div>
        </div>
    </div>

    <div class="ma-page">

        {{-- ============================================================
             SECTION 2: PERIOD SELECTOR (Screen Only)
             ============================================================ --}}
        <div class="ma-period-selector" id="maPeriodSelector">
            <label for="maPeriodDropdown">Period</label>
            <select id="maPeriodDropdown">
                <option value="this_month" {{ ($period ?? '') == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ ($period ?? '') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="q1" {{ ($period ?? '') == 'q1' ? 'selected' : '' }}>Q1 (Mar - May)</option>
                <option value="q2" {{ ($period ?? '') == 'q2' ? 'selected' : '' }}>Q2 (Jun - Aug)</option>
                <option value="q3" {{ ($period ?? '') == 'q3' ? 'selected' : '' }}>Q3 (Sep - Nov)</option>
                <option value="q4" {{ ($period ?? '') == 'q4' ? 'selected' : '' }}>Q4 (Dec - Feb)</option>
                <option value="h1" {{ ($period ?? '') == 'h1' ? 'selected' : '' }}>H1 (Mar - Aug)</option>
                <option value="h2" {{ ($period ?? '') == 'h2' ? 'selected' : '' }}>H2 (Sep - Feb)</option>
                <option value="full_year" {{ ($period ?? '') == 'full_year' ? 'selected' : '' }}>Full Year</option>
                <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>

            <div class="ma-custom-dates {{ ($period ?? '') == 'custom' ? 'active' : '' }}" id="maCustomDates">
                <input type="text" id="maDateFrom" placeholder="From date" data-iso="{{ $dateFrom ?? '' }}">
                <span>to</span>
                <input type="text" id="maDateTo" placeholder="To date" data-iso="{{ $dateTo ?? '' }}">
            </div>

            <button type="button" class="ma-btn-load" id="maLoadBtn">Load</button>
        </div>

        {{-- ============================================================
             SECTION 3: INCOME STATEMENT
             ============================================================ --}}
        <div class="ma-section" id="incomeStatement">
            <div class="ma-section-header">
                <div class="ma-section-number">01</div>
                <div class="ma-section-title-group">
                    <h2 class="ma-section-title">Income Statement</h2>
                    <div class="ma-section-subtitle">{{ $period ?? '' }} &mdash; {{ $compPeriodLabel ?? 'Prior Period' }} Comparison</div>
                </div>
            </div>

            <div class="ma-table-container">
                <table class="ma-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Account</th>
                            <th>Current Period</th>
                            <th>{{ $compPeriodLabel ?? 'Prior Period' }}</th>
                            <th>Variance (R)</th>
                            <th>Variance (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- REVENUE --}}
                        @foreach($revenueByGroup as $groupName => $items)
                            @php $activeItems = collect($items)->filter(fn($i) => round($i['current'], 2) != 0 || round($i['comparison'], 2) != 0); @endphp
                            @if($activeItems->isNotEmpty())
                            <tr class="ma-group-header">
                                <td colspan="5">{{ $groupName }}</td>
                            </tr>
                            @foreach($activeItems as $item)
                                <tr class="ma-sub-item ma-expandable" data-account-id="{{ $item['id'] }}" data-type="pnl">
                                    <td>
                                        {{ $item['name'] }}
                                        <span class="ma-account-code">{{ $item['code'] }}</span>
                                    </td>
                                    <td>R {{ number_format($item['current'], 2, '.', ' ') }}</td>
                                    <td>R {{ number_format($item['comparison'], 2, '.', ' ') }}</td>
                                    <td class="{{ $item['variance'] >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                        R {{ number_format($item['variance'], 2, '.', ' ') }}
                                    </td>
                                    <td class="{{ $item['variance_pct'] >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                        {{ number_format($item['variance_pct'], 1) }}%
                                    </td>
                                </tr>
                                <tr class="ma-drilldown-row" id="drill-{{ $item['id'] }}" style="display:none;">
                                    <td colspan="5"><div class="ma-drilldown-panel" id="panel-{{ $item['id'] }}"></div></td>
                                </tr>
                            @endforeach
                            @endif
                        @endforeach

                        {{-- TOTAL REVENUE --}}
                        <tr class="ma-subtotal">
                            <td>Total Revenue</td>
                            <td>R {{ number_format($totalRevenue->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalRevenue->comparison, 2, '.', ' ') }}</td>
                            <td class="{{ $totalRevenue->variance >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                R {{ number_format($totalRevenue->variance, 2, '.', ' ') }}
                            </td>
                            <td class="{{ $totalRevenue->variance_pct >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                {{ number_format($totalRevenue->variance_pct, 1) }}%
                            </td>
                        </tr>

                        {{-- COST OF SALES --}}
                        @foreach($cosByGroup as $groupName => $items)
                            @php $activeItems = collect($items)->filter(fn($i) => round($i['current'], 2) != 0 || round($i['comparison'], 2) != 0); @endphp
                            @if($activeItems->isNotEmpty())
                            <tr class="ma-group-header">
                                <td colspan="5">{{ $groupName }}</td>
                            </tr>
                            @foreach($activeItems as $item)
                                <tr class="ma-sub-item ma-expandable" data-account-id="{{ $item['id'] }}" data-type="pnl">
                                    <td>
                                        {{ $item['name'] }}
                                        <span class="ma-account-code">{{ $item['code'] }}</span>
                                    </td>
                                    <td>R {{ number_format($item['current'], 2, '.', ' ') }}</td>
                                    <td>R {{ number_format($item['comparison'], 2, '.', ' ') }}</td>
                                    <td class="{{ $item['variance'] >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                        R {{ number_format($item['variance'], 2, '.', ' ') }}
                                    </td>
                                    <td class="{{ $item['variance_pct'] >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                        {{ number_format($item['variance_pct'], 1) }}%
                                    </td>
                                </tr>
                                <tr class="ma-drilldown-row" id="drill-{{ $item['id'] }}" style="display:none;">
                                    <td colspan="5"><div class="ma-drilldown-panel" id="panel-{{ $item['id'] }}"></div></td>
                                </tr>
                            @endforeach
                            @endif
                        @endforeach

                        {{-- TOTAL COST OF SALES --}}
                        <tr class="ma-subtotal">
                            <td>Total Cost of Sales</td>
                            <td>R {{ number_format($totalCos->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalCos->comparison, 2, '.', ' ') }}</td>
                            <td class="{{ $totalCos->variance <= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                R {{ number_format($totalCos->variance, 2, '.', ' ') }}
                            </td>
                            <td class="{{ $totalCos->variance_pct <= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                {{ number_format($totalCos->variance_pct, 1) }}%
                            </td>
                        </tr>

                        {{-- GROSS PROFIT --}}
                        <tr class="ma-gross-profit">
                            <td>Gross Profit</td>
                            <td>R {{ number_format($grossProfit->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($grossProfit->comparison, 2, '.', ' ') }}</td>
                            <td class="{{ $grossProfit->variance >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                R {{ number_format($grossProfit->variance, 2, '.', ' ') }}
                            </td>
                            <td class="{{ $grossProfit->variance_pct >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                {{ number_format($grossProfit->variance_pct, 1) }}%
                            </td>
                        </tr>

                        {{-- OPERATING EXPENSES --}}
                        @foreach($expensesByGroup as $groupName => $items)
                            @php $activeItems = collect($items)->filter(fn($i) => round($i['current'], 2) != 0 || round($i['comparison'], 2) != 0); @endphp
                            @if($activeItems->isNotEmpty())
                            <tr class="ma-group-header">
                                <td colspan="5">{{ $groupName }}</td>
                            </tr>
                            @foreach($activeItems as $item)
                                <tr class="ma-sub-item ma-expandable" data-account-id="{{ $item['id'] }}" data-type="pnl">
                                    <td>
                                        {{ $item['name'] }}
                                        <span class="ma-account-code">{{ $item['code'] }}</span>
                                    </td>
                                    <td>R {{ number_format($item['current'], 2, '.', ' ') }}</td>
                                    <td>R {{ number_format($item['comparison'], 2, '.', ' ') }}</td>
                                    <td class="{{ $item['variance'] <= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                        R {{ number_format($item['variance'], 2, '.', ' ') }}
                                    </td>
                                    <td class="{{ $item['variance_pct'] <= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                        {{ number_format($item['variance_pct'], 1) }}%
                                    </td>
                                </tr>
                                <tr class="ma-drilldown-row" id="drill-{{ $item['id'] }}" style="display:none;">
                                    <td colspan="5"><div class="ma-drilldown-panel" id="panel-{{ $item['id'] }}"></div></td>
                                </tr>
                            @endforeach
                            @endif
                        @endforeach

                        {{-- TOTAL EXPENSES --}}
                        <tr class="ma-subtotal">
                            <td>Total Operating Expenses</td>
                            <td>R {{ number_format($totalExpenses->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalExpenses->comparison, 2, '.', ' ') }}</td>
                            <td class="{{ $totalExpenses->variance <= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                R {{ number_format($totalExpenses->variance, 2, '.', ' ') }}
                            </td>
                            <td class="{{ $totalExpenses->variance_pct <= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                {{ number_format($totalExpenses->variance_pct, 1) }}%
                            </td>
                        </tr>

                        {{-- NET PROFIT --}}
                        <tr class="ma-total-highlight">
                            <td>Net Profit / (Loss)</td>
                            <td>R {{ number_format($netProfit->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($netProfit->comparison, 2, '.', ' ') }}</td>
                            <td class="{{ $netProfit->variance >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                R {{ number_format($netProfit->variance, 2, '.', ' ') }}
                            </td>
                            <td class="{{ $netProfit->variance_pct >= 0 ? 'ma-variance-positive' : 'ma-variance-negative' }}">
                                {{ number_format($netProfit->variance_pct, 1) }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================================
             SECTION 4: BALANCE SHEET
             ============================================================ --}}
        <div class="ma-section" id="balanceSheet">
            <div class="ma-section-header">
                <div class="ma-section-number">02</div>
                <div class="ma-section-title-group">
                    <h2 class="ma-section-title">Balance Sheet</h2>
                    <div class="ma-section-subtitle">Statement of Financial Position as at {{ $dateTo }}</div>
                </div>
            </div>

            <div class="ma-table-container">
                <table class="ma-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Account</th>
                            <th>Current Period</th>
                            <th>{{ $compPeriodLabel ?? 'Prior Period' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ASSETS --}}
                        <tr class="ma-group-header">
                            <td colspan="3">Assets</td>
                        </tr>
                        @foreach($assetsByGroup as $groupName => $items)
                            @php $activeItems = collect($items)->filter(fn($i) => round($i['balance'], 2) != 0 || round($i['comp_balance'], 2) != 0); @endphp
                            @if($activeItems->isNotEmpty())
                            <tr class="ma-group-header">
                                <td colspan="3" style="font-size: 11px; padding-top: 10px;">{{ $groupName }}</td>
                            </tr>
                            @foreach($activeItems as $item)
                                <tr class="ma-sub-item ma-expandable" data-account-id="{{ $item['id'] }}" data-type="bs">
                                    <td>
                                        {{ $item['name'] }}
                                        <span class="ma-account-code">{{ $item['code'] }}</span>
                                    </td>
                                    <td>R {{ number_format($item['balance'], 2, '.', ' ') }}</td>
                                    <td>R {{ number_format($item['comp_balance'], 2, '.', ' ') }}</td>
                                </tr>
                                <tr class="ma-drilldown-row" id="drill-bs-{{ $item['id'] }}" style="display:none;">
                                    <td colspan="3"><div class="ma-drilldown-panel" id="panel-bs-{{ $item['id'] }}"></div></td>
                                </tr>
                            @endforeach
                            @endif
                        @endforeach
                        <tr class="ma-subtotal">
                            <td>Total Assets</td>
                            <td>R {{ number_format($totalAssets->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalAssets->comparison, 2, '.', ' ') }}</td>
                        </tr>

                        {{-- LIABILITIES --}}
                        <tr class="ma-group-header">
                            <td colspan="3">Liabilities</td>
                        </tr>
                        @foreach($liabilitiesByGroup as $groupName => $items)
                            @php $activeItems = collect($items)->filter(fn($i) => round($i['balance'], 2) != 0 || round($i['comp_balance'], 2) != 0); @endphp
                            @if($activeItems->isNotEmpty())
                            <tr class="ma-group-header">
                                <td colspan="3" style="font-size: 11px; padding-top: 10px;">{{ $groupName }}</td>
                            </tr>
                            @foreach($activeItems as $item)
                                <tr class="ma-sub-item ma-expandable" data-account-id="{{ $item['id'] }}" data-type="bs">
                                    <td>
                                        {{ $item['name'] }}
                                        <span class="ma-account-code">{{ $item['code'] }}</span>
                                    </td>
                                    <td>R {{ number_format($item['balance'], 2, '.', ' ') }}</td>
                                    <td>R {{ number_format($item['comp_balance'], 2, '.', ' ') }}</td>
                                </tr>
                                <tr class="ma-drilldown-row" id="drill-bs-{{ $item['id'] }}" style="display:none;">
                                    <td colspan="3"><div class="ma-drilldown-panel" id="panel-bs-{{ $item['id'] }}"></div></td>
                                </tr>
                            @endforeach
                            @endif
                        @endforeach
                        <tr class="ma-subtotal">
                            <td>Total Liabilities</td>
                            <td>R {{ number_format($totalLiabilities->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalLiabilities->comparison, 2, '.', ' ') }}</td>
                        </tr>

                        {{-- EQUITY --}}
                        <tr class="ma-group-header">
                            <td colspan="3">Equity</td>
                        </tr>
                        @foreach($equityByGroup as $groupName => $items)
                            @php $activeItems = collect($items)->filter(fn($i) => round($i['balance'], 2) != 0 || round($i['comp_balance'], 2) != 0); @endphp
                            @if($activeItems->isNotEmpty())
                            <tr class="ma-group-header">
                                <td colspan="3" style="font-size: 11px; padding-top: 10px;">{{ $groupName }}</td>
                            </tr>
                            @foreach($activeItems as $item)
                                <tr class="ma-sub-item ma-expandable" data-account-id="{{ $item['id'] }}" data-type="bs">
                                    <td>
                                        {{ $item['name'] }}
                                        <span class="ma-account-code">{{ $item['code'] }}</span>
                                    </td>
                                    <td>R {{ number_format($item['balance'], 2, '.', ' ') }}</td>
                                    <td>R {{ number_format($item['comp_balance'], 2, '.', ' ') }}</td>
                                </tr>
                                <tr class="ma-drilldown-row" id="drill-bs-{{ $item['id'] }}" style="display:none;">
                                    <td colspan="3"><div class="ma-drilldown-panel" id="panel-bs-{{ $item['id'] }}"></div></td>
                                </tr>
                            @endforeach
                            @endif
                        @endforeach
                        <tr class="ma-sub-item">
                            <td><strong>Retained Earnings</strong></td>
                            <td>R {{ number_format($retainedEarnings->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($retainedEarnings->comparison, 2, '.', ' ') }}</td>
                        </tr>
                        <tr class="ma-subtotal">
                            <td>Total Equity</td>
                            <td>R {{ number_format($totalEquity->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalEquity->comparison, 2, '.', ' ') }}</td>
                        </tr>

                        {{-- TOTAL LIABILITIES AND EQUITY --}}
                        <tr class="ma-total-highlight">
                            <td>Total Liabilities &amp; Equity</td>
                            <td>R {{ number_format($totalLiabilitiesAndEquity->current, 2, '.', ' ') }}</td>
                            <td>R {{ number_format($totalLiabilitiesAndEquity->comparison, 2, '.', ' ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Balance check --}}
            @php
                $isBalanced = abs($totalAssets->current - $totalLiabilitiesAndEquity->current) < 0.01;
            @endphp
            <div class="ma-balance-check {{ $isBalanced ? 'balanced' : 'unbalanced' }}">
                <div class="ma-check-icon">
                    @if($isBalanced)
                        &#10003;
                    @else
                        &#10007;
                    @endif
                </div>
                @if($isBalanced)
                    Balance Sheet is in balance. Assets equal Liabilities plus Equity.
                @else
                    Balance Sheet is out of balance by R {{ number_format(abs($totalAssets->current - $totalLiabilitiesAndEquity->current), 2, '.', ' ') }}. Please review entries.
                @endif
            </div>
        </div>

        {{-- ============================================================
             SECTION 5: KEY FINANCIAL RATIOS
             ============================================================ --}}
        <div class="ma-section" id="financialRatios">
            <div class="ma-section-header">
                <div class="ma-section-number">03</div>
                <div class="ma-section-title-group">
                    <h2 class="ma-section-title">Key Financial Ratios</h2>
                    <div class="ma-section-subtitle">Performance Indicators &mdash; Current vs {{ $compPeriodLabel ?? 'Prior Period' }}</div>
                </div>
            </div>

            <div class="ma-ratios-grid">
                {{-- Gross Profit Margin --}}
                @php
                    $gpCurrent = $ratios['gross_margin'] ?? 0;
                    $gpPrior = $compRatios['gross_margin'] ?? 0;
                    $gpDelta = $gpCurrent - $gpPrior;
                @endphp
                <div class="ma-ratio-card">
                    <div class="ma-ratio-label">Gross Profit Margin</div>
                    <div class="ma-ratio-value">{{ number_format($gpCurrent, 1) }}%</div>
                    <div class="ma-ratio-comparison">Prior: {{ number_format($gpPrior, 1) }}%</div>
                    <div class="ma-ratio-trend {{ $gpDelta > 0.01 ? 'up' : ($gpDelta < -0.01 ? 'down' : 'neutral') }}">
                        <span class="ma-trend-arrow">
                            @if($gpDelta > 0.01) &#9650; @elseif($gpDelta < -0.01) &#9660; @else &#9644; @endif
                        </span>
                        {{ $gpDelta >= 0 ? '+' : '' }}{{ number_format($gpDelta, 1) }}pp
                    </div>
                </div>

                {{-- Net Profit Margin --}}
                @php
                    $npCurrent = $ratios['net_margin'] ?? 0;
                    $npPrior = $compRatios['net_margin'] ?? 0;
                    $npDelta = $npCurrent - $npPrior;
                @endphp
                <div class="ma-ratio-card">
                    <div class="ma-ratio-label">Net Profit Margin</div>
                    <div class="ma-ratio-value">{{ number_format($npCurrent, 1) }}%</div>
                    <div class="ma-ratio-comparison">Prior: {{ number_format($npPrior, 1) }}%</div>
                    <div class="ma-ratio-trend {{ $npDelta > 0.01 ? 'up' : ($npDelta < -0.01 ? 'down' : 'neutral') }}">
                        <span class="ma-trend-arrow">
                            @if($npDelta > 0.01) &#9650; @elseif($npDelta < -0.01) &#9660; @else &#9644; @endif
                        </span>
                        {{ $npDelta >= 0 ? '+' : '' }}{{ number_format($npDelta, 1) }}pp
                    </div>
                </div>

                {{-- Current Ratio --}}
                @php
                    $crCurrent = $ratios['current_ratio'] ?? 0;
                    $crPrior = $compRatios['current_ratio'] ?? 0;
                    $crDelta = $crCurrent - $crPrior;
                @endphp
                <div class="ma-ratio-card">
                    <div class="ma-ratio-label">Current Ratio</div>
                    <div class="ma-ratio-value">{{ number_format($crCurrent, 2) }}</div>
                    <div class="ma-ratio-comparison">Prior: {{ number_format($crPrior, 2) }}</div>
                    <div class="ma-ratio-trend {{ $crDelta > 0.01 ? 'up' : ($crDelta < -0.01 ? 'down' : 'neutral') }}">
                        <span class="ma-trend-arrow">
                            @if($crDelta > 0.01) &#9650; @elseif($crDelta < -0.01) &#9660; @else &#9644; @endif
                        </span>
                        {{ $crDelta >= 0 ? '+' : '' }}{{ number_format($crDelta, 2) }}
                    </div>
                </div>

                {{-- Debt-to-Equity --}}
                @php
                    $deCurrent = $ratios['debt_to_equity'] ?? 0;
                    $dePrior = $compRatios['debt_to_equity'] ?? 0;
                    $deDelta = $deCurrent - $dePrior;
                @endphp
                <div class="ma-ratio-card">
                    <div class="ma-ratio-label">Debt-to-Equity</div>
                    <div class="ma-ratio-value">{{ number_format($deCurrent, 2) }}</div>
                    <div class="ma-ratio-comparison">Prior: {{ number_format($dePrior, 2) }}</div>
                    {{-- Lower D/E is generally better, so flip the arrow logic --}}
                    <div class="ma-ratio-trend {{ $deDelta < -0.01 ? 'up' : ($deDelta > 0.01 ? 'down' : 'neutral') }}">
                        <span class="ma-trend-arrow">
                            @if($deDelta < -0.01) &#9650; @elseif($deDelta > 0.01) &#9660; @else &#9644; @endif
                        </span>
                        {{ $deDelta >= 0 ? '+' : '' }}{{ number_format($deDelta, 2) }}
                    </div>
                </div>

                {{-- Expense Ratio --}}
                @php
                    $erCurrent = $ratios['expense_ratio'] ?? 0;
                    $erPrior = $compRatios['expense_ratio'] ?? 0;
                    $erDelta = $erCurrent - $erPrior;
                @endphp
                <div class="ma-ratio-card">
                    <div class="ma-ratio-label">Expense Ratio</div>
                    <div class="ma-ratio-value">{{ number_format($erCurrent, 1) }}%</div>
                    <div class="ma-ratio-comparison">Prior: {{ number_format($erPrior, 1) }}%</div>
                    {{-- Lower expense ratio is better, so flip the arrow logic --}}
                    <div class="ma-ratio-trend {{ $erDelta < -0.01 ? 'up' : ($erDelta > 0.01 ? 'down' : 'neutral') }}">
                        <span class="ma-trend-arrow">
                            @if($erDelta < -0.01) &#9650; @elseif($erDelta > 0.01) &#9660; @else &#9644; @endif
                        </span>
                        {{ $erDelta >= 0 ? '+' : '' }}{{ number_format($erDelta, 1) }}pp
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             SECTION 6: BANK RECONCILIATION STATUS
             ============================================================ --}}
        <div class="ma-section" id="bankRecon">
            <div class="ma-section-header">
                <div class="ma-section-number">04</div>
                <div class="ma-section-title-group">
                    <h2 class="ma-section-title">Bank Reconciliation Status</h2>
                    <div class="ma-section-subtitle">Latest Reconciliation Summary</div>
                </div>
            </div>

            <div class="ma-table-container">
                <table class="ma-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Bank Account</th>
                            <th>Account Number</th>
                            <th>Statement Date</th>
                            <th>Status</th>
                            <th>Statement Balance</th>
                            <th>Reconciled Balance</th>
                            <th>Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankRecons as $recon)
                            <tr>
                                <td>{{ $recon->bank_name }}</td>
                                <td style="font-variant-numeric: tabular-nums;">{{ $recon->account_number }}</td>
                                <td>{{ $recon->statement_date }}</td>
                                <td>
                                    @php
                                        $statusClass = 'ma-badge-' . ($recon->status ?? 'draft');
                                        $statusLabel = str_replace('_', ' ', ucfirst($recon->status ?? 'Draft'));
                                    @endphp
                                    <span class="ma-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td>R {{ number_format($recon->statement_balance, 2, '.', ' ') }}</td>
                                <td>R {{ number_format($recon->reconciled_balance, 2, '.', ' ') }}</td>
                                <td class="{{ abs($recon->difference) > 0.01 ? 'ma-variance-negative' : 'ma-variance-positive' }}">
                                    R {{ number_format($recon->difference, 2, '.', ' ') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--ma-text-muted); padding: 24px;">
                                    No bank reconciliations available for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /.ma-page --}}
</div>{{-- /.ma-wrapper --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function() {
    'use strict';

    /**
     * Convert a flatpickr-formatted date string (e.g. "7 May 2026") to ISO "YYYY-MM-DD".
     * Falls back to returning the original string if parsing fails.
     */
    function formatDateISO(dateStr) {
        if (!dateStr) return '';
        var parsed = new Date(dateStr);
        if (isNaN(parsed.getTime())) return dateStr;
        var y = parsed.getFullYear();
        var m = String(parsed.getMonth() + 1).padStart(2, '0');
        var d = String(parsed.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    // Initialise flatpickr on the custom date inputs
    var fpConfig = {
        dateFormat: 'j M Y',
        allowInput: true
    };

    var fpFrom = flatpickr('#maDateFrom', fpConfig);
    var fpTo   = flatpickr('#maDateTo', fpConfig);

    var isoFrom = document.getElementById('maDateFrom').getAttribute('data-iso');
    var isoTo   = document.getElementById('maDateTo').getAttribute('data-iso');
    if (isoFrom) fpFrom.setDate(new Date(isoFrom + 'T00:00:00'), true);
    if (isoTo)   fpTo.setDate(new Date(isoTo + 'T00:00:00'), true);

    // Toggle custom date fields based on dropdown selection
    var dropdown    = document.getElementById('maPeriodDropdown');
    var customDates = document.getElementById('maCustomDates');

    function toggleCustomDates() {
        if (dropdown.value === 'custom') {
            customDates.classList.add('active');
        } else {
            customDates.classList.remove('active');
        }
    }

    dropdown.addEventListener('change', toggleCustomDates);
    toggleCustomDates();

    // Load button: redirect with query params
    var loadBtn = document.getElementById('maLoadBtn');
    loadBtn.addEventListener('click', function() {
        var period = dropdown.value;
        var url    = window.location.pathname;
        var params = '?period=' + encodeURIComponent(period);

        if (period === 'custom') {
            var dateFrom = formatDateISO(document.getElementById('maDateFrom').value);
            var dateTo   = formatDateISO(document.getElementById('maDateTo').value);
            if (!dateFrom || !dateTo) {
                alert('Please select both a start and end date.');
                return;
            }
            params += '&date_from=' + encodeURIComponent(dateFrom)
                    + '&date_to='   + encodeURIComponent(dateTo);
        }

        window.location.href = url + params;
    });

    var companyId = {{ $company->id }};
    var dateFrom = '{{ $dateFrom }}';
    var dateTo = '{{ $dateTo }}';
    var drillCache = {};
    var chartAccounts = null;

    function loadChart(cb) {
        if (chartAccounts) return cb(chartAccounts);
        fetch('/cims/accounts/api/chart-tree/' + companyId, { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                var flat = [];
                var tree = resp.tree || {};
                Object.keys(tree).forEach(function(k1) {
                    var l1 = tree[k1];
                    if (l1.children) {
                        Object.keys(l1.children).forEach(function(k2) {
                            var l2 = l1.children[k2];
                            if (l2.children) {
                                l2.children.forEach(function(l3) {
                                    flat.push({ id: l3.id, code: l3.code, name: l3.name, vat: l3.vat_type || 'none' });
                                });
                            }
                        });
                    }
                });
                chartAccounts = flat;
                cb(flat);
            });
    }

    function fmtR(v) {
        return v > 0 ? 'R ' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') : '-';
    }

    function renderDrillTable(data, accountName, panel, cacheKey) {
        if (data.length === 0) {
            panel.innerHTML = '<div class="ma-drilldown-empty">No transactions found for this period.</div>';
            return;
        }
        var hiddenCount = data.filter(function(t) { return t.ma_hidden; }).length;
        var toolbarHtml = '';
        if (hiddenCount > 0) {
            toolbarHtml = '<div class="ma-drill-toolbar"><label><input type="checkbox" class="ma-show-hidden-cb"> Show ' + hiddenCount + ' hidden transaction' + (hiddenCount > 1 ? 's' : '') + '</label></div>';
        }
        var html = toolbarHtml + '<table><thead><tr><th>Date</th><th>Jnl</th><th>Description</th><th style="text-align:right;">Debit</th><th style="text-align:right;">Credit</th><th style="width:120px;"></th></tr></thead><tbody>';
        var totalDebit = 0, totalCredit = 0;
        var visibleCount = 0;
        data.forEach(function(t, idx) {
            var dr = parseFloat(t.debit) || 0;
            var cr = parseFloat(t.credit) || 0;
            var isHidden = t.ma_hidden ? true : false;
            var rowClass = isHidden ? 'ma-hidden-row' : '';
            var rowStyle = isHidden ? ' style="display:none;"' : '';
            if (!isHidden) { totalDebit += dr; totalCredit += cr; visibleCount++; }
            html += '<tr id="txn-' + t.journal_id + '-' + idx + '" class="' + rowClass + '" data-hidden="' + (isHidden ? '1' : '0') + '"' + rowStyle + '>';
            var descHtml = (t.description || '');
            if (t.note) { descHtml += '<div style="font-size:10px;color:#6b7280;font-style:italic;margin-top:2px;">' + t.note + '</div>'; }
            html += '<td>' + (t.date || '') + '</td><td style="font-size:10px;color:#b45309;">' + (t.journal || '') + '</td><td>' + descHtml + '</td>';
            html += '<td style="text-align:right;">' + fmtR(dr) + '</td>';
            html += '<td style="text-align:right;">' + fmtR(cr) + '</td>';
            html += '<td style="white-space:nowrap;">';
            html += '<button class="ma-realloc-btn" onclick="startRealloc(' + t.journal_id + ',' + idx + ',this)" title="Reallocate"><i class="fas fa-pencil-alt"></i> Move</button> ';
            if (isHidden) {
                html += '<button class="ma-unhide-btn" onclick="toggleHideLine(' + t.line_id + ',0,this)" title="Unhide"><i class="fas fa-eye"></i></button>';
            } else {
                html += '<button class="ma-hide-btn" onclick="toggleHideLine(' + t.line_id + ',1,this)" title="Hide"><i class="fas fa-eye-slash"></i></button>';
            }
            html += '</td></tr>';
        });
        html += '<tr class="ma-drill-total" style="font-weight:700;background:#f7d9c4;"><td colspan="3">Total (' + visibleCount + ' transactions)</td>';
        html += '<td style="text-align:right;">' + fmtR(totalDebit) + '</td>';
        html += '<td style="text-align:right;">' + fmtR(totalCredit) + '</td><td></td></tr>';
        html += '</tbody></table>';
        panel.innerHTML = html;

        var cb = panel.querySelector('.ma-show-hidden-cb');
        if (cb) {
            cb.addEventListener('change', function() {
                var show = this.checked;
                panel.querySelectorAll('.ma-hidden-row').forEach(function(r) {
                    r.style.display = show ? '' : 'none';
                });
            });
        }
    }

    window.startRealloc = function(journalId, idx, btn) {
        var txnRow = document.getElementById('txn-' + journalId + '-' + idx);
        if (!txnRow) return;
        var existing = document.getElementById('realloc-row-' + journalId + '-' + idx);
        if (existing) { existing.remove(); return; }

        var searchRow = document.createElement('tr');
        searchRow.id = 'realloc-row-' + journalId + '-' + idx;
        searchRow.innerHTML = '<td colspan="6" style="padding:8px 12px !important;border:none !important;background:#fff5f0;">' +
            '<div class="ma-realloc-wrap" style="max-width:400px;">' +
            '<input type="text" class="ma-realloc-search" placeholder="Type to search account..." autofocus>' +
            '<div class="ma-realloc-list" style="display:none;"></div>' +
            '<div id="realloc-status-' + journalId + '-' + idx + '" style="margin-top:4px;"></div>' +
            '</div></td>';
        txnRow.parentNode.insertBefore(searchRow, txnRow.nextSibling);

        loadChart(function(accounts) {
            var input = searchRow.querySelector('.ma-realloc-search');
            var list = searchRow.querySelector('.ma-realloc-list');
            var status = document.getElementById('realloc-status-' + journalId + '-' + idx);
            input.focus();

            function renderList(q) {
                var filtered = accounts.filter(function(a) {
                    var s = (a.code + ' ' + a.name).toLowerCase();
                    return s.indexOf(q.toLowerCase()) !== -1;
                }).slice(0, 15);
                if (filtered.length === 0) {
                    list.innerHTML = '<div class="ma-realloc-list-item" style="color:#9ca3af;cursor:default;">No matches</div>';
                } else {
                    list.innerHTML = filtered.map(function(a) {
                        return '<div class="ma-realloc-list-item" data-id="' + a.id + '" data-vat="' + a.vat + '" data-name="' + a.name + '">' +
                            a.name + '</div>';
                    }).join('');
                }
                list.style.display = '';
            }

            renderList('');

            input.addEventListener('input', function() {
                renderList(this.value.trim());
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') { searchRow.remove(); }
            });

            list.addEventListener('click', function(e) {
                var item = e.target.closest('.ma-realloc-list-item');
                if (!item || !item.dataset.id) return;
                var newAccId = item.dataset.id;
                var newVat = item.dataset.vat;
                var newName = item.dataset.name;

                list.style.display = 'none';
                input.style.display = 'none';
                status.innerHTML = '<span class="ma-realloc-spinner"><i class="fas fa-spinner fa-spin"></i> Reallocating to ' + newName + '...</span>';

                fetch('/cims/accounts/api/ma-realloc/' + companyId, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ journal_id: journalId, new_account_id: parseInt(newAccId) })
                })
                .then(function(r) { return r.json(); })
                .then(function(resp) {
                    if (resp.success) {
                        status.innerHTML = '<span class="ma-realloc-done"><i class="fas fa-check"></i> ' + resp.journal_number + ': ' + resp.description + ' (R' + Number(resp.amount).toFixed(2) + ') - Refreshing...</span>';
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        status.innerHTML = '<span class="ma-realloc-fail"><i class="fas fa-times"></i> ' + (resp.error || 'Failed') + '</span>';
                    }
                })
                .catch(function() {
                    status.innerHTML = '<span class="ma-realloc-fail"><i class="fas fa-times"></i> Error</span>';
                });
            });
        });
    };

    window.toggleHideLine = function(lineId, hide, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        fetch('/cims/accounts/api/ma-hide/' + companyId, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ line_id: lineId, hide: hide })
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                var row = btn.closest('tr');
                if (hide) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Hidden';
                } else {
                    btn.innerHTML = '<i class="fas fa-check"></i> Restored';
                }
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                btn.innerHTML = '<i class="fas fa-times"></i>';
                setTimeout(function() { btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; btn.disabled = false; }, 2000);
            }
        })
        .catch(function() {
            btn.innerHTML = '<i class="fas fa-times"></i>';
            setTimeout(function() { btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; btn.disabled = false; }, 2000);
        });
    };

    document.querySelectorAll('.ma-expandable').forEach(function(row) {
        row.addEventListener('click', function() {
            var accountId = this.getAttribute('data-account-id');
            var type = this.getAttribute('data-type');
            var prefix = type === 'bs' ? 'bs-' : '';
            var drillRow = document.getElementById('drill-' + prefix + accountId);
            var panel = document.getElementById('panel-' + prefix + accountId);
            var isOpen = this.classList.contains('ma-expanded');
            var accountName = this.querySelector('td:first-child').textContent.trim().split('\n')[0].trim();

            if (isOpen) {
                this.classList.remove('ma-expanded');
                panel.classList.remove('open');
                setTimeout(function() { drillRow.style.display = 'none'; }, 350);
                return;
            }

            this.classList.add('ma-expanded');
            drillRow.style.display = '';

            var cacheKey = type + '-' + accountId;
            if (drillCache[cacheKey]) {
                panel.innerHTML = drillCache[cacheKey];
                setTimeout(function() { panel.classList.add('open'); }, 10);
                return;
            }

            panel.innerHTML = '<div class="ma-drilldown-loading"><i class="fas fa-spinner fa-spin"></i> Loading transactions...</div>';
            setTimeout(function() { panel.classList.add('open'); }, 10);

            var url = '/cims/accounts/api/account-transactions/' + companyId + '/' + accountId;
            if (type === 'pnl') {
                url += '?date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);
            } else {
                url += '?date_to=' + encodeURIComponent(dateTo);
            }

            fetch(url, { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(resp) {
                    var data = resp.rows || [];
                    renderDrillTable(data, accountName, panel, cacheKey);
                })
                .catch(function() {
                    panel.innerHTML = '<div class="ma-drilldown-empty">Failed to load transactions.</div>';
                });
        });
    });
})();
</script>
@endpush
