@extends('nexcore_address::layouts.app')

@php
    $isEdit      = isset($address) && $address->id;
    $isLinking   = isset($linkableType) && isset($linkableId);
    $isEditLink  = isset($link) && $link->id;
    $showLink    = $isLinking || $isEditLink;
    $details     = $isEdit ? ($address->details ?? null) : null;

    // Form action
    if ($isEditLink) {
        $formAction = route('nexcore.addresses.update-link', $link->id);
    } elseif ($isEdit) {
        $formAction = route('nexcore.addresses.update', $address->id);
    } elseif ($isLinking) {
        $formAction = route('nexcore.addresses.store-for-entity', [$linkableType, $linkableId]);
    } else {
        $formAction = route('nexcore.addresses.store');
    }
@endphp

@section('title', $isEdit ? 'Edit Address' : 'New Address')

@section('content')

@push('styles')
<style>
    /* ── Address form page-level overrides ── */
    .nx-addr-form { font-family: 'Montserrat', sans-serif; }

    /* Grid helpers */
    .nx-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .nx-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .nx-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .nx-grid-1 { display: grid; grid-template-columns: 1fr; gap: 16px; }
    @@media (max-width: 768px) {
        .nx-grid-4, .nx-grid-3, .nx-grid-2 { grid-template-columns: 1fr; }
    }

    /* Registry search results */
    .nx-addr-registry-results {
        max-height: 280px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px; background: rgba(0,0,0,0.25); margin-top: 8px;
    }
    .nx-addr-registry-results:empty { display: none; }
    .nx-addr-registry-item {
        padding: 10px 14px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .nx-addr-registry-item:hover { background: rgba(6,182,212,0.08); }
    .nx-addr-registry-item:last-child { border-bottom: none; }
    .nx-addr-registry-line1 { font-size: 13px; font-weight: 600; color: #e2e8f0; }
    .nx-addr-registry-line2 { font-size: 11px; color: rgba(255,255,255,0.45); margin-top: 2px; }

    /* Selected address banner */
    .nx-addr-selected {
        display: flex; align-items: center; gap: 12px; padding: 12px 16px;
        background: rgba(6,182,212,0.08); border: 1px solid rgba(6,182,212,0.25);
        border-radius: 8px; margin-top: 10px;
    }
    .nx-addr-selected-text { flex: 1; font-size: 13px; color: #e2e8f0; }
    .nx-addr-selected-clear {
        background: none; border: none; color: #ef4444; cursor: pointer;
        font-size: 13px; padding: 4px 8px; border-radius: 4px;
    }
    .nx-addr-selected-clear:hover { background: rgba(239,68,68,0.1); }

    /* Suburb autocomplete */
    .nx-suburb-results {
        position: absolute; z-index: 100; top: 100%; left: 0; right: 0;
        max-height: 200px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.12);
        border-radius: 6px; background: #1a1f2e; box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    }
    .nx-suburb-results:empty { display: none; }
    .nx-suburb-item {
        padding: 8px 12px; cursor: pointer; font-size: 12px; color: #e2e8f0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .nx-suburb-item:hover { background: rgba(6,182,212,0.1); }
    .nx-suburb-item:last-child { border-bottom: none; }

    /* GPS coordinate display */
    .nx-gps-display {
        display: flex; align-items: center; gap: 8px; padding: 10px 14px;
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
        border-radius: 6px; font-size: 13px; color: rgba(255,255,255,0.6); min-height: 42px;
    }
    .nx-gps-display i { color: #06b6d4; }

    /* Address category radio pills */
    .nx-cat-pills { display: flex; flex-wrap: wrap; gap: 8px; }
    .nx-cat-pill {
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
        border: 1px solid rgba(255,255,255,0.12); border-radius: 20px;
        background: rgba(255,255,255,0.03); color: rgba(255,255,255,0.6);
        font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        font-family: 'Montserrat', sans-serif;
    }
    .nx-cat-pill:hover { border-color: rgba(6,182,212,0.3); color: #e2e8f0; }
    .nx-cat-pill.active {
        background: rgba(6,182,212,0.12); border-color: #06b6d4;
        color: #06b6d4; box-shadow: 0 0 12px rgba(6,182,212,0.15);
    }
    .nx-cat-pill input[type="radio"] { display: none; }

    /* Collapsible extended details */
    .nx-collapse-toggle {
        display: flex; align-items: center; gap: 8px; cursor: pointer;
        user-select: none; padding: 0; margin-bottom: 4px;
    }
    .nx-collapse-toggle .nx-chevron {
        transition: transform 0.25s; font-size: 10px; color: rgba(255,255,255,0.4);
    }
    .nx-collapse-toggle.open .nx-chevron { transform: rotate(90deg); }
    .nx-collapse-body {
        overflow: hidden; max-height: 0; opacity: 0;
        transition: max-height 0.35s ease, opacity 0.25s ease;
    }
    .nx-collapse-body.open { max-height: 2000px; opacity: 1; }

    /* Detail sub-section labels */
    .nx-detail-sublabel {
        font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
        color: rgba(255,255,255,0.35); margin: 16px 0 8px; padding-bottom: 4px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .nx-detail-sublabel:first-child { margin-top: 0; }

    /* Google search hint */
    .nx-hint {
        font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 6px;
        font-style: italic;
    }

    /* Button row */
    .nx-addr-actions {
        display: flex; gap: 12px; justify-content: flex-end;
        padding-top: 8px; padding-bottom: 40px;
    }

    /* Shimmer loading for registry search */
    .nx-addr-registry-loading {
        padding: 14px; text-align: center; font-size: 12px;
        color: rgba(255,255,255,0.35);
    }

    /* Pulse animation for Google search icon */
    @@keyframes nxAddrPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .nx-google-searching i { animation: nxAddrPulse 1.2s ease-in-out infinite; }
</style>
@endpush

<div class="nx-addr-form">

    {{-- ── PAGE HEADER ── --}}
    @include('nexcore_formkit::components.page-header', [
        'title'    => $isEdit ? 'Edit Address' : 'New Address',
        'subtitle' => $isEdit ? 'Update the address record in the registry' : ($isLinking ? 'Link an existing or create a new address' : 'Add a new address to the registry'),
        'buttons'  => [
            ['label' => 'Back', 'icon' => 'fas fa-arrow-left', 'color' => 'ghost', 'route' => ($isLinking || $isEditLink) ? (request()->input('return_url', route('nexcore.addresses.index'))) : route('nexcore.addresses.index')],
        ],
    ])

    {{-- ── VALIDATION ERRORS ── --}}
    @if($errors->any())
        @include('nexcore_formkit::components.alert', [
            'type'    => 'danger',
            'title'   => 'Validation Error',
            'message' => implode('<br>', $errors->all()),
        ])
    @endif

    {{-- ── FORM ── --}}
    <form method="POST" action="{{ $formAction }}" id="nxAddrForm" autocomplete="off">
        @csrf
        @if($isEdit || $isEditLink) @method('PUT') @endif

        {{-- Hidden fields --}}
        <input type="hidden" name="existing_address_id" id="existingAddressId" value="{{ old('existing_address_id', '') }}">
        @if(request()->has('return_url'))
            <input type="hidden" name="return_url" value="{{ request()->input('return_url') }}">
        @endif
        <input type="hidden" name="latitude" id="addrLat" value="{{ old('latitude', $address->latitude ?? '') }}">
        <input type="hidden" name="longitude" id="addrLng" value="{{ old('longitude', $address->longitude ?? '') }}">
        <input type="hidden" name="suburb_id" id="suburbId" value="{{ old('suburb_id', $address->suburb_id ?? '') }}">
        <input type="hidden" name="municipality_id" id="municipalityId" value="{{ old('municipality_id', $address->municipality_id ?? '') }}">
        <input type="hidden" name="ward_id" id="wardId" value="{{ old('ward_id', $address->ward_id ?? '') }}">


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 1. LINK DETAILS (only when linking to an entity)          --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($showLink)
        @include('nexcore_formkit::components.card-start', [
            'title'       => 'Link Details',
            'icon'        => 'fas fa-link',
            'accentColor' => '#a78bfa',
        ])
            <div class="nx-grid-3">
                @include('nexcore_formkit::components.form-select', [
                    'name'        => 'address_type_id',
                    'label'       => 'Address Type',
                    'options'     => $addressTypes,
                    'optionValue' => 'id',
                    'optionLabel' => 'name',
                    'selected'    => old('address_type_id', $isEditLink ? $link->address_type_id : ''),
                    'required'    => true,
                ])

                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'address_label',
                    'label'       => 'Address Label',
                    'value'       => old('address_label', $isEditLink ? $link->address_label : ''),
                    'placeholder' => 'e.g. Head Office, Factory, Depot...',
                ])

                @include('nexcore_formkit::components.form-toggle', [
                    'name'    => 'is_primary',
                    'label'   => 'Primary Address',
                    'checked' => old('is_primary', $isEditLink ? $link->is_primary : false),
                ])
            </div>
        @include('nexcore_formkit::components.card-end')
        @endif


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 2. SEARCH EXISTING (only on create mode when linking)     --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($isLinking && !$isEdit)
        @include('nexcore_formkit::components.card-start', [
            'title'       => 'Search Existing Addresses',
            'icon'        => 'fas fa-search',
            'accentColor' => '#f59e0b',
        ])
            <div class="nx-grid-1">
                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'registry_search',
                    'label'       => 'Search the Address Registry',
                    'id'          => 'registrySearchInput',
                    'placeholder' => 'Type to search by street, city, postal code...',
                ])
            </div>

            <div id="registryResults" class="nx-addr-registry-results"></div>

            <div id="selectedAddressBanner" class="nx-addr-selected" style="display:none;">
                <i class="fas fa-check-circle" style="color:#06b6d4; font-size:16px;"></i>
                <div class="nx-addr-selected-text">
                    <div id="selectedAddressLine1" style="font-weight:600;"></div>
                    <div id="selectedAddressLine2" style="font-size:11px; color:rgba(255,255,255,0.45); margin-top:2px;"></div>
                </div>
                <button type="button" class="nx-addr-selected-clear" onclick="clearSelectedAddress()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>

            <div class="nx-hint" style="margin-top:8px;">
                <i class="fas fa-info-circle"></i>
                Select an existing address to link it, or skip this section to create a new one.
            </div>
        @include('nexcore_formkit::components.card-end')
        @endif


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 3. GOOGLE QUICK SEARCH                                    --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div id="newAddressFields">

        @include('nexcore_formkit::components.card-start', [
            'title'       => 'Google Quick Search',
            'icon'        => 'fab fa-google',
            'accentColor' => '#22c55e',
        ])
            <div class="nx-grid-1">
                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'google_search',
                    'label'       => 'Search Google Places',
                    'id'          => 'googlePlacesInput',
                    'placeholder' => 'Start typing an address...',
                ])
            </div>
            <div class="nx-hint">
                <i class="fas fa-magic"></i>
                Select a result to auto-fill the fields below, or fill them manually.
            </div>
        @include('nexcore_formkit::components.card-end')


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 4. MAIN ADDRESS                                           --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @include('nexcore_formkit::components.card-start', [
            'title'       => 'Main Address',
            'icon'        => 'fas fa-map-marker-alt',
            'accentColor' => '#06b6d4',
        ])

            {{-- Row 1: Unit, Complex, Street Number, Street Name --}}
            <div class="nx-grid-4">
                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'unit_number',
                    'label'       => 'Unit Number',
                    'value'       => old('unit_number', $address->unit_number ?? ''),
                    'placeholder' => 'e.g. 12A',
                ])

                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'complex_name',
                    'label'       => 'Complex / Estate Name',
                    'value'       => old('complex_name', $address->complex_name ?? ''),
                    'placeholder' => 'e.g. Waterfall Ridge',
                ])

                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'street_number',
                    'label'       => 'Street Number',
                    'value'       => old('street_number', $address->street_number ?? ''),
                    'placeholder' => 'e.g. 42',
                    'required'    => true,
                ])

                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'street_name',
                    'label'       => 'Street Name',
                    'value'       => old('street_name', $address->street_name ?? ''),
                    'placeholder' => 'e.g. Mandela Drive',
                    'required'    => true,
                ])
            </div>

            {{-- Row 2: Suburb (AJAX), City, Postal Code --}}
            <div class="nx-grid-3" style="margin-top: 16px;">
                <div class="nxfk-field" style="position:relative;">
                    <label class="nxfk-label" for="suburbSearch">Suburb</label>
                    <input type="text" id="suburbSearch" class="nxfk-input" placeholder="Start typing suburb..."
                        value="{{ old('suburb_name', ($isEdit && $address->suburb) ? $address->suburb->name : '') }}"
                        autocomplete="off">
                    <div id="suburbResults" class="nx-suburb-results"></div>
                </div>

                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'city',
                    'label'       => 'City',
                    'value'       => old('city', $address->city ?? ''),
                    'placeholder' => 'e.g. Johannesburg',
                    'required'    => true,
                ])

                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'postal_code',
                    'label'       => 'Postal Code',
                    'value'       => old('postal_code', $address->postal_code ?? ''),
                    'placeholder' => 'e.g. 2000',
                    'required'    => true,
                ])
            </div>

            {{-- Row 3: Province, Municipality, Ward --}}
            <div class="nx-grid-3" style="margin-top: 16px;">
                @include('nexcore_formkit::components.form-select', [
                    'name'        => 'province_id',
                    'label'       => 'Province',
                    'id'          => 'provinceSelect',
                    'options'     => $provinces,
                    'optionValue' => 'id',
                    'optionLabel' => 'name',
                    'selected'    => old('province_id', $address->province_id ?? ''),
                    'required'    => true,
                    'placeholder' => '-- Select Province --',
                ])

                <div class="nxfk-field">
                    <label class="nxfk-label" for="municipalitySelect">Municipality</label>
                    <select name="municipality_display" id="municipalitySelect" class="nxfk-select">
                        <option value="">-- Select Province First --</option>
                        @if($isEdit && $address->municipality_id)
                            <option value="{{ $address->municipality_id }}" selected>{{ $address->municipality_name ?? 'Selected' }}</option>
                        @endif
                    </select>
                </div>

                <div class="nxfk-field">
                    <label class="nxfk-label" for="wardSelect">Ward</label>
                    <select name="ward_display" id="wardSelect" class="nxfk-select">
                        <option value="">-- Select Municipality First --</option>
                        @if($isEdit && $address->ward_id && $address->ward)
                            <option value="{{ $address->ward_id }}" selected>{{ $address->ward->name ?? 'Selected' }}</option>
                        @endif
                    </select>
                </div>
            </div>

            {{-- Row 4: Country, Address Category --}}
            <div class="nx-grid-2" style="margin-top: 16px;">
                @include('nexcore_formkit::components.form-input', [
                    'name'        => 'country',
                    'label'       => 'Country',
                    'value'       => old('country', $address->country ?? 'South Africa'),
                ])

                <div class="nxfk-field">
                    <label class="nxfk-label">
                        Address Category <span class="nxfk-required" style="color: #ef4444;">*</span>
                    </label>
                    @php
                        $currentCategory = old('address_category', $address->address_category ?? 'Residential');
                    @endphp
                    <div class="nx-cat-pills">
                        @foreach(['Residential', 'Commercial', 'Industrial', 'Agricultural', 'Mixed Use'] as $cat)
                        <label class="nx-cat-pill {{ $currentCategory === $cat ? 'active' : '' }}" data-cat="{{ $cat }}">
                            <input type="radio" name="address_category" value="{{ $cat }}" {{ $currentCategory === $cat ? 'checked' : '' }}>
                            @php
                                $catIcons = [
                                    'Residential'  => 'fas fa-home',
                                    'Commercial'   => 'fas fa-building',
                                    'Industrial'   => 'fas fa-industry',
                                    'Agricultural' => 'fas fa-tractor',
                                    'Mixed Use'    => 'fas fa-layer-group',
                                ];
                            @endphp
                            <i class="{{ $catIcons[$cat] ?? 'fas fa-map-pin' }}"></i>
                            {{ $cat }}
                        </label>
                        @endforeach
                    </div>
                    @if($errors->has('address_category'))
                        <span class="nxfk-field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('address_category') }}</span>
                    @endif
                </div>
            </div>

            {{-- Row 5: Google Formatted Address, GPS Coordinates --}}
            <div class="nx-grid-2" style="margin-top: 16px;">
                @include('nexcore_formkit::components.form-textarea', [
                    'name'        => 'google_formatted_address',
                    'label'       => 'Google Formatted Address',
                    'value'       => old('google_formatted_address', $address->google_formatted_address ?? ''),
                    'rows'        => 2,
                    'placeholder' => 'Auto-filled from Google Places',
                ])

                <div class="nxfk-field">
                    <label class="nxfk-label">GPS Coordinates</label>
                    <div class="nx-gps-display" id="gpsDisplay">
                        <i class="fas fa-crosshairs"></i>
                        <span id="gpsText">
                            @if($isEdit && $address->latitude && $address->longitude)
                                {{ $address->latitude }}, {{ $address->longitude }}
                            @else
                                No coordinates captured
                            @endif
                        </span>
                    </div>
                </div>
            </div>

        @include('nexcore_formkit::components.card-end')


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 5. EXTENDED DETAILS (collapsible)                         --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @include('nexcore_formkit::components.card-start', [
            'title'       => 'Extended Details',
            'icon'        => 'fas fa-layer-group',
            'accentColor' => '#f59e0b',
        ])

            <div class="nx-collapse-toggle" id="extendedToggle" onclick="toggleExtended()">
                <i class="fas fa-chevron-right nx-chevron"></i>
                <span style="font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.5);">
                    Click to {{ ($details && ($details->floor_level || $details->building_name || $details->farm_name || $details->erf_number || $details->plus_code)) ? 'show' : 'expand' }} additional fields
                </span>
                @if($details && ($details->floor_level || $details->building_name || $details->farm_name || $details->erf_number || $details->plus_code))
                    @include('nexcore_formkit::components.badge', ['type' => 'amber', 'text' => 'Has Data'])
                @endif
            </div>

            <div class="nx-collapse-body" id="extendedBody">

                {{-- Property Details --}}
                <div class="nx-detail-sublabel"><i class="fas fa-building" style="margin-right:6px; color:#06b6d4;"></i> Property</div>
                <div class="nx-grid-4">
                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'floor_level',
                        'label'       => 'Floor / Level',
                        'value'       => old('floor_level', $details->floor_level ?? ''),
                        'placeholder' => 'e.g. 3rd Floor',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'building_name',
                        'label'       => 'Building Name',
                        'value'       => old('building_name', $details->building_name ?? ''),
                        'placeholder' => 'e.g. Sandton Tower',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'estate_name',
                        'label'       => 'Security Estate',
                        'value'       => old('estate_name', $details->estate_name ?? ''),
                        'placeholder' => 'e.g. Dainfern Estate',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'section_number',
                        'label'       => 'Section Number',
                        'value'       => old('section_number', $details->section_number ?? ''),
                        'placeholder' => 'e.g. SS 42/2001',
                    ])
                </div>

                {{-- Farm / Rural --}}
                <div class="nx-detail-sublabel"><i class="fas fa-tractor" style="margin-right:6px; color:#22c55e;"></i> Farm / Rural</div>
                <div class="nx-grid-3">
                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'farm_name',
                        'label'       => 'Farm Name',
                        'value'       => old('farm_name', $details->farm_name ?? ''),
                        'placeholder' => 'e.g. Welgevonden',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'farm_number',
                        'label'       => 'Farm Number',
                        'value'       => old('farm_number', $details->farm_number ?? ''),
                        'placeholder' => 'e.g. 123/4',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'stand_number',
                        'label'       => 'Stand Number',
                        'value'       => old('stand_number', $details->stand_number ?? ''),
                        'placeholder' => 'e.g. Stand 456',
                    ])
                </div>

                {{-- Government / Registry --}}
                <div class="nx-detail-sublabel"><i class="fas fa-landmark" style="margin-right:6px; color:#a78bfa;"></i> Government / Registry</div>
                <div class="nx-grid-3">
                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'erf_number',
                        'label'       => 'ERF Number',
                        'value'       => old('erf_number', $details->erf_number ?? ''),
                        'placeholder' => 'e.g. ERF 789',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'sg_code',
                        'label'       => 'SG Code',
                        'value'       => old('sg_code', $details->sg_code ?? ''),
                        'placeholder' => 'Surveyor General code',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'municipal_account_number',
                        'label'       => 'Municipal Account',
                        'value'       => old('municipal_account_number', $details->municipal_account_number ?? ''),
                        'placeholder' => 'Municipal account number',
                    ])
                </div>

                {{-- Digital / Location --}}
                <div class="nx-detail-sublabel"><i class="fas fa-satellite" style="margin-right:6px; color:#38bdf8;"></i> Digital</div>
                <div class="nx-grid-3">
                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'plus_code',
                        'label'       => 'Plus Code',
                        'value'       => old('plus_code', $details->plus_code ?? ''),
                        'placeholder' => 'e.g. 5G8Q+3W Sandton',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'what3words',
                        'label'       => 'What3Words',
                        'value'       => old('what3words', $details->what3words ?? ''),
                        'placeholder' => 'e.g. ///filled.count.soap',
                    ])

                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'google_place_id',
                        'label'       => 'Google Place ID',
                        'value'       => old('google_place_id', $details->google_place_id ?? ''),
                        'placeholder' => 'Auto-filled from Google',
                        'id'          => 'googlePlaceId',
                    ])
                </div>

                <div class="nx-grid-2" style="margin-top: 16px;">
                    @include('nexcore_formkit::components.form-input', [
                        'name'        => 'map_url',
                        'label'       => 'Map URL',
                        'value'       => old('map_url', $details->map_url ?? ''),
                        'placeholder' => 'https://maps.google.com/...',
                        'type'        => 'url',
                    ])

                    @include('nexcore_formkit::components.form-select', [
                        'name'        => 'address_source',
                        'label'       => 'Address Source',
                        'options'     => [
                            ['id' => 'manual',        'name' => 'Manual Entry'],
                            ['id' => 'google_places', 'name' => 'Google Places'],
                            ['id' => 'import',        'name' => 'Imported'],
                            ['id' => 'client_form',   'name' => 'Client Form'],
                            ['id' => 'government',    'name' => 'Government Record'],
                            ['id' => 'verified',      'name' => 'Verified / Surveyed'],
                        ],
                        'optionValue' => 'id',
                        'optionLabel' => 'name',
                        'selected'    => old('address_source', $details->address_source ?? ''),
                        'placeholder' => '-- Select Source --',
                    ])
                </div>
            </div>

        @include('nexcore_formkit::components.card-end')


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 6. NOTES (only when linking)                              --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if($showLink)
        @include('nexcore_formkit::components.card-start', [
            'title'       => 'Notes',
            'icon'        => 'fas fa-sticky-note',
            'accentColor' => '#f472b6',
        ])
            @include('nexcore_formkit::components.form-textarea', [
                'name'        => 'notes',
                'label'       => 'Link Notes',
                'value'       => old('notes', $isEditLink ? $link->notes : ''),
                'rows'        => 3,
                'placeholder' => 'Any notes about this address link...',
            ])
        @include('nexcore_formkit::components.card-end')
        @endif

        </div>{{-- #newAddressFields --}}


        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- 7. ACTION BUTTONS                                         --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="nx-addr-actions">
            <a href="{{ ($isLinking || $isEditLink) ? (request()->input('return_url', route('nexcore.addresses.index'))) : route('nexcore.addresses.index') }}" class="nxfk-btn nxfk-btn-ghost">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="nxfk-btn nxfk-btn-primary" id="submitBtn">
                <i class="fas fa-save"></i> {{ $isEdit || $isEditLink ? 'Update Address' : ($isLinking ? 'Save & Link Address' : 'Create Address') }}
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
(function() {
    'use strict';

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────
    let debounceTimer = null;
    function debounce(fn, ms) {
        return function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fn.apply(this, arguments), ms);
        };
    }

    // ──────────────────────────────────────────────
    // 1. Address Registry Search (AJAX)
    // ──────────────────────────────────────────────
    const registryInput   = document.getElementById('registrySearchInput');
    const registryResults = document.getElementById('registryResults');
    const selectedBanner  = document.getElementById('selectedAddressBanner');
    const existingIdField = document.getElementById('existingAddressId');
    const newAddressFields = document.getElementById('newAddressFields');

    if (registryInput) {
        registryInput.addEventListener('input', debounce(function() {
            const q = this.value.trim();
            if (q.length < 2) {
                registryResults.innerHTML = '';
                return;
            }

            registryResults.innerHTML = '<div class="nx-addr-registry-loading"><i class="fas fa-spinner fa-spin"></i> Searching registry...</div>';

            fetch("{{ route('nexcore.addresses.search-registry') }}?q=" + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    registryResults.innerHTML = '<div class="nx-addr-registry-loading">No addresses found</div>';
                    return;
                }
                let html = '';
                data.forEach(function(addr) {
                    html += '<div class="nx-addr-registry-item" onclick="selectRegistryAddress(' + addr.id + ', \'' + escapeHtml(addr.line1) + '\', \'' + escapeHtml(addr.line2) + '\')">';
                    html += '<div class="nx-addr-registry-line1">' + escapeHtml(addr.line1) + '</div>';
                    html += '<div class="nx-addr-registry-line2">' + escapeHtml(addr.line2);
                    if (addr.category) html += ' &middot; ' + escapeHtml(addr.category);
                    html += '</div></div>';
                });
                registryResults.innerHTML = html;
            })
            .catch(function() {
                registryResults.innerHTML = '<div class="nx-addr-registry-loading" style="color:#f87171;">Search failed</div>';
            });
        }, 350));
    }

    window.selectRegistryAddress = function(id, line1, line2) {
        existingIdField.value = id;
        document.getElementById('selectedAddressLine1').textContent = line1;
        document.getElementById('selectedAddressLine2').textContent = line2;
        selectedBanner.style.display = 'flex';
        registryResults.innerHTML = '';
        registryInput.value = '';

        // Hide new address form fields when linking existing
        if (newAddressFields) newAddressFields.style.display = 'none';
    };

    window.clearSelectedAddress = function() {
        existingIdField.value = '';
        selectedBanner.style.display = 'none';
        if (newAddressFields) newAddressFields.style.display = '';
    };

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }


    // ──────────────────────────────────────────────
    // 2. Suburb Search (AJAX)
    // ──────────────────────────────────────────────
    const suburbSearch  = document.getElementById('suburbSearch');
    const suburbResults = document.getElementById('suburbResults');
    const suburbIdField = document.getElementById('suburbId');

    if (suburbSearch) {
        suburbSearch.addEventListener('input', debounce(function() {
            const q = this.value.trim();
            suburbIdField.value = '';
            if (q.length < 2) { suburbResults.innerHTML = ''; return; }

            fetch("/pmpro/lookup/search-suburbs?q=" + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.length) { suburbResults.innerHTML = '<div class="nx-suburb-item" style="color:rgba(255,255,255,0.35);">No suburbs found</div>'; return; }
                let html = '';
                data.forEach(function(s) {
                    html += '<div class="nx-suburb-item" data-id="' + s.id + '" data-name="' + escapeHtml(s.name) + '">' + escapeHtml(s.name);
                    if (s.city) html += ' <span style="color:rgba(255,255,255,0.35);">(' + escapeHtml(s.city) + ')</span>';
                    html += '</div>';
                });
                suburbResults.innerHTML = html;
            })
            .catch(function() { suburbResults.innerHTML = ''; });
        }, 300));

        suburbResults.addEventListener('click', function(e) {
            const item = e.target.closest('.nx-suburb-item');
            if (!item || !item.dataset.id) return;
            suburbSearch.value = item.dataset.name;
            suburbIdField.value = item.dataset.id;
            suburbResults.innerHTML = '';
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!suburbSearch.contains(e.target) && !suburbResults.contains(e.target)) {
                suburbResults.innerHTML = '';
            }
        });
    }


    // ──────────────────────────────────────────────
    // 3. Province > Municipality Cascade
    // ──────────────────────────────────────────────
    const provinceSelect      = document.getElementById('provinceSelect');
    const municipalitySelect  = document.getElementById('municipalitySelect');
    const municipalityIdField = document.getElementById('municipalityId');
    const wardSelect          = document.getElementById('wardSelect');
    const wardIdField         = document.getElementById('wardId');

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const pid = this.value;
            municipalitySelect.innerHTML = '<option value="">Loading...</option>';
            wardSelect.innerHTML = '<option value="">-- Select Municipality First --</option>';
            municipalityIdField.value = '';
            wardIdField.value = '';

            if (!pid) {
                municipalitySelect.innerHTML = '<option value="">-- Select Province First --</option>';
                return;
            }

            // Fetch both local municipalities and metros in parallel
            Promise.all([
                fetch("/pmpro/lookup/local-municipalities/" + pid, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => []),
                fetch("/pmpro/lookup/metros/" + pid, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => [])
            ]).then(function(results) {
                const locals = results[0] || [];
                const metros = results[1] || [];
                let html = '<option value="">-- Select Municipality --</option>';

                if (metros.length) {
                    html += '<optgroup label="Metropolitan">';
                    metros.forEach(function(m) { html += '<option value="' + m.id + '">' + escapeHtml(m.name) + '</option>'; });
                    html += '</optgroup>';
                }
                if (locals.length) {
                    html += '<optgroup label="Local Municipality">';
                    locals.forEach(function(m) { html += '<option value="' + m.id + '">' + escapeHtml(m.name) + '</option>'; });
                    html += '</optgroup>';
                }
                if (!metros.length && !locals.length) {
                    html = '<option value="">No municipalities found</option>';
                }

                municipalitySelect.innerHTML = html;
            });
        });

        // Sync hidden field on municipality change
        municipalitySelect.addEventListener('change', function() {
            municipalityIdField.value = this.value;
            // Cascade to wards
            loadWards(this.value);
        });
    }


    // ──────────────────────────────────────────────
    // 4. Municipality > Ward Cascade
    // ──────────────────────────────────────────────
    function loadWards(mid) {
        wardSelect.innerHTML = '<option value="">Loading...</option>';
        wardIdField.value = '';

        if (!mid) {
            wardSelect.innerHTML = '<option value="">-- Select Municipality First --</option>';
            return;
        }

        fetch("/pmpro/lookup/wards/" + mid, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function(data) {
                let html = '<option value="">-- Select Ward --</option>';
                if (data && data.length) {
                    data.forEach(function(w) {
                        html += '<option value="' + w.id + '">' + escapeHtml(w.name) + '</option>';
                    });
                } else {
                    html = '<option value="">No wards found</option>';
                }
                wardSelect.innerHTML = html;
            })
            .catch(function() {
                wardSelect.innerHTML = '<option value="">Failed to load wards</option>';
            });
    }

    // Sync hidden field on ward change
    if (wardSelect) {
        wardSelect.addEventListener('change', function() {
            wardIdField.value = this.value;
        });
    }


    // ──────────────────────────────────────────────
    // 5. Address Category Radio Pills
    // ──────────────────────────────────────────────
    document.querySelectorAll('.nx-cat-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.nx-cat-pill').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
        });
    });


    // ──────────────────────────────────────────────
    // 6. Collapsible Extended Details
    // ──────────────────────────────────────────────
    window.toggleExtended = function() {
        const toggle = document.getElementById('extendedToggle');
        const body   = document.getElementById('extendedBody');
        toggle.classList.toggle('open');
        body.classList.toggle('open');
    };

    // Auto-open if details exist
    @if($details && ($details->floor_level || $details->building_name || $details->farm_name || $details->erf_number || $details->plus_code || $details->what3words || $details->google_place_id))
    document.addEventListener('DOMContentLoaded', function() { toggleExtended(); });
    @endif


    // ──────────────────────────────────────────────
    // 7. Google Places Autocomplete
    // ──────────────────────────────────────────────
    window.initGooglePlaces = function() {
        const input = document.getElementById('googlePlacesInput');
        if (!input || typeof google === 'undefined') return;

        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'za' },
            fields: ['address_components', 'formatted_address', 'geometry', 'place_id', 'plus_code']
        });

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place || !place.address_components) return;

            const c = {};
            place.address_components.forEach(function(comp) {
                comp.types.forEach(function(t) { c[t] = comp.long_name; });
            });

            // Fill form fields
            setFieldValue('street_number', c.street_number || '');
            setFieldValue('street_name', c.route || '');
            setFieldValue('city', c.locality || c.administrative_area_level_2 || '');
            setFieldValue('postal_code', c.postal_code || '');
            setFieldValue('country', c.country || 'South Africa');
            setFieldValue('google_formatted_address', place.formatted_address || '');

            // Suburb search
            if (c.sublocality || c.sublocality_level_1) {
                const suburbName = c.sublocality_level_1 || c.sublocality;
                suburbSearch.value = suburbName;
                // Trigger suburb lookup
                fetch("/pmpro/lookup/search-suburbs?q=" + encodeURIComponent(suburbName), {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json()).then(function(data) {
                    if (data.length) {
                        suburbIdField.value = data[0].id;
                        suburbSearch.value = data[0].name;
                    }
                }).catch(function() {});
            }

            // Province matching
            if (c.administrative_area_level_1 && provinceSelect) {
                const provName = c.administrative_area_level_1;
                const opts = provinceSelect.options;
                for (let i = 0; i < opts.length; i++) {
                    if (opts[i].text.toLowerCase().includes(provName.toLowerCase()) || provName.toLowerCase().includes(opts[i].text.toLowerCase())) {
                        provinceSelect.value = opts[i].value;
                        provinceSelect.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            }

            // GPS
            if (place.geometry && place.geometry.location) {
                const lat = place.geometry.location.lat().toFixed(8);
                const lng = place.geometry.location.lng().toFixed(8);
                document.getElementById('addrLat').value = lat;
                document.getElementById('addrLng').value = lng;
                document.getElementById('gpsText').textContent = lat + ', ' + lng;
            }

            // Place ID
            if (place.place_id) {
                const placeIdField = document.getElementById('googlePlaceId');
                if (placeIdField) placeIdField.value = place.place_id;
            }

            // Plus code
            if (place.plus_code && place.plus_code.global_code) {
                setFieldValue('plus_code', place.plus_code.global_code);
            }

            // Map URL
            if (place.geometry && place.geometry.location) {
                setFieldValue('map_url', 'https://www.google.com/maps/place/?q=place_id:' + (place.place_id || ''));
            }

            // Set source to Google Places
            const sourceSelect = document.getElementById('address_source');
            if (sourceSelect) {
                for (let i = 0; i < sourceSelect.options.length; i++) {
                    if (sourceSelect.options[i].value === 'google_places') {
                        sourceSelect.value = 'google_places';
                        break;
                    }
                }
            }

            // Auto-open extended if we got plus code / place ID
            if (place.place_id || (place.plus_code && place.plus_code.global_code)) {
                const toggle = document.getElementById('extendedToggle');
                const body   = document.getElementById('extendedBody');
                if (!body.classList.contains('open')) {
                    toggle.classList.add('open');
                    body.classList.add('open');
                }
            }

            // Clear the google search input
            input.value = '';
        });
    };

    function setFieldValue(name, value) {
        const field = document.querySelector('[name="' + name + '"]');
        if (field) {
            if (field.tagName === 'TEXTAREA') {
                field.textContent = value;
                field.value = value;
            } else {
                field.value = value;
            }
        }
    }

})();
</script>

{{-- Google Maps Places API --}}
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDlFzdbBe7bMPm9jrCo6C8340ELKtsZjEw&libraries=places&callback=initGooglePlaces" async defer></script>
@endpush

@endsection
