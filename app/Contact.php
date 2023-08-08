<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

class Contact extends Authenticatable
{
    use Notifiable;

    use SoftDeletes;

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
        'shipping_custom_field_details' => 'array',
    ];
    

    /**
    * Get the business that owns the user.
    */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function scopeActive($query)
    {
        return $query->where('contacts.contact_status', 'active');
    }

    /**
    * Filters only own created suppliers or has access to the supplier
    */
    public function scopeOnlySuppliers($query)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $query->whereIn('contacts.type', ['supplier', 'both']);

        if (auth()->check() && !auth()->user()->can('supplier.view') && auth()->user()->can('supplier.view_own')) {
            $query->leftjoin('user_contact_access AS ucas', 'contacts.id', 'ucas.contact_id');
            $query->where( function($q){
                $user_id = auth()->user()->id;
                $q->where('contacts.created_by', $user_id)
                    ->orWhere('ucas.user_id', $user_id);
            });
        }

        return $query;
    }

    /**
    * Filters only own created customers or has access to the customer
    */
    public function scopeOnlyCustomers($query)
    {
        if (!auth()->user()->can('customer.view') && !auth()->user()->can('customer.view_own')) {
            abort(403, 'Unauthorized action.');
        }
            
        $query->whereIn('contacts.type', ['customer', 'both']);

        if (auth()->check() && !auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            $query->leftjoin('user_contact_access AS ucas', 'contacts.id', 'ucas.contact_id');
            $query->where( function($q){
                $user_id = auth()->user()->id;
                $q->where('contacts.created_by', $user_id)
                    ->orWhere('ucas.user_id', $user_id);
            });
        }
        return $query;
    }

    /**
    * Filters only own created contact or has access to the contact
    */
    public function scopeOnlyOwnContact($query)
    {
        $query->leftjoin('user_contact_access AS ucas', 'contacts.id', 'ucas.contact_id');
        $query->where( function($q){
            $user_id = auth()->user()->id;
            $q->where('contacts.created_by', $user_id)
                ->orWhere('ucas.user_id', $user_id);
        });
        return $query;
    }

    /**
     * Get all of the contacts's notes & documents.
     */
    public function documentsAndnote()
    {
        return $this->morphMany('App\DocumentAndNote', 'notable');
    }

    /**
     * Return list of contact dropdown for a business
     *
     * @param $business_id int
     * @param $exclude_default = false (boolean)
     * @param $prepend_none = true (boolean)
     *
     * @return array users
     */
    public static function contactDropdown($business_id, $exclude_default = false, $prepend_none = true, $append_id = true)
    {
        $query = Contact::where('business_id', $business_id)
                    ->where('type', '!=', 'lead')
                    ->active();
                    
        if ($exclude_default) {
            $query->where('is_default', 0);
        }

        if ($append_id) {
            $query->select(
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', name, CONCAT(name, ' - ', COALESCE(supplier_business_name, ''), '(', contacts.contact_id, ')')) AS supplier"),
                'contacts.id'
                    );
        } else {
            $query->select(
                'contacts.id',
                DB::raw("IF (supplier_business_name IS not null, CONCAT(name, ' (', supplier_business_name, ')'), name) as supplier")
            );
        }
        
        if (auth()->check() && !auth()->user()->can('supplier.view') && auth()->user()->can('supplier.view_own')) {
            $query->leftjoin('user_contact_access AS ucas', 'contacts.id', 'ucas.contact_id');
            $query->where( function($q){
                $user_id = auth()->user()->id;
                $q->where('contacts.created_by', $user_id)
                    ->orWhere('ucas.user_id', $user_id);
            });
        }

        $contacts = $query->pluck('supplier', 'contacts.id');

        //Prepend none
        if ($prepend_none) {
            $contacts = $contacts->prepend(__('lang_v1.none'), '');
        }

        return $contacts;
    }

    /**
     * Return list of suppliers dropdown for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     *
     * @return array users
     */
    public static function suppliersDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['supplier', 'both'])
                        ->active();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' - ', COALESCE(supplier_business_name, ''), '(', contact_id, ')')) AS supplier"),
                'id'
                    );
        } else {
            $all_contacts->select(
                'id',
                DB::raw("CONCAT(name, ' (', supplier_business_name, ')') as supplier")
                );
        }

        if (auth()->check() && !auth()->user()->can('supplier.view') && auth()->user()->can('supplier.view_own')) {
            $all_contacts->where('contacts.created_by', auth()->user()->id);
        }

        $suppliers = $all_contacts->pluck('supplier', 'id');

        //Prepend none
        if ($prepend_none) {
            $suppliers = $suppliers->prepend(__('lang_v1.none'), '');
        }

        return $suppliers;
    }

    /**
     * Return list of customers dropdown for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     *
     * @return array users
     */
    public static function customersDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['customer', 'both'])
                        ->active();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', CONCAT( COALESCE(supplier_business_name, ''), ' - ', name), CONCAT(COALESCE(supplier_business_name, ''), ' - ', name, ' (', contact_id, ')')) AS customer"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as customer"));
        }

        if (auth()->check() && !auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            $all_contacts->where('contacts.created_by', auth()->user()->id);
        }

        $customers = $all_contacts->pluck('customer', 'id');

        //Prepend none
        if ($prepend_none) {
            $customers = $customers->prepend(__('lang_v1.none'), '');
        }

        return $customers;
    }

    /**
     * Return list of contact type.
     *
     * @param $prepend_all = false (boolean)
     * @return array
     */
    public static function typeDropdown($prepend_all = false)
    {
        $types = [];

        if ($prepend_all) {
            $types[''] = __('lang_v1.all');
        }

        $types['customer'] = __('report.customer');
        $types['supplier'] = __('report.supplier');
        $types['both'] = __('lang_v1.both_supplier_customer');

        return $types;
    }

    /**
     * Return list of contact type by permissions.
     *
     * @return array
     */
    public static function getContactTypes()
    {
        $types = [];
        if (auth()->check() && auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->check() && auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->check() && auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        return $types;
    }

    public function getContactAddressAttribute()
    {
        $address_array = [];
        if (!empty($this->supplier_business_name)) {
            $address_array[] = $this->supplier_business_name;
        }
        if (!empty($this->name)) {
            $address_array[] = !empty($this->supplier_business_name) ? '<br>' . $this->name : $this->name;
        }
        if (!empty($this->address_line_1)) {
            $address_array[] = '<br>' . $this->address_line_1;
        }
        if (!empty($this->address_line_2)) {
            $address_array[] =  '<br>' . $this->address_line_2;
        }
        if (!empty($this->city)) {
            $address_array[] = '<br>' . $this->city;
        }
        if (!empty($this->state)) {
            $address_array[] = $this->state;
        }
        if (!empty($this->country)) {
            $address_array[] = $this->country;
        }

        $address = '';
        if (!empty($address_array)) {
            $address = implode(', ', $address_array);
        }
        if (!empty($this->zip_code)) {
            $address .= ',<br>' . $this->zip_code;
        }

        return $address;
    }

    public function getFullNameAttribute()
    {
        $name_array = [];
        if (!empty($this->prefix)) {
            $name_array[] = $this->prefix;
        }
        if (!empty($this->first_name)) {
            $name_array[] = $this->first_name;
        }
        if (!empty($this->middle_name)) {
            $name_array[] = $this->middle_name;
        }
        if (!empty($this->last_name)) {
            $name_array[] = $this->last_name;
        }
        
        return implode(' ', $name_array);
    }

    public function getFullNameWithBusinessAttribute()
    {
        $name_array = [];
        if (!empty($this->prefix)) {
            $name_array[] = $this->prefix;
        }
        if (!empty($this->first_name)) {
            $name_array[] = $this->first_name;
        }
        if (!empty($this->middle_name)) {
            $name_array[] = $this->middle_name;
        }
        if (!empty($this->last_name)) {
            $name_array[] = $this->last_name;
        }
        
        $full_name = implode(' ', $name_array);
        $business_name = !empty($this->supplier_business_name) ? $this->supplier_business_name . ', ' : '';

        return $business_name . $full_name;
    }

    public function getContactAddressArrayAttribute()
    {
        $address_array = [];
        if (!empty($this->address_line_1)) {
            $address_array[] = $this->address_line_1;
        }
        if (!empty($this->address_line_2)) {
            $address_array[] = $this->address_line_2;
        }
        if (!empty($this->city)) {
            $address_array[] = $this->city;
        }
        if (!empty($this->state)) {
            $address_array[] = $this->state;
        }
        if (!empty($this->country)) {
            $address_array[] = $this->country;
        }
        if (!empty($this->zip_code)) {
            $address_array[] = $this->zip_code;
        }

        return $address_array;
    }


    /**
     * All user who have access to this contact
     * Applied only when selected_contacts is true for a user in
     * users table
     */
    public function userHavingAccess()
    {
        return $this->belongsToMany(\App\User::class, 'user_contact_access');
    }
}
