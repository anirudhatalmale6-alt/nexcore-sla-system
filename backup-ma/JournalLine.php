<?php

namespace Modules\CIMS_ACCOUNTS\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $table = 'cims_accounts_journal_lines';

    protected $fillable = [
        'journal_id', 'account_id', 'description',
        'debit_amount', 'credit_amount', 'vat_amount', 'vat_type', 'line_order',
        'ma_hidden', 'note',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
