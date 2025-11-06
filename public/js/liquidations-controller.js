/**
 * Liquidations Controller Entry Point
 * Blueprint: Open Interest Controller Entry Point
 * 
 * Loads modular controller and initializes Alpine.js component
 */

import { createLiquidationsController } from './liquidations/controller-coinglass.js';

// Wait for Chart.js to be ready
await window.chartJsReady;

// Register Alpine.js component
window.liquidationsController = createLiquidationsController;

console.log('✅ Liquidations controller loaded and ready');
