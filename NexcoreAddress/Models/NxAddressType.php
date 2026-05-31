<?php

namespace Modules\NexcoreAddress\Models;

use Illuminate\Database\Eloquent\Model;

class NxAddressType extends Model
{
    protected $table = 'nx_address_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }
}
