/**
 * OnChain Test Helper
 * Provides end-to-end testing utilities for onchain metrics pages
 */

window.OnChainTestHelper = {
    // Test results storage
    testResults: {
        pages: {},
        apis: {},
        charts: {},
        overall: {
            passed: 0,
            failed: 0,
            total: 0
        }
    },
    
    // Test configuration
    testConfig: {
        timeout: 10000,
        retries: 2,
        endpoints: [
            '/api/onchain/eth/network-gas',
            '/api/onchain/eth/network-gas/summary',
            '/api/onchain/eth/staking-deposits',
            '/api/onchain/eth/staking-deposits/summary',
            '/api/onchain/exchange/reserves',
            '/api/onchain/exchange/reserves/summary',
            '/api/onchain/market/indicators',
            '/api/onchain/mining/mpi',
            '/api/onchain/mining/mpi/summary',
            '/api/onchain/price/ohlcv',
            '/api/onchain/price/ohlcv/summary'
        ]
    },
    
    // Run comprehensive test suite
    async runFullTestSuite() {
        console.log('🧪 Starting OnChain Metrics Test Suite...');
        this.resetResults();
        
        try {
            // Test API endpoints
            await this.testAllEndpoints();
            
            // Test page functionality
            await this.testPageFunctionality();
            
            // Test chart rendering
            await this.testChartRendering();
            
            // Test responsive design
            await this.testResponsiveDesign();
            
            // Generate report
            this.generateTestReport();
            
        } catch (error) {
            console.error('❌ Test suite failed:', error);
        }
        
        return this.testResults;
    },
    
    // Test all API endpoints
    async testAllEndpoints() {
        console.log('🔗 Testing API endpoints...');
        
        for (const endpoint of this.testConfig.endpoints) {
            await this.testEndpoint(endpoint);
        }
    },
    
    // Test individual endpoint
    async testEndpoint(endpoint, params = {}) {
        const testName = `API: ${endpoint}`;
        console.log(`Testing ${testName}...`);
        
        try {
            const url = this.buildTestUrl(endpoint, params);
            const startTime = performance.now();
            
            const response = await fetch(url);
            const endTime = performance.now();
            const responseTime = endTime - startTime;
            
            const result = {
                endpoint,
                status: response.status,
                responseTime: Math.round(responseTime),
                success: response.ok,
                timestamp: new Date().toISOString()
            };
            
            if (response.ok) {
                const data = await response.json();
                result.dataStructure = this.analyzeDataStructure(data);
                result.recordCount = this.getRecordCount(data);
                
                this.recordTestPass(testName, result);
                console.log(`✅ ${testName} - ${result.recordCount} records in ${result.responseTime}ms`);
            } else {
                result.error = `HTTP ${response.status}: ${response.statusText}`;
                this.recordTestFail(testName, result);
                console.log(`❌ ${testName} - ${result.error}`);
            }
            
            this.testResults.apis[endpoint] = result;
            
        } catch (error) {
            const result = {
                endpoint,
                success: false,
                error: error.message,
                timestamp: new Date().toISOString()
            };
            
            this.recordTestFail(testName, result);
            this.testResults.apis[endpoint] = result;
            console.log(`❌ ${testName} - ${error.message}`);
        }
    },
    
    // Test page functionality
    async testPageFunctionality() {
        console.log('📄 Testing page functionality...');
        
        const pages = [
            { name: 'Ethereum Network', path: '/onchain-ethereum' },
            { name: 'Exchange Reserves', path: '/onchain-exchange' },
            { name: 'Mining & Price', path: '/onchain-mining-price' }
        ];
        
        for (const page of pages) {
            await this.testPage(page);
        }
    },
    
    // Test individual page
    async testPage(page) {
        const testName = `Page: ${page.name}`;
        console.log(`Testing ${testName}...`);
        
        try {
            const result = {
                name: page.name,
                path: page.path,
                elements: {},
                functionality: {},
                success: true,
                timestamp: new Date().toISOString()
            };
            
            // Test if we're on the correct page
            if (window.location.pathname === page.path) {
                // Test page elements
                result.elements = await this.testPageElements(page);
                
                // Test page functionality
                result.functionality = await this.testPageInteractions(page);
                
                // Overall success
                result.success = Object.values(result.elements).every(test => test.success) &&
                                Object.values(result.functionality).every(test => test.success);
            } else {
                result.skipped = true;
                result.reason = 'Not on target page';
            }
            
            this.testResults.pages[page.name] = result;
            
            if (result.success) {
                this.recordTestPass(testName, result);
                console.log(`✅ ${testName} - All tests passed`);
            } else {
                this.recordTestFail(testName, result);
                console.log(`❌ ${testName} - Some tests failed`);
            }
            
        } catch (error) {
            const result = {
                name: page.name,
                success: false,
                error: error.message,
                timestamp: new Date().toISOString()
            };
            
            this.recordTestFail(testName, result);
            this.testResults.pages[page.name] = result;
            console.log(`❌ ${testName} - ${error.message}`);
        }
    },
    
    // Test page elements
    async testPageElements(page) {
        const elements = {};
        
        // Test common elements
        elements.header = {
            selector: '.derivatives-header h1',
            exists: !!document.querySelector('.derivatives-header h1'),
            success: !!document.querySelector('.derivatives-header h1')
        };
        
        elements.controls = {
            selector: 'select.form-select',
            exists: document.querySelectorAll('select.form-select').length > 0,
            count: document.querySelectorAll('select.form-select').length,
            success: document.querySelectorAll('select.form-select').length > 0
        };
        
        elements.charts = {
            selector: 'canvas',
            exists: document.querySelectorAll('canvas').length > 0,
            count: document.querySelectorAll('canvas').length,
            success: document.querySelectorAll('canvas').length > 0
        };
        
        elements.summaryCards = {
            selector: '.card',
            exists: document.querySelectorAll('.card').length > 0,
            count: document.querySelectorAll('.card').length,
            success: document.querySelectorAll('.card').length > 0
        };
        
        return elements;
    },
    
    // Test page interactions
    async testPageInteractions(page) {
        const interactions = {};
        
        // Test refresh button
        const refreshButton = document.querySelector('button[onclick*="refresh"], button[x-on\\:click*="refresh"]');
        interactions.refreshButton = {
            exists: !!refreshButton,
            clickable: refreshButton ? !refreshButton.disabled : false,
            success: !!refreshButton && !refreshButton.disabled
        };
        
        // Test dropdown interactions
        const dropdowns = document.querySelectorAll('select.form-select');
        interactions.dropdowns = {
            count: dropdowns.length,
            functional: Array.from(dropdowns).every(dropdown => !dropdown.disabled),
            success: dropdowns.length > 0 && Array.from(dropdowns).every(dropdown => !dropdown.disabled)
        };
        
        return interactions;
    },
    
    // Test chart rendering
    async testChartRendering() {
        console.log('📊 Testing chart rendering...');
        
        const charts = Chart.instances;
        const chartTests = {};
        
        Object.entries(charts).forEach(([id, chart]) => {
            const testName = `Chart: ${id}`;
            
            try {
                const result = {
                    id,
                    type: chart.config.type,
                    datasets: chart.data.datasets.length,
                    dataPoints: this.getChartDataPoints(chart),
                    rendered: !!chart.canvas,
                    responsive: chart.options.responsive,
                    success: true
                };
                
                // Check if chart has data
                if (result.dataPoints === 0) {
                    result.success = false;
                    result.warning = 'No data points';
                }
                
                // Check if chart is visible
                const canvas = chart.canvas;
                if (canvas) {
                    const rect = canvas.getBoundingClientRect();
                    result.visible = rect.width > 0 && rect.height > 0;
                    result.dimensions = { width: rect.width, height: rect.height };
                    
                    if (!result.visible) {
                        result.success = false;
                        result.warning = 'Chart not visible';
                    }
                }
                
                chartTests[id] = result;
                
                if (result.success) {
                    this.recordTestPass(testName, result);
                    console.log(`✅ ${testName} - ${result.dataPoints} points, ${result.datasets} datasets`);
                } else {
                    this.recordTestFail(testName, result);
                    console.log(`❌ ${testName} - ${result.warning || 'Failed'}`);
                }
                
            } catch (error) {
                const result = {
                    id,
                    success: false,
                    error: error.message
                };
                
                chartTests[id] = result;
                this.recordTestFail(testName, result);
                console.log(`❌ ${testName} - ${error.message}`);
            }
        });
        
        this.testResults.charts = chartTests;
    },
    
    // Test responsive design
    async testResponsiveDesign() {
        console.log('📱 Testing responsive design...');
        
        const breakpoints = [
            { name: 'Mobile', width: 375 },
            { name: 'Tablet', width: 768 },
            { name: 'Desktop', width: 1200 }
        ];
        
        const originalWidth = window.innerWidth;
        
        for (const breakpoint of breakpoints) {
            // Note: This is a simulation - actual responsive testing would require browser automation
            console.log(`Testing ${breakpoint.name} (${breakpoint.width}px)...`);
            
            // Check if elements are responsive
            const elements = document.querySelectorAll('.card, .chart-container, canvas');
            let responsiveElements = 0;
            
            elements.forEach(element => {
                const styles = window.getComputedStyle(element);
                if (styles.maxWidth === '100%' || styles.width.includes('%')) {
                    responsiveElements++;
                }
            });
            
            const result = {
                breakpoint: breakpoint.name,
                width: breakpoint.width,
                responsiveElements,
                totalElements: elements.length,
                percentage: Math.round((responsiveElements / elements.length) * 100),
                success: responsiveElements / elements.length > 0.8 // 80% should be responsive
            };
            
            if (result.success) {
                console.log(`✅ ${breakpoint.name} - ${result.percentage}% responsive elements`);
            } else {
                console.log(`❌ ${breakpoint.name} - Only ${result.percentage}% responsive elements`);
            }
        }
    },
    
    // Helper methods
    buildTestUrl(endpoint, params = {}) {
        const baseMeta = document.querySelector('meta[name="api-base-url"]');
        const baseUrl = baseMeta?.content || '';
        const url = new URL(endpoint, baseUrl || window.location.origin);
        
        // Add default test parameters
        const testParams = {
            window: 'day',
            limit: '50',
            ...params
        };
        
        Object.entries(testParams).forEach(([key, value]) => {
            url.searchParams.append(key, value);
        });
        
        return url.toString();
    },
    
    analyzeDataStructure(data) {
        if (!data) return { type: 'null' };
        
        const structure = {
            type: Array.isArray(data) ? 'array' : typeof data,
            hasData: !!data.data,
            dataType: data.data ? (Array.isArray(data.data) ? 'array' : typeof data.data) : null
        };
        
        if (data.data && Array.isArray(data.data) && data.data.length > 0) {
            structure.sampleKeys = Object.keys(data.data[0]);
        }
        
        return structure;
    },
    
    getRecordCount(data) {
        if (data && data.data && Array.isArray(data.data)) {
            return data.data.length;
        }
        return 0;
    },
    
    getChartDataPoints(chart) {
        if (!chart.data || !chart.data.datasets) return 0;
        
        return chart.data.datasets.reduce((total, dataset) => {
            return total + (dataset.data ? dataset.data.length : 0);
        }, 0);
    },
    
    recordTestPass(testName, result) {
        this.testResults.overall.passed++;
        this.testResults.overall.total++;
    },
    
    recordTestFail(testName, result) {
        this.testResults.overall.failed++;
        this.testResults.overall.total++;
    },
    
    resetResults() {
        this.testResults = {
            pages: {},
            apis: {},
            charts: {},
            overall: {
                passed: 0,
                failed: 0,
                total: 0
            }
        };
    },
    
    // Generate comprehensive test report
    generateTestReport() {
        const { overall, pages, apis, charts } = this.testResults;
        const successRate = overall.total > 0 ? Math.round((overall.passed / overall.total) * 100) : 0;
        
        console.log('\n📋 OnChain Metrics Test Report');
        console.log('================================');
        console.log(`Overall: ${overall.passed}/${overall.total} tests passed (${successRate}%)`);
        
        // API Tests Summary
        const apiTests = Object.values(apis);
        const apiPassed = apiTests.filter(test => test.success).length;
        console.log(`\n🔗 API Tests: ${apiPassed}/${apiTests.length} passed`);
        
        // Page Tests Summary
        const pageTests = Object.values(pages);
        const pagePassed = pageTests.filter(test => test.success).length;
        console.log(`📄 Page Tests: ${pagePassed}/${pageTests.length} passed`);
        
        // Chart Tests Summary
        const chartTests = Object.values(charts);
        const chartPassed = chartTests.filter(test => test.success).length;
        console.log(`📊 Chart Tests: ${chartPassed}/${chartTests.length} passed`);
        
        // Recommendations
        console.log('\n💡 Recommendations:');
        if (successRate < 80) {
            console.log('- Overall success rate is below 80%. Review failed tests.');
        }
        if (apiPassed < apiTests.length) {
            console.log('- Some API endpoints are failing. Check server status.');
        }
        if (chartPassed < chartTests.length) {
            console.log('- Some charts are not rendering properly. Check data and Chart.js setup.');
        }
        
        return this.testResults;
    },
    
    // Export test results
    exportResults() {
        const results = {
            ...this.testResults,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href
        };
        
        const blob = new Blob([JSON.stringify(results, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `onchain-test-results-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
    }
};

// Add global test function for easy access
window.runOnChainTests = () => OnChainTestHelper.runFullTestSuite();