
<div class="df-panel p-4" x-data="bookPressureCard()" x-init="init()">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1">📊 Book Pressure Analysis</h5>
            <p class="small text-secondary mb-0">Order book pressure direction and strength based on bid/ask depth</p>
        </div>
        <span class="badge" :class="getDirectionClass()" x-text="pressureDirection.toUpperCase()">Loading...</span>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="text-center">
                <div class="small text-secondary mb-1">Bid Pressure</div>
                <div class="h4 mb-0 text-success" x-text="formatNumber(bidPressure)">--</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="small text-secondary mb-1">Ask Pressure</div>
                <div class="h4 mb-0 text-danger" x-text="formatNumber(askPressure)">--</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="small text-secondary mb-1">Pressure Ratio</div>
                <div class="h4 mb-0" :class="pressureRatio >= 1 ? 'text-success' : 'text-danger'" x-text="formatNumber(pressureRatio)">--</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <div class="small text-secondary mb-1">Sample Size</div>
                <div class="h4 mb-0" x-text="sampleSize">--</div>
            </div>
        </div>
    </div>

    <!-- Progress bar for bid/ask pressure -->
    <div class="mt-3">
        <div class="d-flex justify-content-between small mb-1">
            <span class="text-success">Bid Pressure</span>
            <span class="text-danger">Ask Pressure</span>
        </div>
        <div class="progress" style="height: 24px;">
            <div class="progress-bar bg-success" role="progressbar"
                 :style="`width: ${(bidPressure / (bidPressure + askPressure)) * 100}%`"
                 x-text="((bidPressure / (bidPressure + askPressure)) * 100).toFixed(1) + '%'">
            </div>
            <div class="progress-bar bg-danger" role="progressbar"
                 :style="`width: ${(askPressure / (bidPressure + askPressure)) * 100}%`"
                 x-text="((askPressure / (bidPressure + askPressure)) * 100).toFixed(1) + '%'">
            </div>
        </div>
    </div>

    <div class="mt-3 text-center" x-show="loading">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
        <span class="small text-secondary ms-2">Loading book pressure...</span>
    </div>
</div>

<?php /**PATH C:\DATASAID\Said\Bisnis\quantwaru\frontend\dragonfortuneai-frontend\resources\views/components/orderbook/pressure-card.blade.php ENDPATH**/ ?>