@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mt-3 card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group mr-2"></i> {{__('Active')}}
            </h3>

            <div class="card-tools">
                <div class="d-flex gap-2" role="fax" aria-label="Faxes actions">

                </div>
            </div>
        </div>

        <div class="card-body">
			<table class="table table-sm table-hover align-middle">
				<thead>
					<tr>
						<th>Server</th>
						<th>Enabled</th>
						<th>Status</th>
						<th>Next</th>
						<th>Files</th>
						<th>URI</th>
						<th></th>
					</tr>
				</thead>
				<livewire:faxes-active-table :fax="$fax" />
			</table>

        </div>
    </div>
</div>
@endsection

