{{-- Market Depth Stats Component --}}
<div class="df-panel p-3 h-100" x-data="marketDepthStats()" x-init="init()">
    <h5 class="mb-3">📏 Market Depth</h5>

    <div class="d-flex flex-column gap-3">
        <div class="stat-item">
            <div class="small text-secondary mb-1">Depth Score</div>
            <div class="h4 mb-0 text-primary" x-text="depthScore.toFixed(2)">--</div>
            <div class="small text-secondary">Market stability indicator</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Bid Levels</div>
            <div class="h5 mb-0 text-success" x-text="bidLevels">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Ask Levels</div>
            <div class="h5 mb-0 text-danger" x-text="askLevels">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Total Bid Volume</div>
            <div class="h6 mb-0 text-success" x-text="formatVolume(totalBidVolume)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Total Ask Volume</div>
            <div class="h6 mb-0 text-danger" x-text="formatVolume(totalAskVolume)">--</div>
        </div>
    </div>

    <div class="mt-3 text-center" x-show="loading">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
    </div>
</div>

