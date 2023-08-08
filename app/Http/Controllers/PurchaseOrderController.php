<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\PurchaseLine;
use App\TaxRate;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;

use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;

use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Activitylog\Models\Activity;
use App\Media;

class PurchaseOrderController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;

        $this->purchaseOrderStatuses = [
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

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	if (!auth()->user()->can('purchase_order.view_all') && !auth()->user()->can('purchase_order.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->businessUtil->is_admin(auth()->user());
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
    	$business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchase_orders = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join(
                        'business_locations AS BS',
                        'transactions.location_id',
                        '=',
                        'BS.id'
                    )
                    ->leftJoin('purchase_lines as pl', 'transactions.id', '=', 'pl.transaction_id')
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase_order')
                    ->select(
                        'transactions.id',
                        'transactions.document',
                        'transactions.transaction_date',
                        'transactions.ref_no',
                        'transactions.status',
                        'contacts.name',
                        'contacts.supplier_business_name',
                        'transactions.final_total',
                        'BS.name as location_name',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'transactions.shipping_status',
                        DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                        DB::raw('SUM(pl.quantity - pl.po_quantity_purchased) as po_qty_remaining')
                    )
                    ->groupBy('transactions.id');

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchase_orders->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchase_orders->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchase_orders->where('transactions.location_id', request()->location_id);
            }

            if (!empty(request()->status)) {
                $purchase_orders->where('transactions.status', request()->status);
            }

            if (!empty(request()->from_dashboard)) {
                $purchase_orders->where('transactions.status', '!=', 'completed')
                    ->orHavingRaw('po_qty_remaining > 0');
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchase_orders->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase_order.view_all') && auth()->user()->can('purchase_order.view_own')) {
                $purchase_orders->where('transactions.created_by', request()->session()->get('user.id'));
            }

            if (!empty(request()->input('shipping_status'))) {
                $purchase_orders->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            return Datatables::of($purchase_orders)
                ->addColumn('action', function ($row) use ($is_admin) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                                __("messages.actions") .
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can("purchase_order.view_all") || auth()->user()->can("purchase_order.view_own")) {
                        $html .= '<li><a href="#" data-href="' . action('PurchaseOrderController@show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';

                        $html .= '<li><a href="#" class="print-invoice" data-href="' . action('PurchaseController@printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>'. __("messages.print") .'</a></li>';
                    }
                    if (config('constants.enable_download_pdf') && (auth()->user()->can("purchase_order.view_all") || auth()->user()->can("purchase_order.view_own"))) {
                        $html .= '<li><a href="' . route('purchaseOrder.downloadPdf', [$row->id]) . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __("lang_v1.download_pdf") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase_order.update")) {
                        $html .= '<li><a href="' . action('PurchaseOrderController@edit', [$row->id]) . '"><i class="fas fa-edit"></i>' . __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase_order.delete")) {
                        $html .= '<li><a href="' . action('PurchaseOrderController@destroy', [$row->id]) . '" class="delete-purchase-order"><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    if ($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping']) ) {
                        $html .= '<li><a href="#" data-href="' . action('SellController@editShipping', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-truck" aria-hidden="true"></i>' . __("lang_v1.edit_shipping") . '</a></li>';
                    }

                    if ((auth()->user()->can("purchase_order.view_all") || auth()->user()->can("purchase_order.view_own")) && !empty($row->document)) {
                        $document_name = !empty(explode("_", $row->document, 2)[1]) ? explode("_", $row->document, 2)[1] : $row->document ;
                        $html .= '<li><a href="' . url('uploads/documents/' . $row->document) .'" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __("purchase.download_document") . '</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) .'" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __("lang_v1.view_document") . '</a></li>';
                        }
                    }
                                        
                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="final_total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('po_qty_remaining', '{{@format_quantity($po_qty_remaining)}}')
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn('status', function($row)use($is_admin){
                    $status = '';
                    $order_statuses = $this->purchaseOrderStatuses;
                    if (array_key_exists($row->status, $order_statuses)) {
                        if ($is_admin && $row->status != 'completed') {
                            $status = '<span class="edit-po-status label ' . $order_statuses[$row->status]['class']
                            . '" data-href="'.action("PurchaseOrderController@getEditPurchaseOrderStatus", ['id' => $row->id]).'">' . $order_statuses[$row->status]['label'] . '</span>';
                        } else {
                            $status = '<span class="label ' . $order_statuses[$row->status]['class']
                            . '" >' . $order_statuses[$row->status]['label'] . '</span>';
                        }
                    }

                    return $status;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action('SellController@editShipping', [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color .'">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';
                     
                    return $status;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        return  action('PurchaseOrderController@show', [$row->id]) ;
                    }])
                ->rawColumns(['final_total', 'action', 'ref_no', 'name', 'status', 'shipping_status'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $purchaseOrderStatuses = [];
        foreach ($this->purchaseOrderStatuses as $key => $value) {
            $purchaseOrderStatuses[$key] = $value['label'];
        }

        return view('purchase_order.index')->with(compact('business_locations', 'suppliers', 'purchaseOrderStatuses', 'shipping_statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    	if (!auth()->user()->can('purchase_order.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];
        return view('purchase_order.create')
            ->with(compact('taxes', 'business_locations', 'currency_details', 'customer_groups', 'types', 'shortcuts', 'bl_attributes', 'shipping_statuses', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	if (!auth()->user()->can('purchase_order.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('PurchaseController@index'));
            }

            $transaction_data = $request->only([ 'ref_no', 'contact_id', 'transaction_date', 'total_before_tax', 'location_id','discount_type', 'discount_amount','tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'shipping_address', 'shipping_status', 'delivered_to']);

            $exchange_rate = $transaction_data['exchange_rate'];

            if ($request->has('shipping_custom_field_1')) {
                $transaction_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1');
            }
            if ($request->has('shipping_custom_field_2')) {
                $transaction_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2');
            }
            if ($request->has('shipping_custom_field_3')) {
                $transaction_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3');
            }
            if ($request->has('shipping_custom_field_4')) {
                $transaction_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4');
            }
            if ($request->has('shipping_custom_field_5')) {
                $transaction_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5');
            }

            //Reverse exchange rate and save it.
            //$transaction_data['exchange_rate'] = $transaction_data['exchange_rate'];

            //TODO: Check for "Undefined index: total_before_tax" issue
            //Adding temporary fix by validating
            $request->validate([
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);

            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            //unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details)*$exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($transaction_data['discount_type'] == 'fixed') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details)*$exchange_rate;
            } elseif ($transaction_data['discount_type'] == 'percentage') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
            } else {
                $transaction_data['discount_amount'] = 0;
            }

            $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'], $currency_details)*$exchange_rate;
            $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges'], $currency_details)*$exchange_rate;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details)*$exchange_rate;

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase_order';
            $transaction_data['status'] = 'ordered';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);

            if ($request->input('additional_expense_value_1') != '') {
                $transaction_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                $transaction_data['additional_expense_value_1'] = $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details)*$exchange_rate;
            }

            if ($request->input('additional_expense_value_2') != '') {
                $transaction_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                $transaction_data['additional_expense_value_2'] = $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details)*$exchange_rate;
            }

            if ($request->input('additional_expense_value_3') != '') {
                $transaction_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                $transaction_data['additional_expense_value_3'] = $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details)*$exchange_rate;
            }

            if ($request->input('additional_expense_value_4') != '') {
                $transaction_data['additional_expense_key_4'] = $request->input('additional_expense_key_1');
                $transaction_data['additional_expense_value_4'] = $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details)*$exchange_rate;
            }

            //upload document
            $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            
            DB::beginTransaction();

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }

            $transaction = Transaction::create($transaction_data);

            //Upload Shipping documents
            Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');
            
            $purchase_lines = [];
            $purchases = $request->input('purchases');

            $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing);

            $this->transactionUtil->activityLog($transaction, 'added');
            
            DB::commit();
            
            $output = ['success' => 1,
                            'msg' => __('lang_v1.added_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect()->action('PurchaseOrderController@index')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('purchase_order.view_all') && !auth()->user()->can('purchase_order.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $query = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(
                                    'contact',
                                    'purchase_lines',
                                    'purchase_lines.product',
                                    'purchase_lines.product.unit',
                                    'purchase_lines.variations',
                                    'purchase_lines.variations.product_variation',
                                    'purchase_lines.sub_unit',
                                    'location',
                                    'tax'
                                );
        if (!auth()->user()->can('purchase_order.view_all') && auth()->user()->can('purchase_order.view_own')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }
                                   
        $purchase = $query->firstOrFail();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }
        
        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        $activities = Activity::forSubject($purchase)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $status_color_in_activity = $this->purchaseOrderStatuses;
        $po_statuses = $this->purchaseOrderStatuses;
        return view('purchase_order.show')
                ->with(compact('taxes', 'purchase', 'purchase_taxes', 'activities', 'shipping_statuses', 'status_color_in_activity', 'po_statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('purchase_order.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business = Business::find($business_id);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
        $query = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(
                        'contact',
                        'purchase_lines',
                        'purchase_lines.product',
                        'purchase_lines.product.unit',
                        //'purchase_lines.product.unit.sub_units',
                        'purchase_lines.variations',
                        'purchase_lines.variations.product_variation',
                        'location',
                        'purchase_lines.sub_unit'
                    );

        if (!auth()->user()->can('purchase_order.view_all') && auth()->user()->can('purchase_order.view_own')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }
        
        $purchase =  $query->first();
        
        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }
       
        $business_locations = BusinessLocation::forDropdown($business_id);

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        return view('purchase_order.edit')
            ->with(compact(
                'taxes',
                'purchase',
                'business_locations',
                'business',
                'currency_details',
                'customer_groups',
                'types',
                'shortcuts',
                'shipping_statuses', 'users'
            ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $transaction = Transaction::findOrFail($id);

            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);

            $transaction = Transaction::findOrFail($id);
            $business_id = request()->session()->get('user.business_id');

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_data = $request->only([ 'ref_no', 'contact_id',
                            'transaction_date', 'total_before_tax',
                            'discount_type', 'discount_amount', 'tax_id',
                            'tax_amount', 'shipping_details',
                            'shipping_charges', 'final_total',
                            'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'shipping_address', 'shipping_status', 'delivered_to']);

            $update_data['shipping_custom_field_1'] = $request->has('shipping_custom_field_1') ? $request->input('shipping_custom_field_1') : null;
            $update_data['shipping_custom_field_2'] = $request->has('shipping_custom_field_2') ? $request->input('shipping_custom_field_2') : null;
            $update_data['shipping_custom_field_3'] = $request->has('shipping_custom_field_3') ? $request->input('shipping_custom_field_3') : null;
            $update_data['shipping_custom_field_4'] = $request->has('shipping_custom_field_4') ? $request->input('shipping_custom_field_4') : null;
            $update_data['shipping_custom_field_5'] = $request->has('shipping_custom_field_5') ? $request->input('shipping_custom_field_5') : null;

            $exchange_rate = $update_data['exchange_rate'];

            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);

            //unformat input values
            $update_data['total_before_tax'] = $this->productUtil->num_uf($update_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($update_data['discount_type'] == 'fixed') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($update_data['discount_type'] == 'percentage') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details);
            } else {
                $update_data['discount_amount'] = 0;
            }

            $update_data['tax_amount'] = $this->productUtil->num_uf($update_data['tax_amount'], $currency_details) * $exchange_rate;
            $update_data['shipping_charges'] = $this->productUtil->num_uf($update_data['shipping_charges'], $currency_details) * $exchange_rate;
            $update_data['final_total'] = $this->productUtil->num_uf($update_data['final_total'], $currency_details) * $exchange_rate;
            //unformat input values ends

            $update_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $update_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $update_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $update_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');

            $update_data['additional_expense_value_1'] = $request->input('additional_expense_value_1') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_2'] = $request->input('additional_expense_value_2') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate: 0;
            $update_data['additional_expense_value_3'] = $request->input('additional_expense_value_3') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_4'] = $request->input('additional_expense_value_4') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate : 0;

            //upload document
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (!empty($document_name)) {
                $update_data['document'] = $document_name;
            }

            $transaction_before = $transaction->replicate();

            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');

            $purchases = $request->input('purchases');

            $delete_purchase_lines = $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, false);

            $this->transactionUtil->updatePurchaseOrderStatus([$transaction->id]);

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_update_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return back()->with('status', $output);
        }

        return redirect()->action('PurchaseOrderController@index')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase_order.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');
        
                $transaction = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase_order')
                                ->with('purchase_lines')
                                ->findOrFail($id);

                //unset purchase_order_line_id if set
                PurchaseLine::whereIn('purchase_order_line_id', $transaction->purchase_lines->pluck('id'))
                        ->update(['purchase_order_line_id' => null]); 

                $log_properities = [
                    'id' => $transaction->id,
                    'ref_no' => $transaction->ref_no
                ];
                $this->transactionUtil->activityLog($transaction, 'po_deleted', null, $log_properities); 

                $transaction->delete();           

                $output = ['success' => true,
                            'msg' => __('lang_v1.purchase_order_delete_success')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => $e->getMessage()
                        ];
        }

        return $output;
    }

    public function getPurchaseOrders($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $purchase_orders = Transaction::where('business_id', $business_id)
                        ->where('type', 'purchase_order')
                        ->whereIn('status', ['partial', 'ordered'])
                        ->where('contact_id', $contact_id)
                        ->select('ref_no as text', 'id')
                        ->get();

        return $purchase_orders;
    }

    /**
     * download pdf for given purchase order
     *
     */
    public function downloadPdf($id)
    {   
        if (!(config('constants.enable_download_pdf') && (auth()->user()->can("purchase_order.view_all") || auth()->user()->can("purchase_order.view_own")))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $taxes = TaxRate::where('business_id', $business_id)
                                ->get();

        $purchase = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(
                        'contact',
                        'purchase_lines',
                        'purchase_lines.product',
                        'purchase_lines.product.category',
                        'purchase_lines.variations',
                        'purchase_lines.variations.product_variation',
                        'location',
                        'payment_lines'
                    )
                    ->first();

        $location_details = BusinessLocation::find($purchase->location_id);
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $purchase->location_id, $location_details->invoice_layout_id);

        //Logo
        $logo = $invoice_layout->show_logo != 0 && !empty($invoice_layout->logo) && file_exists(public_path('uploads/invoice_logos/' . $invoice_layout->logo)) ? asset('uploads/invoice_logos/' . $invoice_layout->logo) : false;

        $word_format = $invoice_layout->common_settings['num_to_word_format'] ? $invoice_layout->common_settings['num_to_word_format'] : 'international';
        $total_in_words = $this->transactionUtil->numToWord($purchase->final_total, null, $word_format);

        $custom_labels = json_decode(session('business.custom_labels'), true);
        
        //Generate pdf
        $body = view('purchase_order.receipts.download_pdf')
                    ->with(compact('purchase', 'invoice_layout', 'location_details', 'logo', 'total_in_words', 'custom_labels', 'taxes'))
                    ->render();

        $mpdf = new \Mpdf\Mpdf(['tempDir' => public_path('uploads/temp'), 
                    'mode' => 'utf-8', 
                    'autoScriptToLang' => true,
                    'autoLangToFont' => true,
                    'autoVietnamese' => true,
                    'autoArabic' => true,
                    'margin_top' => 8,
                    'margin_bottom' => 8,
                    'format' => 'A4'
                ]);

        $mpdf->useSubstitutions=true;
        $mpdf->SetWatermarkText($purchase->business->name, 0.1);
        $mpdf->showWatermarkText = true;
        $mpdf->SetTitle('PO-'.$purchase->ref_no.'.pdf');
        $mpdf->WriteHTML($body);
        $mpdf->Output('PO-'.$purchase->ref_no.'.pdf', 'I');
    }

    /**
     * get required resources 
     *
     * to edit purchase order status
     *
     * @return \Illuminate\Http\Response
     */
    public function getEditPurchaseOrderStatus(Request $request, $id)
    {   
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if ( !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                ->findOrFail($id);

            $status = $transaction->status;
            $statuses = $this->purchaseOrderStatuses;

            return view('purchase_order.edit_status_modal')
                ->with(compact('id', 'status', 'statuses'));
        }
    }

    /**
     * updare purchase order status
     *
     * @return \Illuminate\Http\Response
     */
    public function postEditPurchaseOrderStatus(Request $request, $id)
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if ( !$is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            try {
                
                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('business_id', $business_id)
                                ->findOrFail($id);

                $transaction_before = $transaction->replicate();
                
                $transaction->status = $request->input('status');
                $transaction->save();

                $activity_property = ['from' => $transaction_before->status, 'to' => $request->input('status')];
                $this->transactionUtil->activityLog($transaction, 'status_updated', $transaction_before, $activity_property);

                $output = [
                    'success' => 1,
                    'msg' => trans("lang_v1.success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                $output = [
                    'success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }
}
