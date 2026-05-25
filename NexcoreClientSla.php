<?php

namespace Modules\NexcoreClientManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NexcoreClientSla extends Model
{
    use SoftDeletes;

    protected $table = 'nexcore_client_sla';

    protected $fillable = [
        'client_id',
        'sla_reference',
        'signatory_name',
        'signatory_id_number',
        'signatory_email',
        'signatory_cellphone',
        'signatory_designation',
        'province',
        'emergency_name',
        'emergency_relationship',
        'emergency_phone',
        'emergency_email',
        'emergency_consent',
        'tax_reference_number',
        'coida_rma_number',
        'vat_number',
        'paye_number',
        'uif_number',
        'applying_for',
        'company_reg_number',
        'business_name',
        'nature_of_business',
        'physical_address',
        'postal_address',
        'work_telephone',
        'marital_status',
        'selected_package',
        'service_consent',
        'bank_account_holder',
        'bank_name',
        'bank_branch_code',
        'bank_account_number',
        'bank_account_type',
        'debit_order_date',
        'debit_order_consent',
        'signature_data',
        'signature_type',
        'signed_at_location',
        'signed_date',
        'status',
        'sent_date',
        'effective_date',
        'termination_date',
        'termination_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'signed_date' => 'date',
        'sent_date' => 'date',
        'effective_date' => 'date',
        'termination_date' => 'date',
        'emergency_consent' => 'boolean',
        'service_consent' => 'boolean',
        'debit_order_consent' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(NexcoreClient::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public static function generateReference()
    {
        $prefix = 'ATP-SLA';
        $year = date('Y');
        $latest = static::withTrashed()
            ->where('sla_reference', 'LIKE', "{$prefix}-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $next = $latest ? (int) substr($latest->sla_reference, -4) + 1 : 1;
        return sprintf('%s-%s-%04d', $prefix, $year, $next);
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'draft' => ['label' => 'Draft', 'color' => '#64748b'],
            'sent' => ['label' => 'Sent', 'color' => '#3b82f6'],
            'viewed' => ['label' => 'Viewed', 'color' => '#f59e0b'],
            'signed' => ['label' => 'Signed', 'color' => '#10b981'],
            'active' => ['label' => 'Active', 'color' => '#06b6d4'],
            'terminated' => ['label' => 'Terminated', 'color' => '#ef4444'],
            'expired' => ['label' => 'Expired', 'color' => '#6b7280'],
            default => ['label' => ucfirst($this->status), 'color' => '#64748b'],
        };
    }
}
