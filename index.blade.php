@extends('nexcore_client_manager::layouts.nerve-centre')

@section('title', 'Engagement Letters')
@section('topbar_page', 'Engagement Letters')

@push('styles')
<style>
/* ================================================================
   ATP SERVICES — SLA INDEX / LIST PAGE
   ================================================================ */

.sla-idx-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.sla-idx-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.sla-idx-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(6,182,212,0.15), rgba(6,182,212,0.05));
    border: 1px solid rgba(6,182,212,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sla-idx-icon i { color: #06b6d4; font-size: 20px; }
.sla-idx-title { font-size: 24px; font-weight: 900; color: #f1f5f9; letter-spacing: 1px; margin: 0; }
.sla-idx-sub { font-size: 13px; color: var(--text-muted, #64748b); margin-top: 2px; }
.sla-idx-new-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    background: linear-gradient(135deg, #06b6d4, #0ea5e9);
    color: #0a0e1a;
    text-decoration: none;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(6,182,212,0.3);
    transition: all 0.2s;
    letter-spacing: 0.5px;
}
.sla-idx-new-btn:hover {
    box-shadow: 0 6px 24px rgba(6,182,212,0.45);
    transform: translateY(-1px);
    color: #0a0e1a;
    text-decoration: none;
}

/* Stats */
.sla-idx-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 28px;
}
.sla-idx-stat {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.sla-idx-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.sla-idx-stat-num {
    font-size: 22px;
    font-weight: 900;
    color: #f1f5f9;
    line-height: 1;
}
.sla-idx-stat-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    margin-top: 2px;
}

/* SLA Cards */
.sla-idx-list { display: flex; flex-direction: column; gap: 10px; }

.sla-idx-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 18px;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
}
.sla-idx-card:hover {
    border-color: rgba(6,182,212,0.3);
    background: rgba(6,182,212,0.04);
    transform: translateX(3px);
    text-decoration: none;
}
.sla-idx-card-ref {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.8px;
    color: #06b6d4;
    background: rgba(6,182,212,0.1);
    border: 1px solid rgba(6,182,212,0.2);
    padding: 6px 12px;
    border-radius: 8px;
    white-space: nowrap;
    font-family: 'Montserrat', sans-serif;
}
.sla-idx-card-info { flex: 1; min-width: 0; }
.sla-idx-card-name {
    font-size: 15px;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sla-idx-card-meta {
    font-size: 12px;
    color: var(--text-muted, #64748b);
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.sla-idx-card-meta span { display: flex; align-items: center; gap: 5px; }
.sla-idx-card-meta i { font-size: 10px; }
.sla-idx-card-status {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 20px;
    white-space: nowrap;
}
.sla-idx-card-actions {
    display: flex;
    gap: 6px;
}
.sla-idx-action-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
    color: var(--text-muted, #64748b);
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    text-decoration: none;
}
.sla-idx-action-btn:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.2);
    color: #e2e8f0;
}

/* Empty State */
.sla-idx-empty {
    text-align: center;
    padding: 60px 20px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
}
.sla-idx-empty-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: rgba(6,182,212,0.08);
    border: 1px solid rgba(6,182,212,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
    color: rgba(6,182,212,0.4);
}
.sla-idx-empty-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-secondary, #94a3b8);
    margin-bottom: 8px;
}
.sla-idx-empty-sub {
    font-size: 13px;
    color: var(--text-muted, #64748b);
    max-width: 340px;
    margin: 0 auto;
    line-height: 1.6;
}

@@keyframes sla-idx-slide {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.sla-idx-card {
    animation: sla-idx-slide 0.3s ease both;
}
.sla-idx-card:nth-child(2) { animation-delay: 0.04s; }
.sla-idx-card:nth-child(3) { animation-delay: 0.08s; }
.sla-idx-card:nth-child(4) { animation-delay: 0.12s; }
.sla-idx-card:nth-child(5) { animation-delay: 0.16s; }
</style>
@endpush

@section('content')

<div class="sla-idx-header">
    <div class="sla-idx-header-left">
        <div class="sla-idx-icon"><i class="fas fa-file-contract"></i></div>
        <div>
            <h1 class="sla-idx-title">Engagement Letters</h1>
            <div class="sla-idx-sub">ATP Services &mdash; Service Level Agreements for {{ $client->company_name }}</div>
        </div>
    </div>
    <a href="{{ route('nexcore.clients.show.sla.create', $client->id) }}" class="sla-idx-new-btn">
        <i class="fas fa-plus"></i> New Engagement Letter
    </a>
</div>

{{-- Stats --}}
@php
    $totalSla = $slas->count();
    $activeSla = $slas->whereIn('status', ['signed', 'active'])->count();
    $draftSla = $slas->where('status', 'draft')->count();
    $terminatedSla = $slas->whereIn('status', ['terminated', 'expired'])->count();
@endphp
<div class="sla-idx-stats">
    <div class="sla-idx-stat">
        <div class="sla-idx-stat-icon" style="background:rgba(6,182,212,0.1); color:#06b6d4;"><i class="fas fa-file-contract"></i></div>
        <div>
            <div class="sla-idx-stat-num">{{ $totalSla }}</div>
            <div class="sla-idx-stat-label">Total</div>
        </div>
    </div>
    <div class="sla-idx-stat">
        <div class="sla-idx-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="sla-idx-stat-num">{{ $activeSla }}</div>
            <div class="sla-idx-stat-label">Active / Signed</div>
        </div>
    </div>
    <div class="sla-idx-stat">
        <div class="sla-idx-stat-icon" style="background:rgba(100,116,139,0.1); color:#64748b;"><i class="fas fa-pencil-alt"></i></div>
        <div>
            <div class="sla-idx-stat-num">{{ $draftSla }}</div>
            <div class="sla-idx-stat-label">Drafts</div>
        </div>
    </div>
    <div class="sla-idx-stat">
        <div class="sla-idx-stat-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;"><i class="fas fa-times-circle"></i></div>
        <div>
            <div class="sla-idx-stat-num">{{ $terminatedSla }}</div>
            <div class="sla-idx-stat-label">Terminated</div>
        </div>
    </div>
</div>

{{-- SLA List --}}
@if($slas->count())
<div class="sla-idx-list">
    @foreach($slas as $sla)
    @php $badge = $sla->status_badge; @endphp
    <div class="sla-idx-card" onclick="window.location='{{ route('nexcore.clients.show.sla.show', [$client->id, $sla->id]) }}'">
        <span class="sla-idx-card-ref">{{ $sla->sla_reference }}</span>
        <div class="sla-idx-card-info">
            <div class="sla-idx-card-name">{{ $sla->signatory_name ?: 'Unnamed' }}</div>
            <div class="sla-idx-card-meta">
                <span><i class="fas fa-box"></i> {{ ucfirst($sla->selected_package ?: 'No package') }}</span>
                <span><i class="fas fa-calendar"></i> {{ $sla->created_at->format('j M Y') }}</span>
                @if($sla->signed_date)
                <span><i class="fas fa-signature"></i> Signed {{ $sla->signed_date->format('j M Y') }}</span>
                @endif
            </div>
        </div>
        <span class="sla-idx-card-status" style="background: {{ $badge['color'] }}20; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['color'] }}40;">
            {{ $badge['label'] }}
        </span>
        <div class="sla-idx-card-actions" onclick="event.stopPropagation();">
            <a href="{{ route('nexcore.clients.show.sla.edit', [$client->id, $sla->id]) }}" class="sla-idx-action-btn" title="Edit"><i class="fas fa-pen"></i></a>
            <form method="POST" action="{{ route('nexcore.clients.show.sla.destroy', [$client->id, $sla->id]) }}" onsubmit="return confirm('Delete this engagement letter?');">
                @csrf @method('DELETE')
                <button type="submit" class="sla-idx-action-btn" title="Delete" style="color:#ef4444;"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="sla-idx-empty">
    <div class="sla-idx-empty-icon"><i class="fas fa-file-contract"></i></div>
    <div class="sla-idx-empty-title">No Engagement Letters Yet</div>
    <div class="sla-idx-empty-sub">Create the first Service Level Agreement for this client by clicking the button above.</div>
</div>
@endif

@endsection
