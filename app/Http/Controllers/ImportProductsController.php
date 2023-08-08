<?php

namespace App\Http\Controllers;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\TaxRate;
use App\Transaction;
use App\Unit;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Variation;
use App\VariationValueTemplate;
use DB;
use Excel;
use Illuminate\Http\Request;

class ImportProductsController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $moduleUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

    /**
     * Display import product screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = ['success' => 0,
                            'msg' => 'Please install/enable PHP Zip archive for import'
                        ];

            return view('import_products.index')
                ->with('notification', $output);
        } else {
            return view('import_products.index');
        }
    }

    /**
     * Imports the uploaded file to database.
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
            $notAllowed = $this->productUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
            
            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if ($request->hasFile('products_csv')) {
                $file = $request->file('products_csv');

                $parsed_array = Excel::toArray([], $file);

                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $default_profit_percent = $request->session()->get('business.default_profit_percent');

                $formated_data = [];

                $is_valid = true;
                $error_msg = '';

                $total_rows = count($imported_data);

                //Check if subscribed or not, then check for products quota
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id, $total_rows)) {
                    return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action('ImportProductsController@index'));
                }

                $business_locations = BusinessLocation::where('business_id', $business_id)->get();
                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {

                    //Check if any column is missing
                    if (count($value) < 37) {
                        $is_valid =  false;
                        $error_msg = "Some of the columns are missing. Please, use latest CSV file template.";
                        break;
                    }

                    $row_no = $key + 1;
                    $product_array = [];
                    $product_array['business_id'] = $business_id;
                    $product_array['created_by'] = $user_id;
                    
                    //Add name
                    $product_name = trim($value[0]);
                    if (!empty($product_name)) {
                        $product_array['name'] = $product_name;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Product name is required in row no. $row_no";
                        break;
                    }
                    
                    //image name
                    $image_name = trim($value[29]);
                    if (!empty($image_name)) {
                        if (filter_var($image_name, FILTER_VALIDATE_URL)) { 
                            $source_image = file_get_contents($image_name);

                            $path = parse_url($image_name, PHP_URL_PATH);
                            $new_name = time() . '_' . basename($path);
                            $dest_img = public_path() . '/uploads/' . config('constants.product_img_path') . '/' . $new_name;
                            file_put_contents($dest_img, $source_image);
                          $product_array['image'] = $new_name;
                        } else {
                            $product_array['image'] = $image_name;
                        }
                        
                    } else {
                        $product_array['image'] = '';
                    }

                    $product_array['product_description'] = isset($value[30]) ? $value[30] : null;

                    //Custom fields
                    if (isset($value[31])) {
                        $product_array['product_custom_field1'] = trim($value[31]);
                    } else {
                        $product_array['product_custom_field1'] = '';
                    }
                    if (isset($value[32])) {
                        $product_array['product_custom_field2'] = trim($value[32]);
                    } else {
                        $product_array['product_custom_field2'] = '';
                    }
                    if (isset($value[33])) {
                        $product_array['product_custom_field3'] = trim($value[33]);
                    } else {
                        $product_array['product_custom_field3'] = '';
                    }
                    if (isset($value[34])) {
                        $product_array['product_custom_field4'] = trim($value[34]);
                    } else {
                        $product_array['product_custom_field4'] = '';
                    }

                    //Add not for selling
                    $product_array['not_for_selling'] = !empty($value[35]) && $value[35] == 1 ? 1 : 0;

                    //Add enable stock
                    $enable_stock = trim($value[7]);
                    if (in_array($enable_stock, [0,1])) {
                        $product_array['enable_stock'] = $enable_stock;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Invalid value for MANAGE STOCK in row no. $row_no";
                        break;
                    }

                    //Add product type
                    $product_type = strtolower(trim($value[13]));
                    if (in_array($product_type, ['single','variable'])) {
                        $product_array['type'] = $product_type;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Invalid value for PRODUCT TYPE in row no. $row_no";
                        break;
                    }

                    //Add unit
                    $unit_name = trim($value[2]);
                    if (!empty($unit_name)) {
                        $unit = Unit::where('business_id', $business_id)
                                    ->where(function ($query) use ($unit_name) {
                                        $query->where('short_name', $unit_name)
                                              ->orWhere('actual_name', $unit_name);
                                    })->first();
                        if (!empty($unit)) {
                            $product_array['unit_id'] = $unit->id;
                        } else {
                            $is_valid = false;
                            $error_msg = "Unit with name $unit_name not found in row no. $row_no. You can add unit from Products > Units";
                            break;
                        }
                    } else {
                        $is_valid =  false;
                        $error_msg = "UNIT is required in row no. $row_no";
                        break;
                    }

                    //Add barcode type
                    $barcode_type = strtoupper(trim($value[6]));
                    if (empty($barcode_type)) {
                        $product_array['barcode_type'] = 'C128';
                    } elseif (array_key_exists($barcode_type, $this->barcode_types)) {
                        $product_array['barcode_type'] = $barcode_type;
                    } else {
                        $is_valid = false;
                        $error_msg = "$barcode_type barcode type is not valid in row no. $row_no. Please, check for allowed barcode types in the instructions";
                        break;
                    }

                    //Add Tax
                    $tax_name = trim($value[11]);
                    $tax_amount = 0;
                    if (!empty($tax_name)) {
                        $tax = TaxRate::where('business_id', $business_id)
                                        ->where('name', $tax_name)
                                        ->first();
                        if (!empty($tax)) {
                            $product_array['tax'] = $tax->id;
                            $tax_amount = $tax->amount;
                        } else {
                            $is_valid = false;
                            $error_msg = "Tax with name $tax_name in row no. $row_no not found. You can add tax from Settings > Tax Rates";
                            break;
                        }
                    }

                    //Add tax type
                    $tax_type = strtolower(trim($value[12]));
                    if (in_array($tax_type, ['inclusive', 'exclusive'])) {
                        $product_array['tax_type'] = $tax_type;
                    } else {
                        $is_valid = false;
                        $error_msg = "Invalid value for Selling Price Tax Type in row no. $row_no";
                        break;
                    }

                    //Add alert quantity
                    if ($product_array['enable_stock'] == 1) {
                        $product_array['alert_quantity'] = trim($value[8]);
                    }
                    

                    //Add brand
                    //Check if brand exists else create new
                    $brand_name = trim($value[1]);
                    if (!empty($brand_name)) {
                        $brand = Brands::firstOrCreate(
                            ['business_id' => $business_id, 'name' => $brand_name],
                            ['created_by' => $user_id]
                        );
                        $product_array['brand_id'] = $brand->id;
                    }

                    //Add Category
                    //Check if category exists else create new
                    $category_name = trim($value[3]);
                    if (!empty($category_name)) {
                        $category = Category::firstOrCreate(
                            ['business_id' => $business_id, 'name' => $category_name, 'category_type' => 'product'],
                            ['created_by' => $user_id, 'parent_id' => 0]
                        );
                        $product_array['category_id'] = $category->id;
                    }

                    //Add Sub-Category
                    $sub_category_name = trim($value[4]);
                    if (!empty($sub_category_name)) {
                        $sub_category = Category::firstOrCreate(
                            ['business_id' => $business_id, 'name' => $sub_category_name, 'category_type' => 'product'],
                            ['created_by' => $user_id, 'parent_id' => $category->id]
                        );
                        $product_array['sub_category_id'] = $sub_category->id;
                    }

                    //Add SKU
                    $sku = trim($value[5]);
                    if (!empty($sku)) {
                        $product_array['sku'] = $sku;
                        //Check if product with same SKU already exist
                        $is_exist = Product::where('sku', $product_array['sku'])
                                        ->where('business_id', $business_id)
                                        ->exists();
                        if ($is_exist) {
                            $is_valid = false;
                            $error_msg = "$sku SKU already exist in row no. $row_no";
                            break;
                        }
                    } else {
                        $product_array['sku'] = ' ';
                    }

                    //Add product expiry
                    $expiry_period = trim($value[9]);
                    $expiry_period_type = strtolower(trim($value[10]));
                    if (!empty($expiry_period) && in_array($expiry_period_type, ['months', 'days'])) {
                        $product_array['expiry_period'] = $expiry_period;
                        $product_array['expiry_period_type'] = $expiry_period_type;
                    } else {
                        //If Expiry Date is set then make expiry_period 12 months.
                        if (!empty($value[23])) {
                            $product_array['expiry_period'] = 12;
                            $product_array['expiry_period_type'] = 'months';
                        }
                    }

                    //Enable IMEI or Serial Number
                    $enable_sr_no = trim($value[24]);
                    if (in_array($enable_sr_no, [0,1])) {
                        $product_array['enable_sr_no'] = $enable_sr_no;
                    } elseif (empty($enable_sr_no)) {
                        $product_array['enable_sr_no'] = 0;
                    } else {
                        $is_valid =  false;
                        $error_msg = "Invalid value for ENABLE IMEI OR SERIAL NUMBER  in row no. $row_no";
                        break;
                    }

                    //Weight
                    if (isset($value[25])) {
                        $product_array['weight'] = trim($value[25]);
                    } else {
                        $product_array['weight'] = '';
                    }

                    if ($product_array['type'] == 'single') {
                        //Calculate profit margin
                        $profit_margin = trim($value[19]);
                        if (empty($profit_margin)) {
                            $profit_margin = $default_profit_percent;
                        } else {
                            $profit_margin = trim($value[19]);
                        }
                        $product_array['variation']['profit_percent'] = $profit_margin;

                        //Calculate purchase price
                        $dpp_inc_tax = trim($value[17]);
                        $dpp_exc_tax = trim($value[18]);
                        if ($dpp_inc_tax == '' && $dpp_exc_tax == '') {
                            $is_valid = false;
                            $error_msg = "PURCHASE PRICE is required in row no. $row_no";
                            break;
                        } else {
                            $dpp_inc_tax = ($dpp_inc_tax != '') ? $dpp_inc_tax : 0;
                            $dpp_exc_tax = ($dpp_exc_tax != '') ? $dpp_exc_tax : 0;
                        }

                        //Calculate Selling price
                        $selling_price = !empty(trim($value[20])) ? trim($value[20]) : 0 ;

                        //Calculate product prices
                        $product_prices = $this->calculateVariationPrices($dpp_exc_tax, $dpp_inc_tax, $selling_price, $tax_amount, $tax_type, $profit_margin);

                        //Assign Values
                        $product_array['variation']['dpp_inc_tax'] = $product_prices['dpp_inc_tax'];
                        $product_array['variation']['dpp_exc_tax'] = $product_prices['dpp_exc_tax'];
                        $product_array['variation']['dsp_inc_tax'] = $product_prices['dsp_inc_tax'];
                        $product_array['variation']['dsp_exc_tax'] = $product_prices['dsp_exc_tax'];
                        
                        //Opening stock
                        if (!empty($value[21]) && $enable_stock == 1) {
                            $product_array['opening_stock_details']['quantity'] = trim($value[21]);

                            if (!empty(trim($value[22]))) {
                                $location_name = trim($value[22]);
                                $location = BusinessLocation::where('name', $location_name)
                                                            ->where('business_id', $business_id)
                                                            ->first();
                                if (!empty($location)) {
                                    $product_array['opening_stock_details']['location_id'] = $location->id;
                                } else {
                                    $is_valid = false;
                                    $error_msg = "No location with name '$location_name' found in row no. $row_no";
                                    break;
                                }
                            } else {
                                $location = BusinessLocation::where('business_id', $business_id)->first();
                                $product_array['opening_stock_details']['location_id'] = $location->id;
                            }

                            $product_array['opening_stock_details']['expiry_date'] = null;

                            //Stock expiry date
                            if (!empty($value[23])) {
                                $product_array['opening_stock_details']['exp_date'] = \Carbon::createFromFormat('m-d-Y', trim($value[23]))->format('Y-m-d');
                            } else {
                                $product_array['opening_stock_details']['exp_date'] = null;
                            }
                        }
                    } elseif ($product_array['type'] == 'variable') {
                        $variation_name = trim($value[14]);
                        if (empty($variation_name)) {
                            $is_valid = false;
                            $error_msg = "VARIATION NAME is required in row no. $row_no";
                            break;
                        }
                        $variation_values_string = trim($value[15]);
                        if (empty($variation_values_string)) {
                            $is_valid = false;
                            $error_msg = "VARIATION VALUES are required in row no. $row_no";
                            break;
                        }

                        $variation_sku_string = trim($value[16]);

                        $dpp_inc_tax_string = trim($value[17]);
                        $dpp_exc_tax_string = trim($value[18]);
                        $selling_price_string = trim($value[20]);
                        $profit_margin_string = trim($value[19]);

                        if (empty($dpp_inc_tax_string) && empty($dpp_exc_tax_string)) {
                            $is_valid = false;
                            $error_msg = "PURCHASE PRICE is required in row no. $row_no";
                            break;
                        }

                        //Variation values
                        $variation_values = array_map('trim', explode(
                            '|',
                            $variation_values_string
                        ));

                        $variation_skus = [];
                        if (!empty($variation_sku_string)) {
                            $variation_skus = array_map('trim', explode(
                            '|',
                                $variation_sku_string
                            ));
                        }

                        //Map Purchase price with variation values
                        $dpp_inc_tax = [];
                        if (!empty($dpp_inc_tax_string)) {
                            $dpp_inc_tax = array_map('trim', explode(
                                '|',
                                $dpp_inc_tax_string
                            ));
                        } else {
                            foreach ($variation_values as $k => $v) {
                                $dpp_inc_tax[$k] = 0;
                            }
                        }
                        
                        $dpp_exc_tax = [];
                        if (!empty($dpp_exc_tax_string)) {
                            $dpp_exc_tax = array_map('trim', explode(
                                '|',
                                $dpp_exc_tax_string
                            ));
                        } else {
                            foreach ($variation_values as $k => $v) {
                                $dpp_exc_tax[$k] = 0;
                            }
                        }

                        //Map Selling price with variation values
                        $selling_price = [];
                        if (!empty($selling_price_string)) {
                            $selling_price = array_map('trim', explode(
                                '|',
                                $selling_price_string
                                ));
                        } else {
                            foreach ($variation_values as $k => $v) {
                                $selling_price[$k] = 0;
                            }
                        }

                        //Map profit margin with variation values
                        $profit_margin = [];
                        if (!empty($profit_margin_string)) {
                            $profit_margin = array_map('trim', explode(
                                '|',
                                $profit_margin_string
                                ));
                        } else {
                            foreach ($variation_values as $k => $v) {
                                $profit_margin[$k] = $default_profit_percent;
                            }
                        }

                        //Check if length of prices array is equal to variation values array length
                        $array_lengths_count = [count($variation_values), count($dpp_inc_tax), count($dpp_exc_tax), count($selling_price), count($profit_margin)];

                        if (!empty($variation_skus)) {
                            $array_lengths_count[] = count($variation_skus);
                        }
                        $same = array_count_values($array_lengths_count);

                        if (count($same) != 1) {
                            $is_valid = false;
                            $error_msg = "Prices mismatched with VARIATION VALUES in row no. $row_no";
                            break;
                        }
                        $product_array['variation']['name'] = $variation_name;

                        //Check if variation exists or create new
                        $variation = $this->productUtil->createOrNewVariation($business_id, $variation_name);
                        $product_array['variation']['variation_template_id'] = $variation->id;

                        foreach ($variation_values as $k => $v) {
                            $variation_prices = $this->calculateVariationPrices($dpp_exc_tax[$k], $dpp_inc_tax[$k], $selling_price[$k], $tax_amount, $tax_type, $profit_margin[$k]);

                            //get variation value
                            $variation_value = $variation->values->filter(function ($item) use ($v) {
                                return strtolower($item->name) == strtolower($v);
                            })->first();

                            if (empty($variation_value)) {
                                $variation_value = VariationValueTemplate::create([
                                  'name' => $v,
                                  'variation_template_id' => $variation->id
                                ]);
                            }
                            
                            //Assign Values
                            $product_array['variation']['variations'][] = [
                                'value' => $v,
                                'variation_value_id' => $variation_value->id,
                                'default_purchase_price' => $variation_prices['dpp_exc_tax'],
                                'dpp_inc_tax' => $variation_prices['dpp_inc_tax'],
                                'profit_percent' => $this->productUtil->num_f($profit_margin[$k]),
                                'default_sell_price' => $variation_prices['dsp_exc_tax'],
                                'sell_price_inc_tax' => $variation_prices['dsp_inc_tax'],
                                'sub_sku' => !empty($variation_skus[$k]) ? $variation_skus[$k] : ''
                            ];
                        }

                        //Opening stock
                        if (!empty($value[21]) && $enable_stock == 1) {
                            $variation_os = array_map('trim', explode('|', $value[21]));

                            //$product_array['opening_stock_details']['quantity'] = $variation_os;

                            //Check if count of variation and opening stock is matching or not.
                            if (count($product_array['variation']['variations']) != count($variation_os)) {
                                $is_valid = false;
                                $error_msg = "Opening Stock mismatched with VARIATION VALUES in row no. $row_no";
                                break;
                            }

                            if (!empty(trim($value[22]))) {
                                $location_name = trim($value[22]);
                                $location = BusinessLocation::where('name', $location_name)
                                                            ->where('business_id', $business_id)
                                                            ->first();
                                if (empty($location)) {
                                    $is_valid = false;
                                    $error_msg = "No location with name '$location_name' found in row no. $row_no";
                                    break;
                                }
                            } else {
                                $location = BusinessLocation::where('business_id', $business_id)->first();
                            }
                            $product_array['variation']['opening_stock_location'] = $location->id;

                            foreach ($variation_os as $k => $v) {
                                $product_array['variation']['variations'][$k]['opening_stock'] = $v;
                                $product_array['variation']['variations'][$k]['opening_stock_exp_date'] = null;
                                
                                if (!empty($value[23])) {
                                    $product_array['variation']['variations'][$k]['opening_stock_exp_date'] = \Carbon::createFromFormat('m-d-Y', trim($value[23]))->format('Y-m-d');
                                } else {
                                    $product_array['variation']['variations'][$k]['opening_stock_exp_date'] = null;
                                }
                            }
                        }
                    }
                    //Assign to formated array
                    $formated_data[] = $product_array;
                }

                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {
                    foreach ($formated_data as $index => $product_data) {
                        $variation_data = $product_data['variation'];
                        unset($product_data['variation']);

                        $opening_stock = null;
                        if (!empty($product_data['opening_stock_details'])) {
                            $opening_stock = $product_data['opening_stock_details'];
                        }
                        if (isset($product_data['opening_stock_details'])) {
                            unset($product_data['opening_stock_details']);
                        }

                        //Create new product
                        $product = Product::create($product_data);
                        //If auto generate sku generate new sku
                        if ($product->sku == ' ') {
                            $sku = $this->productUtil->generateProductSku($product->id);
                            $product->sku = $sku;
                            $product->save();
                        }

                        //Rack, Row & Position.
                        $this->rackDetails(
                            $imported_data[$index][26],
                            $imported_data[$index][27],
                            $imported_data[$index][28],
                            $business_id,
                            $product->id,
                            $index+1
                        );

                        //Product locations
                        if (!empty($imported_data[$index][36])) {
                            $locations_array = explode(',', $imported_data[$index][36]);
                            $location_ids = [];
                            foreach ($locations_array as $business_location) {
                                foreach ($business_locations as $loc) {
                                    if (strtolower($loc->name) == strtolower(trim($business_location))) {
                                       $location_ids[] = $loc->id;
                                    }
                                }
                            }
                            if (!empty($location_ids)) {
                                $product->product_locations()->sync($location_ids);
                            }
                        }

                        //Create single product variation
                        if ($product->type == 'single') {
                            $this->productUtil->createSingleProductVariation(
                                $product,
                                $product->sku,
                                $variation_data['dpp_exc_tax'],
                                $variation_data['dpp_inc_tax'],
                                $variation_data['profit_percent'],
                                $variation_data['dsp_exc_tax'],
                                $variation_data['dsp_inc_tax']
                            );
                            if (!empty($opening_stock)) {
                                $this->addOpeningStock($opening_stock, $product, $business_id);
                            }
                        } elseif ($product->type == 'variable') {
                            //Create variable product variations
                            $this->productUtil->createVariableProductVariations(
                                $product,
                                [$variation_data],
                                $business_id
                            );

                            if (!empty($variation_data['opening_stock_location']) && $enable_stock == 1) {
                                $this->addOpeningStockForVariable($variation_data, $product, $business_id);
                            }
                        }
                    }
                }
            }
            
            $output = ['success' => 1,
                            'msg' => __('product.file_imported_successfully')
                        ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return redirect('import-products')->with('notification', $output);
        }

        return redirect('import-products')->with('status', $output);
    }

    private function calculateVariationPrices($dpp_exc_tax, $dpp_inc_tax, $selling_price, $tax_amount, $tax_type, $margin)
    {

        //Calculate purchase prices
        if ($dpp_inc_tax == 0) {
            $dpp_inc_tax = $this->productUtil->calc_percentage(
                $dpp_exc_tax,
                $tax_amount,
                $dpp_exc_tax
            );
        }

        if ($dpp_exc_tax == 0) {
            $dpp_exc_tax = $this->productUtil->calc_percentage_base($dpp_inc_tax, $tax_amount);
        }

        if ($selling_price != 0) {
            if ($tax_type == 'inclusive') {
                $dsp_inc_tax = $selling_price;
                $dsp_exc_tax = $this->productUtil->calc_percentage_base(
                    $dsp_inc_tax,
                    $tax_amount
                );
            } elseif ($tax_type == 'exclusive') {
                $dsp_exc_tax = $selling_price;
                $dsp_inc_tax = $this->productUtil->calc_percentage(
                    $selling_price,
                    $tax_amount,
                    $selling_price
                );
            }
        } else {
            $dsp_exc_tax = $this->productUtil->calc_percentage(
                $dpp_exc_tax,
                $margin,
                $dpp_exc_tax
            );
            $dsp_inc_tax = $this->productUtil->calc_percentage(
                $dsp_exc_tax,
                $tax_amount,
                $dsp_exc_tax
            );
        }

        return [
            'dpp_exc_tax' => $this->productUtil->num_f($dpp_exc_tax),
            'dpp_inc_tax' => $this->productUtil->num_f($dpp_inc_tax),
            'dsp_exc_tax' => $this->productUtil->num_f($dsp_exc_tax),
            'dsp_inc_tax' => $this->productUtil->num_f($dsp_inc_tax)
        ];
    }

    /**
     * Adds opening stock of a single product
     *
     * @param array $opening_stock
     * @param obj $product
     * @param int $business_id
     * @return void
     */
    private function addOpeningStock($opening_stock, $product, $business_id)
    {
        $user_id = request()->session()->get('user.id');
        
        $variation = Variation::where('product_id', $product->id)
            ->first();

        $total_before_tax = $opening_stock['quantity'] * $variation->dpp_inc_tax;

        $transaction_date = request()->session()->get("financial_year.start");
        $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();
        //Add opening stock transaction
        $transaction = Transaction::create(
            [
                                'type' => 'opening_stock',
                                'opening_stock_product_id' => $product->id,
                                'status' => 'received',
                                'business_id' => $business_id,
                                'transaction_date' => $transaction_date,
                                'total_before_tax' => $total_before_tax,
                                'location_id' => $opening_stock['location_id'],
                                'final_total' => $total_before_tax,
                                'payment_status' => 'paid',
                                'created_by' => $user_id
                            ]
        );
        //Get product tax
        $tax_percent = !empty($product->product_tax->amount) ? $product->product_tax->amount : 0;
        $tax_id = !empty($product->product_tax->id) ? $product->product_tax->id : null;

        $item_tax = $this->productUtil->calc_percentage($variation->default_purchase_price, $tax_percent);

        //Create purchase line
        $transaction->purchase_lines()->create([
                        'product_id' => $product->id,
                        'variation_id' => $variation->id,
                        'quantity' => $opening_stock['quantity'],
                        'item_tax' => $item_tax,
                        'tax_id' => $tax_id,
                        'pp_without_discount' => $variation->default_purchase_price,
                        'purchase_price' => $variation->default_purchase_price,
                        'purchase_price_inc_tax' => $variation->dpp_inc_tax,
                        'exp_date' => !empty($opening_stock['exp_date']) ? $opening_stock['exp_date'] : null
                    ]);
        //Update variation location details
        $this->productUtil->updateProductQuantity($opening_stock['location_id'], $product->id, $variation->id, $opening_stock['quantity']);

        //Add product location
        $this->__addProductLocation($product, $opening_stock['location_id']);
        
    }

    private function __addProductLocation($product, $location_id)
    {
        $count = DB::table('product_locations')->where('product_id', $product->id)
                                            ->where('location_id', $location_id)
                                            ->count();
        if ($count == 0) {
            DB::table('product_locations')->insert(['product_id' => $product->id, 
                                'location_id' => $location_id]);
        }
    }


    private function addOpeningStockForVariable($variations, $product, $business_id)
    {
        $user_id = request()->session()->get('user.id');

        $transaction_date = request()->session()->get("financial_year.start");
        $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();

        $total_before_tax = 0;
        $location_id = $variations['opening_stock_location'];
        if (isset($variations['variations'][0]['opening_stock'])) {
            //Add opening stock transaction
            $transaction = Transaction::create(
                [
                                'type' => 'opening_stock',
                                'opening_stock_product_id' => $product->id,
                                'status' => 'received',
                                'business_id' => $business_id,
                                'transaction_date' => $transaction_date,
                                'total_before_tax' => $total_before_tax,
                                'location_id' => $location_id,
                                'final_total' => $total_before_tax,
                                'payment_status' => 'paid',
                                'created_by' => $user_id
                            ]
            );

            //Add product location
            $this->__addProductLocation($product, $location_id);

            foreach ($variations['variations'] as $variation_os) {
                if (!empty($variation_os['opening_stock'])) {
                    $variation = Variation::where('product_id', $product->id)
                                    ->where('name', $variation_os['value'])
                                    ->first();
                    if (!empty($variation)) {
                        $opening_stock = [
                            'quantity' => $variation_os['opening_stock'],
                            'exp_date' => $variation_os['opening_stock_exp_date'],
                        ];

                        $total_before_tax = $total_before_tax + ($variation_os['opening_stock'] * $variation->dpp_inc_tax);
                    }

                    //Get product tax
                    $tax_percent = !empty($product->product_tax->amount) ? $product->product_tax->amount : 0;
                    $tax_id = !empty($product->product_tax->id) ? $product->product_tax->id : null;

                    $item_tax = $this->productUtil->calc_percentage($variation->default_purchase_price, $tax_percent);

                    //Create purchase line
                    $transaction->purchase_lines()->create([
                                    'product_id' => $product->id,
                                    'variation_id' => $variation->id,
                                    'quantity' => $opening_stock['quantity'],
                                    'item_tax' => $item_tax,
                                    'tax_id' => $tax_id,
                                    'purchase_price' => $variation->default_purchase_price,
                                    'purchase_price_inc_tax' => $variation->dpp_inc_tax,
                                    'exp_date' => !empty($opening_stock['exp_date']) ? $opening_stock['exp_date'] : null
                                ]);
                    //Update variation location details
                    $this->productUtil->updateProductQuantity($location_id, $product->id, $variation->id, $opening_stock['quantity']);
                }
            }

            $transaction->total_before_tax = $total_before_tax;
            $transaction->final_total = $total_before_tax;
            $transaction->save();
        }
    }

    private function rackDetails($rack_value, $row_value, $position_value, $business_id, $product_id, $row_no)
    {
        if (!empty($rack_value) || !empty($row_value) || !empty($position_value)) {
            $locations = BusinessLocation::forDropdown($business_id);
            $loc_count = count($locations);

            $racks = explode('|', $rack_value);
            $rows = explode('|', $row_value);
            $position = explode('|', $position_value);

            if (count($racks) > $loc_count) {
                $error_msg = "Invalid value for RACK in row no. $row_no";
                throw new \Exception($error_msg);
            }

            if (count($rows) > $loc_count) {
                $error_msg = "Invalid value for ROW in row no. $row_no";
                throw new \Exception($error_msg);
            }

            if (count($position) > $loc_count) {
                $error_msg = "Invalid value for POSITION in row no. $row_no";
                throw new \Exception($error_msg);
            }

            $rack_details = [];
            $counter = 0;
            foreach ($locations as $key => $value) {
                $rack_details[$key]['rack'] = isset($racks[$counter]) ? $racks[$counter] : '';
                $rack_details[$key]['row'] = isset($rows[$counter]) ? $rows[$counter] : '';
                $rack_details[$key]['position'] = isset($position[$counter]) ? $position[$counter] : '';
                $counter += 1;
            }

            if (!empty($rack_details)) {
                $this->productUtil->addRackDetails($business_id, $product_id, $rack_details);
            }
        }
    }
}
