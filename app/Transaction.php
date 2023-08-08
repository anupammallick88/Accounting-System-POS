<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //Transaction types = ['purchase','sell','expense','stock_adjustment','sell_transfer','purchase_transfer','opening_stock','sell_return','opening_balance','purchase_return', 'payroll', 'expense_refund', 'sales_order', 'purchase_order']

    //Transaction status = ['received','pending','ordered','draft','final', 'in_transit', 'completed']

    
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
        'purchase_order_ids' => 'array',
        'sales_order_ids' => 'array',
        'export_custom_fields_info' => 'array',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';
    
    public function purchase_lines()
    {
        return $this->hasMany(\App\PurchaseLine::class);
    }

    public function sell_lines()
    {
        return $this->hasMany(\App\TransactionSellLine::class);
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function payment_lines()
    {
        return $this->hasMany(\App\TransactionPayment::class, 'transaction_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    public function tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax_id');
    }

    public function stock_adjustment_lines()
    {
        return $this->hasMany(\App\StockAdjustmentLine::class);
    }

    public function sales_person()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function sale_commission_agent()
    {
        return $this->belongsTo(\App\User::class, 'commission_agent');
    }

    public function return_parent()
    {
        return $this->hasOne(\App\Transaction::class, 'return_parent_id');
    }

    public function return_parent_sell()
    {
        return $this->belongsTo(\App\Transaction::class, 'return_parent_id');
    }

    public function table()
    {
        return $this->belongsTo(\App\Restaurant\ResTable::class, 'res_table_id');
    }

    public function service_staff()
    {
        return $this->belongsTo(\App\User::class, 'res_waiter_id');
    }

    public function recurring_invoices()
    {
        return $this->hasMany(\App\Transaction::class, 'recur_parent_id');
    }

    public function recurring_parent()
    {
        return $this->hasOne(\App\Transaction::class, 'id', 'recur_parent_id');
    }

    public function price_group()
    {
        return $this->belongsTo(\App\SellingPriceGroup::class, 'selling_price_group_id');
    }

    public function types_of_service()
    {
        return $this->belongsTo(\App\TypesOfService::class, 'types_of_service_id');
    }

    /**
     * Retrieves documents path if exists
     */
    public function getDocumentPathAttribute()
    {
        $path = !empty($this->document) ? asset('/uploads/documents/' . $this->document) : null;
        
        return $path;
    }

    /**
     * Removes timestamp from document name
     */
    public function getDocumentNameAttribute()
    {
        $document_name = !empty(explode("_", $this->document, 2)[1]) ? explode("_", $this->document, 2)[1] : $this->document ;
        return $document_name;
    }

    public function subscription_invoices()
    {
        return $this->hasMany(\App\Transaction::class, 'recur_parent_id');
    }

    /**
     * Shipping address custom method
     */
    public function shipping_address($array = false)
    {
        $addresses = !empty($this->order_addresses) ? json_decode($this->order_addresses, true) : [];

        $shipping_address = [];

        if (!empty($addresses['shipping_address'])) {
            if (!empty($addresses['shipping_address']['shipping_name'])) {
                $shipping_address['name'] = $addresses['shipping_address']['shipping_name'];
            }
            if (!empty($addresses['shipping_address']['company'])) {
                $shipping_address['company'] = $addresses['shipping_address']['company'];
            }
            if (!empty($addresses['shipping_address']['shipping_address_line_1'])) {
                $shipping_address['address_line_1'] = $addresses['shipping_address']['shipping_address_line_1'];
            }
            if (!empty($addresses['shipping_address']['shipping_address_line_2'])) {
                $shipping_address['address_line_2'] = $addresses['shipping_address']['shipping_address_line_2'];
            }
            if (!empty($addresses['shipping_address']['shipping_city'])) {
                $shipping_address['city'] = $addresses['shipping_address']['shipping_city'];
            }
            if (!empty($addresses['shipping_address']['shipping_state'])) {
                $shipping_address['state'] = $addresses['shipping_address']['shipping_state'];
            }
            if (!empty($addresses['shipping_address']['shipping_country'])) {
                $shipping_address['country'] = $addresses['shipping_address']['shipping_country'];
            }
            if (!empty($addresses['shipping_address']['shipping_zip_code'])) {
                $shipping_address['zipcode'] = $addresses['shipping_address']['shipping_zip_code'];
            }
        }

        if ($array) {
            return $shipping_address;
        } else {
            return implode(', ', $shipping_address);
        }
    }

    /**
     * billing address custom method
     */
    public function billing_address($array = false)
    {
        $addresses = !empty($this->order_addresses) ? json_decode($this->order_addresses, true) : [];

        $billing_address = [];

        if (!empty($addresses['billing_address'])) {
            if (!empty($addresses['billing_address']['billing_name'])) {
                $billing_address['name'] = $addresses['billing_address']['billing_name'];
            }
            if (!empty($addresses['billing_address']['company'])) {
                $billing_address['company'] = $addresses['billing_address']['company'];
            }
            if (!empty($addresses['billing_address']['billing_address_line_1'])) {
                $billing_address['address_line_1'] = $addresses['billing_address']['billing_address_line_1'];
            }
            if (!empty($addresses['billing_address']['billing_address_line_2'])) {
                $billing_address['address_line_2'] = $addresses['billing_address']['billing_address_line_2'];
            }
            if (!empty($addresses['billing_address']['billing_city'])) {
                $billing_address['city'] = $addresses['billing_address']['billing_city'];
            }
            if (!empty($addresses['billing_address']['billing_state'])) {
                $billing_address['state'] = $addresses['billing_address']['billing_state'];
            }
            if (!empty($addresses['billing_address']['billing_country'])) {
                $billing_address['country'] = $addresses['billing_address']['billing_country'];
            }
            if (!empty($addresses['billing_address']['billing_zip_code'])) {
                $billing_address['zipcode'] = $addresses['billing_address']['billing_zip_code'];
            }
        }

        if ($array) {
            return $billing_address;
        } else {
            return implode(', ', $billing_address);
        }
    }

    public function cash_register_payments()
    {
        return $this->hasMany(\App\CashRegisterTransaction::class);
    }

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function transaction_for()
    {
        return $this->belongsTo(\App\User::class, 'expense_for');
    }

    /**
     * Returns preferred account for payment.
     * Used in download pdfs
     */
    public function preferredAccount()
    {
        return $this->belongsTo(\App\Account::class, 'prefer_payment_account');
    }
    
    /**
     * Returns the list of discount types.
     */
    public static function discountTypes()
    {
        return [
                'fixed' => __('lang_v1.fixed'),
                'percentage' => __('lang_v1.percentage')
            ];
    }

    public static function transactionTypes()
    {
        return  [
                'sell' => __('sale.sale'),
                'purchase' => __('lang_v1.purchase'),
                'sell_return' => __('lang_v1.sell_return'),
                'purchase_return' =>  __('lang_v1.purchase_return'),
                'opening_balance' => __('lang_v1.opening_balance'),
                'payment' => __('lang_v1.payment'),
                'advance_balance'=>__('lang_v1.advance_payment')
                
            ];
    }

    public static function getPaymentStatus($transaction)
    {
        $payment_status = $transaction->payment_status;

        if (in_array($payment_status, ['partial', 'due']) && !empty($transaction->pay_term_number) && !empty($transaction->pay_term_type)) {
            $transaction_date = \Carbon::parse($transaction->transaction_date);
            $due_date = $transaction->pay_term_type == 'days' ? $transaction_date->addDays($transaction->pay_term_number) : $transaction_date->addMonths($transaction->pay_term_number);
            $now = \Carbon::now();
            if ($now->gt($due_date)) {
                $payment_status = $payment_status == 'due' ? 'overdue' : 'partial-overdue';
            }
        }

        return $payment_status;
    }

    /**
     * Due date custom attribute
     */
    public function getDueDateAttribute()
    {
        $transaction_date = \Carbon::parse($this->transaction_date);
        if (!empty($this->pay_term_type) && !empty($this->pay_term_number)) {
            $due_date = $this->pay_term_type == 'days' ? $transaction_date->addDays($this->pay_term_number) : $transaction_date->addMonths($this->pay_term_number);
        } else {
            $due_date = $transaction_date->addDays(0);
        }

        return $due_date;
    }

    public static function getSellStatuses()
    {
        return ['final' => __('sale.final'), 'draft' => __('sale.draft'), 'quotation' => __('lang_v1.quotation'), 'proforma' => __('lang_v1.proforma')];
    }

    /**
     * Attributes to be logged for activity
     */
    public function getLogPropertiesAttribute() {
        $properties = [];

        if (in_array($this->type, ['sell_transfer'])) {
            $properties = ['status'];
        } elseif (in_array($this->type, ['sell'])) {
            $properties = ['type', 'status', 'sub_status', 'shipping_status', 'payment_status', 'final_total'];
        } elseif (in_array($this->type, ['purchase'])) {
            $properties = ['type', 'status', 'payment_status', 'final_total'];
        } elseif (in_array($this->type, ['expense'])) {
            $properties = ['payment_status'];
        } elseif (in_array($this->type, ['sell_return'])) {
            $properties = ['type', 'payment_status', 'final_total'];
        } elseif (in_array($this->type, ['purchase_return'])) {
            $properties = ['type', 'payment_status', 'final_total'];
        }

        return $properties;
    }

    public function scopeOverDue($query)
    {
        return $query->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
    }

    public static function sell_statuses()
    {
        return [
            'final' => __('sale.final'), 
            'draft' => __('sale.draft'), 
            'quotation' => __('lang_v1.quotation'), 
            'proforma' => __('lang_v1.proforma')
        ];
    }

    public static function sales_order_statuses($only_key_value = false)
    {
        if ($only_key_value) {
           return [
                'ordered' => __('lang_v1.ordered'),
                'partial' => __('lang_v1.partial'),
                'completed' => __('restaurant.completed')
            ];
        }
        return [
            'ordered' => [
                'label' => __('lang_v1.ordered'),
                'class' => 'bg-info'
            ],
            'partial' => [
                'label' => __('lang_v1.partial'),
                'class' => 'bg-yellow'
            ],
            'completed' => [
                'label' => __('restaurant.completed'),
                'class' => 'bg-green'
            ]
        ];
    }

    public function salesOrders()
    {
        $sales_orders = null;
        if (!empty($this->sales_order_ids)) {
            $sales_orders = Transaction::where('business_id', $this->business_id)
                                ->where('type', 'sales_order')
                                ->whereIn('id', $this->sales_order_ids)
                                ->get();
        }
        
        return $sales_orders;
    }
}
