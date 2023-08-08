<?php

namespace App\Http\Controllers;

use App\DocumentAndNote;
use App\Media;
use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DocumentAndNoteController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
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
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            //model id like project_id, user_id
            $notable_id = request()->get('notable_id');
            //model name like App\User
            $notable_type = request()->get('notable_type');

            $document_note = DocumentAndNote::where('business_id', $business_id)
                ->where('notable_id', $notable_id)
                ->where(function ($query) use ($user_id) {
                    $query->where('is_private', 0)
                        ->orWhere(function ($q) use ($user_id) {
                            $q->where('is_private', 1)
                              ->where('created_by', $user_id);
                        });
                })
                ->where('notable_type', $notable_type)
                ->with('createdBy', 'media')
                ->select('*');

            $permissions = $this->__getPermission($business_id, $notable_id, $notable_type);

            if (!empty($permissions) && in_array('view', $permissions)) {
                return Datatables::of($document_note)
                    ->addColumn('action', function ($row) use ($notable_type, $permissions) {
                        $html = '<div class="btn-group">
                                    <button class="btn btn-info dropdown-toggle btn-xs" type="button"  data-toggle="dropdown" aria-expanded="false">
                                        '.__("messages.action").'
                                        <span class="caret"></span>
                                        <span class="sr-only">
                                            '.__("messages.action").'
                                        </span>
                                    </button>
                                      <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                        ';

                        if (in_array('view', $permissions)) {
                            $html .='<li>
                                        <a data-href="' . action('DocumentAndNoteController@show', ['id' => $row->id, 'notable_id' => $row->notable_id, 'notable_type' => $notable_type]) . '" class="cursor-pointer view_a_docs_note">
                                            <i class="fa fa-eye"></i>
                                            '.__("messages.view").'
                                        </a>
                                    </li>';
                        }
                        if (in_array('create', $permissions)) {
                            $html .= '<li>
                                    <a data-href="' . action('DocumentAndNoteController@edit', ['id' => $row->id, 'notable_id' => $row->notable_id, 'notable_type' => $notable_type]) . '"  class="cursor-pointer docs_and_notes_btn">
                                        <i class="fa fa-edit"></i>
                                        '.__("messages.edit").'
                                    </a>
                                </li>';
                        }
                        if (in_array('delete', $permissions)) {
                            $html .= '<li>
                                    <a data-href="' . action('DocumentAndNoteController@destroy', ['id' => $row->id, 'notable_id' => $row->notable_id, 'notable_type' => $notable_type]) . '"  id="delete_docus_note" class="cursor-pointer">
                                        <i class="fas fa-trash"></i>
                                        '.__("messages.delete").'
                                    </a>
                                </li>';
                        }
                        
                        $html .= '</ul>
                                </div>';

                        return $html;
                    })
                    ->editColumn('created_at', '
                        {{@format_date($created_at)}}
                    ')
                    ->editColumn('updated_at', '
                        {{@format_date($updated_at)}}
                    ')
                    ->editColumn('createdBy', function ($row) {
                        return optional($row->createdBy)->user_full_name;
                    })
                    ->editColumn(
                        'heading',
                        function ($row) use ($notable_type) {
                            $is_private = '';
                            if ($row->is_private) {
                                $private_tooltip = __("lang_v1.private_note");
                                $is_private = '<i class="fas fa-lock text-danger"
                                data-toggle="tooltip" title="'.$private_tooltip.'"></i>';
                            }

                            $icon = '';
                            if ($row->media->count() > 0) {
                                $media_tooltip = __("lang_v1.contains_media");
                                $icon = '<i class="fas fa-file-image text-primary" data-toggle="tooltip" title="'.$media_tooltip.'"></i>';
                            }

                            $html = '<a data-href="' . action('DocumentAndNoteController@show', ['id' => $row->id, 'notable_id' => $row->notable_id, 'notable_type' => $notable_type]) . '" class="cursor-pointer view_a_docs_note text-black">
                                '.
                                    $row->heading .
                                    '&nbsp;'.
                                    $is_private.
                                    '&nbsp;'.
                                    $icon
                                .'
                            </a>';

                            return $html;
                        }
                        )
                    ->removeColumn('id')
                    ->rawColumns(['action', 'heading', 'createdBy', 'created_at', 'updated_at'])
                    ->make(true);
            }
        }
    }

    /**
     * Returns the array of permission for the notable.
     *
     * @return array of permissions
     */
    private function __getPermission($business_id, $notable_id, $notable_type)
    {
        $permissions = [];

        //Define all notable for main app.
        $app_notable = [
            'App\User' => [
                'permissions' => ['view', 'create', 'delete']
            ],
            'App\Contact' => [
                'permissions' => ['view', 'create', 'delete']
            ]
        ];

        if (isset($app_notable[$notable_type])) {
            return $app_notable[$notable_type]['permissions'];
        } else {
            //If not found in main app, get from modules.
            $module_parameters = [
                'business_id' => $business_id,
                'notable_id' => $notable_id,
                'notable_type' => $notable_type
            ];
            $module_data = $this->moduleUtil->getModuleData('addDocumentAndNotes', $module_parameters);

            foreach ($module_data as $module => $data) {
                if (isset($data[$notable_type])) {
                    return $data[$notable_type]['permissions'];
                }
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //model id like project_id, user_id
        $notable_id = request()->get('notable_id');
        //model name like App\User
        $notable_type = request()->get('notable_type');
        return view('documents_and_notes.create')
            ->with(compact('notable_id', 'notable_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            
            //model id like project_id, user_id
            $notable_id = request()->get('notable_id');
            //model name like App\User
            $notable_type = request()->get('notable_type');

            $input = $request->only('heading', 'description', 'is_private');
            $input['business_id'] = request()->session()->get('user.business_id');
            $input['created_by'] = request()->session()->get('user.id');

            DB::beginTransaction();

            if (!empty($input['is_private'])) {
                activity()->disableLogging();
            }

            //find model to which document is to be added
            $model = $notable_type::where('business_id', $input['business_id'])
                ->findOrFail($notable_id);

            $model_note = $model->documentsAndnote()->create($input);
            
            if (!empty($request->get('file_name')[0])) {
                $file_names = explode(',', $request->get('file_name')[0]);
                $business_id = request()->session()->get('user.business_id');
                Media::attachMediaToModel($model_note, $business_id, $file_names);
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //model id like project_id, user_id
        $notable_id = request()->get('notable_id');
        //model name like App\User
        $notable_type = request()->get('notable_type');

        $business_id = request()->session()->get('user.business_id');
        $document_note = DocumentAndNote::where('business_id', $business_id)
            ->where('notable_id', $notable_id)
            ->where('notable_type', $notable_type)
            ->with('media', 'createdBy')
            ->findOrFail($id);

        return view('documents_and_notes.show')
            ->with(compact('document_note'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //model id like project_id, user_id
        $notable_id = request()->get('notable_id');
        //model name like App\User
        $notable_type = request()->get('notable_type');

        $business_id = request()->session()->get('user.business_id');
        $document_note = DocumentAndNote::where('business_id', $business_id)
        ->where('notable_id', $notable_id)
        ->where('notable_type', $notable_type)
        ->findOrFail($id);

        return view('documents_and_notes.edit')
            ->with(compact('notable_id', 'document_note', 'notable_type'));
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
        try {

            //model id like project_id, user_id
            $notable_id = request()->get('notable_id');
            //model name like App\User
            $notable_type = request()->get('notable_type');

            $business_id = request()->session()->get('user.business_id');

            $input = $request->only('heading', 'description');
            $input['is_private'] = !empty($request->get('is_private')) ? 1 : 0;
            
            $document_note = DocumentAndNote::where('business_id', $business_id)
                ->where('notable_id', $notable_id)
                ->where('notable_type', $notable_type)
                ->findOrFail($id);

            DB::beginTransaction();

            if ($input['is_private']) {
                $document_note->disableLogging();
            }

            $document_note->heading = $input['heading'];
            $document_note->description = $input['description'];
            $document_note->is_private = $input['is_private'];
            $document_note->save();

            if (!empty($request->get('file_name')[0])) {
                $file_names = explode(',', $request->get('file_name')[0]);
                $business_id = request()->session()->get('user.business_id');
                Media::attachMediaToModel($document_note, $business_id, $file_names);
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            //model id like project_id, user_id
            $notable_id = request()->get('notable_id');
            //model name like App\User
            $notable_type = request()->get('notable_type');

            $document_note = DocumentAndNote::where('business_id', $business_id)
                ->where('notable_id', $notable_id)
                ->where('notable_type', $notable_type)
                ->findOrFail($id);

            DB::beginTransaction();

            $document_note->delete();
            $document_note->media()->delete();

            DB::commit();
            
            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    /**
     * upload documents in app
     *
     * @return \Illuminate\Http\Response
     */
    public function postMedia(Request $request)
    {
        try {
            $file = $request->file('file')[0];

            $file_name = Media::uploadFile($file);

            $output = [
                'success' => true,
                'file_name' => $file_name,
                'msg' => __('lang_v1.success')
            ];
        } catch (Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    /**
     * get docus & note index page
     * through ajax
     * @return \Illuminate\Http\Response
     */
    public function getDocAndNoteIndexPage(Request $request)
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $notable_type = $request->get('notable_type');
            $notable_id = $request->get('notable_id');
            $permissions = $this->__getPermission($business_id, $notable_id, $notable_type);

            return view('documents_and_notes.index')
                ->with(compact('permissions', 'notable_type', 'notable_id'));
        }
    }
}
