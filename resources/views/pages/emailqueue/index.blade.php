@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i> {{ __('Email Queue Table') }}
                </h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm-" data-bs-toggle="modal" data-bs-target="#testEmailModal"
                        id="openTestModal">
                        <i class="fas fa-envelope"></i> Test
                    </button>
                </div>
            </div>

            <div class="card-body">
                <livewire:email-queue-table />
            </div>
        </div>
    </div>

    <div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Test</h5>
                </div>
                <div class="modal-body">
                    <div id="testForm">
                        <div class="form-group">
                            <label for="testEmailInput">Email Address:</label>
                            <input type="email" id="testEmailInput" class="form-control" placeholder="Enter email address"
                                required>
                        </div>
                        <button type="button" id="sendTestBtn" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </div>

                    <div id="testSpinner" style="display: none;" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Sending...</span>
                        </div>
                        <p class="mt-2">Sending test email...</p>
                    </div>

                    <div id="testResult" style="display: none;">
                        <h6>Settings:</h6>
                        <div id="settingsInfo"></div>

                        <h6 class="mt-3">Result:</h6>
                        <div id="resultInfo"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('sendTestBtn').addEventListener('click', function() {
            const email = document.getElementById('testEmailInput').value;

            if (!email || !email.includes('@')) {
                alert('Please enter a valid email address');
                return;
            }

            document.getElementById('testForm').style.display = 'none';
            document.getElementById('testSpinner').style.display = 'block';
            document.getElementById('testResult').style.display = 'none';

            fetch('/api/email/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')
                            ?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        to: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('testSpinner').style.display = 'none';
                    document.getElementById('testResult').style.display = 'block';

                    if (data.settings) {
                        let settingsHtml = '<table class="table table-sm"><tbody>';
                        for (const [key, value] of Object.entries(data.settings)) {
                            settingsHtml += `<tr><td>${key}</td><td>${value || 'Not set'}</td></tr>`;
                        }
                        settingsHtml += '</tbody></table>';
                        document.getElementById('settingsInfo').innerHTML = settingsHtml;
                    }

                    const resultClass = data.success ? 'alert-success' : 'alert-danger';
                    let resultHtml = `<div class="alert ${resultClass}">
            <strong>${data.message}</strong>
        `;

                    if (data.success && data.recipient) {
                        resultHtml += `<br>Recipient: <a href="mailto:${data.recipient}">${data.recipient}</a>`;
                    }

                    if (!data.success && data.error) {
                        resultHtml += `<br>Error: ${data.error}`;
                    }

                    resultHtml += '</div>';
                    document.getElementById('resultInfo').innerHTML = resultHtml;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('testSpinner').style.display = 'none';
                    document.getElementById('testResult').style.display = 'block';
                    document.getElementById('resultInfo').innerHTML =
                        '<div class="alert alert-danger">An error occurred while sending the test email.</div>';
                });
        });

        const testModal = document.getElementById('testEmailModal');
        testModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('testForm').style.display = 'block';
            document.getElementById('testSpinner').style.display = 'none';
            document.getElementById('testResult').style.display = 'none';
            document.getElementById('testEmailInput').value = '';
        });
    </script>
@endpush
