<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@postFundTransfer'), 'method' => 'post', 'id' => 'fund_transfer_form', 'files' => true ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.fund_transfer' )</h4>
    </div>

    <div class="modal-body">

            <div class="form-group">
                {!! Form::label('from_account', __( 'lang_v1.transfer_from' ) .":*") !!}
                {!! Form::select('from_account', $to_accounts, $from_account->id, ['class' => 'form-control', 'required' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('to_account', __( 'account.transfer_to' ) .":*") !!}
                {!! Form::select('to_account', $to_accounts, null, ['class' => 'form-control', 'required' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('od_datetimepicker', __( 'messages.date' ) .":*") !!}
                <div class="input-group">
                  {!! Form::text('operation_date', null, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ), 'id' => 'od_datetimepicker' ]); !!}
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                {!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
                <p class="help-block">
                  @lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
                  @includeIf('components.document_help_text')
                </p>
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.submit' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
  $(document).ready( function(){
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });
  });
</script>