{{-- Market Depth Table Component --}}
<div class="df-panel p-3" x-data="marketDepthTable()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">📊 Market Depth History</h5>
        <span class="badge bg-secondary" x-show="loading">Loading...</span>
    </div>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-hover">
            <thead class="sticky-top bg-dark">
                <tr>
                    <th>Time</th>
                    <th>Bid Levels</th>
                    <th>Ask Levels</th>
                    <th>Bid Volume</th>
                    <th>Ask Volume</th>
                    <th>Depth Score</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="!loading && depths.length > 0">
                    <template x-for="depth in depths" :key="depth.timestamp">
                        <tr>
                            <td class="small" x-text="formatTime(depth.timestamp)"></td>
                            <td class="small text-success" x-text="depth.bid_levels"></td>
                            <td class="small text-danger" x-text="depth.ask_levels"></td>
                            <td class="small text-success" x-text="formatVolume(depth.total_bid_volume)"></td>
                            <td class="small text-danger" x-text="formatVolume(depth.total_ask_volume)"></td>
                            <td class="small" x-text="depth.depth_score.toFixed(2)"></td>
                        </tr>
                    </template>
                </template>
                <template x-if="!loading && depths.length === 0">
                    <tr>
                        <td colspan="6" class="text-center text-secondary">No market depth data available</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

