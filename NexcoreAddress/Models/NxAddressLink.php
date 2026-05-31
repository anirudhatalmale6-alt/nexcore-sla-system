<?php

namespace Modules\NexcoreAddress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NxAddressLink extends Model
{
    use SoftDeletes;

    protected $table = 'nx_address_links';

    protected $fillable = [
        'address_id',
        'linkable_type',
        'linkable_id',
        'address_type_id',
        'address_label',
        'notes',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function address()
    {
        return $this->belongsTo(NxAddress::class, 'address_id');
    }

    public function addressType()
    {
        return $this->belongsTo(NxAddressType::class, 'address_type_id');
    }

    public function linkable()
    {
        return $this->morphTo();
    }
}
