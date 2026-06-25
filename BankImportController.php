<?php

namespace Modules\nxClients\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\nxClients\Models\NexcoreClient;
use Modules\nxClients\Models\NexcoreBankAccount;
use Modules\nxClients\Models\NexcoreBankTransaction;
use Modules\nxClients\Models\NexcoreBankStatement;
use Modules\nxClients\Models\NexcoreGlJournal;
use Modules\nxClients\Models\NexcoreGlJournalLine;

class BankImportController extends Controller
{
    /**
     * Show the statement register (list of imported statements).
     */
    public function statements($clientId, $bankId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $companyId = $clientId;
        $bankAccount = NexcoreBankAccount::where('company_id', $companyId)->findOrFail($bankId);

        $statements = NexcoreBankStatement::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->orderBy('period_from', 'desc')
            ->get();

        return view('nx_clients::accounting.bank.statements', compact('client', 'bankAccount', 'statements', 'companyId'));
    }

    public function statementView($clientId, $bankId, $statementId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $companyId = $clientId;
        $bankAccount = NexcoreBankAccount::where('company_id', $companyId)->findOrFail($bankId);
        $statement = NexcoreBankStatement::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->findOrFail($statementId);

        $transactions = NexcoreBankTransaction::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->where('batch_ref', $statement->batch_ref)
            ->with('allocatedAccount')
            ->orderBy('transaction_date')
            ->get();

        return view('nx_clients::accounting.bank.statement-view', compact('client', 'bankAccount', 'statement', 'transactions', 'companyId'));
    }

    /**
     * Delete a statement and all its linked transactions.
     */
    public function destroyStatement($clientId, $bankId, $statementId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $companyId = $clientId;
        $statement = NexcoreBankStatement::where('company_id', $companyId)
            ->where('bank_account_id', $bankId)
            ->findOrFail($statementId);

        $transactions = NexcoreBankTransaction::where('batch_ref', $statement->batch_ref)->get();

        $journalIds = $transactions->pluck('journal_id')->filter()->unique()->values()->toArray();

        if (!empty($journalIds)) {
            NexcoreGlJournalLine::whereIn('journal_id', $journalIds)->delete();
            NexcoreGlJournal::whereIn('id', $journalIds)->delete();
        }

        $deletedCount = NexcoreBankTransaction::where('batch_ref', $statement->batch_ref)->delete();

        $statement->delete();

        return redirect()->route('nexcore.clients.show.accounting.bank.statements', [$clientId, $bankId])
            ->with('success', "Statement and {$deletedCount} transactions with all linked journals removed.");
    }

    /**
     * Show the bank statement import page.
     */
    public function import($clientId, $bankId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $companyId = $clientId;
        $bankAccount = NexcoreBankAccount::where('company_id', $companyId)->findOrFail($bankId);

        // Auto-detect bank type from account name
        $name = strtolower($bankAccount->bank_name ?? '');
        if (str_contains($name, 'fnb') || str_contains($name, 'first national')) {
            $bankType = 'fnb';
        } elseif (str_contains($name, 'nedbank')) {
            $bankType = 'nedbank';
        } elseif (str_contains($name, 'absa')) {
            $bankType = 'absa_transaction_history';
        // Capitec auto-detect routes to Mercantile parser by default
        // For Personal/Business, user selects from the dropdown manually
        } elseif (str_contains($name, 'capitec')) {
            $bankType = 'capitec';
        } elseif (str_contains($name, 'standard')) {
            $bankType = 'standard';
        } else {
            $bankType = 'fnb';
        }

        return view('nx_clients::cosmos-bank-import', compact('client', 'bankAccount', 'bankType', 'companyId'));
    }

    /**
     * AJAX endpoint: parse PDF text into structured transactions.
     */
    public function parsePdf(Request $request, $clientId, $bankId)
    {
        $pages = $request->input('pages', []);
        $ocrPages = $request->input('ocr_pages', []);
        $bankType = $request->input('bank_type', 'fnb');

        try {
            switch ($bankType) {
                case 'fnb':
                    $result = $this->parseFnbText($pages, $ocrPages);
                    break;
                case 'nedbank':
                    $result = $this->parseNedbankText($pages);
                    break;
                // NEDBANK ONLINE (Statement Enquiry) - Added June 2026
                case 'nedbank_online':
                    $result = $this->parseNedbankOnlineText($pages);
                    break;
                // ABSA (TRANSACTION HISTORY) - LOCKED - Tested & Working 25 May 2026
                case 'absa_transaction_history':
                    $result = $this->parseAbsaTransactionHistoryText($pages);
                    break;
                // ABSA (BANK STATEMENT) - LOCKED - Tested & Working 25 May 2026
                case 'absa_bank_statement':
                    $result = $this->parseAbsaBankStatementText($pages, $ocrPages);
                    break;
                // CAPITEC (MERCANTILE BANK) - LOCKED - Tested & Working 25 May 2026
                case 'capitec':
                    $result = $this->parseCapitecText($pages);
                    break;
                // CAPITEC (BUSINESS BANKING) - LOCKED - Tested & Working 25 May 2026
                case 'capitec_business':
                    $result = $this->parseCapitecBusinessText($pages);
                    break;
                // CAPITEC (PERSONAL BANKING) - LOCKED - Tested & Working 25 May 2026
                case 'capitec_personal':
                    $result = $this->parseCapitecPersonalText($pages);
                    break;
                case 'standard':
                    $result = $this->parseStandardText($pages);
                    break;
                // STANDARD BANK (NEW FORMAT) - Added June 2026
                case 'standard_new':
                    $usePages = $pages;
                    if (!empty($ocrPages)) {
                        $hasText = false;
                        foreach ($pages as $p) { if (trim($p) !== '') { $hasText = true; break; } }
                        if (!$hasText) $usePages = $ocrPages;
                    }
                    $result = $this->parseStandardNewText($usePages);
                    break;
                default:
                    $result = $this->parseFnbText($pages, $ocrPages);
            }

            $result['_parser_version'] = 'CIMS-ORIGINAL-v4';
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Save parsed transactions to the database.
     */
    public function importSave(Request $request, $clientId, $bankId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $companyId = $clientId;
        $bankAccount = NexcoreBankAccount::where('company_id', $companyId)->findOrFail($bankId);

        $transactions = $request->input('transactions', []);
        $header = $request->input('header', []);
        $summary = $request->input('summary', []);
        $filename = $request->input('filename', 'bank_statement.pdf');

        if (empty($transactions)) {
            return response()->json(['error' => true, 'message' => 'No transactions to import.'], 422);
        }

        $batchRef = 'BSI-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . now()->format('YmdHis');
        $count = 0;

        \DB::beginTransaction();
        try {
            foreach ($transactions as $txn) {
                $amount = abs((float) ($txn['amount'] ?? 0));
                $direction = ((float) ($txn['amount'] ?? 0)) >= 0 ? 'credit' : 'debit';

                NexcoreBankTransaction::create([
                    'company_id'      => $companyId,
                    'bank_account_id' => $bankId,
                    'transaction_date' => $txn['date'] ?? null,
                    'description'     => $txn['description'] ?? '',
                    'amount'          => $amount,
                    'direction'       => $direction,
                    'balance'         => $txn['balance'] ?? null,
                    'status'          => 'unallocated',
                    'batch_ref'       => $batchRef,
                    'imported_at'     => now(),
                ]);
                $count++;
            }

            // Build statement reference: BS[last4 GL code]/[stmt number]/[MMMYYYY]
            $stmtNum = $header['statement_number'] ?? null;
            $periodTo = $header['period_to'] ?? ($summary['last_date'] ?? null);
            $glCode = '';
            if ($bankAccount->glAccount) {
                $glCode = $bankAccount->glAccount->account_code;
            } else {
                $bankAccount->load('glAccount');
                $glCode = $bankAccount->glAccount ? $bankAccount->glAccount->account_code : '';
            }
            $glLast4 = strlen($glCode) >= 4 ? substr($glCode, -4) : str_pad($glCode, 4, '0', STR_PAD_LEFT);
            $stmtNumPart = $stmtNum ?: '###';
            $periodPart = '';
            if ($periodTo) {
                $periodDate = \Carbon\Carbon::parse($periodTo);
                $periodPart = strtoupper($periodDate->format('MY'));
            } else {
                $periodPart = strtoupper(now()->format('MY'));
            }
            $statementRef = 'BS' . $glLast4 . '/' . $stmtNumPart . '/' . $periodPart;

            // Create statement record
            NexcoreBankStatement::create([
                'company_id'        => $companyId,
                'bank_account_id'   => $bankId,
                'statement_name'    => $header['account_holder'] ?? ($bankAccount->bank_name . ' Statement'),
                'statement_number'  => $stmtNum,
                'statement_ref'     => $statementRef,
                'period_from'       => $header['period_from'] ?? ($summary['first_date'] ?? null),
                'period_to'         => $periodTo,
                'upload_date'       => now(),
                'original_filename' => $filename,
                'transaction_count' => $count,
                'opening_balance'   => $header['opening_balance'] ?? ($summary['opening_balance'] ?? 0),
                'closing_balance'   => $header['closing_balance'] ?? ($summary['closing_balance'] ?? 0),
                'total_credits'     => $summary['total_credits'] ?? 0,
                'total_debits'      => $summary['total_debits'] ?? 0,
                'credit_count'      => $summary['credit_count'] ?? 0,
                'debit_count'       => $summary['debit_count'] ?? 0,
                'batch_ref'         => $batchRef,
                'status'            => 'imported',
            ]);

            \DB::commit();

            return response()->json([
                'success'   => true,
                'count'     => $count,
                'batch_ref' => $batchRef,
                'redirect'  => route('nexcore.clients.show.accounting.bank.accounts', $clientId),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    //  FNB PARSER
    // =========================================================================

    /**
     * Parse FNB bank statement text.
     */
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

        $result = $this->buildParseResult($header, $transactions);
        $result['raw_text'] = $allText;
        return $result;
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
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'statement_number' => null, 'period_from' => null, 'period_to' => null, 'opening_balance' => 0, 'closing_balance' => 0, 'bank_charges' => []];
        $fullText = implode(' ', $lines);
        if (preg_match('/(?:Platinum\s+Business\s+Account|Gold\s+Business\s+Account|Business\s+Account|Cheque\s+Account)\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        elseif (preg_match('/Account\s*(?:No|Number|#)?\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        if (preg_match('/Universal\s+Branch\s+Code\s*:?\s*(\d+)/i', $fullText, $m)) $header['branch_code'] = $m[1];
        if (preg_match('/Statement\s+Period\s*:?\s*(.+?)(?:Statement\s+Date|$)/i', $fullText, $m)) $header['statement_period'] = trim($m[1]);
        if (preg_match('/Statement\s+Date\s*:?\s*(\d{1,2}\s+\w+\s+\d{4})/i', $fullText, $m)) $header['statement_date'] = trim($m[1]);
        if (preg_match('/Statement\s+(?:No|Number)\s*:?\s*(\d+)/i', $fullText, $m)) $header['statement_number'] = $m[1];
        if (!empty($header['statement_period']) && preg_match('/(\d{1,2}\s+[A-Za-z]+\s+\d{4})\s+to\s+(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $header['statement_period'], $pm)) { $header['period_from'] = date('Y-m-d', strtotime($pm[1])); $header['period_to'] = date('Y-m-d', strtotime($pm[2])); }
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

    // =========================================================================
    //  NEDBANK PARSER
    // =========================================================================

    /**
     * Parse Nedbank bank statement text (balance-difference method).
     */
    private function parseNedbankText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'Nedbank',
            'account_number'   => null,
            'statement_period' => null,
            'opening_balance'  => null,
            'closing_balance'  => null,
            'account_holder'   => null,
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'bank_charges'     => [],
        ];

        // Account number
        if (preg_match('/Account\s+(?:Number|No)\s*:?\s*(\d{6,15})/i', $allText, $m)) {
            $header['account_number'] = $m[1];
        }

        // Statement period (DD/MM/YYYY to DD/MM/YYYY)
        if (preg_match('/Statement\s+Period\s*:?\s*(\d{2}\/\d{2}\/\d{4})\s*(?:to|-)\s*(\d{2}\/\d{2}\/\d{4})/i', $allText, $m)) {
            $header['statement_period'] = $m[1] . ' to ' . $m[2];
            $from = \DateTime::createFromFormat('d/m/Y', $m[1]);
            $to = \DateTime::createFromFormat('d/m/Y', $m[2]);
            if ($from) $header['period_from'] = $from->format('Y-m-d');
            if ($to) $header['period_to'] = $to->format('Y-m-d');
        }

        // Opening balance
        if (preg_match('/Opening\s+Balance\s*:?\s*([\d,]+\.\d{2})/i', $allText, $m)) {
            $header['opening_balance'] = (float) str_replace(',', '', $m[1]);
        }

        // Closing balance
        if (preg_match('/Closing\s+Balance\s*:?\s*([\d,]+\.\d{2})/i', $allText, $m)) {
            $header['closing_balance'] = (float) str_replace(',', '', $m[1]);
        }

        // Account holder
        if (preg_match('/Account\s+Holder\s*:?\s*(.+)/i', $allText, $m)) {
            $header['account_holder'] = trim($m[1]);
        }

        // Statement number
        if (preg_match('/Statement\s+(?:No|Number)\s*:?\s*(\d+)/i', $allText, $m)) {
            $header['statement_number'] = $m[1];
        }

        // Parse transactions using balance-difference
        $transactions = [];
        $prevBalance = $header['opening_balance'];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip lines without DD/MM/YYYY date
            if (!preg_match('/(\d{2}\/\d{2}\/\d{4})/', $trimmed, $dateMatch)) {
                continue;
            }

            $dateStr = $dateMatch[1];
            $dateObj = \DateTime::createFromFormat('d/m/Y', $dateStr);
            if (!$dateObj) continue;
            $date = $dateObj->format('Y-m-d');

            // Extract all decimal numbers from the line
            preg_match_all('/([\d,]+\.\d{2})/', $trimmed, $numMatches);
            $numbers = array_map(function ($n) { return (float) str_replace(',', '', $n); }, $numMatches[1] ?? []);

            if (empty($numbers)) continue;

            // Last number = balance
            $balance = end($numbers);

            // Description: text between date and first number
            $description = $trimmed;
            // Remove the date
            $description = preg_replace('/\d{2}\/\d{2}\/\d{4}/', '', $description, 1);
            // Remove all numbers at end
            $description = preg_replace('/([\d,]+\.\d{2}\s*)+$/', '', $description);
            $description = trim($description);

            // Amount = current balance - previous balance
            if ($prevBalance !== null) {
                $amount = round($balance - $prevBalance, 2);
            } else {
                // If no previous balance, try second-to-last number as amount
                $amount = count($numbers) >= 2 ? $numbers[count($numbers) - 2] : 0;
            }

            $transactions[] = [
                'date'        => $date,
                'description' => $description,
                'amount'      => $amount,
                'balance'     => $balance,
            ];

            $prevBalance = $balance;
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  NEDBANK ONLINE PARSER (Statement Enquiry / New Online Banking)
    //  Added June 2026
    //
    //  Format: Date(DD/MM/YYYY) | Transactions | Debits | Credits | Balance
    //  Numbers use SPACE-separated thousands (e.g. 581 325.00)
    //  Debits shown with minus sign (e.g. -295.00, -148 000.00)
    //  Header: Statement Number, Account number, Date range
    //  BROUGHT FORWARD / CARRIED FORWARD rows at month boundaries
    //  Uses balance-difference method for amount calculation.
    //
    //  DO NOT MODIFY the original parseNedbankText() — this is a separate
    //  parser for the newer Nedbank online statement format.
    // =========================================================================

    private function parseNedbankOnlineText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'Nedbank',
            'account_number'   => null,
            'statement_period' => null,
            'opening_balance'  => null,
            'closing_balance'  => null,
            'account_holder'   => null,
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'bank_charges'     => [],
        ];

        // Account number: "Account number: 1301995606"
        if (preg_match('/Account\s+(?:Number|No|number)\s*:?\s*(\d{6,15})/i', $allText, $m)) {
            $header['account_number'] = $m[1];
        }

        // Date range: "Date range: 25/06/2021 - 25/06/2026"
        if (preg_match('/Date\s+range\s*:?\s*(\d{2}\/\d{2}\/\d{4})\s*[-–]\s*(\d{2}\/\d{2}\/\d{4})/i', $allText, $m)) {
            $header['statement_period'] = $m[1] . ' to ' . $m[2];
            $from = \DateTime::createFromFormat('d/m/Y', $m[1]);
            $to   = \DateTime::createFromFormat('d/m/Y', $m[2]);
            if ($from) $header['period_from'] = $from->format('Y-m-d');
            if ($to)   $header['period_to']   = $to->format('Y-m-d');
        }

        // Statement number: "Statement Number: 1"
        if (preg_match('/Statement\s+(?:No|Number)\s*:?\s*(\d+)/i', $allText, $m)) {
            $header['statement_number'] = $m[1];
        }

        // Number regex: space-separated thousands with optional minus sign
        // Matches: 0.00, 295.00, 1 000.00, 581 325.00, 1 633 254.84, -295.00, -148 000.00
        $numRegex = '/-?\d{1,3}(?:[ ]\d{3})*\.\d{2}(?!\d)/';

        $toFloat = function ($n) {
            return (float) str_replace(' ', '', $n);
        };

        $transactions = [];
        $prevBalance = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            // Skip header/footer lines
            if (preg_match('/^(Date\s+Transactions|Statement\s+Enquiry|New\s+Online\s+Banking|Statement\s+Number|Account\s+description|Account\s+number|Date\s+range)/i', $trimmed)) continue;

            // Must start with DD/MM/YYYY date
            if (!preg_match('/^(\d{2}\/\d{2}\/\d{4})\s+(.*)$/', $trimmed, $lineMatch)) {
                continue;
            }

            $dateStr = $lineMatch[1];
            $rest    = $lineMatch[2];

            $dateObj = \DateTime::createFromFormat('d/m/Y', $dateStr);
            if (!$dateObj) continue;
            $date = $dateObj->format('Y-m-d');

            // Extract all numbers from the rest of the line
            preg_match_all($numRegex, $rest, $numMatches);
            $numbers = array_map($toFloat, $numMatches[0] ?? []);

            if (empty($numbers)) continue;

            // Last number is always the balance
            $balance = end($numbers);

            // BROUGHT FORWARD: set opening balance and track position
            if (preg_match('/BROUGHT\s+FORWARD/i', $rest)) {
                if ($header['opening_balance'] === null) {
                    $header['opening_balance'] = $balance;
                }
                $prevBalance = $balance;
                continue;
            }

            // CARRIED FORWARD: just update balance tracker, skip as transaction
            if (preg_match('/CARRIED\s+FORWARD/i', $rest)) {
                $prevBalance = $balance;
                continue;
            }

            // Build description: remove numbers from text
            $description = $rest;
            foreach ($numMatches[0] as $numStr) {
                $pos = strpos($description, $numStr);
                if ($pos !== false) {
                    $description = substr($description, 0, $pos) . substr($description, $pos + strlen($numStr));
                }
            }
            $description = trim(preg_replace('/\s+/', ' ', $description));

            if (empty($description)) continue;

            // Amount via balance-difference
            if ($prevBalance !== null) {
                $amount = round($balance - $prevBalance, 2);
            } else {
                $amount = count($numbers) >= 2 ? $numbers[count($numbers) - 2] : 0;
            }

            // Skip zero-amount lines (VAT info, etc.)
            if (abs($amount) < 0.01) {
                continue;
            }

            $transactions[] = [
                'date'        => $date,
                'description' => $description,
                'amount'      => $amount,
                'balance'     => $balance,
            ];

            $prevBalance = $balance;
        }

        // Closing balance from last transaction
        if (!empty($transactions)) {
            $header['closing_balance'] = end($transactions)['balance'];
        }

        // Override period from actual transaction dates (more accurate than header date range)
        if (!empty($transactions)) {
            $header['period_from'] = $transactions[0]['date'];
            $header['period_to']   = end($transactions)['date'];
            $header['statement_period'] = $header['period_from'] . ' to ' . $header['period_to'];
        }

        // Auto-generate statement number if not found
        if (!$header['statement_number'] && $header['period_from']) {
            $d = \Carbon\Carbon::parse($header['period_from']);
            $header['statement_number'] = $d->format('y') . $d->format('m');
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  ABSA (TRANSACTION HISTORY) PARSER
    //  LOCKED - Tested & Working - 25 May 2026
    //
    //  Uses direct amount extraction with calculated running balance.
    //  Statement number auto-generated as YYMM from period start date.
    //  Format: Date(YYYY-MM-DD) | Description | Amount(signed) | Balance
    //  No separate fee column - bank charges are normal transaction lines.
    //
    //  DO NOT MODIFY without full regression testing on known-good statements.
    // =========================================================================

    private function parseAbsaTransactionHistoryText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'ABSA',
            'account_number'   => null,
            'statement_period' => null,
            'opening_balance'  => null,
            'closing_balance'  => null,
            'account_holder'   => null,
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'bank_charges'     => [],
        ];

        // Statement period: "Statement for Period 2025-07-01 - 2025-07-31"
        if (preg_match('/Statement\s+for\s+Period\s+(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})/i', $allText, $m)) {
            $header['period_from'] = $m[1];
            $header['period_to'] = $m[2];
            $header['statement_period'] = $m[1] . ' to ' . $m[2];
        }

        // Auto-generate statement number as YYMM from period start
        if ($header['period_from']) {
            $d = \Carbon\Carbon::parse($header['period_from']);
            $header['statement_number'] = $d->format('y') . $d->format('m');
        }

        // Account number: 10-digit ABSA account number from header area only
        $headerArea = implode("\n", array_slice($lines, 0, min(20, count($lines))));
        if (preg_match('/\b(\d{10})\b/', $headerArea, $m)) {
            $header['account_number'] = $m[1];
        }

        // Account holder: company/person name before "ABSA" on the same line
        if (preg_match('/^(.{5,}?)\s+ABSA\s*$/m', $headerArea, $m)) {
            $header['account_holder'] = trim($m[1]);
        }

        // --- PASS 1: Collect transaction groups (date + continuation lines) ---
        $inTransactions = false;
        $currentDate = null;
        $textLines = [];
        $dateLineData = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            // Skip page headers/footers
            if (preg_match('/^Page\s+\d+\s+of\s+\d+$/i', $trimmed)) continue;
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\s+Page/i', $trimmed)) continue;
            if (preg_match('/^Date:\s+Transaction\s+Description/i', $trimmed)) continue;
            if (preg_match('/^Amount\s+Balance$/i', $trimmed)) continue;

            // "Balance Brought Forward" = opening balance, starts transaction section
            if (preg_match('/Balance\s+Brought\s+Forward/i', $trimmed)) {
                $inTransactions = true;
                if (preg_match('/([\d,]+\.\d{2})\s*$/', $trimmed, $bm)) {
                    $header['opening_balance'] = (float) str_replace(',', '', $bm[1]);
                }
                continue;
            }

            // "Balance Carried Forward" = closing balance, ends transaction section
            if (preg_match('/Balance\s+Carried\s+Forward/i', $trimmed)) {
                if ($currentDate !== null && !empty($textLines)) {
                    $dateLineData[] = ['date' => $currentDate, 'textLines' => $textLines];
                }
                if (preg_match('/([\d,]+\.\d{2})\s*$/', $trimmed, $bm)) {
                    $header['closing_balance'] = (float) str_replace(',', '', $bm[1]);
                }
                $inTransactions = false;
                $currentDate = null;
                $textLines = [];
                continue;
            }

            if (!$inTransactions) continue;

            // Date line: YYYY-MM-DD followed by description text
            if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(.*)$/', $trimmed, $dm)) {
                if ($currentDate !== null && !empty($textLines)) {
                    $dateLineData[] = ['date' => $currentDate, 'textLines' => $textLines];
                }
                $currentDate = $dm[1];
                $textLines = [trim($dm[2])];
            } else {
                if ($currentDate !== null) {
                    $textLines[] = $trimmed;
                }
            }
        }

        // Finalize last pending group
        if ($currentDate !== null && !empty($textLines)) {
            $dateLineData[] = ['date' => $currentDate, 'textLines' => $textLines];
        }

        // --- PASS 2: Build transactions with calculated running balance ---
        $transactions = [];
        $runningBalance = $header['opening_balance'];

        foreach ($dateLineData as $dl) {
            $fullText = implode(' ', $dl['textLines']);

            // Extract signed decimal numbers: -1,360.92 or 14,742.84 or 0.00
            preg_match_all('/-?[\d,]+\.\d{2}(?!\d)/', $fullText, $numMatches);
            $numbers = array_map(function ($n) {
                return (float) str_replace(',', '', $n);
            }, $numMatches[0] ?? []);

            if (count($numbers) < 2) continue;

            // Second-to-last = amount, last = PDF balance (ignored, we calculate our own)
            $amount = $numbers[count($numbers) - 2];

            // Strip the last two numbers from text to get clean description
            $description = $fullText;
            for ($i = count($numMatches[0]) - 1; $i >= max(0, count($numMatches[0]) - 2); $i--) {
                $lastPos = strrpos($description, $numMatches[0][$i]);
                if ($lastPos !== false) {
                    $description = substr($description, 0, $lastPos);
                }
            }
            $description = trim(preg_replace('/\s+/', ' ', $description));
            if (empty($description)) continue;

            $runningBalance = round($runningBalance + $amount, 2);

            $transactions[] = [
                'date'        => $dl['date'],
                'description' => $description,
                'amount'      => $amount,
                'balance'     => $runningBalance,
            ];
        }

        // Fallback: period from transactions if not found in header
        if (!$header['period_from'] && !empty($transactions)) {
            $header['period_from'] = $transactions[0]['date'];
            $header['period_to'] = end($transactions)['date'];
            $header['statement_period'] = $header['period_from'] . ' to ' . $header['period_to'];
            $d = \Carbon\Carbon::parse($header['period_from']);
            $header['statement_number'] = $d->format('y') . $d->format('m');
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  ABSA (BANK STATEMENT) PARSER
    //  LOCKED - Tested & Working - 25 May 2026
    //
    //  Cheque Account Statement format with 6 columns:
    //  Date(DD/MM/YYYY) | Description | Charge | Debit Amount | Credit Amount | Balance
    //  Image-based PDF - uses Tesseract OCR at 4x resolution via frontend.
    //  Space-separated thousands, supports both period and comma decimal.
    //  Charge-only lines (Proof Of Pmt Email) and informational lines skipped.
    //  Only imports transactions with actual Debit/Credit amounts.
    //  Amount calculated from balance-difference (more reliable with OCR text).
    //  Account holder skipped (OCR unreliable, upload is inside client account).
    //
    //  DO NOT MODIFY without full regression testing on known-good statements.
    // =========================================================================

    private function parseAbsaBankStatementText(array $pages, array $ocrPages = []): array
    {
        // Use OCR text if regular pdf.js extraction is empty (scanned/image PDFs)
        $regularText = trim(implode('', $pages));
        if (strlen($regularText) < 20 && !empty($ocrPages)) {
            $pages = $ocrPages;
        }

        $allText = implode("\n", $pages);

        // --- OCR TEXT PREPROCESSING ---
        // Reconstruct space-separated thousands that OCR may have split across words.
        // Pattern: standalone 1-3 digit number followed by a decimal number (e.g. "39 847.50" OCR'd as "39 847.50" or "39\n847.50")
        // This regex finds: "digits(1-3) space/newline digits(3).digits(2)" and joins them
        $allText = preg_replace('/(\d{1,3})\s+(\d{3}[.,]\d{2})/', '$1 $2', $allText);
        // Also handle: "digits(1-3) space digits(3) space digits(3).digits(2)" for larger numbers like "1 234 567.89"
        $allText = preg_replace('/(\d{1,3})\s+(\d{3})\s+(\d{3}[.,]\d{2})/', '$1 $2 $3', $allText);

        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'ABSA',
            'account_number'   => null,
            'statement_period' => null,
            'opening_balance'  => null,
            'closing_balance'  => null,
            'account_holder'   => null,
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'bank_charges'     => [],
        ];

        $headerArea = implode("\n", array_slice($lines, 0, min(30, count($lines))));

        // Cheque Account Number: 41-0470-4679 (also handle OCR variants like "41—0470—4679")
        if (preg_match('/Cheque\s+Account\s+Number\s*:?\s*([\d\-—]+)/i', $allText, $m)) {
            $header['account_number'] = str_replace('—', '-', trim($m[1]));
        }

        // Statement no: 0009
        if (preg_match('/Statement\s+no\s*:?\s*(\S+)/i', $allText, $m)) {
            $header['statement_number'] = trim($m[1]);
        }

        // Account holder: skipped for ABSA Bank Statement (OCR unreliable on company name,
        // and the upload is already inside the client's account in CIMS)

        // Period: "17 Mar 2023 to 16 Apr 2023"
        if (preg_match('/(\d{1,2}\s+\w{3,9}\s+\d{4})\s+to\s+(\d{1,2}\s+\w{3,9}\s+\d{4})/i', $allText, $m)) {
            try {
                $from = \Carbon\Carbon::parse($m[1]);
                $to = \Carbon\Carbon::parse($m[2]);
                $header['period_from'] = $from->format('Y-m-d');
                $header['period_to'] = $to->format('Y-m-d');
                $header['statement_period'] = $header['period_from'] . ' to ' . $header['period_to'];
            } catch (\Exception $e) {}
        }

        // Auto-generate statement number if not found on PDF
        if (!$header['statement_number'] && $header['period_from']) {
            $d = \Carbon\Carbon::parse($header['period_from']);
            $header['statement_number'] = $d->format('y') . $d->format('m');
        }

        // Number regex: space-separated thousands, supports BOTH period and comma decimal
        $numRegex = '/-?\d{1,3}(?:[ ]\d{3})*[.,]\d{2}(?!\d)/';

        // Helper to convert matched number string to float
        $toFloat = function ($n) {
            return (float) str_replace([' ', ','], ['', '.'], $n);
        };

        // --- PASS 1: Collect transaction groups (date + continuation lines) ---
        $inTransactions = false;
        $currentDate = null;
        $textLines = [];
        $dateLineData = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            // Skip page headers/footers
            if (preg_match('/^Page\s+\d+\s+of\s+\d+/i', $trimmed)) continue;
            if (preg_match('/^Date\s+Transaction\s+Description/i', $trimmed)) continue;
            if (preg_match('/^Charge\s+Debit\s+Amount/i', $trimmed)) continue;
            if (preg_match('/^YOUR\s+PRICING\s+PLAN/i', $trimmed)) { $inTransactions = false; continue; }
            if (preg_match('/^CREDIT\s+INTEREST\s+RATE/i', $trimmed)) { $inTransactions = false; continue; }
            if (preg_match('/^CHARGE:\s+A\s*=/i', $trimmed)) { $inTransactions = false; continue; }
            if (preg_match('/^\*\s*=\s*VAT/i', $trimmed)) { $inTransactions = false; continue; }
            if (preg_match('/^Our\s+Privacy\s+Notice/i', $trimmed)) { $inTransactions = false; continue; }
            if (preg_match('/^Account\s+Summary/i', $trimmed)) continue;
            if (preg_match('/^Sundry\s+(Credits|Debits)/i', $trimmed)) continue;
            if (preg_match('/^Charges\b/i', $trimmed)) continue;
            if (preg_match('/^Overdraft\s+Limit/i', $trimmed)) continue;
            if (preg_match('/^Your\s+transactions/i', $trimmed)) continue;

            // "Bal Brought Forward" or "Bal brought forward" = opening balance
            if (preg_match('/\bBal\b\s+Brought\s+Forward/i', $trimmed)) {
                $inTransactions = true;
                preg_match_all($numRegex, $trimmed, $nums);
                $numbers = array_map($toFloat, $nums[0] ?? []);
                if (!empty($numbers)) {
                    $header['opening_balance'] = end($numbers);
                }
                continue;
            }

            // "Balance Brought Forward" from Account Summary = fallback opening balance
            if (!$inTransactions && preg_match('/Balance\s+Brought\s+Forward/i', $trimmed)) {
                preg_match_all($numRegex, $trimmed, $nums);
                $numbers = array_map($toFloat, $nums[0] ?? []);
                if (!empty($numbers) && $header['opening_balance'] === null) {
                    $header['opening_balance'] = end($numbers);
                }
                continue;
            }

            if (!$inTransactions) continue;

            // Date line: DD/MM/YYYY or D/MM/YYYY
            if (preg_match('/^(\d{1,2}\/\d{2}\/\d{4})\s+(.*)$/', $trimmed, $dm)) {
                if ($currentDate !== null && !empty($textLines)) {
                    $dateLineData[] = ['date' => $currentDate, 'textLines' => $textLines];
                }
                $parts = explode('/', $dm[1]);
                $currentDate = $parts[2] . '-' . $parts[1] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $textLines = [trim($dm[2])];
            } else {
                if ($currentDate !== null) {
                    $textLines[] = $trimmed;
                }
            }
        }

        // Finalize last pending group
        if ($currentDate !== null && !empty($textLines)) {
            $dateLineData[] = ['date' => $currentDate, 'textLines' => $textLines];
        }

        // --- PASS 2: Build transactions using PDF balance (balance-difference for amount sign) ---
        // For OCR-parsed PDFs, the PDF balance column is more reliable than reconstructed amounts.
        // We use balance-difference to determine the actual amount, and verify against extracted numbers.
        $transactions = [];
        $prevPdfBalance = $header['opening_balance'];

        foreach ($dateLineData as $dl) {
            $fullText = implode(' ', $dl['textLines']);

            // Extract numbers from ALL text lines
            preg_match_all($numRegex, $fullText, $numMatches);
            $numbers = array_map($toFloat, $numMatches[0] ?? []);

            if (empty($numbers)) continue;

            // Last number = PDF balance (most reliable from OCR - it's the rightmost column)
            $pdfBalance = end($numbers);

            // Balance unchanged = charge-only or informational line, skip
            if ($prevPdfBalance !== null && abs($pdfBalance - $prevPdfBalance) < 0.01) {
                continue;
            }

            // Calculate amount from balance difference (reliable even with OCR errors on amount)
            $amount = ($prevPdfBalance !== null) ? round($pdfBalance - $prevPdfBalance, 2) : 0;

            // Clean description: remove all matched numbers from text
            $description = $fullText;
            foreach ($numMatches[0] as $numStr) {
                $pos = strpos($description, $numStr);
                if ($pos !== false) {
                    $description = substr($description, 0, $pos) . substr($description, $pos + strlen($numStr));
                }
            }
            // Strip OCR artifacts: standalone single letters (charge type codes), asterisks
            $description = preg_replace('/\s*\*\s*/', ' ', $description);
            $description = preg_replace('/\s+[A-Z]\s*$/', '', $description);
            $description = trim(preg_replace('/\s+/', ' ', $description));
            if (empty($description)) continue;

            // Skip zero-amount lines (notifications with same balance)
            if (abs($amount) < 0.01) continue;

            $prevPdfBalance = $pdfBalance;

            $transactions[] = [
                'date'        => $dl['date'],
                'description' => $description,
                'amount'      => $amount,
                'balance'     => $pdfBalance,
            ];
        }

        // Closing balance from last transaction
        if (!empty($transactions)) {
            $header['closing_balance'] = end($transactions)['balance'];
        }

        // Fallback period from transactions
        if (!$header['period_from'] && !empty($transactions)) {
            $header['period_from'] = $transactions[0]['date'];
            $header['period_to'] = end($transactions)['date'];
            $header['statement_period'] = $header['period_from'] . ' to ' . $header['period_to'];
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  CAPITEC PARSER
    // =========================================================================

    /**
     * =====================================================================
     * CAPITEC (MERCANTILE BANK) PARSER
     * =====================================================================
     * LOCKED - Tested & Working - 25 May 2026
     *
     * This parser handles Capitec statements issued through Mercantile Bank.
     * Statement format: date lines with DD/MM/YYYY, transaction text on
     * continuation lines, amount and balance as last two decimals per block.
     *
     * Uses finalizeCapitecTxn() helper below for amount/balance extraction.
     *
     * DO NOT MODIFY without full regression testing on known-good statements.
     * =====================================================================
     */
    private function parseCapitecText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'statement_number' => null, 'period_from' => null, 'period_to' => null, 'opening_balance' => 0, 'closing_balance' => 0];
        $fullText = implode(' ', $lines);
        if (preg_match('/Account\s+No\.?\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        if (preg_match('/Branch:\s*(\d+)/i', $fullText, $m)) $header['branch_code'] = $m[1];
        if (preg_match('/Statement\s+(?:No|Number)\.?\s*:?\s*(\d+)/i', $fullText, $m)) $header['statement_number'] = $m[1];
        if (empty($header['statement_number'])) {
            for ($i = 0; $i < count($lines) - 1; $i++) {
                if (preg_match('/Statement\s+No\.?\s*$/i', trim($lines[$i]))) {
                    $nextLine = trim($lines[$i + 1]);
                    if (preg_match('/^(\d+)/', $nextLine, $snm)) {
                        $header['statement_number'] = $snm[1];
                        break;
                    }
                }
            }
        }
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

        if (!empty($transactions)) {
            $header['closing_balance'] = end($transactions)['balance'];

            $monthCounts = [];
            foreach ($transactions as $txn) {
                $ym = substr($txn['date'], 0, 7);
                if (!isset($monthCounts[$ym])) $monthCounts[$ym] = 0;
                $monthCounts[$ym]++;
            }
            arsort($monthCounts);
            $dominantMonth = array_key_first($monthCounts);
            $header['period_from'] = $dominantMonth . '-01';
            $header['period_to'] = date('Y-m-t', strtotime($dominantMonth . '-01'));
            $header['statement_period'] = date('d F Y', strtotime($header['period_from'])) . ' to ' . date('d F Y', strtotime($header['period_to']));
        }
        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  CAPITEC (BUSINESS BANKING) PARSER
    // =========================================================================

    /**
     * =====================================================================
     * CAPITEC (BUSINESS BANKING) PARSER
     * =====================================================================
     * LOCKED - Tested & Working - 25 May 2026
     *
     * Handles Capitec Business Account statements ("Business Account Statement").
     * Format: 6-column table (Date | Description | Reference | Money in | Money out | Fees | Balance).
     * Dates: DD/MM/YYYY (4-digit year). Amounts: space-separated thousands (e.g. -2 500.00).
     *
     * 3-pass parsing:
     *   Pass 1 - Identify date lines, extract date/description/numbers
     *   Pass 2 - Assign multi-line reference text to nearest date line
     *   Pass 3 - Build transactions with calculated running balance
     *
     * Statement number: auto-generated as YYMM (e.g. "2603" for March 2026).
     * Fee-only entries (Month S/Fee) included as own line items.
     * Attached fees split into separate "FEE - [description]" lines.
     * Running balance calculated from opening balance (not copied from PDF).
     *
     * DO NOT MODIFY without full regression testing on known-good statements.
     * =====================================================================
     */
    private function parseCapitecBusinessText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'Capitec Business Banking',
            'account_number'   => '',
            'account_holder'   => '',
            'branch_code'      => '',
            'statement_period' => '',
            'statement_date'   => '',
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'opening_balance'  => 0,
            'closing_balance'  => 0,
        ];

        $fullText = implode(' ', $lines);

        // Account number
        if (preg_match('/Account\s+number\s+(\d{6,15})/i', $fullText, $m)) {
            $header['account_number'] = $m[1];
        }

        // Branch code from stamp area
        if (preg_match('/Branch:\s*(\d+)/i', $fullText, $m)) {
            $header['branch_code'] = $m[1];
        }

        // Opening balance: "R 39 420.88"
        if (preg_match('/Opening\s+balance\s+R\s*(-?\d[\d ]*\.\d{2})/i', $fullText, $m)) {
            $header['opening_balance'] = (float) str_replace(' ', '', $m[1]);
        }

        // Closing balance: "R 57 895.68"
        if (preg_match('/Closing\s+balance\s+R\s*(-?\d[\d ]*\.\d{2})/i', $fullText, $m)) {
            $header['closing_balance'] = (float) str_replace(' ', '', $m[1]);
        }

        // Statement period: "01 March 2026 - 31 March 2026"
        if (preg_match('/Statement\s+period\s+(\d{1,2}\s+\w+\s+\d{4})\s*[-–]\s*(\d{1,2}\s+\w+\s+\d{4})/i', $fullText, $m)) {
            $header['statement_period'] = trim($m[1]) . ' - ' . trim($m[2]);
            $from = date('Y-m-d', strtotime(trim($m[1])));
            $to = date('Y-m-d', strtotime(trim($m[2])));
            if ($from && $from !== '1970-01-01') $header['period_from'] = $from;
            if ($to && $to !== '1970-01-01') $header['period_to'] = $to;
        }

        // Statement date
        if (preg_match('/Statement\s+date\s+(\d{1,2}\s+\w+\s+\d{4})/i', $fullText, $m)) {
            $header['statement_date'] = trim($m[1]);
        }

        // Statement number: auto-generate as YYMM from statement period
        if (!empty($header['period_from'])) {
            $header['statement_number'] = date('y', strtotime($header['period_from']))
                                        . date('m', strtotime($header['period_from']));
        }

        // Total fees: "R -84.57"
        $totalFees = 0;
        if (preg_match('/Total\s+fees\s+R\s*(-?\d[\d ]*\.\d{2})/i', $fullText, $m)) {
            $totalFees = (float) str_replace(' ', '', $m[1]);
        }

        // Account holder (company name - first line of left address block)
        if (preg_match('/Business\s+Account\s+Statement\s+(\S[^\n]*)/i', $allText, $m)) {
            $holder = trim($m[1]);
            if (!preg_match('/Capitec/i', $holder)) {
                $header['account_holder'] = $holder;
            }
        }

        // --- PASS 1: Identify date lines and extract data ---
        $numPattern = '/-?\d{1,3}(?:[ ]\d{3})*\.\d{2}(?!\d)/';
        $dateLineData = [];
        $inTransactions = false;

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);

            if (preg_match('/Transaction\s+history/i', $trimmed)) {
                $inTransactions = true;
                continue;
            }
            if (!$inTransactions) continue;

            if (preg_match('/^(\d{2}\/\d{2}\/\d{4})\s+(.*)$/', $trimmed, $dm)) {
                $dateParts = explode('/', $dm[1]);
                $isoDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];

                $restOfLine = $dm[2];

                // Extract all amounts from the line
                preg_match_all($numPattern, $restOfLine, $nums);
                $numbers = array_map(function ($n) {
                    return (float) str_replace(' ', '', $n);
                }, $nums[0]);

                // Description type = text before the first number
                $descType = $restOfLine;
                if (!empty($nums[0])) {
                    $firstNumPos = strpos($restOfLine, $nums[0][0]);
                    if ($firstNumPos !== false) {
                        $descType = substr($restOfLine, 0, $firstNumPos);
                    }
                }
                $descType = trim($descType);

                $dateLineData[] = [
                    'lineNum' => $lineNum,
                    'date'    => $isoDate,
                    'descType'=> $descType,
                    'numbers' => $numbers,
                ];
            }
        }

        if (empty($dateLineData)) {
            return $this->buildParseResult($header, []);
        }

        // --- PASS 2: Assign reference lines to nearest date line ---
        $txnRefLines = array_fill(0, count($dateLineData), []);
        $dateLineNums = array_column($dateLineData, 'lineNum');
        $inTransactions = false;

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            if (preg_match('/Transaction\s+history/i', $trimmed)) {
                $inTransactions = true;
                continue;
            }
            if (!$inTransactions) continue;

            // Skip if this IS a date line
            if (in_array($lineNum, $dateLineNums)) continue;

            // Skip headers, footers, column labels
            if (preg_match('/^(Date\s+Description|Money\s+in|Money\s+out|Fees\*?|Balance)\s*$/i', $trimmed)) continue;
            if (preg_match('/^(24hr\s+Business|Capitec\s+Bank\s+is|Unique\s+Document|Page\s+\d|Validate\s+this)/i', $trimmed)) continue;
            if (preg_match('/^(SkyQR|App\s+Store|Google\s+Play|AppGallery)/i', $trimmed)) continue;
            if (preg_match('/^(Account\s+number|Account\s+type|Account\s+information|Business\s+reg|Statement\s+for|Statement\s+period|Statement\s+date|Opening\s+balance|Closing\s+balance|Total\s+fees|Business\s+Account\s+Statement|Capitec\s+Bank\s+Limited)/i', $trimmed)) continue;
            if (preg_match('/^(R\s+-?\d[\d ]*\.\d{2})\s*$/i', $trimmed)) continue;

            // Find the nearest date line by absolute distance
            $nearestIdx = 0;
            $nearestDist = PHP_INT_MAX;
            foreach ($dateLineData as $idx => $dl) {
                $dist = abs($lineNum - $dl['lineNum']);
                if ($dist < $nearestDist || ($dist === $nearestDist && $idx > $nearestIdx)) {
                    $nearestDist = $dist;
                    $nearestIdx = $idx;
                }
            }

            $txnRefLines[$nearestIdx][] = $trimmed;
        }

        // --- PASS 3: Build transactions with calculated running balance ---
        $transactions = [];
        $runningBalance = $header['opening_balance'];

        foreach ($dateLineData as $idx => $dl) {
            $numbers = $dl['numbers'];
            if (empty($numbers)) continue;

            // Build full description
            $refText = implode(' ', $txnRefLines[$idx] ?? []);
            $description = trim($dl['descType'] . ' ' . $refText);
            $description = preg_replace('/\s+/', ' ', $description);
            if (empty($description)) continue;

            // Determine amounts based on number count:
            // 3+ numbers: [amount, fee, balance] → transaction + separate fee line
            // 2 numbers: could be [amount, balance] or [fee-only, balance]
            // 1 number: balance only
            $isFeeOnly = preg_match('/Month\s*S\/Fee|Monthly\s*Fee/i', $description);

            if (count($numbers) >= 3) {
                // Regular transaction with attached fee
                $amount = $numbers[0];
                $fee = $numbers[1];

                $runningBalance = round($runningBalance + $amount, 2);
                $transactions[] = [
                    'date'        => $dl['date'],
                    'description' => $description,
                    'amount'      => $amount,
                    'balance'     => $runningBalance,
                ];

                // Separate fee line
                if (abs($fee) > 0.001) {
                    $runningBalance = round($runningBalance + $fee, 2);
                    $transactions[] = [
                        'date'        => $dl['date'],
                        'description' => 'FEE - ' . $description,
                        'amount'      => $fee,
                        'balance'     => $runningBalance,
                    ];
                }
            } elseif (count($numbers) >= 2) {
                if ($isFeeOnly) {
                    // Fee-only entry (Month S/Fee): first number is the fee amount
                    $fee = $numbers[0];
                    $runningBalance = round($runningBalance + $fee, 2);
                    $transactions[] = [
                        'date'        => $dl['date'],
                        'description' => $description,
                        'amount'      => $fee,
                        'balance'     => $runningBalance,
                    ];
                } else {
                    // Regular transaction without fee
                    $amount = $numbers[0];
                    $runningBalance = round($runningBalance + $amount, 2);
                    $transactions[] = [
                        'date'        => $dl['date'],
                        'description' => $description,
                        'amount'      => $amount,
                        'balance'     => $runningBalance,
                    ];
                }
            }
        }

        // Fill in header gaps from transaction data
        if (!empty($transactions)) {
            if (empty($header['closing_balance'])) {
                $header['closing_balance'] = end($transactions)['balance'];
            }
            if (empty($header['period_from'])) {
                $header['period_from'] = $transactions[0]['date'];
            }
            if (empty($header['period_to'])) {
                $header['period_to'] = end($transactions)['date'];
            }
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  CAPITEC (PERSONAL BANKING) PARSER
    // =========================================================================

    /**
     * =====================================================================
     * CAPITEC (PERSONAL BANKING) PARSER
     * =====================================================================
     * LOCKED - Tested & Working - 25 May 2026
     *
     * Handles Capitec Personal Account statements ("Main Account Statement").
     * Format: 7-column table (Date | Description | Category | Money In | Money Out | Fee* | Balance).
     * Dates: DD/MM/YYYY. Amounts: space-separated thousands. VAT asterisks stripped.
     * Page 1 summaries (Scheduled Payments, Spending Summary) are skipped.
     *
     * 3-pass parsing:
     *   Pass 1 - Identify date lines in Transaction History section
     *   Pass 2 - Assign multi-line description continuations to nearest date line
     *   Pass 3 - Build transactions with calculated running balance
     *
     * Statement number: auto-generated as YYMM from period start.
     * Fee-only entries (Category = "Fees") included as own line items.
     * Attached fees split into separate "FEE - [description]" lines.
     * Running balance calculated from opening balance (not copied from PDF).
     *
     * DO NOT MODIFY without full regression testing on known-good statements.
     * =====================================================================
     */
    private function parseCapitecPersonalText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'Capitec Personal Banking',
            'account_number'   => '',
            'account_holder'   => '',
            'branch_code'      => '',
            'statement_period' => '',
            'statement_date'   => '',
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'opening_balance'  => 0,
            'closing_balance'  => 0,
        ];

        $fullText = implode(' ', $lines);

        // Account number (appears after "Account" heading)
        if (preg_match('/Account\s+(\d{8,15})/i', $fullText, $m)) {
            $header['account_number'] = $m[1];
        }

        // Account holder (name after "Main Account Statement")
        if (preg_match('/Main\s+Account\s+Statement\s+((?:MR|MRS|MS|MISS|DR)\s+[A-Z\s]+?)(?:\s{2,}|Capitec)/i', $allText, $m)) {
            $header['account_holder'] = trim($m[1]);
        }

        // Branch code from stamp
        if (preg_match('/Branch:\s*(\d+)/i', $fullText, $m)) {
            $header['branch_code'] = $m[1];
        }

        // Opening balance: "R82 501.39" (R prefix, no space after R sometimes)
        if (preg_match('/Opening\s+Balance:?\s+R\s*(-?\d[\d ]*\.\d{2})/i', $fullText, $m)) {
            $header['opening_balance'] = (float) str_replace(' ', '', $m[1]);
        }

        // Closing balance
        if (preg_match('/Closing\s+Balance:?\s+R\s*(-?\d[\d ]*\.\d{2})/i', $fullText, $m)) {
            $header['closing_balance'] = (float) str_replace(' ', '', $m[1]);
        }

        // From Date / To Date: "01/03/2026" and "30/04/2026"
        if (preg_match('/From\s+Date:\s*(\d{2}\/\d{2}\/\d{4})/i', $fullText, $m)) {
            $parts = explode('/', $m[1]);
            $header['period_from'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        if (preg_match('/To\s+Date:\s*(\d{2}\/\d{2}\/\d{4})/i', $fullText, $m)) {
            $parts = explode('/', $m[1]);
            $header['period_to'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        if (!empty($header['period_from']) && !empty($header['period_to'])) {
            $header['statement_period'] = date('d F Y', strtotime($header['period_from']))
                                        . ' - ' . date('d F Y', strtotime($header['period_to']));
        }

        // Print date as statement date
        if (preg_match('/Print\s+Date:\s*(\d{2}\/\d{2}\/\d{4})/i', $fullText, $m)) {
            $parts = explode('/', $m[1]);
            $header['statement_date'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        // Statement number: auto-generate as YYMM from period start
        if (!empty($header['period_from'])) {
            $header['statement_number'] = date('y', strtotime($header['period_from']))
                                        . date('m', strtotime($header['period_from']));
        }

        // Total fees: "-R199.80"
        $totalFees = 0;
        if (preg_match('/Total\s+Fees\s+-?R\s*(-?\d[\d ]*\.\d{2})/i', $fullText, $m)) {
            $totalFees = -1 * abs((float) str_replace(' ', '', $m[1]));
        }

        // --- PASS 1: Identify date lines in Transaction History ---
        $numPattern = '/-?\d{1,3}(?:[ ]\d{3})*\.\d{2}(?!\d)/';
        $dateLineData = [];
        $inTransactions = false;

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);

            if (preg_match('/Transaction\s+History/i', $trimmed)) {
                $inTransactions = true;
                continue;
            }
            if (!$inTransactions) continue;

            // Skip column headers (repeat on each page)
            if (preg_match('/^Date\s+Description/i', $trimmed)) continue;

            // Skip footers
            if (preg_match('/^(24hr\s+Client|Capitec\s+Bank\s+is|Unique\s+Document|Page\s+\d|\*\s*Includes\s+VAT)/i', $trimmed)) continue;

            if (preg_match('/^(\d{2}\/\d{2}\/\d{4})\s+(.*)$/', $trimmed, $dm)) {
                $dateParts = explode('/', $dm[1]);
                $isoDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];

                $restOfLine = $dm[2];

                // Strip asterisks from amounts before extraction (VAT marker)
                $cleanLine = preg_replace('/(\d\.\d{2})\*/', '$1', $restOfLine);

                // Extract all amounts
                preg_match_all($numPattern, $cleanLine, $nums);
                $numbers = array_map(function ($n) {
                    return (float) str_replace(' ', '', $n);
                }, $nums[0]);

                // Description = text before the first number
                $descType = $cleanLine;
                if (!empty($nums[0])) {
                    $firstNumPos = strpos($cleanLine, $nums[0][0]);
                    if ($firstNumPos !== false) {
                        $descType = substr($cleanLine, 0, $firstNumPos);
                    }
                }
                $descType = trim($descType);

                $dateLineData[] = [
                    'lineNum' => $lineNum,
                    'date'    => $isoDate,
                    'descType'=> $descType,
                    'numbers' => $numbers,
                ];
            }
        }

        if (empty($dateLineData)) {
            return $this->buildParseResult($header, []);
        }

        // --- PASS 2: Assign continuation lines to nearest date line ---
        $txnRefLines = array_fill(0, count($dateLineData), []);
        $dateLineNums = array_column($dateLineData, 'lineNum');
        $inTransactions = false;

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            if (preg_match('/Transaction\s+History/i', $trimmed)) {
                $inTransactions = true;
                continue;
            }
            if (!$inTransactions) continue;

            if (in_array($lineNum, $dateLineNums)) continue;

            // Skip headers, footers, column labels, summaries
            if (preg_match('/^(Date\s+Description|Money\s+In|Money\s+Out|Fee\*?|Balance|Category)\s*$/i', $trimmed)) continue;
            if (preg_match('/^(24hr\s+Client|Capitec\s+Bank\s+is|Unique\s+Document|Page\s+\d|\*\s*Includes\s+VAT)/i', $trimmed)) continue;
            if (preg_match('/^(Scheduled\s+Payments|Spending\s+Summary|Money\s+In\s+Summary|Money\s+Out\s+Summary|Live\s+Better|Fee\s+Summary|Debit\s+Orders|Card\s+Subscriptions)/i', $trimmed)) continue;
            if (preg_match('/^(SkyQR|App\s+Store|Google\s+Play|Validate\s+this)/i', $trimmed)) continue;

            // Find nearest date line
            $nearestIdx = 0;
            $nearestDist = PHP_INT_MAX;
            foreach ($dateLineData as $idx => $dl) {
                $dist = abs($lineNum - $dl['lineNum']);
                if ($dist < $nearestDist || ($dist === $nearestDist && $idx > $nearestIdx)) {
                    $nearestDist = $dist;
                    $nearestIdx = $idx;
                }
            }

            $txnRefLines[$nearestIdx][] = $trimmed;
        }

        // --- PASS 3: Build transactions with calculated running balance ---
        $transactions = [];
        $runningBalance = $header['opening_balance'];

        foreach ($dateLineData as $idx => $dl) {
            $numbers = $dl['numbers'];
            if (empty($numbers)) continue;

            // Build full description
            $refText = implode(' ', $txnRefLines[$idx] ?? []);
            $description = trim($dl['descType'] . ' ' . $refText);
            $description = preg_replace('/\s+/', ' ', $description);
            if (empty($description)) continue;

            // Fee-only entries: category ends with "Fees" and <=2 numbers (fee + balance)
            $isFeeOnly = (count($numbers) <= 2 && preg_match('/\bFees?\s*$/i', $dl['descType']));

            if (count($numbers) >= 3 && !$isFeeOnly) {
                // Regular transaction with attached fee
                $amount = $numbers[0];
                $fee = $numbers[1];

                $runningBalance = round($runningBalance + $amount, 2);
                $transactions[] = [
                    'date'        => $dl['date'],
                    'description' => $description,
                    'amount'      => $amount,
                    'balance'     => $runningBalance,
                ];

                // Separate fee line with parent description
                if (abs($fee) > 0.001) {
                    $runningBalance = round($runningBalance + $fee, 2);
                    $transactions[] = [
                        'date'        => $dl['date'],
                        'description' => 'FEE - ' . $description,
                        'amount'      => $fee,
                        'balance'     => $runningBalance,
                    ];
                }
            } elseif (count($numbers) >= 2) {
                if ($isFeeOnly) {
                    // Standalone fee entry: first number is the fee amount
                    $fee = $numbers[0];
                    $runningBalance = round($runningBalance + $fee, 2);
                    $transactions[] = [
                        'date'        => $dl['date'],
                        'description' => $description,
                        'amount'      => $fee,
                        'balance'     => $runningBalance,
                    ];
                } else {
                    // Regular transaction without fee
                    $amount = $numbers[0];
                    $runningBalance = round($runningBalance + $amount, 2);
                    $transactions[] = [
                        'date'        => $dl['date'],
                        'description' => $description,
                        'amount'      => $amount,
                        'balance'     => $runningBalance,
                    ];
                }
            }
        }

        // Fill header gaps
        if (!empty($transactions)) {
            if (empty($header['closing_balance'])) {
                $header['closing_balance'] = end($transactions)['balance'];
            }
            if (empty($header['period_from'])) {
                $header['period_from'] = $transactions[0]['date'];
            }
            if (empty($header['period_to'])) {
                $header['period_to'] = end($transactions)['date'];
            }
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  STANDARD BANK PARSER
    // =========================================================================

    /**
     * Parse Standard Bank statement text (balance-difference via finalizeBalanceDiffTxn).
     */
    private function parseStandardText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines = explode("\n", $allText);

        $header = [
            'bank'             => 'Standard Bank',
            'account_number'   => null,
            'statement_period' => null,
            'opening_balance'  => null,
            'closing_balance'  => null,
            'account_holder'   => null,
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'bank_charges'     => [],
        ];

        // Account number
        if (preg_match('/Account\s+(?:Number|No)\s*:?\s*(\d{6,15})/i', $allText, $m)) {
            $header['account_number'] = $m[1];
        }

        // Account holder
        if (preg_match('/Account\s+Holder\s*:?\s*(.+)/i', $allText, $m)) {
            $header['account_holder'] = trim($m[1]);
        }

        // Statement number
        if (preg_match('/Statement\s+(?:No|Number)\s*:?\s*(\d+)/i', $allText, $m)) {
            $header['statement_number'] = $m[1];
        }

        $months = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'may' => '05', 'jun' => '06', 'jul' => '07', 'aug' => '08',
            'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12',
        ];

        $inTransactions = false;
        $transactions = [];
        $prevBalance = null;
        $currentDate = null;
        $textLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // "STATEMENT OPENING BALANCE" starts
            if (preg_match('/STATEMENT\s+OPENING\s+BALANCE/i', $trimmed)) {
                $inTransactions = true;

                // Extract opening balance
                if (preg_match('/([\d,]+\.\d{2})\s*$/', $trimmed, $bm)) {
                    $header['opening_balance'] = (float) str_replace(',', '', $bm[1]);
                    $prevBalance = $header['opening_balance'];
                }
                continue;
            }

            // "Statement Summary" ends
            if (preg_match('/Statement\s+Summary/i', $trimmed)) {
                // Finalize pending transaction
                if ($currentDate !== null && !empty($textLines)) {
                    $txn = $this->finalizeBalanceDiffTxn($currentDate, $textLines, $prevBalance);
                    if ($txn) {
                        $transactions[] = $txn;
                        $prevBalance = $txn['balance'];
                    }
                }
                $inTransactions = false;
                continue;
            }

            if (!$inTransactions) continue;

            // Date: DD Mon YY -> 20YY-MM-DD
            if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{2})\s+(.*)$/', $trimmed, $dm)) {
                // Finalize previous transaction
                if ($currentDate !== null && !empty($textLines)) {
                    $txn = $this->finalizeBalanceDiffTxn($currentDate, $textLines, $prevBalance);
                    if ($txn) {
                        $transactions[] = $txn;
                        $prevBalance = $txn['balance'];
                    }
                }

                $day = str_pad($dm[1], 2, '0', STR_PAD_LEFT);
                $monthStr = strtolower($dm[2]);
                $monthNum = $months[$monthStr] ?? '01';
                $yearShort = $dm[3];
                $yearFull = '20' . $yearShort;

                $currentDate = sprintf('%s-%s-%s', $yearFull, $monthNum, $day);
                $textLines = [trim($dm[4])];
            } else {
                // Continuation line
                if ($currentDate !== null && !empty($trimmed)) {
                    $textLines[] = $trimmed;
                }
            }
        }

        // Finalize last pending transaction
        if ($currentDate !== null && !empty($textLines)) {
            $txn = $this->finalizeBalanceDiffTxn($currentDate, $textLines, $prevBalance);
            if ($txn) {
                $transactions[] = $txn;
            }
        }

        // Closing balance / period
        if (!empty($transactions)) {
            $header['closing_balance'] = $header['closing_balance'] ?? end($transactions)['balance'];
            $header['period_from'] = $header['period_from'] ?? $transactions[0]['date'];
            $header['period_to'] = $header['period_to'] ?? end($transactions)['date'];
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  STANDARD BANK (NEW FORMAT) — Added June 2026
    //  Separate parser for the new Private Banking statement layout.
    //  DO NOT modify parseStandardText() — old format still in use.
    // =========================================================================
    private function parseStandardNewText(array $pages): array
    {
        $allText = implode("\n", $pages);
        $lines  = explode("\n", $allText);

        $header = [
            'bank'             => 'Standard Bank (New Format)',
            'account_number'   => null,
            'statement_period' => null,
            'opening_balance'  => null,
            'closing_balance'  => null,
            'account_holder'   => null,
            'statement_number' => null,
            'period_from'      => null,
            'period_to'        => null,
            'bank_charges'     => [],
        ];

        $months = [
            'january'=>'01','february'=>'02','march'=>'03','april'=>'04',
            'may'=>'05','june'=>'06','july'=>'07','august'=>'08',
            'september'=>'09','october'=>'10','november'=>'11','december'=>'12',
        ];

        $fromMonth = null; $fromYear = null;
        $toMonth   = null; $toYear   = null;

        if (preg_match('/Statement\s+from\s+(\d{1,2})\s+(\w+)\s+(\d{4})\s+to\s+(\d{1,2})\s+(\w+)\s+(\d{4})/i', $allText, $pm)) {
            $fM = $months[strtolower($pm[2])] ?? '01';
            $tM = $months[strtolower($pm[5])] ?? '01';
            $header['period_from'] = sprintf('%s-%s-%s', $pm[3], $fM, str_pad($pm[1],2,'0',STR_PAD_LEFT));
            $header['period_to']   = sprintf('%s-%s-%s', $pm[6], $tM, str_pad($pm[4],2,'0',STR_PAD_LEFT));
            $header['statement_period'] = trim($pm[0]);
            $fromMonth = (int)$fM;  $fromYear = (int)$pm[3];
            $toMonth   = (int)$tM;  $toYear   = (int)$pm[6];
        }

        if (preg_match('/Account\s+Number\s+([\d\s]{8,})/i', $allText, $am)) {
            $header['account_number'] = trim($am[1]);
        }
        if (preg_match('/Statement\s*\/\s*Invoice\s+No\s*:?\s*(\d+)/i', $allText, $sm)) {
            $header['statement_number'] = $sm[1];
        }

        $inTxn       = false;
        $openingDone = false;
        $transactions = [];
        $prevBalance  = null;
        $currentTxn   = null;

        $skipPatterns = [
            '/^Details\s+Service/i',
            '/^Fee\s+Debits/i',
            '/^BANK\s+STATEMENT/i',
            '/^PRIVATE\s+BANKING/i',
            '/^Page\s+\d+/i',
            '/^Statement\s+Frequency/i',
            '/^Month-end\s+Balance/i',
            '/^##\s+These\s+fees/i',
            '/^Statement\s+from\s+\d/i',
            '/^Statement\s*\/\s*Invoice/i',
            '/^VAT\s+Reg/i',
            '/^MONTHLY\s+EMAIL/i',
            '/^BLUE\s+ROUTE/i',
            '/^PO\s+BOX/i',
            '/^Private\s+Banking\s+Contact/i',
            '/^e-?mail:/i',
            '/^\d{1,2}\s+(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}$/i',
            '/^MISS\s+|^MR\s+|^MRS\s+|^MS\s+/i',
            '/^\d+\s+[A-Z]+\s+(?:RD|ST|AVE|ROAD|STREET|DRIVE|LANE|CRESCENT|CLOSE)\b/i',
            '/^MARSHALLTOWN|^LANSDOWNE|^CAPE\s+TOWN|^JOHANNESBURG|^DURBAN|^PRETORIA/i',
            '/^\d{4}$/i',
            '/^Please\s+verify/i',
            '/^The\s+Standard\s+Bank/i',
            '/^VAT\s+Reg\s+No/i',
            '/^We\s+subscribe/i',
            '/^the\s+Ombudsman/i',
        ];

        $stopPatterns = [
            '/^VAT\s+Summary/i',
            '/^Account\s+Summary/i',
            '/^Limit\s+Structure/i',
            '/^Details\s+of\s+Agreement/i',
            '/^Summary\s+of\s+Transactions/i',
            '/^Nett\s+Payment/i',
            '/^Total\s+charge\s+amount/i',
            '/^Total\s+VAT/i',
            '/^Balance\s+outstanding/i',
            '/^Current\s+Limit/i',
            '/^Arranged\s+Limit/i',
            '/^This\s+document\s+constitutes/i',
            '/^\*Overdraft/i',
            '/^\*Private\s+Banking/i',
        ];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') continue;

            // Stop markers — finalize and exit
            foreach ($stopPatterns as $sp) {
                if (preg_match($sp, $t)) {
                    if ($currentTxn) { $transactions[] = $currentTxn; $currentTxn = null; }
                    $inTxn = false;
                    continue 2;
                }
            }

            // Skip header/footer noise
            foreach ($skipPatterns as $sp) {
                if (preg_match($sp, $t)) continue 2;
            }

            // BALANCE BROUGHT FORWARD
            if (preg_match('/BALANCE\s+BROUGHT\s+FORWARD/i', $t)) {
                if ($currentTxn) { $transactions[] = $currentTxn; $currentTxn = null; }

                if (preg_match('/([\d,]+\.\d{2})(-?)\s*$/', $t, $bm)) {
                    $bal = (float) str_replace(',', '', $bm[1]);
                    if ($bm[2] === '-') $bal = -$bal;

                    if (!$openingDone) {
                        $header['opening_balance'] = $bal;
                        $prevBalance = $bal;
                        $openingDone = true;
                        $inTxn = true;
                    }
                }
                continue;
            }

            if (!$inTxn) continue;

            // Transaction line: ... MM DD balance  (balance at end with optional trailing minus)
            if (preg_match('/([\d,]+\.\d{2})(-?)\s*$/', $t, $endNum)
                && preg_match('/\b(\d{2})\s+(\d{2})\s+[\d,]+\.\d{2}-?\s*$/', $t, $dm)) {

                $txnMonth = (int) $dm[1];
                $txnDay   = (int) $dm[2];

                // Validate month 1-12, day 1-31
                if ($txnMonth < 1 || $txnMonth > 12 || $txnDay < 1 || $txnDay > 31) {
                    if ($currentTxn) $currentTxn['description'] .= ' ' . $t;
                    continue;
                }

                // Finalize previous
                if ($currentTxn) $transactions[] = $currentTxn;

                // Balance
                $balance = (float) str_replace(',', '', $endNum[1]);
                if ($endNum[2] === '-') $balance = -$balance;

                // Year from statement period
                $txnYear = $fromYear ?? (int) date('Y');
                if ($fromYear && $toYear && $fromYear !== $toYear) {
                    $txnYear = ($txnMonth >= $fromMonth) ? $fromYear : $toYear;
                    if ($txnMonth <= $toMonth && $toYear > $fromYear) $txnYear = $toYear;
                }

                $date = sprintf('%04d-%02d-%02d', $txnYear, $txnMonth, $txnDay);

                // Amount via balance difference
                $amount = ($prevBalance !== null) ? round($balance - $prevBalance, 2) : 0;

                // Description: strip the trailing  "MM DD balance" and any amounts before it
                $desc = preg_replace('/\s*\d{2}\s+\d{2}\s+[\d,]+\.\d{2}-?\s*$/', '', $t);
                $desc = preg_replace('/\s+[\d,]+\.\d{2}-?\s*$/', '', $desc);
                $desc = preg_replace('/\s*##\s*/', ' ', $desc);
                $desc = trim(preg_replace('/\s+/', ' ', $desc));

                $currentTxn  = [
                    'date'        => $date,
                    'description' => $desc,
                    'amount'      => $amount,
                    'balance'     => $balance,
                ];
                $prevBalance = $balance;

            } else {
                // Description continuation
                if ($currentTxn) {
                    $currentTxn['description'] .= ' ' . $t;
                }
            }
        }

        if ($currentTxn) $transactions[] = $currentTxn;

        if (!empty($transactions)) {
            $header['closing_balance'] = end($transactions)['balance'];
        }

        return $this->buildParseResult($header, $transactions);
    }

    // =========================================================================
    //  SHARED HELPERS
    // =========================================================================

    /**
     * Finalize a balance-difference transaction.
     * Joins textLines, extracts all decimals, last = balance, diff from prevBalance.
     * Strips last 2 numbers from text for description.
     */
    private function finalizeBalanceDiffTxn(string $date, array $textLines, ?float $prevBalance): ?array
    {
        $fullText = implode(' ', $textLines);

        // Extract all decimal numbers
        preg_match_all('/([\d,]+\.\d{2})/', $fullText, $numMatches);
        $numbers = array_map(function ($n) { return (float) str_replace(',', '', $n); }, $numMatches[1] ?? []);

        if (empty($numbers)) return null;

        // Last number = balance
        $balance = end($numbers);

        // Amount = balance - prevBalance
        if ($prevBalance !== null) {
            $amount = round($balance - $prevBalance, 2);
        } else {
            // Fallback: second-to-last as amount
            $amount = count($numbers) >= 2 ? $numbers[count($numbers) - 2] : 0;
        }

        // Description: strip last 2 numbers from text
        $description = $fullText;
        // Remove numbers from end (balance and amount)
        $numCount = min(2, count($numbers));
        for ($i = 0; $i < $numCount; $i++) {
            $lastPos = strrpos($description, $numMatches[1][count($numMatches[1]) - 1 - $i]);
            if ($lastPos !== false) {
                $description = substr($description, 0, $lastPos);
            }
        }
        $description = trim(preg_replace('/\s+/', ' ', $description));

        return [
            'date'        => $date,
            'description' => $description,
            'amount'      => $amount,
            'balance'     => $balance,
        ];
    }

    /**
     * =====================================================================
     * CAPITEC (MERCANTILE BANK) - Transaction Finalizer
     * =====================================================================
     * LOCKED - Tested & Working - 25 May 2026
     *
     * Helper for parseCapitecText(). Extracts amount (second-last decimal)
     * and balance (last decimal) from joined transaction text lines.
     * Strips those numbers to produce a clean description.
     *
     * DO NOT MODIFY without full regression testing on known-good statements.
     * =====================================================================
     */
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

    /**
     * Build the final parse result with summary computations.
     */
    private function buildParseResult(array $header, array $transactions): array
    {
        $totalCredits = 0;
        $totalDebits = 0;
        $creditCount = 0;
        $debitCount = 0;

        foreach ($transactions as $txn) {
            $amt = (float) ($txn['amount'] ?? 0);
            if ($amt >= 0) {
                $totalCredits += $amt;
                $creditCount++;
            } else {
                $totalDebits += abs($amt);
                $debitCount++;
            }
        }

        $openingBalance = $header['opening_balance'] ?? 0;
        $closingBalance = $header['closing_balance'] ?? 0;

        // calculated_closing = opening + credits - debits
        $calculatedClosing = round($openingBalance + $totalCredits - $totalDebits, 2);

        // balance_match = abs(calculated - actual) < 0.02
        $balanceMatch = abs($calculatedClosing - $closingBalance) < 0.02;

        $firstDate = !empty($transactions) ? $transactions[0]['date'] : null;
        $lastDate = !empty($transactions) ? end($transactions)['date'] : null;

        return [
            'header'       => $header,
            'transactions' => $transactions,
            'summary'      => [
                'total_credits'      => round($totalCredits, 2),
                'total_debits'       => round($totalDebits, 2),
                'credit_count'       => $creditCount,
                'debit_count'        => $debitCount,
                'opening_balance'    => $openingBalance,
                'closing_balance'    => $closingBalance,
                'calculated_closing' => $calculatedClosing,
                'balance_match'      => $balanceMatch,
                'transaction_count'  => count($transactions),
                'first_date'         => $firstDate,
                'last_date'          => $lastDate,
            ],
        ];
    }

    // =========================================================================
    //  FNB FIXER — Exact copy of the original CIMS AccountsController parser
    //  DO NOT MODIFY — this is a reference baseline
    // =========================================================================

    public function parsePdfFixer(Request $request, $clientId, $bankId)
    {
        $pages = $request->input('pages', []);
        $ocrPages = $request->input('ocr_pages', []);

        if (empty($pages)) {
            return response()->json(['error' => 'No text data received.'], 400);
        }

        $result = $this->parseFnbTextFixer($pages, $ocrPages);
        $result['_parser_version'] = 'CIMS-ORIGINAL-FIXER';
        return response()->json($result);
    }

    private function parseFnbTextFixer(array $pages, array $ocrPages = []): array
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

        $header = $this->parseFnbHeaderFixer($lines);
        $transactions = $this->parseFnbTransactionsFixer($lines, $header);

        if (!empty($ocrPages)) {
            $ocrTxns = $this->parseFnbOcrTransactionsFixer($ocrPages);

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

        return $this->buildParseResultFixer($header, $transactions);
    }

    private function parseFnbOcrTransactionsFixer(array $ocrPages): array
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

    private function parseFnbHeaderFixer(array $lines): array
    {
        $header = ['account_number' => '', 'account_holder' => '', 'branch_code' => '', 'statement_period' => '', 'statement_date' => '', 'statement_number' => null, 'period_from' => null, 'period_to' => null, 'opening_balance' => 0, 'closing_balance' => 0, 'bank_charges' => []];
        $fullText = implode(' ', $lines);
        if (preg_match('/(?:Platinum\s+Business\s+Account|Business\s+Account|Cheque\s+Account)\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        elseif (preg_match('/Account\s*(?:No|Number|#)?\s*:?\s*(\d{8,15})/i', $fullText, $m)) $header['account_number'] = $m[1];
        if (preg_match('/Universal\s+Branch\s+Code\s*:?\s*(\d+)/i', $fullText, $m)) $header['branch_code'] = $m[1];
        if (preg_match('/Statement\s+Period\s*:?\s*(.+?)(?:Statement\s+Date|$)/i', $fullText, $m)) $header['statement_period'] = trim($m[1]);
        if (preg_match('/Statement\s+Date\s*:?\s*(\d{1,2}\s+\w+\s+\d{4})/i', $fullText, $m)) $header['statement_date'] = trim($m[1]);
        if (preg_match('/Statement\s+(?:No|Number)\s*:?\s*(\d+)/i', $fullText, $m)) $header['statement_number'] = $m[1];
        if (!empty($header['statement_period']) && preg_match('/(\d{1,2}\s+[A-Za-z]+\s+\d{4})\s+to\s+(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $header['statement_period'], $pm)) { $header['period_from'] = date('Y-m-d', strtotime($pm[1])); $header['period_to'] = date('Y-m-d', strtotime($pm[2])); }
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

    private function parseFnbTransactionsFixer(array $lines, array $header): array
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

    private function buildParseResultFixer(array $header, array $transactions): array
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
}
