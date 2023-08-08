<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@store'), 'method' => 'post', 'id' => 'payment_account_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.add_account' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __( 'lang_v1.name' ) .":*") !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'required','placeholder' => __( 'lang_v1.name' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_number', __( 'account.account_number' ) .":*") !!}
                {!! Form::text('account_number', null, ['class' => 'form-control', 'required','placeholder' => __( 'account.account_number' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_type_id', __( 'account.account_type' ) .":") !!}
                <select name="account_type_id" class="form-control select2">\
                    <option>@lang('messages.please_select')</option>
                    @foreach($account_types as $account_type)
                        <optgroup label="{{$account_type->name}}">
                            <option value="{{$account_type->id}}">{{$account_type->name}}</option>
                            @foreach($account_type->sub_types as $sub_type)
                                <option value="{{$sub_type->id}}">{{$sub_type->name}}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                {!! Form::label('opening_balance', __( 'account.opening_balance' ) .":") !!}
                {!! Form::text('opening_balance', 0, ['class' => 'form-control input_number','placeholder' => __( 'account.opening_balance' ) ]); !!}
            </div>

            <label>@lang('lang_v1.account_details'):</label>
            <table class="table table-striped">
                <tr>
                    <th>
                        @lang('lang_v1.label')
                    </th>
                    <th>
                        @lang('product.value')
                    </th>
                </tr>
                @for ($i = 0; $i < 6; $i++)
                    <tr>
                        <td>
                            {!! Form::text('account_details['.$i.'][label]', null, ['class' => 'form-control']); !!}
                        </td>
                        <td>
                            {!! Form::text('account_details['.$i.'][value]', null, ['class' => 'form-control']); !!}      
                        </td>
                    </tr>
                @endfor
            </table>
        
            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->