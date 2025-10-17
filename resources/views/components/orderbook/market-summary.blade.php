{{-- Market Summary Component --}}
<div class="df-panel p-3" x-data="marketSummary()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">📊 Market Summary</h5>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary" x-show="loading">Loading...</span>
        </div>
    </div>

    <div x-show="!loading && currentDepth">
        <div class="row g-3">
            <!-- Bid Side Summary -->
            <div class="col-md-6">
                <div class="card border-0 bg-success bg-opacity-10">
                    <div class="card-body p-3">
                        <h6 class="card-title text-success mb-3">📈 Bid Side (Buy Orders)</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-secondary">Active Levels:</span>
                            <span class="fw-bold text-success" x-text="currentDepth?.bid_levels || 0"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-secondary">Total Volume:</span>
                            <span class="fw-bold text-success" x-text="formatVolume(currentDepth?.total_bid_volume || 0)"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-secondary">Avg per Level:</span>
                            <span class="fw-bold text-success" x-text="formatVolume(getAvgVolumePerLevel(currentDepth?.total_bid_volume, currentDepth?.bid_levels))"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ask Side Summary -->
            <div class="col-md-6">
                <div class="card border-0 bg-danger bg-opacity-10">
                    <div class="card-body p-3">
                        <h6 class="card-title text-danger mb-3">📉 Ask Side (Sell Orders)</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-secondary">Active Levels:</span>
                            <span class="fw-bold text-danger" x-text="currentDepth?.ask_levels || 0"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-secondary">Total Volume:</span>
                            <span class="fw-bold text-danger" x-text="formatVolume(currentDepth?.total_ask_volume || 0)"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-secondary">Avg per Level:</span>
                            <span class="fw-bold text-danger" x-text="formatVolume(getAvgVolumePerLevel(currentDepth?.total_ask_volume, currentDepth?.ask_levels))"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Market Depth Score -->
            <div class="col-12">
                <div class="card border-0 bg-primary bg-opacity-10">
                    <div class="card-body p-3 text-center">
                        <h6 class="card-title mb-3">🎯 Market Liquidity Assessment</h6>
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="h3 mb-1 fw-bold text-primary" x-text="currentDepth?.depth_score?.toFixed(1) || '0.0'"></div>
                                <div class="small text-secondary">Depth Score</div>
                            </div>
                            <div class="col-md-4">
                                <div class="h5 mb-1 fw-bold" :class="getLiquidityClass(currentDepth?.depth_score)" x-text="getLiquidityAssessment(currentDepth?.depth_score)"></div>
                                <div class="small text-secondary">Liquidity Quality</div>
                            </div>
                            <div class="col-md-4">
                                <div class="h5 mb-1 fw-bold text-info" x-text="getVolumeRatio(currentDepth?.total_bid_volume, currentDepth?.total_ask_volume)"></div>
                                <div class="small text-secondary">Bid/Ask Ratio</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Market Insights -->
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Market Insight:</strong>
                            <span x-text="getMarketInsight(currentDepth)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <div class="small text-secondary mt-2">Loading market summary...</div>
    </div>

    <!-- No Data State -->
    <div x-show="!loading && !currentDepth" class="text-center py-4">
        <div class="text-secondary">
            <i class="fas fa-chart-line fa-2x mb-2"></i>
            <div>No market depth data available</div>
        </div>
    </div>
</div>

