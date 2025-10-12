# Requirements Document

## Introduction

This feature expands the existing onchain metrics functionality by adding three new specialized pages to handle additional CryptoQuant API endpoints. The expansion includes Ethereum-specific metrics, exchange reserves data, and mining/price analytics. This follows the established pattern of separating complex functionality into focused, domain-specific pages similar to the spot-microstructure and derivatives modules.

## Requirements

### Requirement 1

**User Story:** As a crypto trader, I want to access Ethereum network metrics (gas fees and staking data) in a dedicated page, so that I can analyze Ethereum network health and staking trends separately from other onchain metrics.

#### Acceptance Criteria

1. WHEN a user navigates to the Ethereum metrics page THEN the system SHALL display network gas metrics including gas limit, gas price, and gas usage data
2. WHEN a user views the Ethereum page THEN the system SHALL show staking deposits data with historical trends and summaries
3. WHEN gas metrics are loaded THEN the system SHALL fetch data from `/api/onchain/eth/network-gas` and `/api/onchain/eth/network-gas/summary` endpoints
4. WHEN staking data is requested THEN the system SHALL retrieve information from `/api/onchain/eth/staking-deposits` and `/api/onchain/eth/staking-deposits/summary` endpoints
5. WHEN data is displayed THEN the system SHALL show both detailed historical charts and summary statistics
6. WHEN the page loads THEN the system SHALL support configurable time windows (day, hour) and data limits up to 200 records

### Requirement 2

**User Story:** As a crypto analyst, I want to monitor exchange reserves and market indicators in a dedicated interface, so that I can track institutional flows and market leverage ratios effectively.

#### Acceptance Criteria

1. WHEN a user accesses the exchange metrics page THEN the system SHALL display exchange reserve data for multiple assets and exchanges
2. WHEN reserve data is requested THEN the system SHALL fetch from `/api/onchain/exchange/reserves` and `/api/onchain/exchange/reserves/summary` endpoints
3. WHEN market indicators are needed THEN the system SHALL retrieve data from `/api/onchain/market/indicators` endpoint
4. WHEN displaying reserves THEN the system SHALL support filtering by asset (BTC default) and exchange (binance default)
5. WHEN showing market data THEN the system SHALL include estimated leverage ratios and exchange-specific gauges
6. WHEN summary data is presented THEN the system SHALL show aggregated balances across multiple exchanges with trend analysis

### Requirement 3

**User Story:** As a mining analyst, I want to access mining position indices and comprehensive price data in one location, so that I can correlate miner behavior with market price movements.

#### Acceptance Criteria

1. WHEN a user visits the mining/price page THEN the system SHALL display Miners Position Index (MPI) data with statistical analysis
2. WHEN MPI data is loaded THEN the system SHALL fetch from `/api/onchain/miners/mpi` and `/api/onchain/miners/mpi/summary` endpoints
3. WHEN price data is requested THEN the system SHALL retrieve OHLCV data from `/api/onchain/price/ohlcv` for major assets
4. WHEN ERC20 token prices are needed THEN the system SHALL fetch from `/api/onchain/price/erc20` endpoint
5. WHEN stablecoin data is required THEN the system SHALL access `/api/onchain/price/stablecoin` endpoint
6. WHEN displaying MPI THEN the system SHALL show averages, extremes, z-scores, and latest changes
7. WHEN showing price data THEN the system SHALL support multiple assets (BTC, ETH) and ERC20 tokens (LINK, MATIC, UNI)

### Requirement 4

**User Story:** As a platform user, I want consistent navigation and UI patterns across all onchain metric pages, so that I can efficiently switch between different types of analysis without learning new interfaces.

#### Acceptance Criteria

1. WHEN new pages are created THEN the system SHALL follow the existing UI patterns from sentiment-flow, spot-microstructure and derivatives modules
2. WHEN navigation is updated THEN the system SHALL add new menu items to the sidebar under an "Advanced Onchain" or similar section
3. WHEN components are built THEN the system SHALL reuse existing chart libraries and styling patterns
4. WHEN data is displayed THEN the system SHALL maintain consistent loading states, error handling, and responsive design
5. WHEN filters are implemented THEN the system SHALL use similar control patterns as existing pages
6. WHEN API calls are made THEN the system SHALL implement proper error handling and retry mechanisms

### Requirement 5

**User Story:** As a system administrator, I want the new pages to be performant and maintainable, so that they don't impact overall application performance and can be easily updated.

#### Acceptance Criteria

1. WHEN pages are implemented THEN the system SHALL use efficient data fetching patterns to avoid overloading the API
2. WHEN components are created THEN the system SHALL follow Laravel Blade component architecture
3. WHEN JavaScript controllers are built THEN the system SHALL follow the established Alpine.js patterns
4. WHEN API responses are processed THEN the system SHALL implement proper data caching where appropriate
5. WHEN charts are rendered THEN the system SHALL optimize for performance with large datasets (up to 200 records)
6. WHEN errors occur THEN the system SHALL provide meaningful feedback to users and log issues appropriately