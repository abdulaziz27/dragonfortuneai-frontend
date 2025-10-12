<div class="df-panel p-4 h-100">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-1">⚖️ Market Indicators</h5>
            <small class="text-secondary">Estimated leverage ratios and market gauges</small>
        </div>
        <div class="d-flex gap-2">
            <span x-show="loadingStates.indicators" class="spinner-border spinner-border-sm text-primary"></span>
            <button class="btn btn-sm btn-outline-primary" @click="refreshIndicatorsData()">Refresh</button>
        </div>
    </div>
    
    <div style="height: 350px; position: relative;">
        <canvas x-ref="indicatorsChart"></canvas>
    </div>
    
    <!-- Risk Zones Legend -->
    <div class="row g-2 mt-3">
        <div class="col-4">
            <div class="d-flex align-items-center gap-2">
                <div class="legend-dot" style="background-color: #22c55e;"></div>
                <small class="text-secondary">Low Risk (< 0.3)</small>
            </div>
        </div>
        <div class="col-4">
            <div class="d-flex align-items-center gap-2">
                <div class="legend-dot" style="background-color: #f59e0b;"></div>
                <small class="text-secondary">Medium Risk (0.3-0.5)</small>
            </div>
        </div>
        <div class="col-4">
            <div class="d-flex align-items-center gap-2">
                <div class="legend-dot" style="background-color: #ef4444;"></div>
                <small class="text-secondary">High Risk (> 0.5)</small>
            </div>
        </div>
    </div>
</div>

<style>
.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
</style>