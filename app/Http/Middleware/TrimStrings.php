<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as BaseTrimmer;

class TrimStrings extends BaseTrimmer
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
        'prefix', //Invoice scheme prefix
        'ref_no_prefixes.purchase', //Business settings prefixes
        'ref_no_prefixes.purchase_return',
        'ref_no_prefixes.stock_transfer',
        'ref_no_prefixes.stock_adjustment',
        'ref_no_prefixes.sell_return',
        'ref_no_prefixes.expense',
        'ref_no_prefixes.contacts',
        'ref_no_prefixes.purchase_payment',
        'ref_no_prefixes.sell_payment',
        'ref_no_prefixes.expense_payment',
        'ref_no_prefixes.business_location',
        'ref_no_prefixes.username',
        'ref_no_prefixes.subscription',
        'ref_no_prefixes.draft'
    ];
}
