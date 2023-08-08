<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@update',$account->id), 'method' => 'PUT', 'id' => 'edit_payment_account_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.edit_account' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __( 'lang_v1.name' ) .":*") !!}
                {!! Form::text('name', $account->name, ['class' => 'form-control', 'required','placeholder' => __( 'lang_v1.name' ) ]); !!}
            </div>

             <div class="form-group">
                {!! Form::label('account_number', __( 'account.account_number' ) .":*") !!}
                {!! Form::text('account_number', $account->account_number, ['class' => 'form-control', 'required','placeholder' => __( 'account.account_number' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_type_id', __( 'account.account_type' ) .":") !!}
                <select name="account_type_id" class="form-control select2">
                    <option>@lang('messages.please_select')</option>
                    @foreach($account_types as $account_type)
                        <optgroup label="{{$account_type->name}}">
                            <option value="{{$account_type->id}}" @if($account->account_type_id == $account_type->id) selected @endif >{{$account_type->name}}</option>
                            @foreach($account_type->sub_types as $sub_type)
                                <option value="{{$sub_type->id}}" @if($account->account_type_id == $sub_type->id) selected @endif >{{$sub_type->name}}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
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
                @if(!empty($account->account_details))
                    @foreach($account->account_details as $key => $account_detail)
                        <tr>
                            <td>
                                {!! Form::text('account_details['.$key.'][label]', !empty($account->account_details[$key]['label'])? $account->account_details[$key]['label'] : null, ['class' => 'form-control']); !!}
                            </td>
                            <td>
                                {!! Form::text('account_details['.$key.'][value]', !empty($account->account_details[$key]['value'])?$account->account_details[$key]['value']:null, ['class' => 'form-control']); !!}      
                            </td>
                        </tr>
                    @endforeach
                @else
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
                @endif
            </table>
            
            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', $account->note, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->