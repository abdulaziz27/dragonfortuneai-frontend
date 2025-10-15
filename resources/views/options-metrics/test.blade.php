@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1 fw-semibold">Options Metrics API Test</h2>
                    <p class="text-muted mb-0">Test all 16 endpoints to see which return data</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="runTests()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                            <path d="M21 12a9 9 0 1 1-9-9c2.5 0 4.8 1 6.4 2.6M21 3v6h-6"/>
                        </svg>
                        Test All Endpoints
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">Endpoint Test Results</h5>
                </div>
                <div class="card-body">
                    <div id="testResults">
                        <div class="text-center text-muted">
                            <p>Click "Test All Endpoints" to start testing...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/options-metrics-test.js"></script>
<script>
async function runTests() {
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Testing endpoints...</p></div>';
    
    try {
        const results = await testAllEndpoints();
        displayResults(results);
    } catch (error) {
        resultsDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    }
}

function displayResults(results) {
    const resultsDiv = document.getElementById('testResults');
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>${results.working.length}</h3>
                        <p class="mb-0">Working with Data</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>${results.workingNoData.length}</h3>
                        <p class="mb-0">Working but No Data</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3>${results.errors.length}</h3>
                        <p class="mb-0">Errors</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="accordion" id="resultsAccordion">
    `;
    
    // Working endpoints
    if (results.working.length > 0) {
        html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#working">
                        <span class="badge bg-success me-2">${results.working.length}</span>
                        Working Endpoints (with data)
                    </button>
                </h2>
                <div id="working" class="accordion-collapse collapse show" data-bs-parent="#resultsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Endpoint</th>
                                        <th>Status</th>
                                        <th>Data Items</th>
                                        <th>Sample Data</th>
                                    </tr>
                                </thead>
                                <tbody>
        `;
        
        results.working.forEach(result => {
            html += `
                <tr>
                    <td><code>${result.path}</code></td>
                    <td><span class="badge bg-success">${result.status}</span></td>
                    <td>${result.dataLength}</td>
                    <td><small class="text-muted">${JSON.stringify(result).substring(0, 100)}...</small></td>
                </tr>
            `;
        });
        
        html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Working but no data
    if (results.workingNoData.length > 0) {
        html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#noData">
                        <span class="badge bg-warning me-2">${results.workingNoData.length}</span>
                        Working but No Data
                    </button>
                </h2>
                <div id="noData" class="accordion-collapse collapse" data-bs-parent="#resultsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Endpoint</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
        `;
        
        results.workingNoData.forEach(result => {
            html += `
                <tr>
                    <td><code>${result.path}</code></td>
                    <td><span class="badge bg-warning">${result.status}</span></td>
                    <td>Endpoint works but returns empty data</td>
                </tr>
            `;
        });
        
        html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Errors
    if (results.errors.length > 0) {
        html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#errors">
                        <span class="badge bg-danger me-2">${results.errors.length}</span>
                        Error Endpoints
                    </button>
                </h2>
                <div id="errors" class="accordion-collapse collapse" data-bs-parent="#resultsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Endpoint</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody>
        `;
        
        results.errors.forEach(result => {
            html += `
                <tr>
                    <td><code>${result.path}</code></td>
                    <td><span class="text-danger">${result.error}</span></td>
                </tr>
            `;
        });
        
        html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += `</div>`;
    
    resultsDiv.innerHTML = html;
}
</script>
@endsection
