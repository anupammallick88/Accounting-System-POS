<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@updateAccountTransaction', ['id' => $account_transaction->id ]), 'method' => 'post', 'id' => 'edit_account_transaction_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@if($account_transaction->sub_type == 'opening_balance')@lang( 'lang_v1.edit_opening_balance' ) @elseif($account_transaction->sub_type == 'fund_transfer') @lang( 'lang_v1.edit_fund_transfer' ) @elseif($account_transaction->sub_type == 'deposit') @lang( 'lang_v1.edit_deposit' ) @endif</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                <strong>@lang('account.selected_account')</strong>: 
                {{$account_transaction->account->name}}
            </div>

            @if($account_transaction->sub_type == 'deposit')
            @php
              $label = !empty($account_transaction->type == 'debit') ? __( 'account.deposit_from' ) :  __('lang_v1.deposit_to');
            @endphp 
            <div class="form-group">  
                {!! Form::label('account_id', $label .":") !!}
                {!! Form::select('account_id', $accounts, $account_transaction->account_id, ['class' => 'form-control', 'placeholder' => __('messages.please_select') ]); !!}
            </div>
            @endif

            @if($account_transaction->sub_type == 'fund_transfer') 
            @php
              $label = !empty($account_transaction->type == 'credit') ? __( 'account.transfer_to' ) :__('lang_v1.transfer_from')  ;
            @endphp 
            <div class="form-group">  
                {!! Form::label('account_id', $label .":") !!}
                {!! Form::select('account_id', $accounts, $account_transaction->account_id, ['class' => 'form-control', 'placeholder' => __('messages.please_select') ]); !!}
            </div>
            @endif

            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', @num_format($account_transaction->amount), ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]); !!}
            </div>
            @if($account_transaction->sub_type == 'deposit')
            @php
              $label = !empty($account_transaction->type == 'debit') ? __('lang_v1.deposit_to') :  __( 'account.deposit_from' );
            @endphp 
            <div class="form-group">  
                {!! Form::label('from_account', $label .":") !!}
                {!! Form::select('from_account', $accounts, $account_transaction->transfer_transaction->account_id ?? null, ['class' => 'form-control', 'placeholder' => __('messages.please_select') ]); !!}
            </div>
            @endif
            @if($account_transaction->sub_type == 'fund_transfer') 
            @php
              $label = !empty($account_transaction->type == 'credit') ? __('lang_v1.transfer_from') :  __( 'account.transfer_to' );
            @endphp 
            <div class="form-group">
                {!! Form::label('to_account', $label .":*") !!}
                {!! Form::select('to_account', $accounts, $account_transaction->transfer_transaction->account_id ?? null, ['class' => 'form-control', 'required' ]); !!}
            </div>
            @endif
 
            <div class="form-group">
                {!! Form::label('operation_date', __( 'messages.date' ) .":*") !!}
                <div class="input-group date">
                  {!! Form::text('operation_date', @format_datetime($account_transaction->operation_date), ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ), 'id' => 'od_datetimepicker' ]); !!}
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                </div>
            </div>
            @if($account_transaction->sub_type == 'fund_transfer' || $account_transaction->sub_type == 'deposit')
            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', $account_transaction->note, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
            @endif
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