# NexCore Client Manager - Accounting Module Audit

**Date:** 27 May 2026
**Server:** smartdash.co.za (Hostinger)
**Base Path:** `/home/u911260017/domains/smartdash.co.za/public_html/application`
**Module Path:** `Modules/NexcoreClientManager/`
**Database:** `u911260017_NexCore`

---

## 1. DATABASE TABLES

The accounting module uses **two parallel table schemas**:
- **`cims_gl_*`** tables: The active GL (General Ledger) system used by all controllers for chart of accounts, journals, bank imports, allocations, and reconciliations.
- **`nexcore_client_*`** tables: A legacy/secondary set (with SoftDeletes) for client-level accounts, journals, banks, budgets, and financials. These models exist but are NOT actively used by the main accounting controllers.

### 1.1 Core GL Tables (Active)

#### `cims_gl_chart_of_accounts_master`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | auto_increment |
| company_id | bigint unsigned | FK to client |
| account_code | varchar(20) | e.g. "1/10/1010" |
| segment1 | varchar(4) | Level 1 code segment |
| segment2 | varchar(4) | Level 2 code segment |
| segment3 | varchar(4) | Level 3 code segment |
| account_level | tinyint unsigned | 1=Main, 2=Sub, 3=Detail |
| account_name | varchar(255) | |
| account_type | enum | asset, liability, equity, revenue, cost_of_sales, expense |
| normal_balance | enum | debit, credit |
| vat_type | enum | standard, zero_rated, exempt, none |
| is_active | tinyint(1) | |
| is_system | tinyint(1) | System accounts cannot be deleted |
| is_header | tinyint(1) | Header accounts group children |
| description | varchar(500) | |
| parent_id | bigint unsigned | Self-referential FK |
| created_at, updated_at | timestamp | |

#### `cims_gl_journal_master_header`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| company_id | bigint unsigned | |
| journal_number | varchar(20) | e.g. "JNL-00001" or "JNL000001" |
| journal_date | date | |
| period_id | int unsigned | nullable |
| reference | varchar(100) | |
| description | varchar(500) | |
| source | enum | manual, bank_import, system, opening |
| status | enum | draft, posted, reversed |
| total_debit | decimal(15,2) | |
| total_credit | decimal(15,2) | |
| reversal_of | bigint unsigned | FK to reversed journal |
| notes | text | |
| attachment_path | varchar(500) | |
| attachment_name | varchar(255) | |
| created_by | bigint unsigned | user FK |
| posted_by | bigint unsigned | user FK |
| posted_at | timestamp | |
| created_at, updated_at | timestamp | |

#### `cims_gl_journal_header_linked_entries`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| journal_id | bigint unsigned | FK to journal header |
| account_id | bigint unsigned | FK to chart of accounts |
| description | varchar(500) | |
| debit_amount | decimal(15,2) | |
| credit_amount | decimal(15,2) | |
| vat_amount | decimal(15,2) | |
| vat_type | enum | standard, zero_rated, exempt, none |
| ma_hidden | tinyint(1) | Management accounts hidden flag |
| note | text | |
| line_order | smallint unsigned | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_accounts_linked_to_coa`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| company_id | bigint unsigned | |
| bank_id | bigint unsigned | FK to nexcore_system_banks |
| account_id | bigint unsigned | FK to chart of accounts (GL link) |
| bank_name | varchar(100) | Denormalized bank name |
| account_number | varchar(50) | |
| branch_code | varchar(20) | |
| account_type | varchar(50) | default 'cheque' |
| is_active | tinyint(1) | |
| opening_balance_date | date | |
| opening_balance_amount | decimal(15,2) | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_statement_upload_register`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| company_id | bigint unsigned | |
| bank_account_id | bigint unsigned | FK to bank accounts |
| statement_name | varchar(255) | Account holder or label |
| statement_number | varchar(100) | |
| statement_ref | varchar(50) | e.g. "BS1010/123/MAY2026" |
| period_from | date | |
| period_to | date | |
| upload_date | date | |
| original_filename | varchar(500) | |
| file_path | varchar(500) | |
| transaction_count | int | |
| opening_balance | decimal(15,2) | |
| closing_balance | decimal(15,2) | |
| total_credits | decimal(15,2) | |
| total_debits | decimal(15,2) | |
| credit_count | int | |
| debit_count | int | |
| batch_ref | varchar(50) | Unique batch identifier |
| status | varchar(30) | e.g. 'imported' |
| notes | text | |
| uploaded_by | bigint unsigned | |
| imported_by | bigint unsigned | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_statement_upload_transactions`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| company_id | bigint unsigned | |
| bank_account_id | bigint unsigned | |
| transaction_date | date | |
| description | varchar(500) | |
| reference | varchar(100) | |
| amount | decimal(15,2) | Absolute amount |
| direction | enum | debit, credit |
| balance | decimal(15,2) | Running balance |
| allocated_account_id | bigint unsigned | FK to chart of accounts |
| vat_type | enum | standard, zero_rated, exempt, none |
| vat_amount | decimal(15,2) | |
| net_amount | decimal(15,2) | |
| journal_id | bigint unsigned | FK to journal (after posting) |
| status | enum | unallocated, allocated, posted, excluded |
| batch_ref | varchar(50) | Links to statement |
| imported_at | timestamp | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_allocation_rules_master`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| company_id | bigint unsigned | |
| keyword | varchar(255) | Text match pattern |
| account_id | bigint unsigned | FK to chart of accounts |
| vat_type | enum | standard, zero_rated, exempt, none |
| priority | int | Higher = matched first |
| match_count | int unsigned | Auto-incremented on match |
| is_active | tinyint(1) | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_recon_master_header`
| Column | Type | Notes |
|--------|------|-------|
| id | int unsigned PK | |
| company_id | int unsigned | |
| bank_account_id | int unsigned | |
| statement_date | date | |
| statement_balance | decimal(15,2) | |
| reference | varchar(255) | |
| gl_balance | decimal(15,2) | |
| reconciled_balance | decimal(15,2) | |
| outstanding_balance | decimal(15,2) | |
| difference | decimal(15,2) | |
| status | varchar(20) | default 'draft' |
| reconciled_by | int unsigned | |
| reconciled_at | timestamp | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_recon_header_linked_lines`
| Column | Type | Notes |
|--------|------|-------|
| id | int unsigned PK | |
| recon_id | int unsigned | FK to recon header |
| journal_id | int unsigned | |
| journal_line_id | int unsigned | |
| amount | decimal(15,2) | |
| is_reconciled | tinyint(1) | |
| reconciled_at | timestamp | |
| created_at, updated_at | timestamp | |

### 1.2 Template Tables

#### `nexcore_account_templates`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| template_name | varchar(255) | |
| industry_type | varchar(100) | |
| description | text | |
| is_default | tinyint(1) | |
| is_active | tinyint(1) | |
| created_at, updated_at | timestamp | |

#### `nexcore_account_template_items`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| template_id | bigint unsigned | FK to template |
| account_code | varchar(20) | |
| segment1-3 | varchar(4) | |
| account_level | tinyint | |
| account_name | varchar(255) | |
| account_type | varchar(20) | |
| normal_balance | varchar(10) | |
| vat_type | varchar(20) | |
| is_system, is_header | tinyint(1) | |
| description | text | |
| created_at, updated_at | timestamp | |

#### `cims_gl_chart_of_accounts_template_header`
Duplicate/legacy template table (same schema as nexcore_account_templates but with enum types matching the GL master).

#### `cims_gl_chart_of_accounts_template_items`
Duplicate/legacy template items table (same schema as nexcore_account_template_items but with proper enums).

### 1.3 Legacy/Secondary GL Tables (in DB, NOT actively used by main accounting module)

#### `cims_gl_company_master`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| client_id | bigint unsigned | FK to client |
| client_code, company_name, trading_name | varchar | |
| vat_number | varchar(50) | |
| vat_rate | int | default 15 |
| registration_number | varchar(50) | |
| financial_year_start | tinyint unsigned | default 3 (March) |
| is_vat_registered | tinyint(1) | |
| is_active | tinyint(1) | |
| created_by | bigint unsigned | |
| created_at, updated_at | timestamp | |

#### `cims_gl_bank_conversions_master`
Stores PDF-to-CSV bank statement conversion history with bank_type, account_number, balances, transaction counts.

#### `cims_gl_bank_names_master`
Legacy bank name directory (bank_name, branch_name, branch_code, swift_code, bank_logo, show_in_conversion).

#### `cims_gl_bank_account_types`
Bank-specific account type lookup (bank_account_type, bank_link_id, bank_name).

#### `cims_gl_bank_account_status`
Bank-specific account status lookup (bank_account_status, bank_link_id, bank_name).

#### `cims_gl_bank_statement_frequency_master`
Bank-specific statement frequency lookup.

### 1.4 Client-Level Tables (SoftDeletes, secondary schema)

#### `nexcore_client_accounts`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| client_id | bigint unsigned | |
| account_code | varchar(50) | |
| account_name | varchar(255) | |
| account_type | varchar(50) | default 'asset' |
| sub_type | varchar(100) | |
| parent_id | bigint unsigned | Self-referential |
| description | text | |
| opening_balance | decimal(15,2) | |
| is_active | tinyint(1) | |
| created_by, updated_by | bigint unsigned | |
| created_at, updated_at, deleted_at | timestamp | SoftDeletes |

#### `nexcore_client_budgets`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| client_id | bigint unsigned | |
| account_id | bigint unsigned | FK to nexcore_client_accounts |
| period_year | int | |
| period_month | int | |
| budget_amount | decimal(15,2) | |
| notes | text | |
| created_by, updated_by | bigint unsigned | |
| timestamps + deleted_at | | SoftDeletes |

#### `nexcore_client_banks`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| client_id | bigint unsigned | |
| bank_id | bigint unsigned | FK to nexcore_system_banks |
| account_type_id | bigint unsigned | FK to nexcore_system_bank_account_types |
| gl_account_id | bigint unsigned | FK to cims_gl_chart_of_accounts_master |
| account_name | varchar(255) | |
| account_number | varchar(50) | |
| branch_code | varchar(20) | |
| swift_code | varchar(20) | |
| account_label | varchar(100) | |
| is_primary | tinyint(1) | |
| is_active | tinyint(1) | |
| notes | text | |
| created_by, updated_by | bigint unsigned | |
| timestamps + deleted_at | | SoftDeletes |

#### `nexcore_client_financials`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned PK | |
| client_id | bigint unsigned | |
| financial_type_id | bigint unsigned | FK to nexcore_system_financial_types |
| status_id | bigint unsigned | FK to nexcore_system_return_statuses |
| financial_year | varchar(10) | |
| period_start, period_end | date | |
| prepared_by, reviewed_by | varchar(255) | |
| approved_date | date | |
| document_path | varchar(255) | |
| notes | text | |
| is_active | tinyint(1) | |
| created_by, updated_by | bigint unsigned | |
| timestamps + deleted_at | | SoftDeletes |

### 1.5 System Lookup Tables

#### `nexcore_system_account_types`
Columns: id, name, description, is_active, created_by, updated_by, timestamps

#### `nexcore_system_financial_types`
Columns: id, name, code, description, is_active, created_by, updated_by, timestamps

#### `nexcore_system_banks`
Columns: id, name, code, bank_code, branch_code, swift_code, bank_logo, description, is_active, is_deleted, timestamps

#### `nexcore_system_bank_account_types`
Columns: id, name, code, description, is_active, is_deleted, timestamps

---

## 2. MODELS

All models in `Modules/NexcoreClientManager/Models/`. Only accounting-related models listed.

### 2.1 GL Models (Primary - used by all accounting controllers)

#### `NexcoreGlChartOfAccount`
- **Table:** `cims_gl_chart_of_accounts_master`
- **Fillable:** company_id, account_code, segment1-3, account_level, account_name, account_type, normal_balance, vat_type, is_active, is_system, is_header, description, parent_id
- **Casts:** is_active (bool), is_system (bool), is_header (bool), account_level (int)
- **Relationships:**
  - `parent()` -> belongsTo(self, 'parent_id')
  - `children()` -> hasMany(self, 'parent_id')
- **Scopes:** `scopeOfType($type)`, `scopeMainAccounts` (level 1), `scopeSubAccounts` (level 2), `scopeDetailAccounts` (level 3)

#### `NexcoreGlJournal`
- **Table:** `cims_gl_journal_master_header`
- **Fillable:** company_id, journal_number, journal_date, period_id, reference, description, source, status, total_debit, total_credit, reversal_of, notes, attachment_path, attachment_name, created_by, posted_by, posted_at
- **Casts:** total_debit/total_credit (decimal:2), journal_date (date), posted_at (datetime)
- **Relationships:**
  - `client()` -> belongsTo(NexcoreClient, 'company_id')
  - `lines()` -> hasMany(NexcoreGlJournalLine, 'journal_id')

#### `NexcoreGlJournalLine`
- **Table:** `cims_gl_journal_header_linked_entries`
- **Fillable:** journal_id, account_id, description, debit_amount, credit_amount, vat_amount, vat_type, ma_hidden, note, line_order
- **Casts:** debit_amount/credit_amount/vat_amount (decimal:2), ma_hidden (bool)
- **Relationships:**
  - `journal()` -> belongsTo(NexcoreGlJournal, 'journal_id')
  - `account()` -> belongsTo(NexcoreGlChartOfAccount, 'account_id')

#### `NexcoreBankAccount`
- **Table:** `cims_gl_bank_accounts_linked_to_coa`
- **Fillable:** company_id, account_id, bank_id, bank_name, account_number, branch_code, account_type, is_active, opening_balance_date, opening_balance_amount
- **Casts:** is_active (bool), opening_balance_date (date), opening_balance_amount (decimal:2)
- **Relationships:**
  - `client()` -> belongsTo(NexcoreClient, 'company_id')
  - `glAccount()` -> belongsTo(NexcoreGlChartOfAccount, 'account_id')
  - `systemBank()` -> belongsTo(NexcorSystemBank from CIMS_PMPRO module, 'bank_id')
  - `transactions()` -> hasMany(NexcoreBankTransaction, 'bank_account_id')
  - `statements()` -> hasMany(NexcoreBankStatement, 'bank_account_id')

#### `NexcoreBankStatement`
- **Table:** `cims_gl_bank_statement_upload_register`
- **Fillable:** company_id, bank_account_id, statement_name, statement_number, statement_ref, period_from, period_to, upload_date, original_filename, transaction_count, opening_balance, closing_balance, total_credits, total_debits, credit_count, debit_count, batch_ref, status, uploaded_by, imported_by, notes
- **Casts:** opening_balance/closing_balance/total_credits/total_debits (decimal:2), period_from/period_to (date), upload_date (datetime)
- **Relationships:**
  - `bankAccount()` -> belongsTo(NexcoreBankAccount, 'bank_account_id')

#### `NexcoreBankTransaction`
- **Table:** `cims_gl_bank_statement_upload_transactions`
- **Fillable:** company_id, bank_account_id, transaction_date, description, amount, direction, balance, reference, status, allocated_account_id, vat_type, vat_amount, net_amount, batch_ref, journal_id, imported_at
- **Casts:** amount/balance/vat_amount/net_amount (decimal:2), transaction_date (date), imported_at (datetime)
- **Relationships:**
  - `client()` -> belongsTo(NexcoreClient, 'company_id')
  - `bankAccount()` -> belongsTo(NexcoreBankAccount, 'bank_account_id')
  - `allocatedAccount()` -> belongsTo(NexcoreGlChartOfAccount, 'allocated_account_id')
  - `journal()` -> belongsTo(NexcoreGlJournal, 'journal_id')

#### `NexcoreBankAllocationRule`
- **Table:** `cims_gl_bank_allocation_rules_master`
- **Fillable:** company_id, keyword, account_id, vat_type, priority, match_count, is_active
- **Casts:** is_active (bool)
- **Relationships:**
  - `account()` -> belongsTo(NexcoreGlChartOfAccount, 'account_id')

#### `NexcoreBankReconciliation`
- **Table:** `cims_gl_bank_recon_master_header`
- **Fillable:** company_id, bank_account_id, statement_date, statement_balance, gl_balance, reconciled_balance, status, completed_by, completed_at, notes
- **Casts:** statement_balance/gl_balance/reconciled_balance (decimal:2), statement_date (date), completed_at (datetime)
- **Relationships:**
  - `bankAccount()` -> belongsTo(NexcoreBankAccount, 'bank_account_id')
  - `lines()` -> hasMany(NexcoreBankReconLine, 'reconciliation_id')

#### `NexcoreBankReconLine`
- **Table:** `cims_gl_bank_recon_header_linked_lines`
- **Fillable:** reconciliation_id, transaction_id, journal_line_id, source, transaction_date, description, amount, is_matched
- **Casts:** amount (decimal:2), transaction_date (date), is_matched (bool)
- **Relationships:**
  - `reconciliation()` -> belongsTo(NexcoreBankReconciliation, 'reconciliation_id')
  - `transaction()` -> belongsTo(NexcoreBankTransaction, 'transaction_id')

### 2.2 Template Models

#### `NexcoreAccountTemplate`
- **Table:** `nexcore_account_templates`
- **Fillable:** template_name, industry_type, description, is_default, is_active
- **Relationships:** `items()` -> hasMany(NexcoreAccountTemplateItem, 'template_id')
- **Scopes:** `scopeActive()`

#### `NexcoreAccountTemplateItem`
- **Table:** `nexcore_account_template_items`
- **Fillable:** template_id, account_code, segment1-3, account_level, account_name, account_type, normal_balance, vat_type, is_system, is_header, description
- **Relationships:** `template()` -> belongsTo(NexcoreAccountTemplate, 'template_id')

### 2.3 Client-Level Models (Secondary)

#### `NexcoreClientAccount` (SoftDeletes)
- **Table:** `nexcore_client_accounts`
- **Fillable:** client_id, account_code, account_name, account_type, sub_type, parent_id, description, opening_balance, is_active, created_by, updated_by
- **Relationships:** client(), parent(), children(), journalLines(), budgets()
- **Scopes:** `scopeOfType($type)`

#### `NexcoreClientJournal` (SoftDeletes)
- **Table:** `nexcore_client_journals` (**NOTE: Table does NOT exist in database**)
- **Fillable:** client_id, journal_number, journal_date, reference, narration, period_year, period_month, status, total_amount, is_active, created_by, updated_by
- **Relationships:** client(), lines()

#### `NexcoreClientJournalLine`
- **Table:** `nexcore_client_journal_lines` (**NOTE: Table does NOT exist in database**)
- **Fillable:** journal_id, account_id, description, debit, credit
- **Relationships:** journal(), account()

#### `NexcoreClientBank` (SoftDeletes)
- **Table:** `nexcore_client_banks`
- **Fillable:** client_id, bank_id, account_type_id, gl_account_id, account_name, account_number, branch_code, swift_code, account_label, is_primary, is_active, notes, created_by, updated_by
- **Relationships:** client(), bank() (CIMS_PMPRO), accountType(), glAccount()

#### `NexcoreClientBudget` (SoftDeletes)
- **Table:** `nexcore_client_budgets`
- **Fillable:** client_id, account_id, period_year, period_month, budget_amount, notes, created_by, updated_by
- **Relationships:** client(), account()

#### `NexcoreClientFinancial` (SoftDeletes)
- **Table:** `nexcore_client_financials`
- **Fillable:** client_id, financial_type_id, status_id, financial_year, period_start, period_end, prepared_by, reviewed_by, approved_date, document_path, notes, is_active, created_by, updated_by
- **Relationships:** client(), financialType(), status()

### 2.4 System Lookup Models

#### `NexcoreSystemAccountType`
- **Table:** `nexcore_system_account_types`
- **Fillable:** name, description, is_active, created_by, updated_by

#### `NexcoreSystemFinancialType`
- **Table:** `nexcore_system_financial_types`
- **Fillable:** name, code, description, is_active, created_by, updated_by

---

## 3. CONTROLLERS

All controllers in `Modules/NexcoreClientManager/Http/Controllers/`.

### 3.1 AccountController

**File:** `AccountController.php`

**Properties:**
- `$accountTypes` - Array: asset, liability, equity, revenue, cost_of_sales, expense, other
- `$normalBalances` - Array: debit, credit
- `$vatTypes` - Array: none, standard, exempt

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($clientId)` | Lists all non-level-1 accounts for a client. Renders `accounting.accounts.index` |
| `create($clientId)` | Shows create form with account types, normal balances, VAT types, parent accounts (headers only). Renders `accounting.accounts.form` |
| `store($request, $clientId)` | Validates and creates a new GL account in `cims_gl_chart_of_accounts_master` |
| `edit($clientId, $accountId)` | Shows edit form for existing account. Renders `accounting.accounts.form` |
| `update($request, $clientId, $accountId)` | Validates and updates an existing GL account |
| `destroy($clientId, $accountId)` | Deletes account (blocks if system account or has journal lines) |
| `seedForm($clientId)` | Shows COA setup page with templates and target client selector. Renders `accounting.setup-coa` |
| `seed($request, $clientId)` | Seeds chart of accounts from a template into a target client (only if chart is empty). Builds parent_id mapping across 3 levels |
| `manageCoa()` | Top-level COA management page showing all clients with account/journal/bank/transaction counts. Renders `accounting.manage-coa` |
| `resetClientAccounting($request, $clientId)` | **DESTRUCTIVE**: Deletes ALL accounting data for a client (recon lines, reconciliations, journal lines, journals, bank transactions, statements, allocation rules, bank accounts, chart of accounts). Requires confirmation text "DELETE". Returns JSON |

### 3.2 JournalController

**File:** `JournalController.php`

**Properties:**
- `$statuses` - Array: draft, posted, reversed
- `$sources` - Array: manual, bank_import, system, opening

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($clientId)` | Lists all journals with line count. Renders `accounting.journals.index` |
| `create($clientId)` | Shows journal entry form with active level-3 accounts, auto-generates journal number. Renders `accounting.journals.form` |
| `store($request, $clientId)` | Creates journal with lines. Validates debits = credits. Always posts immediately (status='posted'). Uses DB::transaction |
| `edit($clientId, $journalId)` | Shows edit form for existing journal with its lines. Renders `accounting.journals.form` |
| `update($request, $clientId, $journalId)` | Updates journal and replaces all lines. Validates balance. Uses DB::transaction |
| `destroy($clientId, $journalId)` | Deletes journal and all its lines. Uses DB::transaction |

### 3.3 TrialBalanceController

**File:** `TrialBalanceController.php`

**Properties:** `$typeLabels`, `$typeIcons`, `$typeColors` (for UI rendering of account type groups)

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($request, $clientId)` | Generates trial balance report. Auto-detects current SA financial year quarter as default period. Groups accounts by type, calculates closing debit/credit based on normal_balance. Checks balance (debits = credits). Renders `accounting.trial-balance` |

### 3.4 IncomeStatementController

**File:** `IncomeStatementController.php`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($request, $clientId)` | Generates income statement. Builds 3-level hierarchy for revenue, cost_of_sales, expense. Calculates gross profit and net profit. Renders `accounting.income-statement` |
| `buildHierarchy(private)` | Builds hierarchical account groups (main -> sub -> detail) for a given type |
| `countAccounts(private)` | Counts detail accounts across groups |

### 3.5 BalanceSheetController

**File:** `BalanceSheetController.php`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($request, $clientId)` | Generates balance sheet. Groups asset/liability/equity with hierarchy. Computes net profit from income accounts. Checks A = L + E + Net Profit. Renders `accounting.balance-sheet` |
| `buildHierarchy(private)` | Same pattern as IncomeStatementController |
| `countAccounts(private)` | Same pattern |
| `calcTypeTotal(private)` | Calculates total for a given account type |

### 3.6 GeneralLedgerController

**File:** `GeneralLedgerController.php`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($request, $clientId)` | Full general ledger report with account range filter (from_account, to_account). Shows every transaction per account with running balance. Supports date range + preset periods. Renders `accounting.general-ledger` |

### 3.7 AccountingDashboardController

**File:** `AccountingDashboardController.php`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($clientId)` | Accounting dashboard with KPI cards: total accounts (main/sub/detail), journal counts, revenue total, expense total, net profit, total assets, total liabilities, recent 10 journals. Renders `accounting.dashboard` |

### 3.8 ManagementPackController

**File:** `ManagementPackController.php`

**Properties:** `$typeLabels`, `$typeIcons`, `$typeColors`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($request, $clientId)` | Combined management accounts pack: Income Statement + Balance Sheet + Trial Balance all in one view. Renders `accounting.management-pack` |
| `buildISHierarchy(private)` | Income Statement hierarchy builder |
| `buildBSHierarchy(private)` | Balance Sheet hierarchy builder |
| `countHierarchyAccounts(private)` | Account counter |

### 3.9 NexcoreBankAccountController

**File:** `NexcoreBankAccountController.php`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($clientId)` | Lists all bank accounts with GL links, unallocated/total/posted transaction counts. Renders `accounting.bank.accounts` |
| `create($clientId)` | Shows bank account link form. Auto-finds bank-related GL accounts. Loads system banks and account types. Renders `accounting.bank.account-form` |
| `store($request, $clientId)` | Creates bank account linked to GL. Prevents duplicate GL links. Creates opening balance journal entry if amount provided |
| `edit($clientId, $bankId)` | Edit bank account. Renders `accounting.bank.account-form` |
| `update($request, $clientId, $bankId)` | Updates bank account details |
| `toggle($clientId, $bankId)` | Toggles bank account active/inactive |
| `destroy($clientId, $bankId)` | Deletes bank account AND all its transactions and statements |
| `createOpeningBalanceJournal(private)` | Auto-creates equity accounts (Opening Balance Equity) if needed, then posts a balanced journal for the opening balance |

### 3.10 BankImportController

**File:** `BankImportController.php` (~2000 lines)

**Methods:**

| Method | Purpose |
|--------|---------|
| `statements($clientId, $bankId)` | Lists imported statements for a bank account. Renders `accounting.bank.statements` |
| `statementView($clientId, $bankId, $statementId)` | Views a single statement with all its transactions. Renders `accounting.bank.statement-view` |
| `destroyStatement($clientId, $bankId, $statementId)` | Deletes statement, its transactions, AND any linked journals+lines |
| `import($clientId, $bankId)` | Shows the bank statement import page. Auto-detects bank type from name (FNB, Nedbank, ABSA, Capitec, Standard). Renders `accounting.bank.import` |
| `parsePdf($request, $clientId, $bankId)` | **AJAX**: Parses PDF text into structured transactions. Supports 8 bank parsers: FNB, Nedbank, ABSA Transaction History, ABSA Bank Statement, Capitec (Mercantile), Capitec Business, Capitec Personal, Standard Bank |
| `importSave($request, $clientId, $bankId)` | **AJAX**: Saves parsed transactions to DB. Creates batch_ref, generates statement_ref (BS[GL code]/[number]/[MMMYYYY]), creates statement record |
| `parsePdfFixer($request, $clientId, $bankId)` | **AJAX**: Secondary PDF parser/fixer endpoint |
| Various private parsers | `parseFnbText`, `parseFnbHeader`, `parseFnbTransactions`, `parseFnbOcrTransactions`, `parseNedbankText`, `parseAbsaTransactionHistoryText`, `parseAbsaBankStatementText`, `parseCapitecText`, `parseCapitecBusinessText`, `parseCapitecPersonalText`, `parseStandardText`, `buildParseResult` |

**Supported Banks (PDF Parsers):**
1. **FNB** - Full parser with OCR fallback for missing descriptions
2. **Nedbank** - Balance-difference method
3. **ABSA Transaction History** - Direct amount extraction (LOCKED/Tested 25 May 2026)
4. **ABSA Bank Statement** - Separate parser for statement format (LOCKED/Tested 25 May 2026)
5. **Capitec (Mercantile)** - (LOCKED/Tested 25 May 2026)
6. **Capitec Business** - (LOCKED/Tested 25 May 2026)
7. **Capitec Personal** - (LOCKED/Tested 25 May 2026)
8. **Standard Bank** - Basic parser

### 3.11 BankAllocationController

**File:** `BankAllocationController.php`

**Methods:**

| Method | Purpose |
|--------|---------|
| `index($clientId, $bankId)` | Shows allocation page with unallocated/allocated transactions, GL accounts, allocation rules, suggested accounts. Renders `accounting.bank.allocate` |
| `save($request, $clientId, $bankId)` | Saves manual allocations. Computes VAT (standard rate from client). Optionally saves allocation rules. Updates transactions to 'allocated' |
| `post($request, $clientId, $bankId)` | **Posts** all allocated transactions to the GL. Creates one journal per transaction. Handles debit/credit direction, VAT Input/Output account lookups, 2-3 line journals. Updates transactions to 'posted' |
| `exclude($request, $clientId, $bankId)` | **AJAX**: Excludes selected transactions from allocation |
| `unexclude($request, $clientId, $bankId)` | **AJAX**: Restores excluded transactions to unallocated |
| `autoAllocate($request, $clientId, $bankId)` | **AJAX**: Auto-allocates unallocated transactions using keyword rules. Computes VAT. Increments rule match_count |
| `chartQuickAdd($request, $clientId)` | **AJAX**: Quick-adds a new level-3 GL account under a level-2 parent. Validates uniqueness. Returns JSON |
| `saveRule($request, $clientId)` | **AJAX**: Creates/updates allocation rule (keyword -> account mapping) |
| `deleteRule($request, $clientId, $ruleId)` | **AJAX**: Deletes allocation rule |

### 3.12 BankingController (Client Banking Tab - NOT GL Banking)

**File:** `BankingController.php`

This is the client profile banking tab (CRM data), separate from the GL bank module above. Manages `nexcore_client_banks` table.

| Method | Purpose |
|--------|---------|
| `index($clientId)` | List client bank accounts |
| `create($clientId)` | Bank account form |
| `store($request, $clientId)` | Create client bank record |
| `edit($clientId, $accountId)` | Edit bank account |
| `update($request, $clientId, $accountId)` | Update bank account |
| `destroy($clientId, $accountId)` | Delete bank account |
| `toggle($clientId, $accountId)` | Toggle active/inactive |

---

## 4. VIEWS (Blade Templates)

All views in `Modules/NexcoreClientManager/Resources/views/accounting/`.

### 4.1 Dashboard
| View | Purpose |
|------|---------|
| `accounting/dashboard.blade.php` | Main accounting dashboard with KPI cards (accounts, journals, revenue, expenses, net profit, assets, liabilities) and recent journals list |

### 4.2 Chart of Accounts
| View | Purpose |
|------|---------|
| `accounting/accounts/index.blade.php` | Chart of accounts listing with filter by account type |
| `accounting/accounts/form.blade.php` | Create/edit account form (shared for both actions) |
| `accounting/setup-coa.blade.php` | COA setup from industry template, with target client selector and template stats |
| `accounting/manage-coa.blade.php` | Top-level COA management showing all clients, account/journal/bank counts, with reset functionality |

### 4.3 Journals
| View | Purpose |
|------|---------|
| `accounting/journals/index.blade.php` | Journal entry listing with status badges |
| `accounting/journals/form.blade.php` | Create/edit journal form with dynamic line items (add/remove rows, debit/credit columns) |

### 4.4 Bank Module
| View | Purpose |
|------|---------|
| `accounting/bank/accounts.blade.php` | Bank accounts overview with GL links, transaction counts, action buttons |
| `accounting/bank/account-form.blade.php` | Create/edit bank account linking form |
| `accounting/bank/import.blade.php` | Bank statement PDF import page with PDF.js preview, bank type selector, parse/save flow |
| `accounting/bank/statements.blade.php` | Statement register listing imported statements |
| `accounting/bank/statement-view.blade.php` | Single statement detail with all transactions |
| `accounting/bank/allocate.blade.php` | Transaction allocation workspace with rule suggestions, manual allocation, auto-allocate, exclude/unexclude, quick chart-add, post-to-GL |

### 4.5 Financial Reports
| View | Purpose |
|------|---------|
| `accounting/trial-balance.blade.php` | Trial balance report with period filter, grouped by account type, balance check indicator |
| `accounting/income-statement.blade.php` | Income statement (P&L) with hierarchical revenue/COS/expense groups, gross/net profit |
| `accounting/balance-sheet.blade.php` | Balance sheet with assets/liabilities/equity groups, net profit, balance check |
| `accounting/general-ledger.blade.php` | General ledger with account range filter, per-account transaction listings, running balances |
| `accounting/management-pack.blade.php` | Combined management pack: income statement + balance sheet + trial balance in one view |

### 4.6 Shared Partials
| View | Purpose |
|------|---------|
| `accounting/partials/period-filter.blade.php` | Reusable SA financial year period selector (Q1-Q4, FY, custom) |

---

## 5. ROUTES

All routes are prefixed with `/nexcore/clients` and named `nexcore.clients.*`.
Routes file: `Modules/NexcoreClientManager/Routes/web.php`

### 5.1 Top-Level Accounting Routes (outside client context)

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `/nexcore/clients/manage-coa` | AccountController@manageCoa | nexcore.clients.manage-coa |
| DELETE | `/nexcore/clients/manage-coa/{client}/reset` | AccountController@resetClientAccounting | nexcore.clients.manage-coa.reset |

### 5.2 Client Accounting Dashboard

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `/nexcore/clients/{client}/accounting` | AccountingDashboardController@index | nexcore.clients.show.accounting.dashboard |

### 5.3 Chart of Accounts

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/accounts` | AccountController@index | ...show.accounting.accounts |
| GET | `{client}/accounting/accounts/create` | AccountController@create | ...show.accounting.accounts.create |
| POST | `{client}/accounting/accounts` | AccountController@store | ...show.accounting.accounts.store |
| GET | `{client}/accounting/accounts/{account}/edit` | AccountController@edit | ...show.accounting.accounts.edit |
| PUT | `{client}/accounting/accounts/{account}` | AccountController@update | ...show.accounting.accounts.update |
| DELETE | `{client}/accounting/accounts/{account}` | AccountController@destroy | ...show.accounting.accounts.destroy |

### 5.4 COA Template Setup

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/setup-coa` | AccountController@seedForm | ...show.accounting.setup-coa |
| POST | `{client}/accounting/setup-coa` | AccountController@seed | ...show.accounting.setup-coa.seed |

### 5.5 Journals

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/journals` | JournalController@index | ...show.accounting.journals |
| GET | `{client}/accounting/journals/create` | JournalController@create | ...show.accounting.journals.create |
| POST | `{client}/accounting/journals` | JournalController@store | ...show.accounting.journals.store |
| GET | `{client}/accounting/journals/{journal}/edit` | JournalController@edit | ...show.accounting.journals.edit |
| PUT | `{client}/accounting/journals/{journal}` | JournalController@update | ...show.accounting.journals.update |
| DELETE | `{client}/accounting/journals/{journal}` | JournalController@destroy | ...show.accounting.journals.destroy |

### 5.6 Bank Accounts (GL-linked)

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/bank/accounts` | NexcoreBankAccountController@index | ...show.accounting.bank.accounts |
| GET | `{client}/accounting/bank/accounts/create` | NexcoreBankAccountController@create | ...show.accounting.bank.accounts.create |
| POST | `{client}/accounting/bank/accounts` | NexcoreBankAccountController@store | ...show.accounting.bank.accounts.store |
| GET | `{client}/accounting/bank/accounts/{bank}/edit` | NexcoreBankAccountController@edit | ...show.accounting.bank.accounts.edit |
| PUT | `{client}/accounting/bank/accounts/{bank}` | NexcoreBankAccountController@update | ...show.accounting.bank.accounts.update |
| POST | `{client}/accounting/bank/accounts/{bank}/toggle` | NexcoreBankAccountController@toggle | ...show.accounting.bank.accounts.toggle |
| DELETE | `{client}/accounting/bank/accounts/{bank}` | NexcoreBankAccountController@destroy | ...show.accounting.bank.accounts.destroy |

### 5.7 Bank Statement Import

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/bank/{bank}/import` | BankImportController@import | ...show.accounting.bank.import |
| POST | `{client}/accounting/bank/{bank}/parse-pdf` | BankImportController@parsePdf | ...show.accounting.bank.parse-pdf |
| POST | `{client}/accounting/bank/{bank}/parse-pdf-fixer` | BankImportController@parsePdfFixer | ...show.accounting.bank.parse-pdf-fixer |
| POST | `{client}/accounting/bank/{bank}/import-save` | BankImportController@importSave | ...show.accounting.bank.import-save |
| GET | `{client}/accounting/bank/{bank}/statements` | BankImportController@statements | ...show.accounting.bank.statements |
| GET | `{client}/accounting/bank/{bank}/statements/{statement}/view` | BankImportController@statementView | ...show.accounting.bank.statements.view |
| DELETE | `{client}/accounting/bank/{bank}/statements/{statement}` | BankImportController@destroyStatement | ...show.accounting.bank.statements.destroy |

### 5.8 Bank Transaction Allocation

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/bank/{bank}/allocate` | BankAllocationController@index | ...show.accounting.bank.allocate |
| POST | `{client}/accounting/bank/{bank}/allocate/save` | BankAllocationController@save | ...show.accounting.bank.allocate.save |
| POST | `{client}/accounting/bank/{bank}/allocate/auto` | BankAllocationController@autoAllocate | ...show.accounting.bank.allocate.auto |
| POST | `{client}/accounting/bank/{bank}/allocate/exclude` | BankAllocationController@exclude | ...show.accounting.bank.allocate.exclude |
| POST | `{client}/accounting/bank/{bank}/allocate/unexclude` | BankAllocationController@unexclude | ...show.accounting.bank.allocate.unexclude |
| POST | `{client}/accounting/bank/{bank}/allocate/post` | BankAllocationController@post | ...show.accounting.bank.allocate.post |
| POST | `{client}/accounting/bank/chart-quick-add` | BankAllocationController@chartQuickAdd | ...show.accounting.bank.chart-quick-add |
| POST | `{client}/accounting/bank/rules` | BankAllocationController@saveRule | ...show.accounting.bank.rules.save |
| DELETE | `{client}/accounting/bank/rules/{rule}` | BankAllocationController@deleteRule | ...show.accounting.bank.rules.delete |

### 5.9 Financial Reports

| HTTP | URL | Controller@Method | Route Name |
|------|-----|-------------------|------------|
| GET | `{client}/accounting/ledger` | GeneralLedgerController@index | ...show.accounting.ledger |
| GET | `{client}/accounting/trial-balance` | TrialBalanceController@index | ...show.accounting.trial-balance |
| GET | `{client}/accounting/income-statement` | IncomeStatementController@index | ...show.accounting.income-statement |
| GET | `{client}/accounting/balance-sheet` | BalanceSheetController@index | ...show.accounting.balance-sheet |
| GET | `{client}/accounting/cash-flow` | AccountingDashboardController@index | ...show.accounting.cash-flow (placeholder - redirects to dashboard) |
| GET | `{client}/accounting/budget` | AccountingDashboardController@index | ...show.accounting.budget (placeholder - redirects to dashboard) |
| GET | `{client}/accounting/management-pack` | ManagementPackController@index | ...show.accounting.management-pack |

### 5.10 Tax Returns (Accounting-Adjacent, rendered from `cims_accounts` views)

| HTTP | URL | Route Name |
|------|-----|------------|
| GET | `{client}/returns/itr14` | ...show.returns.itr14 |
| GET | `{client}/returns/vat201` | ...show.returns.vat201 |
| GET | `{client}/returns/vat201v2` | ...show.returns.vat201v2 |
| GET | `{client}/returns/emp201` | ...show.returns.emp201 |
| GET | `{client}/returns/client-docs` | ...show.returns.client-docs |

---

## 6. SUMMARY

### Total Component Count

| Component | Count |
|-----------|-------|
| **Database Tables (Active GL)** | 9 core GL tables |
| **Database Tables (Templates)** | 4 (2 nexcore + 2 cims_gl duplicates) |
| **Database Tables (System Lookups)** | 4 (system_banks, system_bank_account_types, system_account_types, system_financial_types) |
| **Database Tables (Legacy/Support)** | 10 (cims_gl_company_master, cims_gl_bank_conversions_master, cims_gl_bank_names_master, cims_gl_bank_account_types, cims_gl_bank_account_status, cims_gl_bank_statement_frequency_master, nexcore_client_accounts, nexcore_client_budgets, nexcore_client_banks, nexcore_client_financials) |
| **Models (GL - Active)** | 9 (GlChartOfAccount, GlJournal, GlJournalLine, BankAccount, BankStatement, BankTransaction, BankAllocationRule, BankReconciliation, BankReconLine) |
| **Models (Templates)** | 2 (AccountTemplate, AccountTemplateItem) |
| **Models (Client-Level)** | 6 (ClientAccount, ClientJournal, ClientJournalLine, ClientBank, ClientBudget, ClientFinancial) |
| **Models (System Lookups)** | 2 (SystemAccountType, SystemFinancialType) |
| **Controllers** | 11 accounting controllers + 1 BankingController (CRM) |
| **Views** | 19 blade templates (including 1 shared partial) |
| **Routes** | 42 accounting routes + 5 tax return routes |

### Data Flow

1. **Chart of Accounts Setup**: Template -> AccountController@seed -> `cims_gl_chart_of_accounts_master` (3-level hierarchy with parent_id mapping)
2. **Manual Journals**: JournalController@store -> `cims_gl_journal_master_header` + `cims_gl_journal_header_linked_entries` (balanced debit=credit enforcement)
3. **Bank Import Pipeline**: PDF upload -> BankImportController@parsePdf (bank-specific parsers) -> BankImportController@importSave -> `cims_gl_bank_statement_upload_register` + `cims_gl_bank_statement_upload_transactions` (status: unallocated)
4. **Bank Allocation**: BankAllocationController@save/autoAllocate -> transactions updated to 'allocated' with account_id, VAT computed
5. **Bank Posting**: BankAllocationController@post -> creates journal per transaction with 2-3 lines (bank GL + contra account + optional VAT), transactions updated to 'posted'
6. **Reports**: All report controllers query posted journals within date range and build hierarchical summaries from journal line aggregates

### Key Architectural Notes

- **Company ID = Client ID**: The `company_id` field across all GL tables directly maps to the NexcoreClient primary key.
- **SA Financial Year**: Default period detection uses March fiscal year start (Q1=Mar-May, Q2=Jun-Aug, Q3=Sep-Nov, Q4=Dec-Feb).
- **VAT Handling**: 15% standard rate from `vat_rate` on the client. VAT Input/Output accounts are found by name match ("VAT Input"/"VAT Output") during bank posting.
- **Two Journal Number Formats**: JournalController uses `JNL-00001` format; BankAllocationController and NexcoreBankAccountController use `JNL000001` format.
- **Orphaned Models**: `NexcoreClientJournal` and `NexcoreClientJournalLine` reference tables (`nexcore_client_journals`, `nexcore_client_journal_lines`) that do NOT exist in the database.
- **Placeholder Routes**: `cash-flow` and `budget` routes exist but both point to AccountingDashboardController@index (not yet implemented).
- **No Migration Files**: The module's `Database/Migrations/` directory is empty. All tables were created via the main `database/migrations/` directory or directly in MySQL.
