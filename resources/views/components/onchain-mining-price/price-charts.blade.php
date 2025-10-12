<div class="df-panel p-4 h-100">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-1">📈 Price Analysis</h5>
            <small class="text-secondary">OHLCV data for major assets, ERC20 tokens, and stablecoins</small>
        </div>
        <div class="d-flex gap-2">
            <!-- Chart Type Toggle -->
            <select class="form-select form-select-sm" style="width: 120px;" x-model="chartType" @change="updatePriceChart()">
                <option value="line">Line Chart</option>
                <option value="candlestick">Candlestick</option>
            </select>
            <span x-show="loadingStates.price" class="spinner-border spinner-border-sm text-primary"></span>
            <button class="btn btn-sm btn-outline-primary" @click="refreshPriceData()">Refresh</button>
        </div>
    </div>
    
    <div style="height: 350px; position: relative;">
        <canvas x-ref="priceChart"></canvas>
    </div>
    
    <!-- Price Metrics Summary -->
    <div class="row g-3 mt-3">
        <div class="col-3">
            <div class="text-center p-2 rounded" style="background: rgba(34, 197, 94, 0.1);">
                <div class="small text-muted">Open</div>
                <div class="fw-bold" x-text="formatPrice(latestPriceData?.open, selectedAsset)">--</div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-center p-2 rounded" style="background: rgba(239, 68, 68, 0.1);">
                <div class="small text-muted">High</div>
                <div class="fw-bold" x-text="formatPrice(latestPriceData?.high, selectedAsset)">--</div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-center p-2 rounded" style="background: rgba(59, 130, 246, 0.1);">
                <div class="small text-muted">Low</div>
                <div class="fw-bold" x-text="formatPrice(latestPriceData?.low, selectedAsset)">--</div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-center p-2 rounded" style="background: rgba(139, 92, 246, 0.1);">
                <div class="small text-muted">Volume</div>
                <div class="fw-bold" x-text="formatVolume(latestPriceData?.volume, selectedAsset)">--</div>
            </div>
        </div>
    </div>
</div>