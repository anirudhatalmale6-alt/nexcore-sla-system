{{--
|--------------------------------------------------------------------------
| Person Master Registry
|--------------------------------------------------------------------------
|
| Module:       NexcoreClientManager
| Version:      2.0
| Route:        /nexcore/clients/persons
| Standards:    NxAlert M1-M6, flatpickr 'j M Y', Montserrat
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}
@extends('nexcore_client_manager::layouts.app')

@section('title', 'Person Master Registry')
@section('page_heading', 'PERSON REGISTRY')

@push('styles')
{{-- NexCore Master CSS --}}
<link href="/public/nexcore/master/nexcore_main_settings/nexcore_main_settings.css?v={{ time() }}" rel="stylesheet">
<link href="/public/nexcore/master/nexcore_main_css/nexcore_main_typography.css?v={{ time() }}" rel="stylesheet">
<link href="/public/nexcore/master/nexcore_main_css/nexcore_main_forms.css?v={{ time() }}" rel="stylesheet">
<link href="/public/nexcore/master/nexcore_main_css/nexcore_main_tables.css?v={{ time() }}" rel="stylesheet">
<link href="/public/nexcore/master/nexcore_main_messages/nexcore_main_messages.css?v={{ time() }}" rel="stylesheet">

{{-- Third Party --}}
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

{{-- Page-Specific Styles --}}
<link href="/public/nexcore/master/nexcore_main_css/nexcore_main_persons.css?v={{ time() }}" rel="stylesheet">
@endpush

@section('content')

{{-- ================================================================
     INDEX PAGE: Person List (Dark Theme)
     ================================================================ --}}
<div class="sl-animate d1">

    {{-- Page Header --}}
    <div class="pm-header">
        <div class="pm-header-icon"><i class="fas fa-users-cog"></i></div>
        <div>
            <h1 class="pm-header-title">Person Master Registry</h1>
            <div class="pm-header-sub">Manage all persons independently &mdash; link to clients from their profiles</div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="pm-stats">
        <div class="pm-stat">
            <div class="pm-stat-label">Total Persons</div>
            <div class="pm-stat-value">{{ $persons->count() }}</div>
        </div>
        <div class="pm-stat">
            <div class="pm-stat-label">Active</div>
            <div class="pm-stat-value">{{ $persons->where('person_status','active')->count() }}</div>
        </div>
        <div class="pm-stat">
            <div class="pm-stat-label">With ID Number</div>
            <div class="pm-stat-value">{{ $persons->whereNotNull('identity_number')->count() }}</div>
        </div>
        <div class="pm-stat">
            <div class="pm-stat-label">Linked to Clients</div>
            <div class="pm-stat-value">{{ $persons->filter(fn($p) => $p->roles->count() > 0)->count() }}</div>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="pm-action-bar">
        <div class="pm-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" class="pm-search-input" id="pmSearch" placeholder="Search by name, ID, passport...">
        </div>
        <div class="pm-spacer"></div>
        <button class="button_master_add" onclick="pmOpenAddModal()"><i class="fas fa-plus"></i> Add Person</button>
    </div>

    {{-- Person Card List --}}
    <div class="pm-card-list" id="pmCardList">
        @forelse($persons as $person)
        <div class="pm-card" data-id="{{ $person->id }}" onclick="pmOpenDrawer({{ $person->id }})">
            <div class="pm-photo">
                @if($person->profile_photo)
                    <img src="/storage/{{ $person->profile_photo }}" alt="{{ $person->first_name }}"
                         onerror="this.parentElement.innerHTML='<div class=\'pm-photo-initials\'>{{ strtoupper(substr($person->first_name,0,1).substr($person->last_name,0,1)) }}</div>'">
                @else
                    <div class="pm-photo-initials">{{ strtoupper(substr($person->first_name,0,1).substr($person->last_name,0,1)) }}</div>
                @endif
            </div>
            <div class="pm-card-body">
                <div class="pm-card-row">
                    <span class="pm-name">{{ $person->title_abbr ? $person->title_abbr.' ' : '' }}{{ $person->first_name }} {{ $person->last_name }}</span>
                    @if($person->person_status === 'active')
                        <span class="pm-badge pm-badge-active">Active</span>
                    @elseif($person->person_status === 'deceased')
                        <span class="pm-badge pm-badge-deceased">Deceased</span>
                    @else
                        <span class="pm-badge pm-badge-inactive">{{ ucfirst($person->person_status) }}</span>
                    @endif
                    @if($person->gender_name)
                        <span class="pm-badge pm-badge-gender">{{ $person->gender_name }}</span>
                    @endif
                </div>
                <div class="pm-card-details">
                    @if($person->identity_number)
                        <span class="pm-detail"><i class="fas fa-id-card"></i> <span class="pm-detail-value">{{ $person->identity_number }}</span></span>
                    @endif
                    @php
                        $email = $person->contacts->where('contact_type', 'email')->first();
                        $phone = $person->contacts->where('contact_type', 'phone')->first();
                    @endphp
                    @if($email)
                        <span class="pm-detail"><i class="fas fa-envelope"></i> <span class="pm-detail-value">{{ $email->contact_value }}</span></span>
                    @endif
                    @if($phone)
                        <span class="pm-detail"><i class="fas fa-phone"></i> <span class="pm-detail-value">{{ $phone->contact_value }}</span></span>
                    @endif
                    @if($person->nationality_name)
                        <span class="pm-detail"><i class="fas fa-globe-africa"></i> <span class="pm-detail-value">{{ $person->nationality_name }}</span></span>
                    @endif
                </div>
                @if($person->roles->count() > 0)
                <div class="pm-card-tags">
                    @foreach($person->roles as $role)
                        <span class="pm-company-tag">{{ $role->client_code ?? $role->company_name }} ({{ ucfirst(str_replace('_', ' ', $role->role_type)) }})</span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="pm-card-actions" onclick="event.stopPropagation()">
                <button class="pm-card-btn" onclick="pmOpenEditModal({{ $person->id }})" title="Edit">
                    <i class="fas fa-pen"></i>
                </button>
            </div>
        </div>
        @empty
        <div class="pm-empty">
            <i class="fas fa-users"></i>
            <div class="pm-empty-title">No Persons Found</div>
            <div class="pm-empty-sub">Click "Add Person" to create the first entry</div>
        </div>
        @endforelse
    </div>

</div>

{{-- ================================================================
     DRAWER: Person Detail Slide-Out (Dark Theme)
     ================================================================ --}}
<div class="pm-dd-overlay" id="pmDdOverlay" onclick="pmCloseDrawer()"></div>
<div class="pm-dd-drawer" id="pmDdDrawer">
    <div class="pm-dd-header">
        <div class="pm-dd-photo" id="pmDdPhoto"></div>
        <div class="pm-dd-header-info">
            <div class="pm-dd-name" id="pmDdName"></div>
            <div class="pm-dd-sub" id="pmDdSub"></div>
        </div>
        <button class="pm-dd-close" onclick="pmCloseDrawer()"><i class="fas fa-times"></i></button>
    </div>
    <div class="pm-dd-tabs">
        <button class="pm-dd-tab-btn pm-dd-tab-active" data-tab="personal" onclick="pmSwitchTab('personal')"><i class="fas fa-user"></i>&nbsp; Personal</button>
        <button class="pm-dd-tab-btn" data-tab="contacts" onclick="pmSwitchTab('contacts')"><i class="fas fa-address-book"></i>&nbsp; Contacts</button>
        <button class="pm-dd-tab-btn" data-tab="banking" onclick="pmSwitchTab('banking')"><i class="fas fa-landmark"></i>&nbsp; Banking</button>
        <button class="pm-dd-tab-btn" data-tab="relationships" onclick="pmSwitchTab('relationships')"><i class="fas fa-heart"></i>&nbsp; Relationships</button>
        <button class="pm-dd-tab-btn" data-tab="documents" onclick="pmSwitchTab('documents')"><i class="fas fa-folder-open"></i>&nbsp; Documents</button>
        <button class="pm-dd-tab-btn" data-tab="clients" onclick="pmSwitchTab('clients')"><i class="fas fa-building"></i>&nbsp; Client Links</button>
    </div>
    <div class="pm-dd-tab-content pm-dd-tab-visible" data-tab="personal" id="pmTabPersonal"></div>
    <div class="pm-dd-tab-content" data-tab="contacts" id="pmTabContacts"></div>
    <div class="pm-dd-tab-content" data-tab="banking" id="pmTabBanking"></div>
    <div class="pm-dd-tab-content" data-tab="relationships" id="pmTabRelationships"></div>
    <div class="pm-dd-tab-content" data-tab="documents" id="pmTabDocuments"></div>
    <div class="pm-dd-tab-content" data-tab="clients" id="pmTabClients"></div>
</div>

{{-- ================================================================
     MODAL: Add/Edit Person (White/Light Theme)
     ================================================================ --}}
<div class="nx-modal-overlay" id="pmModal">
    <div class="nx-modal">
        <div class="nx-modal-header">
            <div class="nx-modal-header-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <h3 id="pmModalTitle">Add New Person</h3>
                <div class="nx-modal-header-sub" id="pmModalSub">Enter person details below</div>
            </div>
            <button class="nx-modal-close" onclick="pmCloseModal()"><i class="fas fa-times"></i></button>
        </div>

        <div class="nx-modal-body">
            <form id="pmForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="pmFormId" name="person_id" value="">
                <input type="hidden" name="address_type_id" value="1">

                {{-- Section 1: Personal Details --}}
                <div class="nx-form-section nx-section-cyan">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-user"></i></div>
                        <span class="nx-form-section-title">Personal Details</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div class="nx-form-row-4">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Title</label>
                            <select name="title_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($titles as $t)<option value="{{ $t->id }}">{{ $t->abbreviation }} - {{ $t->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">First Name <span class="nx-form-req">*</span></label>
                            <input type="text" name="first_name" class="nx-form-input" required>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="nx-form-input">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Last Name <span class="nx-form-req">*</span></label>
                            <input type="text" name="last_name" class="nx-form-input" required>
                        </div>
                    </div>
                    <div class="nx-form-row-4">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Known As</label>
                            <input type="text" name="known_as" class="nx-form-input">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Initials</label>
                            <input type="text" name="initials" class="nx-form-input" maxlength="10">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Gender</label>
                            <select name="gender_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($genders as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Date of Birth</label>
                            <input type="text" name="date_of_birth" class="nx-form-input pm-date" placeholder="Select date...">
                        </div>
                    </div>
                    <div class="nx-form-row-3">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Status</label>
                            <select name="person_status" class="nx-form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="deceased">Deceased</option>
                                <option value="emigrated">Emigrated</option>
                                <option value="blacklisted">Blacklisted</option>
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Nationality</label>
                            <select name="nationality_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($nationalities as $n)<option value="{{ $n->id }}">{{ $n->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Language</label>
                            <select name="language_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($languages as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Identity & Documentation --}}
                <div class="nx-form-section nx-section-blue">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-id-card"></i></div>
                        <span class="nx-form-section-title">Identity & Documentation</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div class="nx-form-row-3">
                        <div class="nx-form-group">
                            <label class="nx-form-label">ID Document Type</label>
                            <select name="id_document_type_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($idDocTypes as $idt)<option value="{{ $idt->id }}">{{ $idt->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">ID Number</label>
                            <input type="text" name="identity_number" class="nx-form-input" maxlength="30">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">ID Date of Issue</label>
                            <input type="text" name="id_date_of_issue" class="nx-form-input pm-date" placeholder="Select date...">
                        </div>
                    </div>
                    <div class="nx-form-row-4">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Passport Number</label>
                            <input type="text" name="passport_number" class="nx-form-input" maxlength="30">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Passport Issued</label>
                            <input type="text" name="passport_date_of_issue" class="nx-form-input pm-date" placeholder="Select date...">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Passport Expiry</label>
                            <input type="text" name="passport_expiry" class="nx-form-input pm-date" placeholder="Select date...">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Passport Country</label>
                            <input type="text" name="passport_country" class="nx-form-input" maxlength="80">
                        </div>
                    </div>
                </div>

                {{-- Section 3: Address Details --}}
                <div class="nx-form-section nx-section-rose">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <span class="nx-form-section-title">Address Details</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div id="pmAddrMainForm">
                        <div class="pm-addr-form-row">
                            <div class="nx-form-group">
                                <label class="nx-form-label">Search Address</label>
                                <select id="pmAddrSelect" class="nx-form-select"></select>
                            </div>
                            <div class="nx-form-group">
                                <label class="nx-form-label">&nbsp;</label>
                                <label class="nx-form-check"><input type="checkbox" id="pmAddrPrimary"> Primary</label>
                            </div>
                        </div>
                        <div class="pm-addr-proof-row">
                            <div class="nx-form-group">
                                <label class="nx-form-label">Proof of Address</label>
                                <input type="file" id="pmAddrProofFile" class="nx-form-input nx-form-input-file" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="nx-form-group">
                                <label class="nx-form-label">File Name</label>
                                <input type="text" id="pmAddrProofName" class="nx-form-input" readonly placeholder="No file selected">
                            </div>
                            <div class="nx-form-group">
                                <label class="nx-form-label">Effective Date</label>
                                <input type="text" id="pmAddrEffDate" class="nx-form-input pm-date" placeholder="Select date...">
                            </div>
                        </div>
                        <button type="button" class="button_master_add pm-addr-link-btn" onclick="pmLinkAddress()">
                            <i class="fas fa-link"></i> Link Address
                        </button>
                    </div>
                    <div class="pm-addr-cards" id="pmAddrCards"></div>
                </div>

                {{-- Section 4: Demographics & Marriage --}}
                <div class="nx-form-section nx-section-purple">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-globe-africa"></i></div>
                        <span class="nx-form-section-title">Demographics & Marriage</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div class="nx-form-row-3">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Ethnic Group</label>
                            <select name="ethnic_group_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($ethnicGroups as $eg)<option value="{{ $eg->id }}">{{ $eg->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Religion</label>
                            <select name="religion_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($religions as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Marital Status</label>
                            <select name="marital_status_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($maritalStatuses as $ms)<option value="{{ $ms->id }}">{{ $ms->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="nx-form-row-3">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Marriage Type</label>
                            <input type="text" name="marriage_type" class="nx-form-input" placeholder="e.g. COP, ANC...">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Marriage Date</label>
                            <input type="text" name="marriage_date" class="nx-form-input pm-date" placeholder="Select date...">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Date of Death</label>
                            <input type="text" name="date_of_death" class="nx-form-input pm-date" placeholder="If applicable...">
                        </div>
                    </div>
                    <div class="nx-form-row">
                        <div class="nx-form-group">
                            <label class="nx-form-check"><input type="checkbox" name="has_disability"> Has Disability</label>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Disability Type</label>
                            <select name="disability_type_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($disabilityTypes as $dt)<option value="{{ $dt->id }}">{{ $dt->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 5: Employment & Education --}}
                <div class="nx-form-section nx-section-green">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-briefcase"></i></div>
                        <span class="nx-form-section-title">Employment & Education</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div class="nx-form-row-3">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Education Level</label>
                            <select name="education_level_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($educationLevels as $el)<option value="{{ $el->id }}">{{ $el->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Employment Status</label>
                            <select name="employment_status_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($employmentStatuses as $es)<option value="{{ $es->id }}">{{ $es->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Occupation</label>
                            <input type="text" name="occupation" class="nx-form-input">
                        </div>
                    </div>
                    <div class="nx-form-row">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Employer</label>
                            <input type="text" name="employer" class="nx-form-input">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Income Source</label>
                            <select name="income_source_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($incomeSources as $is)<option value="{{ $is->id }}">{{ $is->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 6: Tax & SARS --}}
                <div class="nx-form-section nx-section-amber">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <span class="nx-form-section-title">Tax & SARS</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div class="nx-form-row-4">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="nx-form-input" maxlength="20">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Tax Status</label>
                            <select name="tax_status" class="nx-form-select">
                                <option value="">Select...</option>
                                <option value="registered">Registered</option>
                                <option value="not_registered">Not Registered</option>
                                <option value="exempt">Exempt</option>
                                <option value="provisional">Provisional</option>
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">eFiling Username</label>
                            <input type="text" name="sars_efiling_username" class="nx-form-input">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">eFiling Password</label>
                            <input type="password" name="sars_efiling_password" class="nx-form-input">
                        </div>
                    </div>
                    <div class="nx-form-row">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Communication Preference</label>
                            <select name="communication_pref_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($commPrefs as $cp)<option value="{{ $cp->id }}">{{ $cp->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Preferred Language</label>
                            <select name="preferred_language_id" class="nx-form-select">
                                <option value="">Select...</option>
                                @foreach($languages as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 7: Photo & Notes --}}
                <div class="nx-form-section nx-section-purple">
                    <div class="nx-form-section-header">
                        <div class="nx-form-section-icon"><i class="fas fa-camera"></i></div>
                        <span class="nx-form-section-title">Photo & Notes</span>
                        <div class="nx-form-section-line"></div>
                    </div>
                    <div class="nx-form-row">
                        <div class="nx-form-group">
                            <label class="nx-form-label">Profile Photo</label>
                            <input type="file" name="profile_photo" class="nx-form-input nx-form-input-file" accept="image/*">
                        </div>
                        <div class="nx-form-group">
                            <label class="nx-form-label">Signature Image</label>
                            <input type="file" name="signature_image" class="nx-form-input nx-form-input-file" accept="image/*">
                        </div>
                    </div>
                    <div class="nx-form-group">
                        <label class="nx-form-label">Notes</label>
                        <textarea name="notes" class="nx-form-input nx-form-textarea"></textarea>
                    </div>
                </div>

            </form>
        </div>

        <div class="nx-modal-footer">
            <span class="nx-modal-footer-info"><i class="fas fa-shield-alt"></i> All data stored securely</span>
            <div class="nx-modal-footer-actions">
                <button class="button_master_cancel" onclick="pmCloseModal()">Cancel</button>
                <button class="button_master_save" onclick="pmSaveForm()"><i class="fas fa-check"></i> Save Person</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Third Party --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- NexCore Master JS --}}
<script src="/public/nexcore/master/nexcore_main_messages/nexcore_main_messages.js?v={{ time() }}"></script>
<script src="/public/nexcore/master/nexcore_main_js/nexcore_main_forms.js?v={{ time() }}"></script>
<script src="/public/nexcore/master/nexcore_main_js/nexcore_main_utilities.js?v={{ time() }}"></script>
<script>
/* =============================================================
   GLOBALS
   ============================================================= */
var pmApiBase = '/nexcore/clients/persons';
var pmPersonsData = {};
var pmCurrentId = null;
var pmPendingAddrs = [];
var pmRemovedAddrs = [];
var pmEditingAddrId = null;
var pmBanks = @json($banks);
var pmBankTypes = @json($bankAccountTypes);
var pmRelTypes = @json($relationshipTypes);

@foreach($persons as $p)
pmPersonsData[{{ $p->id }}] = @json($p);
@endforeach

/* =============================================================
   UTILITIES
   ============================================================= */
function pmFmtDate(d) {
    if (!d) return '-';
    var dt = new Date(d);
    if (isNaN(dt)) return d;
    var m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return dt.getDate() + ' ' + m[dt.getMonth()] + ' ' + dt.getFullYear();
}

function pmAlert(type, title, msg) {
    if (typeof NxAlert === 'undefined') return;
    if (type === 'success') NxAlert.success(title, msg);
    else if (type === 'error') NxAlert.error(title, msg);
    else if (type === 'warning') NxAlert.warning(title, msg);
    else NxAlert.info(title, msg);
}

function pmCsrf() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function pmInitials(first, last) {
    return ((first || '').charAt(0) + (last || '').charAt(0)).toUpperCase();
}

/* =============================================================
   SEARCH FILTER
   ============================================================= */
document.getElementById('pmSearch').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.pm-card').forEach(function(c) {
        c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

/* =============================================================
   DRAWER
   ============================================================= */
function pmOpenDrawer(id) {
    pmCurrentId = id;
    var p = pmPersonsData[id];
    if (!p) return;

    var ini = pmInitials(p.first_name, p.last_name);
    var ph = '';
    if (p.profile_photo) {
        ph = '<img src="/storage/' + p.profile_photo + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">'
           + '<div class="pm-photo-initials" style="display:none;">' + ini + '</div>';
    } else {
        ph = '<div class="pm-photo-initials">' + ini + '</div>';
    }
    document.getElementById('pmDdPhoto').innerHTML = ph;
    document.getElementById('pmDdName').textContent = (p.title_abbr ? p.title_abbr + ' ' : '') + p.first_name + ' ' + p.last_name;
    document.getElementById('pmDdSub').textContent = (p.identity_number || 'No ID') + ' | ' + (p.gender_name || 'N/A') + ' | ' + (p.nationality_name || 'N/A');

    pmPopPersonal(p);
    pmPopContacts(p);
    pmPopBanking(p);
    pmPopRels(p);
    pmPopDocs(p);
    pmPopClients(p);

    document.getElementById('pmDdOverlay').classList.add('pm-dd-show');
    document.getElementById('pmDdDrawer').classList.add('pm-dd-open');
    pmSwitchTab('personal');
}

function pmCloseDrawer() {
    document.getElementById('pmDdOverlay').classList.remove('pm-dd-show');
    document.getElementById('pmDdDrawer').classList.remove('pm-dd-open');
    pmCurrentId = null;
}

function pmSwitchTab(t) {
    document.querySelectorAll('.pm-dd-tab-btn').forEach(function(b) { b.classList.remove('pm-dd-tab-active'); });
    document.querySelectorAll('.pm-dd-tab-content').forEach(function(c) { c.classList.remove('pm-dd-tab-visible'); });
    document.querySelector('.pm-dd-tab-btn[data-tab="' + t + '"]').classList.add('pm-dd-tab-active');
    document.querySelector('.pm-dd-tab-content[data-tab="' + t + '"]').classList.add('pm-dd-tab-visible');
}

function pmI(label, value) {
    return '<div class="pm-dd-info-item"><span class="pm-dd-info-label">' + label + '</span><span class="pm-dd-info-value">' + (value || '-') + '</span></div>';
}

/* =============================================================
   DRAWER TAB: Personal
   ============================================================= */
function pmPopPersonal(p) {
    var h = '<div class="pm-dd-section-title"><i class="fas fa-id-card"></i> Identity</div><div class="pm-dd-info-grid">';
    h += pmI('Full Name', (p.title_abbr ? p.title_abbr + ' ' : '') + p.first_name + (p.middle_name ? ' ' + p.middle_name : '') + ' ' + p.last_name);
    h += pmI('ID Number', p.identity_number);
    h += pmI('ID Issue Date', pmFmtDate(p.id_date_of_issue));
    h += pmI('Tax Number', p.tax_number);
    h += pmI('Date of Birth', pmFmtDate(p.date_of_birth));
    var sb = p.person_status === 'active' ? 'pm-badge-active' : p.person_status === 'deceased' ? 'pm-badge-deceased' : 'pm-badge-inactive';
    h += pmI('Status', '<span class="pm-badge ' + sb + '">' + (p.person_status || '-').charAt(0).toUpperCase() + (p.person_status || '').slice(1) + '</span>');
    h += pmI('Passport Number', p.passport_number);
    h += pmI('Passport Issued', pmFmtDate(p.passport_date_of_issue));
    h += pmI('Passport Expiry', pmFmtDate(p.passport_expiry));
    h += pmI('Country of Origin', p.passport_country);
    h += pmI('ID Document Type', p.id_doc_type_name);
    h += pmI('Known As', p.known_as);
    h += '</div>';

    h += '<div class="pm-dd-section-title"><i class="fas fa-globe-africa"></i> Demographics</div><div class="pm-dd-info-grid">';
    h += pmI('Gender', p.gender_name);
    h += pmI('Ethnic Group', p.ethnic_group_name);
    h += pmI('Religion', p.religion_name);
    h += pmI('Nationality', p.nationality_name);
    h += pmI('Language', p.language_name);
    h += pmI('Education', p.education_level_name);
    h += pmI('Employment', p.employment_status_name);
    h += pmI('Occupation', p.occupation);
    h += pmI('Employer', p.employer);
    h += pmI('Income Source', p.income_source_name);
    h += pmI('Disability', p.has_disability ? (p.disability_type_name || 'Yes') : 'None');
    h += '</div>';

    h += '<div class="pm-dd-section-title"><i class="fas fa-ring"></i> Marriage</div><div class="pm-dd-info-grid">';
    h += pmI('Marital Status', p.marital_status_name);
    h += pmI('Marriage Type', p.marriage_type);
    h += pmI('Marriage Date', pmFmtDate(p.marriage_date));
    h += '</div>';

    h += '<div class="pm-dd-section-title"><i class="fas fa-shield-alt"></i> Tax & Communication</div><div class="pm-dd-info-grid">';
    h += pmI('Tax Status', p.tax_status ? p.tax_status.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }) : null);
    h += pmI('eFiling Username', p.sars_efiling_username ? '****' + p.sars_efiling_username.slice(-4) : null);
    h += pmI('Communication Pref', p.comm_pref_name);
    h += pmI('Preferred Language', p.preferred_language_name);
    h += '</div>';

    if (p.notes) {
        h += '<div class="pm-dd-section-title"><i class="fas fa-sticky-note"></i> Notes</div>';
        h += '<div class="pm-dd-notes-box">' + p.notes.replace(/\n/g, '<br>') + '</div>';
    }

    document.getElementById('pmTabPersonal').innerHTML = h;
}

/* =============================================================
   DRAWER TAB: Contacts
   ============================================================= */
function pmPopContacts(p) {
    var count = p.contacts ? p.contacts.length : 0;
    var h = '<div class="pm-dd-section-title"><i class="fas fa-address-book"></i> Contact Details <span class="pm-dd-section-count">' + count + ' contacts</span></div>';

    if (p.contacts && p.contacts.length > 0) {
        p.contacts.forEach(function(c) {
            var ic = c.contact_type === 'email' ? 'email' : c.contact_type === 'whatsapp' ? 'whatsapp' : 'phone';
            var ii = c.contact_type === 'email' ? 'fas fa-envelope' : c.contact_type === 'whatsapp' ? 'fab fa-whatsapp' : 'fas fa-phone';
            h += '<div class="pm-dd-item-card">';
            h += '<div class="pm-dd-item-icon ' + ic + '"><i class="' + ii + '"></i></div>';
            h += '<div class="pm-dd-item-info"><div class="pm-dd-item-title">' + c.contact_value + '</div>';
            h += '<div class="pm-dd-item-sub">' + c.contact_type.charAt(0).toUpperCase() + c.contact_type.slice(1) + (c.label ? ' - ' + c.label : '') + '</div></div>';
            if (c.is_primary) h += '<span class="pm-dd-item-badge primary">Primary</span>';
            h += '<button class="pm-dd-item-delete" onclick="pmDelContact(' + c.id + ')"><i class="fas fa-times"></i></button>';
            h += '</div>';
        });
    } else {
        h += '<div class="pm-dd-empty">No contacts yet</div>';
    }

    h += '<div class="pm-dd-add-form">';
    h += '<div class="pm-dd-add-form-title">Add Contact</div>';
    h += '<div class="pm-dd-form-row-3">';
    h += '<div><label>Type</label><select id="pmACType"><option value="email">Email</option><option value="phone">Phone</option><option value="whatsapp">WhatsApp</option><option value="other">Other</option></select></div>';
    h += '<div><label>Value</label><input type="text" id="pmACVal" placeholder="Enter contact..."></div>';
    h += '<div><label>Label</label><input type="text" id="pmACLabel" placeholder="Work, Personal..."></div>';
    h += '</div>';
    h += '<label class="pm-dd-primary-check"><input type="checkbox" id="pmACPrimary"> Primary</label> ';
    h += '<button class="pm-dd-add-btn" onclick="pmAddContact()"><i class="fas fa-plus"></i> Add</button>';
    h += '</div>';

    document.getElementById('pmTabContacts').innerHTML = h;
}

/* =============================================================
   DRAWER TAB: Banking
   ============================================================= */
function pmPopBanking(p) {
    var count = p.banks ? p.banks.length : 0;
    var h = '<div class="pm-dd-section-title"><i class="fas fa-landmark"></i> Bank Accounts <span class="pm-dd-section-count">' + count + ' accounts</span></div>';

    if (p.banks && p.banks.length > 0) {
        p.banks.forEach(function(b) {
            h += '<div class="pm-dd-item-card">';
            if (b.bank_logo) {
                h += '<div class="pm-dd-bank-logo"><img src="/uploads/banks/' + b.bank_logo + '" onerror="this.parentElement.innerHTML=\'<i class=\\\'fas fa-landmark\\\' style=\\\'color:#8b5cf6;\\\'></i>\'"></div>';
            } else {
                h += '<div class="pm-dd-item-icon bank"><i class="fas fa-landmark"></i></div>';
            }
            h += '<div class="pm-dd-item-info"><div class="pm-dd-item-title">' + (b.bank_name_display || 'Unknown Bank') + '</div>';
            h += '<div class="pm-dd-item-sub">' + b.account_number + (b.account_type_name ? ' | ' + b.account_type_name : '') + (b.branch_code ? ' | Branch: ' + b.branch_code : '') + '</div></div>';
            if (b.is_primary) h += '<span class="pm-dd-item-badge primary">Primary</span>';
            h += '<span class="pm-badge ' + (b.bank_account_status === 'active' ? 'pm-badge-active' : 'pm-badge-inactive') + '">' + b.bank_account_status + '</span>';
            h += '<button class="pm-dd-item-delete" onclick="pmDelBank(' + b.id + ')"><i class="fas fa-times"></i></button>';
            h += '</div>';
        });
    } else {
        h += '<div class="pm-dd-empty">No bank accounts yet</div>';
    }

    h += '<div class="pm-dd-add-form">';
    h += '<div class="pm-dd-add-form-title">Add Bank Account</div>';
    h += '<div class="pm-dd-form-row"><div><label>Bank</label><select id="pmABBank"></select></div><div><label>Account Type</label><select id="pmABType"></select></div></div>';
    h += '<div class="pm-dd-form-row-3"><div><label>Account Number</label><input type="text" id="pmABNum" placeholder="Account number..."></div><div><label>Branch Code</label><input type="text" id="pmABBranch" placeholder="Branch code..."></div><div><label>Account Holder</label><input type="text" id="pmABHolder" placeholder="Holder name..."></div></div>';
    h += '<button class="pm-dd-add-btn" onclick="pmAddBank()"><i class="fas fa-plus"></i> Add</button>';
    h += '</div>';

    document.getElementById('pmTabBanking').innerHTML = h;

    var bs = document.getElementById('pmABBank');
    if (bs) {
        bs.innerHTML = '<option value="">Select bank...</option>';
        pmBanks.forEach(function(b) { bs.innerHTML += '<option value="' + b.id + '">' + b.name + '</option>'; });
    }
    var bt = document.getElementById('pmABType');
    if (bt) {
        bt.innerHTML = '<option value="">Select type...</option>';
        pmBankTypes.forEach(function(t) { bt.innerHTML += '<option value="' + t.id + '">' + t.name + '</option>'; });
    }
}

/* =============================================================
   DRAWER TAB: Relationships
   ============================================================= */
function pmPopRels(p) {
    var count = p.relationships ? p.relationships.length : 0;
    var h = '<div class="pm-dd-section-title"><i class="fas fa-heart"></i> Relationships <span class="pm-dd-section-count">' + count + ' relationships</span></div>';

    if (p.relationships && p.relationships.length > 0) {
        p.relationships.forEach(function(r) {
            var ini = pmInitials(r.related_first_name, r.related_last_name);
            h += '<div class="pm-dd-item-card align-top">';
            if (r.related_photo) {
                h += '<div class="pm-dd-rel-avatar"><img src="/storage/' + r.related_photo + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'"><div class="pm-dd-rel-initials" style="display:none;">' + ini + '</div></div>';
            } else {
                h += '<div class="pm-dd-rel-initials">' + ini + '</div>';
            }
            h += '<div class="pm-dd-item-info">';
            h += '<div class="pm-dd-item-title">' + r.related_first_name + ' ' + r.related_last_name + '</div>';
            h += '<div class="pm-dd-item-sub">' + (r.relationship_name || 'Related') + (r.start_date ? ' | Since ' + pmFmtDate(r.start_date) : '') + '</div>';
            if (r.related_id_number) h += '<div class="pm-dd-item-sub">ID: ' + r.related_id_number + '</div>';
            if (r.related_dob) h += '<div class="pm-dd-item-sub">DOB: ' + pmFmtDate(r.related_dob) + '</div>';
            if (r.related_passport) h += '<div class="pm-dd-item-sub">Passport: ' + r.related_passport + '</div>';
            h += '</div>';
            h += '<button class="pm-dd-item-delete" onclick="pmDelRel(' + r.id + ')"><i class="fas fa-times"></i></button>';
            h += '</div>';
        });
    } else {
        h += '<div class="pm-dd-empty">No relationships yet</div>';
    }

    h += '<div class="pm-dd-add-form">';
    h += '<div class="pm-dd-add-form-title">Add Relationship</div>';
    h += '<div class="pm-dd-form-row-3">';
    h += '<div style="position:relative;"><label>Related Person</label><input type="text" id="pmARSearch" placeholder="Search name or ID..." oninput="pmSearchRel(this.value)"><input type="hidden" id="pmARId"><div id="pmARResults" class="pm-dd-search-results"></div></div>';
    h += '<div><label>Type</label><select id="pmARType">';
    pmRelTypes.forEach(function(rt) { h += '<option value="' + rt.id + '">' + rt.name + '</option>'; });
    h += '</select></div>';
    h += '<div><label>Start Date</label><input type="text" id="pmARDate" class="pm-date" placeholder="Select..."></div>';
    h += '</div>';
    h += '<button class="pm-dd-add-btn" onclick="pmAddRel()"><i class="fas fa-plus"></i> Add</button>';
    h += '</div>';

    document.getElementById('pmTabRelationships').innerHTML = h;
    flatpickr('#pmARDate', { dateFormat: 'j M Y', allowInput: true });
}

/* =============================================================
   DRAWER TAB: Documents
   ============================================================= */
function pmPopDocs(p) {
    var count = p.documents ? p.documents.length : 0;
    var h = '<div class="pm-dd-section-title"><i class="fas fa-folder-open"></i> Documents <span class="pm-dd-section-count">' + count + ' documents</span></div>';

    if (p.documents && p.documents.length > 0) {
        p.documents.forEach(function(d) {
            var fi = d.file_type === 'pdf' ? 'fa-file-pdf' : (d.file_type === 'jpg' || d.file_type === 'png' ? 'fa-file-image' : 'fa-file-alt');
            h += '<div class="pm-dd-item-card">';
            h += '<div class="pm-dd-item-icon doc"><i class="fas ' + fi + '"></i></div>';
            h += '<div class="pm-dd-item-info"><div class="pm-dd-item-title">' + d.document_name + '</div>';
            h += '<div class="pm-dd-item-sub">' + d.document_type.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
            if (d.file_size) h += ' | ' + Math.round(d.file_size / 1024) + 'KB';
            if (d.expiry_date) h += ' | Exp: ' + pmFmtDate(d.expiry_date);
            h += '</div></div>';
            if (d.is_verified) h += '<span class="pm-dd-item-badge primary">Verified</span>';
            h += '<a href="/storage/' + d.file_path + '" target="_blank" class="pm-dd-download-btn" title="Download"><i class="fas fa-download"></i></a>';
            h += '<button class="pm-dd-item-delete" onclick="pmDelDoc(' + d.id + ')"><i class="fas fa-times"></i></button>';
            h += '</div>';
        });
    } else {
        h += '<div class="pm-dd-empty">No documents yet</div>';
    }

    h += '<div class="pm-dd-add-form">';
    h += '<div class="pm-dd-add-form-title">Upload Document</div>';
    h += '<div class="pm-dd-form-row-3">';
    h += '<div><label>Name</label><input type="text" id="pmADName" placeholder="e.g. ID Copy Front..."></div>';
    h += '<div><label>Type</label><select id="pmADType"><option value="id_front">ID Front</option><option value="id_back">ID Back</option><option value="passport">Passport</option><option value="proof_of_address">Proof of Address</option><option value="tax_certificate">Tax Certificate</option><option value="bank_confirmation">Bank Confirmation</option><option value="qualification">Qualification</option><option value="contract">Contract</option><option value="other">Other</option></select></div>';
    h += '<div><label>File</label><input type="file" id="pmADFile" class="nx-form-input-file"></div>';
    h += '</div>';
    h += '<div class="pm-dd-form-row"><div><label>Expiry Date</label><input type="text" id="pmADExpiry" class="pm-date" placeholder="If applicable..."></div><div><label>Notes</label><input type="text" id="pmADNotes" placeholder="Optional notes..."></div></div>';
    h += '<button class="pm-dd-add-btn" onclick="pmAddDoc()"><i class="fas fa-plus"></i> Upload</button>';
    h += '</div>';

    document.getElementById('pmTabDocuments').innerHTML = h;
    flatpickr('#pmADExpiry', { dateFormat: 'j M Y', allowInput: true });
}

/* =============================================================
   DRAWER TAB: Client Links
   ============================================================= */
function pmPopClients(p) {
    var count = p.roles ? p.roles.length : 0;
    var h = '<div class="pm-dd-section-title"><i class="fas fa-building"></i> Client Links <span class="pm-dd-section-count">' + count + ' links</span></div>';

    if (p.roles && p.roles.length > 0) {
        p.roles.forEach(function(r) {
            h += '<div class="pm-dd-role-card">';
            h += '<div class="pm-dd-role-company">' + (r.company_name || 'Unknown Client') + '</div>';
            h += '<div class="pm-dd-role-code">' + (r.client_code || '') + '</div>';
            h += '<span class="pm-dd-role-type">' + r.role_type.replace(/_/g, ' ') + '</span>';
            if (r.designation) h += ' <span class="pm-dd-role-designation">- ' + r.designation + '</span>';
            h += '<div class="pm-dd-role-dates">';
            if (r.appointed_date) h += 'Appointed: ' + pmFmtDate(r.appointed_date);
            if (r.resigned_date) h += ' | Resigned: ' + pmFmtDate(r.resigned_date);
            if (r.shareholding_percent) h += ' | Shares: ' + parseFloat(r.shareholding_percent).toFixed(2) + '%';
            if (r.number_of_shares) h += ' (' + Number(r.number_of_shares).toLocaleString() + ' shares)';
            h += '</div></div>';
        });
    } else {
        h += '<div class="pm-dd-empty-large">';
        h += '<i class="fas fa-unlink"></i>';
        h += '<div class="pm-dd-empty-large-title">Not linked to any clients</div>';
        h += '<div class="pm-dd-empty-large-sub">Link this person from a client\'s Director, Contact, or Staff page</div>';
        h += '</div>';
    }

    document.getElementById('pmTabClients').innerHTML = h;
}

/* =============================================================
   MODAL: Open / Close
   ============================================================= */
function pmOpenAddModal() {
    document.getElementById('pmModalTitle').textContent = 'Add New Person';
    document.getElementById('pmModalSub').textContent = 'Enter person details below';
    document.getElementById('pmFormId').value = '';
    document.getElementById('pmForm').reset();
    pmPendingAddrs = [];
    pmRemovedAddrs = [];
    pmRenderAddrCards();
    document.getElementById('pmModal').classList.add('nx-modal-open');
    pmInitFormControls();
}

function pmOpenEditModal(id) {
    fetch(pmApiBase + '/' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { pmAlert('error', 'Error', data.message); return; }
            var p = data.person;

            document.getElementById('pmModalTitle').textContent = 'Edit Person';
            document.getElementById('pmModalSub').textContent = p.first_name + ' ' + p.last_name;
            document.getElementById('pmFormId').value = p.id;

            var form = document.getElementById('pmForm');
            ['title_id','first_name','middle_name','last_name','known_as','initials','gender_id',
             'ethnic_group_id','religion_id','nationality_id','language_id','id_document_type_id',
             'identity_number','passport_number','passport_country','person_status','disability_type_id',
             'marital_status_id','marriage_type','education_level_id','employment_status_id','occupation',
             'employer','income_source_id','tax_number','tax_status','sars_efiling_username',
             'sars_efiling_password','communication_pref_id','preferred_language_id','notes'].forEach(function(f) {
                var el = form.querySelector('[name="' + f + '"]');
                if (el && p[f] !== null && p[f] !== undefined) el.value = p[f];
            });

            ['date_of_birth','id_date_of_issue','passport_date_of_issue','passport_expiry','marriage_date','date_of_death'].forEach(function(df) {
                var el = form.querySelector('[name="' + df + '"]');
                if (el && p[df]) el.value = pmFmtDate(p[df]);
            });

            var hd = form.querySelector('[name="has_disability"]');
            if (hd) hd.checked = p.has_disability == 1;

            pmPendingAddrs = [];
            pmRemovedAddrs = [];
            pmRenderAddrCards(p.addresses || []);

            document.getElementById('pmModal').classList.add('nx-modal-open');
            pmInitFormControls();
        })
        .catch(function() { pmAlert('error', 'Error', 'Failed to load person data'); });
}

function pmCloseModal() {
    var form = document.getElementById('pmForm');
    var hasData = form.querySelector('[name="first_name"]').value || form.querySelector('[name="last_name"]').value;
    if (hasData) {
        NxAlert.confirm('Unsaved Changes', 'You have unsaved changes. Are you sure you want to exit? Your changes will be lost.', 'Leave')
            .then(function(r) { if (r.isConfirmed) document.getElementById('pmModal').classList.remove('nx-modal-open'); });
    } else {
        document.getElementById('pmModal').classList.remove('nx-modal-open');
    }
}

function pmInitFormControls() {
    setTimeout(function() {
        flatpickr('.pm-date', { dateFormat: 'j M Y', allowInput: true });
        pmInitAddrSelect();
    }, 100);
}

/* =============================================================
   ADDRESS: Select2 Init
   ============================================================= */
function pmInitAddrSelect() {
    var $sel = jQuery('#pmAddrSelect');
    if ($sel.data('select2')) $sel.select2('destroy');
    $sel.select2({
        placeholder: 'Type to search address...',
        minimumInputLength: 2,
        dropdownParent: jQuery('.nx-modal-overlay'),
        ajax: {
            url: pmApiBase.replace('/persons', '') + '/addresses/search',
            type: 'POST',
            dataType: 'json',
            delay: 300,
            headers: { 'X-CSRF-TOKEN': pmCsrf() },
            data: function(params) { return { q: params.term }; },
            processResults: function(data) {
                return {
                    results: (data.addresses || []).map(function(a) {
                        return { id: a.id, text: a.google_formatted_address, address: a };
                    })
                };
            }
        }
    });

    $sel.on('select2:open', function() {
        setTimeout(function() {
            var searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) searchField.focus();
        }, 100);
    });
}

/* =============================================================
   ADDRESS: Proof File Name Display
   ============================================================= */
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'pmAddrProofFile') {
        var nameEl = document.getElementById('pmAddrProofName');
        nameEl.value = e.target.files.length > 0 ? e.target.files[0].name : 'No file selected';
    }
});

/* =============================================================
   ADDRESS: Link / Delink / Render
   ============================================================= */
function pmLinkAddress() {
    var $sel = jQuery('#pmAddrSelect');
    var selData = $sel.select2('data');
    if (!selData || !selData[0] || !selData[0].id) {
        pmAlert('warning', 'Required', 'Please select an address from the dropdown.');
        return;
    }

    var addr = selData[0].address || {};
    var isPrimary = document.getElementById('pmAddrPrimary').checked ? 1 : 0;
    var proofFile = document.getElementById('pmAddrProofFile').files[0] || null;
    var effDate = document.getElementById('pmAddrEffDate').value || '';

    pmPendingAddrs.push({
        tempId: Date.now(),
        address_id: selData[0].id,
        google_formatted_address: selData[0].text,
        is_primary: isPrimary,
        proof_file: proofFile,
        effective_date: effDate,
        address: addr
    });

    pmRenderAddrCards();
    pmClearAddrForm();
    pmAlert('success', 'Linked', 'Address linked. Click Save Person to persist.');
}

function pmClearAddrForm() {
    var $sel = jQuery('#pmAddrSelect');
    if ($sel.data('select2')) $sel.val(null).trigger('change');
    document.getElementById('pmAddrPrimary').checked = false;
    document.getElementById('pmAddrProofFile').value = '';
    document.getElementById('pmAddrProofName').value = 'No file selected';
    document.getElementById('pmAddrEffDate').value = '';
}

function pmRenderAddrCards(savedAddrs) {
    var container = document.getElementById('pmAddrCards');
    var h = '';

    if (savedAddrs) {
        savedAddrs.forEach(function(a) {
            if (a.delinked_at) return;
            h += pmRenderSingleAddrCard(a, 'saved');
        });
    }

    pmPendingAddrs.forEach(function(a) {
        h += pmRenderSingleAddrCard(a, 'pending');
    });

    container.innerHTML = h;
}

function pmRenderSingleAddrCard(a, type) {
    var addr = a.google_formatted_address || '';
    var parts = addr.split(',').map(function(s) { return s.trim(); });
    var id = type === 'saved' ? a.link_id : a.tempId;
    var cardId = type === 'saved' ? 'pmAddrExist_' + id : 'pmAddrPending_' + id;

    var h = '<div class="pm-addr-card" id="' + cardId + '">';

    for (var i = 0; i < parts.length; i++) {
        h += '<div class="pm-addr-card-row">' + parts[i] + '</div>';
    }

    h += '<div class="pm-addr-badges">';
    if (a.is_primary) h += '<span class="pm-addr-badge pm-addr-badge-primary">Primary</span>';
    if (a.start_date) h += '<span class="pm-addr-badge pm-addr-badge-from">From ' + pmFmtDate(a.start_date) + '</span>';
    if (a.end_date) h += '<span class="pm-addr-badge pm-addr-badge-to">To ' + pmFmtDate(a.end_date) + '</span>';

    if (a.has_proof && !a.proof_expired) {
        h += '<span class="pm-addr-badge pm-addr-badge-proof-valid pm-addr-proof-badge">Proof of Address' + (a.proof_expiry_date ? ' - Expires ' + pmFmtDate(a.proof_expiry_date) : '') + '</span>';
    }
    if (a.has_proof && a.proof_expired) {
        h += '<span class="pm-addr-badge pm-addr-badge-proof-expired pm-addr-proof-badge">Proof of Address - Expired' + (a.proof_expiry_date ? ' ' + pmFmtDate(a.proof_expiry_date) : '') + '</span>';
    }
    h += '</div>';

    h += '<div class="pm-addr-card-actions">';
    if (a.latitude && a.longitude) {
        h += '<button class="pm-addr-action-btn map" onclick="window.open(\'https://maps.google.com/?q=' + a.latitude + ',' + a.longitude + '\',\'_blank\')" title="View on Map"><i class="fas fa-map-marked-alt"></i></button>';
    }
    h += '<button class="pm-addr-action-btn edit" onclick="pmEditAddrProof(\'' + type + '_' + id + '\')" title="Upload Proof"><i class="fas fa-pen"></i></button>';

    if (type === 'pending') {
        h += '<button class="pm-addr-action-btn delink" onclick="pmRemovePendingAddr(' + id + ')" title="Remove"><i class="fas fa-unlink"></i></button>';
    } else {
        h += '<button class="pm-addr-action-btn delink" onclick="pmDelinkSavedAddr(' + id + ')" title="Delink"><i class="fas fa-unlink"></i></button>';
    }
    h += '</div></div>';

    return h;
}

function pmRemovePendingAddr(tempId) {
    NxAlert.confirm('Remove Address?', 'This pending address link will be removed.', 'Yes, Remove')
        .then(function(result) {
            if (result.isConfirmed) {
                pmPendingAddrs = pmPendingAddrs.filter(function(a) { return a.tempId !== tempId; });
                pmRenderAddrCards();
            }
        });
}

function pmDelinkSavedAddr(linkId) {
    Swal.fire({
        title: 'Delink Address?',
        html: 'Enter the end date for this address.<br>The address history will be preserved.',
        input: 'text',
        inputPlaceholder: 'Select end date...',
        showCancelButton: true,
        confirmButtonText: 'Delink',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0891b2',
        width: 'auto',
        background: '#ffffff',
        customClass: {
            popup: 'nx-swal-popup nx-swal-confirm',
            title: 'nx-swal-title',
            htmlContainer: 'nx-swal-html',
            actions: 'nx-swal-actions',
            input: 'nx-swal-input'
        },
        footer: 'NexCore Africa Proprietary Limited',
        didOpen: function(popup) {
            flatpickr(popup.querySelector('.swal2-input'), { dateFormat: 'j M Y', allowInput: true, defaultDate: new Date() });
        }
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            var personId = document.getElementById('pmFormId').value;
            fetch(pmApiBase.replace('/persons', '') + '/addresses/' + linkId + '/delink', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pmCsrf() },
                body: JSON.stringify({ person_id: personId, end_date: result.value })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    pmAlert('success', 'Delinked', 'Address delinked successfully.');
                    var card = document.getElementById('pmAddrExist_' + linkId);
                    if (card) card.remove();
                } else {
                    pmAlert('error', 'Error', d.message || 'Failed to delink address.');
                }
            });
        }
    });
}

/* =============================================================
   ADDRESS: Proof Edit (Inline)
   ============================================================= */
function pmEditAddrProof(linkRef) {
    pmEditingAddrId = linkRef;
    var parts = linkRef.split('_');
    var type = parts[0];
    var id = parts.slice(1).join('_');

    var cardId = type === 'saved' ? 'pmAddrExist_' + id : 'pmAddrPending_' + id;
    var card = document.getElementById(cardId);
    if (!card) return;

    card.classList.add('pm-addr-editing');

    var addrText = card.querySelector('.pm-addr-card-row') ? card.querySelector('.pm-addr-card-row').textContent : '';

    var editHtml = '<div class="pm-addr-proof-edit" id="pmAddrProofEditForm">';
    editHtml += '<div class="pm-addr-proof-edit-addr">' + addrText + '</div>';
    editHtml += '<div class="pm-addr-proof-edit-row">';
    editHtml += '<div class="nx-form-group"><label class="nx-form-label">Browse File</label><input type="file" id="pmAddrEditFile" class="nx-form-input nx-form-input-file" accept=".pdf,.jpg,.jpeg,.png"></div>';
    editHtml += '<div class="nx-form-group"><label class="nx-form-label">File Name</label><input type="text" id="pmAddrEditFileName" class="nx-form-input" readonly placeholder="No file selected"></div>';
    editHtml += '<div class="nx-form-group"><label class="nx-form-label">Effective Date</label><input type="text" id="pmAddrEditEffDate" class="nx-form-input pm-date" placeholder="Select date..."></div>';
    editHtml += '</div>';
    editHtml += '<div class="pm-addr-proof-edit-actions">';
    editHtml += '<button type="button" class="button_master_update" onclick="pmSaveAddrProofEdit(\'' + linkRef + '\')"><i class="fas fa-check"></i> Update</button>';
    editHtml += '<button type="button" class="button_master_cancel" onclick="pmCancelAddrProofEdit()">Cancel</button>';
    editHtml += '</div></div>';

    card.insertAdjacentHTML('afterend', editHtml);

    document.getElementById('pmAddrEditFile').addEventListener('change', function() {
        document.getElementById('pmAddrEditFileName').value = this.files.length > 0 ? this.files[0].name : 'No file selected';
    });

    flatpickr('#pmAddrEditEffDate', { dateFormat: 'j M Y', allowInput: true });
}

function pmCancelAddrProofEdit() {
    var editForm = document.getElementById('pmAddrProofEditForm');
    if (editForm) editForm.remove();
    document.querySelectorAll('.pm-addr-editing').forEach(function(c) { c.classList.remove('pm-addr-editing'); });
    pmEditingAddrId = null;
}

function pmSaveAddrProofEdit(linkRef) {
    var parts = linkRef.split('_');
    var type = parts[0];
    var id = parts.slice(1).join('_');
    var fileInput = document.getElementById('pmAddrEditFile');
    var effDate = document.getElementById('pmAddrEditEffDate').value;

    if (!fileInput.files.length) {
        pmAlert('warning', 'Required', 'Please select a file to upload.');
        return;
    }
    if (!effDate) {
        pmAlert('warning', 'Required', 'Please enter the effective date.');
        return;
    }

    if (type === 'pending') {
        var tempId = parseInt(id);
        pmPendingAddrs.forEach(function(a) {
            if (a.tempId === tempId) {
                a.proof_file = fileInput.files[0];
                a.effective_date = effDate;
            }
        });
        pmCancelAddrProofEdit();
        pmAlert('success', 'Updated', 'Proof of address will be uploaded when you save.');
        return;
    }

    var personId = document.getElementById('pmFormId').value;
    var fd = new FormData();
    fd.append('person_id', personId);
    fd.append('file', fileInput.files[0]);
    if (effDate) fd.append('effective_date', effDate);

    fetch(pmApiBase.replace('/persons', '') + '/addresses/proof', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': pmCsrf() },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var uplinkId = id;
            pmCancelAddrProofEdit();

            var expDt = '';
            if (effDate) {
                var dp = new Date(effDate);
                if (isNaN(dp)) {
                    var pts = effDate.match(/(\d+)\s+(\w+)\s+(\d+)/);
                    if (pts) {
                        var mos = {Jan:0,Feb:1,Mar:2,Apr:3,May:4,Jun:5,Jul:6,Aug:7,Sep:8,Oct:9,Nov:10,Dec:11};
                        dp = new Date(pts[3], mos[pts[2]], pts[1]);
                    }
                }
                dp.setDate(dp.getDate() + 85);
                expDt = dp.getDate() + ' ' + ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][dp.getMonth()] + ' ' + dp.getFullYear();
            }

            var badgeCard = document.getElementById('pmAddrExist_' + uplinkId);
            if (badgeCard) {
                var badges = badgeCard.querySelector('.pm-addr-badges');
                if (badges) {
                    var oldP = badges.querySelectorAll('.pm-addr-proof-badge');
                    oldP.forEach(function(b) { b.remove(); });

                    var now = new Date();
                    var expCheck = new Date(effDate);
                    if (isNaN(expCheck) && effDate) {
                        var pts2 = effDate.match(/(\d+)\s+(\w+)\s+(\d+)/);
                        if (pts2) {
                            var mos2 = {Jan:0,Feb:1,Mar:2,Apr:3,May:4,Jun:5,Jul:6,Aug:7,Sep:8,Oct:9,Nov:10,Dec:11};
                            expCheck = new Date(pts2[3], mos2[pts2[2]], pts2[1]);
                        }
                    }
                    expCheck.setDate(expCheck.getDate() + 85);

                    if (expCheck < now) {
                        badges.insertAdjacentHTML('beforeend', '<span class="pm-addr-badge pm-addr-badge-proof-expired pm-addr-proof-badge">Proof of Address - Expired ' + expDt + '</span>');
                    } else {
                        badges.insertAdjacentHTML('beforeend', '<span class="pm-addr-badge pm-addr-badge-proof-valid pm-addr-proof-badge">Proof of Address - Expires ' + expDt + '</span>');
                    }
                }
            }

            pmAlert('success', 'Uploaded', 'Proof of address uploaded successfully.');
        } else {
            pmAlert('error', 'Error', d.message || 'Upload failed.');
        }
    })
    .catch(function(e) { pmAlert('error', 'Error', 'Network error: ' + e.message); });
}

/* =============================================================
   MODAL: Save Person
   ============================================================= */
function pmSaveForm() {
    var form = document.getElementById('pmForm');
    var fd = new FormData(form);
    var id = document.getElementById('pmFormId').value;
    var url = id ? pmApiBase + '/' + id : pmApiBase + '/store';

    if (id) fd.append('_method', 'PUT');

    if (pmPendingAddrs.length > 0) {
        fd.append('pending_addresses', JSON.stringify(pmPendingAddrs.map(function(a) {
            return {
                address_id: a.address_id,
                is_primary: a.is_primary,
                effective_date: a.effective_date || ''
            };
        })));
    }

    if (pmRemovedAddrs.length > 0) {
        fd.append('removed_addresses', JSON.stringify(pmRemovedAddrs));
    }

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': pmCsrf() },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            pmAlert('success', 'Success', d.message);
            document.getElementById('pmModal').classList.remove('nx-modal-open');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            pmAlert('error', 'Error', d.message || 'Failed to save');
        }
    })
    .catch(function(e) { pmAlert('error', 'Error', 'Network error: ' + e.message); });
}

/* =============================================================
   DRAWER ACTIONS: Contacts
   ============================================================= */
function pmAddContact() {
    var v = document.getElementById('pmACVal').value;
    if (!v) { pmAlert('warning', 'Required', 'Enter a contact value'); return; }

    fetch(pmApiBase + '/contacts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pmCsrf() },
        body: JSON.stringify({
            person_id: pmCurrentId,
            contact_type: document.getElementById('pmACType').value,
            contact_value: v,
            label: document.getElementById('pmACLabel').value,
            is_primary: document.getElementById('pmACPrimary').checked ? 1 : 0
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { pmAlert('success', 'Added', d.message); setTimeout(function() { location.reload(); }, 800); }
        else { pmAlert('error', 'Error', d.message); }
    });
}

function pmDelContact(id) {
    NxAlert.confirm('Remove Contact?', 'This contact will be removed.', 'Yes, Remove')
        .then(function(r) {
            if (r.isConfirmed) {
                fetch(pmApiBase + '/contacts/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': pmCsrf() } })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.success) { pmAlert('success', 'Removed', d.message); setTimeout(function() { location.reload(); }, 800); }
                    });
            }
        });
}

/* =============================================================
   DRAWER ACTIONS: Banking
   ============================================================= */
function pmAddBank() {
    var acc = document.getElementById('pmABNum').value;
    if (!acc) { pmAlert('warning', 'Required', 'Enter an account number'); return; }

    fetch(pmApiBase + '/banks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pmCsrf() },
        body: JSON.stringify({
            person_id: pmCurrentId,
            bank_id: document.getElementById('pmABBank').value || null,
            bank_account_type_id: document.getElementById('pmABType').value || null,
            account_number: acc,
            branch_code: document.getElementById('pmABBranch').value,
            account_holder_name: document.getElementById('pmABHolder').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { pmAlert('success', 'Added', d.message); setTimeout(function() { location.reload(); }, 800); }
        else { pmAlert('error', 'Error', d.message); }
    });
}

function pmDelBank(id) {
    NxAlert.confirm('Remove Bank Account?', 'This bank account will be removed.', 'Yes, Remove')
        .then(function(r) {
            if (r.isConfirmed) {
                fetch(pmApiBase + '/banks/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': pmCsrf() } })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.success) { pmAlert('success', 'Closed', d.message); setTimeout(function() { location.reload(); }, 800); }
                    });
            }
        });
}

/* =============================================================
   DRAWER ACTIONS: Relationships
   ============================================================= */
function pmAddRel() {
    var rid = document.getElementById('pmARId').value;
    if (!rid) { pmAlert('warning', 'Required', 'Search and select a related person'); return; }

    fetch(pmApiBase + '/relationships', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pmCsrf() },
        body: JSON.stringify({
            person_id: pmCurrentId,
            related_person_id: rid,
            relationship_type_id: document.getElementById('pmARType').value,
            start_date: document.getElementById('pmARDate').value || null
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { pmAlert('success', 'Added', d.message); setTimeout(function() { location.reload(); }, 800); }
        else { pmAlert('error', 'Error', d.message); }
    });
}

function pmDelRel(id) {
    NxAlert.confirm('Remove Relationship?', 'This relationship link will be removed.', 'Yes, Remove')
        .then(function(r) {
            if (r.isConfirmed) {
                fetch(pmApiBase + '/relationships/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': pmCsrf() } })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.success) { pmAlert('success', 'Removed', d.message); setTimeout(function() { location.reload(); }, 800); }
                    });
            }
        });
}

var pmRelST;
function pmSearchRel(q) {
    clearTimeout(pmRelST);
    var res = document.getElementById('pmARResults');
    if (q.length < 2) { res.style.display = 'none'; return; }

    pmRelST = setTimeout(function() {
        fetch(pmApiBase + '/search?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.results || data.results.length === 0) { res.style.display = 'none'; return; }
                var html = '';
                data.results.forEach(function(p) {
                    if (p.id == pmCurrentId) return;
                    html += '<div class="pm-dd-search-item" onclick="pmSelRel(' + p.id + ',\'' + (p.title_abbr ? p.title_abbr + ' ' : '') + p.first_name + ' ' + p.last_name + '\')">';
                    html += '<strong>' + (p.title_abbr ? p.title_abbr + ' ' : '') + p.first_name + ' ' + p.last_name + '</strong>';
                    if (p.identity_number) html += ' <span class="pm-dd-search-item-id">| ' + p.identity_number + '</span>';
                    html += '</div>';
                });
                res.innerHTML = html;
                res.style.display = 'block';
            });
    }, 300);
}

function pmSelRel(id, name) {
    document.getElementById('pmARId').value = id;
    document.getElementById('pmARSearch').value = name;
    document.getElementById('pmARResults').style.display = 'none';
}

/* =============================================================
   DRAWER ACTIONS: Documents
   ============================================================= */
function pmAddDoc() {
    var name = document.getElementById('pmADName').value;
    var file = document.getElementById('pmADFile').files[0];
    if (!name || !file) { pmAlert('warning', 'Required', 'Enter name and select file'); return; }

    var fd = new FormData();
    fd.append('person_id', pmCurrentId);
    fd.append('document_name', name);
    fd.append('document_type', document.getElementById('pmADType').value);
    fd.append('file', file);
    var exp = document.getElementById('pmADExpiry').value;
    if (exp) fd.append('expiry_date', exp);
    var notes = document.getElementById('pmADNotes').value;
    if (notes) fd.append('notes', notes);

    fetch(pmApiBase + '/documents', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': pmCsrf() },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { pmAlert('success', 'Uploaded', d.message); setTimeout(function() { location.reload(); }, 800); }
        else { pmAlert('error', 'Error', d.message); }
    });
}

function pmDelDoc(id) {
    NxAlert.confirm('Remove Document?', 'This document will be removed.', 'Yes, Remove')
        .then(function(r) {
            if (r.isConfirmed) {
                fetch(pmApiBase + '/documents/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': pmCsrf() } })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.success) { pmAlert('success', 'Removed', d.message); setTimeout(function() { location.reload(); }, 800); }
                    });
            }
        });
}

/* =============================================================
   DOM READY
   ============================================================= */
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.pm-date', { dateFormat: 'j M Y', allowInput: true });
});
</script>
@endpush
