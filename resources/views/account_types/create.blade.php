<div class="modal-dialog" role="document">
  	<div class="modal-content">

    {!! Form::open(['url' => action('AccountTypeController@store'), 'method' => 'post', 'id' => 'account_type_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.add_account_type' )</h4>
    </div>

    <div class="modal-body">
      	<div class="form-group">
        	{!! Form::label('name', __( 'lang_v1.name' ) . ':*') !!}
          	{!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.name' )]); !!}
      	</div>

      <div class="form-group">
        	{!! Form::label('parent_account_type_id', __( 'lang_v1.parent_account_type' ) . ':') !!}
          	{!! Form::select('parent_account_type_id', $account_types->pluck('name', 'id'), null, ['class' => 'form-control', 'placeholder' => __( 'messages.please_select' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->