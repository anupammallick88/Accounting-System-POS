<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('WarrantyController@store'), 'method' => 'post', 'id' => 'warranty_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.add_warranty' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'lang_v1.name' ) . ':*') !!}
          {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.name' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', __( 'lang_v1.description' ) . ':') !!}
          {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.description' ), 'rows' => 3 ]); !!}
      </div>
      <strong>{!! Form::label('duration', __( 'lang_v1.duration' ) . ':') !!}*</strong>
      <div class="form-group">
          {!! Form::number('duration', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __( 'lang_v1.duration' ), 'required' ]); !!}

          {!! Form::select('duration_type', ['days' => __('lang_v1.days'), 'months' => __('lang_v1.months'), 'years' => __('lang_v1.years')], '', ['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select'), 'required']); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->