/**
 * Long Short Ratio Controller - Entry Point
 * 
 * Modular implementation with:
 * - Hybrid API approach (internal + external)
 * - Smart auto-refresh (5 seconds)
 * - Production-ready error handling
 * - Clean separation of concerns
 * 
 * Architecture:
 * - api-service.js: Data fetching (internal + external APIs)
 * - chart-manager.js: Chart operations
 * - utils.js: Helper functions
 * - controller.js: Main logic
 */

import { createLongShortRatioController } from './long-short-ratio/controller.js';

// Create controller function for Alpine.js
function longShortRatioController() {
    return createLongShortRatioController();
}

// Export for Alpine.js
window.longShortRatioController = longShortRatioController;

console.log('✅ Long Short Ratio Controller loaded (Modular ES6)');

