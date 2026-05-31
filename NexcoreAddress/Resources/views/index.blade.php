@extends('nexcore_address::layouts.app')

@section('title', 'Address Registry')

@section('content')
<style>
    /* ── Address Index Styles ── */
    .nx-addr-table { width:100%; border-collapse:collapse; }
    .nx-addr-table th,
    .nx-addr-table td { padding:12px 14px; text-align:left; font-size:13px; font-family:'Montserrat',sans-serif; border-bottom:1px solid rgba(255,255,255,0.06); }
    .nx-addr-table th { font-size:11px; font-weight:700; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1px; }
    .nx-addr-table tbody tr { transition:background 0.2s ease; }
    .nx-addr-table tbody tr:hover { background:rgba(255,255,255,0.03); }
    .nx-addr-table .center { text-align:center; }

    .nx-addr-stat-grid {
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:16px;
        margin-bottom:24px;
    }
    @@media (max-width:900px) {
        .nx-addr-stat-grid { grid-template-columns:repeat(2, 1fr); }
    }
    @@media (max-width:520px) {
        .nx-addr-stat-grid { grid-template-columns:1fr; }
    }

    .nx-addr-stat-icon {
        width:44px; height:44px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:18px; flex-shrink:0;
    }

    .nx-addr-category-badge {
        display:inline-block; padding:3px 10px; font-size:10px; font-weight:700;
        letter-spacing:0.5px; text-transform:uppercase; border-radius:4px;
    }

    .nx-addr-status-dot {
        display:inline-block; width:8px; height:8px; border-radius:50%;
        margin-right:6px; vertical-align:middle;
    }
    .nx-addr-status-dot.active { background:#22c55e; box-shadow:0 0 6px rgba(34,197,94,0.5); }
    .nx-addr-status-dot.inactive { background:#ef4444; box-shadow:0 0 6px rgba(239,68,68,0.5); }

    .nx-addr-link-count {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:24px; height:24px; border-radius:6px; font-size:12px; font-weight:700;
        padding:0 6px;
    }

    /* Fade-in animation */
    .nx-addr-animate { opacity:0; transform:translateY(12px); animation:nxAddrFadeIn 0.4s ease forwards; }
    .nx-addr-d1 { animation-delay:0.05s; }
    .nx-addr-d2 { animation-delay:0.1s; }
    .nx-addr-d3 { animation-delay:0.15s; }
    .nx-addr-d4 { animation-delay:0.2s; }
    .nx-addr-d5 { animation-delay:0.25s; }

    @@keyframes nxAddrFadeIn {
        to { opacity:1; transform:translateY(0); }
    }
</style>

{{-- ─── 1. PAGE HEADER ─── --}}
@include('nexcore_formkit::components.page-header', [
    'title'    => 'Address Registry',
    'subtitle' => 'Manage all addresses across the system',
    'buttons'  => [
        [
            'label' => 'New Address',
            'route' => route('nexcore.addresses.create'),
            'icon'  => 'fas fa-plus',
            'color' => 'primary',
        ],
    ],
])

{{-- ─── 2. SESSION ALERTS ─── --}}
@if(session('success'))
    @include('nexcore_formkit::components.alert', [
        'type'    => 'success',
        'message' => session('success'),
    ])
@endif
@if(session('error'))
    @include('nexcore_formkit::components.alert', [
        'type'    => 'danger',
        'message' => session('error'),
    ])
@endif

{{-- ─── 3. STATS ROW ─── --}}
@php
    $totalCount      = $addresses->total();
    $activeCount     = $addresses->getCollection()->where('is_active', true)->count();
    $categoryCount   = $addresses->getCollection()->pluck('address_category')->filter()->unique()->count();
    $linkedCount     = $addresses->getCollection()->filter(fn($a) => $a->links->count() > 0)->count();
@endphp
<div class="nx-addr-stat-grid nx-addr-animate nx-addr-d2">
    {{-- Total Addresses --}}
    <div class="nxfk-glass" style="padding:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="nx-addr-stat-icon" style="color:#06b6d4; border:1px solid rgba(6,182,212,0.2); background:rgba(6,182,212,0.1); box-shadow:0 0 10px rgba(6,182,212,0.1);">
                <i class="fas fa-map-marker-alt"></i>
            </span>
            <div>
                <div style="font-size:24px; font-weight:700; color:#fff; font-family:'Montserrat',sans-serif;">{{ number_format($totalCount) }}</div>
                <div style="font-size:12px; color:var(--nx-text-muted, rgba(255,255,255,0.45)); font-family:'Montserrat',sans-serif;">Total Addresses</div>
            </div>
        </div>
    </div>

    {{-- Active --}}
    <div class="nxfk-glass" style="padding:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="nx-addr-stat-icon" style="color:#22c55e; border:1px solid rgba(34,197,94,0.2); background:rgba(34,197,94,0.1); box-shadow:0 0 10px rgba(34,197,94,0.1);">
                <i class="fas fa-check-circle"></i>
            </span>
            <div>
                <div style="font-size:24px; font-weight:700; color:#fff; font-family:'Montserrat',sans-serif;">{{ number_format($activeCount) }}</div>
                <div style="font-size:12px; color:var(--nx-text-muted, rgba(255,255,255,0.45)); font-family:'Montserrat',sans-serif;">Active</div>
            </div>
        </div>
    </div>

    {{-- Categories --}}
    <div class="nxfk-glass" style="padding:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="nx-addr-stat-icon" style="color:#f59e0b; border:1px solid rgba(245,158,11,0.2); background:rgba(245,158,11,0.1); box-shadow:0 0 10px rgba(245,158,11,0.1);">
                <i class="fas fa-th-large"></i>
            </span>
            <div>
                <div style="font-size:24px; font-weight:700; color:#fff; font-family:'Montserrat',sans-serif;">{{ number_format($categoryCount) }}</div>
                <div style="font-size:12px; color:var(--nx-text-muted, rgba(255,255,255,0.45)); font-family:'Montserrat',sans-serif;">Categories</div>
            </div>
        </div>
    </div>

    {{-- Linked --}}
    <div class="nxfk-glass" style="padding:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="nx-addr-stat-icon" style="color:#8b5cf6; border:1px solid rgba(139,92,246,0.2); background:rgba(139,92,246,0.1); box-shadow:0 0 10px rgba(139,92,246,0.1);">
                <i class="fas fa-link"></i>
            </span>
            <div>
                <div style="font-size:24px; font-weight:700; color:#fff; font-family:'Montserrat',sans-serif;">{{ number_format($linkedCount) }}</div>
                <div style="font-size:12px; color:var(--nx-text-muted, rgba(255,255,255,0.45)); font-family:'Montserrat',sans-serif;">Linked</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── 4. SEARCH & FILTER BAR ─── --}}
@php
    $categoryOptions = [
        'Residential'  => 'Residential',
        'Commercial'   => 'Commercial',
        'Industrial'   => 'Industrial',
        'Agricultural' => 'Agricultural',
        'Mixed Use'    => 'Mixed Use',
    ];
    $provinceOptions = $provinces->pluck('name', 'id')->toArray();
@endphp
@include('nexcore_formkit::components.search-bar', [
    'action'       => route('nexcore.addresses.index'),
    'searchValue'  => request('search'),
    'placeholder'  => 'Search street, city, postal code...',
    'clearUrl'     => route('nexcore.addresses.index'),
    'filters'      => [
        [
            'label'       => 'Province',
            'name'        => 'province',
            'placeholder' => 'All Provinces',
            'options'     => $provinceOptions,
        ],
        [
            'label'       => 'Category',
            'name'        => 'category',
            'placeholder' => 'All Categories',
            'options'     => $categoryOptions,
        ],
    ],
])

{{-- ─── 5. ADDRESS TABLE ─── --}}
<div class="nx-addr-animate nx-addr-d4">
    @include('nexcore_formkit::components.card-start', [
        'title'       => 'Addresses',
        'icon'        => 'fas fa-map-marker-alt',
        'accentColor' => '#06b6d4',
    ])

    @if($addresses->count() > 0)
        <div style="font-size:13px; color:var(--nx-text-muted, rgba(255,255,255,0.45)); margin-bottom:14px; font-family:'Montserrat',sans-serif;">
            Showing {{ $addresses->firstItem() }}–{{ $addresses->lastItem() }} of {{ $addresses->total() }} addresses
        </div>

        <div style="overflow-x:auto;">
            <table class="nx-addr-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Address</th>
                        <th>Suburb</th>
                        <th>City</th>
                        <th>Province</th>
                        <th>Postal Code</th>
                        <th class="center">Category</th>
                        <th class="center">Links</th>
                        <th class="center">Status</th>
                        <th class="center" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($addresses as $idx => $address)
                    @php
                        // Build display address
                        $addrLine = '';
                        if ($address->unit_number) {
                            $addrLine .= 'Unit ' . $address->unit_number . ', ';
                        }
                        if ($address->complex_name) {
                            $addrLine .= $address->complex_name . ', ';
                        }
                        $addrLine .= trim($address->street_number . ' ' . $address->street_name);

                        // Category badge colours
                        $catColors = [
                            'Residential'  => ['color' => '#22c55e', 'bg' => 'rgba(34,197,94,0.1)',  'border' => 'rgba(34,197,94,0.3)'],
                            'Commercial'   => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.1)',  'border' => 'rgba(6,182,212,0.3)'],
                            'Industrial'   => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)', 'border' => 'rgba(245,158,11,0.3)'],
                            'Agricultural' => ['color' => '#a78bfa', 'bg' => 'rgba(167,139,250,0.1)','border' => 'rgba(167,139,250,0.3)'],
                            'Mixed Use'    => ['color' => '#ec4899', 'bg' => 'rgba(236,72,153,0.1)', 'border' => 'rgba(236,72,153,0.3)'],
                        ];
                        $cat = $address->address_category ?? '';
                        $catStyle = $catColors[$cat] ?? ['color' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.1)', 'border' => 'rgba(148,163,184,0.3)'];
                        $linkCount = $address->links->count();
                    @endphp
                    <tr>
                        {{-- # --}}
                        <td style="color:var(--nx-text-muted, rgba(255,255,255,0.35)); font-size:12px;">{{ $addresses->firstItem() + $idx }}</td>

                        {{-- Address --}}
                        <td>
                            <div style="font-weight:600; color:var(--text-primary, #fff); font-family:'Montserrat',sans-serif;">{{ $addrLine }}</div>
                        </td>

                        {{-- Suburb --}}
                        <td style="color:var(--text-primary, rgba(255,255,255,0.85)); font-family:'Montserrat',sans-serif;">
                            {{ $address->suburb->name ?? '—' }}
                        </td>

                        {{-- City --}}
                        <td style="color:var(--text-primary, rgba(255,255,255,0.85)); font-family:'Montserrat',sans-serif;">
                            {{ $address->city ?? '—' }}
                        </td>

                        {{-- Province --}}
                        <td style="color:var(--text-primary, rgba(255,255,255,0.85)); font-family:'Montserrat',sans-serif;">
                            {{ $address->province->name ?? '—' }}
                        </td>

                        {{-- Postal Code --}}
                        <td style="font-family:'Montserrat',sans-serif; font-weight:600; color:var(--accent-cyan, #06b6d4);">
                            {{ $address->postal_code ?? '—' }}
                        </td>

                        {{-- Category --}}
                        <td class="center">
                            @if($cat)
                                <span class="nx-addr-category-badge" style="color:{{ $catStyle['color'] }}; background:{{ $catStyle['bg'] }}; border:1px solid {{ $catStyle['border'] }};">
                                    {{ $cat }}
                                </span>
                            @else
                                <span style="color:var(--nx-text-muted, rgba(255,255,255,0.35));">—</span>
                            @endif
                        </td>

                        {{-- Links --}}
                        <td class="center">
                            @if($linkCount > 0)
                                <span class="nx-addr-link-count" style="color:#8b5cf6; background:rgba(139,92,246,0.12); border:1px solid rgba(139,92,246,0.3);">{{ $linkCount }}</span>
                            @else
                                <span style="color:var(--nx-text-muted, rgba(255,255,255,0.25));">0</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="center">
                            @if($address->is_active)
                                @include('nexcore_formkit::components.badge', ['type' => 'emerald', 'text' => 'Active'])
                            @else
                                @include('nexcore_formkit::components.badge', ['type' => 'red', 'text' => 'Inactive'])
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="center">
                            @include('nexcore_formkit::components.action-buttons', [
                                'editRoute'   => route('nexcore.addresses.edit', $address->id),
                                'toggleRoute' => route('nexcore.addresses.toggle', $address->id),
                                'toggleState' => $address->is_active,
                                'deleteRoute' => route('nexcore.addresses.destroy', $address->id),
                                'deleteLabel' => 'this address',
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ─── 6. PAGINATION ─── --}}
        @include('nexcore_formkit::components.pagination', [
            'paginator' => $addresses->appends(request()->query()),
        ])

    @else
        {{-- ─── 7. EMPTY STATE ─── --}}
        @include('nexcore_formkit::components.empty-state', [
            'icon'        => 'fas fa-map-marker-alt',
            'title'       => 'No addresses found',
            'text'        => 'There are no addresses matching your criteria. Create a new address to get started.',
            'buttonLabel' => 'New Address',
            'buttonRoute' => route('nexcore.addresses.create'),
            'buttonIcon'  => 'fas fa-plus',
        ])
    @endif

    @include('nexcore_formkit::components.card-end')
</div>
@endsection
