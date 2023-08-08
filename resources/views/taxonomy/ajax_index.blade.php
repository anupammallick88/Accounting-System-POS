@php
    $is_cat_code_enabled = isset($module_category_data['enable_taxonomy_code']) && !$module_category_data['enable_taxonomy_code'] ? false : true;
@endphp
@can('category.create')
	<button type="button" class="btn btn-sm pull-right btn-primary btn-modal" data-href="{{action('TaxonomyController@create')}}?type={{$category_type}}" data-container=".category_modal">
		<i class="fa fa-plus"></i>
		@lang( 'messages.add' )
	</button>
	<br><br>
@endcan

 @can('category.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="category_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>@if(!empty($module_category_data['taxonomy_label'])) {{$module_category_data['taxonomy_label']}} @else @lang( 'category.category' ) @endif</th>
                    @if($is_cat_code_enabled)
                        <th>{{ $module_category_data['taxonomy_code_label'] ?? __( 'category.code' )}}</th>
                    @endif
                    <th>@lang( 'lang_v1.description' )</th>
                    <th>@lang( 'messages.action' )</th>
                </tr>
            </thead>
        </table>
    </div>
@endcan

<div class="modal fade category_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>