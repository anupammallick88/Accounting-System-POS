<div class="modal-dialog" role="document">
    {!! Form::open(['url' => action('SalesOrderController@postEditSalesOrderStatus', ['id' => $id]), 'method' => 'put', 'id' => 'update_so_status_form']) !!}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('lang_v1.edit_status')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('so_status', __('sale.status') . ':') !!}
                        <select name="status" id="so_status" class="form-control" style="width: 100%;">
                            @foreach($statuses as $key => $so_status)
                                <option value="{{$key}}" @if($key == $status) selected @endif>
                                    {{$so_status['label']}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
                @lang('messages.close')
            </button>
            <button type="submit" class="btn btn-primary ladda-button">
                @lang('messages.update')
            </button>
        </div>
    </div><!-- /.modal-content -->
    {!! Form::close() !!}
</div><!-- /.modal-dialog -->
