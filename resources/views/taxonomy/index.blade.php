@extends('layouts.app')
@php
    $heading = !empty($module_category_data['heading']) ? $module_category_data['heading'] : __('category.categories');
    $navbar = !empty($module_category_data['navbar']) ? $module_category_data['navbar'] : null;
@endphp
@section('title', $heading)

@section('content')
@if(!empty($navbar))
    @include($navbar)
@endif
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{$heading }}
        <small>
            {{ $module_category_data['sub_heading'] ?? __( 'category.manage_your_categories' ) }}
        </small>
        @if(isset($module_category_data['heading_tooltip']))
            @show_tooltip($module_category_data['heading_tooltip'])
        @endif
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @php
        $cat_code_enabled = isset($module_category_data['enable_taxonomy_code']) && !$module_category_data['enable_taxonomy_code'] ? false : true;
    @endphp
    <input type="hidden" id="category_type" value="{{request()->get('type')}}">
    @php
        $can_add = true;
        if(request()->get('type') == 'product' && !auth()->user()->can('category.create')) {
            $can_add = false;
        }
    @endphp
    @component('components.widget', ['class' => 'box-solid', 'can_add' => $can_add])
            @if($can_add)
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action('TaxonomyController@create')}}?type={{request()->get('type')}}" 
                    data-container=".category_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
            @endif
       
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="category_table">
                    <thead>
                        <tr>
                            <th>@if(!empty($module_category_data['taxonomy_label'])) {{$module_category_data['taxonomy_label']}} @else @lang( 'category.category' ) @endif</th>
                            @if($cat_code_enabled)
                                <th>{{ $module_category_data['taxonomy_code_label'] ?? __( 'category.code' )}}</th>
                            @endif
                            <th>@lang( 'lang_v1.description' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
    @endcomponent

    <div class="modal fade category_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
@includeIf('taxonomy.taxonomies_js')
@endsection
