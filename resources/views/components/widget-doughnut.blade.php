@php
    $widget = $widget ?? '';
    $count = $count ?? 0;
    $labels = $labels ?? [];
    $values = $values ?? [];
    $colors = $colors ?? [];

    if(empty($labels) || empty($values) || empty($colors) || empty($count))
    {
        $labels = ['No data'];
        $values = [100];
        $colors = ['#d2d6de'];
    }

    $cols = (int) ($cols ?? 2);
    $valid = [1,2,3,4,6];

    if(!in_array($cols, $valid))
    {
        $cols = 2;
    }

    $colClass = "col-lg-" . (12 / $cols) . " col-12";
@endphp

<div class="{{ $colClass }}">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <!-- <h6 class="text-muted mb-2">{{ $title }}</h6> -->
                <div class="widget-title mb-2">{{ $title }}</div>
                <!-- <i class="fas fa-ellipsis-v text-muted"></i> -->
                @isset($controls)
                    <div>
                        {{ $controls }}
                    </div>
                @endisset
            </div>
            <div class="d-flex justify-content-center">
                <div style="max-width: 220px; width: 100%;">
                    <canvas class="dashboard"
                        data-widget="{{ $widget }}"
                        data-type="{{ $type }}"
                        data-labels='@json($labels)'
                        data-values='@json($values)'
                        data-colors='@json($colors)'
                        data-extra='@json($extra)'
                        data-links='@json($links)'
                        data-count='@json($count)'
                        height="200"
                    ></canvas>
                </div>
            </div>
            @isset($table)
                <div class="mt-3">
                    {{ $table }}
                </div>
            @endisset
        </div>
    </div>
</div>
