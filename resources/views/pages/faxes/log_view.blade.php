@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card card-primary mt-3">
        <div class="card-header">
            <h3 class="card-title">
                {{ 'View Log' }}
            </h3>
        </div>

        <form action="">
            @csrf

            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Success</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_success }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Result Code</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_result_code }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Result Text</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_result_text }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">File</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_file }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">ECM Used</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_ecm_used }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Local Station ID</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_local_station_id }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Transferred Pages</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_document_transferred_pages }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Total Pages</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_document_total_pages }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Image Resolution</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_image_resolution }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Image Size</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_image_size }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Bad Rows</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_bad_rows }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Transfer Rate</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_transfer_rate }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Retry Attempts</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_retry_attempts }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Retry Limit</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_retry_limit }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Retry Sleep</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_retry_sleep }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">URI</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_uri }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_date }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Epoch</label>
                            <input type="text" class="form-control" value="{{ $faxLog->fax_epoch }}" readonly>
                        </div>
                    </div>
                </div>


            </div>

            <div class="card-footer">
                <a href="{{ route('faxes.logs', [$faxLog->fax_uuid]) }}" class="btn btn-secondary ml-2 px-4 py-2" style="border-radius: 4px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
