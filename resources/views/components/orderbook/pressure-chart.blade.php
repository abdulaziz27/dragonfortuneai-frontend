{{-- Book Pressure Chart Component --}}
<div class="df-panel p-3" x-data="bookPressureChart()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">📈 Book Pressure History</h5>
        <span class="badge bg-secondary" x-show="loading">Loading...</span>
    </div>

    <canvas id="bookPressureChart" height="80"></canvas>

    <div class="mt-2 small text-secondary" x-show="!loading && dataPoints > 0">
        Showing last <span x-text="dataPoints"></span> data points
    </div>
    <div class="mt-2 text-center text-secondary" x-show="!loading && dataPoints === 0">
        No book pressure data available
    </div>
</div>

