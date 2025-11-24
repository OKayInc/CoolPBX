<div>
    <!-- Web Component -->
    <dialplan-builder
        initial-data='@json($flowData)'
        readonly="true"></dialplan-builder>

    <!-- Stats -->
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="alert alert-info">
                <strong>📊 Nodos:</strong> {{ count($flowData['nodes']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success">
                <strong>🔗 Conexiones:</strong> {{ count($flowData['edges']) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-warning">
                <strong>📝 Detalles:</strong> {{ count($flowData['nodes']) - 1 }}
            </div>
        </div>
    </div>
</div>