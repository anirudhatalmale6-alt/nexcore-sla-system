<?php

use Illuminate\Support\Facades\Route;

Route::prefix('cims/accounts')->middleware(['web', 'auth'])->name('cimsaccounts.')->group(function () {

    Route::get('/', 'AccountsController@dashboard')->name('dashboard');

    // Companies
    Route::get('/companies', 'AccountsController@companies')->name('companies');
    Route::get('/companies/create', 'AccountsController@companyCreate')->name('company.create');
    Route::post('/companies', 'AccountsController@companyStore')->name('company.store');
    Route::get('/companies/{id}/edit', 'AccountsController@companyEdit')->name('company.edit');
    Route::put('/companies/{id}', 'AccountsController@companyUpdate')->name('company.update');

    // Chart of Accounts
    Route::get('/chart/{companyId}', 'AccountsController@chartIndex')->name('chart.index');
    Route::get('/chart/{companyId}/create', 'AccountsController@chartCreate')->name('chart.create');
    Route::post('/chart/{companyId}', 'AccountsController@chartStore')->name('chart.store');
    Route::get('/chart/{companyId}/edit/{accountId}', 'AccountsController@chartEdit')->name('chart.edit');
    Route::put('/chart/{companyId}/{accountId}', 'AccountsController@chartUpdate')->name('chart.update');
    Route::delete('/chart/{companyId}/{accountId}', 'AccountsController@chartDelete')->name('chart.delete');
    Route::post('/chart/{companyId}/seed', 'AccountsController@chartSeed')->name('chart.seed');

    // Bank Accounts
    Route::get('/banks/{companyId}', 'AccountsController@bankAccounts')->name('banks.index');
    Route::get('/banks/{companyId}/create', 'AccountsController@bankAccountCreate')->name('banks.create');
    Route::post('/banks/{companyId}', 'AccountsController@bankAccountStore')->name('banks.store');

    // Bank Import
    Route::get('/banks/{companyId}/{bankId}/import', 'AccountsController@bankImport')->name('banks.import');
    Route::post('/banks/{companyId}/{bankId}/import', 'AccountsController@bankImportProcess')->name('banks.import.process');

    // Bank Allocation
    Route::get('/allocate/{companyId}', 'AccountsController@bankAllocate')->name('allocate');
    Route::post('/allocate/{companyId}/save', 'AccountsController@bankAllocateSave')->name('allocate.save');
    Route::post('/allocate/{companyId}/post', 'AccountsController@bankAllocatePost')->name('allocate.post');
    Route::post('/allocate/{companyId}/exclude/{txnId}', 'AccountsController@bankExclude')->name('allocate.exclude');

    // Journals
    Route::get('/journals/{companyId}', 'AccountsController@journalIndex')->name('journals.index');
    Route::get('/journals/{companyId}/create', 'AccountsController@journalCreate')->name('journals.create');
    Route::post('/journals/{companyId}', 'AccountsController@journalStore')->name('journals.store');
    Route::get('/journals/{companyId}/{journalId}', 'AccountsController@journalShow')->name('journals.show');
    Route::post('/journals/{companyId}/{journalId}/post', 'AccountsController@journalPost')->name('journals.post');
    Route::post('/journals/{companyId}/{journalId}/reverse', 'AccountsController@journalReverse')->name('journals.reverse');

    // Allocation Rules
    Route::get('/rules/{companyId}', 'AccountsController@allocationRules')->name('rules.index');
    Route::post('/rules/{companyId}', 'AccountsController@allocationRuleStore')->name('rules.store');
    Route::delete('/rules/{companyId}/{ruleId}', 'AccountsController@allocationRuleDelete')->name('rules.delete');

    // PDF Import
    Route::post('/api/parse-pdf/{companyId}/{bankId}', 'AccountsController@apiParsePdf')->name('api.parse.pdf');
    Route::post('/banks/{companyId}/{bankId}/import-pdf', 'AccountsController@bankImportPdfSave')->name('banks.import.pdf.save');

    // Reports
    Route::get('/reports/pnl/{companyId}', 'AccountsController@profitAndLoss')->name('reports.pnl');
    Route::get('/reports/tb/{companyId}', 'AccountsController@trialBalance')->name('reports.tb');
    Route::get('/reports/bs/{companyId}', 'AccountsController@balanceSheet')->name('reports.bs');
    Route::get('/reports/gl/{companyId}', 'AccountsController@generalLedger')->name('reports.gl');
    Route::get('/reports/vat/{companyId}', 'AccountsController@vatReport')->name('reports.vat');
    Route::get('/reports/bankrecon/{companyId}', 'AccountsController@bankReconciliation')->name('reports.bankrecon');
    Route::get('/bankrecon-dashboard', 'AccountsController@bankReconDashboardView')->name('bankrecon.dashboard');
    Route::get('/sales-dashboard', 'AccountsController@salesDashboardView')->name('sales.dashboard');
    Route::get('/financial-dashboard/{companyId?}', 'AccountsController@financialDashboardView')->name('financial.dashboard');
    Route::get('/income-statement/{companyId}', 'AccountsController@incomeStatementView')->name('income.statement');
    Route::get('/balance-sheet-dash/{companyId}', 'AccountsController@balanceSheetDashView')->name('balance.sheet');
    Route::get('/cashflow-statement/{companyId}', 'AccountsController@cashflowStatementView')->name('cashflow.statement');
    Route::get('/expenses-dash/{companyId}', 'AccountsController@expensesDashView')->name('expenses.dash');
    Route::get('/expenses-category/{companyId}', 'AccountsController@expensesCategoryDashView')->name('expenses.category');
    Route::get('/compliance-dashboard', 'AccountsController@complianceDashboardView')->name('compliance.dashboard');
    Route::get('/cipc-dashboard', 'AccountsController@cipcDashboardView')->name('cipc.dashboard');
    Route::get('/sars-dashboard', 'AccountsController@sarsDashboardView')->name('sars.dashboard');
    Route::get('/payroll-dashboard', 'AccountsController@payrollDashboardView')->name('payroll.dashboard');
    Route::get('/executive-dashboard', 'AccountsController@executiveDashboardView')->name('executive.dashboard');
    Route::get('/operations-dashboard', 'AccountsController@operationsDashboardView')->name('operations.dashboard');
    Route::get('/executive-overview', 'AccountsController@executiveOverviewDashboardView')->name('executive.overview');
    Route::get('/inventory-dashboard', 'AccountsController@inventoryDashboardView')->name('inventory.dashboard');
    Route::get('/sales-engine', 'AccountsController@salesEngineDashboardView')->name('salesengine.dashboard');
    Route::get('/cashflow-dashboard', 'AccountsController@cashflowDashboardView')->name('cashflow.dashboard');
    Route::get('/tyre-sales-dashboard', 'AccountsController@tyreSalesDashboardView')->name('tyresales.dashboard');
    Route::get('/bankrecon/{companyId}/{reconId}', 'AccountsController@bankReconReconcile')->name('bankrecon.reconcile');

    // AJAX endpoints
    Route::get('/api/account-transactions/{companyId}/{accountId}', 'AccountsController@apiAccountTransactions')->name('api.account.transactions');
    Route::post('/api/reallocate/{companyId}', 'AccountsController@apiReallocateJournal')->name('api.reallocate');
    Route::post('/api/chart-quick-add/{companyId}', 'AccountsController@chartQuickAdd')->name('api.chart.quickadd');
    Route::get('/api/chart-tree/{companyId}', 'AccountsController@apiChartTree')->name('api.chart.tree');
    Route::get('/api/chart-balances/{companyId}', 'AccountsController@apiChartBalances')->name('api.chart.balances');
    Route::get('/api/suggest-account/{companyId}', 'AccountsController@apiSuggestAccount')->name('api.suggest');

    // Management Accounts
    Route::get('/management-accounts/{companyId}', 'AccountsController@managementAccounts')->name('management.accounts');
    Route::post('/api/ma-realloc/{companyId}', 'AccountsController@maSimpleRealloc')->name('api.ma.realloc');
    Route::post('/api/ma-hide/{companyId}', 'AccountsController@maToggleHide')->name('api.ma.hide');

    // New Bank Reconciliation
    Route::get('/new-bank-recon/{companyId}', 'AccountsController@newBankRecon')->name('newbankrecon');
    Route::post('/api/new-bank-recon/{companyId}/load', 'AccountsController@newBankReconLoad')->name('api.newbankrecon.load');
    Route::post('/api/new-bank-recon/{companyId}/toggle', 'AccountsController@newBankReconToggle')->name('api.newbankrecon.toggle');
    Route::post('/api/new-bank-recon/{companyId}/bulk', 'AccountsController@newBankReconBulkToggle')->name('api.newbankrecon.bulk');
    Route::post('/api/new-bank-recon/{companyId}/save', 'AccountsController@newBankReconSave')->name('api.newbankrecon.save');
    Route::post('/api/new-bank-recon/{companyId}/reset', 'AccountsController@newBankReconReset')->name('api.newbankrecon.reset');
    Route::get('/api/new-bank-recon/{companyId}/list', 'AccountsController@newBankReconList')->name('api.newbankrecon.list');

    // Bank Reconciliation API
    Route::post('/api/bankrecon/{companyId}/{reconId}/toggle', 'AccountsController@bankReconToggleLine')->name('api.bankrecon.toggle');
    Route::post('/api/bankrecon/{companyId}/{reconId}/bulk', 'AccountsController@bankReconBulkToggle')->name('api.bankrecon.bulk');
    Route::post('/api/bankrecon/{companyId}/{reconId}/statement', 'AccountsController@bankReconUpdateStatement')->name('api.bankrecon.statement');
    Route::post('/api/bankrecon/{companyId}/{reconId}/complete', 'AccountsController@bankReconComplete')->name('api.bankrecon.complete');
    Route::post('/api/bankrecon/{companyId}/{reconId}/received', 'AccountsController@bankReconMarkReceived')->name('api.bankrecon.received');

    // Bank Statement Register
    Route::get('/statements/{companyId}/{bankId}', 'AccountsController@statementRegister')->name('statements.index');
    Route::get('/statements/{companyId}/{bankId}/{statementId}', 'AccountsController@statementView')->name('statements.view');
    Route::put('/statements/{companyId}/{bankId}/{statementId}/status', 'AccountsController@statementUpdateStatus')->name('statements.status');
    Route::delete('/statements/{companyId}/{bankId}/{statementId}', 'AccountsController@statementDelete')->name('statements.delete');
});