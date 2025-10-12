# Design Document

## Overview

This design expands the existing onchain metrics functionality by adding three specialized pages that handle additional CryptoQuant API endpoints. The design follows the established Laravel Blade component architecture and Alpine.js patterns used throughout the application, ensuring consistency with existing modules like spot-microstructure and derivatives.

The expansion includes:
1. **Ethereum Metrics Page** - Network gas and staking analytics
2. **Exchange Metrics Page** - Reserve tracking and market indicators  
3. **Mining & Price Page** - MPI analysis and comprehensive price data

## Architecture

### Page Structure Pattern
Following the established pattern from existing modules:

```
/onchain-ethereum/
├── dashboard.blade.php (main page)
└── components/
    ├── network-gas-chart.blade.php
    ├── staking-deposits-chart.blade.php
    └── eth-summary-cards.blade.php

/onchain-exchange/
├── dashboard.blade.php (main page)
└── components/
    ├── exchange-reserves-chart.blade.php
    ├── market-indicators-chart.blade.php
    └── exchange-summary-cards.blade.php

/onchain-mining-price/
├── dashboard.blade.php (main page)
└── components/
    ├── miners-mpi-chart.blade.php
    ├── price-charts.blade.php
    └── mining-price-summary.blade.php
```

### JavaScript Controller Pattern
Each page will have a dedicated Alpine.js controller following the established pattern:

```javascript
// public/js/onchain-ethereum-controller.js
// public/js/onchain-exchange-controller.js  
// public/js/onchain-mining-price-controller.js
```

### Routing Structure
New routes will be added to `routes/web.php`:

```php
// Advanced On-Chain Metrics Routes
Route::view('/onchain-ethereum', 'onchain-ethereum.dashboard')->name('onchain-ethereum.dashboard');
Route::view('/onchain-exchange', 'onchain-exchange.dashboard')->name('onchain-exchange.dashboard');
Route::view('/onchain-mining-price', 'onchain-mining-price.dashboard')->name('onchain-mining-price.dashboard');
```

## Components and Interfaces

### 1. Ethereum Metrics Page Components

#### Network Gas Chart Component
- **File**: `resources/views/components/onchain-ethereum/network-gas-chart.blade.php`
- **Purpose**: Display gas limit, gas price, and gas usage trends
- **API Endpoints**: 
  - `/api/onchain/eth/network-gas`
  - `/api/onchain/eth/network-gas/summary`
- **Chart Type**: Multi-line time series with dual y-axis
- **Features**:
  - Configurable time windows (day, hour)
  - Data limit controls (up to 200 records)
  - Real-time updates every 30 seconds
  - Summary statistics panel

#### Staking Deposits Chart Component  
- **File**: `resources/views/components/onchain-ethereum/staking-deposits-chart.blade.php`
- **Purpose**: Visualize ETH 2.0 staking inflow trends
- **API Endpoints**:
  - `/api/onchain/eth/staking-deposits`
  - `/api/onchain/eth/staking-deposits/summary`
- **Chart Type**: Area chart with trend indicators
- **Features**:
  - 7-day, 30-day, and all-time averages
  - Momentum percentage calculations
  - Change indicators with percentage values

#### ETH Summary Cards Component
- **File**: `resources/views/components/onchain-ethereum/eth-summary-cards.blade.php`
- **Purpose**: Display key Ethereum metrics at a glance
- **Features**:
  - Current gas price with trend indicators
  - Total staking inflows with momentum
  - Network utilization percentage
  - Gas efficiency metrics

### 2. Exchange Metrics Page Components

#### Exchange Reserves Chart Component
- **File**: `resources/views/components/onchain-exchange/exchange-reserves-chart.blade.php`
- **Purpose**: Track exchange reserve balances over time
- **API Endpoints**:
  - `/api/onchain/exchange/reserves`
  - `/api/onchain/exchange/reserves/summary`
- **Chart Type**: Multi-line chart with asset/exchange filtering
- **Features**:
  - Asset selection (BTC, ETH, etc.)
  - Exchange filtering (Binance, OKX, etc.)
  - USD value overlay
  - Trend analysis with percentage changes

#### Market Indicators Chart Component
- **File**: `resources/views/components/onchain-exchange/market-indicators-chart.blade.php`
- **Purpose**: Display estimated leverage ratios and market gauges
- **API Endpoints**:
  - `/api/onchain/market/indicators`
- **Chart Type**: Line chart with threshold indicators
- **Features**:
  - Leverage ratio visualization
  - Risk zone highlighting
  - Exchange-specific indicators

#### Exchange Summary Cards Component
- **File**: `resources/views/components/onchain-exchange/exchange-summary-cards.blade.php`
- **Purpose**: Aggregate exchange data overview
- **Features**:
  - Total reserves across exchanges
  - Largest inflows/outflows
  - Exchange ranking by reserves
  - Trend indicators

### 3. Mining & Price Page Components

#### Miners MPI Chart Component
- **File**: `resources/views/components/onchain-mining-price/miners-mpi-chart.blade.php`
- **Purpose**: Visualize Miners Position Index with statistical analysis
- **API Endpoints**:
  - `/api/onchain/miners/mpi`
  - `/api/onchain/miners/mpi/summary`
- **Chart Type**: Line chart with statistical overlays
- **Features**:
  - Z-score indicators
  - Statistical bands (mean, std dev)
  - Extreme value highlighting
  - Asset filtering (BTC default)

#### Price Charts Component
- **File**: `resources/views/components/onchain-mining-price/price-charts.blade.php`
- **Purpose**: Comprehensive OHLCV data visualization
- **API Endpoints**:
  - `/api/onchain/price/ohlcv`
  - `/api/onchain/price/erc20`
  - `/api/onchain/price/stablecoin`
- **Chart Type**: Candlestick charts with volume
- **Features**:
  - Multi-asset support (BTC, ETH, ERC20 tokens)
  - Stablecoin price tracking
  - Volume analysis
  - Price correlation views

#### Mining Price Summary Component
- **File**: `resources/views/components/onchain-mining-price/mining-price-summary.blade.php`
- **Purpose**: Key metrics dashboard
- **Features**:
  - Current MPI value with interpretation
  - Price performance summaries
  - Mining profitability indicators
  - Market correlation metrics

## Data Models

### API Response Structures

#### Ethereum Network Gas Response
```typescript
interface NetworkGasResponse {
  data: {
    date: string;
    gas_limit_mean: number;
    gas_price_mean: number;
    gas_used_mean: number;
    gas_used_total: number;
    timestamp: number;
    window: string;
  }[];
}

interface NetworkGasSummary {
  data: {
    averages: {
      gas_limit_mean: number;
      gas_price_mean: number;
      gas_used_mean: number;
      gas_used_total: number;
    };
    change_pct: {
      gas_limit_mean: number;
      gas_price_mean: number;
      gas_used_mean: number;
      gas_used_total: number;
    };
    latest: NetworkGasData;
    window: string;
  };
}
```

#### Exchange Reserves Response
```typescript
interface ExchangeReservesResponse {
  data: {
    date: string;
    reserve: number;
    reserve_usd: number;
    timestamp: number;
    window: string;
  }[];
}

interface ExchangeReservesSummary {
  data: {
    asset: string;
    exchanges: {
      averages: {
        reserve: number;
        reserve_usd: number;
      };
      change: {
        absolute: number;
        percentage: number;
        usd: number;
      };
      exchange: string;
      latest: ExchangeReserveData;
      trend: string;
    }[];
    totals: {
      change: number;
      change_usd: number;
      latest_reserve: number;
      latest_reserve_usd: number;
    };
  };
}
```

#### Miners MPI Response
```typescript
interface MinersMPIResponse {
  data: {
    date: string;
    mpi: number;
    timestamp: number;
    window: string;
  }[];
}

interface MinersMPISummary {
  data: {
    asset: string;
    latest: {
      change: number;
      change_pct: number;
      date: string;
      mpi: number;
      timestamp: number;
    };
    stats: {
      average: number;
      max: number;
      median: number;
      min: number;
      observations: number;
      std_dev: number;
      z_score: number;
    };
    window: string;
  };
}
```

### Frontend Data Management

#### State Management Pattern
Each controller will maintain state following the established pattern:

```javascript
function onchainEthereumController() {
  return {
    // Global state
    loading: false,
    selectedWindow: 'day',
    selectedLimit: 200,
    
    // Component-specific state
    gasData: [],
    gasSummary: null,
    stakingData: [],
    stakingSummary: null,
    
    // Loading states
    loadingStates: {
      gas: false,
      staking: false
    },
    
    // Methods
    init() { /* initialization */ },
    refreshAll() { /* refresh all data */ },
    updateFilters() { /* handle filter changes */ }
  };
}
```

## Error Handling

### API Error Management
Following the established pattern from existing controllers:

```javascript
async function fetchWithErrorHandling(url, component) {
  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    return await response.json();
  } catch (error) {
    console.error(`❌ Error loading ${component}:`, error);
    // Show user-friendly error message
    this.showError(`Failed to load ${component} data. Please try again.`);
    return { data: [] };
  }
}
```

### User Feedback
- Loading spinners for each component
- Error messages with retry options
- Empty state handling with helpful messages
- Network connectivity indicators

## Testing Strategy

### Component Testing
1. **Unit Tests**: Test individual Alpine.js controller methods
2. **Integration Tests**: Verify API endpoint consumption
3. **UI Tests**: Ensure responsive design and chart rendering
4. **Performance Tests**: Validate with large datasets (200 records)

### API Testing
1. **Endpoint Validation**: Verify all 12 endpoints return expected data structures
2. **Parameter Testing**: Test window, limit, asset, and exchange filters
3. **Error Handling**: Test network failures and invalid responses
4. **Rate Limiting**: Ensure proper handling of API limits

### Browser Testing
1. **Cross-browser Compatibility**: Chrome, Firefox, Safari, Edge
2. **Responsive Design**: Mobile, tablet, desktop viewports
3. **Chart Performance**: Large dataset rendering
4. **Real-time Updates**: WebSocket or polling functionality

### Test Data Scenarios
1. **Normal Operations**: Standard API responses
2. **Edge Cases**: Empty datasets, extreme values
3. **Error Conditions**: Network failures, invalid parameters
4. **Performance**: Large datasets, concurrent requests

## Implementation Phases

### Phase 1: Foundation Setup
- Create directory structure
- Set up routing
- Create base page templates
- Implement basic navigation

### Phase 2: Ethereum Metrics Implementation
- Network gas chart component
- Staking deposits visualization
- Summary cards and statistics
- API integration and error handling

### Phase 3: Exchange Metrics Implementation  
- Exchange reserves tracking
- Market indicators visualization
- Multi-exchange comparison features
- Asset filtering capabilities

### Phase 4: Mining & Price Implementation
- MPI statistical analysis
- OHLCV price charts
- ERC20 and stablecoin support
- Correlation analysis features

### Phase 5: Integration & Polish
- Cross-page navigation
- Consistent styling and UX
- Performance optimization
- Comprehensive testing

### Phase 6: Documentation & Deployment
- User documentation
- API documentation updates
- Deployment procedures
- Monitoring and alerting setup