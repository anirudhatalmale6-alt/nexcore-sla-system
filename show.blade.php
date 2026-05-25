@extends('nexcore_client_manager::layouts.nerve-centre')

@section('title', 'Engagement Letter — ' . $sla->sla_reference)
@section('topbar_page', 'Engagement Letter')

@push('styles')
<style>
/* ================================================================
   ATP SERVICES — SLA SHOW / DETAIL PAGE
   ================================================================ */

.sla-show-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    gap: 16px;
    flex-wrap: wrap;
}
.sla-show-header-left { display: flex; align-items: center; gap: 16px; }
.sla-show-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(6,182,212,0.15), rgba(6,182,212,0.05));
    border: 1px solid rgba(6,182,212,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}
.sla-show-icon i { color: #06b6d4; font-size: 20px; }
.sla-show-title { font-size: 22px; font-weight: 900; color: #f1f5f9; letter-spacing: 0.8px; margin: 0; }
.sla-show-sub { font-size: 13px; color: var(--text-muted, #64748b); margin-top: 2px; }
.sla-show-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.sla-show-btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.05);
    color: var(--text-secondary, #94a3b8);
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.5px;
}
.sla-show-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #e2e8f0; text-decoration: none; }
.sla-show-btn-primary {
    background: linear-gradient(135deg, #06b6d4, #0ea5e9);
    color: #0a0e1a;
    border: none;
    box-shadow: 0 3px 12px rgba(6,182,212,0.25);
}
.sla-show-btn-primary:hover { box-shadow: 0 5px 20px rgba(6,182,212,0.4); color: #0a0e1a; }

/* Status Badge */
.sla-show-status {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Detail Grid */
.sla-show-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 768px) { .sla-show-grid { grid-template-columns: 1fr; } }

.sla-show-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 20px;
}
.sla-show-card-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #06b6d4;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sla-show-card-title i { font-size: 13px; }
.sla-show-field { margin-bottom: 12px; }
.sla-show-field:last-child { margin-bottom: 0; }
.sla-show-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    margin-bottom: 3px;
}
.sla-show-value {
    font-size: 14px;
    font-weight: 600;
    color: #e2e8f0;
    word-break: break-word;
}
.sla-show-value.sla-empty { color: rgba(100,116,139,0.5); font-style: italic; font-weight: 400; }

/* Package Badge */
.sla-show-package {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.5px;
}

/* T&C preview */
.sla-show-tc-note {
    background: rgba(6,182,212,0.04);
    border: 1px solid rgba(6,182,212,0.1);
    border-radius: 12px;
    padding: 16px 20px;
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.7;
    margin-bottom: 24px;
}

/* Signature Display */
.sla-show-sig-box {
    background: rgba(255,255,255,0.9);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 20px;
    max-width: 500px;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sla-show-sig-box img { max-width: 100%; max-height: 160px; }
.sla-show-sig-typed {
    font-size: 32px;
    font-weight: 300;
    font-style: italic;
    font-family: 'Georgia', serif;
    color: #1a1a2e;
}

@@keyframes sla-show-fade {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.sla-show-card {
    animation: sla-show-fade 0.35s ease both;
}
.sla-show-card:nth-child(2) { animation-delay: 0.05s; }
.sla-show-card:nth-child(3) { animation-delay: 0.10s; }
.sla-show-card:nth-child(4) { animation-delay: 0.15s; }
</style>
@endpush

@section('content')

@php $badge = $sla->status_badge; @endphp

<div class="sla-show-header">
    <div class="sla-show-header-left">
        <div class="sla-show-icon"><i class="fas fa-file-contract"></i></div>
        <div>
            <h1 class="sla-show-title">{{ $sla->sla_reference }}</h1>
            <div class="sla-show-sub">Engagement Letter for {{ $sla->signatory_name ?: $client->company_name }}</div>
        </div>
        <span class="sla-show-status" style="background: {{ $badge['color'] }}20; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['color'] }}40;">
            <i class="fas fa-circle" style="font-size:6px;"></i> {{ $badge['label'] }}
        </span>
    </div>
    <div class="sla-show-actions">
        <a href="{{ route('nexcore.clients.show.sla', $client->id) }}" class="sla-show-btn"><i class="fas fa-arrow-left"></i> Back to List</a>
        <a href="{{ route('nexcore.clients.show.sla.edit', [$client->id, $sla->id]) }}" class="sla-show-btn"><i class="fas fa-pen"></i> Edit</a>
        <button class="sla-show-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>
</div>

{{-- Detail Cards --}}
<div class="sla-show-grid">
    {{-- Signatory --}}
    <div class="sla-show-card">
        <div class="sla-show-card-title"><i class="fas fa-user-tie"></i> Signatory Details</div>
        <div class="sla-show-field">
            <div class="sla-show-label">Full Name</div>
            <div class="sla-show-value {{ $sla->signatory_name ? '' : 'sla-empty' }}">{{ $sla->signatory_name ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">ID / Passport Number</div>
            <div class="sla-show-value {{ $sla->signatory_id_number ? '' : 'sla-empty' }}">{{ $sla->signatory_id_number ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Email</div>
            <div class="sla-show-value {{ $sla->signatory_email ? '' : 'sla-empty' }}">{{ $sla->signatory_email ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Cellphone</div>
            <div class="sla-show-value {{ $sla->signatory_cellphone ? '' : 'sla-empty' }}">{{ $sla->signatory_cellphone ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Designation</div>
            <div class="sla-show-value {{ $sla->signatory_designation ? '' : 'sla-empty' }}">{{ $sla->signatory_designation ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Province</div>
            <div class="sla-show-value {{ $sla->province ? '' : 'sla-empty' }}">{{ $sla->province ?: 'Not provided' }}</div>
        </div>
    </div>

    {{-- Company & Tax --}}
    <div class="sla-show-card">
        <div class="sla-show-card-title"><i class="fas fa-building"></i> Company &amp; Tax Information</div>
        <div class="sla-show-field">
            <div class="sla-show-label">Business Name</div>
            <div class="sla-show-value {{ $sla->business_name ? '' : 'sla-empty' }}">{{ $sla->business_name ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Registration Number</div>
            <div class="sla-show-value {{ $sla->company_reg_number ? '' : 'sla-empty' }}">{{ $sla->company_reg_number ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Tax Reference</div>
            <div class="sla-show-value {{ $sla->tax_reference_number ? '' : 'sla-empty' }}">{{ $sla->tax_reference_number ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">VAT Number</div>
            <div class="sla-show-value {{ $sla->vat_number ? '' : 'sla-empty' }}">{{ $sla->vat_number ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">PAYE Number</div>
            <div class="sla-show-value {{ $sla->paye_number ? '' : 'sla-empty' }}">{{ $sla->paye_number ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">UIF Number</div>
            <div class="sla-show-value {{ $sla->uif_number ? '' : 'sla-empty' }}">{{ $sla->uif_number ?: 'Not provided' }}</div>
        </div>
    </div>

    {{-- Service Package --}}
    <div class="sla-show-card">
        <div class="sla-show-card-title"><i class="fas fa-boxes-stacked"></i> Service Package</div>
        @php
            $pkgColors = ['starter'=>'#06b6d4','growth'=>'#3b82f6','professional'=>'#10b981','enterprise'=>'#f59e0b','premium'=>'#8b5cf6'];
            $pkgColor = $pkgColors[$sla->selected_package ?? ''] ?? '#64748b';
        @endphp
        <div class="sla-show-field">
            <div class="sla-show-label">Selected Package</div>
            @if($sla->selected_package)
            <span class="sla-show-package" style="background:{{ $pkgColor }}15; color:{{ $pkgColor }}; border: 1px solid {{ $pkgColor }}40;">
                <i class="fas fa-crown"></i> {{ ucfirst($sla->selected_package) }}
            </span>
            @else
            <div class="sla-show-value sla-empty">No package selected</div>
            @endif
        </div>
        <div class="sla-show-field" style="margin-top:12px;">
            <div class="sla-show-label">Service Consent</div>
            <div class="sla-show-value">
                @if($sla->service_consent)
                <span style="color:#10b981;"><i class="fas fa-check-circle"></i> Accepted</span>
                @else
                <span style="color:#ef4444;"><i class="fas fa-times-circle"></i> Not accepted</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Banking --}}
    <div class="sla-show-card">
        <div class="sla-show-card-title"><i class="fas fa-university"></i> Banking &amp; Debit Order</div>
        <div class="sla-show-field">
            <div class="sla-show-label">Account Holder</div>
            <div class="sla-show-value {{ $sla->bank_account_holder ? '' : 'sla-empty' }}">{{ $sla->bank_account_holder ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Bank</div>
            <div class="sla-show-value {{ $sla->bank_name ? '' : 'sla-empty' }}">{{ $sla->bank_name ?: 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Account Number</div>
            <div class="sla-show-value {{ $sla->bank_account_number ? '' : 'sla-empty' }}">{{ $sla->bank_account_number ? '****' . substr($sla->bank_account_number, -4) : 'Not provided' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Debit Order Date</div>
            <div class="sla-show-value {{ $sla->debit_order_date ? '' : 'sla-empty' }}">{{ $sla->debit_order_date ?: 'Not set' }}</div>
        </div>
        <div class="sla-show-field">
            <div class="sla-show-label">Mandate Consent</div>
            <div class="sla-show-value">
                @if($sla->debit_order_consent)
                <span style="color:#10b981;"><i class="fas fa-check-circle"></i> Authorised</span>
                @else
                <span style="color:#ef4444;"><i class="fas fa-times-circle"></i> Not authorised</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Signature --}}
@if($sla->signature_data)
<div class="sla-show-card" style="margin-bottom:24px; max-width:600px;">
    <div class="sla-show-card-title"><i class="fas fa-signature"></i> Signature</div>
    <div class="sla-show-field">
        <div class="sla-show-label">Signed At</div>
        <div class="sla-show-value">{{ $sla->signed_at_location ?? 'Not specified' }} &mdash; {{ $sla->signed_date ? $sla->signed_date->format('j M Y') : 'Date not set' }}</div>
    </div>
    <div style="margin-top:12px;">
        <div class="sla-show-sig-box">
            @if($sla->signature_type === 'drawn')
                <img src="{{ $sla->signature_data }}" alt="Signature">
            @else
                <span class="sla-show-sig-typed">{{ $sla->signature_data }}</span>
            @endif
        </div>
    </div>
</div>
@endif

@endsection
