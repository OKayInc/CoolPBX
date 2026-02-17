<div class="form-group" style="column-count: {{ $columns }};">
	@foreach($options as $option)
		@foreach($option->values as $value)
			@php
				$checked = in_array($value->id, $selected);
			@endphp
			<div class="form-check">
				<input class="form-check-input" type="{{ $type }}" name="{{ $name }}@if($type == 'checkbox')[]@endif" value="{{ $value->id }}" @if($checked) checked @endif>
				<label class="form-check-label">{{ $value->name }}</label>
			</div>
		@endforeach
	@endforeach
</div>
