<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ATP Services - Business Proposal - {{ $sla->sla_reference }}</title>
    <style>
        /* ================================================================
           ATP SERVICES — BUSINESS PROPOSAL PDF
           DomPDF Compatible (no flexbox, no grid, tables + floats only)
           ================================================================ */
        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.55;
            color: #1a1a2e;
        }

        /* ── Color Variables (inline) ── */
        /* Primary teal: #1a8a7f */
        /* Dark teal: #147a70 */
        /* Light teal bg: #e8f5f3 */
        /* Text dark: #1a1a2e */
        /* Text secondary: #4a5568 */

        /* ── Page Container ── */
        .page {
            width: 210mm;
            min-height: 297mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        .page-inner {
            padding: 20mm;
        }

        /* ── Page Number Footer ── */
        .page-footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #4a5568;
        }

        /* ── Section Headers ── */
        .section-header {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .section-header-line {
            width: 60px;
            height: 3px;
            background-color: #1a8a7f;
            margin-bottom: 22px;
        }
        .section-subheader {
            font-size: 16px;
            font-weight: bold;
            color: #1a8a7f;
            margin-top: 18px;
            margin-bottom: 8px;
        }

        /* ── Body Text ── */
        .body-text {
            font-size: 11px;
            color: #4a5568;
            line-height: 1.65;
            margin-bottom: 12px;
        }

        /* ── Bullet Lists ── */
        .bullet-list {
            margin-left: 16px;
            margin-bottom: 12px;
        }
        .bullet-list li {
            font-size: 11px;
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 3px;
            list-style: none;
            position: relative;
            padding-left: 14px;
        }
        .bullet-list li::before {
            content: "\2022";
            color: #1a8a7f;
            font-weight: bold;
            font-size: 14px;
            position: absolute;
            left: 0;
            top: -1px;
        }

        /* ── Tables ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #1a8a7f;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #147a70;
        }
        .data-table td {
            padding: 7px 10px;
            font-size: 10.5px;
            border: 1px solid #d1d5db;
            color: #1a1a2e;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafb;
        }

        /* ── Info Box ── */
        .info-box {
            background-color: #e8f5f3;
            border-left: 4px solid #1a8a7f;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 10.5px;
            color: #1a1a2e;
        }

        /* ── Two Column Layout ── */
        .two-col-table {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .two-col-table td.col-left {
            padding-right: 12px;
        }
        .two-col-table td.col-right {
            padding-left: 12px;
        }

        /* ── Cover Page ── */
        .cover-page {
            background: linear-gradient(180deg, #ffffff 0%, #f0fdfb 100%);
            text-align: center;
            position: relative;
        }
        .cover-inner {
            padding: 30mm 25mm 20mm 25mm;
        }

        /* ── CSS Building Icon ── */
        .logo-icon-wrapper {
            display: inline-block;
            margin-bottom: 10px;
        }
        .logo-building {
            width: 70px;
            height: 80px;
            position: relative;
            display: inline-block;
        }
        .building-base {
            width: 60px;
            height: 60px;
            background-color: #1a8a7f;
            border-radius: 6px;
            margin: 0 auto;
            position: relative;
        }
        .building-roof {
            width: 0;
            height: 0;
            border-left: 35px solid transparent;
            border-right: 35px solid transparent;
            border-bottom: 20px solid #147a70;
            margin: 0 auto 0 auto;
        }
        .building-window {
            width: 10px;
            height: 10px;
            background-color: #ffffff;
            border-radius: 1px;
            display: inline-block;
            margin: 3px;
        }

        /* ── Grid Cards (for Why Choose Us) ── */
        .card-grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        .card-grid-table td {
            width: 33.33%;
            vertical-align: top;
            background-color: #f0fdfb;
            border: 1px solid #d1e7e4;
            border-radius: 6px;
            padding: 14px;
        }
        .card-title {
            font-size: 12px;
            font-weight: bold;
            color: #1a8a7f;
            margin-bottom: 6px;
        }
        .card-text {
            font-size: 10px;
            color: #4a5568;
            line-height: 1.55;
        }

        /* ── Signature Block ── */
        .sig-box {
            border: 1px solid #d1d5db;
            padding: 12px;
            min-height: 40px;
        }
        .sig-label {
            font-size: 9px;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .sig-value {
            font-size: 11px;
            color: #1a1a2e;
            font-weight: bold;
            min-height: 18px;
        }
        .sig-line {
            border-bottom: 1px solid #d1d5db;
            height: 30px;
            margin-bottom: 4px;
        }

        /* ── Clause Numbering ── */
        .clause-num {
            font-weight: bold;
            color: #1a8a7f;
            margin-right: 4px;
        }
        .clause-text {
            font-size: 10.5px;
            color: #4a5568;
            line-height: 1.65;
            margin-bottom: 8px;
        }

        /* ── Totals Row ── */
        .totals-label {
            text-align: right;
            font-weight: bold;
            padding: 6px 10px;
            font-size: 10.5px;
            border: 1px solid #d1d5db;
        }
        .totals-value {
            text-align: right;
            font-weight: bold;
            padding: 6px 10px;
            font-size: 10.5px;
            border: 1px solid #d1d5db;
        }
        .grand-total {
            background-color: #1a8a7f;
            color: #ffffff;
            font-size: 12px;
        }

        /* ── TOC ── */
        .toc-table {
            width: 100%;
            border-collapse: collapse;
        }
        .toc-table tr td {
            padding: 10px 0;
            border-bottom: 1px solid #e8f5f3;
            font-size: 13px;
            color: #1a1a2e;
        }
        .toc-num {
            width: 40px;
            font-weight: bold;
            color: #1a8a7f;
            font-size: 14px;
        }
        .toc-title {
            font-weight: 600;
        }
        .toc-page {
            width: 40px;
            text-align: right;
            font-weight: bold;
            color: #1a8a7f;
        }
        .toc-dots {
            border-bottom: 2px dotted #d1e7e4;
        }

        /* ── Back Cover ── */
        .back-cover {
            background-color: #1a8a7f;
            text-align: center;
            color: #ffffff;
        }
        .back-inner {
            padding: 60mm 30mm 40mm 30mm;
        }

        /* ── Service Pillar ── */
        .pillar-box {
            background-color: #f0fdfb;
            border: 1px solid #d1e7e4;
            border-left: 4px solid #1a8a7f;
            padding: 14px 16px;
            margin-bottom: 14px;
        }
        .pillar-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a8a7f;
            margin-bottom: 6px;
        }
        .pillar-text {
            font-size: 10.5px;
            color: #4a5568;
            line-height: 1.6;
        }

        /* ── Page Number (manual) ── */
        .page-num {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            padding-top: 10px;
            position: absolute;
            bottom: 12mm;
            left: 0;
            right: 0;
        }

        /* ── Compact text ── */
        .small-text {
            font-size: 9.5px;
            color: #4a5568;
            line-height: 1.55;
        }

        /* ── Note ── */
        .note-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            padding: 8px 12px;
            font-size: 9.5px;
            color: #92400e;
            margin-top: 12px;
        }
    </style>
</head>
<body>

@php
    // ── Package Price Lookup ──
    $packages = [
        'starter'      => ['name' => 'Starter',      'price' => 749,  'turnover' => 'R750K',  'tier' => 'Tier 1'],
        'growth'       => ['name' => 'Growth',        'price' => 1499, 'turnover' => 'R1.5M',  'tier' => 'Tier 2'],
        'professional' => ['name' => 'Professional',  'price' => 2499, 'turnover' => 'R2.5M',  'tier' => 'Tier 3'],
        'enterprise'   => ['name' => 'Enterprise',    'price' => 4999, 'turnover' => 'R6.5M',  'tier' => 'Tier 4'],
        'premium'      => ['name' => 'Premium',       'price' => 9999, 'turnover' => 'R10M',   'tier' => 'Tier 5'],
    ];
    $selectedPkg = $packages[$sla->selected_package] ?? null;

    // ── Payroll Price Lookup ──
    $payrollTiers = [
        'payroll_3'      => ['name' => 'Payroll: 1 - 3 Employees',   'price' => 600,  'band' => 'Band 1', 'billing' => 'Flat Rate'],
        'payroll_10'     => ['name' => 'Payroll: 4 - 10 Employees',  'price' => 1500, 'band' => 'Band 2', 'billing' => 'Flat Rate'],
        'payroll_20'     => ['name' => 'Payroll: 11 - 20 Employees', 'price' => 2500, 'band' => 'Band 3', 'billing' => 'Flat Rate'],
        'payroll_21plus' => ['name' => 'Payroll: 21+ Employees',     'price' => 125,  'band' => 'Band 4', 'billing' => 'Per Employee'],
    ];
    $selectedPayroll = $payrollTiers[$sla->selected_payroll ?? ''] ?? null;

    // ── Add-on Lookup ──
    $addons = [
        'addon_mibco'         => ['name' => 'MIBCO Compliance',        'price' => 500, 'billing' => 'Monthly'],
        'addon_leave'         => ['name' => 'Leave Management',        'price' => 400, 'billing' => 'Monthly'],
        'addon_coida'         => ['name' => 'COIDA Registration',      'price' => 350, 'billing' => 'Once-off'],
        'addon_contracts'     => ['name' => 'Employment Contracts',    'price' => 385, 'billing' => 'Per Employee'],
        'addon_payroll_file'  => ['name' => 'Payroll File Setup',      'price' => 350, 'billing' => 'Per Employee'],
        'addon_notices'       => ['name' => 'Notices',                 'price' => 95,  'billing' => 'Per Employee'],
        'addon_payslips'      => ['name' => 'Printed Payslips',        'price' => 55,  'billing' => 'Per Employee'],
        'addon_registrations' => ['name' => 'SARS Registrations',      'price' => 995, 'billing' => 'Per Employee'],
        'addon_terminations'  => ['name' => 'Employee Terminations',   'price' => 500, 'billing' => 'Per Employee'],
    ];

    // Build line items
    $lineItems = [];
    $lineNum = 0;
    $subtotal = 0;

    if ($selectedPkg) {
        $lineNum++;
        $lineItems[] = [
            'num'   => $lineNum,
            'desc'  => $selectedPkg['name'] . ' Package (' . $selectedPkg['tier'] . ' - Turnover up to ' . $selectedPkg['turnover'] . ' p.a.)',
            'price' => $selectedPkg['price'],
            'qty'   => 1,
            'total' => $selectedPkg['price'],
        ];
        $subtotal += $selectedPkg['price'];
    }

    if ($selectedPayroll) {
        $lineNum++;
        $qty = ($sla->selected_payroll === 'payroll_21plus') ? ($sla->payroll_employee_count ?? 21) : 1;
        $total = $selectedPayroll['price'] * $qty;
        $lineItems[] = [
            'num'   => $lineNum,
            'desc'  => $selectedPayroll['name'] . ' (' . $selectedPayroll['band'] . ' - ' . $selectedPayroll['billing'] . ')',
            'price' => $selectedPayroll['price'],
            'qty'   => $qty,
            'total' => $total,
        ];
        $subtotal += $total;
    }

    foreach ($addons as $key => $addon) {
        if (!empty($sla->$key)) {
            $lineNum++;
            $lineItems[] = [
                'num'   => $lineNum,
                'desc'  => $addon['name'] . ' (' . $addon['billing'] . ')',
                'price' => $addon['price'],
                'qty'   => 1,
                'total' => $addon['price'],
            ];
            $subtotal += $addon['price'];
        }
    }

    $vat = round($subtotal * 0.15, 2);
    $grandTotal = $subtotal + $vat;

    // Date formatting
    $proposalDate = now()->format('j F Y');
    $signedDate = $sla->signed_date ? \Carbon\Carbon::parse($sla->signed_date)->format('j F Y') : $proposalDate;
@endphp


{{-- ================================================================
     PAGE 1 — COVER PAGE
     ================================================================ --}}
<div class="page cover-page">
    <div class="cover-inner">

        {{-- Logo Area --}}
        <div style="margin-bottom: 12px;">
            <table style="margin: 0 auto; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                        {{-- Teal building icon --}}
                        <div style="width: 56px; height: 64px; display: inline-block;">
                            <div style="width: 0; height: 0; border-left: 28px solid transparent; border-right: 28px solid transparent; border-bottom: 16px solid #147a70; margin: 0 auto;"></div>
                            <div style="width: 50px; height: 44px; background-color: #1a8a7f; border-radius: 3px; margin: 0 auto; text-align: center; padding-top: 6px;">
                                <table style="margin: 0 auto; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                        <td colspan="1" style="padding: 2px;"><div style="width: 8px; height: 12px; background: #e8f5f3; border-radius: 1px 1px 0 0;"></div></td>
                                        <td style="padding: 2px;"><div style="width: 8px; height: 8px; background: #fff; border-radius: 1px;"></div></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: middle; text-align: left;">
                        <div style="font-size: 28px; font-weight: bold; color: #1a8a7f; letter-spacing: -1px; line-height: 1.1;">ATP</div>
                        <div style="font-size: 10px; font-weight: bold; color: #4a5568; letter-spacing: 2px; text-transform: uppercase;">Services</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="font-size: 11px; color: #4a5568; margin-bottom: 6px;">Accounting Taxation &amp; Payroll (Pty) Ltd</div>
        <div style="font-size: 10px; color: #94a3b8; margin-bottom: 50px;">
            <span style="margin-right: 20px;">&#9742; 031 101 3876</span>
            <span>&#9741; www.atpservices.co.za</span>
        </div>

        {{-- Teal accent line --}}
        <div style="width: 80px; height: 3px; background-color: #1a8a7f; margin: 0 auto 40px auto;"></div>

        {{-- Main Title --}}
        <div style="font-size: 42px; font-weight: bold; color: #1a1a2e; letter-spacing: -1.5px; margin-bottom: 16px;">
            BUSINESS PROPOSAL
        </div>

        {{-- Tagline --}}
        <div style="font-size: 13px; color: #1a8a7f; font-style: italic; margin-bottom: 60px; letter-spacing: 0.3px;">
            Ensuring Compliance. Protecting Directors. Driving Business Growth.
        </div>

        {{-- Divider --}}
        <div style="width: 120px; height: 1px; background-color: #d1e7e4; margin: 0 auto 40px auto;"></div>

        {{-- Presented to / Prepared by --}}
        <div style="margin-bottom: 8px;">
            <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Presented to</span>
        </div>
        <div style="font-size: 22px; font-weight: bold; color: #1a1a2e; margin-bottom: 30px;">
            {{ $client->company_name }}
        </div>

        <div style="margin-bottom: 8px;">
            <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Prepared by</span>
        </div>
        <div style="font-size: 16px; font-weight: bold; color: #1a8a7f; margin-bottom: 30px;">
            Krish Moodley
        </div>

        {{-- Date & Reference --}}
        <table style="margin: 0 auto; border-collapse: collapse;">
            <tr>
                <td style="padding: 4px 20px; text-align: right; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Date</td>
                <td style="padding: 4px 20px; text-align: left; font-size: 12px; font-weight: bold; color: #1a1a2e;">{{ $proposalDate }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 20px; text-align: right; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Reference</td>
                <td style="padding: 4px 20px; text-align: left; font-size: 12px; font-weight: bold; color: #1a8a7f;">{{ $sla->sla_reference }}</td>
            </tr>
        </table>

    </div>

    {{-- Bottom teal bar --}}
    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 8px; background: linear-gradient(90deg, #147a70, #1a8a7f, #22b8ab);"></div>
</div>


{{-- ================================================================
     PAGE 2 — TABLE OF CONTENTS
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Table of Contents</div>
        <div class="section-header-line"></div>

        <table class="toc-table" style="margin-top: 20px;">
            <tr>
                <td class="toc-num">01</td>
                <td class="toc-title">Executive Summary</td>
                <td class="toc-dots"></td>
                <td class="toc-page">3</td>
            </tr>
            <tr>
                <td class="toc-num">02</td>
                <td class="toc-title">About ATP Services</td>
                <td class="toc-dots"></td>
                <td class="toc-page">4</td>
            </tr>
            <tr>
                <td class="toc-num">03</td>
                <td class="toc-title">Our Services</td>
                <td class="toc-dots"></td>
                <td class="toc-page">5</td>
            </tr>
            <tr>
                <td class="toc-num">04</td>
                <td class="toc-title">Why Choose Us</td>
                <td class="toc-dots"></td>
                <td class="toc-page">6</td>
            </tr>
            <tr>
                <td class="toc-num">05</td>
                <td class="toc-title">Service Quotation</td>
                <td class="toc-dots"></td>
                <td class="toc-page">7</td>
            </tr>
            <tr>
                <td class="toc-num">06</td>
                <td class="toc-title">Service Level Agreement</td>
                <td class="toc-dots"></td>
                <td class="toc-page">8</td>
            </tr>
            <tr>
                <td class="toc-num">07</td>
                <td class="toc-title">Debit Order Authorisation</td>
                <td class="toc-dots"></td>
                <td class="toc-page">9</td>
            </tr>
            <tr>
                <td class="toc-num">08</td>
                <td class="toc-title">POPIA Consent</td>
                <td class="toc-dots"></td>
                <td class="toc-page">10</td>
            </tr>
            <tr>
                <td class="toc-num">09</td>
                <td class="toc-title">Terms &amp; Conditions</td>
                <td class="toc-dots"></td>
                <td class="toc-page">11</td>
            </tr>
            <tr>
                <td class="toc-num">10</td>
                <td class="toc-title">Declaration &amp; Signature</td>
                <td class="toc-dots"></td>
                <td class="toc-page">12</td>
            </tr>
        </table>

        <div style="margin-top: 50px; text-align: center;">
            <div style="width: 40px; height: 2px; background-color: #1a8a7f; margin: 0 auto 16px auto;"></div>
            <div style="font-size: 10px; color: #94a3b8; font-style: italic;">
                This proposal is confidential and intended solely for {{ $client->company_name }}.
            </div>
        </div>
    </div>
    <div class="page-num">2</div>
</div>


{{-- ================================================================
     PAGE 3 — EXECUTIVE SUMMARY
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Executive Summary</div>
        <div class="section-header-line"></div>

        <p class="body-text">
            ATP Services is pleased to present this comprehensive proposal for accounting, taxation, and payroll services to <strong>{{ $client->company_name }}</strong>. Our solution is designed to ensure full regulatory compliance, protect your directors from personal liability, and provide the financial clarity needed to drive sustainable business growth.
        </p>

        <p class="body-text">
            In South Africa, businesses are required to comply with a range of regulatory obligations. Non-compliance carries severe consequences, including financial penalties, legal exposure, and operational disruption. The key areas of compliance include:
        </p>

        <ul class="bullet-list">
            <li><strong>SARS Compliance</strong> &mdash; Company tax returns (ITR14), VAT submissions, provisional tax (IRP6), PAYE, UIF, and SDL obligations</li>
            <li><strong>Department of Labour</strong> &mdash; Employment equity, skills development levies, and basic conditions of employment</li>
            <li><strong>COIDA / Workmen's Compensation</strong> &mdash; Registration, annual returns, and assessment payments to the Compensation Fund</li>
            <li><strong>Bargaining Councils</strong> &mdash; MIBCO and other sector-specific compliance obligations</li>
            <li><strong>CIPC</strong> &mdash; Annual returns, beneficial ownership declarations, and company amendments</li>
        </ul>

        <div class="section-subheader">Consequences of Non-Compliance</div>

        <p class="body-text">
            Failure to meet these obligations may result in:
        </p>

        <ul class="bullet-list">
            <li><strong>Financial Penalties</strong> &mdash; SARS penalties of up to 200% on outstanding tax, plus daily compounding interest</li>
            <li><strong>Legal Exposure</strong> &mdash; Directors may be held personally liable for company tax debts under section 180 of the Tax Administration Act</li>
            <li><strong>Business Disruption</strong> &mdash; Inability to obtain tax clearance certificates, loss of government tenders, and CIPC deregistration</li>
            <li><strong>Criminal Prosecution</strong> &mdash; Wilful non-compliance may result in criminal charges against responsible directors</li>
        </ul>

        <div class="info-box">
            <strong style="color: #1a8a7f;">Our Vision:</strong> To be a trusted compliance and financial management partner for every growing South African business.
        </div>

        <div class="section-subheader">Our Mission</div>

        <ul class="bullet-list">
            <li>Deliver practical, high-quality accounting and tax solutions that protect businesses and their directors</li>
            <li>Ensure full regulatory compliance across SARS, CIPC, COIDA, and the Department of Labour</li>
            <li>Empower business owners with accurate, timely financial information for better decision-making</li>
            <li>Provide fixed, transparent pricing that eliminates billing uncertainty</li>
        </ul>
    </div>
    <div class="page-num">3</div>
</div>


{{-- ================================================================
     PAGE 4 — ABOUT ATP SERVICES
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">About ATP Services</div>
        <div class="section-header-line"></div>

        <p class="body-text">
            <strong>Accounting Taxation &amp; Payroll (Pty) Ltd</strong> (trading as ATP Services) is a Durban-based accounting practice committed to delivering practical, high-quality financial solutions to small and medium enterprises across South Africa.
        </p>

        <p class="body-text">
            Founded on the belief that every business deserves access to professional accounting services without the burden of unpredictable fees, ATP Services offers a unique fixed-pricing model that never exceeds 1% of a client's monthly gross revenue. This transparent approach eliminates billing surprises and allows business owners to budget with confidence.
        </p>

        <p class="body-text">
            Our team combines deep technical knowledge of South African tax legislation with a hands-on, client-focused approach. We don't simply process numbers and submit returns &mdash; we actively monitor your compliance status, identify risks before they become problems, and provide proactive advisory to support your growth objectives.
        </p>

        <div class="section-subheader">Our Values</div>

        <div class="pillar-box">
            <div class="pillar-title">Transparency</div>
            <div class="pillar-text">
                We believe in honest, upfront communication. Our fixed pricing model means you always know what you're paying for. There are no hidden fees, no surprise invoices, and no hourly rate escalation. Every engagement is documented clearly, and every deliverable is defined in advance.
            </div>
        </div>

        <div class="pillar-box">
            <div class="pillar-title">Collaboration</div>
            <div class="pillar-text">
                We work alongside our clients as an extension of their team, not as a distant service provider. Your dedicated account manager is accessible via phone, email, or in-person meetings. We take the time to understand your business, your industry, and your goals.
            </div>
        </div>

        <div class="pillar-box">
            <div class="pillar-title">Continuous Improvement</div>
            <div class="pillar-text">
                The regulatory landscape in South Africa is constantly evolving. We invest in ongoing professional development, adopt the latest cloud-based accounting technologies, and continuously refine our processes to deliver faster, more accurate results for our clients.
            </div>
        </div>

        <div style="margin-top: 24px; padding: 16px; background-color: #1a8a7f; border-radius: 6px; text-align: center;">
            <div style="font-size: 14px; font-weight: bold; color: #ffffff; font-style: italic;">
                "We don't just process numbers &mdash; we protect your business."
            </div>
        </div>
    </div>
    <div class="page-num">4</div>
</div>


{{-- ================================================================
     PAGE 5 — OUR SERVICES
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Our Services</div>
        <div class="section-header-line"></div>

        <p class="body-text">
            ATP Services operates across three core service pillars, each designed to address a critical area of business compliance and financial management. Together, these pillars provide a complete solution that protects your business, ensures regulatory compliance, and supports informed decision-making.
        </p>

        {{-- Pillar 1 --}}
        <div style="margin-top: 20px;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="width: 6px; background-color: #1a8a7f; border-radius: 3px 0 0 3px;"></td>
                    <td style="background-color: #f0fdfb; padding: 18px 20px; border: 1px solid #d1e7e4; border-left: none; border-radius: 0 6px 6px 0;">
                        <div style="font-size: 10px; font-weight: bold; color: #1a8a7f; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Pillar 1</div>
                        <div style="font-size: 18px; font-weight: bold; color: #1a1a2e; margin-bottom: 10px;">Accounting &amp; Bookkeeping</div>
                        <div class="body-text" style="margin-bottom: 8px;">
                            Accurate financial records are the foundation of every successful business. Our accounting team processes your transactions, reconciles your accounts, and delivers monthly management reports that give you a clear picture of your financial position.
                        </div>
                        <ul class="bullet-list" style="margin-bottom: 0;">
                            <li>Monthly bookkeeping and bank reconciliations</li>
                            <li>Annual financial statements and trial balances</li>
                            <li>Management reports and cash flow analysis</li>
                            <li>Creditors and debtors management support</li>
                            <li>Cloud-based accounting platform setup and maintenance</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Pillar 2 --}}
        <div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="width: 6px; background-color: #1a8a7f; border-radius: 3px 0 0 3px;"></td>
                    <td style="background-color: #f0fdfb; padding: 18px 20px; border: 1px solid #d1e7e4; border-left: none; border-radius: 0 6px 6px 0;">
                        <div style="font-size: 10px; font-weight: bold; color: #1a8a7f; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Pillar 2</div>
                        <div style="font-size: 18px; font-weight: bold; color: #1a1a2e; margin-bottom: 10px;">Taxation &amp; Compliance</div>
                        <div class="body-text" style="margin-bottom: 8px;">
                            Navigating South Africa's tax landscape requires specialist knowledge and meticulous attention to deadlines. We handle every aspect of your tax obligations, from annual returns to ad-hoc compliance requirements.
                        </div>
                        <ul class="bullet-list" style="margin-bottom: 0;">
                            <li>Company tax returns (ITR14) and assessments</li>
                            <li>Monthly and bi-monthly VAT submissions (VAT201)</li>
                            <li>Provisional tax returns (IRP6) &mdash; first and second period</li>
                            <li>CIPC annual returns and beneficial ownership declarations</li>
                            <li>Tax compliance certificates and letters of good standing</li>
                            <li>SARS dispute resolution and objection submissions</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Pillar 3 --}}
        <div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="width: 6px; background-color: #1a8a7f; border-radius: 3px 0 0 3px;"></td>
                    <td style="background-color: #f0fdfb; padding: 18px 20px; border: 1px solid #d1e7e4; border-left: none; border-radius: 0 6px 6px 0;">
                        <div style="font-size: 10px; font-weight: bold; color: #1a8a7f; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Pillar 3</div>
                        <div style="font-size: 18px; font-weight: bold; color: #1a1a2e; margin-bottom: 10px;">Payroll Management</div>
                        <div class="body-text" style="margin-bottom: 8px;">
                            Payroll is one of the most time-sensitive compliance areas for any employer. Our payroll team ensures your employees are paid accurately and on time, while all statutory submissions are completed within the prescribed deadlines.
                        </div>
                        <ul class="bullet-list" style="margin-bottom: 0;">
                            <li>Monthly employee payroll processing and payslip generation</li>
                            <li>PAYE, UIF, and SDL submissions to SARS (EMP201)</li>
                            <li>Annual employer reconciliation (EMP501) and IRP5 certificates</li>
                            <li>Leave management and employee record maintenance</li>
                            <li>COIDA/WCA registration and annual returns</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="page-num">5</div>
</div>


{{-- ================================================================
     PAGE 6 — WHY CHOOSE US
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Why Choose Us</div>
        <div class="section-header-line"></div>

        <p class="body-text" style="margin-bottom: 24px;">
            ATP Services is built on a foundation of transparency, reliability, and genuine commitment to our clients' success. Here are six reasons why businesses across South Africa trust us with their compliance and financial management.
        </p>

        {{-- 2x3 Grid using table --}}
        <table class="card-grid-table">
            <tr>
                <td>
                    <div style="font-size: 24px; color: #1a8a7f; margin-bottom: 8px;">&#9733;</div>
                    <div class="card-title">Fixed Transparent Pricing</div>
                    <div class="card-text">Our fees are based on a small fraction of your turnover &mdash; never more than 1% of monthly gross revenue. No hourly rates, no hidden fees, no surprises. The smaller your turnover, the less you pay.</div>
                </td>
                <td>
                    <div style="font-size: 24px; color: #1a8a7f; margin-bottom: 8px;">&#9745;</div>
                    <div class="card-title">Full Compliance Coverage</div>
                    <div class="card-text">From SARS and CIPC to COIDA and the Department of Labour, we cover every regulatory obligation your business faces. One provider, complete peace of mind.</div>
                </td>
                <td>
                    <div style="font-size: 24px; color: #1a8a7f; margin-bottom: 8px;">&#9742;</div>
                    <div class="card-title">Dedicated Account Manager</div>
                    <div class="card-text">Every client is assigned a single point of contact who knows your business inside out. No call centres, no ticket queues &mdash; just direct access to your accountant.</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div style="font-size: 24px; color: #1a8a7f; margin-bottom: 8px;">&#9729;</div>
                    <div class="card-title">Technology Driven</div>
                    <div class="card-text">We leverage cloud-based accounting platforms for real-time collaboration, automated bank feeds, and instant access to your financial data from anywhere, at any time.</div>
                </td>
                <td>
                    <div style="font-size: 24px; color: #1a8a7f; margin-bottom: 8px;">&#9736;</div>
                    <div class="card-title">Director Protection</div>
                    <div class="card-text">Directors can be held personally liable for company tax debts. We proactively monitor your compliance status and ensure all statutory obligations are met, protecting you from personal exposure.</div>
                </td>
                <td>
                    <div style="font-size: 24px; color: #1a8a7f; margin-bottom: 8px;">&#9752;</div>
                    <div class="card-title">Proactive Advisory</div>
                    <div class="card-text">Beyond compliance, we provide monthly management reports and strategic consultations to help you identify growth opportunities, optimise tax structures, and make informed decisions.</div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 30px; padding: 20px; background-color: #e8f5f3; border-radius: 6px; text-align: center;">
            <div style="font-size: 10px; color: #1a8a7f; text-transform: uppercase; letter-spacing: 1.5px; font-weight: bold; margin-bottom: 8px;">Our Promise</div>
            <div style="font-size: 13px; color: #1a1a2e; font-weight: bold;">
                We treat your business as if it were our own. Every submission is checked twice. Every deadline is tracked. Every rand is accounted for.
            </div>
        </div>

        <div style="margin-top: 30px;">
            <div class="section-subheader">Client Testimonial Process</div>
            <p class="body-text">
                We believe that results speak louder than promises. Upon engagement, we commit to delivering measurable outcomes within the first 90 days, including a full compliance health check, resolution of any outstanding SARS issues, and establishment of a structured monthly reporting cycle.
            </p>
        </div>
    </div>
    <div class="page-num">6</div>
</div>


{{-- ================================================================
     PAGE 7 — SERVICE QUOTATION (DYNAMIC)
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Service Quotation</div>
        <div class="section-header-line"></div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Reference</div>
                    <div style="font-size: 13px; font-weight: bold; color: #1a8a7f;">{{ $sla->sla_reference }}</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Date</div>
                    <div style="font-size: 13px; font-weight: bold; color: #1a1a2e;">{{ $proposalDate }}</div>
                </td>
            </tr>
        </table>

        <div class="info-box" style="margin-bottom: 20px;">
            <strong>Prepared for:</strong> {{ $client->company_name }}
            @if($client->trading_name)
                (t/a {{ $client->trading_name }})
            @endif
            @if($client->registration_number)
                <br><strong>Registration No:</strong> {{ $client->registration_number }}
            @endif
        </div>

        {{-- Quotation Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">#</th>
                    <th>Description</th>
                    <th style="width: 85px; text-align: right;">Unit Price</th>
                    <th style="width: 40px; text-align: center;">Qty</th>
                    <th style="width: 85px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if(count($lineItems) > 0)
                    @foreach($lineItems as $item)
                        <tr>
                            <td style="text-align: center;">{{ $item['num'] }}</td>
                            <td>{{ $item['desc'] }}</td>
                            <td style="text-align: right;">R {{ number_format($item['price'], 2) }}</td>
                            <td style="text-align: center;">{{ $item['qty'] }}</td>
                            <td style="text-align: right;">R {{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; font-style: italic; padding: 20px;">
                            No services selected
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(count($lineItems) > 0)
                <tfoot>
                    <tr>
                        <td colspan="3" style="border: none;"></td>
                        <td class="totals-label">Subtotal</td>
                        <td class="totals-value">R {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border: none;"></td>
                        <td class="totals-label">VAT (15%)</td>
                        <td class="totals-value">R {{ number_format($vat, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border: none;"></td>
                        <td class="totals-label grand-total">TOTAL</td>
                        <td class="totals-value grand-total">R {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="note-box">
            <strong>Note:</strong> All prices are fixed for the current tax year and are exclusive of VAT unless otherwise stated. Monthly fees are billed in advance via debit order on the agreed date.
        </div>

        <div class="note-box" style="margin-top: 8px; background-color: #f0fdfb; border-color: #d1e7e4; border-left-color: #1a8a7f; color: #1a1a2e;">
            <strong>Ad Hoc Services:</strong> Any services not included in the selected package are charged separately per the ATP Services Rate Card. This includes, but is not limited to: CIPC Annual Returns, UBO Declarations, COIDA submissions, Tax Compliance Certificates, Letters of Good Standing, Company Registrations, and Individual Tax Returns.
        </div>

        <div style="margin-top: 20px; padding: 12px 16px; background-color: #e8f5f3; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Payment Method</div>
                        <div style="font-size: 11px; font-weight: bold; color: #1a1a2e;">Debit Order ({{ $sla->debit_order_date ?? 'TBC' }} of each month)</div>
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: right;">
                        <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Billing Cycle</div>
                        <div style="font-size: 11px; font-weight: bold; color: #1a1a2e;">Monthly in Advance</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="page-num">7</div>
</div>


{{-- ================================================================
     PAGE 8 — SERVICE LEVEL AGREEMENT
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Service Level Agreement</div>
        <div class="section-header-line"></div>

        <p class="body-text">
            This Service Level Agreement outlines the scope of services, delivery commitments, and obligations of both parties under the engagement between <strong>Accounting Taxation and Payroll (Pty) Ltd</strong> ("ATP Services") and <strong>{{ $client->company_name }}</strong> ("the Client").
        </p>

        <div class="section-subheader">Scope of Services</div>

        <p class="body-text">
            @if($selectedPkg)
                Based on the selected <strong>{{ $selectedPkg['name'] }} Package</strong> ({{ $selectedPkg['tier'] }} &mdash; Turnover up to {{ $selectedPkg['turnover'] }} p.a.), ATP Services shall provide the following core services:
            @else
                ATP Services shall provide the following core services based on the selected package:
            @endif
        </p>

        <ul class="bullet-list">
            <li>Monthly bookkeeping, bank reconciliations, and management reporting</li>
            <li>Annual company tax return (ITR14) preparation and submission</li>
            <li>Monthly PAYE submissions (EMP201) to SARS</li>
            <li>Trial balance and monthly financial reports</li>
            @if(in_array($sla->selected_package, ['growth','professional','enterprise','premium']))
                <li>Monthly or bi-monthly VAT submissions (VAT201)</li>
                <li>Provisional tax returns (IRP6) &mdash; first and second period</li>
                <li>Annual financial statements</li>
            @endif
            @if(in_array($sla->selected_package, ['professional','enterprise','premium']))
                <li>CIPC annual returns (at 50% discount)</li>
                <li>Tax compliance certificate applications</li>
            @endif
            @if(in_array($sla->selected_package, ['enterprise','premium']))
                <li>COIDA &amp; SDL submissions (at 50% discount)</li>
                <li>Quarterly strategy consultation</li>
                <li>Priority support response</li>
                <li>Dedicated account manager</li>
            @endif
            @if($sla->selected_package === 'premium')
                <li>Full audit preparation and support</li>
                <li>Company registration and amendments</li>
                <li>Optional on-site consultation</li>
                <li>Dedicated senior accountant</li>
            @endif
        </ul>

        <div class="section-subheader">Service Delivery Commitments</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Deliverable</th>
                    <th>Commitment</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Monthly bookkeeping &amp; reports</td>
                    <td>Completed by the 15th of the following month</td>
                </tr>
                <tr>
                    <td>PAYE submissions (EMP201)</td>
                    <td>Submitted by the 7th of each month</td>
                </tr>
                <tr>
                    <td>VAT submissions (VAT201)</td>
                    <td>Submitted within prescribed SARS deadlines</td>
                </tr>
                <tr>
                    <td>Annual tax returns (ITR14)</td>
                    <td>Submitted within SARS filing deadlines</td>
                </tr>
                <tr>
                    <td>Annual reconciliation (EMP501)</td>
                    <td>Submitted within SARS filing deadlines</td>
                </tr>
                <tr>
                    <td>Response to client queries</td>
                    <td>Within 72 hours via phone, email, or virtual meeting</td>
                </tr>
                <tr>
                    <td>On-site services</td>
                    <td>Within 72 hours (50km radius, subject to booking)</td>
                </tr>
            </tbody>
        </table>

        <div class="section-subheader">Client Obligations</div>

        <p class="body-text">To enable ATP Services to meet the above commitments, the Client agrees to:</p>

        <ul class="bullet-list">
            <li>Provide complete bank statements by the <strong>5th of each month</strong></li>
            <li>Provide all source documents (invoices, receipts, contracts) timeously</li>
            <li>Maintain an endorsed accounting platform as recommended by ATP Services</li>
            <li>Settle all invoices and debit orders before work commences for each billing cycle</li>
            <li>Respond to information requests within a reasonable timeframe</li>
            <li>Notify ATP Services of any changes to business structure, directors, or banking details</li>
        </ul>

        <div class="info-box">
            <strong>Important:</strong> ATP Services shall not be held liable for penalties, interest, or non-compliance arising from the Client's failure to provide the required documentation within the specified timeframes.
        </div>
    </div>
    <div class="page-num">8</div>
</div>


{{-- ================================================================
     PAGE 9 — DEBIT ORDER AUTHORISATION
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Debit Order Authorisation</div>
        <div class="section-header-line"></div>

        <div style="font-size: 10px; color: #1a8a7f; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">
            Annexure C &mdash; Debit Order Mandate
        </div>

        <p class="body-text">
            I/We hereby authorise <strong>Accounting Taxation and Payroll (Pty) Ltd</strong> to debit my/our bank account with the agreed monthly service fee as stipulated in the Service Quotation on page 7 of this proposal.
        </p>

        <div class="section-subheader">Banking Details</div>

        <table class="data-table">
            <tbody>
                <tr>
                    <td style="width: 35%; font-weight: bold; background-color: #f0fdfb;">Account Holder</td>
                    <td>{{ $sla->bank_account_holder ?? '---' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f0fdfb;">Bank Name</td>
                    <td>{{ $sla->bank_name ?? '---' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f0fdfb;">Branch Code</td>
                    <td>{{ $sla->bank_branch_code ?? '---' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f0fdfb;">Account Number</td>
                    <td>{{ $sla->bank_account_number ?? '---' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f0fdfb;">Account Type</td>
                    <td>{{ ucfirst($sla->bank_account_type ?? '---') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f0fdfb;">Debit Order Date</td>
                    <td>{{ $sla->debit_order_date ?? '---' }} of each month</td>
                </tr>
            </tbody>
        </table>

        <div class="section-subheader">Debit Order Mandate</div>

        <div class="small-text" style="margin-bottom: 12px;">
            <p style="margin-bottom: 8px;">
                I/We authorise ATP Services to issue and deliver debit order instructions to the bank for collection against my/our above-mentioned bank account on the agreed debit date of each month. In the event that the debit date falls on a weekend or public holiday, the debit will be processed on the next business day.
            </p>
            <p style="margin-bottom: 8px;">
                I/We understand that the amount debited will correspond to the monthly service fee as detailed in the quotation, inclusive of VAT at the applicable rate. Any changes to the fee structure will be communicated in writing at least 30 days prior to implementation.
            </p>
            <p style="margin-bottom: 8px;">
                I/We acknowledge that this mandate may be cancelled by providing ATP Services with 30 days' written notice. Cancellation of the debit order does not constitute termination of the Service Level Agreement.
            </p>
            <p>
                I/We confirm that the banking details provided above are correct and that the account holder(s) have authorised this debit order mandate.
            </p>
        </div>

        <div class="info-box">
            <strong>Consent Acknowledgment:</strong>
            @if($sla->debit_order_consent)
                The Client has confirmed consent to this debit order mandate.
            @else
                Consent to this debit order mandate is pending.
            @endif
        </div>

        <div style="margin-top: 20px; padding: 12px; border: 1px dashed #d1d5db; border-radius: 4px;">
            <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Important Notice</div>
            <div class="small-text">
                Should a debit order be returned unpaid, ATP Services reserves the right to re-present the debit order and to charge an administration fee of R150.00 (excl. VAT) per returned item. Services may be suspended until all outstanding amounts are settled in full.
            </div>
        </div>
    </div>
    <div class="page-num">9</div>
</div>


{{-- ================================================================
     PAGE 10 — POPIA CONSENT
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Protection of Personal Information</div>
        <div class="section-header-line"></div>

        <div style="font-size: 10px; color: #1a8a7f; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">
            POPIA Act No. 4 of 2013 &mdash; Consent Declaration
        </div>

        <p class="body-text">
            In accordance with the Protection of Personal Information Act, 2013 (Act No. 4 of 2013) ("POPIA"), Accounting Taxation and Payroll (Pty) Ltd ("ATP Services") is committed to protecting the personal information of its clients, their employees, and associated persons.
        </p>

        <div class="section-subheader">Purpose of Data Collection</div>

        <p class="body-text">ATP Services collects and processes personal information for the following purposes:</p>

        <ul class="bullet-list">
            <li>Rendering of accounting, taxation, and payroll services as contracted</li>
            <li>Submission of statutory returns to SARS, CIPC, the Department of Labour, and other regulatory bodies</li>
            <li>Communication regarding service delivery, billing, and compliance matters</li>
            <li>Compliance with legal and regulatory obligations applicable to ATP Services</li>
            <li>Internal record-keeping and quality assurance</li>
        </ul>

        <div class="section-subheader">Data Processing Rights</div>

        <p class="body-text">
            ATP Services processes personal information lawfully and in a manner that does not infringe on the rights of the data subject. Personal information is collected directly from the Client or from authorised representatives. Information is processed only to the extent necessary to fulfil the contracted services and legal obligations.
        </p>

        <div class="section-subheader">Data Retention Policy</div>

        <p class="body-text">
            Personal information is retained for the duration of the service engagement and for a period of five (5) years following termination, in accordance with the Tax Administration Act and other applicable legislation. Upon expiry of the retention period, personal information is securely destroyed.
        </p>

        <div class="section-subheader">Third Party Disclosure</div>

        <p class="body-text">
            ATP Services may disclose personal information to the following third parties, solely for the purpose of rendering the contracted services:
        </p>

        <ul class="bullet-list">
            <li>South African Revenue Service (SARS)</li>
            <li>Companies and Intellectual Property Commission (CIPC)</li>
            <li>Department of Employment and Labour</li>
            <li>Compensation Fund / COIDA</li>
            <li>Cloud-based accounting and payroll platform providers (subject to their own POPIA compliance)</li>
            <li>Banking institutions (for debit order processing only)</li>
        </ul>

        <div class="section-subheader">Client Rights Under POPIA</div>

        <p class="body-text">The Client has the right to:</p>

        <ul class="bullet-list">
            <li>Request access to personal information held by ATP Services</li>
            <li>Request correction or deletion of inaccurate personal information</li>
            <li>Object to the processing of personal information on reasonable grounds</li>
            <li>Lodge a complaint with the Information Regulator</li>
            <li>Withdraw consent for the processing of personal information (subject to contractual and legal obligations)</li>
        </ul>

        <div class="info-box">
            <strong>Consent Declaration:</strong> By signing this proposal, the Client consents to the collection, processing, and storage of personal information by ATP Services for the purposes described above, in compliance with the Protection of Personal Information Act, 2013.
        </div>
    </div>
    <div class="page-num">10</div>
</div>


{{-- ================================================================
     PAGE 11 — TERMS & CONDITIONS
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Terms &amp; Conditions</div>
        <div class="section-header-line"></div>

        <p class="clause-text">
            <span class="clause-num">1.</span> <strong>Commencement &amp; Duration</strong><br>
            This agreement shall commence on the date of signature and shall continue indefinitely until terminated by either party in accordance with clause 6. The initial engagement period is twelve (12) months, during which the agreed fees shall remain fixed.
        </p>

        <p class="clause-text">
            <span class="clause-num">2.</span> <strong>Service Fees &amp; Payment</strong><br>
            Service fees are as stipulated in the Service Quotation on page 7 and are payable monthly in advance via debit order. All fees are exclusive of VAT at the prevailing rate (currently 15%). ATP Services reserves the right to review fees annually, with 30 days' written notice. Outstanding fees exceeding 30 days will attract interest at the prescribed rate.
        </p>

        <p class="clause-text">
            <span class="clause-num">3.</span> <strong>Client Obligations</strong><br>
            The Client shall provide all necessary documentation, information, and access required for ATP Services to perform the contracted services. The Client warrants that all information provided is accurate, complete, and not misleading. ATP Services shall not be liable for any consequences arising from inaccurate or incomplete information provided by the Client.
        </p>

        <p class="clause-text">
            <span class="clause-num">4.</span> <strong>Confidentiality</strong><br>
            Both parties agree to maintain strict confidentiality of all information exchanged during the term of this agreement. Neither party shall disclose confidential information to any third party without the prior written consent of the other party, except where required by law or regulatory authority.
        </p>

        <p class="clause-text">
            <span class="clause-num">5.</span> <strong>Limitation of Liability</strong><br>
            ATP Services' total liability under this agreement shall be limited to the total fees paid by the Client in the twelve (12) months preceding the event giving rise to the claim. ATP Services shall not be liable for indirect, consequential, or special damages, including loss of profit, loss of business, or loss of data.
        </p>

        <p class="clause-text">
            <span class="clause-num">6.</span> <strong>Termination</strong><br>
            Either party may terminate this agreement by providing thirty (30) calendar days' written notice to the other party. Upon termination, all outstanding fees become immediately due and payable. ATP Services shall return all client documentation within 30 days of termination, subject to settlement of outstanding fees.
        </p>

        <p class="clause-text">
            <span class="clause-num">7.</span> <strong>Force Majeure</strong><br>
            Neither party shall be liable for any failure or delay in performance resulting from circumstances beyond its reasonable control, including but not limited to: natural disasters, government actions, power failures, internet outages, pandemics, or SARS system downtime.
        </p>

        <p class="clause-text">
            <span class="clause-num">8.</span> <strong>Governing Law</strong><br>
            This agreement shall be governed by and construed in accordance with the laws of the Republic of South Africa. The parties consent to the jurisdiction of the High Court of South Africa, KwaZulu-Natal Division, Durban.
        </p>

        <p class="clause-text">
            <span class="clause-num">9.</span> <strong>Dispute Resolution</strong><br>
            In the event of a dispute arising from this agreement, the parties shall first attempt to resolve the matter through good-faith negotiation. Should negotiation fail within 30 days, the dispute shall be referred to mediation. If mediation is unsuccessful, either party may institute legal proceedings.
        </p>

        <p class="clause-text">
            <span class="clause-num">10.</span> <strong>Entire Agreement</strong><br>
            This document, including all annexures and schedules, constitutes the entire agreement between the parties and supersedes all prior negotiations, representations, and agreements, whether written or oral. No amendment to this agreement shall be valid unless made in writing and signed by both parties.
        </p>
    </div>
    <div class="page-num">11</div>
</div>


{{-- ================================================================
     PAGE 12 — DECLARATION & SIGNATURE
     ================================================================ --}}
<div class="page">
    <div class="page-inner">
        <div class="section-header">Declaration &amp; Acceptance</div>
        <div class="section-header-line"></div>

        <p class="body-text">
            I, the undersigned, hereby confirm that I have read, understood, and agree to the terms and conditions contained in this Business Proposal, including the Service Level Agreement, Debit Order Authorisation, POPIA Consent, and all associated annexures.
        </p>

        <p class="body-text">
            I confirm that I am duly authorised to enter into this agreement on behalf of <strong>{{ $client->company_name }}</strong> and that the information provided herein is true, accurate, and complete.
        </p>

        <p class="body-text" style="margin-bottom: 30px;">
            By signing below, both parties agree to be bound by the terms of this agreement effective from the date of signature.
        </p>

        {{-- Two-column signature block --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 16px;">
                    {{-- CLIENT SIDE --}}
                    <div style="background-color: #f0fdfb; border: 1px solid #d1e7e4; border-radius: 6px; padding: 16px;">
                        <div style="font-size: 12px; font-weight: bold; color: #1a8a7f; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; text-align: center; border-bottom: 2px solid #1a8a7f; padding-bottom: 8px;">
                            Client
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Signed at</div>
                            <div class="sig-box">
                                <div class="sig-value">{{ $sla->signed_at_location ?? '' }}</div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Date</div>
                            <div class="sig-box">
                                <div class="sig-value">{{ $signedDate }}</div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Full Name</div>
                            <div class="sig-box">
                                <div class="sig-value">{{ $sla->signatory_name ?? '' }}</div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Designation</div>
                            <div class="sig-box">
                                <div class="sig-value">{{ $sla->signatory_designation ?? '' }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="sig-label">Signature</div>
                            <div class="sig-box" style="min-height: 60px; text-align: center; padding-top: 8px;">
                                @if($sla->signature_data && $sla->signature_type === 'drawn')
                                    <img src="{{ $sla->signature_data }}" style="max-width: 180px; max-height: 50px;" alt="Client Signature">
                                @elseif($sla->signature_data && $sla->signature_type === 'typed')
                                    <div style="font-size: 22px; font-style: italic; color: #1a1a2e; font-family: 'Times New Roman', Times, serif; padding-top: 6px;">
                                        {{ $sla->signature_data }}
                                    </div>
                                @else
                                    <div style="color: #94a3b8; font-style: italic; font-size: 10px; padding-top: 18px;">
                                        Awaiting signature
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>

                <td style="width: 4%;"></td>

                <td style="width: 48%; vertical-align: top; padding-left: 0;">
                    {{-- ATP SERVICES SIDE --}}
                    <div style="background-color: #f0fdfb; border: 1px solid #d1e7e4; border-radius: 6px; padding: 16px;">
                        <div style="font-size: 12px; font-weight: bold; color: #1a8a7f; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; text-align: center; border-bottom: 2px solid #1a8a7f; padding-bottom: 8px;">
                            ATP Services
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Signed at</div>
                            <div class="sig-box">
                                <div class="sig-value">Durban</div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Date</div>
                            <div class="sig-box">
                                <div class="sig-value">{{ $signedDate }}</div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Full Name</div>
                            <div class="sig-box">
                                <div class="sig-value">Krish Moodley</div>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div class="sig-label">Designation</div>
                            <div class="sig-box">
                                <div class="sig-value">Director</div>
                            </div>
                        </div>

                        <div>
                            <div class="sig-label">Signature</div>
                            <div class="sig-box" style="min-height: 60px; text-align: center; padding-top: 8px;">
                                <div style="font-size: 24px; font-style: italic; color: #1a8a7f; font-family: 'Times New Roman', Times, serif; padding-top: 4px;">
                                    K. Moodley
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 24px; text-align: center;">
            <div style="width: 40px; height: 2px; background-color: #1a8a7f; margin: 0 auto 10px auto;"></div>
            <div style="font-size: 9px; color: #94a3b8;">
                This document was generated electronically. Reference: {{ $sla->sla_reference }}
            </div>
        </div>
    </div>
    <div class="page-num">12</div>
</div>


{{-- ================================================================
     PAGE 13 — BACK COVER
     ================================================================ --}}
<div class="page back-cover" style="page-break-after: avoid;">
    <div class="back-inner">

        <div style="font-size: 48px; font-weight: bold; color: #ffffff; margin-bottom: 20px; letter-spacing: -1px;">
            Thank You
        </div>

        <div style="width: 60px; height: 2px; background-color: rgba(255,255,255,0.4); margin: 0 auto 20px auto;"></div>

        <div style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 60px; font-style: italic;">
            We look forward to a successful partnership.
        </div>

        <div style="width: 100px; height: 1px; background-color: rgba(255,255,255,0.2); margin: 0 auto 40px auto;"></div>

        {{-- Contact Details --}}
        <table style="margin: 0 auto; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 20px; text-align: center;">
                    <div style="font-size: 9px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;">Phone</div>
                    <div style="font-size: 14px; font-weight: bold; color: #ffffff;">031 101 3876</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 20px; text-align: center;">
                    <div style="font-size: 9px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;">Website</div>
                    <div style="font-size: 14px; font-weight: bold; color: #ffffff;">www.atpservices.co.za</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 20px; text-align: center;">
                    <div style="font-size: 9px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;">Address</div>
                    <div style="font-size: 14px; font-weight: bold; color: #ffffff;">Durban, KwaZulu-Natal, South Africa</div>
                </td>
            </tr>
        </table>

        {{-- Logo area --}}
        <div style="margin-top: 60px;">
            <table style="margin: 0 auto; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                        <div style="width: 40px; height: 46px; display: inline-block;">
                            <div style="width: 0; height: 0; border-left: 20px solid transparent; border-right: 20px solid transparent; border-bottom: 12px solid rgba(255,255,255,0.3); margin: 0 auto;"></div>
                            <div style="width: 36px; height: 30px; background-color: rgba(255,255,255,0.15); border-radius: 3px; margin: 0 auto; text-align: center; padding-top: 4px;">
                                <table style="margin: 0 auto; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 1px;"><div style="width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 1px;"></div></td>
                                        <td style="padding: 1px;"><div style="width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 1px;"></div></td>
                                        <td style="padding: 1px;"><div style="width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 1px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 1px;"><div style="width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 1px;"></div></td>
                                        <td style="padding: 1px;"><div style="width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 1px;"></div></td>
                                        <td style="padding: 1px;"><div style="width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 1px;"></div></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: middle; text-align: left;">
                        <div style="font-size: 22px; font-weight: bold; color: rgba(255,255,255,0.9); letter-spacing: -0.5px; line-height: 1.1;">ATP</div>
                        <div style="font-size: 8px; font-weight: bold; color: rgba(255,255,255,0.5); letter-spacing: 2px; text-transform: uppercase;">Services</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 16px; font-size: 9px; color: rgba(255,255,255,0.4);">
            Accounting Taxation &amp; Payroll (Pty) Ltd
        </div>
    </div>
</div>

</body>
</html>
