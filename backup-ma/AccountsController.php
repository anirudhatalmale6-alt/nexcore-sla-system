<?php

namespace Modules\CIMS_ACCOUNTS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CIMS_ACCOUNTS\Models\AccountsCompany;
use Modules\CIMS_ACCOUNTS\Models\ChartOfAccount;
use Modules\CIMS_ACCOUNTS\Models\AccountTemplate;
use Modules\CIMS_ACCOUNTS\Models\AccountTemplateItem;
use Modules\CIMS_ACCOUNTS\Models\Journal;
use Modules\CIMS_ACCOUNTS\Models\JournalLine;
use Modules\CIMS_ACCOUNTS\Models\BankAccount;
use Modules\CIMS_ACCOUNTS\Models\BankTransaction;
use Modules\CIMS_ACCOUNTS\Models\AllocationRule;
use Modules\CIMS_ACCOUNTS\Models\BankStatement;

class AccountsController extends Controller
{
    public function dashboard()
    {
        // Only load companies that have chart of accounts entries
        $companies = AccountsCompany::where('is_active', 1)
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('cims_accounts_chart')
                    ->whereColumn('cims_accounts_chart.company_id', 'cims_accounts_companies.id');
            })
            ->get();

        return view('cims_accounts::accounts.dashboard', compact('companies'));
    }

    // ===== COMPANIES =====

    public function companies()
    {
        $companies = AccountsCompany::orderBy('company_name')->get();
        foreach ($companies as $c) {
            $c->account_count = ChartOfAccount::where('company_id', $c->id)->count();
        }
        return view('cims_accounts::accounts.companies', compact('companies'));
    }

    public function companyCreate()
    {
        $clients = \DB::table('client_master')
            ->select('client_id', 'client_code', 'company_name', 'trading_name')
            ->orderBy('company_name')->get();
        $company = null;
        return view('cims_accounts::accounts.company-form', compact('clients', 'company'));
    }

    public function companyStore(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'financial_year_start' => 'required|integer|min:1|max:12',
        ]);
        $data = $request->only(['client_id', 'client_code', 'company_name', 'trading_name', 'vat_number', 'registration_number', 'financial_year_start']);
        $data['is_active'] = 1;
        $data['created_by'] = auth()->id();
        if ($request->filled('client_id')) {
            $client = \DB::table('client_master')->where('client_id', $request->client_id)->first();
            if ($client) {
                $data['client_code'] = $client->client_code;
                if (empty($data['company_name'])) $data['company_name'] = $client->company_name;
            }
        }
        $company = AccountsCompany::create($data);
        return redirect()->route('cimsaccounts.chart.index', $company->id)->with('success', 'Company created. Now seed the Chart of Accounts from a template.');
    }

    public function companyEdit($id)
    {
        $company = AccountsCompany::findOrFail($id);
        $clients = \DB::table('client_master')->select('client_id', 'client_code', 'company_name', 'trading_name')->orderBy('company_name')->get();
        return view('cims_accounts::accounts.company-form', compact('company', 'clients'));
    }

    public function companyUpdate(Request $request, $id)
    {
        $company = AccountsCompany::findOrFail($id);
        $request->validate(['company_name' => 'required|string|max:255', 'financial_year_start' => 'required|integer|min:1|max:12']);
        $company->update($request->only(['client_id', 'client_code', 'company_name', 'trading_name', 'vat_number', 'registration_number', 'financial_year_start', 'is_active']));
        return redirect()->route('cimsaccounts.companies')->with('success', 'Company updated.');
    }

    // ===== CHART OF ACCOUNTS =====

    public function chartIndex($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->orderBy('segment1')->orderBy(\DB::raw('COALESCE(segment2, "0000")'))->orderBy(\DB::raw('COALESCE(segment3, "0000")'))->get();
        $tree = $this->buildTree($accounts);
        $templates = AccountTemplate::all();
        $hasAccounts = $accounts->count() > 0;
        return view('cims_accounts::accounts.chart-index', compact('company', 'accounts', 'tree', 'templates', 'hasAccounts'));
    }

    private function buildTree($accounts)
    {
        $tree = [];
        foreach ($accounts as $a) {
            $item = ['id' => $a->id, 'code' => $a->account_code, 'name' => $a->account_name, 'type' => $a->account_type, 'normal_balance' => $a->normal_balance, 'vat_type' => $a->vat_type, 'level' => $a->account_level, 'is_header' => $a->is_header, 'is_system' => $a->is_system, 'is_active' => $a->is_active, 'children' => []];
            if ($a->account_level == 1) {
                $tree[$a->segment1] = $item;
            } elseif ($a->account_level == 2) {
                $key = $a->segment1 . '/' . $a->segment2;
                if (isset($tree[$a->segment1])) $tree[$a->segment1]['children'][$key] = $item;
            } elseif ($a->account_level == 3) {
                $parentKey = $a->segment1 . '/' . $a->segment2;
                if (isset($tree[$a->segment1]['children'][$parentKey])) $tree[$a->segment1]['children'][$parentKey]['children'][] = $item;
            }
        }
        return $tree;
    }

    public function chartCreate($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $account = null;
        $parents = ChartOfAccount::where('company_id', $companyId)->whereIn('account_level', [1, 2])->orderBy('account_code')->get();
        return view('cims_accounts::accounts.chart-form', compact('company', 'account', 'parents'));
    }

    public function chartStore(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $request->validate(['segment1' => 'required|string|size:4', 'account_name' => 'required|string|max:255', 'account_type' => 'required|in:asset,liability,equity,revenue,cost_of_sales,expense', 'normal_balance' => 'required|in:debit,credit', 'vat_type' => 'required|in:standard,zero_rated,exempt,none']);
        $seg1 = $request->segment1; $seg2 = $request->segment2 ?: null; $seg3 = $request->segment3 ?: null;
        $code = $seg1; $level = 1; $parentId = null;
        if ($seg2) { $code .= '/' . $seg2; $level = 2; $p = ChartOfAccount::where('company_id', $companyId)->where('segment1', $seg1)->where('account_level', 1)->first(); if ($p) $parentId = $p->id; }
        if ($seg3) {
            $code .= '/' . $seg3; $level = 3;
            $p = ChartOfAccount::where('company_id', $companyId)->where('segment1', $seg1)->where('segment2', $seg2)->where('account_level', 2)->first();
            if (!$p) {
                $l1 = ChartOfAccount::where('company_id', $companyId)->where('segment1', $seg1)->where('account_level', 1)->first();
                $p = ChartOfAccount::create([
                    'company_id' => $companyId, 'account_code' => $seg1 . '/' . $seg2,
                    'segment1' => $seg1, 'segment2' => $seg2, 'segment3' => null,
                    'account_level' => 2, 'account_name' => $request->account_name,
                    'account_type' => $request->account_type, 'normal_balance' => $request->normal_balance,
                    'vat_type' => 'none', 'is_active' => 1, 'is_system' => 0, 'is_header' => 1,
                    'description' => null, 'parent_id' => $l1 ? $l1->id : null,
                ]);
            }
            if ($p) $parentId = $p->id;
        }
        if (ChartOfAccount::where('company_id', $companyId)->where('account_code', $code)->exists()) return back()->withInput()->with('error', "Account code $code already exists.");
        ChartOfAccount::create(['company_id' => $companyId, 'account_code' => $code, 'segment1' => $seg1, 'segment2' => $seg2, 'segment3' => $seg3, 'account_level' => $level, 'account_name' => $request->account_name, 'account_type' => $request->account_type, 'normal_balance' => $request->normal_balance, 'vat_type' => $request->vat_type, 'is_active' => 1, 'is_system' => 0, 'is_header' => ($level < 3) ? 1 : 0, 'description' => $request->description, 'parent_id' => $parentId]);
        return redirect()->route('cimsaccounts.chart.index', $companyId)->with('success', "Account $code created.");
    }

    public function chartEdit($companyId, $accountId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $account = ChartOfAccount::where('company_id', $companyId)->findOrFail($accountId);
        $parents = ChartOfAccount::where('company_id', $companyId)->whereIn('account_level', [1, 2])->where('id', '!=', $accountId)->orderBy('account_code')->get();
        return view('cims_accounts::accounts.chart-form', compact('company', 'account', 'parents'));
    }

    public function chartUpdate(Request $request, $companyId, $accountId)
    {
        $account = ChartOfAccount::where('company_id', $companyId)->findOrFail($accountId);
        $request->validate(['account_name' => 'required|string|max:255', 'account_type' => 'required|in:asset,liability,equity,revenue,cost_of_sales,expense', 'normal_balance' => 'required|in:debit,credit', 'vat_type' => 'required|in:standard,zero_rated,exempt,none']);
        $account->update(['account_name' => $request->account_name, 'account_type' => $request->account_type, 'normal_balance' => $request->normal_balance, 'vat_type' => $request->vat_type, 'is_active' => $request->input('is_active', 1), 'description' => $request->description]);
        return redirect()->route('cimsaccounts.chart.index', $companyId)->with('success', "Account {$account->account_code} updated.");
    }

    public function chartDelete($companyId, $accountId)
    {
        $account = ChartOfAccount::where('company_id', $companyId)->findOrFail($accountId);
        if ($account->is_system) return back()->with('error', 'Cannot delete a system account.');
        if (JournalLine::where('account_id', $accountId)->exists()) return back()->with('error', 'Cannot delete account with posted transactions.');
        if (BankTransaction::where('allocated_account_id', $accountId)->exists()) return back()->with('error', 'Cannot delete account with allocated bank transactions.');
        if (ChartOfAccount::where('parent_id', $accountId)->exists()) return back()->with('error', 'Cannot delete account with sub-accounts.');
        $account->delete();
        return redirect()->route('cimsaccounts.chart.index', $companyId)->with('success', "Account {$account->account_code} deleted.");
    }

    public function chartQuickAdd(Request $request, $companyId)
    {
        try {
            $request->validate([
                'parent_id' => 'required|exists:cims_accounts_chart,id',
                'segment3' => 'required|string|size:4',
                'account_name' => 'required|string|max:255',
                'account_type' => 'required|in:asset,liability,equity,revenue,cost_of_sales,expense',
                'normal_balance' => 'required|in:debit,credit',
                'vat_type' => 'required|in:standard,zero_rated,exempt,none',
            ]);
            $parent = ChartOfAccount::where('company_id', $companyId)->findOrFail($request->parent_id);
            if ($parent->account_level != 2) return response()->json(['success' => false, 'error' => 'Parent must be a level 2 account.'], 422);
            $code = $parent->segment1 . '/' . $parent->segment2 . '/' . $request->segment3;
            if (ChartOfAccount::where('company_id', $companyId)->where('account_code', $code)->exists()) {
                return response()->json(['success' => false, 'error' => "Account code $code already exists."], 422);
            }
            $account = ChartOfAccount::create([
                'company_id' => $companyId, 'account_code' => $code,
                'segment1' => $parent->segment1, 'segment2' => $parent->segment2, 'segment3' => $request->segment3,
                'account_level' => 3, 'account_name' => $request->account_name,
                'account_type' => $request->account_type, 'normal_balance' => $request->normal_balance,
                'vat_type' => $request->vat_type, 'is_active' => 1, 'is_system' => 0, 'is_header' => 0,
                'description' => $request->description, 'parent_id' => $parent->id,
            ]);
            return response()->json(['success' => true, 'account' => ['id' => $account->id, 'name' => $account->account_name, 'vat' => $account->vat_type, 'code' => $code]]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'error' => implode(' ', $e->validator->errors()->all())], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    public function chartSeed(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $request->validate(['template_id' => 'required|exists:cims_accounts_templates,id']);
        if (ChartOfAccount::where('company_id', $companyId)->count() > 0) return back()->with('error', 'Company already has accounts.');
        $items = AccountTemplateItem::where('template_id', $request->template_id)->orderBy('account_code')->get();
        $parentMap = []; $count = 0;
        foreach ($items as $item) {
            $parentId = null;
            if ($item->account_level == 2 && isset($parentMap[$item->segment1])) $parentId = $parentMap[$item->segment1];
            elseif ($item->account_level == 3 && isset($parentMap[$item->segment1 . '/' . $item->segment2])) $parentId = $parentMap[$item->segment1 . '/' . $item->segment2];
            $acc = ChartOfAccount::create(['company_id' => $companyId, 'account_code' => $item->account_code, 'segment1' => $item->segment1, 'segment2' => $item->segment2, 'segment3' => $item->segment3, 'account_level' => $item->account_level, 'account_name' => $item->account_name, 'account_type' => $item->account_type, 'normal_balance' => $item->normal_balance, 'vat_type' => $item->vat_type, 'is_active' => 1, 'is_system' => $item->is_system, 'is_header' => $item->is_header, 'description' => $item->description, 'parent_id' => $parentId]);
            if ($item->account_level == 1) $parentMap[$item->segment1] = $acc->id;
            elseif ($item->account_level == 2) $parentMap[$item->segment1 . '/' . $item->segment2] = $acc->id;
            $count++;
        }
        return redirect()->route('cimsaccounts.chart.index', $companyId)->with('success', "Seeded $count accounts from template.");
    }

    // ===== BANK ACCOUNTS =====

    public function bankAccounts($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccounts = BankAccount::where('company_id', $companyId)->with('glAccount')->get();
        foreach ($bankAccounts as $ba) {
            $ba->unallocated_count = BankTransaction::where('bank_account_id', $ba->id)->where('status', 'unallocated')->count();
            $ba->total_transactions = BankTransaction::where('bank_account_id', $ba->id)->count();
        }
        return view('cims_accounts::accounts.bank-accounts', compact('company', 'bankAccounts'));
    }

    public function bankAccountCreate($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankGlAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('account_type', 'asset')
            ->where('account_name', 'LIKE', '%Bank%')
            ->orWhere(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->where('account_level', 3)->where('segment1', '1000')->where('segment2', '1000')->whereIn('segment3', ['0010', '0020', '0030']);
            })
            ->orderBy('account_code')->get();
        if ($bankGlAccounts->isEmpty()) {
            $bankGlAccounts = ChartOfAccount::where('company_id', $companyId)->where('account_level', 3)->where('account_type', 'asset')->orderBy('account_code')->get();
        }
        $bankNames = \DB::table('cims_bank_names')->where('is_active', 1)->orderBy('bank_name')->get();
        $accountTypes = \DB::table('cims_bank_account_types')->where('is_active', 1)->orderBy('bank_account_type')->get();
        return view('cims_accounts::accounts.bank-account-form', compact('company', 'bankGlAccounts', 'bankNames', 'accountTypes'));
    }

    public function bankAccountStore(Request $request, $companyId)
    {
        $request->validate(['account_id' => 'required|exists:cims_accounts_chart,id', 'bank_name' => 'required|string|max:100', 'account_number' => 'required|string|max:50']);
        BankAccount::create(['company_id' => $companyId, 'account_id' => $request->account_id, 'bank_name' => $request->bank_name, 'account_number' => $request->account_number, 'branch_code' => $request->branch_code, 'account_type' => $request->account_type ?? 'cheque', 'is_active' => 1]);
        return redirect()->route('cimsaccounts.banks.index', $companyId)->with('success', 'Bank account linked.');
    }

    // ===== BANK IMPORT =====

    public function bankImport($companyId, $bankId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccount = BankAccount::where('company_id', $companyId)->findOrFail($bankId);
        return view('cims_accounts::accounts.bank-import', compact('company', 'bankAccount'));
    }

    public function bankImportProcess(Request $request, $companyId, $bankId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccount = BankAccount::where('company_id', $companyId)->findOrFail($bankId);

        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $dateCol = $request->input('date_col', 0);
        $descCol = $request->input('desc_col', 1);
        $amountCol = $request->input('amount_col', 2);
        $balanceCol = $request->input('balance_col', -1);

        $batchRef = 'IMP-' . date('YmdHis');
        $now = now();
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;
            $rawDate = trim($row[$dateCol] ?? '');
            $desc = trim($row[$descCol] ?? '');
            $rawAmount = trim(str_replace([' ', ',', 'R'], '', $row[$amountCol] ?? '0'));
            $amount = floatval($rawAmount);
            if (empty($desc) || $amount == 0) continue;

            $txnDate = null;
            foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd M Y', 'j M Y'] as $fmt) {
                $d = \DateTime::createFromFormat($fmt, $rawDate);
                if ($d) { $txnDate = $d->format('Y-m-d'); break; }
            }
            if (!$txnDate) continue;

            $direction = $amount >= 0 ? 'credit' : 'debit';
            $absAmount = abs($amount);
            $balance = ($balanceCol >= 0 && isset($row[$balanceCol])) ? floatval(str_replace([' ', ',', 'R'], '', $row[$balanceCol])) : null;

            BankTransaction::create([
                'company_id' => $companyId,
                'bank_account_id' => $bankId,
                'transaction_date' => $txnDate,
                'description' => $desc,
                'amount' => $absAmount,
                'direction' => $direction,
                'balance' => $balance,
                'status' => 'unallocated',
                'batch_ref' => $batchRef,
                'imported_at' => $now,
            ]);
            $count++;
        }
        fclose($handle);

        return redirect()->route('cimsaccounts.allocate', $companyId)->with('success', "Imported $count transactions from bank statement. Now allocate them.");
    }

    // ===== BANK ALLOCATION =====

    public function bankAllocate($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $transactions = BankTransaction::where('company_id', $companyId)
            ->whereIn('status', ['unallocated', 'allocated'])
            ->with(['bankAccount.glAccount', 'allocatedAccount'])
            ->orderBy('transaction_date')
            ->get();

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)->where('is_header', 0)
            ->orderBy('account_name')->get();

        $rules = AllocationRule::where('company_id', $companyId)
            ->where('is_active', 1)->with('account')
            ->orderBy('priority', 'desc')->get();

        foreach ($transactions as $txn) {
            if ($txn->status === 'unallocated' && !$txn->allocated_account_id) {
                $suggested = $this->suggestAccount($rules, $txn->description);
                $txn->suggested_account_id = $suggested ? $suggested->account_id : null;
                $txn->suggested_account_name = $suggested ? $suggested->account->account_name : null;
                $txn->suggested_vat_type = $suggested ? $suggested->vat_type : null;
            }
        }

        $parentAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->where('is_active', 1)
            ->orderBy('account_name')->get();

        return view('cims_accounts::accounts.bank-allocate', compact('company', 'transactions', 'accounts', 'rules', 'parentAccounts'));
    }

    private function suggestAccount($rules, $description)
    {
        $desc = strtolower($description);
        foreach ($rules as $rule) {
            if (strpos($desc, strtolower($rule->keyword)) !== false) {
                return $rule;
            }
        }
        return null;
    }

    public function bankAllocateSave(Request $request, $companyId)
    {
        $allocations = $request->input('allocations', []);
        $count = 0;
        foreach ($allocations as $txnId => $data) {
            $accountId = $data['account_id'] ?? null;
            if (!$accountId) continue;
            $txn = BankTransaction::where('company_id', $companyId)->find($txnId);
            if (!$txn || $txn->status === 'posted') continue;

            $account = ChartOfAccount::find($accountId);
            $vatType = $data['vat_type'] ?? ($account ? $account->vat_type : 'none');
            $absAmount = abs($txn->amount);
            $vatAmount = 0;
            $netAmount = $absAmount;
            if ($vatType === 'standard') {
                $vatAmount = round($absAmount - ($absAmount / 1.15), 2);
                $netAmount = $absAmount - $vatAmount;
            }

            $txn->update([
                'allocated_account_id' => $accountId,
                'vat_type' => $vatType,
                'vat_amount' => $vatAmount,
                'net_amount' => $netAmount,
                'status' => 'allocated',
            ]);

            if (!empty($data['save_rule']) && !empty($data['rule_keyword'])) {
                $keyword = trim($data['rule_keyword']);
                if ($keyword) {
                    AllocationRule::updateOrCreate(
                        ['company_id' => $companyId, 'keyword' => $keyword],
                        ['account_id' => $accountId, 'vat_type' => $vatType, 'is_active' => 1]
                    );
                }
            }

            $count++;
        }
        return redirect()->route('cimsaccounts.allocate', $companyId)->with('success', "Allocated $count transactions.");
    }

    public function bankAllocatePost(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $transactions = BankTransaction::where('company_id', $companyId)
            ->where('status', 'allocated')
            ->with(['bankAccount.glAccount', 'allocatedAccount'])
            ->get();

        if ($transactions->isEmpty()) return back()->with('error', 'No allocated transactions to post.');

        $posted = 0;
        foreach ($transactions as $txn) {
            $bankGlId = $txn->bankAccount->account_id;
            $contraId = $txn->allocated_account_id;
            $absAmount = abs($txn->amount);

            $lastJournal = Journal::where('company_id', $companyId)->orderBy('id', 'desc')->first();
            $nextNum = $lastJournal ? intval(substr($lastJournal->journal_number, 3)) + 1 : 1;
            $journalNumber = 'JNL' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $period = \DB::table('cims_master_periods')
                ->where('is_active', 1)
                ->whereRaw("SUBSTRING(period_combo, 3, 2) = ?", [str_pad($txn->transaction_date->month, 2, '0', STR_PAD_LEFT)])
                ->first();

            $journal = Journal::create([
                'company_id' => $companyId,
                'journal_number' => $journalNumber,
                'journal_date' => $txn->transaction_date,
                'period_id' => $period ? $period->id : null,
                'reference' => $txn->reference ?: $txn->batch_ref,
                'description' => $txn->description,
                'source' => 'bank_import',
                'status' => 'posted',
                'total_debit' => $absAmount,
                'total_credit' => $absAmount,
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $lineOrder = 1;
            if ($txn->direction === 'debit') {
                $netAmount = $txn->net_amount ?: $absAmount;
                $vatAmount = $txn->vat_amount ?: 0;
                JournalLine::create(['journal_id' => $journal->id, 'account_id' => $contraId, 'description' => $txn->description, 'debit_amount' => $netAmount, 'credit_amount' => 0, 'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder++]);
                if ($vatAmount > 0) {
                    $vatInput = ChartOfAccount::where('company_id', $companyId)->where('account_name', 'VAT Input')->where('account_level', 3)->first();
                    if ($vatInput) JournalLine::create(['journal_id' => $journal->id, 'account_id' => $vatInput->id, 'description' => 'VAT Input - ' . $txn->description, 'debit_amount' => $vatAmount, 'credit_amount' => 0, 'vat_amount' => $vatAmount, 'vat_type' => 'standard', 'line_order' => $lineOrder++]);
                }
                JournalLine::create(['journal_id' => $journal->id, 'account_id' => $bankGlId, 'description' => $txn->description, 'debit_amount' => 0, 'credit_amount' => $absAmount, 'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder]);
            } else {
                JournalLine::create(['journal_id' => $journal->id, 'account_id' => $bankGlId, 'description' => $txn->description, 'debit_amount' => $absAmount, 'credit_amount' => 0, 'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder++]);
                $netAmount = $txn->net_amount ?: $absAmount;
                $vatAmount = $txn->vat_amount ?: 0;
                if ($vatAmount > 0) {
                    $vatOutput = ChartOfAccount::where('company_id', $companyId)->where('account_name', 'VAT Output')->where('account_level', 3)->first();
                    if ($vatOutput) JournalLine::create(['journal_id' => $journal->id, 'account_id' => $vatOutput->id, 'description' => 'VAT Output - ' . $txn->description, 'debit_amount' => 0, 'credit_amount' => $vatAmount, 'vat_amount' => $vatAmount, 'vat_type' => 'standard', 'line_order' => $lineOrder++]);
                }
                JournalLine::create(['journal_id' => $journal->id, 'account_id' => $contraId, 'description' => $txn->description, 'debit_amount' => 0, 'credit_amount' => $netAmount, 'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder]);
            }

            $txn->update(['status' => 'posted', 'journal_id' => $journal->id]);
            $posted++;
        }

        return redirect()->route('cimsaccounts.journals.index', $companyId)->with('success', "Posted $posted journal entries from bank transactions.");
    }

    public function bankExclude(Request $request, $companyId, $txnId)
    {
        $txn = BankTransaction::where('company_id', $companyId)->findOrFail($txnId);
        $txn->update(['status' => 'excluded']);
        return response()->json(['success' => true]);
    }

    // ===== JOURNALS =====

    public function journalIndex($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $journals = Journal::where('company_id', $companyId)
            ->orderBy('journal_date', 'desc')->orderBy('id', 'desc')
            ->paginate(30);
        return view('cims_accounts::accounts.journal-index', compact('company', 'journals'));
    }

    public function journalCreate($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)
            ->orderBy('account_name')->get();
        return view('cims_accounts::accounts.journal-form', compact('company', 'accounts'));
    }

    public function journalStore(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $lines = $request->input('lines', []);
        if (count($lines) < 2) return back()->withInput()->with('error', 'At least 2 lines required.');

        $totalDebit = 0; $totalCredit = 0;
        foreach ($lines as $line) {
            $totalDebit += floatval($line['debit'] ?? 0);
            $totalCredit += floatval($line['credit'] ?? 0);
        }
        if (round($totalDebit, 2) !== round($totalCredit, 2)) return back()->withInput()->with('error', 'Total debits (R' . number_format($totalDebit, 2) . ') must equal total credits (R' . number_format($totalCredit, 2) . ').');

        $lastJournal = Journal::where('company_id', $companyId)->orderBy('id', 'desc')->first();
        $nextNum = $lastJournal ? intval(substr($lastJournal->journal_number, 3)) + 1 : 1;
        $journalNumber = 'JNL' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

        $journal = Journal::create([
            'company_id' => $companyId,
            'journal_number' => $journalNumber,
            'journal_date' => $request->journal_date ?: now()->toDateString(),
            'reference' => $request->reference,
            'description' => $request->description,
            'source' => 'manual',
            'status' => 'draft',
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'created_by' => auth()->id(),
        ]);

        $order = 1;
        foreach ($lines as $line) {
            $accountId = $line['account_id'] ?? null;
            if (!$accountId) continue;
            JournalLine::create([
                'journal_id' => $journal->id,
                'account_id' => $accountId,
                'description' => $line['description'] ?? '',
                'debit_amount' => floatval($line['debit'] ?? 0),
                'credit_amount' => floatval($line['credit'] ?? 0),
                'vat_amount' => floatval($line['vat'] ?? 0),
                'vat_type' => $line['vat_type'] ?? 'none',
                'line_order' => $order++,
            ]);
        }

        return redirect()->route('cimsaccounts.journals.show', [$companyId, $journal->id])->with('success', "Journal $journalNumber created as draft.");
    }

    public function journalShow($companyId, $journalId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $journal = Journal::where('company_id', $companyId)->with('lines.account')->findOrFail($journalId);
        return view('cims_accounts::accounts.journal-show', compact('company', 'journal'));
    }

    public function journalPost(Request $request, $companyId, $journalId)
    {
        $journal = Journal::where('company_id', $companyId)->findOrFail($journalId);
        if ($journal->status !== 'draft') return back()->with('error', 'Only draft journals can be posted.');
        if (round($journal->total_debit, 2) !== round($journal->total_credit, 2)) return back()->with('error', 'Journal does not balance.');
        $journal->update(['status' => 'posted', 'posted_by' => auth()->id(), 'posted_at' => now()]);
        return back()->with('success', "Journal {$journal->journal_number} posted.");
    }

    public function journalReverse(Request $request, $companyId, $journalId)
    {
        $journal = Journal::where('company_id', $companyId)->with('lines')->findOrFail($journalId);
        if ($journal->status !== 'posted') return back()->with('error', 'Only posted journals can be reversed.');

        $lastJournal = Journal::where('company_id', $companyId)->orderBy('id', 'desc')->first();
        $nextNum = $lastJournal ? intval(substr($lastJournal->journal_number, 3)) + 1 : 1;
        $revNumber = 'JNL' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

        $reversal = Journal::create([
            'company_id' => $companyId, 'journal_number' => $revNumber,
            'journal_date' => now()->toDateString(), 'reference' => 'REV-' . $journal->journal_number,
            'description' => 'Reversal of ' . $journal->journal_number, 'source' => 'system',
            'status' => 'posted', 'total_debit' => $journal->total_credit, 'total_credit' => $journal->total_debit,
            'reversal_of' => $journal->id, 'created_by' => auth()->id(), 'posted_by' => auth()->id(), 'posted_at' => now(),
        ]);

        foreach ($journal->lines as $line) {
            JournalLine::create(['journal_id' => $reversal->id, 'account_id' => $line->account_id, 'description' => 'Reversal - ' . $line->description, 'debit_amount' => $line->credit_amount, 'credit_amount' => $line->debit_amount, 'vat_amount' => $line->vat_amount, 'vat_type' => $line->vat_type, 'line_order' => $line->line_order]);
        }

        $journal->update(['status' => 'reversed']);
        return redirect()->route('cimsaccounts.journals.show', [$companyId, $reversal->id])->with('success', "Journal reversed. New journal: $revNumber");
    }

    // ===== ALLOCATION RULES =====

    public function allocationRules($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $rules = AllocationRule::where('company_id', $companyId)->with('account')->orderBy('keyword')->get();
        $accounts = ChartOfAccount::where('company_id', $companyId)->where('account_level', 3)->where('is_active', 1)->orderBy('account_name')->get();
        return view('cims_accounts::accounts.allocation-rules', compact('company', 'rules', 'accounts'));
    }

    public function allocationRuleStore(Request $request, $companyId)
    {
        $request->validate(['keyword' => 'required|string|max:255', 'account_id' => 'required|exists:cims_accounts_chart,id']);
        AllocationRule::updateOrCreate(
            ['company_id' => $companyId, 'keyword' => strtolower(trim($request->keyword))],
            ['account_id' => $request->account_id, 'vat_type' => $request->vat_type ?? 'standard', 'is_active' => 1]
        );
        return back()->with('success', 'Rule saved.');
    }

    public function allocationRuleDelete($companyId, $ruleId)
    {
        AllocationRule::where('company_id', $companyId)->where('id', $ruleId)->delete();
        return back()->with('success', 'Rule deleted.');
    }

    // ===== REPORTS =====

    public function profitAndLoss(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $period = $request->input('period', 'this_year');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $now = \Carbon\Carbon::now();
        $fyStart = $now->month >= 3 ? $now->copy()->startOfYear()->addMonths(2) : $now->copy()->subYear()->startOfYear()->addMonths(2);

        if ($period !== 'custom' || !$dateFrom || !$dateTo) {
            switch ($period) {
                case 'this_month':
                    $dateFrom = $now->copy()->startOfMonth()->toDateString();
                    $dateTo = $now->copy()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $dateFrom = $now->copy()->subMonth()->startOfMonth()->toDateString();
                    $dateTo = $now->copy()->subMonth()->endOfMonth()->toDateString();
                    break;
                case 'q1':
                    $dateFrom = $fyStart->copy()->toDateString();
                    $dateTo = $fyStart->copy()->addMonths(2)->endOfMonth()->toDateString();
                    break;
                case 'q2':
                    $dateFrom = $fyStart->copy()->addMonths(3)->toDateString();
                    $dateTo = $fyStart->copy()->addMonths(5)->endOfMonth()->toDateString();
                    break;
                case 'q3':
                    $dateFrom = $fyStart->copy()->addMonths(6)->toDateString();
                    $dateTo = $fyStart->copy()->addMonths(8)->endOfMonth()->toDateString();
                    break;
                case 'q4':
                    $dateFrom = $fyStart->copy()->addMonths(9)->toDateString();
                    $dateTo = $fyStart->copy()->addMonths(11)->endOfMonth()->toDateString();
                    break;
                case '6_months':
                    $dateFrom = $now->copy()->subMonths(5)->startOfMonth()->toDateString();
                    $dateTo = $now->copy()->endOfMonth()->toDateString();
                    break;
                case 'last_year':
                    $prevFy = $fyStart->copy()->subYear();
                    $dateFrom = $prevFy->toDateString();
                    $dateTo = $prevFy->copy()->addMonths(11)->endOfMonth()->toDateString();
                    break;
                case 'all':
                    $earliest = \DB::table('cims_accounts_journals')
                        ->where('company_id', $companyId)->where('status', 'posted')
                        ->min('journal_date');
                    $latest = \DB::table('cims_accounts_journals')
                        ->where('company_id', $companyId)->where('status', 'posted')
                        ->max('journal_date');
                    if ($earliest && $latest) {
                        $dateFrom = \Carbon\Carbon::parse($earliest)->startOfMonth()->toDateString();
                        $dateTo = \Carbon\Carbon::parse($latest)->endOfMonth()->toDateString();
                    } else {
                        $dateFrom = $fyStart->toDateString();
                        $dateTo = $fyStart->copy()->addMonths(11)->endOfMonth()->toDateString();
                    }
                    break;
                case 'this_year':
                default:
                    $dateFrom = $fyStart->toDateString();
                    $dateTo = $fyStart->copy()->addMonths(11)->endOfMonth()->toDateString();
                    break;
            }
        }

        $from = \Carbon\Carbon::parse($dateFrom);
        $to = \Carbon\Carbon::parse($dateTo);
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
            ->select(
                'c.id as account_id', 'c.account_code', 'c.account_name',
                'c.account_type', 'c.parent_id',
                \DB::raw("DATE_FORMAT(j.journal_date, '%Y-%m') as month_key"),
                \DB::raw('SUM(jl.debit_amount) as total_debit'),
                \DB::raw('SUM(jl.credit_amount) as total_credit')
            )
            ->groupBy('c.id', 'c.account_code', 'c.account_name', 'c.account_type', 'c.parent_id', \DB::raw("DATE_FORMAT(j.journal_date, '%Y-%m')"))
            ->orderBy('c.account_code')
            ->get();

        $parentNames = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->pluck('account_name', 'id');

        $accounts = [];
        foreach ($lines as $line) {
            $key = $line->account_id;
            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'id' => $line->account_id,
                    'name' => $line->account_name, 'code' => $line->account_code,
                    'type' => $line->account_type, 'group' => $parentNames[$line->parent_id] ?? 'Other',
                    'months' => array_fill_keys($months, 0), 'total' => 0,
                ];
            }
            $bal = 0;
            if ($line->account_type === 'revenue') {
                $bal = $line->total_credit - $line->total_debit;
            } else {
                $bal = $line->total_debit - $line->total_credit;
            }
            if (isset($accounts[$key]['months'][$line->month_key])) {
                $accounts[$key]['months'][$line->month_key] += $bal;
            }
            $accounts[$key]['total'] += $bal;
        }

        $revenue = $cos = $expenses = [];
        foreach ($accounts as $acc) {
            if ($acc['type'] === 'revenue') $revenue[] = $acc;
            elseif ($acc['type'] === 'cost_of_sales') $cos[] = $acc;
            elseif ($acc['type'] === 'expense') $expenses[] = $acc;
        }

        $monthTotals = ['revenue' => array_fill_keys($months, 0), 'cos' => array_fill_keys($months, 0), 'expense' => array_fill_keys($months, 0)];
        foreach ($revenue as $r) { foreach ($months as $m) $monthTotals['revenue'][$m] += $r['months'][$m]; }
        foreach ($cos as $c) { foreach ($months as $m) $monthTotals['cos'][$m] += $c['months'][$m]; }
        foreach ($expenses as $e) { foreach ($months as $m) $monthTotals['expense'][$m] += $e['months'][$m]; }

        $totalRevenue = array_sum($monthTotals['revenue']);
        $totalCos = array_sum($monthTotals['cos']);
        $totalExpenses = array_sum($monthTotals['expense']);
        $grossProfit = $totalRevenue - $totalCos;
        $netProfit = $grossProfit - $totalExpenses;

        $revenueByGroup = collect($revenue)->groupBy('group');
        $cosByGroup = collect($cos)->groupBy('group');
        $expensesByGroup = collect($expenses)->groupBy('group');

        $allAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)->where('is_header', 0)
            ->orderBy('account_name')->get();

        $dataHint = null;
        if ($totalRevenue == 0 && $totalCos == 0 && $totalExpenses == 0) {
            $earliest = \DB::table('cims_accounts_journals')
                ->where('company_id', $companyId)->where('status', 'posted')
                ->min('journal_date');
            $latest = \DB::table('cims_accounts_journals')
                ->where('company_id', $companyId)->where('status', 'posted')
                ->max('journal_date');
            if ($earliest && $latest) {
                $dataHint = 'Posted journals exist from ' . \Carbon\Carbon::parse($earliest)->format('j M Y') . ' to ' . \Carbon\Carbon::parse($latest)->format('j M Y') . '. Try adjusting the period or use Custom dates.';
            }
        }

        return view('cims_accounts::accounts.report-pnl', compact(
            'company', 'period', 'dateFrom', 'dateTo', 'months', 'monthTotals',
            'revenueByGroup', 'cosByGroup', 'expensesByGroup',
            'totalRevenue', 'totalCos', 'grossProfit', 'totalExpenses', 'netProfit',
            'dataHint', 'allAccounts'
        ));
    }

    private function resolvePeriodDates($period, $dateFrom, $dateTo, $fyMonth = 3, $companyId = null)
    {
        $now = \Carbon\Carbon::now();
        $fyStart = $now->month >= $fyMonth
            ? $now->copy()->startOfYear()->addMonths($fyMonth - 1)
            : $now->copy()->subYear()->startOfYear()->addMonths($fyMonth - 1);

        if ($period === 'custom' && $dateFrom && $dateTo) {
            return [$dateFrom, $dateTo];
        }

        switch ($period) {
            case 'this_month':
                return [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()];
            case 'last_month':
                return [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()];
            case 'q1':
                return [$fyStart->copy()->toDateString(), $fyStart->copy()->addMonths(2)->endOfMonth()->toDateString()];
            case 'q2':
                return [$fyStart->copy()->addMonths(3)->toDateString(), $fyStart->copy()->addMonths(5)->endOfMonth()->toDateString()];
            case 'q3':
                return [$fyStart->copy()->addMonths(6)->toDateString(), $fyStart->copy()->addMonths(8)->endOfMonth()->toDateString()];
            case 'q4':
                return [$fyStart->copy()->addMonths(9)->toDateString(), $fyStart->copy()->addMonths(11)->endOfMonth()->toDateString()];
            case '6_months':
                return [$now->copy()->subMonths(5)->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()];
            case 'last_year':
                $prevFy = $fyStart->copy()->subYear();
                return [$prevFy->toDateString(), $prevFy->copy()->addMonths(11)->endOfMonth()->toDateString()];
            case 'all':
                if ($companyId) {
                    $earliest = \DB::table('cims_accounts_journals')
                        ->where('company_id', $companyId)->where('status', 'posted')
                        ->min('journal_date');
                    $latest = \DB::table('cims_accounts_journals')
                        ->where('company_id', $companyId)->where('status', 'posted')
                        ->max('journal_date');
                    if ($earliest && $latest) {
                        return [\Carbon\Carbon::parse($earliest)->startOfMonth()->toDateString(), \Carbon\Carbon::parse($latest)->endOfMonth()->toDateString()];
                    }
                }
                return [$fyStart->toDateString(), $fyStart->copy()->addMonths(11)->endOfMonth()->toDateString()];
            case 'this_year':
            default:
                return [$fyStart->toDateString(), $fyStart->copy()->addMonths(11)->endOfMonth()->toDateString()];
        }
    }

    public function trialBalance(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $period = $request->input('period', 'this_year');
        $fyMonth = $company->financial_year_start ?: 3;

        list($dateFrom, $dateTo) = $this->resolvePeriodDates(
            $period, $request->input('date_from'), $request->input('date_to'), $fyMonth, $companyId
        );

        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
            ->select(
                'c.id as account_id', 'c.account_code', 'c.account_name',
                'c.account_type', 'c.normal_balance', 'c.parent_id', 'c.segment1',
                \DB::raw('SUM(jl.debit_amount) as total_debit'),
                \DB::raw('SUM(jl.credit_amount) as total_credit')
            )
            ->groupBy('c.id', 'c.account_code', 'c.account_name', 'c.account_type', 'c.normal_balance', 'c.parent_id', 'c.segment1')
            ->orderBy('c.account_code')
            ->get();

        $level1Names = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 1)->pluck('account_name', 'segment1');
        $level2Names = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->pluck('account_name', 'id');

        $typeLabels = [
            'asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity',
            'revenue' => 'Revenue', 'cost_of_sales' => 'Cost of Sales', 'expense' => 'Expenses',
        ];
        $typeOrder = ['asset', 'liability', 'equity', 'revenue', 'cost_of_sales', 'expense'];

        $accounts = [];
        $grandDebit = 0;
        $grandCredit = 0;

        foreach ($lines as $line) {
            $dr = (float)$line->total_debit;
            $cr = (float)$line->total_credit;
            $net = $dr - $cr;
            $balDebit = $net > 0 ? $net : 0;
            $balCredit = $net < 0 ? abs($net) : 0;

            $grandDebit += $balDebit;
            $grandCredit += $balCredit;

            $accounts[] = [
                'id' => $line->account_id,
                'code' => $line->account_code,
                'name' => $line->account_name,
                'type' => $line->account_type,
                'type_label' => $typeLabels[$line->account_type] ?? $line->account_type,
                'group' => $level2Names[$line->parent_id] ?? 'Other',
                'segment1' => $line->segment1,
                'movement_debit' => $dr,
                'movement_credit' => $cr,
                'balance_debit' => $balDebit,
                'balance_credit' => $balCredit,
            ];
        }

        $byType = collect($accounts)->groupBy('type');
        $orderedSections = [];
        foreach ($typeOrder as $type) {
            if ($byType->has($type)) {
                $section = $byType[$type];
                $secDr = $section->sum('balance_debit');
                $secCr = $section->sum('balance_credit');
                $orderedSections[] = [
                    'type' => $type,
                    'label' => $typeLabels[$type],
                    'accounts' => $section->groupBy('group'),
                    'total_debit' => $secDr,
                    'total_credit' => $secCr,
                ];
            }
        }

        $balanced = abs(round($grandDebit, 2) - round($grandCredit, 2)) < 0.01;

        $dataHint = null;
        if (empty($accounts)) {
            $earliest = \DB::table('cims_accounts_journals')
                ->where('company_id', $companyId)->where('status', 'posted')->min('journal_date');
            $latest = \DB::table('cims_accounts_journals')
                ->where('company_id', $companyId)->where('status', 'posted')->max('journal_date');
            if ($earliest && $latest) {
                $dataHint = 'Posted journals exist from ' . \Carbon\Carbon::parse($earliest)->format('j M Y') . ' to ' . \Carbon\Carbon::parse($latest)->format('j M Y') . '. Try adjusting the date range.';
            }
        }

        $allAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)->where('is_header', 0)
            ->orderBy('account_name')->get();

        return view('cims_accounts::accounts.report-tb', compact(
            'company', 'period', 'dateFrom', 'dateTo', 'orderedSections',
            'grandDebit', 'grandCredit', 'balanced', 'dataHint', 'allAccounts'
        ));
    }

    public function balanceSheet(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $period = $request->input('period', 'this_year');
        $fyMonth = $company->financial_year_start ?: 3;

        list($dateFrom, $dateTo) = $this->resolvePeriodDates(
            $period, $request->input('date_from'), $request->input('date_to'), $fyMonth, $companyId
        );
        $asAt = $dateTo;

        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('j.journal_date', '<=', $asAt)
            ->whereIn('c.account_type', ['asset', 'liability', 'equity'])
            ->select(
                'c.id as account_id', 'c.account_code', 'c.account_name',
                'c.account_type', 'c.normal_balance', 'c.parent_id',
                \DB::raw('SUM(jl.debit_amount) as total_debit'),
                \DB::raw('SUM(jl.credit_amount) as total_credit')
            )
            ->groupBy('c.id', 'c.account_code', 'c.account_name', 'c.account_type', 'c.normal_balance', 'c.parent_id')
            ->orderBy('c.account_code')
            ->get();

        $pnlLines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('j.journal_date', '<=', $asAt)
            ->whereIn('c.account_type', ['revenue', 'cost_of_sales', 'expense'])
            ->select(
                \DB::raw('SUM(jl.debit_amount) as total_debit'),
                \DB::raw('SUM(jl.credit_amount) as total_credit')
            )
            ->first();

        $retainedEarnings = 0;
        if ($pnlLines) {
            $revenue = (float)$pnlLines->total_credit - (float)$pnlLines->total_debit;
            $retainedEarnings = $revenue;
        }

        $level2Names = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->pluck('account_name', 'id');

        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        foreach ($lines as $line) {
            $dr = (float)$line->total_debit;
            $cr = (float)$line->total_credit;

            if ($line->account_type === 'asset') {
                $bal = $dr - $cr;
            } else {
                $bal = $cr - $dr;
            }

            if (abs($bal) < 0.01) continue;

            $item = [
                'id' => $line->account_id,
                'code' => $line->account_code,
                'name' => $line->account_name,
                'type' => $line->account_type,
                'group' => $level2Names[$line->parent_id] ?? 'Other',
                'balance' => $bal,
            ];

            if ($line->account_type === 'asset') {
                $assets[] = $item;
                $totalAssets += $bal;
            } elseif ($line->account_type === 'liability') {
                $liabilities[] = $item;
                $totalLiabilities += $bal;
            } elseif ($line->account_type === 'equity') {
                $equity[] = $item;
                $totalEquity += $bal;
            }
        }

        $totalEquity += $retainedEarnings;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $balanced = abs(round($totalAssets, 2) - round($totalLiabilitiesAndEquity, 2)) < 0.01;

        $assetsByGroup = collect($assets)->groupBy('group');
        $liabilitiesByGroup = collect($liabilities)->groupBy('group');
        $equityByGroup = collect($equity)->groupBy('group');

        $dataHint = null;
        if ($totalAssets == 0 && $totalLiabilities == 0 && $totalEquity == 0 && $retainedEarnings == 0) {
            $earliest = \DB::table('cims_accounts_journals')
                ->where('company_id', $companyId)->where('status', 'posted')->min('journal_date');
            $latest = \DB::table('cims_accounts_journals')
                ->where('company_id', $companyId)->where('status', 'posted')->max('journal_date');
            if ($earliest && $latest) {
                $dataHint = 'Posted journals exist from ' . \Carbon\Carbon::parse($earliest)->format('j M Y') . ' to ' . \Carbon\Carbon::parse($latest)->format('j M Y') . '. Try adjusting the date.';
            }
        }

        $allAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)->where('is_header', 0)
            ->orderBy('account_name')->get();

        return view('cims_accounts::accounts.report-bs', compact(
            'company', 'period', 'dateFrom', 'asAt', 'assetsByGroup', 'liabilitiesByGroup', 'equityByGroup',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'retainedEarnings',
            'totalLiabilitiesAndEquity', 'balanced', 'dataHint', 'allAccounts'
        ));
    }

    public function generalLedger(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $period = $request->input('period', 'this_year');
        $fyMonth = $company->financial_year_start ?: 3;
        list($dateFrom, $dateTo) = $this->resolvePeriodDates($period, $request->input('date_from'), $request->input('date_to'), $fyMonth, $companyId);

        $accountId = $request->input('account_id');

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)->where('is_header', 0)
            ->orderBy('account_name')->get();

        $level1 = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 1)->orderBy('account_code')->get()->keyBy('id');
        $level2 = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->orderBy('account_code')->get();

        $groupedAccounts = [];
        $l2ToL1 = [];
        foreach ($level2 as $l2) {
            if ($l2->parent_id && $level1->has($l2->parent_id)) {
                $l2ToL1[$l2->id] = $l2->parent_id;
            }
        }
        foreach ($accounts as $acc) {
            $l1Name = 'Other';
            if ($acc->parent_id && isset($l2ToL1[$acc->parent_id])) {
                $l1Name = $level1[$l2ToL1[$acc->parent_id]]->account_name;
            } elseif ($acc->parent_id && $level1->has($acc->parent_id)) {
                $l1Name = $level1[$acc->parent_id]->account_name;
            }
            $groupedAccounts[$l1Name][] = $acc;
        }

        $transactions = collect();
        $openingBalance = 0;
        $selectedAccount = null;
        $allAccounts = $accounts;

        if ($accountId) {
            $selectedAccount = ChartOfAccount::find($accountId);

            $openingQuery = \DB::table('cims_accounts_journal_lines as jl')
                ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
                ->where('j.company_id', $companyId)
                ->where('j.status', 'posted')
                ->where('jl.account_id', $accountId)
                ->where('j.journal_date', '<', $dateFrom)
                ->select(
                    \DB::raw('COALESCE(SUM(jl.debit_amount), 0) as total_debit'),
                    \DB::raw('COALESCE(SUM(jl.credit_amount), 0) as total_credit')
                )->first();

            if ($openingQuery) {
                $openingBalance = (float)$openingQuery->total_debit - (float)$openingQuery->total_credit;
            }

            $transactions = \DB::table('cims_accounts_journal_lines as jl')
                ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
                ->where('j.company_id', $companyId)
                ->where('j.status', 'posted')
                ->where('jl.account_id', $accountId)
                ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
                ->select('j.id as journal_id', 'jl.id as line_id', 'j.journal_number', 'j.journal_date', 'j.description as journal_desc', 'jl.description as line_desc', 'jl.debit_amount', 'jl.credit_amount', 'j.source', 'j.reference')
                ->orderBy('j.journal_date')
                ->orderBy('j.id')
                ->get();

            foreach ($transactions as $txn) {
                $txn->display_source = $txn->source ?: 'bank_import';
                $txn->realloc_info = null;
                $txn->display_reference = $txn->reference ?? '';
                $ref = $txn->reference ?? '';

                if (strpos($ref, 'REALLOCATED') !== false) {
                    $txn->display_source = 'reallocated';
                    $reallocJournal = \DB::table('cims_accounts_journals')
                        ->where('reference', 'REALLOC-' . $txn->journal_number)
                        ->where('company_id', $companyId)
                        ->where('status', 'posted')
                        ->first();
                    if ($reallocJournal) {
                        $targetLine = \DB::table('cims_accounts_journal_lines as jl')
                            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
                            ->where('jl.journal_id', $reallocJournal->id)
                            ->where('jl.account_id', '!=', $accountId)
                            ->whereNotIn('c.account_type', ['asset'])
                            ->first();
                        if (!$targetLine) {
                            $targetLine = \DB::table('cims_accounts_journal_lines as jl')
                                ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
                                ->where('jl.journal_id', $reallocJournal->id)
                                ->where('jl.account_id', '!=', $accountId)
                                ->first();
                        }
                        if ($targetLine) {
                            $txn->realloc_info = 'Reallocated to ' . $targetLine->account_name;
                            $txn->display_reference = 'Reallocated to ' . $targetLine->account_name;
                        }
                    }
                } elseif (strpos($ref, 'REALLOC-REV-') !== false) {
                    $txn->display_source = 'reallocated';
                    $origNum = str_replace('REALLOC-REV-', '', $ref);
                    $origJournal = \DB::table('cims_accounts_journals')
                        ->where('journal_number', $origNum)
                        ->where('company_id', $companyId)
                        ->first();
                    if ($origJournal) {
                        $txn->realloc_info = 'Reversal of ' . $origNum;
                        $txn->display_reference = 'Reversal of ' . $origNum;
                    }
                } elseif (strpos($ref, 'REALLOC-') !== false) {
                    $txn->display_source = 'system';
                    $origNum = str_replace('REALLOC-', '', $ref);
                    $origJournal = \DB::table('cims_accounts_journals')
                        ->where('journal_number', $origNum)
                        ->where('company_id', $companyId)
                        ->first();
                    if ($origJournal) {
                        $txn->display_reference = $origJournal->reference ?? '';
                        if (strpos($txn->display_reference, 'REALLOCATED') !== false) {
                            $txn->display_reference = str_replace(' REALLOCATED', '', $txn->display_reference);
                        }
                    }
                }
            }
        }

        return view('cims_accounts::accounts.report-gl', compact(
            'company', 'period', 'dateFrom', 'dateTo', 'accounts', 'accountId',
            'selectedAccount', 'transactions', 'openingBalance', 'allAccounts', 'groupedAccounts'
        ));
    }

    public function vatReport(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);

        $vatCycle = 'A';
        $vatEffectFrom = null;
        $vatYears = [];
        if ($company->client_id) {
            $client = \DB::table('client_master')->where('client_id', $company->client_id)->first();
            if ($client) {
                $cycleName = $client->vat_cycle_name ?? '';
                if (stripos($cycleName, 'Category B') !== false || stripos($cycleName, 'Even') !== false) {
                    $vatCycle = 'B';
                }
                $vatEffectFrom = $client->vat_effect_from ?? null;
            }
        }

        $startYear = $vatEffectFrom ? \Carbon\Carbon::parse($vatEffectFrom)->year : 2024;
        $currentYear = (int)date('Y');
        for ($y = $startYear; $y <= $currentYear + 1; $y++) {
            $vatYears[] = $y;
        }

        $period = $request->input('period', '');
        $vatYear = $request->input('vat_year', $currentYear);

        if ($period && strpos($period, 'vat_') === 0) {
            $parts = explode('_', $period);
            $m1 = (int)($parts[1] ?? 1);
            $m2 = (int)($parts[2] ?? 2);
            $y = (int)$vatYear;
            if ($m2 < $m1) {
                $dateFrom = \Carbon\Carbon::create($y - 1, $m1, 1)->toDateString();
                $dateTo = \Carbon\Carbon::create($y, $m2, 1)->endOfMonth()->toDateString();
            } else {
                $dateFrom = \Carbon\Carbon::create($y, $m1, 1)->toDateString();
                $dateTo = \Carbon\Carbon::create($y, $m2, 1)->endOfMonth()->toDateString();
            }
        } elseif ($period === 'full_year') {
            $y = (int)$vatYear;
            $dateFrom = \Carbon\Carbon::create($y, 1, 1)->toDateString();
            $dateTo = \Carbon\Carbon::create($y, 12, 31)->toDateString();
        } elseif ($period === 'custom' && $request->input('date_from') && $request->input('date_to')) {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
        } else {
            $now = \Carbon\Carbon::now();
            if ($vatCycle === 'A') {
                $oddEnds = [1, 3, 5, 7, 9, 11];
                $curMonth = $now->month;
                $endMonth = null;
                foreach ($oddEnds as $m) {
                    if ($curMonth <= $m) { $endMonth = $m; break; }
                }
                if (!$endMonth) $endMonth = 1;
                $startMonth = $endMonth - 1;
                if ($startMonth < 1) $startMonth = 12;
                $sy = $startMonth > $endMonth ? $now->year - 1 : $now->year;
                $ey = $endMonth < $startMonth ? $now->year : $now->year;
                $dateFrom = \Carbon\Carbon::create($sy, $startMonth, 1)->toDateString();
                $dateTo = \Carbon\Carbon::create($ey, $endMonth, 1)->endOfMonth()->toDateString();
            } else {
                $evenEnds = [2, 4, 6, 8, 10, 12];
                $curMonth = $now->month;
                $endMonth = null;
                foreach ($evenEnds as $m) {
                    if ($curMonth <= $m) { $endMonth = $m; break; }
                }
                if (!$endMonth) $endMonth = 2;
                $startMonth = $endMonth - 1;
                $dateFrom = \Carbon\Carbon::create($now->year, $startMonth, 1)->toDateString();
                $dateTo = \Carbon\Carbon::create($now->year, $endMonth, 1)->endOfMonth()->toDateString();
            }
            $vatYear = $now->year;
            $period = '';
        }

        $vatInputAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('account_name', 'LIKE', '%VAT Input%')->where('account_level', 3)->first();
        $vatOutputAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('account_name', 'LIKE', '%VAT Output%')->where('account_level', 3)->first();

        $outputVatLines = [];
        $inputVatLines = [];
        $totalOutputVat = 0;
        $totalInputVat = 0;
        $totalOutputSales = 0;
        $totalInputPurchases = 0;

        $vatAccountIds = [];
        if ($vatInputAccount) $vatAccountIds[] = $vatInputAccount->id;
        if ($vatOutputAccount) $vatAccountIds[] = $vatOutputAccount->id;

        if (!empty($vatAccountIds)) {
            $vatLines = \DB::table('cims_accounts_journal_lines as jl')
                ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
                ->where('j.company_id', $companyId)
                ->where('j.status', 'posted')
                ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
                ->whereIn('jl.account_id', $vatAccountIds)
                ->select('j.id as journal_id', 'j.journal_number', 'j.journal_date',
                    'j.description as journal_desc', 'j.reference', 'j.source',
                    'jl.description as line_desc', 'jl.account_id',
                    'jl.debit_amount', 'jl.credit_amount', 'jl.vat_amount')
                ->orderBy('j.journal_date')
                ->get();

            foreach ($vatLines as $vl) {
                $dr = (float)$vl->debit_amount;
                $cr = (float)$vl->credit_amount;

                $isOutput = $vatOutputAccount && $vl->account_id == $vatOutputAccount->id;
                if ($isOutput) {
                    $vatAmt = $cr > 0 ? $cr : -$dr;
                } else {
                    $vatAmt = $dr > 0 ? $dr : -$cr;
                }

                $contraLine = \DB::table('cims_accounts_journal_lines as jl')
                    ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
                    ->where('jl.journal_id', $vl->journal_id)
                    ->whereNotIn('jl.account_id', $vatAccountIds)
                    ->whereNotIn('c.account_type', ['asset', 'liability'])
                    ->select('c.account_name', 'c.account_type')
                    ->first();

                if (!$contraLine) {
                    $contraLine = \DB::table('cims_accounts_journal_lines as jl')
                        ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
                        ->where('jl.journal_id', $vl->journal_id)
                        ->whereNotIn('jl.account_id', $vatAccountIds)
                        ->select('c.account_name', 'c.account_type')
                        ->first();
                }

                $contraName = $contraLine ? $contraLine->account_name : 'Unknown';
                $contraNetAmt = 0;
                $netLine = \DB::table('cims_accounts_journal_lines')
                    ->where('journal_id', $vl->journal_id)
                    ->whereNotIn('account_id', $vatAccountIds)
                    ->where(function ($q) {
                        $q->where('debit_amount', '>', 0)->orWhere('credit_amount', '>', 0);
                    })
                    ->get();

                foreach ($netLine as $nl) {
                    $bankAccount = \DB::table('cims_accounts_chart')
                        ->where('id', $nl->account_id)
                        ->whereIn('account_type', ['asset'])
                        ->where('account_name', 'LIKE', '%Bank%')
                        ->first();
                    if (!$bankAccount) {
                        $contraNetAmt = max((float)$nl->debit_amount, (float)$nl->credit_amount);
                        break;
                    }
                }

                $sign = $vatAmt < 0 ? -1 : 1;
                $contraNetAmt = $contraNetAmt * $sign;

                $vl->account_name = $contraName;
                $vl->account_type = $contraLine ? $contraLine->account_type : '';
                $vl->vat_amount = $vatAmt;
                $vl->net_amount = $contraNetAmt;

                if ($isOutput) {
                    $outputVatLines[] = $vl;
                    $totalOutputVat += $vatAmt;
                    $totalOutputSales += $contraNetAmt;
                } else {
                    $inputVatLines[] = $vl;
                    $totalInputVat += $vatAmt;
                    $totalInputPurchases += $contraNetAmt;
                }
            }
        }

        $netVat = $totalOutputVat - $totalInputVat;

        $zeroRatedSales = 0;
        $exemptSales = 0;
        $zeroRatedPurchases = 0;
        $exemptPurchases = 0;

        $allSalesLines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
            ->whereIn('c.account_type', ['revenue', 'cost_of_sales', 'expense'])
            ->whereIn('jl.vat_type', ['zero_rated', 'exempt'])
            ->select('c.account_type', 'jl.vat_type', 'jl.debit_amount', 'jl.credit_amount')
            ->get();

        foreach ($allSalesLines as $sl) {
            $amt = max((float)$sl->debit_amount, (float)$sl->credit_amount);
            if ($sl->account_type === 'revenue') {
                if ($sl->vat_type === 'zero_rated') $zeroRatedSales += $amt;
                if ($sl->vat_type === 'exempt') $exemptSales += $amt;
            } else {
                if ($sl->vat_type === 'zero_rated') $zeroRatedPurchases += $amt;
                if ($sl->vat_type === 'exempt') $exemptPurchases += $amt;
            }
        }

        $allAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 3)->where('is_active', 1)->where('is_header', 0)
            ->orderBy('account_name')->get();

        return view('cims_accounts::accounts.report-vat', compact(
            'company', 'period', 'dateFrom', 'dateTo',
            'outputVatLines', 'inputVatLines',
            'totalOutputVat', 'totalInputVat', 'netVat',
            'totalOutputSales', 'totalInputPurchases',
            'zeroRatedSales', 'exemptSales', 'zeroRatedPurchases', 'exemptPurchases',
            'allAccounts', 'vatCycle', 'vatYear', 'vatYears'
        ));
    }

    public function bankReconciliation(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $fyMonth = $company->financial_year_start ?: 3;
        $now = \Carbon\Carbon::now();
        $currentFyYear = $now->month >= $fyMonth ? $now->year : $now->year - 1;
        $selectedYear = (int)$request->input('fy_year', $currentFyYear);

        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', 1)->get();
        $selectedBankId = $request->input('bank_account_id', $bankAccounts->first() ? $bankAccounts->first()->id : null);
        $selectedBank = $selectedBankId ? BankAccount::with('glAccount')->find($selectedBankId) : null;

        $periods = [];
        $summaryCards = ['received' => 0, 'reconciled' => 0, 'in_progress' => 0, 'pending' => 0];

        if ($selectedBank) {
            for ($i = 0; $i < 12; $i++) {
                $month = $fyMonth + $i;
                $year = $selectedYear;
                if ($month > 12) { $month -= 12; $year++; }

                $recon = \DB::table('cims_accounts_bank_reconciliations')
                    ->where('company_id', $companyId)
                    ->where('bank_account_id', $selectedBankId)
                    ->where('period_year', $year)
                    ->where('period_month', $month)
                    ->first();

                if (!$recon) {
                    \DB::table('cims_accounts_bank_reconciliations')->insert([
                        'company_id' => $companyId,
                        'bank_account_id' => $selectedBankId,
                        'period_year' => $year,
                        'period_month' => $month,
                        'status' => 'not_started',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $recon = \DB::table('cims_accounts_bank_reconciliations')
                        ->where('company_id', $companyId)
                        ->where('bank_account_id', $selectedBankId)
                        ->where('period_year', $year)
                        ->where('period_month', $month)
                        ->first();
                }

                $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $periodEnd = $periodStart->copy()->endOfMonth();

                $glBal = \DB::table('cims_accounts_journal_lines as jl')
                    ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
                    ->where('j.company_id', $companyId)
                    ->where('j.status', 'posted')
                    ->where('jl.account_id', $selectedBank->account_id)
                    ->where('j.journal_date', '<=', $periodEnd->toDateString())
                    ->select(
                        \DB::raw('COALESCE(SUM(jl.debit_amount), 0) as total_debit'),
                        \DB::raw('COALESCE(SUM(jl.credit_amount), 0) as total_credit')
                    )->first();
                $glBalance = $glBal ? round((float)$glBal->total_debit - (float)$glBal->total_credit, 2) : 0;

                $txnCount = \DB::table('cims_accounts_journal_lines as jl')
                    ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
                    ->where('j.company_id', $companyId)
                    ->where('j.status', 'posted')
                    ->where('jl.account_id', $selectedBank->account_id)
                    ->whereBetween('j.journal_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                    ->count();

                $reconLines = \DB::table('cims_accounts_bank_recon_lines')
                    ->where('reconciliation_id', $recon->id)
                    ->where('is_reconciled', 1)
                    ->count();

                if ($recon->statement_received) $summaryCards['received']++;
                if ($recon->status === 'completed' || $recon->status === 'balanced') $summaryCards['reconciled']++;
                elseif ($recon->status === 'in_progress') $summaryCards['in_progress']++;
                else $summaryCards['pending']++;

                $periods[] = (object)[
                    'id' => $recon->id,
                    'year' => $year,
                    'month' => $month,
                    'month_name' => $periodStart->format('F Y'),
                    'status' => $recon->status,
                    'statement_received' => $recon->statement_received,
                    'statement_received_at' => $recon->statement_received_at,
                    'statement_balance' => (float)$recon->statement_balance,
                    'statement_number' => $recon->statement_number,
                    'gl_balance' => $glBalance,
                    'reconciled_balance' => (float)$recon->reconciled_balance,
                    'difference' => (float)$recon->difference,
                    'reconciled_at' => $recon->reconciled_at,
                    'txn_count' => $txnCount,
                    'recon_lines' => $reconLines,
                    'is_future' => $periodStart->isFuture(),
                ];
            }
        }

        $activity = \DB::table('cims_accounts_bank_recon_activity')
            ->where('company_id', $companyId)
            ->when($selectedBankId, function ($q) use ($selectedBankId) {
                $q->where('bank_account_id', $selectedBankId);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $fyYears = [];
        $startYear = $currentFyYear - 2;
        for ($y = $startYear; $y <= $currentFyYear + 1; $y++) {
            $endY = $y + ($fyMonth > 1 ? 1 : 0);
            $label = \DateTime::createFromFormat('!m', $fyMonth)->format('M') . ' ' . $y . ' - ' .
                     \DateTime::createFromFormat('!m', $fyMonth > 1 ? $fyMonth - 1 : 12)->format('M') . ' ' . $endY;
            $fyYears[] = (object)['value' => $y, 'label' => $label];
        }

        return view('cims_accounts::accounts.report-bankrecon', compact(
            'company', 'bankAccounts', 'selectedBankId', 'selectedBank',
            'periods', 'summaryCards', 'selectedYear', 'fyYears',
            'activity', 'fyMonth'
        ));
    }

    public function bankReconReconcile(Request $request, $companyId, $reconId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $recon = \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) abort(404);

        $bankAccount = BankAccount::with('glAccount')->findOrFail($recon->bank_account_id);
        $periodStart = \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $transactions = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->whereBetween('j.journal_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->select('j.id as journal_id', 'jl.id as line_id', 'j.journal_number', 'j.journal_date',
                     'j.description as journal_desc', 'jl.description as line_desc',
                     'jl.debit_amount', 'jl.credit_amount', 'j.reference', 'j.source')
            ->orderBy('j.journal_date')
            ->orderBy('j.id')
            ->get();

        $reconLines = \DB::table('cims_accounts_bank_recon_lines')
            ->where('reconciliation_id', $reconId)
            ->pluck('is_reconciled', 'journal_line_id')
            ->toArray();

        $glBalEnd = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $periodEnd->toDateString())
            ->select(
                \DB::raw('COALESCE(SUM(jl.debit_amount), 0) as total_debit'),
                \DB::raw('COALESCE(SUM(jl.credit_amount), 0) as total_credit')
            )->first();
        $glBalance = $glBalEnd ? round((float)$glBalEnd->total_debit - (float)$glBalEnd->total_credit, 2) : 0;

        foreach ($transactions as $txn) {
            $txn->amount = round((float)$txn->debit_amount - (float)$txn->credit_amount, 2);
            $txn->is_reconciled = isset($reconLines[$txn->line_id]) ? (int)$reconLines[$txn->line_id] : 0;
        }

        $reconciledTotal = $transactions->where('is_reconciled', 1)->sum('amount');
        $outstandingTotal = $transactions->where('is_reconciled', 0)->sum('amount');

        return view('cims_accounts::accounts.bank-recon-reconcile', compact(
            'company', 'recon', 'bankAccount', 'periodStart', 'periodEnd',
            'transactions', 'glBalance', 'reconciledTotal', 'outstandingTotal'
        ));
    }

    public function bankReconToggleLine(Request $request, $companyId, $reconId)
    {
        $recon = \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        $lineId = $request->input('line_id');
        $checked = $request->input('checked') ? 1 : 0;
        $journalId = $request->input('journal_id');

        $amount = 0;
        $jl = \DB::table('cims_accounts_journal_lines')->where('id', $lineId)->first();
        if ($jl) $amount = round((float)$jl->debit_amount - (float)$jl->credit_amount, 2);

        $existing = \DB::table('cims_accounts_bank_recon_lines')
            ->where('reconciliation_id', $reconId)
            ->where('journal_line_id', $lineId)
            ->first();

        if ($existing) {
            \DB::table('cims_accounts_bank_recon_lines')->where('id', $existing->id)->update([
                'is_reconciled' => $checked,
                'reconciled_at' => $checked ? now() : null,
                'updated_at' => now(),
            ]);
        } else {
            \DB::table('cims_accounts_bank_recon_lines')->insert([
                'reconciliation_id' => $reconId,
                'journal_id' => $journalId ?: 0,
                'journal_line_id' => $lineId,
                'amount' => $amount,
                'is_reconciled' => $checked,
                'reconciled_at' => $checked ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $bankAccount = BankAccount::find($recon->bank_account_id);
        $periodEnd = \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->endOfMonth();

        $reconciledSum = \DB::table('cims_accounts_bank_recon_lines as rl')
            ->join('cims_accounts_journal_lines as jl', 'rl.journal_line_id', '=', 'jl.id')
            ->where('rl.reconciliation_id', $reconId)
            ->where('rl.is_reconciled', 1)
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $reconciledSum = round((float)$reconciledSum, 2);

        $glBal = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $periodEnd->toDateString())
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $glBal = round((float)$glBal, 2);

        $stmtBal = (float)$recon->statement_balance;
        $outstanding = round($glBal - $reconciledSum, 2);
        $difference = round($stmtBal - $reconciledSum, 2);

        $reconCount = \DB::table('cims_accounts_bank_recon_lines')
            ->where('reconciliation_id', $reconId)->where('is_reconciled', 1)->count();
        $totalCount = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->whereBetween('j.journal_date', [
                \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->startOfMonth()->toDateString(),
                $periodEnd->toDateString()
            ])->count();

        $newStatus = $recon->status;
        if ($reconCount > 0 && $reconCount < $totalCount) $newStatus = 'in_progress';
        if ($reconCount > 0 && abs($difference) < 0.01) $newStatus = 'balanced';

        \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->update([
            'reconciled_balance' => $reconciledSum,
            'outstanding_balance' => $outstanding,
            'gl_balance' => $glBal,
            'difference' => $difference,
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'reconciled_total' => $reconciledSum,
            'outstanding_total' => $outstanding,
            'gl_balance' => $glBal,
            'statement_balance' => $stmtBal,
            'difference' => $difference,
            'recon_count' => $reconCount,
            'total_count' => $totalCount,
            'status' => $newStatus,
        ]);
    }

    public function bankReconBulkToggle(Request $request, $companyId, $reconId)
    {
        $recon = \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        $action = $request->input('action');
        $bankAccount = BankAccount::find($recon->bank_account_id);
        $periodStart = \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->whereBetween('j.journal_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->select('j.id as journal_id', 'jl.id as line_id', 'jl.debit_amount', 'jl.credit_amount')
            ->get();

        $checked = $action === 'select_all' ? 1 : 0;

        foreach ($lines as $l) {
            $amount = round((float)$l->debit_amount - (float)$l->credit_amount, 2);
            $existing = \DB::table('cims_accounts_bank_recon_lines')
                ->where('reconciliation_id', $reconId)
                ->where('journal_line_id', $l->line_id)
                ->first();
            if ($existing) {
                \DB::table('cims_accounts_bank_recon_lines')->where('id', $existing->id)->update([
                    'is_reconciled' => $checked,
                    'reconciled_at' => $checked ? now() : null,
                    'updated_at' => now(),
                ]);
            } else {
                \DB::table('cims_accounts_bank_recon_lines')->insert([
                    'reconciliation_id' => $reconId,
                    'journal_id' => $l->journal_id,
                    'journal_line_id' => $l->line_id,
                    'amount' => $amount,
                    'is_reconciled' => $checked,
                    'reconciled_at' => $checked ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $reconciledSum = \DB::table('cims_accounts_bank_recon_lines as rl')
            ->join('cims_accounts_journal_lines as jl', 'rl.journal_line_id', '=', 'jl.id')
            ->where('rl.reconciliation_id', $reconId)
            ->where('rl.is_reconciled', 1)
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $reconciledSum = round((float)$reconciledSum, 2);

        $glBal = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $periodEnd->toDateString())
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $glBal = round((float)$glBal, 2);

        $stmtBal = (float)$recon->statement_balance;
        $outstanding = round($glBal - $reconciledSum, 2);
        $difference = round($stmtBal - $reconciledSum, 2);

        $reconCount = \DB::table('cims_accounts_bank_recon_lines')
            ->where('reconciliation_id', $reconId)->where('is_reconciled', 1)->count();
        $totalCount = $lines->count();

        $newStatus = 'not_started';
        if ($reconCount > 0 && $reconCount < $totalCount) $newStatus = 'in_progress';
        if ($reconCount > 0 && abs($difference) < 0.01) $newStatus = 'balanced';
        if ($reconCount === 0) $newStatus = 'not_started';

        \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->update([
            'reconciled_balance' => $reconciledSum,
            'outstanding_balance' => $outstanding,
            'gl_balance' => $glBal,
            'difference' => $difference,
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'reconciled_total' => $reconciledSum,
            'outstanding_total' => $outstanding,
            'gl_balance' => $glBal,
            'statement_balance' => $stmtBal,
            'difference' => $difference,
            'recon_count' => $reconCount,
            'total_count' => $totalCount,
            'status' => $newStatus,
        ]);
    }

    public function bankReconUpdateStatement(Request $request, $companyId, $reconId)
    {
        $recon = \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        $updates = ['updated_at' => now()];
        if ($request->has('statement_balance')) $updates['statement_balance'] = (float)$request->input('statement_balance');
        if ($request->has('statement_number')) $updates['statement_number'] = $request->input('statement_number');
        if ($request->has('statement_date')) $updates['statement_date'] = $request->input('statement_date');
        if ($request->has('statement_received')) {
            $updates['statement_received'] = $request->input('statement_received') ? 1 : 0;
            if ($updates['statement_received']) $updates['statement_received_at'] = now();
        }

        \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->update($updates);

        if (isset($updates['statement_balance'])) {
            $reconciledSum = \DB::table('cims_accounts_bank_recon_lines as rl')
                ->join('cims_accounts_journal_lines as jl', 'rl.journal_line_id', '=', 'jl.id')
                ->where('rl.reconciliation_id', $reconId)
                ->where('rl.is_reconciled', 1)
                ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
                ->value('total');
            $reconciledSum = round((float)$reconciledSum, 2);
            $difference = round((float)$updates['statement_balance'] - $reconciledSum, 2);
            \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->update([
                'difference' => $difference,
            ]);
        }

        \DB::table('cims_accounts_bank_recon_activity')->insert([
            'company_id' => $companyId,
            'bank_account_id' => $recon->bank_account_id,
            'reconciliation_id' => $reconId,
            'action' => 'statement_updated',
            'description' => 'Statement details updated for ' . \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->format('F Y'),
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function bankReconComplete(Request $request, $companyId, $reconId)
    {
        $recon = \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->update([
            'status' => 'completed',
            'reconciled_by' => auth()->id(),
            'reconciled_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('cims_accounts_bank_recon_activity')->insert([
            'company_id' => $companyId,
            'bank_account_id' => $recon->bank_account_id,
            'reconciliation_id' => $reconId,
            'action' => 'completed',
            'description' => 'Reconciliation completed for ' . \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->format('F Y'),
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function bankReconMarkReceived(Request $request, $companyId, $reconId)
    {
        $recon = \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        $received = $request->input('received', 1);
        \DB::table('cims_accounts_bank_reconciliations')->where('id', $reconId)->update([
            'statement_received' => $received ? 1 : 0,
            'statement_received_at' => $received ? now() : null,
            'updated_at' => now(),
        ]);

        \DB::table('cims_accounts_bank_recon_activity')->insert([
            'company_id' => $companyId,
            'bank_account_id' => $recon->bank_account_id,
            'reconciliation_id' => $reconId,
            'action' => $received ? 'statement_received' : 'statement_unreceived',
            'description' => 'Statement ' . ($received ? 'marked as received' : 'unmarked') . ' for ' . \Carbon\Carbon::create($recon->period_year, $recon->period_month, 1)->format('F Y'),
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function bankReconDashboardView()
    {
        return view('smartdash.reconciliation.dashboard');
    }

    public function salesDashboardView()
    {
        return view('smartdash.sales.dashboard');
    }

    public function financialDashboardView(Request $request, $companyId = null)
    {
        if (!$companyId) return redirect()->route('cimsaccounts.dashboard');
        $company = AccountsCompany::findOrFail($companyId);
        return view('cims_accounts::accounts.financial-dashboard', compact('company'));
    }

    public function incomeStatementView(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        return view('cims_accounts::accounts.income-statement', compact('company'));
    }

    public function balanceSheetDashView(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        return view('cims_accounts::accounts.balance-sheet-dash', compact('company'));
    }

    public function cashflowStatementView(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        return view('cims_accounts::accounts.cashflow-dash', compact('company'));
    }

    public function expensesDashView(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        return view('cims_accounts::accounts.expenses-dash', compact('company'));
    }

    public function expensesCategoryDashView(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        return view('cims_accounts::accounts.expenses-category-dash', compact('company'));
    }

    /* --- financialDashboardView data logic (commented out for static build) ---
        $period = $request->input('period', 'this_year');
        $fyMonth = $company->financial_year_start ?: 3;
        list($dateFrom, $dateTo) = $this->resolvePeriodDates($period, $request->input('date_from'), $request->input('date_to'), $fyMonth, $companyId);

        $from = \Carbon\Carbon::parse($dateFrom);
        $to = \Carbon\Carbon::parse($dateTo);
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) { $months[] = $cursor->format('Y-m'); $cursor->addMonth(); }

        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
            ->select('c.id as account_id', 'c.account_code', 'c.account_name', 'c.account_type', 'c.parent_id',
                \DB::raw("DATE_FORMAT(j.journal_date, '%Y-%m') as month_key"),
                \DB::raw('SUM(jl.debit_amount) as total_debit'), \DB::raw('SUM(jl.credit_amount) as total_credit'))
            ->groupBy('c.id','c.account_code','c.account_name','c.account_type','c.parent_id',\DB::raw("DATE_FORMAT(j.journal_date, '%Y-%m')"))
            ->orderBy('c.account_code')->get();

        $parentNames = ChartOfAccount::where('company_id', $companyId)->where('account_level', 2)->pluck('account_name', 'id');
        $monthTotals = ['revenue' => array_fill_keys($months, 0), 'cos' => array_fill_keys($months, 0), 'expense' => array_fill_keys($months, 0)];
        $expenseBreakdown = [];

        foreach ($lines as $line) {
            $bal = $line->account_type === 'revenue' ? $line->total_credit - $line->total_debit : $line->total_debit - $line->total_credit;
            $mk = $line->month_key;
            if ($line->account_type === 'revenue' && isset($monthTotals['revenue'][$mk])) $monthTotals['revenue'][$mk] += $bal;
            elseif ($line->account_type === 'cost_of_sales' && isset($monthTotals['cos'][$mk])) $monthTotals['cos'][$mk] += $bal;
            elseif ($line->account_type === 'expense' && isset($monthTotals['expense'][$mk])) {
                $monthTotals['expense'][$mk] += $bal;
                $group = $parentNames[$line->parent_id] ?? $line->account_name;
                $expenseBreakdown[$group] = ($expenseBreakdown[$group] ?? 0) + $bal;
            }
        }

        $totalRevenue = array_sum($monthTotals['revenue']);
        $totalCos = array_sum($monthTotals['cos']);
        $totalExpenses = array_sum($monthTotals['expense']);
        $grossProfit = $totalRevenue - $totalCos;
        $netProfit = $grossProfit - $totalExpenses;
        $netProfitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;
        $grossProfitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;
        $expenseRatio = $totalRevenue > 0 ? round(($totalExpenses / $totalRevenue) * 100, 1) : 0;

        arsort($expenseBreakdown);

        $bsLines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)->where('j.status', 'posted')->where('j.journal_date', '<=', $dateTo)
            ->whereIn('c.account_type', ['asset','liability','equity'])
            ->select('c.account_type', 'c.parent_id',
                \DB::raw('SUM(jl.debit_amount) as total_debit'), \DB::raw('SUM(jl.credit_amount) as total_credit'))
            ->groupBy('c.account_type', 'c.parent_id')->get();

        $totalAssets = 0; $totalLiabilities = 0; $totalEquity = 0;
        $currentAssets = 0; $fixedAssets = 0; $currentLiabilities = 0; $longTermLiabilities = 0;
        foreach ($bsLines as $bl) {
            $bal = $bl->account_type === 'asset' ? $bl->total_debit - $bl->total_credit : $bl->total_credit - $bl->total_debit;
            $groupName = strtolower($parentNames[$bl->parent_id] ?? '');
            if ($bl->account_type === 'asset') {
                $totalAssets += $bal;
                if (str_contains($groupName, 'fixed') || str_contains($groupName, 'non-current') || str_contains($groupName, 'property')) $fixedAssets += $bal;
                else $currentAssets += $bal;
            } elseif ($bl->account_type === 'liability') {
                $totalLiabilities += $bal;
                if (str_contains($groupName, 'long') || str_contains($groupName, 'non-current')) $longTermLiabilities += $bal;
                else $currentLiabilities += $bal;
            } else { $totalEquity += $bal; }
        }

        $pnlRetained = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)->where('j.status', 'posted')->where('j.journal_date', '<=', $dateTo)
            ->whereIn('c.account_type', ['revenue','cost_of_sales','expense'])
            ->select(\DB::raw('SUM(jl.debit_amount) as td'), \DB::raw('SUM(jl.credit_amount) as tc'))->first();
        $retainedEarnings = $pnlRetained ? ($pnlRetained->tc - $pnlRetained->td) : 0;
        $totalEquity += $retainedEarnings;

        $monthLabels = array_map(function($m) { return \Carbon\Carbon::parse($m.'-01')->format('M Y'); }, $months);
        $monthlyIncome = array_values($monthTotals['revenue']);
        $monthlyExpenses = array_map(function($m) use ($monthTotals) { return $monthTotals['cos'][$m] + $monthTotals['expense'][$m]; }, $months);
        $monthlyNetProfit = [];
        $monthlyMargin = [];
        foreach ($months as $i => $m) {
            $inc = $monthTotals['revenue'][$m];
            $exp = $monthTotals['cos'][$m] + $monthTotals['expense'][$m];
            $np = $inc - $exp;
            $monthlyNetProfit[] = $np;
            $monthlyMargin[] = $inc > 0 ? round(($np / $inc) * 100, 1) : 0;
        }

        $prevFrom = $from->copy()->subYear()->toDateString();
        $prevTo = $to->copy()->subYear()->toDateString();
        $prevLines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$prevFrom, $prevTo])
            ->select('c.account_type', \DB::raw('SUM(jl.debit_amount) as td'), \DB::raw('SUM(jl.credit_amount) as tc'))
            ->groupBy('c.account_type')->get();
        $prevRevenue = 0; $prevCos = 0; $prevExpenses = 0;
        foreach ($prevLines as $pl) {
            if ($pl->account_type === 'revenue') $prevRevenue += ($pl->tc - $pl->td);
            elseif ($pl->account_type === 'cost_of_sales') $prevCos += ($pl->td - $pl->tc);
            elseif ($pl->account_type === 'expense') $prevExpenses += ($pl->td - $pl->tc);
        }
        $prevNetProfit = $prevRevenue - $prevCos - $prevExpenses;
        $prevMargin = $prevRevenue > 0 ? round(($prevNetProfit / $prevRevenue) * 100, 1) : 0;

        $pctChange = function($curr, $prev) { return $prev != 0 ? round((($curr - $prev) / abs($prev)) * 100, 1) : ($curr > 0 ? 100 : 0); };
        $changes = [
            'revenue' => $pctChange($totalRevenue, $prevRevenue),
            'grossProfit' => $pctChange($grossProfit, $prevRevenue - $prevCos),
            'expenses' => $pctChange($totalExpenses + $totalCos, $prevExpenses + $prevCos),
            'netProfit' => $pctChange($netProfit, $prevNetProfit),
            'margin' => round($netProfitMargin - $prevMargin, 1),
        ];

        return view('cims_accounts::accounts.financial-dashboard', compact(
            'company','period','dateFrom','dateTo','months','monthLabels',
            'monthlyIncome','monthlyExpenses','monthlyNetProfit','monthlyMargin',
            'totalRevenue','totalCos','grossProfit','grossProfitMargin','totalExpenses','expenseRatio','netProfit','netProfitMargin',
            'expenseBreakdown','totalAssets','currentAssets','fixedAssets','totalLiabilities','currentLiabilities','longTermLiabilities','totalEquity','retainedEarnings',
            'changes'
        ));
    }
    --- end of commented out data logic --- */

    public function complianceDashboardView()
    {
        return view('smartdash.compliance.dashboard');
    }

    public function apiAccountTransactions(Request $request, $companyId, $accountId)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $accountId);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('j.journal_date', [$dateFrom, $dateTo]);
        } elseif ($dateTo) {
            $query->where('j.journal_date', '<=', $dateTo);
        } elseif ($dateFrom) {
            $query->where('j.journal_date', '>=', $dateFrom);
        }

        $lines = $query
            ->select('j.id as journal_id', 'jl.id as line_id', 'j.journal_number', 'j.journal_date', 'j.description as journal_desc', 'jl.description as line_desc', 'jl.debit_amount', 'jl.credit_amount', 'j.source', 'j.reference', 'jl.ma_hidden', 'jl.note')
            ->orderBy('j.journal_date')
            ->orderBy('j.id')
            ->get();

        $rows = [];
        foreach ($lines as $l) {
            $displaySource = $l->source ?: 'bank_import';
            $reallocInfo = null;
            $displayRef = $ref = $l->reference ?? '';

            if (strpos($ref, 'REALLOCATED') !== false) {
                $displaySource = 'reallocated';
                $reallocJournal = \DB::table('cims_accounts_journals')
                    ->where('reference', 'REALLOC-' . $l->journal_number)
                    ->where('company_id', $companyId)
                    ->first();
                if ($reallocJournal) {
                    $targetLine = \DB::table('cims_accounts_journal_lines as jl')
                        ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
                        ->where('jl.journal_id', $reallocJournal->id)
                        ->where('jl.account_id', '!=', $accountId)
                        ->where('c.account_type', '!=', 'asset')
                        ->first();
                    if ($targetLine) {
                        $reallocInfo = 'Reallocated to ' . $targetLine->account_name;
                        $displayRef = 'Reallocated to ' . $targetLine->account_name;
                    }
                }
            } elseif (strpos($ref, 'REALLOC-REV-') !== false) {
                $displaySource = 'reallocated';
                $origNum = str_replace('REALLOC-REV-', '', $ref);
                $origJournal = \DB::table('cims_accounts_journals')
                    ->where('journal_number', $origNum)
                    ->where('company_id', $companyId)
                    ->first();
                if ($origJournal) {
                    $reallocInfo = 'Reversal of ' . $origNum;
                    $displayRef = 'Reversal of ' . $origNum;
                }
            } elseif (strpos($ref, 'REALLOC-') !== false) {
                $displaySource = 'system';
                $origNum = str_replace('REALLOC-', '', $ref);
                $origJournal = \DB::table('cims_accounts_journals')
                    ->where('journal_number', $origNum)
                    ->where('company_id', $companyId)
                    ->first();
                if ($origJournal) {
                    $displayRef = $origJournal->reference ?? '';
                    if (strpos($displayRef, 'REALLOCATED') !== false) {
                        $displayRef = str_replace(' REALLOCATED', '', $displayRef);
                    }
                }
            }

            $rows[] = [
                'journal_id' => $l->journal_id,
                'line_id' => $l->line_id,
                'date' => \Carbon\Carbon::parse($l->journal_date)->format('d M Y'),
                'journal' => $l->journal_number,
                'reference' => $displayRef,
                'description' => $l->line_desc ?: $l->journal_desc,
                'source' => $displaySource,
                'realloc_info' => $reallocInfo,
                'debit' => (float)$l->debit_amount,
                'credit' => (float)$l->credit_amount,
                'ma_hidden' => (bool)($l->ma_hidden ?? false),
                'note' => $l->note ?? null,
            ];
        }

        return response()->json(['rows' => $rows]);
    }

    public function apiReallocateJournal(Request $request, $companyId)
    {
        try {
            $request->validate([
                'journal_id' => 'required|integer',
                'new_account_id' => 'required|exists:cims_accounts_chart,id',
                'new_vat_type' => 'required|in:standard,zero_rated,exempt,none',
            ]);

            $journal = Journal::where('company_id', $companyId)
                ->where('status', 'posted')
                ->with('lines')
                ->findOrFail($request->journal_id);

            $newAccount = ChartOfAccount::where('company_id', $companyId)->findOrFail($request->new_account_id);

            // Step 1: Reverse original journal
            $lastJournal = Journal::where('company_id', $companyId)->orderBy('id', 'desc')->first();
            $nextNum = $lastJournal ? intval(substr($lastJournal->journal_number, 3)) + 1 : 1;
            $revNumber = 'JNL' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $reversal = Journal::create([
                'company_id' => $companyId, 'journal_number' => $revNumber,
                'journal_date' => $journal->journal_date, 'reference' => 'REALLOC-REV-' . $journal->journal_number,
                'description' => 'Reallocation reversal of ' . $journal->journal_number,
                'source' => 'system', 'status' => 'posted',
                'total_debit' => $journal->total_credit, 'total_credit' => $journal->total_debit,
                'reversal_of' => $journal->id,
                'created_by' => auth()->id(), 'posted_by' => auth()->id(), 'posted_at' => now(),
            ]);

            foreach ($journal->lines as $line) {
                JournalLine::create([
                    'journal_id' => $reversal->id, 'account_id' => $line->account_id,
                    'description' => 'Reversal - ' . $line->description,
                    'debit_amount' => $line->credit_amount, 'credit_amount' => $line->debit_amount,
                    'vat_amount' => $line->vat_amount, 'vat_type' => $line->vat_type,
                    'line_order' => $line->line_order,
                ]);
            }

            $journal->update(['status' => 'posted', 'reference' => ($journal->reference ? $journal->reference . ' | ' : '') . 'REALLOCATED']);

            // Step 2: Create new journal with corrected allocation
            $nextNum++;
            $newNumber = 'JNL' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $bankGlId = null;
            $contraLine = null;
            $vatLine = null;
            $bankLine = null;

            foreach ($journal->lines as $line) {
                $acct = ChartOfAccount::find($line->account_id);
                if ($acct && $acct->account_type === 'asset' && strpos(strtolower($acct->account_name), 'bank') !== false) {
                    $bankLine = $line;
                    $bankGlId = $line->account_id;
                } elseif ($line->vat_type === 'standard' && $line->vat_amount > 0) {
                    $vatLine = $line;
                } else {
                    $contraLine = $line;
                }
            }

            if (!$contraLine) {
                $contraLine = $journal->lines->first();
            }

            $absAmount = $journal->total_debit;
            $newVatType = $request->new_vat_type;
            $vatAmount = 0;
            $netAmount = $absAmount;
            if ($newVatType === 'standard') {
                $vatAmount = round($absAmount - ($absAmount / 1.15), 2);
                $netAmount = $absAmount - $vatAmount;
            }

            $newJournal = Journal::create([
                'company_id' => $companyId, 'journal_number' => $newNumber,
                'journal_date' => $journal->journal_date,
                'reference' => 'REALLOC-' . $journal->journal_number,
                'description' => $journal->description,
                'source' => 'system', 'status' => 'posted',
                'total_debit' => $absAmount, 'total_credit' => $absAmount,
                'created_by' => auth()->id(), 'posted_by' => auth()->id(), 'posted_at' => now(),
            ]);

            $lineOrder = 1;

            if ($bankLine && $bankLine->debit_amount > 0) {
                // Money came IN: Bank DR, Contra CR
                JournalLine::create([
                    'journal_id' => $newJournal->id, 'account_id' => $bankGlId,
                    'description' => $journal->description,
                    'debit_amount' => $absAmount, 'credit_amount' => 0,
                    'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder++,
                ]);
                if ($vatAmount > 0) {
                    $vatOutput = ChartOfAccount::where('company_id', $companyId)->where('account_name', 'VAT Output')->where('account_level', 3)->first();
                    if ($vatOutput) {
                        JournalLine::create([
                            'journal_id' => $newJournal->id, 'account_id' => $vatOutput->id,
                            'description' => 'VAT Output - ' . $journal->description,
                            'debit_amount' => 0, 'credit_amount' => $vatAmount,
                            'vat_amount' => $vatAmount, 'vat_type' => 'standard', 'line_order' => $lineOrder++,
                        ]);
                    }
                }
                JournalLine::create([
                    'journal_id' => $newJournal->id, 'account_id' => $newAccount->id,
                    'description' => $journal->description,
                    'debit_amount' => 0, 'credit_amount' => $netAmount,
                    'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder,
                ]);
            } elseif ($bankLine) {
                // Money went OUT: Contra DR, Bank CR
                JournalLine::create([
                    'journal_id' => $newJournal->id, 'account_id' => $newAccount->id,
                    'description' => $journal->description,
                    'debit_amount' => $netAmount, 'credit_amount' => 0,
                    'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder++,
                ]);
                if ($vatAmount > 0) {
                    $vatInput = ChartOfAccount::where('company_id', $companyId)->where('account_name', 'VAT Input')->where('account_level', 3)->first();
                    if ($vatInput) {
                        JournalLine::create([
                            'journal_id' => $newJournal->id, 'account_id' => $vatInput->id,
                            'description' => 'VAT Input - ' . $journal->description,
                            'debit_amount' => $vatAmount, 'credit_amount' => 0,
                            'vat_amount' => $vatAmount, 'vat_type' => 'standard', 'line_order' => $lineOrder++,
                        ]);
                    }
                }
                JournalLine::create([
                    'journal_id' => $newJournal->id, 'account_id' => $bankGlId,
                    'description' => $journal->description,
                    'debit_amount' => 0, 'credit_amount' => $absAmount,
                    'vat_amount' => 0, 'vat_type' => 'none', 'line_order' => $lineOrder,
                ]);
            } else {
                // Manual journal - replicate lines but swap the contra account
                foreach ($journal->lines as $line) {
                    $accId = ($line->account_id === $contraLine->account_id) ? $newAccount->id : $line->account_id;
                    JournalLine::create([
                        'journal_id' => $newJournal->id, 'account_id' => $accId,
                        'description' => $line->description,
                        'debit_amount' => $line->debit_amount, 'credit_amount' => $line->credit_amount,
                        'vat_amount' => $line->vat_amount, 'vat_type' => $line->vat_type,
                        'line_order' => $lineOrder++,
                    ]);
                }
            }

            // Step 3: Update bank transaction if applicable
            $bankTxn = BankTransaction::where('company_id', $companyId)->where('journal_id', $journal->id)->first();
            if ($bankTxn) {
                $bankTxn->update([
                    'allocated_account_id' => $newAccount->id,
                    'vat_type' => $newVatType,
                    'vat_amount' => $vatAmount,
                    'net_amount' => $netAmount,
                    'journal_id' => $newJournal->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reallocated: ' . $journal->journal_number . ' reversed, new journal ' . $newNumber . ' created with account ' . $newAccount->account_name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ===== API =====

    public function apiChartTree($companyId)
    {
        $accounts = ChartOfAccount::where('company_id', $companyId)->where('is_active', 1)
            ->orderBy('segment1')->orderBy(\DB::raw('COALESCE(segment2, "0000")'))->orderBy(\DB::raw('COALESCE(segment3, "0000")'))->get();
        return response()->json(['tree' => $this->buildTree($accounts)]);
    }

    public function apiChartBalances($companyId)
    {
        $accounts = ChartOfAccount::where('company_id', $companyId)->where('is_active', 1)->where('account_level', 3)->get();
        $balances = [];
        foreach ($accounts as $a) {
            $debits = JournalLine::where('account_id', $a->id)->whereHas('journal', function ($q) { $q->where('status', 'posted'); })->sum('debit_amount');
            $credits = JournalLine::where('account_id', $a->id)->whereHas('journal', function ($q) { $q->where('status', 'posted'); })->sum('credit_amount');
            $balance = ($a->normal_balance === 'debit') ? ($debits - $credits) : ($credits - $debits);
            $balances[$a->id] = ['code' => $a->account_code, 'debits' => (float)$debits, 'credits' => (float)$credits, 'balance' => (float)$balance];
        }
        return response()->json(['balances' => $balances]);
    }

    public function apiSuggestAccount($companyId, Request $request)
    {
        $desc = $request->input('description', '');
        $rules = AllocationRule::where('company_id', $companyId)->where('is_active', 1)->with('account')->orderBy('priority', 'desc')->get();
        $match = $this->suggestAccount($rules, $desc);
        if ($match) return response()->json(['account_id' => $match->account_id, 'account_code' => $match->account->account_code, 'account_name' => $match->account->account_name, 'vat_type' => $match->vat_type]);
        return response()->json(['account_id' => null]);
    }

    // ===== PDF BANK STATEMENT PARSING =====

    public function apiParsePdf(Request $request, $companyId, $bankId)
    {
        $pages = $request->input('pages', []);
        $ocrPages = $request->input('ocr_pages', []);
        $bankType = strtolower($request->input('bank_type', 'fnb'));
        if (empty($pages)) return response()->json(['error' => 'No text data received.'], 400);

        switch ($bankType) {
            case 'fnb': case 'first national bank': $result = $this->parseFnbText($pages, $ocrPages); break;
            case 'nedbank': $result = $this->parseNedbankText($pages); break;
            case 'absa': $result = $this->parseAbsaText($pages); break;
            case 'capitec bank': case 'capitec': $result = $this->parseCapitecText($pages); break;
            case 'standard bank': case 'standard': $result = $this->parseStandardText($pages); break;
            default: return response()->json(['error' => 'Parser for "' . $bankType . '" is not available. Supported: FNB, Nedbank, ABSA, Capitec, Standard Bank.'], 400);
        }
        return response()->json($result);
    }

    public function bankImportPdfSave(Request $request, $companyId, $bankId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccount = BankAccount::where('company_id', $companyId)->findOrFail($bankId);
        $transactions = $request->input('transactions', []);
        if (empty($transactions)) return response()->json(['error' => 'No transactions to import.'], 400);

        $batchRef = 'PDF-' . date('YmdHis');
        $now = now();
        $count = 0;

        foreach ($transactions as $txn) {
            $date = $txn['date'] ?? null;
            $desc = trim($txn['description'] ?? '');
            $amount = floatval($txn['amount'] ?? 0);
            $balance = isset($txn['balance']) ? floatval($txn['balance']) : null;
            if (empty($desc) || $amount == 0 || empty($date)) continue;

            $direction = $amount >= 0 ? 'credit' : 'debit';
            $absAmount = abs($amount);

            BankTransaction::create([
                'company_id' => $companyId,
                'bank_account_id' => $bankId,
                'transaction_date' => $date,
                'description' => $desc,
                'amount' => $absAmount,
                'direction' => $direction,
                'balance' => $balance,
                'status' => 'unallocated',
                'batch_ref' => $batchRef,
                'imported_at' => $now,
            ]);
            $count++;
        }

        // Create statement register entry
        $header = $request->input('header', []);
        $summary = $request->input('summary', []);
        $filename = $request->input('filename', '');

        $periodFrom = null;
        $periodTo = null;
        if (!empty($header['statement_period'])) {
            $periodParts = preg_split('/\s*(?:to|-)\s*/i', $header['statement_period']);
            if (count($periodParts) >= 2) {
                try { $periodFrom = \Carbon\Carbon::parse(trim($periodParts[0])); } catch (\Exception $e) {}
                try { $periodTo = \Carbon\Carbon::parse(trim($periodParts[1])); } catch (\Exception $e) {}
            }
        }

        $stmtName = $bankAccount->bank_name . ' Statement';
        if ($periodFrom && $periodTo) {
            $stmtName = $bankAccount->bank_name . ' - ' . $periodFrom->format('M Y');
        }

        BankStatement::create([
            'company_id' => $companyId,
            'bank_account_id' => $bankId,
            'statement_name' => $stmtName,
            'statement_number' => $header['statement_number'] ?? null,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'upload_date' => now(),
            'original_filename' => $filename ?: null,
            'transaction_count' => $count,
            'opening_balance' => floatval($header['opening_balance'] ?? 0),
            'closing_balance' => floatval($header['closing_balance'] ?? 0),
            'total_credits' => floatval($summary['total_credits'] ?? 0),
            'total_debits' => floatval($summary['total_debits'] ?? 0),
            'credit_count' => intval($summary['credit_count'] ?? 0),
            'debit_count' => intval($summary['debit_count'] ?? 0),
            'batch_ref' => $batchRef,
            'status' => 'imported',
            'uploaded_by' => auth()->id(),
            'imported_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'count' => $count, 'redirect' => route('cimsaccounts.statements.index', [$companyId, $bankId])]);
    }

    // ===== FNB PARSER =====

    private function parseFnbText(array $pages, array $ocrPages = []): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $processedLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^(Cr|Dr)$/i', $trimmed) && !empty($processedLines)) {
                $processedLines[count($processedLines) - 1] .= $trimmed;
            } else {
                $processedLines[] = $line;
            }
        }
        $lines = $processedLines;

        $header = $this->parseFnbHeader($lines);
        $transactions = $this->parseFnbTransactions($lines, $header);

        if (!empty($ocrPages)) {
            $ocrTxns = $this->parseFnbOcrTransactions($ocrPages);

            $ocrUsed = [];
            foreach ($transactions as $txn) {
                if (!empty($txn['description'])) {
                    foreach ($ocrTxns as $oi => $ocrTxn) {
                        if (isset($ocrUsed[$oi])) continue;
                        if ($txn['date'] === $ocrTxn['date'] && abs(abs($txn['amount']) - abs($ocrTxn['amount'])) < 0.01) {
                            $ocrUsed[$oi] = true;
                            break;
                        }
                    }
                }
            }

            foreach ($transactions as &$txn) {
                if (empty($txn['description'])) {
                    foreach ($ocrTxns as $oi => $ocrTxn) {
                        if (isset($ocrUsed[$oi])) continue;
                        if ($txn['date'] === $ocrTxn['date'] && abs(abs($txn['amount']) - abs($ocrTxn['amount'])) < 0.01 && !empty($ocrTxn['description'])) {
                            $txn['description'] = $ocrTxn['description'];
                            $ocrUsed[$oi] = true;
                            break;
                        }
                    }
                }
            }
            unset($txn);

            foreach ($transactions as &$txn) {
                if (empty($txn['description'])) {
                    foreach ($ocrTxns as $oi => $ocrTxn) {
                        if (isset($ocrUsed[$oi])) continue;
                        if ($txn['date'] === $ocrTxn['date'] && !empty($ocrTxn['description'])) {
                            $txn['description'] = $ocrTxn['description'];
                            $ocrUsed[$oi] = true;
                            break;
                        }
                    }
                }
            }
            unset($txn);
        }

        $lastDesc = '';
        $bankCharges = $header['bank_charges'] ?? [];
        foreach ($transactions as &$txn) {
            if (!empty($txn['description'])) { $lastDesc = $txn['description']; continue; }
            $absAmt = abs($txn['amount']);
            $found = false;
            foreach ($bankCharges as $cAmt => $cName) {
                if (abs($absAmt - $cAmt) < 0.01) { $txn['description'] = $cName; $found = true; break; }
            }
            if (!$found) {
                $txn['description'] = $lastDesc ? $lastDesc . ' (cont.)' : 'Bank charge';
            }
        }
        unset($txn);

        return $this->buildParseResult($header, $transactions);
    }

    private function parseFnbOcrTransactions(array $ocrPages): array
    {
        $ocrTxns = [];
        $months = ['jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','may'=>'05','jun'=>'06','jul'=>'07','aug'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dec'=>'12'];

        $allOcrText = implode("\n", $ocrPages);
        $endYear = '';
        $startYear = '';
        $startMonth = 0;
        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\s+to\s+(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $allOcrText, $pm)) {
            $startYear = $pm[3]; $endYear = $pm[6];
            $startMonth = intval($months[strtolower(substr($pm[2], 0, 3))] ?? 0);
        } elseif (preg_match('/(\d{4})/', $allOcrText, $pm)) {
            $startYear = $pm[1]; $endYear = $pm[1];
        }
        if (!$endYear) { $endYear = date('Y'); $startYear = $endYear; }

        $lines = explode("\n", $allOcrText);
        foreach ($lines as $line) {
            $t = trim($line);
            if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3})\s+[|(\[]?\s*(.+?)\s+([\d,]+(?:\.\d{2})?)\s*(?:Cr|Dr)?\s*[|]?\s*([\d,]+\.\d{2})\s*(?:Cr|Dr)?/', $t, $m)) {
                $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $mon = $months[strtolower($m[2])] ?? '01';
                $monInt = intval($mon);
                if ($startYear !== $endYear) {
                    $txnYear = ($monInt >= $startMonth) ? $startYear : $endYear;
                } else {
                    $txnYear = $endYear;
                }
                $desc = trim($m[3]);
                $desc = preg_replace('/^[^A-Za-z0-9]+\s*/', '', $desc);
                $desc = preg_replace('/\s*[|}\]]+\s*$/', '', $desc);
                $rawAmt = str_replace(',', '', $m[4]);
                $amount = (float) $rawAmt;
                if (strpos($rawAmt, '.') === false && $amount > 100) {
                    $amount = $amount / 100;
                }
                $ocrTxns[] = [
                    'date' => $txnYear . '-' . $mon . '-' . $day,
                    'description' => $desc,
                    'amount' => $amount,
                ];
            }
        }
        return $ocrTxns;
    }

    private function parseFnbHeader(array $lines): array
    {
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'opening_balance' => 0, 'closing_balance' => 0, 'bank_charges' => []];
        $fullText = implode(' ', $lines);
        if (preg_match('/(?:Platinum\s+Business\s+Account|Business\s+Account|Cheque\s+Account)\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        elseif (preg_match('/Account\s*(?:No|Number|#)?\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        if (preg_match('/Universal\s+Branch\s+Code\s*:?\s*(\d+)/i', $fullText, $m)) $header['branch_code'] = $m[1];
        if (preg_match('/Statement\s+Period\s*:?\s*(.+?)(?:Statement\s+Date|$)/i', $fullText, $m)) $header['statement_period'] = trim($m[1]);
        if (preg_match('/Statement\s+Date\s*:?\s*(\d{1,2}\s+\w+\s+\d{4})/i', $fullText, $m)) $header['statement_date'] = trim($m[1]);
        if (preg_match('/Opening\s+Balance\s+([\d,]+\.\d{2})\s*(Cr|Dr)?/i', $fullText, $m)) { $val = (float) str_replace(',', '', $m[1]); if (isset($m[2]) && strtolower($m[2]) === 'dr') $val = -$val; $header['opening_balance'] = $val; }
        if (preg_match('/Closing\s+Balance\s+([\d,]+\.\d{2})\s*(Cr|Dr)?/i', $fullText, $m)) { $val = (float) str_replace(',', '', $m[1]); if (isset($m[2]) && strtolower($m[2]) === 'dr') $val = -$val; $header['closing_balance'] = $val; }
        foreach ($lines as $line) { if (preg_match('/^\*(.+?)$/m', trim($line), $m)) { $header['account_holder'] = trim($m[1]); break; } }

        $chargeTypes = ['Service Fees', 'Cash Deposit Fees', 'Cash Handling Fees', 'Other Fees', 'Monthly Account Fee', 'Account Fee'];
        foreach ($chargeTypes as $ct) {
            if (preg_match('/' . preg_quote($ct, '/') . '\s+([\d,]+\.\d{2})\s*(Dr|Cr)?/i', $fullText, $cm)) {
                $chargeAmt = (float) str_replace(',', '', $cm[1]);
                if ($chargeAmt > 0) $header['bank_charges'][$chargeAmt] = $ct;
            }
        }

        return $header;
    }

    private function parseFnbTransactions(array $lines, array $header): array
    {
        $transactions = [];
        $months = ['jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','may'=>'05','jun'=>'06','jul'=>'07','aug'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dec'=>'12'];

        $startYear = ''; $endYear = ''; $startMonth = 0; $endMonth = 0;
        $periodStr = $header['statement_period'] . ' ' . $header['statement_date'];
        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\s+to\s+(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $periodStr, $pm)) {
            $startYear = $pm[3]; $endYear = $pm[6];
            $startMonth = intval($months[strtolower(substr($pm[2], 0, 3))] ?? 0);
            $endMonth = intval($months[strtolower(substr($pm[5], 0, 3))] ?? 0);
        } elseif (preg_match('/(\d{4})/', $periodStr, $pm)) {
            $startYear = $pm[1]; $endYear = $pm[1];
        }
        if (!$endYear) { $endYear = date('Y'); $startYear = $endYear; }

        $inTxn = false;
        $lastDescription = '';

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            if (preg_match('/Transactions\s+in\s+RAND/i', $t)) { $inTxn = true; continue; }
            if ($inTxn && preg_match('/Closing\s+Balance/i', $t)) { $inTxn = false; continue; }
            if (preg_match('/^\s*Date\s+Description\s+Amount\s+Balance/i', $t)) continue;
            if (preg_match('/^Page\s+\d+\s+of\s+\d+/i', $t)) continue;
            if (preg_match('/^Delivery\s+Method/i', $t)) continue;
            if (preg_match('/^EN:EM/i', $t)) continue;
            if (preg_match('/^\d{5,6}$/', $t)) continue;
            if (preg_match('/^(Branch\s+Number|Account\s+Number|GOLD\s+BUSINESS|DDA\s+AA|DDA\s+BH|XSTZFN|FN$)/i', $t)) continue;
            if (preg_match('/^(FNB\s+Verified|Reference\s+Number|Statements\s+\d|\$[A-Z0-9]+|PLATINUM\s+BUSINESS)/i', $t)) continue;
            if (preg_match('/^\d{4}\/\d{2}\/\d{2}/', $t)) continue;
            if (preg_match('/No\.\s+(Credit|Debit)\s+Transactions|Turnover\s+for\s+Statement/i', $t)) continue;
            if (preg_match('/Accrued\s+Bank\s+Charges/i', $t)) continue;
            if (!$inTxn) {
                if (preg_match('/Opening\s+Balance|Statement\s+Balances|Bank\s+Charges|Interest\s+Rate|Service\s+Fees|Credit\s+Rate|Debit\s+Rate|Inclusive\s+of\s+VAT|Total\s+VAT/i', $t)) continue;
                if (preg_match('/^(P\s*O\s*Box|Street\s*Address|Universal\s*Branch|Lost\s*Cards|Account\s*Enquiries|Fraud|Relationship|Customer\s*VAT|Bank\s*VAT|Statement\s*(Period|Date)|Platinum|Gold|Tax\s*Invoice)/i', $t)) continue;
                if (preg_match('/Cash\s+Deposit\s+Fees|Cash\s+Handling\s+Fees|Other\s+Fees|Overdraft\s+Limit/i', $t)) continue;
                continue;
            }


            if (preg_match('/^(\d{2}\s+[A-Za-z]{3})\s*(.+)$/', $t, $lm)) {
                $rest = trim($lm[2]);

                $rest = preg_replace('/^[^A-Za-z0-9]+\s*/', '', $rest);

                if (preg_match('/^(.+?)\s+([\d,]+\.\d{2})\s*(Cr)?\s+([\d,]+\.\d{2})\s*(Cr|Dr)?\s*(?:[\d,]+\.\d{2})?\s*$/', $rest, $tm)) {
                    $amount = (float) str_replace(',', '', $tm[2]);
                    if (empty($tm[3])) $amount = -$amount;
                    $balance = (float) str_replace(',', '', $tm[4]);
                    $balanceType = isset($tm[5]) && $tm[5] !== '' ? $tm[5] : 'Dr';
                    if ($balanceType === 'Dr') $balance = -$balance;
                    $desc = trim($tm[1]);
                    $desc = preg_replace('/\s+\d{6}\*\d{4}\s+\d{2}\s+[A-Za-z]{3}\s*$/', '', $desc);
                    $desc = preg_replace('/\s+\d{6}\*\d{4}\s*$/', '', $desc);
                    if (preg_match('/(\d{2})\s+([A-Za-z]{3})/', $lm[1], $dm)) {
                        $day = $dm[1]; $mon = $months[strtolower($dm[2])] ?? '01';
                        $monInt = intval($mon);
                        if ($startYear !== $endYear) {
                            $txnYear = ($monInt >= $startMonth) ? $startYear : $endYear;
                        } else {
                            $txnYear = $endYear;
                        }
                        $fullDate = $txnYear . '-' . $mon . '-' . $day;
                    } else { $fullDate = $endYear . '-01-01'; }
                    $lastDescription = $desc;
                    $transactions[] = ['date' => $fullDate, 'description' => $desc, 'amount' => $amount, 'balance' => $balance];

                }
                elseif (preg_match('/^\s*([\d,]+\.\d{2})\s*(Cr)?\s+([\d,]+\.\d{2})\s*(Cr|Dr)?\s*(?:[\d,]+\.\d{2})?\s*$/', $rest, $tm)) {
                    $amount = (float) str_replace(',', '', $tm[1]);
                    if (empty($tm[2])) $amount = -$amount;
                    $balance = (float) str_replace(',', '', $tm[3]);
                    $balanceType = isset($tm[4]) && $tm[4] !== '' ? $tm[4] : 'Dr';
                    if ($balanceType === 'Dr') $balance = -$balance;
                    if (preg_match('/(\d{2})\s+([A-Za-z]{3})/', $lm[1], $dm)) {
                        $day = $dm[1]; $mon = $months[strtolower($dm[2])] ?? '01';
                        $monInt = intval($mon);
                        if ($startYear !== $endYear) {
                            $txnYear = ($monInt >= $startMonth) ? $startYear : $endYear;
                        } else {
                            $txnYear = $endYear;
                        }
                        $fullDate = $txnYear . '-' . $mon . '-' . $day;
                    } else { $fullDate = $endYear . '-01-01'; }
                    $transactions[] = ['date' => $fullDate, 'description' => '', 'amount' => $amount, 'balance' => $balance];
                }
            }
        }
        return $transactions;
    }

    // ===== NEDBANK PARSER =====

    private function parseNedbankText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'opening_balance' => 0, 'closing_balance' => 0];
        $fullText = implode(' ', $lines);
        if (preg_match('/Account\s+number\s+(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        if (preg_match('/Statement\s+period:?\s*(\d{2}\/\d{2}\/\d{4}\s*[–\-]\s*\d{2}\/\d{2}\/\d{4})/i', $fullText, $m)) $header['statement_period'] = trim($m[1]);
        if (preg_match('/Opening\s+balance\s+R?([\d,]+\.\d{2})/i', $fullText, $m)) $header['opening_balance'] = (float) str_replace(',', '', $m[1]);
        if (preg_match('/Closing\s+balance\s+R?([\d,]+\.\d{2})/i', $fullText, $m)) $header['closing_balance'] = (float) str_replace(',', '', $m[1]);

        $transactions = [];
        $prevBalance = $header['opening_balance'];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            if (preg_match('/Opening\s+balance/i', $t)) { if (preg_match('/([\d,]+\.\d{2})\s*$/', $t, $m)) $prevBalance = (float) str_replace(',', '', $m[1]); continue; }
            if (preg_match('/Closing\s+balance|Balance\s+carried\s+forward/i', $t)) continue;
            if (preg_match('/^(Narrative\s+Description|Tran\s+list|Bank\s+charges|Cash\s+fees|Electronic\s+banking|Service\s+fees|Transaction\s+service|Other\s+charges|Total\s+Charges|Item\s+cost|VAT\s*\(R\)|Total\s*\(R\)|Page\s+\d|see\s+money|Nedbank\s+Ltd|We\s+subscribe|through\s+the)/i', $t)) continue;
            if (preg_match('/Account\s+(type|number|summary)|Current\s+account|Statement\s+(date|period|frequency)|Envelope|Total\s+pages/i', $t)) continue;
            if (preg_match('/Cashflow|Funds\s+received|Funds\s+used|Annual\s+credit|Bank\s+charge|VAT\s+inclusive|VAT\s+calculated/i', $t)) continue;
            if (preg_match('/Atm.teller|Electronic\s+payments|Investment\s+repayments|Transfers\s+in|Other\s+credits|Account\s+payments|Debit.stop|Electronic\s+transfers|Total\s+charges|Other\s+debits/i', $t)) continue;
            if (preg_match('/^\s*Total\s*$/i', $t)) continue;
            if (preg_match('/%\s+of\s+(funds|utilisation)/i', $t)) continue;
            if (preg_match('/eConfirm|Reg\s+No/i', $t)) continue;
            if (preg_match('/Some\s+of\s+our\s+fees|dedicated\s+to\s+keeping|Simplify\s+your|personal\.nedbank/i', $t)) continue;
            if (preg_match('/Please\s+examine|reported\s+within\s+30/i', $t)) continue;
            if (preg_match('/^\d{4,6}$/', $t)) continue;
            if (preg_match('/Fees\s*\(R\)|Debits\s*\(R\)|Credits\s*\(R\)|Balance\s*\(R\)/i', $t)) continue;
            if (!preg_match('/(\d{2}\/\d{2}\/\d{4})/', $t, $dateMatch)) continue;

            $dateStr = $dateMatch[1];
            $afterDate = trim(substr($t, strpos($t, $dateStr) + strlen($dateStr)));
            if (empty($afterDate)) continue;

            preg_match_all('/(?<![A-Za-z])([\d,]+\.\d{2})\s*\*?/', $afterDate, $allNums, PREG_OFFSET_CAPTURE);
            if (empty($allNums[1]) || count($allNums[1]) < 1) continue;

            $numEntries = $allNums[1];
            $lastEntry = end($numEntries);
            $currentBalance = (float) str_replace(',', '', $lastEntry[0]);
            $diff = round($currentBalance - $prevBalance, 2);
            if (abs($diff) < 0.01) { $prevBalance = $currentBalance; continue; }

            if (count($numEntries) >= 2) { $secondLast = $numEntries[count($numEntries) - 2]; $description = trim(substr($afterDate, 0, $secondLast[1])); }
            else { $description = trim(substr($afterDate, 0, $lastEntry[1])); }
            $description = preg_replace('/\s+/', ' ', $description);
            if (empty($description)) continue;

            $parts = explode('/', $dateStr);
            $fullDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            $transactions[] = ['date' => $fullDate, 'description' => $description, 'amount' => $diff, 'balance' => $currentBalance];
            $prevBalance = $currentBalance;
        }
        return $this->buildParseResult($header, $transactions);
    }

    // ===== ABSA PARSER =====

    private function parseAbsaText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'opening_balance' => 0, 'closing_balance' => 0];
        $fullText = implode(' ', $lines);
        if (preg_match('/Statement\s+for\s+Period\s+(\d{4}-\d{2}-\d{2}\s*-\s*\d{4}-\d{2}-\d{2})/i', $fullText, $m)) $header['statement_period'] = trim($m[1]);
        if (preg_match('/Balance\s+Brought\s+Forward\s+([\d,]+\.\d{2})/i', $fullText, $m)) $header['opening_balance'] = (float) str_replace(',', '', $m[1]);
        if (preg_match('/Balance\s+Carried\s+Forward\s+([\d,]+\.\d{2})/i', $fullText, $m)) $header['closing_balance'] = (float) str_replace(',', '', $m[1]);

        $transactions = [];
        $prevBalance = $header['opening_balance'];
        $currentTxn = null;
        $inTxn = false;

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            if (preg_match('/Balance\s+Brought\s+Forward/i', $t)) { if (preg_match('/([\d,]+\.\d{2})\s*$/', $t, $m)) $prevBalance = (float) str_replace(',', '', $m[1]); $inTxn = true; continue; }
            if (preg_match('/Balance\s+Carried\s+Forward/i', $t)) {
                if ($currentTxn !== null) { $txn = $this->finalizeBalanceDiffTxn($currentTxn, $prevBalance); if ($txn) { $transactions[] = $txn; $prevBalance = $txn['balance']; } $currentTxn = null; }
                continue;
            }
            if (!$inTxn) continue;
            if (preg_match('/Transaction\s+History|^ABSA$|Current\s+balance|Available\s+Balance|Unclaimed\s+Cheques|Statement\s+for\s+Period|^Date:?\s+Transaction|Page\s+\d+\s+of\s+\d+/i', $t)) continue;
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $t)) continue;

            if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(.+)$/', $t, $dm)) {
                if (preg_match('/^\d{2}:\d{2}:\d{2}/', $dm[2]) || preg_match('/Page\s+\d+/i', $dm[2])) continue;
                if ($currentTxn !== null) { $txn = $this->finalizeBalanceDiffTxn($currentTxn, $prevBalance); if ($txn) { $transactions[] = $txn; $prevBalance = $txn['balance']; } }
                $currentTxn = ['date' => $dm[1], 'textLines' => [$dm[2]]];
            } elseif ($currentTxn !== null) { $currentTxn['textLines'][] = $t; }
        }
        if ($currentTxn !== null) { $txn = $this->finalizeBalanceDiffTxn($currentTxn, $prevBalance); if ($txn) $transactions[] = $txn; }
        return $this->buildParseResult($header, $transactions);
    }

    // ===== CAPITEC PARSER =====

    private function parseCapitecText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'opening_balance' => 0, 'closing_balance' => 0];
        $fullText = implode(' ', $lines);
        if (preg_match('/Account\s+No\.?\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        if (preg_match('/Branch:\s*(\d+)/i', $fullText, $m)) $header['branch_code'] = $m[1];
        if (preg_match('/Balance\s+brought\s+forward\s+.*?([+-]?\d[\d ]*\.\d{2})/i', $fullText, $m)) $header['opening_balance'] = (float) str_replace(['+', ' '], '', $m[1]);

        $transactions = [];
        $currentTxn = null;
        $inTxn = false;

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            if (preg_match('/Balance\s+brought\s+forward/i', $t)) { $inTxn = true; continue; }
            if (!$inTxn) continue;
            if (preg_match('/^(Capitec\s+Bank|Branch:|Device:|Tel:|Date\s+\d{2}\/\d{2}\/\d{4}|Account\s+(type|No)|Statement\s+No|Business\s+Account\s+Statement|Telephone|Business\s+Reg|Client\s+VAT|Relationship)/i', $t)) continue;
            if (preg_match('/Page:?\s+\d+|^Post\s+Trans|^Date\s+Date\s*$/i', $t)) continue;
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}\s*$/i', $t)) continue;
            if (preg_match('/^Balance\s+[+-]?\d/i', $t) && !preg_match('/Balance\s+(brought|carried)/i', $t)) continue;
            if (preg_match('/^(Fee\s+Total|VAT\s+@|VAT\s+Total|All\s+fees|Statements\s+are|capitecbank|financial\s+services|Reg\.?\s+No|VAT\s+Reg|24hr|Neutron|No\s+Limit|Overdraft|Prime\s+Lending)/i', $t)) continue;
            if (preg_match('/^\d{1,3}\.\d{4}%\s*$/', $t)) continue;
            if (preg_match('/^(Description\s+Reference\s+Fees|Fees\s+Amount\s+Balance)/i', $t)) continue;

            if (preg_match('/^(\d{2}\/\d{2}\/\d{2})\s+(\d{2}\/\d{2}\/\d{2})\s+(.+)$/', $t, $dm)) {
                if ($currentTxn !== null) { $txn = $this->finalizeCapitecTxn($currentTxn); if ($txn) $transactions[] = $txn; }
                $parts = explode('/', $dm[1]);
                $postDate = '20' . $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                $currentTxn = ['date' => $postDate, 'textLines' => [$dm[3]]];
            } elseif ($currentTxn !== null) { $currentTxn['textLines'][] = $t; }
        }
        if ($currentTxn !== null) { $txn = $this->finalizeCapitecTxn($currentTxn); if ($txn) $transactions[] = $txn; }

        if (!empty($transactions)) { $header['closing_balance'] = end($transactions)['balance']; }
        return $this->buildParseResult($header, $transactions);
    }

    private function finalizeCapitecTxn(array $txnData): ?array
    {
        $fullText = implode(' ', $txnData['textLines']);
        preg_match_all('/[+-]?\d[\d ]*\.\d{2}(?!\d)/', $fullText, $allNums, PREG_OFFSET_CAPTURE);
        if (empty($allNums[0]) || count($allNums[0]) < 2) return null;
        $numEntries = $allNums[0];
        $lastEntry = end($numEntries);
        $secondLastEntry = $numEntries[count($numEntries) - 2];
        $balance = (float) str_replace(['+', ' '], '', $lastEntry[0]);
        $amount = (float) str_replace(['+', ' '], '', $secondLastEntry[0]);
        if (abs($amount) < 0.01) return null;

        $desc = $fullText;
        $toRemove = array_slice($numEntries, -2);
        usort($toRemove, function ($a, $b) { return $b[1] - $a[1]; });
        foreach ($toRemove as $entry) { $desc = substr_replace($desc, '', $entry[1], strlen($entry[0])); }
        $description = trim(preg_replace('/\s+/', ' ', $desc));
        if (empty($description)) return null;
        return ['date' => $txnData['date'], 'description' => $description, 'amount' => $amount, 'balance' => $balance];
    }

    // ===== STANDARD BANK PARSER =====

    private function parseStandardText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'opening_balance' => 0, 'closing_balance' => 0];
        $fullText = implode(' ', $lines);
        if (preg_match('/Account\s+number:\s*([\d\s]+)/i', $fullText, $m)) $header['account_number'] = trim($m[1]);
        if (preg_match('/Account\s+holder:\s*(.+?)(?:\s*Product|\s*Address|$)/i', $fullText, $m)) $header['account_holder'] = trim($m[1]);
        if (preg_match('/From:?\s*(\d{1,2}\s+[A-Za-z]{3}\s+\d{2,4})\s+To:?\s*(\d{1,2}\s+[A-Za-z]{3}\s+\d{2,4})/i', $fullText, $m)) $header['statement_period'] = $m[1] . ' - ' . $m[2];
        if (preg_match('/STATEMENT\s+OPENING\s+BALANCE\s+([\d,]+\.\d{2})/i', $fullText, $m)) $header['opening_balance'] = (float) str_replace(',', '', $m[1]);

        $months = ['jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','may'=>'05','jun'=>'06','jul'=>'07','aug'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dec'=>'12'];
        $transactions = [];
        $prevBalance = $header['opening_balance'];
        $currentTxn = null;
        $inTxn = false;

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;
            if (preg_match('/STATEMENT\s+OPENING\s+BALANCE/i', $t)) { if (preg_match('/([\d,]+\.\d{2})\s*$/', $t, $m)) $prevBalance = (float) str_replace(',', '', $m[1]); $inTxn = true; continue; }
            if (preg_match('/Statement\s+Summary|Please\s+verify\s+all\s+transactions/i', $t)) {
                if ($currentTxn !== null) { $txn = $this->finalizeBalanceDiffTxn($currentTxn, $prevBalance); if ($txn) $transactions[] = $txn; $currentTxn = null; }
                break;
            }
            if (!$inTxn) continue;
            if (preg_match('/Customer\s+Care|standardbank|^STANDARD\s+BANK$|^QUEENSBURGH$|month\s+statement/i', $t)) continue;
            if (preg_match('/^(From|To):?\s+\d{1,2}\s+[A-Za-z]{3}/i', $t)) continue;
            if (preg_match('/^Account\s+(number|holder|name)|^Product\s+name|^\d{5,6}$/i', $t)) continue;
            if (preg_match('/Transaction\s+details|Available\s+Balance|^Date\s+Description|^Payments\s+Deposits|Pg\s+\d+\s+of\s+\d+/i', $t)) continue;
            if (preg_match('/Standard\s+Bank\s+of\s+South|Authorised\s+financial|Registered\s+credit/i', $t)) continue;
            if (preg_match('/^R[\d,]+\.\d{2}\s*$|^Address:|^MY\/MOB|Today.s\s+debits|^Payments\s+R|^Deposits\s+R/i', $t)) continue;

            if (preg_match('/^(\d{1,2}\s+[A-Za-z]{3}\s+\d{2})\s+(.+)$/', $t, $dm)) {
                if (preg_match('/^\d{1,2}\s+[A-Za-z]{3}\s+\d{4}/', $t)) continue;
                if ($currentTxn !== null) { $txn = $this->finalizeBalanceDiffTxn($currentTxn, $prevBalance); if ($txn) { $transactions[] = $txn; $prevBalance = $txn['balance']; } }
                if (preg_match('/(\d{1,2})\s+([A-Za-z]{3})\s+(\d{2})/', $dm[1], $dp)) {
                    $fullDate = '20' . $dp[3] . '-' . ($months[strtolower($dp[2])] ?? '01') . '-' . str_pad($dp[1], 2, '0', STR_PAD_LEFT);
                } else { $fullDate = '2023-01-01'; }
                $currentTxn = ['date' => $fullDate, 'textLines' => [$dm[2]]];
            } elseif ($currentTxn !== null) { $currentTxn['textLines'][] = $t; }
        }
        if ($currentTxn !== null) { $txn = $this->finalizeBalanceDiffTxn($currentTxn, $prevBalance); if ($txn) $transactions[] = $txn; }

        if (!empty($transactions)) { $header['closing_balance'] = end($transactions)['balance']; }
        return $this->buildParseResult($header, $transactions);
    }

    // ===== SHARED HELPERS =====

    private function finalizeBalanceDiffTxn(array $txnData, float $prevBalance): ?array
    {
        $fullText = implode(' ', $txnData['textLines']);
        preg_match_all('/-?[\d,]+\.\d{2}/', $fullText, $allNums, PREG_OFFSET_CAPTURE);
        if (empty($allNums[0]) || count($allNums[0]) < 1) return null;
        $numEntries = $allNums[0];
        $lastEntry = end($numEntries);
        $currentBalance = (float) str_replace(',', '', $lastEntry[0]);
        $diff = round($currentBalance - $prevBalance, 2);
        if (abs($diff) < 0.01) return null;

        $desc = $fullText;
        $removeCount = min(2, count($numEntries));
        $toRemove = array_slice($numEntries, -$removeCount);
        usort($toRemove, function ($a, $b) { return $b[1] - $a[1]; });
        foreach ($toRemove as $entry) { $desc = substr_replace($desc, '', $entry[1], strlen($entry[0])); }
        $description = trim(preg_replace('/\s+/', ' ', $desc));
        if (empty($description)) return null;
        return ['date' => $txnData['date'], 'description' => $description, 'amount' => $diff, 'balance' => $currentBalance];
    }

    private function buildParseResult(array $header, array $transactions): array
    {
        $totalCredits = 0; $totalDebits = 0; $creditCount = 0; $debitCount = 0;
        foreach ($transactions as $txn) {
            if ($txn['amount'] > 0) { $totalCredits += $txn['amount']; $creditCount++; }
            else { $totalDebits += abs($txn['amount']); $debitCount++; }
        }
        $calculatedClosing = $header['opening_balance'] + $totalCredits - $totalDebits;
        $closingBalance = $header['closing_balance'] ?: (empty($transactions) ? 0 : end($transactions)['balance']);
        $balanceMatch = abs($calculatedClosing - $closingBalance) < 0.02;

        return [
            'header' => $header,
            'transactions' => $transactions,
            'summary' => [
                'transaction_count' => count($transactions), 'credit_count' => $creditCount, 'debit_count' => $debitCount,
                'total_credits' => round($totalCredits, 2), 'total_debits' => round($totalDebits, 2),
                'calculated_closing' => round($calculatedClosing, 2), 'balance_match' => $balanceMatch,
            ],
        ];
    }

    // =====================================================
    // MANAGEMENT ACCOUNTS
    // =====================================================

    public function maSimpleRealloc(Request $request, $companyId)
    {
        try {
            $request->validate([
                'journal_id' => 'required|integer',
                'new_account_id' => 'required|exists:cims_accounts_chart,id',
            ]);

            $origJournal = Journal::where('company_id', $companyId)
                ->where('status', 'posted')
                ->with('lines')
                ->findOrFail($request->journal_id);

            $newAccount = ChartOfAccount::where('company_id', $companyId)->findOrFail($request->new_account_id);

            $oldAccountId = null;
            $amount = 0;
            $origLineId = null;
            foreach ($origJournal->lines as $line) {
                $acct = ChartOfAccount::find($line->account_id);
                if ($acct && !in_array($acct->account_type, ['asset'])) {
                    $oldAccountId = $line->account_id;
                    $origLineId = $line->id;
                    $amount = max((float)$line->debit_amount, (float)$line->credit_amount);
                    break;
                }
            }

            if (!$oldAccountId || $amount <= 0) {
                return response()->json(['success' => false, 'error' => 'Could not determine source account or amount.'], 422);
            }

            if ($origLineId) {
                $origLine = JournalLine::find($origLineId);
                if ($origLine) {
                    $origLine->ma_hidden = 1;
                    $origLine->note = 'Moved to ' . ChartOfAccount::find($request->new_account_id)->account_name;
                    $origLine->save();
                }
            }

            $oldAccount = ChartOfAccount::find($oldAccountId);
            $oldName = $oldAccount ? $oldAccount->account_name : 'Unknown';
            $newName = $newAccount->account_name;
            $moveNote = 'Moved from ' . $oldName . ' to ' . $newName;
            $origDesc = $origJournal->description ?: 'Reallocation';

            $lastJournal = Journal::where('company_id', $companyId)->orderBy('id', 'desc')->first();
            $nextNum = $lastJournal ? intval(substr($lastJournal->journal_number, 3)) + 1 : 1;
            $jnlNumber = 'JNL' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $journal = Journal::create([
                'company_id' => $companyId,
                'journal_number' => $jnlNumber,
                'journal_date' => $origJournal->journal_date,
                'reference' => 'MA-REALLOC',
                'description' => $origDesc,
                'source' => 'management_accounts',
                'status' => 'posted',
                'total_debit' => $amount,
                'total_credit' => $amount,
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            JournalLine::create([
                'journal_id' => $journal->id,
                'account_id' => $newAccount->id,
                'description' => $origDesc,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'vat_amount' => 0,
                'vat_type' => 'none',
                'line_order' => 1,
                'note' => $moveNote,
            ]);

            JournalLine::create([
                'journal_id' => $journal->id,
                'account_id' => $oldAccountId,
                'description' => $origDesc,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'vat_amount' => 0,
                'vat_type' => 'none',
                'line_order' => 2,
                'note' => $moveNote,
                'ma_hidden' => 1,
            ]);

            return response()->json([
                'success' => true,
                'journal_number' => $jnlNumber,
                'description' => $moveNote,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function maToggleHide(Request $request, $companyId)
    {
        try {
            $request->validate([
                'line_id' => 'required|integer',
                'hide' => 'required|boolean',
            ]);

            $line = JournalLine::findOrFail($request->line_id);
            $journal = Journal::where('company_id', $companyId)->where('id', $line->journal_id)->firstOrFail();

            $line->ma_hidden = $request->hide ? 1 : 0;
            $line->save();

            return response()->json(['success' => true, 'hidden' => (bool)$line->ma_hidden]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function managementAccounts(Request $request, $companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $period = $request->input('period', 'this_year');
        $fyMonth = $company->financial_year_start ?: 3;

        list($dateFrom, $dateTo) = $this->resolvePeriodDates(
            $period, $request->input('date_from'), $request->input('date_to'), $fyMonth, $companyId
        );

        $compDateFrom = null;
        $compDateTo = null;
        $compPeriodLabel = '';

        $from = \Carbon\Carbon::parse($dateFrom);
        $to = \Carbon\Carbon::parse($dateTo);
        $monthsInPeriod = $from->diffInMonths($to) + 1;

        if ($monthsInPeriod <= 1) {
            $compDateFrom = $from->copy()->subMonth()->startOfMonth()->toDateString();
            $compDateTo = $from->copy()->subMonth()->endOfMonth()->toDateString();
            $compPeriodLabel = \Carbon\Carbon::parse($compDateFrom)->format('M Y');
        } elseif ($monthsInPeriod <= 3) {
            $compDateFrom = $from->copy()->subMonths(3)->toDateString();
            $compDateTo = $to->copy()->subMonths(3)->toDateString();
            $compPeriodLabel = \Carbon\Carbon::parse($compDateFrom)->format('M Y') . ' - ' . \Carbon\Carbon::parse($compDateTo)->format('M Y');
        } else {
            $compDateFrom = $from->copy()->subYear()->toDateString();
            $compDateTo = $to->copy()->subYear()->toDateString();
            $compPeriodLabel = \Carbon\Carbon::parse($compDateFrom)->format('M Y') . ' - ' . \Carbon\Carbon::parse($compDateTo)->format('M Y');
        }

        $pnlData = $this->calcPnlData($companyId, $dateFrom, $dateTo);
        $compPnlData = $this->calcPnlData($companyId, $compDateFrom, $compDateTo);

        $buildGroup = function($current, $comparison) {
            $merged = [];
            foreach ($current as $acc) {
                $key = $acc['code'];
                $compVal = 0;
                foreach ($comparison as $cAcc) {
                    if ($cAcc['code'] === $key) { $compVal = $cAcc['total']; break; }
                }
                $variance = $acc['total'] - $compVal;
                $merged[] = [
                    'id' => $acc['id'], 'name' => $acc['name'], 'code' => $acc['code'], 'group' => $acc['group'],
                    'current' => $acc['total'], 'comparison' => $compVal,
                    'variance' => $variance,
                    'variance_pct' => $compVal != 0 ? round(($variance / abs($compVal)) * 100, 1) : 0,
                ];
            }
            return collect($merged)->groupBy('group');
        };

        $revenueByGroup = $buildGroup($pnlData['revenue'], $compPnlData['revenue']);
        $cosByGroup = $buildGroup($pnlData['cos'], $compPnlData['cos']);
        $expensesByGroup = $buildGroup($pnlData['expenses'], $compPnlData['expenses']);

        $buildTotal = function($cur, $comp) {
            $variance = $cur - $comp;
            return (object)[
                'current' => $cur, 'comparison' => $comp,
                'variance' => $variance,
                'variance_pct' => $comp != 0 ? round(($variance / abs($comp)) * 100, 1) : 0,
            ];
        };

        $totalRevenue = $buildTotal($pnlData['totalRevenue'], $compPnlData['totalRevenue']);
        $totalCos = $buildTotal($pnlData['totalCos'], $compPnlData['totalCos']);
        $grossProfit = $buildTotal($pnlData['grossProfit'], $compPnlData['grossProfit']);
        $totalExpenses = $buildTotal($pnlData['totalExpenses'], $compPnlData['totalExpenses']);
        $netProfit = $buildTotal($pnlData['netProfit'], $compPnlData['netProfit']);

        $bsData = $this->calcBsData($companyId, $dateTo);
        $compBsData = $this->calcBsData($companyId, $compDateTo);

        $buildBsGroup = function($current, $comparison) {
            $merged = [];
            foreach ($current as $acc) {
                $compVal = 0;
                foreach ($comparison as $cAcc) {
                    if ($cAcc['code'] === $acc['code']) { $compVal = $cAcc['balance']; break; }
                }
                $merged[] = [
                    'id' => $acc['id'], 'name' => $acc['name'], 'code' => $acc['code'], 'group' => $acc['group'],
                    'balance' => $acc['balance'], 'comp_balance' => $compVal,
                ];
            }
            return collect($merged)->groupBy('group');
        };

        $assetsByGroup = $buildBsGroup($bsData['assets'], $compBsData['assets']);
        $liabilitiesByGroup = $buildBsGroup($bsData['liabilities'], $compBsData['liabilities']);
        $equityByGroup = $buildBsGroup($bsData['equity'], $compBsData['equity']);

        $totalAssets = (object)['current' => $bsData['totalAssets'], 'comparison' => $compBsData['totalAssets']];
        $totalLiabilities = (object)['current' => $bsData['totalLiabilities'], 'comparison' => $compBsData['totalLiabilities']];
        $totalEquity = (object)['current' => $bsData['totalEquity'], 'comparison' => $compBsData['totalEquity']];
        $retainedEarnings = (object)['current' => $bsData['retainedEarnings'], 'comparison' => $compBsData['retainedEarnings']];
        $totalLiabilitiesAndEquity = (object)['current' => $bsData['totalLiabilitiesAndEquity'], 'comparison' => $compBsData['totalLiabilitiesAndEquity']];

        $calcRatios = function($pnl, $bs) {
            $gm = $pnl['totalRevenue'] != 0 ? round(($pnl['grossProfit'] / $pnl['totalRevenue']) * 100, 1) : 0;
            $nm = $pnl['totalRevenue'] != 0 ? round(($pnl['netProfit'] / $pnl['totalRevenue']) * 100, 1) : 0;
            $currentAssets = 0; $currentLiab = 0;
            foreach ($bs['assets'] as $a) {
                if (strpos($a['code'], '1100') === 0 || strpos($a['code'], '11') === 0) $currentAssets += $a['balance'];
                elseif (substr($a['code'], 0, 2) <= '13') $currentAssets += $a['balance'];
            }
            foreach ($bs['liabilities'] as $l) {
                if (strpos($l['code'], '2100') === 0 || strpos($l['code'], '21') === 0) $currentLiab += $l['balance'];
                elseif (substr($l['code'], 0, 2) <= '22') $currentLiab += $l['balance'];
            }
            $cr = $currentLiab != 0 ? round($currentAssets / $currentLiab, 2) : 0;
            $equityTotal = $bs['totalEquity'] + $bs['retainedEarnings'];
            $de = $equityTotal != 0 ? round($bs['totalLiabilities'] / $equityTotal, 2) : 0;
            $er = $pnl['totalRevenue'] != 0 ? round(($pnl['totalExpenses'] / $pnl['totalRevenue']) * 100, 1) : 0;
            return ['gross_margin' => $gm, 'net_margin' => $nm, 'current_ratio' => $cr, 'debt_to_equity' => $de, 'expense_ratio' => $er];
        };

        $ratios = $calcRatios($pnlData, $bsData);
        $compRatios = $calcRatios($compPnlData, $compBsData);

        $bankRecons = \DB::table('cims_accounts_new_bank_recons as r')
            ->leftJoin('cims_accounts_bank_accounts as ba', 'r.bank_account_id', '=', 'ba.id')
            ->where('r.company_id', $companyId)
            ->where('r.status', 'completed')
            ->select('ba.bank_name', 'ba.account_number', 'r.statement_date', 'r.status', 'r.statement_balance', 'r.reconciled_balance', 'r.difference')
            ->orderBy('r.statement_date', 'desc')
            ->get();

        $settingsRow = \DB::table('settings')->where('settings_id', 1)->first();
        $companySettings = [
            'settings_company_name' => $settingsRow->settings_company_name ?? 'Accounting Taxation and Payroll (Pty) Ltd',
            'settings_company_address_line_1' => $settingsRow->settings_company_address_line_1 ?? '',
            'settings_company_city' => $settingsRow->settings_company_city ?? '',
            'settings_company_state' => $settingsRow->settings_company_state ?? '',
            'settings_company_zipcode' => $settingsRow->settings_company_zipcode ?? '',
        ];

        $preparedDate = \Carbon\Carbon::now()->format('j F Y');

        return view('cims_accounts::accounts.management-accounts', compact(
            'company', 'period', 'dateFrom', 'dateTo', 'compPeriodLabel',
            'revenueByGroup', 'cosByGroup', 'expensesByGroup',
            'totalRevenue', 'totalCos', 'grossProfit', 'totalExpenses', 'netProfit',
            'assetsByGroup', 'liabilitiesByGroup', 'equityByGroup',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'retainedEarnings', 'totalLiabilitiesAndEquity',
            'ratios', 'compRatios', 'bankRecons',
            'companySettings', 'preparedDate'
        ));
    }

    private function calcPnlData($companyId, $dateFrom, $dateTo)
    {
        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.ma_hidden', 0)
            ->whereBetween('j.journal_date', [$dateFrom, $dateTo])
            ->whereIn('c.account_type', ['revenue', 'cost_of_sales', 'expense'])
            ->select(
                'c.id as account_id', 'c.account_code', 'c.account_name',
                'c.account_type', 'c.parent_id',
                \DB::raw('SUM(jl.debit_amount) as total_debit'),
                \DB::raw('SUM(jl.credit_amount) as total_credit')
            )
            ->groupBy('c.id', 'c.account_code', 'c.account_name', 'c.account_type', 'c.parent_id')
            ->orderBy('c.account_code')
            ->get();

        $parentNames = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->pluck('account_name', 'id');

        $revenue = []; $cos = []; $expenses = [];
        $totalRevenue = 0; $totalCos = 0; $totalExpenses = 0;

        foreach ($lines as $line) {
            $bal = $line->account_type === 'revenue'
                ? (float)$line->total_credit - (float)$line->total_debit
                : (float)$line->total_debit - (float)$line->total_credit;

            $item = [
                'id' => $line->account_id, 'code' => $line->account_code, 'name' => $line->account_name,
                'group' => $parentNames[$line->parent_id] ?? 'Other', 'total' => $bal,
            ];

            if ($line->account_type === 'revenue') { $revenue[] = $item; $totalRevenue += $bal; }
            elseif ($line->account_type === 'cost_of_sales') { $cos[] = $item; $totalCos += $bal; }
            else { $expenses[] = $item; $totalExpenses += $bal; }
        }

        return [
            'revenue' => $revenue, 'cos' => $cos, 'expenses' => $expenses,
            'totalRevenue' => round($totalRevenue, 2), 'totalCos' => round($totalCos, 2),
            'grossProfit' => round($totalRevenue - $totalCos, 2),
            'totalExpenses' => round($totalExpenses, 2),
            'netProfit' => round($totalRevenue - $totalCos - $totalExpenses, 2),
        ];
    }

    private function calcBsData($companyId, $asAt)
    {
        $lines = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.ma_hidden', 0)
            ->where('j.journal_date', '<=', $asAt)
            ->whereIn('c.account_type', ['asset', 'liability', 'equity'])
            ->select(
                'c.id as account_id', 'c.account_code', 'c.account_name',
                'c.account_type', 'c.parent_id',
                \DB::raw('SUM(jl.debit_amount) as total_debit'),
                \DB::raw('SUM(jl.credit_amount) as total_credit')
            )
            ->groupBy('c.id', 'c.account_code', 'c.account_name', 'c.account_type', 'c.parent_id')
            ->orderBy('c.account_code')
            ->get();

        $pnl = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->join('cims_accounts_chart as c', 'jl.account_id', '=', 'c.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.ma_hidden', 0)
            ->where('j.journal_date', '<=', $asAt)
            ->whereIn('c.account_type', ['revenue', 'cost_of_sales', 'expense'])
            ->select(\DB::raw('SUM(jl.debit_amount) as total_debit'), \DB::raw('SUM(jl.credit_amount) as total_credit'))
            ->first();

        $retainedEarnings = $pnl ? round((float)$pnl->total_credit - (float)$pnl->total_debit, 2) : 0;

        $level2Names = ChartOfAccount::where('company_id', $companyId)
            ->where('account_level', 2)->pluck('account_name', 'id');

        $assets = []; $liabilities = []; $equity = [];
        $totalAssets = 0; $totalLiabilities = 0; $totalEquity = 0;

        foreach ($lines as $line) {
            $bal = $line->account_type === 'asset'
                ? (float)$line->total_debit - (float)$line->total_credit
                : (float)$line->total_credit - (float)$line->total_debit;

            if (abs($bal) < 0.01) continue;

            $item = [
                'id' => $line->account_id, 'code' => $line->account_code, 'name' => $line->account_name,
                'group' => $level2Names[$line->parent_id] ?? 'Other', 'balance' => $bal,
            ];

            if ($line->account_type === 'asset') { $assets[] = $item; $totalAssets += $bal; }
            elseif ($line->account_type === 'liability') { $liabilities[] = $item; $totalLiabilities += $bal; }
            else { $equity[] = $item; $totalEquity += $bal; }
        }

        return [
            'assets' => $assets, 'liabilities' => $liabilities, 'equity' => $equity,
            'totalAssets' => round($totalAssets, 2), 'totalLiabilities' => round($totalLiabilities, 2),
            'totalEquity' => round($totalEquity, 2), 'retainedEarnings' => $retainedEarnings,
            'totalLiabilitiesAndEquity' => round($totalLiabilities + $totalEquity + $retainedEarnings, 2),
        ];
    }

    // =====================================================
    // NEW BANK RECONCILIATION
    // =====================================================

    public function newBankRecon($companyId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', 1)->get();
        return view('cims_accounts::accounts.new-bank-recon', compact('company', 'bankAccounts'));
    }

    public function newBankReconLoad(Request $request, $companyId)
    {
        $bankAccountId = $request->input('bank_account_id');
        $statementDate = $request->input('statement_date');
        $statementBalance = (float)$request->input('statement_balance', 0);
        $reference = $request->input('reference', '');
        $loadReconId = $request->input('recon_id');

        $company = AccountsCompany::findOrFail($companyId);

        if ($loadReconId) {
            $recon = \DB::table('cims_accounts_new_bank_recons')
                ->where('id', $loadReconId)->where('company_id', $companyId)->first();
            if (!$recon) {
                return response()->json(['error' => 'Recon session not found.']);
            }
            $reconId = $recon->id;
            $bankAccountId = $recon->bank_account_id;
            $statementDate = $recon->statement_date;
            $statementBalance = (float)$recon->statement_balance;
            $isCompleted = $recon->status === 'completed';
        } else {
            $isCompleted = false;
        }

        $bankAccount = BankAccount::with('glAccount')->findOrFail($bankAccountId);

        if (!$bankAccount->account_id) {
            return response()->json(['error' => 'This bank account has no linked GL account.']);
        }

        if (!$loadReconId) {
            $recon = \DB::table('cims_accounts_new_bank_recons')
                ->where('company_id', $companyId)
                ->where('bank_account_id', $bankAccountId)
                ->where('statement_date', $statementDate)
                ->whereIn('status', ['draft', 'in_progress', 'balanced'])
                ->first();

            if (!$recon) {
                $reconId = \DB::table('cims_accounts_new_bank_recons')->insertGetId([
                    'company_id' => $companyId,
                    'bank_account_id' => $bankAccountId,
                    'statement_date' => $statementDate,
                    'statement_balance' => $statementBalance,
                    'reference' => $reference,
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $reconId = $recon->id;
                $updateData = [
                    'statement_balance' => $statementBalance,
                    'updated_at' => now(),
                ];
                if ($reference) $updateData['reference'] = $reference;
                \DB::table('cims_accounts_new_bank_recons')->where('id', $reconId)->update($updateData);
            }
        }

        $glBal = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $statementDate)
            ->select(
                \DB::raw('COALESCE(SUM(jl.debit_amount), 0) as total_debit'),
                \DB::raw('COALESCE(SUM(jl.credit_amount), 0) as total_credit')
            )->first();
        $glBalance = round((float)$glBal->total_debit - (float)$glBal->total_credit, 2);

        if ($isCompleted) {
            $reconLineIds = \DB::table('cims_accounts_new_bank_recon_lines')
                ->where('recon_id', $reconId)
                ->where('is_reconciled', 1)
                ->pluck('journal_line_id')
                ->toArray();

            $transactions = \DB::table('cims_accounts_journal_lines as jl')
                ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
                ->whereIn('jl.id', $reconLineIds)
                ->select('j.id as journal_id', 'jl.id as line_id', 'j.journal_number', 'j.journal_date',
                         'j.description as journal_desc', 'jl.description as line_desc',
                         'jl.debit_amount', 'jl.credit_amount', 'j.reference', 'j.source')
                ->orderBy('j.journal_date')
                ->orderBy('j.id')
                ->get();

            foreach ($transactions as $txn) {
                $txn->amount = round((float)$txn->debit_amount - (float)$txn->credit_amount, 2);
                $txn->is_reconciled = 1;
            }

            return response()->json([
                'success' => true,
                'recon_id' => $reconId,
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'gl_balance' => (float)$recon->gl_balance,
                'statement_balance' => $statementBalance,
                'reconciled_total' => (float)$recon->reconciled_balance,
                'outstanding_total' => (float)$recon->outstanding_balance,
                'difference' => (float)$recon->difference,
                'recon_count' => $transactions->count(),
                'total_count' => $transactions->count(),
                'status' => 'Completed',
                'is_completed' => true,
                'statement_date' => $statementDate,
                'transactions' => $transactions->values()->toArray(),
            ]);
        }

        $completedLineIds = \DB::table('cims_accounts_new_bank_recon_lines as rl')
            ->join('cims_accounts_new_bank_recons as r', 'rl.recon_id', '=', 'r.id')
            ->where('r.company_id', $companyId)
            ->where('r.bank_account_id', $bankAccountId)
            ->where('r.status', 'completed')
            ->where('r.id', '!=', $reconId)
            ->where('rl.is_reconciled', 1)
            ->pluck('rl.journal_line_id')
            ->toArray();

        $query = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $statementDate)
            ->select('j.id as journal_id', 'jl.id as line_id', 'j.journal_number', 'j.journal_date',
                     'j.description as journal_desc', 'jl.description as line_desc',
                     'jl.debit_amount', 'jl.credit_amount', 'j.reference', 'j.source')
            ->orderBy('j.journal_date')
            ->orderBy('j.id');

        if (!empty($completedLineIds)) {
            $query->whereNotIn('jl.id', $completedLineIds);
        }

        $transactions = $query->get();

        $currentReconLines = \DB::table('cims_accounts_new_bank_recon_lines')
            ->where('recon_id', $reconId)
            ->pluck('is_reconciled', 'journal_line_id')
            ->toArray();

        $reconciledTotal = 0;
        $outstandingTotal = 0;
        $reconCount = 0;

        foreach ($transactions as $txn) {
            $txn->amount = round((float)$txn->debit_amount - (float)$txn->credit_amount, 2);
            $txn->is_reconciled = isset($currentReconLines[$txn->line_id]) ? (int)$currentReconLines[$txn->line_id] : 0;

            if ($txn->is_reconciled) {
                $reconciledTotal += $txn->amount;
                $reconCount++;
            } else {
                $outstandingTotal += $txn->amount;
            }
        }

        $reconciledTotal = round($reconciledTotal, 2);
        $outstandingTotal = round($outstandingTotal, 2);

        $prevReconciledTotal = (float)\DB::table('cims_accounts_new_bank_recon_lines as rl')
            ->join('cims_accounts_journal_lines as jl', 'rl.journal_line_id', '=', 'jl.id')
            ->join('cims_accounts_new_bank_recons as r', 'rl.recon_id', '=', 'r.id')
            ->where('r.company_id', $companyId)
            ->where('r.bank_account_id', $bankAccountId)
            ->where('r.status', 'completed')
            ->where('r.id', '!=', $reconId)
            ->where('rl.is_reconciled', 1)
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $prevReconciledTotal = round($prevReconciledTotal, 2);

        $cumulativeReconciled = round($prevReconciledTotal + $reconciledTotal, 2);
        $difference = round($statementBalance - $cumulativeReconciled, 2);

        $status = 'draft';
        if ($reconCount > 0) $status = 'in_progress';
        if ($reconCount > 0 && abs($difference) < 0.01) $status = 'balanced';

        \DB::table('cims_accounts_new_bank_recons')->where('id', $reconId)->update([
            'gl_balance' => $glBalance,
            'reconciled_balance' => $reconciledTotal,
            'outstanding_balance' => $outstandingTotal,
            'difference' => $difference,
            'status' => $status,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'recon_id' => $reconId,
            'bank_name' => $bankAccount->bank_name,
            'account_number' => $bankAccount->account_number,
            'gl_balance' => $glBalance,
            'statement_balance' => $statementBalance,
            'reconciled_total' => $reconciledTotal,
            'outstanding_total' => $outstandingTotal,
            'difference' => $difference,
            'recon_count' => $reconCount,
            'total_count' => $transactions->count(),
            'status' => ucfirst(str_replace('_', ' ', $status)),
            'is_completed' => false,
            'transactions' => $transactions->values()->toArray(),
        ]);
    }

    public function newBankReconToggle(Request $request, $companyId)
    {
        $reconId = $request->input('recon_id');
        $lineId = $request->input('line_id');
        $journalId = $request->input('journal_id');
        $checked = $request->input('checked') ? 1 : 0;

        $recon = \DB::table('cims_accounts_new_bank_recons')
            ->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        $jl = \DB::table('cims_accounts_journal_lines')->where('id', $lineId)->first();
        $amount = $jl ? round((float)$jl->debit_amount - (float)$jl->credit_amount, 2) : 0;

        $existing = \DB::table('cims_accounts_new_bank_recon_lines')
            ->where('recon_id', $reconId)->where('journal_line_id', $lineId)->first();

        if ($existing) {
            \DB::table('cims_accounts_new_bank_recon_lines')->where('id', $existing->id)->update([
                'is_reconciled' => $checked,
                'reconciled_at' => $checked ? now() : null,
                'updated_at' => now(),
            ]);
        } else {
            \DB::table('cims_accounts_new_bank_recon_lines')->insert([
                'recon_id' => $reconId,
                'journal_id' => $journalId ?: 0,
                'journal_line_id' => $lineId,
                'amount' => $amount,
                'is_reconciled' => $checked,
                'reconciled_at' => $checked ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this->recalcNewBankRecon($companyId, $reconId);
    }

    public function newBankReconBulkToggle(Request $request, $companyId)
    {
        $reconId = $request->input('recon_id');
        $action = $request->input('action');

        $recon = \DB::table('cims_accounts_new_bank_recons')
            ->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        $bankAccount = BankAccount::find($recon->bank_account_id);
        $checked = $action === 'select_all' ? 1 : 0;

        $completedLineIds = \DB::table('cims_accounts_new_bank_recon_lines as rl')
            ->join('cims_accounts_new_bank_recons as r', 'rl.recon_id', '=', 'r.id')
            ->where('r.company_id', $companyId)
            ->where('r.bank_account_id', $recon->bank_account_id)
            ->where('r.status', 'completed')
            ->where('r.id', '!=', $reconId)
            ->where('rl.is_reconciled', 1)
            ->pluck('rl.journal_line_id')
            ->toArray();

        $query = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $recon->statement_date)
            ->select('j.id as journal_id', 'jl.id as line_id', 'jl.debit_amount', 'jl.credit_amount');

        if (!empty($completedLineIds)) {
            $query->whereNotIn('jl.id', $completedLineIds);
        }

        $lines = $query->get();

        foreach ($lines as $l) {
            $amount = round((float)$l->debit_amount - (float)$l->credit_amount, 2);
            $existing = \DB::table('cims_accounts_new_bank_recon_lines')
                ->where('recon_id', $reconId)->where('journal_line_id', $l->line_id)->first();

            if ($existing) {
                \DB::table('cims_accounts_new_bank_recon_lines')->where('id', $existing->id)->update([
                    'is_reconciled' => $checked,
                    'reconciled_at' => $checked ? now() : null,
                    'updated_at' => now(),
                ]);
            } else {
                \DB::table('cims_accounts_new_bank_recon_lines')->insert([
                    'recon_id' => $reconId,
                    'journal_id' => $l->journal_id,
                    'journal_line_id' => $l->line_id,
                    'amount' => $amount,
                    'is_reconciled' => $checked,
                    'reconciled_at' => $checked ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $this->recalcNewBankRecon($companyId, $reconId);
    }

    public function newBankReconSave(Request $request, $companyId)
    {
        $reconId = $request->input('recon_id');
        $action = $request->input('action');

        $recon = \DB::table('cims_accounts_new_bank_recons')
            ->where('id', $reconId)->where('company_id', $companyId)->first();
        if (!$recon) return response()->json(['error' => 'Not found'], 404);

        if ($action === 'complete') {
            \DB::table('cims_accounts_new_bank_recons')->where('id', $reconId)->update([
                'status' => 'completed',
                'reconciled_by' => auth()->id(),
                'reconciled_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function newBankReconList(Request $request, $companyId)
    {
        $recons = \DB::table('cims_accounts_new_bank_recons as r')
            ->leftJoin('cims_accounts_bank_accounts as ba', 'r.bank_account_id', '=', 'ba.id')
            ->where('r.company_id', $companyId)
            ->select(
                'r.id', 'r.bank_account_id', 'r.statement_date', 'r.statement_balance', 'r.reference',
                'r.gl_balance', 'r.status', 'r.reconciled_balance', 'r.outstanding_balance',
                'r.difference', 'r.created_at',
                'ba.bank_name', 'ba.account_number'
            )
            ->orderBy('r.statement_date', 'asc')
            ->orderBy('r.id', 'asc')
            ->get();

        $prevClosing = [];
        foreach ($recons as $r) {
            $r->line_count = \DB::table('cims_accounts_new_bank_recon_lines')
                ->where('recon_id', $r->id)->where('is_reconciled', 1)->count();

            $bankKey = $r->bank_account_id;
            $r->opening_balance = isset($prevClosing[$bankKey]) ? $prevClosing[$bankKey] : 0;
            if ($r->status === 'completed') {
                $prevClosing[$bankKey] = (float)$r->statement_balance;
            }
        }

        $recons = $recons->sortByDesc('statement_date')->values();

        return response()->json(['success' => true, 'recons' => $recons]);
    }

    public function newBankReconReset(Request $request, $companyId)
    {
        $reconIds = $request->input('recon_ids', []);

        if (empty($reconIds)) {
            return response()->json(['error' => 'No recon sessions selected.'], 400);
        }

        $totalLines = 0;
        $totalRecons = 0;

        foreach ($reconIds as $reconId) {
            $recon = \DB::table('cims_accounts_new_bank_recons')
                ->where('id', $reconId)->where('company_id', $companyId)->first();
            if (!$recon) continue;

            $lineCount = \DB::table('cims_accounts_new_bank_recon_lines')
                ->where('recon_id', $reconId)->count();

            \DB::table('cims_accounts_new_bank_recon_lines')->where('recon_id', $reconId)->delete();
            \DB::table('cims_accounts_new_bank_recons')->where('id', $reconId)->delete();

            $totalLines += $lineCount;
            $totalRecons++;
        }

        return response()->json([
            'success' => true,
            'message' => $totalRecons . ' recon session(s) reset. ' . $totalLines . ' reconciled lines cleared.',
        ]);
    }

    private function recalcNewBankRecon($companyId, $reconId)
    {
        $recon = \DB::table('cims_accounts_new_bank_recons')->where('id', $reconId)->first();
        $bankAccount = BankAccount::find($recon->bank_account_id);

        $completedLineIds = \DB::table('cims_accounts_new_bank_recon_lines as rl')
            ->join('cims_accounts_new_bank_recons as r', 'rl.recon_id', '=', 'r.id')
            ->where('r.company_id', $companyId)
            ->where('r.bank_account_id', $recon->bank_account_id)
            ->where('r.status', 'completed')
            ->where('r.id', '!=', $reconId)
            ->where('rl.is_reconciled', 1)
            ->pluck('rl.journal_line_id')
            ->toArray();

        $countQuery = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $recon->statement_date);

        if (!empty($completedLineIds)) {
            $countQuery->whereNotIn('jl.id', $completedLineIds);
        }
        $totalCount = $countQuery->count();

        $glBal = \DB::table('cims_accounts_journal_lines as jl')
            ->join('cims_accounts_journals as j', 'jl.journal_id', '=', 'j.id')
            ->where('j.company_id', $companyId)
            ->where('j.status', 'posted')
            ->where('jl.account_id', $bankAccount->account_id)
            ->where('j.journal_date', '<=', $recon->statement_date)
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $glBalance = round((float)$glBal, 2);

        $reconciledSum = \DB::table('cims_accounts_new_bank_recon_lines as rl')
            ->join('cims_accounts_journal_lines as jl', 'rl.journal_line_id', '=', 'jl.id')
            ->where('rl.recon_id', $reconId)
            ->where('rl.is_reconciled', 1)
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $reconciledSum = round((float)$reconciledSum, 2);

        $reconCount = \DB::table('cims_accounts_new_bank_recon_lines')
            ->where('recon_id', $reconId)->where('is_reconciled', 1)->count();

        $stmtBal = (float)$recon->statement_balance;
        $outstanding = round($glBalance - $reconciledSum, 2);

        // Add previously completed reconciled totals (carried forward)
        $prevReconciledTotal = (float)\DB::table('cims_accounts_new_bank_recon_lines as rl')
            ->join('cims_accounts_journal_lines as jl', 'rl.journal_line_id', '=', 'jl.id')
            ->join('cims_accounts_new_bank_recons as r', 'rl.recon_id', '=', 'r.id')
            ->where('r.company_id', $companyId)
            ->where('r.bank_account_id', $recon->bank_account_id)
            ->where('r.status', 'completed')
            ->where('r.id', '!=', $reconId)
            ->where('rl.is_reconciled', 1)
            ->select(\DB::raw('COALESCE(SUM(jl.debit_amount - jl.credit_amount), 0) as total'))
            ->value('total');
        $prevReconciledTotal = round($prevReconciledTotal, 2);

        $cumulativeReconciled = round($prevReconciledTotal + $reconciledSum, 2);
        $difference = round($stmtBal - $cumulativeReconciled, 2);

        $status = 'draft';
        if ($reconCount > 0) $status = 'in_progress';
        if ($reconCount > 0 && abs($difference) < 0.01) $status = 'balanced';

        \DB::table('cims_accounts_new_bank_recons')->where('id', $reconId)->update([
            'gl_balance' => $glBalance,
            'reconciled_balance' => $reconciledSum,
            'outstanding_balance' => $outstanding,
            'difference' => $difference,
            'status' => $status,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'gl_balance' => $glBalance,
            'statement_balance' => $stmtBal,
            'reconciled_total' => $reconciledSum,
            'outstanding_total' => $outstanding,
            'difference' => $difference,
            'recon_count' => $reconCount,
            'total_count' => $totalCount,
            'status' => ucfirst(str_replace('_', ' ', $status)),
        ]);
    }

    public function cipcDashboardView()
    {
        return view('smartdash.cipc.dashboard');
    }

    public function sarsDashboardView()
    {
        return view('smartdash.sars.dashboard');
    }

    public function payrollDashboardView()
    {
        return view('smartdash.payroll.dashboard');
    }

    public function executiveDashboardView()
    {
        return view('smartdash.executive.dashboard');
    }

    public function operationsDashboardView()
    {
        return view('smartdash.operations.dashboard');
    }

    public function executiveOverviewDashboardView()
    {
        return view('smartdash.executive_overview.dashboard');
    }

    public function inventoryDashboardView()
    {
        return view('smartdash.inventory.dashboard');
    }

    public function salesEngineDashboardView()
    {
        return view('smartdash.sales_engine.dashboard');
    }

    public function cashflowDashboardView()
    {
        return view('smartdash.cashflow.dashboard');
    }

    public function tyreSalesDashboardView()
    {
        return view('smartdash.tyre_sales.dashboard');
    }

    // ===== BANK STATEMENT REGISTER =====

    public function statementRegister($companyId, $bankId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccount = BankAccount::where('company_id', $companyId)->findOrFail($bankId);
        $statements = BankStatement::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('cims_accounts::accounts.bank-statements', compact('company', 'bankAccount', 'statements'));
    }

    public function statementView($companyId, $bankId, $statementId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $bankAccount = BankAccount::where('company_id', $companyId)->findOrFail($bankId);
        $statement = BankStatement::where('company_id', $companyId)->findOrFail($statementId);
        $transactions = BankTransaction::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->where('batch_ref', $statement->batch_ref)
            ->with('allocatedAccount')
            ->orderBy('transaction_date')
            ->get();
        return view('cims_accounts::accounts.bank-statement-view', compact('company', 'bankAccount', 'statement', 'transactions'));
    }

    public function statementUpdateStatus(Request $request, $companyId, $bankId, $statementId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $statement = BankStatement::where('company_id', $companyId)->findOrFail($statementId);
        $newStatus = $request->input('status');
        $allowed = ['imported', 'reconciled', 'archived', 'rejected'];
        if (!in_array($newStatus, $allowed)) {
            return back()->with('error', 'Invalid status.');
        }
        $statement->update(['status' => $newStatus]);
        return redirect()->route('cimsaccounts.statements.index', [$companyId, $bankId])->with('success', 'Statement status updated to ' . ucfirst($newStatus) . '.');
    }

    public function statementDelete(Request $request, $companyId, $bankId, $statementId)
    {
        $company = AccountsCompany::findOrFail($companyId);
        $statement = BankStatement::where('company_id', $companyId)->findOrFail($statementId);

        if ($statement->status === 'reconciled') {
            return back()->with('error', 'Cannot delete a reconciled statement.');
        }

        // Delete associated transactions
        BankTransaction::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->where('batch_ref', $statement->batch_ref)
            ->whereIn('status', ['unallocated', 'excluded'])
            ->delete();

        $statement->delete();
        return redirect()->route('cimsaccounts.statements.index', [$companyId, $bankId])->with('success', 'Statement removed from register.');
    }
}
