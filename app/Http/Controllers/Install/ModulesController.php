<?php

namespace App\Http\Controllers\Install;

use \Module;
use App\Http\Controllers\Controller;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use ZipArchive;

class ModulesController extends Controller
{
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ModuleUtil $moduleUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        //Get list of all modules.
        $modules = Module::toCollection()->toArray();

        foreach ($modules as $module => $details) {
            $modules[$module]['is_installed'] = $this->moduleUtil->isModuleInstalled($details['name']) ? true : false;

            //Get version information.
            if ($modules[$module]['is_installed']) {
                $modules[$module]['version'] = $this->moduleUtil->getModuleVersionInfo($details['name']);
            }

            //Install Link.
            try {
                $modules[$module]['install_link'] = action('\Modules\\' . $details['name'] . '\Http\Controllers\InstallController@index');
            } catch (\Exception $e) {
                $modules[$module]['install_link'] = "#";
            }

            //Update Link.
            try {
                $modules[$module]['update_link'] = action('\Modules\\' . $details['name'] . '\Http\Controllers\InstallController@update');
            } catch (\Exception $e) {
                $modules[$module]['update_link'] = "#";
            }

            //Uninstall Link.
            try {
                $modules[$module]['uninstall_link'] = action('\Modules\\' . $details['name'] . '\Http\Controllers\InstallController@uninstall');
            } catch (\Exception $e) {
                $modules[$module]['uninstall_link'] = "#";
            }
        }

        $is_demo = (config('app.env') == 'demo');
        $mods = $this->__available_modules();

        return view('install.modules.index')
            ->with(compact('modules', 'is_demo', 'mods'));


        //Option to uninstall

        //Option to activate/deactivate

        //Upload module.
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Activate/Deaactivate the specified module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $module_name)
    {
        if (!auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }
        
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $module = Module::find($module_name);

            //php artisan module:disable Blog
            if ($request->action_type == 'activate') {
                $module->enable();
            } elseif ($request->action_type == 'deactivate') {
                $module->disable();
            }

            $output = ['success' => true,
                            'msg' => __("lang_v1.success")
                        ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                        'msg' => $e->getMessage()
                    ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Deletes the module.
     *
     * @param  string  $module_name
     * @return \Illuminate\Http\Response
     */
    public function destroy($module_name)
    {
        if (!auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $module = Module::find($module_name);
            $module->delete();

            $output = ['success' => true,
                            'msg' => __("lang_v1.success")
                        ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                        'msg' => $e->getMessage()
                    ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Upload the module.
     *
     */
    public function uploadModule(Request $request)
    {
        $notAllowed = $this->moduleUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {

            //get zipped file
            $module = $request->file('module');

            //check if uploaded file is valid or not and and if not redirect back
            if ($module->getMimeType() != 'application/zip') {
                $output = ['success' => false,
                    'msg' => __('lang_v1.pls_upload_valid_zip_file')
                ];

                return redirect()->back()->with(['status' => $output]);
            }

            //check if 'Modules' folder exist or not, if not exist create
            $path = '../Modules';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            //extract the zipped file in given path
            $zip = new ZipArchive();
            if ($zip->open($module) === true) {
                $zip->extractTo($path .'/');
                $zip->close();
            }

            $output = ['success' => true,
                    'msg' => __("lang_v1.success")
                ];
        } catch (Exception $e) {
            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    private function __available_modules()
    {
        return 'a:8:{i:0;a:4:{s:1:"n";s:10:"Essentials";s:2:"dn";s:17:"Essentials Module";s:1:"u";s:53:"https://ultimatefosters.com/recommends/essential-app/";s:1:"d";s:49:"Essentials features for every growing businesses.";}i:1;a:4:{s:1:"n";s:10:"Superadmin";s:2:"dn";s:17:"Superadmin Module";s:1:"u";s:54:"https://ultimatefosters.com/recommends/superadmin-app/";s:1:"d";s:76:"Turn your POS to SaaS application and start earning by selling subscriptions";}i:2;a:4:{s:1:"n";s:11:"Woocommerce";s:2:"dn";s:18:"Woocommerce Module";s:1:"u";s:55:"https://ultimatefosters.com/recommends/woocommerce-app/";s:1:"d";s:36:"Sync your Woocommerce store with POS";}i:3;a:4:{s:1:"n";s:13:"Manufacturing";s:2:"dn";s:20:"Manufacturing Module";s:1:"u";s:57:"https://ultimatefosters.com/recommends/manufacturing-app/";s:1:"d";s:70:"Manufacture products from raw materials, organise recipe & ingredients";}i:4;a:4:{s:1:"n";s:7:"Project";s:2:"dn";s:14:"Project Module";s:1:"u";s:51:"https://ultimatefosters.com/recommends/project-app/";s:1:"d";s:66:"Manage Projects, tasks, tasks time logs, activities and much more.";}i:5;a:4:{s:1:"n";s:6:"Repair";s:2:"dn";s:13:"Repair Module";s:1:"u";s:50:"https://ultimatefosters.com/recommends/repair-app/";s:1:"d";s:248:"Repair module helps with complete repair service management of electronic goods like Cellphone, Computers, Desktops, Tablets, Television, Watch, Wireless devices, Printers, Electronic instruments and many more similar devices which you can imagine!";}i:6;a:4:{s:1:"n";s:3:"Crm";s:2:"dn";s:10:"CRM Module";s:1:"u";s:63:"https://ultimatefosters.com/product/crm-module-for-ultimatepos/";s:1:"d";s:39:"Customer relationship management module";}i:7;a:4:{s:1:"n";s:16:"ProductCatalogue";s:2:"dn";s:16:"ProductCatalogue";s:1:"u";s:90:"https://codecanyon.net/item/digital-product-catalogue-menu-module-for-ultimatepos/28825346";s:1:"d";s:32:"Digital Product catalogue Module";}}
';
    }
}
