<?php

use Illuminate\Support\Facades\Route;
use Modules\NexcoreClientManager\Http\Controllers\ClientController;
use Modules\NexcoreClientManager\Http\Controllers\AddressController;
use Modules\NexcoreClientManager\Http\Controllers\ContactController;
use Modules\NexcoreClientManager\Http\Controllers\BankingController;
use Modules\NexcoreClientManager\Http\Controllers\DirectorController;
use Modules\NexcoreClientManager\Http\Controllers\SarsController;
use Modules\NexcoreClientManager\Http\Controllers\CipcController;
use Modules\NexcoreClientManager\Http\Controllers\FinancialController;
use Modules\NexcoreClientManager\Http\Controllers\DocumentController;
use Modules\NexcoreClientManager\Http\Controllers\TaskController;
use Modules\NexcoreClientManager\Http\Controllers\MeetingController;
use Modules\NexcoreClientManager\Http\Controllers\AlertController;
use Modules\NexcoreClientManager\Http\Controllers\AuditController;
use Modules\NexcoreClientManager\Http\Controllers\EmployeeController;
use Modules\NexcoreClientManager\Http\Controllers\PayPeriodController;
use Modules\NexcoreClientManager\Http\Controllers\PayslipController;
use Modules\NexcoreClientManager\Http\Controllers\MibcoController;
use Modules\NexcoreClientManager\Http\Controllers\PayrollReportController;
use Modules\NexcoreClientManager\Http\Controllers\AccountingDashboardController;
use Modules\NexcoreClientManager\Http\Controllers\AccountController;
use Modules\NexcoreClientManager\Http\Controllers\JournalController;
use Modules\NexcoreClientManager\Http\Controllers\TrialBalanceController;
use Modules\NexcoreClientManager\Http\Controllers\IncomeStatementController;
use Modules\NexcoreClientManager\Http\Controllers\BalanceSheetController;
use Modules\NexcoreClientManager\Http\Controllers\GeneralLedgerController;
use Modules\NexcoreClientManager\Http\Controllers\NexcoreBankAccountController;
use Modules\NexcoreClientManager\Http\Controllers\BankImportController;
use Modules\NexcoreClientManager\Http\Controllers\BankAllocationController;
use Modules\NexcoreClientManager\Http\Controllers\ManagementPackController;
use Modules\NexcoreClientManager\Http\Controllers\PracticeController;
use Modules\NexcoreClientManager\Http\Controllers\ClerkController;
use Modules\NexcoreClientManager\Http\Controllers\DashboardPanelController;
use Modules\NexcoreClientManager\Http\Controllers\NexcoreDirectorMasterController;
use Modules\NexcoreClientManager\Http\Controllers\SlaController;

/*
|--------------------------------------------------------------------------
| NexCore Client Manager Routes
|--------------------------------------------------------------------------
| Prefix: /nexcore/clients
| Name: nexcore.clients.
*/

// Client Index (Practice Overview)
Route::get('/', [ClientController::class, 'index'])->name('index');

// Practice Management (top-level, before client context)
Route::prefix('practices')->name('practices.')->group(function () {
    Route::get('/', [PracticeController::class, 'index'])->name('index');
    Route::get('/create', [PracticeController::class, 'create'])->name('create');
    Route::post('/', [PracticeController::class, 'store'])->name('store');
    Route::get('/{practice}/edit', [PracticeController::class, 'edit'])->name('edit');
    Route::put('/{practice}', [PracticeController::class, 'update'])->name('update');
    Route::delete('/{practice}', [PracticeController::class, 'destroy'])->name('destroy');
    Route::post('/{practice}/toggle', [PracticeController::class, 'toggle'])->name('toggle');
});

// Clerk Management (top-level, before client context)
Route::prefix('clerks')->name('clerks.')->group(function () {
    Route::get('/', [ClerkController::class, 'index'])->name('index');
    Route::get('/create', [ClerkController::class, 'create'])->name('create');
    Route::post('/', [ClerkController::class, 'store'])->name('store');
    Route::get('/{clerk}/edit', [ClerkController::class, 'edit'])->name('edit');
    Route::put('/{clerk}', [ClerkController::class, 'update'])->name('update');
    Route::delete('/{clerk}', [ClerkController::class, 'destroy'])->name('destroy');
    Route::post('/{clerk}/toggle', [ClerkController::class, 'toggle'])->name('toggle');
});

// Manage COA (top-level, before client context)
Route::get('/manage-coa', [AccountController::class, 'manageCoa'])->name('manage-coa');
Route::delete('/manage-coa/{client}/reset', [AccountController::class, 'resetClientAccounting'])->name('manage-coa.reset');

// System Master Form (Design Gold Standard)
Route::view('/system-master', 'nexcore_client_manager::system-master')->name('system-master');

// Client CRUD
Route::get('/create', [ClientController::class, 'create'])->name('create');
Route::post('/', [ClientController::class, 'store'])->name('store');

// Client Dashboard (single client context)
Route::prefix('{client}')->name('show.')->where(['client' => '[0-9]+'])->group(function () {
    Route::get('/', [ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [ClientController::class, 'dashboard']);
    Route::get('/edit', [ClientController::class, 'edit'])->name('edit');
    Route::put('/', [ClientController::class, 'update'])->name('update');
    Route::delete('/', [ClientController::class, 'destroy'])->name('destroy');
    Route::post('/toggle', [ClientController::class, 'toggle'])->name('toggle');

    // Addresses (backed by PMPRO address registry)
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/toggle', [AddressController::class, 'toggle'])->name('addresses.toggle');
    Route::get('/addresses/search-registry', [AddressController::class, 'searchRegistry'])->name('addresses.search-registry');

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/{contact}/toggle', [ContactController::class, 'toggle'])->name('contacts.toggle');

    // Banking
    Route::get('/banking', [BankingController::class, 'index'])->name('banking');
    Route::get('/banking/create', [BankingController::class, 'create'])->name('banking.create');
    Route::post('/banking', [BankingController::class, 'store'])->name('banking.store');
    Route::get('/banking/{account}/edit', [BankingController::class, 'edit'])->name('banking.edit');
    Route::put('/banking/{account}', [BankingController::class, 'update'])->name('banking.update');
    Route::delete('/banking/{account}', [BankingController::class, 'destroy'])->name('banking.destroy');
    Route::post('/banking/{account}/toggle', [BankingController::class, 'toggle'])->name('banking.toggle');

    // Directors (Legacy)
    Route::get('/directors', [DirectorController::class, 'index'])->name('directors');
    Route::get('/directors/create', [DirectorController::class, 'create'])->name('directors.create');
    Route::post('/directors', [DirectorController::class, 'store'])->name('directors.store');
    Route::get('/directors/{director}/edit', [DirectorController::class, 'edit'])->name('directors.edit');
    Route::put('/directors/{director}', [DirectorController::class, 'update'])->name('directors.update');
    Route::delete('/directors/{director}', [DirectorController::class, 'destroy'])->name('directors.destroy');
    Route::post('/directors/{director}/toggle', [DirectorController::class, 'toggle'])->name('directors.toggle');

    // Director Master (Independent Module)
    Route::get('/director-master', [NexcoreDirectorMasterController::class, 'index'])->name('director-master');
    Route::post('/director-master/search', [NexcoreDirectorMasterController::class, 'search'])->name('director-master.search');
    Route::post('/director-master', [NexcoreDirectorMasterController::class, 'store'])->name('director-master.store');
    Route::put('/director-master/{director}', [NexcoreDirectorMasterController::class, 'update'])->name('director-master.update');
    Route::get('/director-master/addresses', [NexcoreDirectorMasterController::class, 'getAddresses'])->name('director-master.addresses');
    Route::post('/director-master/addresses', [NexcoreDirectorMasterController::class, 'storeAddress'])->name('director-master.addresses.store');
    Route::post('/director-master/link', [NexcoreDirectorMasterController::class, 'linkToClient'])->name('director-master.link');
    Route::delete('/director-master/{director}/unlink', [NexcoreDirectorMasterController::class, 'unlinkFromClient'])->name('director-master.unlink');
    Route::get('/director-master/{director}', [NexcoreDirectorMasterController::class, 'getDirector'])->name('director-master.get');

    // SARS Returns
    Route::get('/sars', [SarsController::class, 'index'])->name('sars');
    Route::get('/sars/create', [SarsController::class, 'create'])->name('sars.create');
    Route::post('/sars', [SarsController::class, 'store'])->name('sars.store');
    Route::get('/sars/{sarsReturn}/edit', [SarsController::class, 'edit'])->name('sars.edit');
    Route::put('/sars/{sarsReturn}', [SarsController::class, 'update'])->name('sars.update');
    Route::delete('/sars/{sarsReturn}', [SarsController::class, 'destroy'])->name('sars.destroy');

    // CIPC Returns
    Route::get('/cipc', [CipcController::class, 'index'])->name('cipc');
    Route::get('/cipc/create', [CipcController::class, 'create'])->name('cipc.create');
    Route::post('/cipc', [CipcController::class, 'store'])->name('cipc.store');
    Route::get('/cipc/{cipcReturn}/edit', [CipcController::class, 'edit'])->name('cipc.edit');
    Route::put('/cipc/{cipcReturn}', [CipcController::class, 'update'])->name('cipc.update');
    Route::delete('/cipc/{cipcReturn}', [CipcController::class, 'destroy'])->name('cipc.destroy');

    // ITR14 Tax Return
    Route::get('/returns/itr14', function ($client) {
        $client = \Modules\NexcoreClientManager\Models\NexcoreClient::findOrFail($client);
        return view('cims_accounts::accounts.itr14', compact('client'));
    })->name('returns.itr14');

    // VAT201 Vendor Declaration
    Route::get('/returns/vat201', function ($client) {
        $client = \Modules\NexcoreClientManager\Models\NexcoreClient::findOrFail($client);
        return view('cims_accounts::accounts.vat201', compact('client'));
    })->name('returns.vat201');

    // VAT201 Vendor Declaration v2
    Route::get('/returns/vat201v2', function ($client) {
        $client = \Modules\NexcoreClientManager\Models\NexcoreClient::findOrFail($client);
        return view('cims_accounts::accounts.vat201v2', compact('client'));
    })->name('returns.vat201v2');

    // EMP201 Monthly Employer Return
    Route::get('/returns/emp201', function ($client) {
        $client = \Modules\NexcoreClientManager\Models\NexcoreClient::findOrFail($client);
        return view('cims_accounts::accounts.emp201', compact('client'));
    })->name('returns.emp201');

    // Client Docs (CIPC InfoDocs)
    Route::get('/returns/client-docs', function ($client) {
        $client = \Modules\NexcoreClientManager\Models\NexcoreClient::findOrFail($client);
        return view('cims_accounts::accounts.client-docs', compact('client'));
    })->name('returns.client-docs');

    // Financials
    Route::get('/financials', [FinancialController::class, 'index'])->name('financials');
    Route::get('/financials/create', [FinancialController::class, 'create'])->name('financials.create');
    Route::post('/financials', [FinancialController::class, 'store'])->name('financials.store');
    Route::get('/financials/{financial}/edit', [FinancialController::class, 'edit'])->name('financials.edit');
    Route::put('/financials/{financial}', [FinancialController::class, 'update'])->name('financials.update');
    Route::delete('/financials/{financial}', [FinancialController::class, 'destroy'])->name('financials.destroy');

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // ═══════════════════════════════════════════════════════════
    // TASK MANAGEMENT SYSTEM (Full Feature Set)
    // ═══════════════════════════════════════════════════════════

    // Task Views
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}/jobcard', [TaskController::class, 'jobcard'])->name('tasks.jobcard');

    // Kanban AJAX
    Route::patch('/tasks/{task}/kanban-update', [TaskController::class, 'kanbanUpdate'])->name('tasks.kanban-update');

    // Task Progress AJAX
    Route::patch('/tasks/{task}/progress', [TaskController::class, 'updateProgress'])->name('tasks.progress');

    // Task Steps (Checklist) AJAX
    Route::post('/tasks/{task}/steps', [TaskController::class, 'storeStep'])->name('tasks.steps.store');
    Route::patch('/tasks/{task}/steps/{step}/toggle', [TaskController::class, 'toggleStep'])->name('tasks.steps.toggle');
    Route::delete('/tasks/{task}/steps/{step}', [TaskController::class, 'destroyStep'])->name('tasks.steps.destroy');
    Route::post('/tasks/{task}/steps/reorder', [TaskController::class, 'reorderSteps'])->name('tasks.steps.reorder');

    // Task Comments AJAX
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskController::class, 'destroyComment'])->name('tasks.comments.destroy');

    // Task Attachments AJAX
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');

    // Task Notes AJAX
    Route::post('/tasks/{task}/notes', [TaskController::class, 'storeNote'])->name('tasks.notes.store');
    Route::put('/tasks/{task}/notes/{note}', [TaskController::class, 'updateNote'])->name('tasks.notes.update');
    Route::delete('/tasks/{task}/notes/{note}', [TaskController::class, 'destroyNote'])->name('tasks.notes.destroy');
    Route::patch('/tasks/{task}/notes/{note}/pin', [TaskController::class, 'toggleNotePin'])->name('tasks.notes.pin');

    // Task Reminders AJAX
    Route::post('/tasks/{task}/reminders', [TaskController::class, 'storeReminder'])->name('tasks.reminders.store');
    Route::delete('/tasks/{task}/reminders/{reminder}', [TaskController::class, 'destroyReminder'])->name('tasks.reminders.destroy');

    // Meetings
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings');
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');
    Route::get('/alerts/create', [AlertController::class, 'create'])->name('alerts.create');
    Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
    Route::get('/alerts/{alert}/edit', [AlertController::class, 'edit'])->name('alerts.edit');
    Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');
    Route::post('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
    Route::post('/alerts/{alert}/toggle-read', [AlertController::class, 'toggleRead'])->name('alerts.toggle-read');

    // Audit Trail
    Route::get('/audit', [AuditController::class, 'index'])->name('audit');

    // Payroll - Employees
    Route::get('/payroll/employees', [EmployeeController::class, 'index'])->name('payroll.employees');
    Route::get('/payroll/employees/create', [EmployeeController::class, 'create'])->name('payroll.employees.create');
    Route::post('/payroll/employees', [EmployeeController::class, 'store'])->name('payroll.employees.store');
    Route::get('/payroll/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('payroll.employees.edit');
    Route::put('/payroll/employees/{employee}', [EmployeeController::class, 'update'])->name('payroll.employees.update');
    Route::delete('/payroll/employees/{employee}', [EmployeeController::class, 'destroy'])->name('payroll.employees.destroy');

    // Payroll - Pay Periods
    Route::get('/payroll/periods', [PayPeriodController::class, 'index'])->name('payroll.periods');
    Route::get('/payroll/periods/create', [PayPeriodController::class, 'create'])->name('payroll.periods.create');
    Route::post('/payroll/periods', [PayPeriodController::class, 'store'])->name('payroll.periods.store');
    Route::get('/payroll/periods/{period}/edit', [PayPeriodController::class, 'edit'])->name('payroll.periods.edit');
    Route::put('/payroll/periods/{period}', [PayPeriodController::class, 'update'])->name('payroll.periods.update');
    Route::delete('/payroll/periods/{period}', [PayPeriodController::class, 'destroy'])->name('payroll.periods.destroy');

    // Payroll - Payslips
    Route::get('/payroll/payslips', [PayslipController::class, 'index'])->name('payroll.payslips');
    Route::get('/payroll/payslips/create', [PayslipController::class, 'create'])->name('payroll.payslips.create');
    Route::post('/payroll/payslips', [PayslipController::class, 'store'])->name('payroll.payslips.store');
    Route::get('/payroll/payslips/{payslip}/edit', [PayslipController::class, 'edit'])->name('payroll.payslips.edit');
    Route::put('/payroll/payslips/{payslip}', [PayslipController::class, 'update'])->name('payroll.payslips.update');
    Route::delete('/payroll/payslips/{payslip}', [PayslipController::class, 'destroy'])->name('payroll.payslips.destroy');

    // Payroll - MIBCO Contributions
    Route::get('/payroll/mibco', [MibcoController::class, 'index'])->name('payroll.mibco');
    Route::get('/payroll/mibco/create', [MibcoController::class, 'create'])->name('payroll.mibco.create');
    Route::post('/payroll/mibco', [MibcoController::class, 'store'])->name('payroll.mibco.store');
    Route::get('/payroll/mibco/{contribution}/edit', [MibcoController::class, 'edit'])->name('payroll.mibco.edit');
    Route::put('/payroll/mibco/{contribution}', [MibcoController::class, 'update'])->name('payroll.mibco.update');
    Route::delete('/payroll/mibco/{contribution}', [MibcoController::class, 'destroy'])->name('payroll.mibco.destroy');

    // Payroll - Reports
    Route::get('/payroll/reports', [PayrollReportController::class, 'index'])->name('payroll.reports');

    // Accounting - Dashboard
    Route::get('/accounting', [AccountingDashboardController::class, 'index'])->name('accounting.dashboard');

    // Accounting - Chart of Accounts
    Route::get('/accounting/accounts', [AccountController::class, 'index'])->name('accounting.accounts');
    Route::get('/accounting/accounts/create', [AccountController::class, 'create'])->name('accounting.accounts.create');
    Route::post('/accounting/accounts', [AccountController::class, 'store'])->name('accounting.accounts.store');
    Route::get('/accounting/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounting.accounts.edit');
    Route::put('/accounting/accounts/{account}', [AccountController::class, 'update'])->name('accounting.accounts.update');
    Route::delete('/accounting/accounts/{account}', [AccountController::class, 'destroy'])->name('accounting.accounts.destroy');

    // Accounting - Setup COA from Template
    Route::get('/accounting/setup-coa', [AccountController::class, 'seedForm'])->name('accounting.setup-coa');
    Route::post('/accounting/setup-coa', [AccountController::class, 'seed'])->name('accounting.setup-coa.seed');

    // Accounting - Journals
    Route::get('/accounting/journals', [JournalController::class, 'index'])->name('accounting.journals');
    Route::get('/accounting/journals/create', [JournalController::class, 'create'])->name('accounting.journals.create');
    Route::post('/accounting/journals', [JournalController::class, 'store'])->name('accounting.journals.store');
    Route::get('/accounting/journals/{journal}/edit', [JournalController::class, 'edit'])->name('accounting.journals.edit');
    Route::put('/accounting/journals/{journal}', [JournalController::class, 'update'])->name('accounting.journals.update');
    Route::delete('/accounting/journals/{journal}', [JournalController::class, 'destroy'])->name('accounting.journals.destroy');

    // Accounting - Bank
    Route::get('/accounting/bank/accounts', [NexcoreBankAccountController::class, 'index'])->name('accounting.bank.accounts');
    Route::get('/accounting/bank/accounts/create', [NexcoreBankAccountController::class, 'create'])->name('accounting.bank.accounts.create');
    Route::post('/accounting/bank/accounts', [NexcoreBankAccountController::class, 'store'])->name('accounting.bank.accounts.store');
    Route::get('/accounting/bank/accounts/{bank}/edit', [NexcoreBankAccountController::class, 'edit'])->name('accounting.bank.accounts.edit');
    Route::put('/accounting/bank/accounts/{bank}', [NexcoreBankAccountController::class, 'update'])->name('accounting.bank.accounts.update');
    Route::post('/accounting/bank/accounts/{bank}/toggle', [NexcoreBankAccountController::class, 'toggle'])->name('accounting.bank.accounts.toggle');
    Route::delete('/accounting/bank/accounts/{bank}', [NexcoreBankAccountController::class, 'destroy'])->name('accounting.bank.accounts.destroy');
    Route::get('/accounting/bank/{bank}/import', [BankImportController::class, 'import'])->name('accounting.bank.import');
    Route::post('/accounting/bank/{bank}/parse-pdf', [BankImportController::class, 'parsePdf'])->name('accounting.bank.parse-pdf');
    Route::post('/accounting/bank/{bank}/parse-pdf-fixer', [BankImportController::class, 'parsePdfFixer'])->name('accounting.bank.parse-pdf-fixer');
    Route::post('/accounting/bank/{bank}/import-save', [BankImportController::class, 'importSave'])->name('accounting.bank.import-save');
    Route::get('/accounting/bank/{bank}/statements', [BankImportController::class, 'statements'])->name('accounting.bank.statements');
    Route::get('/accounting/bank/{bank}/statements/{statement}/view', [BankImportController::class, 'statementView'])->name('accounting.bank.statements.view');
    Route::delete('/accounting/bank/{bank}/statements/{statement}', [BankImportController::class, 'destroyStatement'])->name('accounting.bank.statements.destroy');
    Route::get('/accounting/bank/{bank}/allocate', [BankAllocationController::class, 'index'])->name('accounting.bank.allocate');
    Route::post('/accounting/bank/{bank}/allocate/save', [BankAllocationController::class, 'save'])->name('accounting.bank.allocate.save');
    Route::post('/accounting/bank/{bank}/allocate/auto', [BankAllocationController::class, 'autoAllocate'])->name('accounting.bank.allocate.auto');
    Route::post('/accounting/bank/{bank}/allocate/exclude', [BankAllocationController::class, 'exclude'])->name('accounting.bank.allocate.exclude');
    Route::post('/accounting/bank/{bank}/allocate/unexclude', [BankAllocationController::class, 'unexclude'])->name('accounting.bank.allocate.unexclude');
    Route::post('/accounting/bank/{bank}/allocate/post', [BankAllocationController::class, 'post'])->name('accounting.bank.allocate.post');
    Route::post('/accounting/bank/chart-quick-add', [BankAllocationController::class, 'chartQuickAdd'])->name('accounting.bank.chart-quick-add');
    Route::post('/accounting/bank/rules', [BankAllocationController::class, 'saveRule'])->name('accounting.bank.rules.save');
    Route::delete('/accounting/bank/rules/{rule}', [BankAllocationController::class, 'deleteRule'])->name('accounting.bank.rules.delete');

    // Accounting - Reports (placeholder routes for sidebar links)
    Route::get('/accounting/ledger', [GeneralLedgerController::class, 'index'])->name('accounting.ledger');
    Route::get('/accounting/trial-balance', [TrialBalanceController::class, 'index'])->name('accounting.trial-balance');
    Route::get('/accounting/income-statement', [IncomeStatementController::class, 'index'])->name('accounting.income-statement');
    Route::get('/accounting/balance-sheet', [BalanceSheetController::class, 'index'])->name('accounting.balance-sheet');
    Route::get('/accounting/cash-flow', [AccountingDashboardController::class, 'index'])->name('accounting.cash-flow');
    Route::get('/accounting/budget', [AccountingDashboardController::class, 'index'])->name('accounting.budget');
    Route::get('/accounting/management-pack', [ManagementPackController::class, 'index'])->name('accounting.management-pack');

    // Dashboard Panels
    Route::get('/dashboard/{panel}', [DashboardPanelController::class, 'show'])->name('dashboard.panel');

    // ═══════════════════════════════════════════════════════════
    // SLA / ENGAGEMENT LETTERS (ATP Services)
    // ═══════════════════════════════════════════════════════════
    Route::get('/sla', [SlaController::class, 'index'])->name('sla');
    Route::get('/sla/create', [SlaController::class, 'create'])->name('sla.create');
    Route::get('/sla/client-data', [SlaController::class, 'clientData'])->name('sla.client-data');
    Route::post('/sla', [SlaController::class, 'store'])->name('sla.store');
    Route::get('/sla/{sla}', [SlaController::class, 'show'])->name('sla.show');
    Route::get('/sla/{sla}/edit', [SlaController::class, 'edit'])->name('sla.edit');
    Route::put('/sla/{sla}', [SlaController::class, 'update'])->name('sla.update');
    Route::delete('/sla/{sla}', [SlaController::class, 'destroy'])->name('sla.destroy');
    Route::patch('/sla/{sla}/status', [SlaController::class, 'updateStatus'])->name('sla.status');
    Route::get('/sla/{sla}/pdf', [SlaController::class, 'generatePdf'])->name('sla.pdf');

});
