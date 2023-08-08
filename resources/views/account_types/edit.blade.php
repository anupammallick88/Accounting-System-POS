<div class="modal-dialog" role="document">
  	<div class="modal-content">

    {!! Form::open(['url' => action('AccountTypeController@update', $account_type->id), 'method' => 'put', 'id' => 'account_type_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.edit_account_type' )</h4>
    </div>

    <div class="modal-body">
      	<div class="form-group">
        	{!! Form::label('name', __( 'lang_v1.name' ) . ':*') !!}
          	{!! Form::text('name', $account_type->name, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.name' )]); !!}
      	</div>

      <div class="form-group">
        	{!! Form::label('parent_account_type_id', __( 'lang_v1.parent_account_type' ) . ':') !!}
          	{!! Form::select('parent_account_type_id', $account_types->pluck('name', 'id'), $account_type->parent_account_type_id, ['class' => 'form-control', 'placeholder' => __( 'messages.please_select' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->