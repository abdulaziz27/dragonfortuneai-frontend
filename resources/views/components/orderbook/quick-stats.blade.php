{{-- Quick Stats Component --}}
<div class="df-panel p-3 h-100" x-data="quickStats()" x-init="init()">
    <h5 class="mb-3">⚡ Quick Stats</h5>

    <div class="d-flex flex-column gap-3">
        <div class="stat-item">
            <div class="small text-secondary mb-1">Mid Price</div>
            <div class="h4 mb-0 text-primary" x-text="formatPrice(midPrice)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Current Spread</div>
            <div class="h5 mb-0" x-text="formatPrice(currentSpread)">--</div>
        </div>

        <div class="stat-item">
            <div class="small text-secondary mb-1">Spread %</div>
            <div class="h5 mb-0" x-text="formatPercent(spreadPercent)">--</div>
        </div>

        <hr class="my-2">

        <div class="alert alert-info mb-0 small">
            <div class="fw-semibold mb-1">💡 Market Status</div>
            <div x-show="spreadPercent < 0.01">
                Tight spread - High liquidity market
            </div>
            <div x-show="spreadPercent >= 0.01 && spreadPercent < 0.05">
                Normal spread - Moderate liquidity
            </div>
            <div x-show="spreadPercent >= 0.05">
                Wide spread - Low liquidity or volatile market
            </div>
        </div>
    </div>

    <div class="mt-3 text-center" x-show="loading">
        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
    </div>
</div>

