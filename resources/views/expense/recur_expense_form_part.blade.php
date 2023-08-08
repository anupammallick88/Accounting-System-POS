<div class="box box-solid @if(!empty($expense->type) && $expense->type == 'expense_refund') hide @endif" id="recur_expense_div">
	<div class="box-body">
		<div class="row">
			<div class="col-md-4 col-sm-6">
				<br>
				<label>
	              {!! Form::checkbox('is_recurring', 1, !empty($expense->is_recurring) == 1, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.is_recurring')?
	            </label>@show_tooltip(__('lang_v1.recurring_expense_help'))
			</div>
			<div class="col-md-4 col-sm-6">
		        <div class="form-group">
		        	{!! Form::label('recur_interval', __('lang_v1.recur_interval') . ':*' ) !!}
		        	<div class="input-group">
		               {!! Form::number('recur_interval', !empty($expense->recur_interval) ? $expense->recur_interval : null, ['class' => 'form-control', 'style' => 'width: 50%;']); !!}
		               
		                {!! Form::select('recur_interval_type', ['days' => __('lang_v1.days'), 'months' => __('lang_v1.months'), 'years' => __('lang_v1.years')], !empty($expense->recur_interval_type) ? $expense->recur_interval_type : 'days', ['class' => 'form-control', 'style' => 'width: 50%;', 'id' => 'recur_interval_type']); !!}
		                
		            </div>
		        </div>
		    </div>

		    <div class="col-md-4 col-sm-6">
		        <div class="form-group">
		        	{!! Form::label('recur_repetitions', __('lang_v1.no_of_repetitions') . ':' ) !!}
		        	{!! Form::number('recur_repetitions', !empty($expense->recur_repetitions) ? $expense->recur_repetitions : null, ['class' => 'form-control']); !!}
			        <p class="help-block">@lang('lang_v1.recur_expense_repetition_help')</p>
		        </div>
		    </div>
		    @php
		    	$repetitions = [];
		    	for ($i=1; $i <= 30; $i++) { 
		    		$repetitions[$i] = str_ordinal($i);
		        }
		    @endphp
		    <div class="recur_repeat_on_div col-md-4 @if(empty($expense->recur_interval_type)) hide @elseif(!empty($expense->recur_interval_type) && $expense->recur_interval_type != 'months') hide @endif">
		        <div class="form-group">
		        	{!! Form::label('subscription_repeat_on', __('lang_v1.repeat_on') . ':' ) !!}
		        	{!! Form::select('subscription_repeat_on', $repetitions, !empty($expense->subscription_repeat_on) ? $expense->subscription_repeat_on : null, ['class' => 'form-control', 'placeholder' => __('messages.please_select')]); !!}
		        </div>
		    </div>
		</div>
	</div>
</div>