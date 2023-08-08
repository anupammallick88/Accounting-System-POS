<?php

namespace App\Http\Controllers;

use App\SellingPriceGroup;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class SellingPriceGroupController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
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
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $price_groups = SellingPriceGroup::where('business_id', $business_id)
                        ->select(['name', 'description', 'id', 'is_active']);

            return Datatables::of($price_groups)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'SellingPriceGroupController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'SellingPriceGroupController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_spg_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        &nbsp;
                        <button data-href="{{action(\'SellingPriceGroupController@activateDeactivate\', [$id])}}" class="btn btn-xs @if($is_active) btn-danger @else btn-success @endif activate_deactivate_spg"><i class="fas fa-power-off"></i> @if($is_active) @lang("messages.deactivate") @else @lang("messages.activate") @endif</button>'
                )
                ->removeColumn('is_active')
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('selling_price_group.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $spg = SellingPriceGroup::create($input);

            //Create a new permission related to the created selling price group
            Permission::create(['name' => 'selling_price_group.' . $spg->id ]);

            $output = ['success' => true,
                            'data' => $spg,
                            'msg' => __("lang_v1.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);

            return view('selling_price_group.edit')
                ->with(compact('spg'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->name = $input['name'];
                $spg->description = $input['description'];
                $spg->save();

                $output = ['success' => true,
                            'msg' => __("lang_v1.updated_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->delete();

                $output = ['success' => true,
                            'msg' => __("lang_v1.deleted_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Exports selling price group prices for all the products in xls format
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $business_id = request()->user()->business_id;
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

        $variations = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                            ->where('p.business_id', $business_id)
                            ->whereIn('p.type', ['single', 'variable'])
                            ->select('sub_sku', 'p.name as product_name', 'variations.name as variation_name', 'p.type', 'variations.id', 'pv.name as product_variation_name', 'sell_price_inc_tax')
                            ->with(['group_prices'])
                            ->get();
        $export_data = [];
        foreach ($variations as $variation) {
            $temp = [];
            $temp['product'] = $variation->type == 'single' ? $variation->product_name : $variation->product_name . ' - ' . $variation->product_variation_name . ' - ' . $variation->variation_name;
            $temp['sku'] = $variation->sub_sku;
            $temp['Base Selling Price'] = $variation->sell_price_inc_tax;

            foreach ($price_groups as $price_group) {
                $price_group_id = $price_group->id;
                $variation_pg = $variation->group_prices->filter(function ($item) use ($price_group_id) {
                    return $item->price_group_id == $price_group_id;
                });
                
                $temp[$price_group->name] = $variation_pg->isNotEmpty() ? $variation_pg->first()->price_inc_tax : '';
            }
            $export_data[] = $temp;
        }

        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return collect($export_data)->downloadExcel(
            'product_group_prices.xlsx',
            null,
            true
        );
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        try {

            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
        
            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('product_group_prices')) {
                $file = $request->file('product_group_prices');
                
                $parsed_array = Excel::toArray([], $file);

                $headers = $parsed_array[0][0];

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = request()->user()->business_id;
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();

                //Get price group names from headers
                $imported_pgs = [];
                foreach ($headers as $key => $value) {
                    if (!empty($value) && $key > 2) {
                        $imported_pgs[$key] = $value;
                    }
                }

                $error_msg = '';
                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    $variation = Variation::where('sub_sku', $value[1])
                                        ->first();
                    if (empty($variation)) {
                        $row = $key + 1;
                        $error_msg = __('lang_v1.product_not_found_exception', ['sku' => $value[1], 'row' => $row]);

                        throw new \Exception($error_msg);
                    }

                    foreach ($imported_pgs as $k => $v) {
                        $price_group = $price_groups->filter(function ($item) use ($v) {
                            return strtolower($item->name) == strtolower($v);
                        });

                        if ($price_group->isNotEmpty()) {
                            //Check if price is numeric
                            if (!is_null($value[$k]) && !is_numeric($value[$k])) {
                                $row = $key + 1;
                                $error_msg = __('lang_v1.price_group_non_numeric_exception', ['row' => $row]);

                                throw new \Exception($error_msg);
                            }

                            if (!is_null($value[$k])) {
                                VariationGroupPrice::updateOrCreate(
                                    ['variation_id' => $variation->id,
                                    'price_group_id' => $price_group->first()->id
                                    ],
                                    ['price_inc_tax' => $value[$k]
                                ]
                                );
                            }
                        } else {
                            $row = $key + 1;
                            $error_msg = __('lang_v1.price_group_not_found_exception', ['pg' => $v, 'row' => $row]);

                            throw new \Exception($error_msg);
                        }
                    }
                }
                DB::commit();
            }
            $output = ['success' => 1,
                            'msg' => __('lang_v1.product_grp_prices_imported_successfully')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return redirect('selling-price-group')->with('notification', $output);
        }

        return redirect('selling-price-group')->with('status', $output);
    }

    /**
     * Activate/deactivate selling price group.
     *
     */
    public function activateDeactivate($id)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);
            $spg->is_active = $spg->is_active == 1 ? 0 : 1;
            $spg->save();

            $output = ['success' => true,
                            'msg' => __("lang_v1.updated_success")
                            ];

            return $output;
        }
    }
}
