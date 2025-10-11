{{-- Liquidity Imbalance Component --}}
<div class="df-panel p-3 h-100" x-data="liquidityImbalance()" x-init="init()">
    <h5 class="mb-3">⚖️ Liquidity Imbalance</h5>

    <div class="d-flex flex-column gap-3">
        <div class="stat-item">
            <div class="small text-secondary mb-1">Total Liquidity</div>
            <div class="h5 mb-0" x-text="formatLiquidity(totalLiquidity)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Bid/Ask Ratio</div>
            <div class="h5 mb-0" :class="bidAskRatio >= 1 ? 'text-success' : 'text-danger'" x-text="bidAskRatio.toFixed(4)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Imbalance</div>
            <div class="h4 mb-0" :class="getImbalanceClass()" x-text="formatLiquidity(imbalance)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Imbalance %</div>
            <div class="h5 mb-0" :class="getImbalanceClass()" x-text="formatPercent(imbalancePct)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Bid Liquidity</div>
            <div class="h6 mb-0 text-success" x-text="formatLiquidity(totalBidLiquidity)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Ask Liquidity</div>
            <div class="h6 mb-0 text-danger" x-text="formatLiquidity(totalAskLiquidity)">--</div>
        </div>
    </div>

    <div class="mt-3 text-center" x-show="loading">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
    </div>
</div>

