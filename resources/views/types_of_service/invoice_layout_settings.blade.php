<div class="box box-solid">
    <div class="box-header">
      <h3 class="box-title">@lang('lang_v1.types_of_service_module_settings')</h3>
    </div>
    <div class="box-body">
      <div class="row">
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('types_of_service_label', __('lang_v1.types_of_service_label') . ':' ) !!}
            {!! Form::text('module_info[types_of_service][types_of_service_label]', !empty($module_info['types_of_service']['types_of_service_label']) ? $module_info['types_of_service']['types_of_service_label'] : null, ['class' => 'form-control',
              'placeholder' => __('lang_v1.types_of_service_label') ]); !!}
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <br>
            <div class="checkbox">
              <label>
                {!! Form::checkbox('module_info[types_of_service][show_types_of_service]', 1, !empty($module_info['types_of_service']['show_types_of_service']), ['class' => 'input-icheck']); !!} @lang('lang_v1.show_types_of_service')</label>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <br>
            <div class="checkbox">
              <label>
                {!! Form::checkbox('module_info[types_of_service][show_tos_custom_fields]', 1, !empty($module_info['types_of_service']['show_tos_custom_fields']), ['class' => 'input-icheck']); !!} @lang('lang_v1.show_tos_custom_fields')</label>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>