{{-- Live Orderbook Snapshot Component --}}
<div class="df-panel p-3" x-data="liveOrderbookSnapshot()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">📖 Live Orderbook Snapshot</h5>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary" x-show="loading">Loading...</span>
        </div>
    </div>

    <div class="row">
        <!-- Asks (Sell Orders) -->
        <div class="col-md-5">
            <h6 class="text-danger mb-2">Asks (Sell Orders)</h6>
            
            <!-- Column Headers for Asks -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                <span class="small fw-bold text-secondary">Price</span>
                <span class="small fw-bold text-secondary">Size</span>
                <span class="small fw-bold text-secondary">Total</span>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <template x-if="asks.length > 0">
                    <div>
                        <template x-for="(ask, index) in asks" :key="index">
                            <div class="orderbook-row d-flex justify-content-between align-items-center">
                                <div class="orderbook-bg orderbook-bg-ask"
                                     :style="`width: ${calculateDepthPercentage(ask.size, getMaxQuantity(asks))}%`">
                                </div>
                                <span class="small text-danger" x-text="formatPrice(ask.price)" style="position: relative; z-index: 1;"></span>
                                <span class="small" x-text="formatQuantity(ask.size)" style="position: relative; z-index: 1;"></span>
                                <span class="small text-secondary" x-text="formatTotal(ask.price, ask.size)" style="position: relative; z-index: 1;"></span>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="asks.length === 0">
                    <div class="text-center text-secondary small">No ask data</div>
                </template>
            </div>
        </div>

        <!-- Spread Info -->
        <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
            <div class="text-center py-3">
                <div class="small text-secondary mb-1">Mid Price</div>
                <div class="h5 mb-2 fw-bold text-primary" x-text="formatPrice(midPrice)">--</div>
                <div class="small text-secondary mb-1">Spread</div>
                <div class="h6 mb-0" x-text="formatPrice(spread)">--</div>
            </div>
        </div>

        <!-- Bids (Buy Orders) -->
        <div class="col-md-5">
            <h6 class="text-success mb-2">Bids (Buy Orders)</h6>
            
            <!-- Column Headers for Bids -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                <span class="small fw-bold text-secondary">Price</span>
                <span class="small fw-bold text-secondary">Size</span>
                <span class="small fw-bold text-secondary">Total</span>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <template x-if="bids.length > 0">
                    <div>
                        <template x-for="(bid, index) in bids" :key="index">
                            <div class="orderbook-row d-flex justify-content-between align-items-center">
                                <div class="orderbook-bg orderbook-bg-bid"
                                     :style="`width: ${calculateDepthPercentage(bid.size, getMaxQuantity(bids))}%`">
                                </div>
                                <span class="small text-success" x-text="formatPrice(bid.price)" style="position: relative; z-index: 1;"></span>
                                <span class="small" x-text="formatQuantity(bid.size)" style="position: relative; z-index: 1;"></span>
                                <span class="small text-secondary" x-text="formatTotal(bid.price, bid.size)" style="position: relative; z-index: 1;"></span>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="bids.length === 0">
                    <div class="text-center text-secondary small">No bid data</div>
                </template>
            </div>
        </div>
    </div>
</div>

