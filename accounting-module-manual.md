# NexCore Accounting Module - Developer's Manual

**Version:** 1.0
**Date:** 27 May 2026
**Prepared for:** Yudeshan Gounden / ATP Services
**Purpose:** Complete isolation and documentation of the accounting module for migration to the new GrowCRM-based system

---

## TABLE OF CONTENTS

1. Executive Summary
2. Module Architecture Overview
3. Entity Relationship Diagram (ERD)
4. Database Tables (Detailed)
5. Data Flow: End-to-End Transaction Lifecycle
6. Forms Inventory (What Users Enter)
7. Views and Reports Inventory (What Users See)
8. Controllers and Business Logic
9. Route Map
10. Clean vs Clutter Assessment
11. Recommendations for New System

---

## 1. EXECUTIVE SUMMARY

The NexCore Accounting Module is a double-entry bookkeeping system built for South African small-to-medium businesses. It handles:

- Chart of Accounts (3-level segmented hierarchy)
- Manual journal entries with debit/credit enforcement
- Bank statement PDF import (8 SA bank parsers: FNB, Nedbank, ABSA x2, Capitec x3, Standard Bank)
- Transaction allocation to GL accounts (manual + keyword rule-based auto-allocation)
- VAT calculation (15% standard, zero-rated, exempt)
- Bank reconciliation
- Financial reports: Trial Balance, Income Statement, Balance Sheet, General Ledger, Management Pack

The module uses a SA financial year (March start) and enforces double-entry principles throughout.

CRITICAL FINDING: There are TWO parallel table schemas in the database:
- cims_gl_* tables = The ACTIVE system (9 core tables, all controllers use these)
- nexcore_client_* tables = LEGACY/unused (6 tables, some with no physical DB table)

Only the cims_gl_* schema is production-relevant. The nexcore_client_* tables and their 6 models are dead code.

---

## 2. MODULE ARCHITECTURE OVERVIEW

```
NexcoreClientManager Module
|
|-- Models/ (19 accounting models)
|   |-- GL Models (9) .............. ACTIVE - powers everything
|   |-- Template Models (2) ........ Used for COA seeding
|   |-- Client Models (6) .......... DEAD CODE - not used by any controller
|   |-- System Lookups (2) ......... Reference data
|
|-- Http/Controllers/ (12 controllers)
|   |-- AccountController .......... Chart of Accounts CRUD + template seeding + reset
|   |-- JournalController .......... Manual journal entries
|   |-- TrialBalanceController ..... Trial balance report
|   |-- IncomeStatementController .. P&L report
|   |-- BalanceSheetController ..... Balance sheet report
|   |-- GeneralLedgerController .... Full GL detail report
|   |-- ManagementPackController ... Combined report pack
|   |-- AccountingDashboardController  KPI dashboard
|   |-- NexcoreBankAccountController   Bank account setup + GL linking
|   |-- BankImportController ....... PDF parsing + statement import (~2000 lines)
|   |-- BankAllocationController ... Transaction allocation + GL posting
|   |-- BankingController .......... CRM bank tab (separate from GL banking)
|
|-- Resources/views/accounting/ (19 blade templates)
|   |-- dashboard.blade.php
|   |-- accounts/ (index, form)
|   |-- journals/ (index, form)
|   |-- bank/ (accounts, account-form, import, statements, statement-view, allocate)
|   |-- Reports (trial-balance, income-statement, balance-sheet, general-ledger, management-pack)
|   |-- partials/ (period-filter)
|   |-- setup-coa, manage-coa
|
|-- Routes/web.php (47 routes)
```

---

## 3. ENTITY RELATIONSHIP DIAGRAM (ERD)

### 3.1 Core GL Tables - Primary ERD

```
+----------------------------------+
|   NexcoreClient (GrowCRM)        |
|  (company_id FK across all GL)   |
+----------------------------------+
         |
         | 1:M
         v
+----------------------------------+       +----------------------------------+
| cims_gl_chart_of_accounts_master |       | cims_gl_bank_accounts_linked_    |
|----------------------------------|       |           to_coa                 |
| PK id                            |       |----------------------------------|
| FK company_id --> Client          |       | PK id                            |
|    account_code (varchar 20)     |<------| FK account_id --> COA             |
|    segment1, segment2, segment3  |       | FK company_id --> Client          |
|    account_level (1=Main,2=Sub,  |       | FK bank_id --> nexcore_system_    |
|                   3=Detail)      |       |                banks              |
|    account_name                  |       |    bank_name, account_number     |
|    account_type (asset/liability/|       |    branch_code, account_type     |
|      equity/revenue/cost_of_     |       |    is_active                     |
|      sales/expense)              |       |    opening_balance_date          |
|    normal_balance (debit/credit) |       |    opening_balance_amount        |
|    vat_type                      |       +----------------------------------+
|    is_active, is_system,         |                    |
|    is_header                     |                    | 1:M
| FK parent_id --> self            |                    v
+----------------------------------+       +----------------------------------+
         ^                                 | cims_gl_bank_statement_upload_   |
         |                                 |           register               |
         | M:1                             |----------------------------------|
         |                                 | PK id                            |
+----------------------------------+       | FK company_id --> Client          |
| cims_gl_journal_header_linked_   |       | FK bank_account_id --> Bank Acct  |
|           entries                |       |    statement_name, statement_ref |
|----------------------------------|       |    period_from, period_to        |
| PK id                            |       |    opening_balance, closing_bal  |
| FK journal_id --> Journal Header |       |    total_credits, total_debits   |
| FK account_id --> COA            |       |    transaction_count             |
|    description                   |       |    batch_ref, status             |
|    debit_amount, credit_amount   |       |    original_filename, file_path  |
|    vat_amount, vat_type          |       +----------------------------------+
|    ma_hidden                     |                    |
|    line_order                    |                    | batch_ref link
+----------------------------------+                    v
         ^                                 +----------------------------------+
         | M:1                             | cims_gl_bank_statement_upload_   |
         |                                 |          transactions            |
+----------------------------------+       |----------------------------------|
| cims_gl_journal_master_header    |<------| PK id                            |
|----------------------------------|  FK   | FK company_id --> Client          |
| PK id                            |       | FK bank_account_id --> Bank Acct  |
| FK company_id --> Client          |       |    transaction_date              |
|    journal_number                |       |    description, reference        |
|    journal_date                  |       |    amount, direction (debit/     |
|    period_id                     |       |                     credit)      |
|    reference, description        |       |    balance (running)             |
|    source (manual/bank_import/   |       | FK allocated_account_id --> COA  |
|     system/opening)              |       |    vat_type, vat_amount,         |
|    status (draft/posted/reversed)|       |    net_amount                    |
|    total_debit, total_credit     |       | FK journal_id --> Journal Header |
| FK reversal_of --> self          |       |    status (unallocated/allocated/|
|    attachment_path               |       |     posted/excluded)             |
|    created_by, posted_by         |       |    batch_ref                     |
+----------------------------------+       +----------------------------------+
                                                        ^
                                                        |
+----------------------------------+                    |
| cims_gl_bank_allocation_rules_   |                    |
|           master                 |       +----------------------------------+
|----------------------------------|       | cims_gl_bank_recon_master_header |
| PK id                            |       |----------------------------------|
| FK company_id --> Client          |       | PK id                            |
|    keyword (match pattern)       |       | FK company_id --> Client          |
| FK account_id --> COA            |       | FK bank_account_id --> Bank Acct  |
|    vat_type                      |       |    statement_date                |
|    priority                      |       |    statement_balance             |
|    match_count                   |       |    gl_balance                    |
|    is_active                     |       |    reconciled_balance            |
+----------------------------------+       |    outstanding_balance           |
                                           |    difference                    |
                                           |    status (draft/...)            |
                                           +----------------------------------+
                                                        |
                                                        | 1:M
                                                        v
                                           +----------------------------------+
                                           | cims_gl_bank_recon_header_       |
                                           |          linked_lines            |
                                           |----------------------------------|
                                           | PK id                            |
                                           | FK recon_id --> Recon Header     |
                                           | FK journal_id --> Journal Header |
                                           |    journal_line_id               |
                                           |    amount                        |
                                           |    is_reconciled                 |
                                           |    reconciled_at                 |
                                           +----------------------------------+
```

### 3.2 Relationship Summary

| Parent Table | Child Table | FK Column | Relationship |
|-------------|-------------|-----------|--------------|
| Client | chart_of_accounts_master | company_id | 1:M |
| Client | journal_master_header | company_id | 1:M |
| Client | bank_accounts_linked_to_coa | company_id | 1:M |
| Client | bank_statement_upload_register | company_id | 1:M |
| Client | bank_statement_upload_transactions | company_id | 1:M |
| Client | bank_allocation_rules_master | company_id | 1:M |
| Client | bank_recon_master_header | company_id | 1:M |
| chart_of_accounts_master | chart_of_accounts_master | parent_id | Self-referential (3-level tree) |
| chart_of_accounts_master | journal_header_linked_entries | account_id | 1:M |
| chart_of_accounts_master | bank_accounts_linked_to_coa | account_id | 1:M (GL link) |
| chart_of_accounts_master | bank_statement_upload_transactions | allocated_account_id | 1:M |
| chart_of_accounts_master | bank_allocation_rules_master | account_id | 1:M |
| journal_master_header | journal_header_linked_entries | journal_id | 1:M |
| journal_master_header | bank_statement_upload_transactions | journal_id | 1:M (after posting) |
| journal_master_header | journal_master_header | reversal_of | Self-referential (reversal) |
| bank_accounts_linked_to_coa | bank_statement_upload_register | bank_account_id | 1:M |
| bank_accounts_linked_to_coa | bank_statement_upload_transactions | bank_account_id | 1:M |
| bank_accounts_linked_to_coa | bank_recon_master_header | bank_account_id | 1:M |
| bank_recon_master_header | bank_recon_header_linked_lines | recon_id | 1:M |
| nexcore_system_banks | bank_accounts_linked_to_coa | bank_id | 1:M |

### 3.3 Template Tables ERD (COA Seeding)

```
+----------------------------------+       +----------------------------------+
| nexcore_account_templates        |       | nexcore_account_template_items   |
|----------------------------------|       |----------------------------------|
| PK id                            |------>| PK id                            |
|    template_name                 |  1:M  | FK template_id                   |
|    industry_type                 |       |    account_code, segment1-3      |
|    description                   |       |    account_level, account_name   |
|    is_default, is_active         |       |    account_type, normal_balance  |
+----------------------------------+       |    vat_type, is_system, is_header|
                                           +----------------------------------+

NOTE: There are DUPLICATE template tables (cims_gl_chart_of_accounts_template_header
and cims_gl_chart_of_accounts_template_items) with the same schema but using enums
instead of varchars. This duplication should be eliminated in the new system.
```

---

## 4. DATABASE TABLES (DETAILED)

### 4.1 ACTIVE GL Tables (9 tables - CARRY FORWARD)

These are the only tables that matter. Every controller reads/writes to these.

| # | Table Name | Purpose | Key Columns | Row Scope |
|---|-----------|---------|-------------|-----------|
| 1 | cims_gl_chart_of_accounts_master | 3-level segmented COA | account_code (1/10/1010 format), segment1-3, account_level, account_type, normal_balance, vat_type, parent_id | Per company_id |
| 2 | cims_gl_journal_master_header | Journal entry headers | journal_number, journal_date, source, status, total_debit, total_credit, reversal_of | Per company_id |
| 3 | cims_gl_journal_header_linked_entries | Journal line items | journal_id, account_id, debit_amount, credit_amount, vat_amount, vat_type, ma_hidden | Per journal |
| 4 | cims_gl_bank_accounts_linked_to_coa | Bank accounts linked to GL | bank_id, account_id (GL link), account_number, branch_code, opening_balance_date/amount | Per company_id |
| 5 | cims_gl_bank_statement_upload_register | Imported statement headers | bank_account_id, statement_ref, period_from/to, opening/closing balance, batch_ref | Per bank account |
| 6 | cims_gl_bank_statement_upload_transactions | Individual bank transactions | bank_account_id, amount, direction, allocated_account_id, vat_type/amount, journal_id, status | Per bank account |
| 7 | cims_gl_bank_allocation_rules_master | Keyword auto-allocation rules | keyword, account_id, vat_type, priority, match_count | Per company_id |
| 8 | cims_gl_bank_recon_master_header | Bank reconciliation headers | bank_account_id, statement_balance, gl_balance, reconciled_balance, difference, status | Per bank account |
| 9 | cims_gl_bank_recon_header_linked_lines | Reconciliation matched lines | recon_id, journal_id, journal_line_id, amount, is_reconciled | Per recon |

### 4.2 Template Tables (2 tables - CARRY FORWARD, eliminate duplicates)

| # | Table Name | Purpose |
|---|-----------|---------|
| 1 | nexcore_account_templates | Industry COA templates (name, type, default flag) |
| 2 | nexcore_account_template_items | Template line items (account structure to seed into client COA) |

NOTE: cims_gl_chart_of_accounts_template_header and cims_gl_chart_of_accounts_template_items are DUPLICATES. Eliminate.

### 4.3 System Lookup Tables (4 tables - CARRY FORWARD)

| # | Table Name | Purpose |
|---|-----------|---------|
| 1 | nexcore_system_banks | SA bank directory (FNB, Nedbank, ABSA, etc.) |
| 2 | nexcore_system_bank_account_types | Bank account type reference (cheque, savings, etc.) |
| 3 | nexcore_system_account_types | GL account type reference |
| 4 | nexcore_system_financial_types | Financial return type reference |

### 4.4 LEGACY Tables (10 tables - DO NOT CARRY FORWARD)

| # | Table Name | Why It's Dead |
|---|-----------|--------------|
| 1 | cims_gl_company_master | Company-level settings - never queried by any active controller |
| 2 | cims_gl_bank_conversions_master | PDF conversion history tracking - not used in current flow |
| 3 | cims_gl_bank_names_master | Legacy bank directory - replaced by nexcore_system_banks |
| 4 | cims_gl_bank_account_types | Legacy account type lookup - replaced by nexcore_system_bank_account_types |
| 5 | cims_gl_bank_account_status | Bank status lookup - never referenced |
| 6 | cims_gl_bank_statement_frequency_master | Statement frequency lookup - never referenced |
| 7 | nexcore_client_accounts | Parallel COA - not used by any controller |
| 8 | nexcore_client_budgets | Budget table - no controller writes to it |
| 9 | nexcore_client_banks | CRM bank tab only (separate from GL banking) |
| 10 | nexcore_client_financials | Financial returns tracker - no active controller |

---

## 5. DATA FLOW: END-TO-END TRANSACTION LIFECYCLE

### 5.1 Overview Flow

```
[1. SETUP]            [2. DATA ENTRY]         [3. PROCESSING]         [4. REPORTING]

Template ----seed---> Chart of                                         Trial Balance
                      Accounts                                         Income Statement
                         |                                             Balance Sheet
                         v                                             General Ledger
                    Bank Account ----link----> GL Account              Management Pack
                      Setup                                                  ^
                         |                                                   |
                         v                                            [Query posted
                    PDF Upload                                         journals by
                         |                                             date range]
                         v                                                   |
                    Parse PDF ------8 bank parsers                           |
                         |                                                   |
                         v                                                   |
                    Save to DB (status: unallocated)                         |
                         |                                                   |
                         v                                                   |
                    Allocate to GL Accounts                                  |
                    (manual or auto-rules)                                   |
                    (status: allocated)                                       |
                         |                                                   |
                         v                                                   |
                    Post to GL --------creates journal--------> Journals ----+
                    (status: posted)   (2-3 lines per txn)
                         |
                         v
                    Bank Reconciliation
                    (match GL entries to bank statement)

       Manual Journal Entry --------direct post-----------> Journals --------+
```

### 5.2 Step-by-Step: Bank Import Pipeline

This is the primary data entry path for most transactions.

STEP 1: CHART OF ACCOUNTS SETUP
- Controller: AccountController@seedForm / @seed
- User selects industry template (e.g., "General Business")
- System copies all template_items into cims_gl_chart_of_accounts_master
- Builds 3-level hierarchy: Level 1 (Main) -> Level 2 (Sub) -> Level 3 (Detail)
- parent_id is mapped across levels using account_code segment matching
- Only level-3 (detail) accounts can receive journal postings

STEP 2: BANK ACCOUNT SETUP
- Controller: NexcoreBankAccountController@store
- User creates bank account record linked to a GL account (typically a level-3 bank-type asset account)
- If opening balance is provided, system auto-creates:
  - An "Opening Balance Equity" account (if not exists) under equity
  - A balanced journal entry: DR Bank, CR Opening Balance Equity

STEP 3: PDF BANK STATEMENT UPLOAD
- Controller: BankImportController@import (shows upload page)
- System auto-detects bank type from bank_name (FNB, Nedbank, ABSA, Capitec, Standard)
- User uploads PDF bank statement
- PDF text is extracted client-side (PDF.js)
- Text sent to BankImportController@parsePdf via AJAX

STEP 4: PDF PARSING
- Controller: BankImportController@parsePdf
- Routes to bank-specific parser based on bank type:
  - parseFnbText (+ OCR fallback for missing descriptions)
  - parseNedbankText (balance-difference method)
  - parseAbsaTransactionHistoryText
  - parseAbsaBankStatementText
  - parseCapitecText (Mercantile)
  - parseCapitecBusinessText
  - parseCapitecPersonalText
  - parseStandardText
- Returns structured JSON: array of {date, description, reference, amount, direction, balance}

STEP 5: IMPORT SAVE
- Controller: BankImportController@importSave
- Creates batch_ref (unique identifier)
- Creates statement record in cims_gl_bank_statement_upload_register
- Creates transaction records in cims_gl_bank_statement_upload_transactions
- All transactions saved with status = 'unallocated'

STEP 6: TRANSACTION ALLOCATION
- Controller: BankAllocationController@index (workspace view)
- Two allocation methods:
  a) MANUAL: User selects GL account + VAT type for each transaction
  b) AUTO: System applies keyword rules from cims_gl_bank_allocation_rules_master
     - Rules matched by keyword substring in transaction description
     - Higher priority rules matched first
     - match_count incremented on each successful match
- VAT is computed: net_amount = amount / 1.15, vat_amount = amount - net_amount (standard rate)
- Transactions updated to status = 'allocated'
- User can also EXCLUDE transactions (e.g., inter-account transfers)

STEP 7: POST TO GL
- Controller: BankAllocationController@post
- For each allocated transaction, creates a journal entry:
  - Journal header: source='bank_import', status='posted'
  - Journal lines (2 or 3):
    - Line 1: Bank GL account (DR for credits received, CR for debits paid)
    - Line 2: Contra GL account (the allocated account)
    - Line 3: VAT account (if vat_type='standard') - looks up "VAT Input" or "VAT Output" by name
- Transaction updated to status = 'posted' with journal_id FK

STEP 8: BANK RECONCILIATION
- Controller: Uses BankReconciliation / BankReconLine models
- Matches bank statement lines against GL journal entries
- Calculates: statement_balance vs gl_balance, reconciled_balance, difference

### 5.3 Step-by-Step: Manual Journal Entry

STEP 1: CREATE JOURNAL
- Controller: JournalController@create
- Auto-generates journal number: JNL-00001 (incremental)
- Shows form with all active level-3 accounts

STEP 2: ENTER LINES
- User adds debit/credit lines against GL accounts
- Each line: account_id, description, debit_amount OR credit_amount, vat_amount, vat_type

STEP 3: VALIDATE AND POST
- Controller: JournalController@store
- Validates: total debits = total credits (must balance)
- Creates header + lines in DB::transaction
- Status immediately set to 'posted'

### 5.4 Step-by-Step: Financial Reports

All reports follow the same pattern:
1. Accept date range (defaults to current SA financial year quarter)
2. Query all POSTED journal lines within date range
3. Group by account, sum debits and credits
4. Calculate closing balance based on normal_balance direction

TRIAL BALANCE:
- Groups all accounts by account_type
- Shows closing debit OR credit for each account
- Validates: total debits = total credits

INCOME STATEMENT (P&L):
- Revenue accounts (credit balances = income)
- Less: Cost of Sales accounts
- = Gross Profit
- Less: Expense accounts
- = Net Profit
- Builds 3-level hierarchy: Main -> Sub -> Detail

BALANCE SHEET:
- Assets (debit balances)
- Liabilities (credit balances)
- Equity (credit balances) + Net Profit (from income calculation)
- Validates: Assets = Liabilities + Equity + Net Profit

GENERAL LEDGER:
- Per-account transaction listing
- Each line shows date, description, debit, credit, running balance
- Supports account range filter (from_account to to_account)

MANAGEMENT PACK:
- Combined view: Income Statement + Balance Sheet + Trial Balance in one page

---

## 6. FORMS INVENTORY (What Users Enter)

| # | Form | Controller Method | What It Captures | Database Target |
|---|------|------------------|------------------|-----------------|
| 1 | Create/Edit GL Account | AccountController@create/edit | account_code, segment1-3, account_level, account_name, account_type, normal_balance, vat_type, is_active, is_system, is_header, description, parent_id | cims_gl_chart_of_accounts_master |
| 2 | Seed COA from Template | AccountController@seedForm | template_id, target client | Bulk insert into cims_gl_chart_of_accounts_master |
| 3 | COA Management + Reset | AccountController@manageCoa | Confirmation text "DELETE" to reset | Deletes ALL accounting data for client |
| 4 | Create/Edit Journal | JournalController@create/edit | journal_date, reference, description, notes, attachment + N lines (account_id, description, debit_amount, credit_amount, vat_amount, vat_type) | cims_gl_journal_master_header + cims_gl_journal_header_linked_entries |
| 5 | Create/Edit Bank Account | NexcoreBankAccountController@create/edit | bank_id, account_id (GL link), account_number, branch_code, account_type, opening_balance_date, opening_balance_amount | cims_gl_bank_accounts_linked_to_coa |
| 6 | Upload Bank Statement PDF | BankImportController@import | PDF file upload, bank type selection | Parsed then saved to register + transactions |
| 7 | Allocate Transactions | BankAllocationController@index | Per-transaction: account_id, vat_type. Bulk: auto-allocate, exclude, unexclude | Updates cims_gl_bank_statement_upload_transactions |
| 8 | Quick-Add GL Account | BankAllocationController@chartQuickAdd | parent_id, account_name | Creates new level-3 account in COA |
| 9 | Create/Edit Allocation Rule | BankAllocationController@saveRule | keyword, account_id, vat_type, priority | cims_gl_bank_allocation_rules_master |

---

## 7. VIEWS AND REPORTS INVENTORY (What Users See)

### 7.1 Dashboard and Setup Views

| # | View File | Purpose | Key Data Shown |
|---|----------|---------|---------------|
| 1 | accounting/dashboard.blade.php | KPI overview | Account counts (main/sub/detail), journal count, revenue total, expense total, net profit, total assets, total liabilities, 10 recent journals |
| 2 | accounting/setup-coa.blade.php | COA template seeding | Available templates with item counts, target client selector |
| 3 | accounting/manage-coa.blade.php | Multi-client COA management | All clients with account/journal/bank/transaction counts, reset button |

### 7.2 Chart of Accounts Views

| # | View File | Purpose | Key Data Shown |
|---|----------|---------|---------------|
| 4 | accounting/accounts/index.blade.php | COA listing | All accounts (non-level-1) with type, code, name, balance direction, active status, filter by type |
| 5 | accounting/accounts/form.blade.php | Account create/edit | All account fields, parent selector (headers only), dropdowns for type/balance/VAT |

### 7.3 Journal Views

| # | View File | Purpose | Key Data Shown |
|---|----------|---------|---------------|
| 6 | accounting/journals/index.blade.php | Journal listing | All journals with number, date, description, source, status badge, line count |
| 7 | accounting/journals/form.blade.php | Journal create/edit | Dynamic line items table (add/remove rows), debit/credit columns, running totals |

### 7.4 Bank Module Views

| # | View File | Purpose | Key Data Shown |
|---|----------|---------|---------------|
| 8 | accounting/bank/accounts.blade.php | Bank accounts overview | All bank accounts with GL link, bank name, account number, unallocated/total/posted counts |
| 9 | accounting/bank/account-form.blade.php | Bank account create/edit | Bank selector, GL account link, account details, opening balance |
| 10 | accounting/bank/import.blade.php | PDF import workspace | PDF.js preview panel, bank type selector, parse button, transaction preview table, save button |
| 11 | accounting/bank/statements.blade.php | Statement register | All imported statements with ref, period, balances, transaction count |
| 12 | accounting/bank/statement-view.blade.php | Statement detail | All transactions in a statement with date, description, amount, direction, status |
| 13 | accounting/bank/allocate.blade.php | Allocation workspace | Unallocated/allocated transaction tables, GL account dropdowns, rule suggestions, auto-allocate button, post-to-GL button |

### 7.5 Financial Report Views

| # | View File | Purpose | Key Data Shown |
|---|----------|---------|---------------|
| 14 | accounting/trial-balance.blade.php | Trial balance report | Accounts grouped by type, closing debit/credit per account, balance check (debits = credits) |
| 15 | accounting/income-statement.blade.php | Income statement (P&L) | 3-level hierarchy: Revenue - COS = Gross Profit - Expenses = Net Profit |
| 16 | accounting/balance-sheet.blade.php | Balance sheet | 3-level hierarchy: Assets = Liabilities + Equity + Net Profit, balance check |
| 17 | accounting/general-ledger.blade.php | General ledger detail | Per-account transaction listing, running balance, account range filter, date range |
| 18 | accounting/management-pack.blade.php | Combined pack | Income Statement + Balance Sheet + Trial Balance in single view |

### 7.6 Shared Partials

| # | View File | Purpose |
|---|----------|---------|
| 19 | accounting/partials/period-filter.blade.php | SA financial year period selector (Q1=Mar-May, Q2=Jun-Aug, Q3=Sep-Nov, Q4=Dec-Feb, Full Year, Custom) |

---

## 8. CONTROLLERS AND BUSINESS LOGIC

### 8.1 Business Rules Enforced in Code

| Rule | Where Enforced | Details |
|------|---------------|---------|
| Debits must equal credits | JournalController@store, @update | Rejects journal if total_debit != total_credit |
| Only level-3 accounts accept postings | JournalController@create, BankAllocationController | Account dropdowns filter to level-3 only |
| System accounts cannot be deleted | AccountController@destroy | Checks is_system flag |
| Accounts with journal lines cannot be deleted | AccountController@destroy | Checks for existing journal_header_linked_entries |
| Bank accounts prevent duplicate GL links | NexcoreBankAccountController@store | Validates no other bank account uses same GL account_id |
| VAT @ 15% standard rate | BankAllocationController@save, @autoAllocate, @post | net = amount / 1.15, vat = amount - net |
| VAT Input/Output accounts found by name | BankAllocationController@post | Searches COA for account_name LIKE "VAT Input" / "VAT Output" |
| Opening balance auto-creates equity account | NexcoreBankAccountController@createOpeningBalanceJournal | Creates "Opening Balance Equity" if not exists |
| All financial reports use posted journals only | All report controllers | WHERE status = 'posted' |
| SA Financial Year (March start) | TrialBalanceController | Q1=Mar-May, Q2=Jun-Aug, Q3=Sep-Nov, Q4=Dec-Feb |
| Bank allocation rules by priority | BankAllocationController@autoAllocate | Higher priority number = matched first |

### 8.2 Controller Sizes (Complexity Indicator)

| Controller | Approx Lines | Complexity |
|-----------|-------------|-----------|
| BankImportController | ~2000 | HIGHEST - 8 bank parsers, each with unique logic |
| BankAllocationController | ~400 | HIGH - allocation, auto-rules, posting, VAT calc |
| AccountController | ~300 | MEDIUM - CRUD + seeding + reset |
| NexcoreBankAccountController | ~250 | MEDIUM - CRUD + opening balance journal |
| JournalController | ~200 | MEDIUM - CRUD + balance validation |
| ManagementPackController | ~200 | MEDIUM - combined report building |
| IncomeStatementController | ~150 | LOW-MEDIUM - hierarchy builder |
| BalanceSheetController | ~150 | LOW-MEDIUM - hierarchy builder |
| TrialBalanceController | ~100 | LOW - straightforward aggregation |
| GeneralLedgerController | ~100 | LOW - per-account listing |
| AccountingDashboardController | ~80 | LOW - KPI queries |

---

## 9. ROUTE MAP

All routes prefixed with /nexcore/clients. Total: 47 routes.

### Setup and Management
```
GET    /nexcore/clients/manage-coa                           -> COA management dashboard
DELETE /nexcore/clients/manage-coa/{client}/reset             -> Reset all client accounting data
GET    /nexcore/clients/{client}/accounting                   -> Accounting dashboard
GET    /nexcore/clients/{client}/accounting/setup-coa         -> Template seeding form
POST   /nexcore/clients/{client}/accounting/setup-coa         -> Execute seeding
```

### Chart of Accounts (6 routes)
```
GET    {client}/accounting/accounts                           -> List accounts
GET    {client}/accounting/accounts/create                    -> Create form
POST   {client}/accounting/accounts                           -> Store account
GET    {client}/accounting/accounts/{account}/edit            -> Edit form
PUT    {client}/accounting/accounts/{account}                 -> Update account
DELETE {client}/accounting/accounts/{account}                 -> Delete account
```

### Journals (6 routes)
```
GET    {client}/accounting/journals                           -> List journals
GET    {client}/accounting/journals/create                    -> Create form
POST   {client}/accounting/journals                           -> Store journal
GET    {client}/accounting/journals/{journal}/edit            -> Edit form
PUT    {client}/accounting/journals/{journal}                 -> Update journal
DELETE {client}/accounting/journals/{journal}                 -> Delete journal
```

### Bank Accounts (7 routes)
```
GET    {client}/accounting/bank/accounts                      -> List bank accounts
GET    {client}/accounting/bank/accounts/create               -> Create form
POST   {client}/accounting/bank/accounts                      -> Store bank account
GET    {client}/accounting/bank/accounts/{bank}/edit          -> Edit form
PUT    {client}/accounting/bank/accounts/{bank}               -> Update bank account
POST   {client}/accounting/bank/accounts/{bank}/toggle        -> Toggle active
DELETE {client}/accounting/bank/accounts/{bank}               -> Delete bank account
```

### Bank Statement Import (7 routes)
```
GET    {client}/accounting/bank/{bank}/import                 -> Import page
POST   {client}/accounting/bank/{bank}/parse-pdf              -> Parse PDF (AJAX)
POST   {client}/accounting/bank/{bank}/parse-pdf-fixer        -> Parse fixer (AJAX)
POST   {client}/accounting/bank/{bank}/import-save            -> Save import (AJAX)
GET    {client}/accounting/bank/{bank}/statements             -> Statement list
GET    {client}/accounting/bank/{bank}/statements/{stmt}/view -> Statement detail
DELETE {client}/accounting/bank/{bank}/statements/{stmt}      -> Delete statement
```

### Bank Allocation (9 routes)
```
GET    {client}/accounting/bank/{bank}/allocate               -> Allocation workspace
POST   {client}/accounting/bank/{bank}/allocate/save          -> Save allocations
POST   {client}/accounting/bank/{bank}/allocate/auto          -> Auto-allocate
POST   {client}/accounting/bank/{bank}/allocate/exclude       -> Exclude transactions
POST   {client}/accounting/bank/{bank}/allocate/unexclude     -> Unexclude
POST   {client}/accounting/bank/{bank}/allocate/post          -> Post to GL
POST   {client}/accounting/bank/chart-quick-add               -> Quick-add GL account
POST   {client}/accounting/bank/rules                         -> Save allocation rule
DELETE {client}/accounting/bank/rules/{rule}                  -> Delete rule
```

### Financial Reports (7 routes)
```
GET    {client}/accounting/trial-balance                      -> Trial balance
GET    {client}/accounting/income-statement                   -> Income statement
GET    {client}/accounting/balance-sheet                      -> Balance sheet
GET    {client}/accounting/ledger                             -> General ledger
GET    {client}/accounting/management-pack                    -> Management pack
GET    {client}/accounting/cash-flow                          -> PLACEHOLDER (dashboard)
GET    {client}/accounting/budget                             -> PLACEHOLDER (dashboard)
```

### Tax Returns (5 routes - accounting-adjacent)
```
GET    {client}/returns/itr14                                 -> ITR14
GET    {client}/returns/vat201                                -> VAT201
GET    {client}/returns/vat201v2                              -> VAT201 v2
GET    {client}/returns/emp201                                -> EMP201
GET    {client}/returns/client-docs                           -> Client documents
```

---

## 10. CLEAN vs CLUTTER ASSESSMENT

### CARRY FORWARD (Clean, Production-Ready)

| Component | Status | Notes |
|-----------|--------|-------|
| 9 core cims_gl_* tables | CLEAN | Well-structured, proper FKs, consistent naming |
| 9 GL models | CLEAN | Proper relationships, casts, scopes |
| AccountController | CLEAN | Solid CRUD + seeding logic |
| JournalController | CLEAN | Proper double-entry enforcement |
| TrialBalanceController | CLEAN | Correct SA fiscal year handling |
| IncomeStatementController | CLEAN | Good 3-level hierarchy builder |
| BalanceSheetController | CLEAN | Proper A=L+E+NP validation |
| GeneralLedgerController | CLEAN | Clean per-account listing |
| ManagementPackController | CLEAN | Combined report works |
| AccountingDashboardController | CLEAN | Good KPI summary |
| NexcoreBankAccountController | CLEAN | Proper GL linking + opening balance journal |
| BankImportController (8 parsers) | CLEAN but COMPLEX | 2000 lines, tested and locked parsers |
| BankAllocationController | CLEAN | Manual + auto allocation + posting |
| All 19 blade views | CLEAN | Consistent UI patterns |
| period-filter partial | CLEAN | Reusable SA fiscal year component |
| 2 template tables | CLEAN | But eliminate the duplicates |
| 4 system lookup tables | CLEAN | Reference data |

### DO NOT CARRY FORWARD (Clutter)

| Component | Why It's Clutter |
|-----------|-----------------|
| nexcore_client_accounts table + model | Parallel COA that no controller uses |
| nexcore_client_budgets table + model | Budget table with no controller |
| nexcore_client_banks table + model | CRM-level banking, separate from GL |
| nexcore_client_financials table + model | Financial returns tracker, no active controller |
| NexcoreClientJournal model | References table that DOES NOT EXIST in database |
| NexcoreClientJournalLine model | References table that DOES NOT EXIST in database |
| cims_gl_company_master table | Legacy company settings, never queried |
| cims_gl_bank_conversions_master table | Old conversion history, not used |
| cims_gl_bank_names_master table | Replaced by nexcore_system_banks |
| cims_gl_bank_account_types table | Replaced by nexcore_system_bank_account_types |
| cims_gl_bank_account_status table | Never referenced |
| cims_gl_bank_statement_frequency_master table | Never referenced |
| cims_gl_chart_of_accounts_template_header table | Duplicate of nexcore_account_templates |
| cims_gl_chart_of_accounts_template_items table | Duplicate of nexcore_account_template_items |
| NexcoreSystemAccountType model | Not referenced by any active controller |
| NexcoreSystemFinancialType model | Not referenced by any active controller |
| cash-flow route | Placeholder pointing to dashboard |
| budget route | Placeholder pointing to dashboard |
| BankingController | CRM bank tab, not part of GL module |

### KNOWN ISSUES TO FIX IN NEW SYSTEM

| Issue | Details | Impact |
|-------|---------|--------|
| Two journal number formats | JournalController uses JNL-00001, BankAllocationController uses JNL000001 | Inconsistency in journal numbering |
| VAT accounts found by name match | BankAllocationController@post searches for "VAT Input"/"VAT Output" by name string | Fragile - breaks if account renamed |
| No migration files | All tables created directly in MySQL or via main migrations directory | No version control on schema |
| No financial periods table | Period handling is calculated at runtime based on March fiscal year | Cannot customize fiscal year start |
| Duplicate template tables | 2 sets of template tables with slightly different column types | Confusing, wastes space |
| 6 orphaned models | NexcoreClient* models reference unused/nonexistent tables | Dead code in codebase |
| No audit trail | No logging of who posted/modified/deleted what (beyond created_by) | Compliance risk |
| No multi-currency support | All amounts assumed ZAR | Cannot handle forex clients |

---

## 11. RECOMMENDATIONS FOR NEW SYSTEM

### 11.1 Tables to Migrate (15 tables)

```
CORE GL (9):
  cims_gl_chart_of_accounts_master
  cims_gl_journal_master_header
  cims_gl_journal_header_linked_entries
  cims_gl_bank_accounts_linked_to_coa
  cims_gl_bank_statement_upload_register
  cims_gl_bank_statement_upload_transactions
  cims_gl_bank_allocation_rules_master
  cims_gl_bank_recon_master_header
  cims_gl_bank_recon_header_linked_lines

TEMPLATES (2 - consolidated):
  nexcore_account_templates
  nexcore_account_template_items

SYSTEM LOOKUPS (4):
  nexcore_system_banks
  nexcore_system_bank_account_types
  nexcore_system_account_types
  nexcore_system_financial_types
```

### 11.2 Rename Recommendation

For the new GrowCRM install, rename all tables with a consistent prefix:

```
Old: cims_gl_chart_of_accounts_master     -> New: gl_chart_of_accounts
Old: cims_gl_journal_master_header        -> New: gl_journals
Old: cims_gl_journal_header_linked_entries -> New: gl_journal_lines
Old: cims_gl_bank_accounts_linked_to_coa  -> New: gl_bank_accounts
Old: cims_gl_bank_statement_upload_register      -> New: gl_bank_statements
Old: cims_gl_bank_statement_upload_transactions   -> New: gl_bank_transactions
Old: cims_gl_bank_allocation_rules_master  -> New: gl_bank_allocation_rules
Old: cims_gl_bank_recon_master_header     -> New: gl_bank_reconciliations
Old: cims_gl_bank_recon_header_linked_lines -> New: gl_bank_reconciliation_lines
```

### 11.3 New Tables to Add

| Table | Purpose |
|-------|---------|
| gl_financial_periods | Proper period management (year, month, start_date, end_date, is_closed, closed_by) |
| gl_vat_accounts | Explicit VAT account mapping instead of name-based lookup |
| gl_audit_log | Track all GL actions (who, what, when, before/after values) |
| gl_journal_attachments | Separate attachment table for multiple files per journal |

### 11.4 Migration Strategy

1. Install fresh GrowCRM on new domain
2. Create proper Laravel migrations for all 15 tables (with correct indexes and foreign keys)
3. Link company_id to GrowCRM's clients table
4. Port the 9 GL models (clean, tested)
5. Port the 11 active controllers
6. Port the 19 blade views
7. Add the 4 new tables (periods, vat accounts, audit log, attachments)
8. Fix the two known issues (journal number format, VAT name-based lookup)
9. Seed system lookup tables
10. Write data migration script to copy existing production data from old system

---

## END OF MANUAL

This document covers the complete accounting module as it exists today.
Everything marked "CARRY FORWARD" is production-tested and clean.
Everything marked "DO NOT CARRY FORWARD" is dead code or legacy cruft.

The module is self-contained: 9 tables, 9 models, 11 controllers, 19 views.
No external dependencies beyond GrowCRM's client table and the system lookup tables.
