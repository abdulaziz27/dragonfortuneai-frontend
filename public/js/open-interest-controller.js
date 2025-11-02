/**
 * Open Interest Controller - Entry Point
 * Thin wrapper to expose the modular controller to Alpine.js
 */

import { createOpenInterestController } from './open-interest/controller.js';

// Expose to global scope for Alpine.js
window.openInterestController = createOpenInterestController;

