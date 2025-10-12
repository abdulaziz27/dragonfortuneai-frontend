{{-- Book Pressure History Table Component --}}
<div class="df-panel p-3" x-data="bookPressureTable()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">📈 Book Pressure History</h5>
            <small class="text-secondary">Recent pressure data</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-outline-primary" @click="loadData()" :disabled="loading">
                <span x-show="!loading">🔄 Refresh</span>
                <span x-show="loading" class="spinner-border spinner-border-sm"></span>
            </button>
        </div>
    </div>

    <!-- Book Pressure Table -->
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-striped">
            <thead class="sticky-top bg-white">
                <tr>
                    <th>Time</th>
                    <th>Exchange</th>
                    <th>Symbol</th>
                    <th class="text-end">Bid Pressure</th>
                    <th class="text-end">Ask Pressure</th>
                    <th class="text-end">Ratio</th>
                    <th class="text-center">Direction</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in pressureData" :key="'pressure-' + index + '-' + item.timestamp">
                    <tr>
                        <td x-text="formatTime(item.timestamp)">--</td>
                        <td>
                            <span class="badge bg-secondary" x-text="item.exchange">--</span>
                        </td>
                        <td x-text="item.symbol">--</td>
                        <td class="text-end text-success fw-bold" x-text="formatPressure(item.bid_pressure)">--</td>
                        <td class="text-end text-danger fw-bold" x-text="formatPressure(item.ask_pressure)">--</td>
                        <td class="text-end fw-bold" x-text="formatRatio(item.pressure_ratio)">--</td>
                        <td class="text-center">
                            <span class="badge" :class="getDirectionClass(item.pressure_direction)" x-text="item.pressure_direction?.toUpperCase()">--</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Summary Stats -->
    <div class="mt-3 pt-3 border-top">
        <div class="row g-2 small">
            <div class="col-3">
                <div class="text-secondary">Data Points</div>
                <div class="fw-bold" x-text="pressureData.length">--</div>
            </div>
            <div class="col-3">
                <div class="text-secondary">Avg Bid Pressure</div>
                <div class="fw-bold text-success" x-text="formatPressure(avgBidPressure)">--</div>
            </div>
            <div class="col-3">
                <div class="text-secondary">Avg Ask Pressure</div>
                <div class="fw-bold text-danger" x-text="formatPressure(avgAskPressure)">--</div>
            </div>
            <div class="col-3">
                <div class="text-secondary">Avg Ratio</div>
                <div class="fw-bold" x-text="formatRatio(avgRatio)">--</div>
            </div>
        </div>
    </div>

    <!-- No Data State -->
    <div x-show="!loading && pressureData.length === 0" class="text-center py-4">
        <div class="text-secondary mb-2" style="font-size: 3rem;">📈</div>
        <div class="text-secondary">No book pressure data available</div>
        <div class="small text-muted mt-2">Try refreshing the data</div>
    </div>
</div>

