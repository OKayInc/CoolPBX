@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">Destination export</h3>
        </div>

		<form action="{{ route('destinations.exportpost') }}" method="post">
			@csrf
			<div class="card-body">
				<h4>Select the fields you wish to include in the export.</h4>
				<div class="form-group">
					<label><input type="checkbox" class="form-check-input select-group"> Select all</label>
				</div>
				<hr>
				@foreach($available_columns as $c)
				<div class="form-group">
					<label><input type="checkbox" name="columns[]" class="form-check-input" value="{{ $c }}"> {{ $c }}</label>
				</div>
				@endforeach
        	</div>

			<div class="card-footer">
				<button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 4px;">Export</button>
			</div>

		</form>

    </div>
</div>

@endsection

@push("scripts")
<script>

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.select-group').forEach(groupCheckbox => {
        groupCheckbox.addEventListener('change', function()
        {
            const form = this.closest("form");
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');

            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });
});

</script>
@endpush
