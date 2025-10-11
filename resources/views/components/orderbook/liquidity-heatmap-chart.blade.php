{{-- Liquidity Distribution Table Component --}}
<div class="df-panel p-3" x-data="liquidityDistributionTable()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">🔥 Liquidity Distribution</h5>
        <span class="badge bg-secondary" x-show="loading">Loading...</span>
    </div>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-hover">
            <thead class="sticky-top bg-dark">
                <tr>
                    <th>Price Level</th>
                    <th>Bid Liquidity</th>
                    <th>Ask Liquidity</th>
                    <th>Total</th>
                    <th>Distribution</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="!loading && liquidityData.length > 0">
                    <template x-for="item in liquidityData" :key="item.price_level">
                        <tr>
                            <td class="small fw-semibold" x-text="formatPrice(item.price_level)"></td>
                            <td class="small text-success" x-text="formatLiquidity(item.bid_liquidity)"></td>
                            <td class="small text-danger" x-text="formatLiquidity(item.ask_liquidity)"></td>
                            <td class="small" x-text="formatLiquidity(item.total_liquidity)"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             :style="`width: ${getBidPercentage(item)}%`">
                                        </div>
                                        <div class="progress-bar bg-danger" role="progressbar"
                                             :style="`width: ${getAskPercentage(item)}%`">
                                        </div>
                                    </div>
                                    <small class="text-secondary" x-text="getImbalanceText(item)"></small>
                                </div>
                            </td>
                        </tr>
                    </template>
                </template>
                <template x-if="!loading && liquidityData.length === 0">
                    <tr>
                        <td colspan="5" class="text-center text-secondary">No liquidity data available</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="mt-2 small text-secondary" x-show="!loading && liquidityData.length > 0">
        Showing <span x-text="liquidityData.length"></span> price levels
    </div>
</div>

