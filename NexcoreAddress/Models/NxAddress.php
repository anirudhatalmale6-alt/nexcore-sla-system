<?php

namespace Modules\NexcoreAddress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NxAddress extends Model
{
    use SoftDeletes;

    protected $table = 'nx_addresses';

    protected $fillable = [
        'unit_number',
        'complex_name',
        'street_number',
        'street_name',
        'suburb_id',
        'city',
        'postal_code',
        'province_id',
        'municipality_id',
        'ward_id',
        'country',
        'latitude',
        'longitude',
        'google_formatted_address',
        'address_category',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'latitude'   => 'decimal:8',
        'longitude'  => 'decimal:8',
    ];

    public function province()
    {
        return $this->belongsTo(\Modules\CIMS_PMPRO\Models\PmproProvince::class, 'province_id');
    }

    public function localMunicipality()
    {
        return $this->belongsTo(\Modules\CIMS_PMPRO\Models\PmproLocalMunicipality::class, 'municipality_id');
    }

    public function metro()
    {
        return $this->belongsTo(\Modules\CIMS_PMPRO\Models\PmproMetro::class, 'municipality_id');
    }

    public function getMunicipalityNameAttribute()
    {
        if ($this->localMunicipality) return $this->localMunicipality->name;
        if ($this->metro) return $this->metro->name;
        return null;
    }

    public function ward()
    {
        return $this->belongsTo(\Modules\CIMS_PMPRO\Models\PmproWard::class, 'ward_id');
    }

    public function suburb()
    {
        return $this->belongsTo(\Modules\CIMS_PMPRO\Models\PmproSuburb::class, 'suburb_id');
    }

    public function details()
    {
        return $this->hasOne(NxAddressDetail::class, 'address_id');
    }

    public function links()
    {
        return $this->hasMany(NxAddressLink::class, 'address_id');
    }

    public function getFormattedAddressAttribute()
    {
        $parts = array_filter([
            $this->unit_number ? 'Unit ' . $this->unit_number : null,
            $this->complex_name,
            trim($this->street_number . ' ' . $this->street_name),
            $this->suburb ? $this->suburb->name : null,
            $this->city,
            $this->province ? $this->province->name : null,
            $this->postal_code,
        ]);
        return implode(', ', $parts);
    }

    public function getShortAddressAttribute()
    {
        $parts = array_filter([
            trim($this->street_number . ' ' . $this->street_name),
            $this->city,
            $this->postal_code,
        ]);
        return implode(', ', $parts);
    }
}
