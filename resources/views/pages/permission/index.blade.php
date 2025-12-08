@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mt-3 card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-2"></i>
                    {{ __('Group Permissions') }} ({{ $group->group_name ?? __('All Permissions') }})
                </h3>

                <div class="card-tools">
                    <div class="d-flex gap-2 " role="group" aria-label="Group actions">
                        <a href="{{ route('groups.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-fast-backward" aria-hidden="true"></i>
                            {{ __('Back') }}
                        </a>


                        @can('permission_add')
                            <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                {{ __('Add') }}
                            </a>
                        @endcan

                        <a href="" class="btn btn-primary btn-sm">
                            <i class="fa fa-refresh" aria-hidden="true"></i>
                            {{ __('Reload') }}
                        </a>

                        <a href="" class="btn btn-primary btn-sm">
                            <i class="fas fa-users mr-1"></i> {{ __('Users') }}
                        </a>

                        <form
                            action="{{ $group ? route('permissions.index', ['groupUuid' => $group->group_uuid]) : route('permissions.all') }}"
                            method="GET" class="d-flex gap-2">
                            @if ($group)
                                <input type="hidden" name="group_uuid" value="{{ $groupUuid }}">
                            @endif

                            <select name="filter" class="form-select mr-2 " onchange="this.form.submit()">
                                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>{{ __('All') }}
                                </option>
                                <option value="assigned" {{ $filter === 'assigned' ? 'selected' : '' }}>
                                    {{ __('Assigned') }}</option>
                                <option value="not_assigned" {{ $filter === 'not_assigned' ? 'selected' : '' }}>
                                    {{ __('Not Assigned') }}</option>
                                <option value="protected" {{ $filter === 'protected' ? 'selected' : '' }}>
                                    {{ __('Protected') }}</option>
                            </select>

                            <input type="text" name="search" class="form-control mr-2"
                                placeholder="{{ __('Search...') }}" value="{{ $search ?? '' }}">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Search') }}</button>
                            @if ($search)
                                <a href="{{ $group ? route('permissions.index', ['groupUuid' => $group->group_uuid]) : route('permissions.all') }}"
                                    class="btn btn-secondary ml-2">{{ __('Clear') }}</a>
                            @endif
                        </form>

                        @if ($group)
                            @can('group_permission_edit')
                                <button type="submit" form="permissions-form" class="btn btn-primary btn-sm">
                                    <i class="fa fa-bolt" aria-hidden="true"></i> {{ __('Save') }}
                                </button>
                            @endcan
                        @endif

                    </div>
                </div>
            </div>

            @if ($group)
                <form action="{{ route('permissions.update', ['groupUuid' => $group->group_uuid]) }}" method="POST"
                    id="permissions-form">
                    @csrf
                    @method('PUT')
            @endif
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Permission') }}</th>
                                <th>{{ __('Protected') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissionsByApp as $appName)
                                <tr class="table-secondary">
                                    <td colspan="2" class="fw-bold">
                                        <i class="fas fa-folder-open mr-2"></i>{{ $appName }}
                                    </td>
                                </tr>

                                @foreach ($permissions->where('application_name', $appName) as $permission)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="permissions[]"
                                                    value="{{ $permission->permission_name }}"
                                                    id="permission-{{ $permission->permission_name }}"
                                                    {{ $permission->groupPermissionByGroup ? 'checked' : '' }}
                                                    {{ !auth()->user()->hasPermission('group_permission_edit') ? 'disabled' : '' }}>

                                                <label class="form-check-label"
                                                    for="permission-{{ $permission->permission_name }}">
                                                    <a href="{{ route('permissions.edit', ['permissionUuid' => $permission->permission_uuid]) }}"
                                                        class="text-decoration-none">
                                                        {{ $permission->permission_name }}
                                                    </a>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    name="permissions_protected[]"
                                                    value="{{ $permission->permission_name }}"
                                                    id="permission-protected-{{ $permission->permission_name }}"
                                                    {{ $permission->groupPermissionByGroup?->permission_protected === 'true' ? 'checked' : '' }}
                                                    {{ !auth()->user()->hasPermission('group_permission_edit') ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($group)
                </form>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const searchForm = searchInput?.closest('form');
            const table = document.querySelector('.table-responsive table');
            let highlightedRows = [];
            let shouldScroll = false;

            function highlightAndScroll(searchTerm, doScroll = false) {
                highlightedRows.forEach(row => {
                    row.style.backgroundColor = '';
                    row.style.transition = '';
                    row.classList.remove('highlight-match');

                    const label = row.querySelector('.form-check-label');
                    if (label) {
                        const originalText = label.getAttribute('data-original-text') || label.textContent;
                        label.innerHTML =
                            `<a href="${label.querySelector('a')?.href || '#'}" class="text-decoration-none">${originalText}</a>`;
                    }
                });
                highlightedRows = [];

                if (!searchTerm) {
                    showSearchResults(0);
                    return;
                }

                const normalizedSearch = searchTerm.toLowerCase().trim();
                const rows = table.querySelectorAll('tbody tr:not(.table-secondary)');
                let firstMatch = null;
                let matchCount = 0;

                rows.forEach(row => {
                    const checkbox = row.querySelector('input[name="permissions[]"]');
                    const label = row.querySelector('.form-check-label');

                    if (checkbox && label) {
                        const permissionName = checkbox.value;
                        const lowerPermissionName = permissionName.toLowerCase();

                        if (lowerPermissionName.includes(normalizedSearch)) {
                            highlightedRows.push(row);
                            matchCount++;

                            if (!label.getAttribute('data-original-text')) {
                                label.setAttribute('data-original-text', permissionName);
                            }

                            row.classList.add('highlight-match');
                            row.style.backgroundColor = '#fff3cd';
                            row.style.transition = 'background-color 0.3s ease';

                            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                            const highlightedText = permissionName.replace(regex,
                                '<mark class="search-highlight">$1</mark>');

                            const link = label.querySelector('a');
                            const href = link ? link.href : '#';
                            label.innerHTML =
                                `<a href="${href}" class="text-decoration-none">${highlightedText}</a>`;

                            if (!firstMatch) {
                                firstMatch = row;
                            }
                        }
                    }
                });

                if (firstMatch && doScroll) {
                    setTimeout(() => {
                        firstMatch.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 100);
                }

                showSearchResults(matchCount);

                if (matchCount > 0) {
                    setTimeout(() => {
                        highlightedRows.forEach(row => {
                            row.style.backgroundColor = '';
                            row.classList.remove('highlight-match');
                        });
                    }, 8000);
                }
            }

            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function showSearchResults(count) {
                let resultBadge = document.getElementById('search-result-badge');

                if (!resultBadge) {
                    resultBadge = document.createElement('span');
                    resultBadge.id = 'search-result-badge';
                    resultBadge.className = 'badge ms-2';
                    searchInput.parentElement.appendChild(resultBadge);
                }

                if (count > 0) {
                    resultBadge.className = 'badge bg-success ms-2';
                    resultBadge.textContent = `${count} found${count > 1 ? 's' : ''}`;
                } else if (searchInput.value.trim()) {
                    resultBadge.className = 'badge bg-warning ms-2';
                    resultBadge.textContent = 'No results';
                } else {
                    resultBadge.remove();
                }
            }

            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            if (searchParam) {
                highlightAndScroll(searchParam, true); 
            }

            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const searchValue = searchInput.value;

                    const url = new URL(window.location);
                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.history.pushState({}, '', url);

                    highlightAndScroll(searchValue, true); 
                });

                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        highlightAndScroll(this.value, false); 
                    }, 300);
                });
            }

            const clearButton = document.querySelector('a.btn-secondary');
            if (clearButton) {
                clearButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchInput.value = '';
                    highlightAndScroll('', false);

                    const url = new URL(window.location);
                    url.searchParams.delete('search');
                    window.history.pushState({}, '', url);

                    const badge = document.getElementById('search-result-badge');
                    if (badge) badge.remove();
                });
            }
        });
    </script>

    <style>
        #search-result-badge {
            animation: fadeIn 0.3s ease;
            font-size: 0.875rem;
            padding: 0.35rem 0.65rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input[name="search"] {
            min-width: 250px;
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }

        .table tbody tr.highlight-match {
            border-left: 4px solid #ffc107 !important;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
        }

        .search-highlight {
            background-color: #ffeb3b;
            color: #000;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            animation: pulse 0.5s ease;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }
        .table-secondary {
            background-color: #e9ecef !important;
            font-weight: 600;
        }
    </style>
@endpush
