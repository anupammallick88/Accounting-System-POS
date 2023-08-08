<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypesOfService extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'location_price_group' => 'array'
    ];

    /**
     * Return list of types of service for a business
     *
     * @param int $business_id
     *
     * @return array
     */
    public static function forDropdown($business_id)
    {
        $types_of_service = TypesOfService::where('business_id', $business_id)
                    ->pluck('name', 'id');

        return $types_of_service;
    }
}
