/**
 * Perp-Quarterly Spread Controller - Entry Point
 * 
 * Modular implementation with:
 * - Direct API calls to internal API
 * - Smart auto-refresh (5 seconds)
 * - Production-ready error handling
 * - Clean separation of concerns
 * 
 * Architecture:
 * - api-service.js: Data fetching
 * - chart-manager.js: Chart operations
 * - utils.js: Helper functions
 * - controller.js: Main logic
 */

import { createPerpQuarterlyController } from './perp-quarterly/controller.js';

// Create controller function for Alpine.js
function perpQuarterlySpreadController() {
    return createPerpQuarterlyController();
}

// Export for Alpine.js
window.perpQuarterlySpreadController = perpQuarterlySpreadController;

console.log('✅ Perp-Quarterly Spread Controller loaded (Modular ES6)');

