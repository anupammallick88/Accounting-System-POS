<?php

namespace App\Http\Controllers;

use App\InvoiceLayout;
use App\Utils\Util;
use Illuminate\Http\Request;
use Validator;

class InvoiceLayoutController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $designs = $this->getDesigns();
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;

        return view('invoice_layout.create')->with(compact('designs', 'is_warranty_enabled'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validator = Validator::make($request->all(), [
                'logo' => 'mimes:jpeg,gif,png|1000',
            ]);

            $input = $request->only(['name', 'header_text',
                'invoice_no_prefix', 'invoice_heading', 'sub_total_label', 'discount_label', 'tax_label', 'total_label', 'highlight_color', 'footer_text', 'invoice_heading_not_paid', 'invoice_heading_paid', 'total_due_label', 'customer_label', 'paid_label', 'sub_heading_line1', 'sub_heading_line2',
                    'sub_heading_line3', 'sub_heading_line4', 'sub_heading_line5',
                    'table_product_label', 'table_qty_label', 'table_unit_price_label',
                    'table_subtotal_label', 'client_id_label', 'date_label', 'quotation_heading', 'quotation_no_prefix', 'design', 'client_tax_label', 'cat_code_label', 'cn_heading', 'cn_no_label', 'cn_amount_label', 'sales_person_label', 'prev_bal_label', 'date_time_format', 'common_settings', 'change_return_label', 'round_off_label', 'qr_code_fields', 'commission_agent_label']);

            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            //Set value for checkboxes
            $checkboxes = ['show_business_name', 'show_location_name', 'show_landmark', 'show_city', 'show_state', 'show_country', 'show_zip_code', 'show_mobile_number', 'show_alternate_number', 'show_email', 'show_tax_1', 'show_tax_2', 'show_logo', 'show_barcode', 'show_payments', 'show_customer', 'show_client_id',
                'show_brand', 'show_sku', 'show_cat_code', 'show_sale_description', 'show_sales_person', 'show_expiry', 'show_lot', 'show_previous_bal', 'show_image', 'show_reward_point', 'show_qr_code', 'show_commission_agent'];
            foreach ($checkboxes as $name) {
                $input[$name] = !empty($request->input($name)) ? 1 : 0;
            }

            //Upload Logo
            $logo_name = $this->commonUtil->uploadFile($request, 'logo', 'invoice_logos', 'image');
            if (!empty($logo_name)) {
                $input['logo'] = $logo_name;
            }

            if (!empty($request->input('is_default'))) {
                //get_default
                $default = InvoiceLayout::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0 ]);
                $input['is_default'] = 1;
            }

            //Module info
            if ($request->has('module_info')) {
                $input['module_info'] = json_encode($request->input('module_info'));
            }

            if (!empty($request->input('table_tax_headings'))) {
                $input['table_tax_headings'] = json_encode($request->input('table_tax_headings'));
            }
            $input['product_custom_fields'] = !empty($request->input('product_custom_fields')) ? $request->input('product_custom_fields') : null;
            $input['contact_custom_fields'] = !empty($request->input('contact_custom_fields')) ? $request->input('contact_custom_fields') : null;
            $input['location_custom_fields'] = !empty($request->input('location_custom_fields')) ? $request->input('location_custom_fields') : null;

            InvoiceLayout::create($input);
            $output = ['success' => 1,
                            'msg' => __("invoice.layout_added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect('invoice-schemes')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\InvoiceLayout  $invoiceLayout
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceLayout $invoiceLayout)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InvoiceLayout  $invoiceLayout
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $invoice_layout = InvoiceLayout::findOrFail($id);

        //Module info
        $invoice_layout->module_info = json_decode($invoice_layout->module_info, true);
        $invoice_layout->table_tax_headings = !empty($invoice_layout->table_tax_headings) ? json_decode($invoice_layout->table_tax_headings) : ['', '', '', ''];

        $designs = $this->getDesigns();

        return view('invoice_layout.edit')
                ->with(compact('invoice_layout', 'designs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InvoiceLayout  $invoiceLayout
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('invoice_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validator = Validator::make($request->all(), [
                'logo' => 'mimes:jpeg,gif,png|1000',
            ]);

            $input = $request->only(['name', 'header_text',
                'invoice_no_prefix', 'invoice_heading', 'sub_total_label', 'discount_label', 'tax_label', 'total_label', 'highlight_color', 'footer_text', 'invoice_heading_not_paid', 'invoice_heading_paid', 'total_due_label', 'customer_label', 'paid_label', 'sub_heading_line1', 'sub_heading_line2',
                    'sub_heading_line3', 'sub_heading_line4', 'sub_heading_line5',
                    'table_product_label', 'table_qty_label', 'table_unit_price_label',
                    'table_subtotal_label', 'client_id_label', 'date_label', 'quotation_heading', 'quotation_no_prefix', 'design',
                    'client_tax_label', 'cat_code_label', 'cn_heading', 'cn_no_label', 'cn_amount_label',
                    'sales_person_label', 'prev_bal_label', 'date_time_format', 'change_return_label', 'round_off_label', 'commission_agent_label']);
            $business_id = $request->session()->get('user.business_id');

            $checkboxes = ['show_business_name', 'show_location_name', 'show_landmark', 'show_city', 'show_state', 'show_country', 'show_zip_code', 'show_mobile_number', 'show_alternate_number', 'show_email', 'show_tax_1', 'show_tax_2', 'show_logo', 'show_barcode', 'show_payments', 'show_customer', 'show_client_id',
                'show_brand', 'show_sku', 'show_cat_code', 'show_sale_description', 'show_sales_person', 'show_expiry', 'show_lot', 'show_previous_bal', 'show_image', 'show_reward_point', 'show_qr_code', 'show_commission_agent'];
            foreach ($checkboxes as $name) {
                $input[$name] = !empty($request->input($name)) ? 1 : 0;
            }

            //Upload Logo
            $logo_name = $this->commonUtil->uploadFile($request, 'logo', 'invoice_logos', 'image');
            if (!empty($logo_name)) {
                $input['logo'] = $logo_name;
            }

            if (!empty($request->input('is_default'))) {
                //get_default
                $default = InvoiceLayout::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0 ]);
                $input['is_default'] = 1;
            }

            //Module info
            if ($request->has('module_info')) {
                $input['module_info'] = json_encode($request->input('module_info'));
            }
            
            if (!empty($request->input('table_tax_headings'))) {
                $input['table_tax_headings'] = json_encode($request->input('table_tax_headings'));
            }

            $input['product_custom_fields'] = !empty($request->input('product_custom_fields')) ? json_encode($request->input('product_custom_fields')) : null;
            $input['contact_custom_fields'] = !empty($request->input('contact_custom_fields')) ? json_encode($request->input('contact_custom_fields')) : null;
            $input['location_custom_fields'] = !empty($request->input('location_custom_fields')) ? json_encode($request->input('location_custom_fields')) : null;
            $input['common_settings'] = !empty($request->input('common_settings')) ? json_encode($request->input('common_settings')) : null;
            $input['qr_code_fields'] = !empty($request->input('qr_code_fields')) ? json_encode($request->input('qr_code_fields')) : null;

            InvoiceLayout::where('id', $id)
                        ->where('business_id', $business_id)
                        ->update($input);
            $output = ['success' => 1,
                            'msg' => __("invoice.layout_updated_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect('invoice-schemes')->with('status', $output);
    }

    private function getDesigns() {
        return ['classic' => __('lang_v1.classic') .  ' (' . __('lang_v1.for_normal_printer') . ')',
                'elegant' => __('lang_v1.elegant') . ' (' . __('lang_v1.for_normal_printer') . ')',
                'detailed' => __('lang_v1.detailed') . ' (' . __('lang_v1.for_normal_printer') . ')',
                'columnize-taxes' => __('lang_v1.columnize_taxes') . ' (' . __('lang_v1.for_normal_printer') . ')',
                'slim' => __('lang_v1.slim') . ' (' . __('lang_v1.recomended_for_80mm') . ')',
                'slim2' => __('lang_v1.slim') . ' 2 (' . __('lang_v1.recomended_for_58mm') . ')'
            ];
    }
}
