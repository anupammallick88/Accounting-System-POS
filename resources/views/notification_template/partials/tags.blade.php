@foreach($tags as $tag)
<p class="help-block">
	{{implode(', ', $tag)}}
</p>
@endforeach