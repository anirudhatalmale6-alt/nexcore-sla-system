{{-- Shared Nerve Centre Sidebar — included on all client-context pages --}}
<aside class="nx-sidebar" id="nxSidebar">
    <div class="nx-sidebar-inner">
        {{-- NexCore Branding --}}
        <div class="nx-sb-brand">
            <a href="{{ route('nexcore.clients.index') }}" class="nx-sb-brand-link" title="Back to NexCore">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <rect x="2" y="2" width="9" height="9" rx="2" fill="#059669"/>
                    <rect x="13" y="2" width="9" height="9" rx="2" fill="#2563eb"/>
                    <rect x="2" y="13" width="9" height="9" rx="2" fill="#d97706"/>
                    <rect x="13" y="13" width="9" height="9" rx="2" fill="#7c3aed"/>
                </svg>
                <span class="nx-sb-text nx-sb-brand-text">NexCore</span>
            </a>
            <button class="nx-sb-toggle" id="nxSbToggle" onclick="nxToggleSidebar()" title="Toggle Sidebar">
                <i class="fas fa-chevron-left" id="nxSbToggleIcon"></i>
            </button>
        </div>

        {{-- Client Identity --}}
        <div class="nx-sb-client">
            <div class="nx-sb-avatar">
                @if($client->client_logo)
                    <img src="{{ asset($client->client_logo) }}" alt="{{ $client->client_code }}">
                @else
                    {{ substr($client->client_code ?? '?', 0, 3) }}
                @endif
            </div>
            <div class="nx-sb-client-info" id="nxSbClientInfo">
                <div class="nx-sb-client-name">{{ \Illuminate\Support\Str::limit($client->company_name, 22) }}</div>
                <div class="nx-sb-client-code">{{ $client->client_code }}</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="nx-sb-nav" id="nxSbNav">
            {{-- 1. Client Profile --}}
            <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}" class="nx-sb-link {{ request()->routeIs('nexcore.clients.show.dashboard') ? 'nx-sb-link-active' : '' }}">
                <i class="fas fa-id-card"></i>
                <span class="nx-sb-text">CLIENT PROFILE</span>
            </a>

            {{-- Command Centre --}}
            <a href="{{ route('nexcore.command-centre') }}" class="nx-sb-link">
                <i class="fas fa-satellite-dish" style="color:#06b6d4;"></i>
                <span class="nx-sb-text">COMMAND CENTRE</span>
            </a>

            {{-- 2. Dashboards --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-tachometer-alt" style="color:#f59e0b;"></i> <span class="nx-sb-text">Dashboards</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->is('*/dashboard/business-operations','*/dashboard/compliance-command','*/dashboard/revenue-billing','*/dashboard/business-growth','*/dashboard/executive-overview') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'business-operations']) }}" class="nx-sb-child {{ request()->is('*/dashboard/business-operations') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-building"></i> <span class="nx-sb-text">Business Operations Hub</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'compliance-command']) }}" class="nx-sb-child {{ request()->is('*/dashboard/compliance-command') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-shield-alt"></i> <span class="nx-sb-text">Compliance Command Centre</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'revenue-billing']) }}" class="nx-sb-child {{ request()->is('*/dashboard/revenue-billing') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-coins"></i> <span class="nx-sb-text">Revenue & Billing Intelligence</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'business-growth']) }}" class="nx-sb-child {{ request()->is('*/dashboard/business-growth') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-rocket"></i> <span class="nx-sb-text">Business Growth Insights</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'executive-overview']) }}" class="nx-sb-child {{ request()->is('*/dashboard/executive-overview') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-crown"></i> <span class="nx-sb-text">Executive Client Overview</span></a>
                </div>
            </div>

            {{-- 3. Tasks (Enhanced) --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-tasks" style="color:#10b981;"></i> <span class="nx-sb-text">Tasks</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.tasks*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.tasks', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.tasks') && !request()->routeIs('nexcore.clients.show.tasks.kanban') && !request()->routeIs('nexcore.clients.show.tasks.calendar') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-list-ul"></i> <span class="nx-sb-text">Task List</span></a>
                    <a href="{{ route('nexcore.clients.show.tasks.kanban', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.tasks.kanban') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-columns"></i> <span class="nx-sb-text">Kanban Board</span></a>
                    <a href="{{ route('nexcore.clients.show.tasks.calendar', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.tasks.calendar') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-calendar-alt"></i> <span class="nx-sb-text">Calendar View</span></a>
                    <a href="{{ route('nexcore.clients.show.tasks.create', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.tasks.create') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-plus-circle"></i> <span class="nx-sb-text">New Task</span></a>
                </div>
            </div>

            {{-- 4. Reminders --}}
            <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'reminders']) }}" class="nx-sb-link {{ request()->is('*/dashboard/reminders') ? 'nx-sb-link-active' : '' }}">
                <i class="fas fa-bell" style="color:#f59e0b;"></i> <span class="nx-sb-text">REMINDERS</span>
            </a>

            {{-- 5. Appointments --}}
            <a href="{{ route('nexcore.clients.show.dashboard.panel', [$client->id, 'appointments']) }}" class="nx-sb-link {{ request()->is('*/dashboard/appointments') ? 'nx-sb-link-active' : '' }}">
                <i class="fas fa-calendar-check" style="color:#0891b2;"></i> <span class="nx-sb-text">APPOINTMENTS</span>
            </a>

            {{-- SLA / Engagement Letters --}}
            <a href="{{ route('nexcore.clients.show.sla', $client->id) }}" class="nx-sb-link {{ request()->routeIs('nexcore.clients.show.sla*') ? 'nx-sb-link-active' : '' }}">
                <i class="fas fa-file-contract" style="color:#f97316;"></i> <span class="nx-sb-text">ENGAGEMENT LETTERS</span>
            </a>

            {{-- Directors --}}
            <a href="{{ route('nexcore.clients.show.director-master', $client->id) }}" class="nx-sb-link {{ request()->routeIs('nexcore.clients.show.director-master*') ? 'nx-sb-link-active' : '' }}">
                <i class="fas fa-user-tie" style="color:#8b5cf6;"></i> <span class="nx-sb-text">DIRECTORS</span>
            </a>

            {{-- 6. Documents --}}
            <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=documents" class="nx-sb-link {{ request('tab') === 'documents' ? 'nx-sb-link-active' : '' }}">
                <i class="fas fa-folder-open" style="color:#ef4444;"></i> <span class="nx-sb-text">DOCUMENTS</span>
            </a>

            {{-- 7. Accounting --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-calculator" style="color:#059669;"></i> <span class="nx-sb-text">Accounting</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.accounting.*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.accounting.trial-balance', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.trial-balance*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-balance-scale"></i> <span class="nx-sb-text">Trial Balance</span></a>
                    <a href="{{ route('nexcore.clients.show.accounting.income-statement', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.income-statement*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> <span class="nx-sb-text">Income Statement</span></a>
                    <a href="{{ route('nexcore.clients.show.accounting.balance-sheet', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.balance-sheet*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-chart-pie"></i> <span class="nx-sb-text">Balance Sheet</span></a>
                    <a href="{{ route('nexcore.clients.show.accounting.journals', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.journals*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-book"></i> <span class="nx-sb-text">Journals</span></a>
                    <a href="{{ route('nexcore.clients.show.accounting.ledger', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.ledger*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-scroll"></i> <span class="nx-sb-text">General Ledger</span></a>
                    @php $coaCount = \Modules\NexcoreClientManager\Models\NexcoreGlChartOfAccount::where('company_id', $client->id)->count(); @endphp
                    @if($coaCount === 0)
                    <a href="{{ route('nexcore.clients.show.accounting.setup-coa', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.setup-coa') ? 'nx-sb-link-active' : '' }}" style="color:#f59e0b;"><i class="fas fa-magic"></i> <span class="nx-sb-text">Setup COA</span></a>
                    @endif
                    <a href="{{ route('nexcore.clients.show.accounting.accounts', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.accounts*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-list-ol"></i> <span class="nx-sb-text">Chart of Accounts</span></a>
                </div>
            </div>

            {{-- 8. Banking --}}
            <a href="{{ route('nexcore.clients.show.accounting.bank.accounts', $client->id) }}" class="nx-sb-link {{ request()->routeIs('nexcore.clients.show.accounting.bank*') ? 'nx-sb-link-active' : '' }}" style="text-decoration:none;">
                <i class="fas fa-landmark" style="color:#0891b2;"></i> <span class="nx-sb-text">BANKING</span>
            </a>

            {{-- 9. Payroll --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-money-check-alt" style="color:#d97706;"></i> <span class="nx-sb-text">Payroll</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.payroll*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.payroll.employees', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.payroll.employees*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-users"></i> <span class="nx-sb-text">Employees</span></a>
                    <a href="{{ route('nexcore.clients.show.payroll.periods', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.payroll.periods*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-calendar-alt"></i> <span class="nx-sb-text">Pay Periods</span></a>
                    <a href="{{ route('nexcore.clients.show.payroll.payslips', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.payroll.payslips*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-file-alt"></i> <span class="nx-sb-text">Payslips</span></a>
                    <a href="{{ route('nexcore.clients.show.payroll.mibco', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.payroll.mibco*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-handshake"></i> <span class="nx-sb-text">MIBCO</span></a>
                    <a href="{{ route('nexcore.clients.show.payroll.reports', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.payroll.reports*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-chart-bar"></i> <span class="nx-sb-text">Reports</span></a>
                </div>
            </div>

            {{-- Returns --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-file-contract" style="color:#f59e0b;"></i> <span class="nx-sb-text">Returns</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.returns.*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.returns.itr14', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.returns.itr14') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-file-alt"></i> <span class="nx-sb-text">ITR 14 Return</span></a>
                    <a href="{{ route('nexcore.clients.show.returns.vat201v2', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.returns.vat201v2') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> <span class="nx-sb-text">VAT 201 Return</span></a>
                    <a href="{{ route('nexcore.clients.show.returns.emp201', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.returns.emp201') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-users-cog"></i> <span class="nx-sb-text">EMP 201 Return</span></a>
                    <a href="{{ route('nexcore.clients.show.returns.client-docs', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.returns.client-docs') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-clipboard-check"></i> <span class="nx-sb-text">Client Docs</span></a>
                </div>
            </div>

            {{-- 10. Reporting --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-chart-bar" style="color:#f59e0b;"></i> <span class="nx-sb-text">Reporting</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.accounting.management-pack*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.accounting.management-pack', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.accounting.management-pack*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-briefcase"></i> <span class="nx-sb-text">Management Report</span></a>
                    <a href="/cims/accounts/management-accounts/{{ $client->id }}?period=q1" class="nx-sb-child"><i class="fas fa-file-alt"></i> <span class="nx-sb-text">Report Pack</span></a>
                </div>
            </div>

            {{-- 11. Compliance --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-shield-alt" style="color:#8b5cf6;"></i> <span class="nx-sb-text">Compliance</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.sars*','nexcore.clients.show.cipc*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.sars', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.sars*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> <span class="nx-sb-text">SARS Returns</span> <span class="nx-sb-badge nx-sb-text">{{ $client->sarsReturns()->count() }}</span></a>
                    <a href="{{ route('nexcore.clients.show.cipc', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.cipc*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-clipboard-check"></i> <span class="nx-sb-text">CIPC Returns</span> <span class="nx-sb-badge nx-sb-text">{{ $client->cipcReturns()->count() }}</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=compliance&sub=comp-bee" class="nx-sb-child"><i class="fas fa-certificate"></i> <span class="nx-sb-text">BEE</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=compliance&sub=comp-coida" class="nx-sb-child"><i class="fas fa-hard-hat"></i> <span class="nx-sb-text">COIDA</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=compliance&sub=comp-uif" class="nx-sb-child"><i class="fas fa-hand-holding-usd"></i> <span class="nx-sb-text">UIF</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=compliance&sub=comp-paye" class="nx-sb-child"><i class="fas fa-receipt"></i> <span class="nx-sb-text">PAYE</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=compliance&sub=comp-sdl" class="nx-sb-child"><i class="fas fa-graduation-cap"></i> <span class="nx-sb-text">SDL</span></a>
                    <a href="{{ route('nexcore.clients.show.dashboard', $client->id) }}?tab=compliance&sub=comp-unions" class="nx-sb-child"><i class="fas fa-users-cog"></i> <span class="nx-sb-text">Unions / MIBCO</span></a>
                </div>
            </div>

            {{-- 12. Operations --}}
            <div class="nx-sb-section">
                <button class="nx-sb-heading" onclick="nxToggleSection(this)">
                    <span class="nx-sb-heading-left"><i class="fas fa-cogs" style="color:#0891b2;"></i> <span class="nx-sb-text">Operations</span></span>
                    <i class="fas fa-chevron-down nx-sb-arrow nx-sb-text"></i>
                </button>
                <div class="nx-sb-children {{ request()->routeIs('nexcore.clients.show.meetings*','nexcore.clients.show.alerts*','nexcore.clients.show.audit*') ? 'nx-sb-open' : '' }}">
                    <a href="{{ route('nexcore.clients.show.meetings', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.meetings*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-video"></i> <span class="nx-sb-text">Meetings</span></a>
                    <a href="{{ route('nexcore.clients.show.alerts', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.alerts*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-bell"></i> <span class="nx-sb-text">Alerts</span></a>
                    <a href="{{ route('nexcore.clients.show.audit', $client->id) }}" class="nx-sb-child {{ request()->routeIs('nexcore.clients.show.audit*') ? 'nx-sb-link-active' : '' }}"><i class="fas fa-history"></i> <span class="nx-sb-text">Audit Trail</span></a>
                </div>
            </div>
        </nav>

        {{-- Footer --}}
        <div class="nx-sb-footer" id="nxSbFooter">
            <a href="{{ route('nexcore.clients.index') }}" class="nx-sb-back">
                <i class="fas fa-arrow-left"></i> <span class="nx-sb-text">All Clients</span>
            </a>
            <a href="/home" class="nx-sb-back" style="opacity:0.5;">
                <i class="fas fa-home"></i> <span class="nx-sb-text">Back to CIMS</span>
            </a>
        </div>
    </div>
</aside>

{{-- Sidebar JS --}}
<script>
var _nxSbCollapsed = false;

function nxToggleSidebar() {
    _nxSbCollapsed = !_nxSbCollapsed;
    var layout = document.getElementById('nxNerveCentre');
    var icon = document.getElementById('nxSbToggleIcon');
    if (_nxSbCollapsed) {
        layout.classList.add('nx-sb-collapsed');
        icon.className = 'fas fa-chevron-right';
    } else {
        layout.classList.remove('nx-sb-collapsed');
        icon.className = 'fas fa-chevron-left';
    }
    localStorage.setItem('nxSidebarCollapsed', _nxSbCollapsed ? '1' : '0');
}

function nxToggleSection(btn) {
    if (_nxSbCollapsed) return;
    var children = btn.nextElementSibling;
    var arrow = btn.querySelector('.nx-sb-arrow');
    var isOpen = children.classList.contains('nx-sb-open');

    document.querySelectorAll('.nx-sb-children.nx-sb-open').forEach(function(ch) {
        ch.classList.remove('nx-sb-open');
        ch.style.maxHeight = '0px';
        var a = ch.previousElementSibling ? ch.previousElementSibling.querySelector('.nx-sb-arrow') : null;
        if (a) a.style.transform = 'rotate(-90deg)';
    });

    if (!isOpen) {
        children.classList.add('nx-sb-open');
        children.style.maxHeight = children.scrollHeight + 'px';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
}

(function() {
    if (localStorage.getItem('nxSidebarCollapsed') === '1') {
        _nxSbCollapsed = true;
        var layout = document.getElementById('nxNerveCentre');
        if (layout) layout.classList.add('nx-sb-collapsed');
        var icon = document.getElementById('nxSbToggleIcon');
        if (icon) icon.className = 'fas fa-chevron-right';
    }
    document.querySelectorAll('.nx-sb-children').forEach(function(ch) {
        if (ch.classList.contains('nx-sb-open')) {
            ch.style.maxHeight = ch.scrollHeight + 'px';
        } else {
            ch.style.maxHeight = '0px';
            var arrow = ch.previousElementSibling ? ch.previousElementSibling.querySelector('.nx-sb-arrow') : null;
            if (arrow) arrow.style.transform = 'rotate(-90deg)';
        }
    });
})();
</script>
