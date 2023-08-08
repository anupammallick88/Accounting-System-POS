<div class="modal fade" id ="woocommerce_sync_modal" tabindex="-1" role="dialog">
    {!! Form::open(['url' => action('ProductController@toggleWooCommerceSync'), 'method' => 'post', 'id' => 'toggle_woocommerce_sync_form' ]) !!}
        <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    @lang('lang_v1.woocommerce_sync')
                </h4>
              </div>
              <div class="modal-body">
                <input type="hidden" id="woocommerce_products_sync" name="woocommerce_products_sync" value="">
                <div class="row">
                    <div class="col-md-12">
                        <label for="woocommerce_disable_sync">
                            @lang('lang_v1.woocommerce_sync')
                        </label>
                        <select name="woocommerce_disable_sync" class="form-control" id="woocommerce_disable_sync">
                            <option value="0">
                                @lang('lang_v1.enable')
                            </option>
                            <option value="1">
                                @lang('lang_v1.disable')
                            </option>
                        </select>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    @lang('messages.close')
                </button>
                <button type="submit" class="btn btn-primary ladda-button">
                    @lang('messages.save')
                </button>
              </div>
            </div>
        </div>
    {!! Form::close() !!}
</div>