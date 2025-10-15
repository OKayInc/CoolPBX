@php
    $labels = $labels ?? [];
    $values = $values ?? [];
    $colors = $colors ?? [];

    if(empty($labels) || empty($values) || empty($colors))
    {
        $labels = ['No data'];
        $values = [100];
        $colors = ['#d2d6de'];
    }
@endphp

<div class="col-lg-6 col-12">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6 class="text-muted mb-2">{{ $title }}</h6>
                <i class="fas fa-ellipsis-v text-muted"></i>
            </div>
            <div class="d-flex justify-content-center">
                <div style="max-width: 220px; width: 100%;">
                    <canvas class="dashboard"
                        data-type="{{ $type }}"
                        data-labels='@json($labels)'
                        data-values='@json($values)'
                        data-colors='@json($colors)'
                        data-count='@json($count)'
                        height="200"
                    ></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
