/**
 * Options Metrics Endpoint Test Script
 * 
 * Test all 16 endpoints to see which return data vs empty/error
 * Only implement working endpoints in the main integration
 */

// API base URL will be read from meta tag

// All 16 endpoints to test
const ENDPOINTS = [
    // IV endpoints
    { name: 'IV Smile', path: '/api/options-metrics/iv/smile', params: { exchange: 'Deribit', underlying: 'BTC', tenor: '30D' } },
    { name: 'IV Surface', path: '/api/options-metrics/iv/surface', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'IV Term Structure', path: '/api/options-metrics/iv/term-structure', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'IV Timeseries', path: '/api/options-metrics/iv/timeseries', params: { exchange: 'Deribit', underlying: 'BTC', tenor: '30D' } },
    { name: 'IV Summary', path: '/api/options-metrics/iv/summary', params: { exchange: 'Deribit', underlying: 'BTC' } },
    
    // Skew endpoints
    { name: 'Skew History', path: '/api/options-metrics/skew/history', params: { exchange: 'Deribit', underlying: 'BTC', tenor: '30D' } },
    { name: 'Skew Summary', path: '/api/options-metrics/skew/summary', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'Skew Heatmap', path: '/api/options-metrics/skew/heatmap', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'Skew Regime', path: '/api/options-metrics/skew/regime', params: { exchange: 'Deribit', underlying: 'BTC' } },
    
    // OI endpoints
    { name: 'OI Strike', path: '/api/options-metrics/oi/strike', params: { exchange: 'Deribit', underlying: 'BTC', expiry: '2024-03-29' } },
    { name: 'OI Expiry', path: '/api/options-metrics/oi/expiry', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'OI Timeseries', path: '/api/options-metrics/oi/timeseries', params: { exchange: 'Deribit', underlying: 'BTC', expiry: '2024-03-29' } },
    { name: 'OI Summary', path: '/api/options-metrics/oi/summary', params: { exchange: 'Deribit', underlying: 'BTC' } },
    
    // Dealer Greeks endpoints
    { name: 'Dealer Greeks GEX', path: '/api/options-metrics/dealer-greeks/gex', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'Dealer Greeks Summary', path: '/api/options-metrics/dealer-greeks/summary', params: { exchange: 'Deribit', underlying: 'BTC' } },
    { name: 'Dealer Greeks Timeline', path: '/api/options-metrics/dealer-greeks/timeline', params: { exchange: 'Deribit', underlying: 'BTC' } }
];

async function testEndpoint(endpoint) {
    try {
        const baseUrl = document.querySelector('meta[name="api-base-url"]')?.content || 'https://test.dragonfortune.ai';
        const url = new URL(baseUrl + endpoint.path);
        Object.keys(endpoint.params).forEach(key => {
            if (endpoint.params[key]) {
                url.searchParams.append(key, endpoint.params[key]);
            }
        });
        
        console.log(`Testing ${endpoint.name}: ${url.toString()}`);
        
        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        return {
            name: endpoint.name,
            path: endpoint.path,
            status: response.status,
            success: response.ok,
            hasData: data && (Array.isArray(data) ? data.length > 0 : Object.keys(data).length > 0),
            dataLength: Array.isArray(data) ? data.length : (data ? Object.keys(data).length : 0),
            error: response.ok ? null : data
        };
        
    } catch (error) {
        return {
            name: endpoint.name,
            path: endpoint.path,
            status: 'ERROR',
            success: false,
            hasData: false,
            dataLength: 0,
            error: error.message
        };
    }
}

async function testAllEndpoints() {
    console.log('🧪 Testing all 16 Options Metrics endpoints...');
    console.log('='.repeat(60));
    
    const results = [];
    
    for (const endpoint of ENDPOINTS) {
        const result = await testEndpoint(endpoint);
        results.push(result);
        
        const status = result.success ? '✅' : '❌';
        const dataInfo = result.hasData ? `(${result.dataLength} items)` : '(no data)';
        
        console.log(`${status} ${result.name}: ${result.status} ${dataInfo}`);
        
        if (result.error) {
            console.log(`   Error: ${result.error}`);
        }
        
        // Small delay between requests
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    
    console.log('='.repeat(60));
    console.log('📊 SUMMARY:');
    
    const working = results.filter(r => r.success && r.hasData);
    const workingNoData = results.filter(r => r.success && !r.hasData);
    const errors = results.filter(r => !r.success);
    
    console.log(`✅ Working with data: ${working.length}/16`);
    working.forEach(r => console.log(`   - ${r.name}`));
    
    console.log(`⚠️  Working but no data: ${workingNoData.length}/16`);
    workingNoData.forEach(r => console.log(`   - ${r.name}`));
    
    console.log(`❌ Errors: ${errors.length}/16`);
    errors.forEach(r => console.log(`   - ${r.name}: ${r.error}`));
    
    return {
        working,
        workingNoData,
        errors,
        all: results
    };
}

// Auto-run test when script loads
if (typeof window !== 'undefined') {
    window.testOptionsEndpoints = testAllEndpoints;
    console.log('🔧 Options Metrics Test Script loaded. Run testOptionsEndpoints() to test all endpoints.');
}
