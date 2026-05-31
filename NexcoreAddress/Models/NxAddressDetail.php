<?php

namespace Modules\NexcoreAddress\Models;

use Illuminate\Database\Eloquent\Model;

class NxAddressDetail extends Model
{
    protected $table = 'nx_address_details';

    protected $fillable = [
        'address_id',
        'floor_level',
        'building_name',
        'estate_name',
        'section_number',
        'farm_name',
        'farm_number',
        'stand_number',
        'erf_number',
        'sg_code',
        'municipal_account_number',
        'plus_code',
        'what3words',
        'google_place_id',
        'map_url',
        'address_source',
        'is_verified',
        'verified_date',
    ];

    protected $casts = [
        'is_verified'   => 'boolean',
        'verified_date' => 'date',
    ];

    public function address()
    {
        return $this->belongsTo(NxAddress::class, 'address_id');
    }
}
