<div class="table-responsive">
    @if(in_array('create', $permissions))
        <div class="pull-right">
            <button type="button" class="btn btn-sm btn-primary docs_and_notes_btn pull-right" data-href="{{action('DocumentAndNoteController@create', ['notable_id' => $notable_id, 'notable_type' => $notable_type])}}">
                @lang('messages.add')&nbsp;
                <i class="fa fa-plus"></i>
            </button> 
        </div> <br><br>
    @endif
    <table class="table table-bordered table-striped" style="width: 100%;" id="documents_and_notes_table">
        <thead>
            <tr>
                <th>@lang('messages.action')</th>
                <th>@lang('lang_v1.heading')</th>
                <th>@lang('lang_v1.added_by')</th>
                <th>@lang('lang_v1.created_at')</th>
                <th>@lang('lang_v1.updated_at')</th>
            </tr>
        </thead>
    </table>
</div>
<div class="modal fade docus_note_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>