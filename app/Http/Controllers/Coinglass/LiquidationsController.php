<?php

namespace App\Http\Controllers\Coinglass;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\CoinglassClient;

class LiquidationsController extends Controller
{
    private CoinglassClient $client;
    private int $cacheTtlSeconds;

    public function __construct(CoinglassClient $client)
    {
        $this->client = $client;
        $this->cacheTtlSeconds = (int) env('COINGLASS_LIQUIDATIONS_CACHE_TTL', 10);
    }

    /**
     * GET /api/coinglass/liquidation/aggregated-heatmap/model3
     * 
     * Fetch liquidation heatmap data (Model 3)
     * 
     * Query params:
     * - symbol: string (required) - Trading symbol (e.g., BTC)
     * - range: string (required) - Time range (12h, 24h, 3d, 7d, 30d, 90d, 180d, 1y)
     */
    public function heatmapModel3(Request $request)
    {
        $symbol = $this->toCoinglassSymbol($request->query('symbol', 'BTC'));
        $range = $request->query('range', '3d');

        // Validate range
        $validRanges = ['12h', '24h', '3d', '7d', '30d', '90d', '180d', '1y'];
        if (!in_array($range, $validRanges)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 400,
                    'message' => 'Invalid range. Supported: ' . implode(', ', $validRanges)
                ]
            ], 400);
        }

        $cacheKey = sprintf('coinglass:liquidations:heatmap:model3:%s:%s', $symbol, $range);

        $raw = Cache::remember($cacheKey, $this->cacheTtlSeconds, function () use ($symbol, $range) {
            $query = [
                'symbol' => $symbol,
                'range' => $range,
            ];

            return $this->client->get('/futures/liquidation/aggregated-heatmap/model3', $query);
        });

        $normalized = $this->normalizeHeatmapData($raw);
        return response()->json($normalized);
    }

    /**
     * Normalize heatmap data from Coinglass API
     */
    private function normalizeHeatmapData($raw): array
    {
        // Check for API error
        if (!is_array($raw) || (isset($raw['success']) && $raw['success'] === false)) {
            return [
                'success' => false,
                'error' => $raw['error'] ?? ['code' => 500, 'message' => 'Unknown error']
            ];
        }

        // Check for Coinglass error code
        if (isset($raw['code']) && $raw['code'] !== '0' && $raw['code'] !== 0) {
            return [
                'success' => false,
                'error' => [
                    'code' => $raw['code'],
                    'message' => $raw['msg'] ?? $raw['message'] ?? 'API error'
                ]
            ];
        }

        // Extract data
        $data = $raw['data'] ?? [];
        
        if (empty($data)) {
            return [
                'success' => false,
                'error' => ['code' => 404, 'message' => 'No data available']
            ];
        }

        // Return normalized structure
        return [
            'success' => true,
            'data' => [
                'y_axis' => $data['y_axis'] ?? [],
                'liquidation_leverage_data' => $data['liquidation_leverage_data'] ?? [],
                'price_candlesticks' => $data['price_candlesticks'] ?? [],
                'update_time' => $data['update_time'] ?? time(),
                'timestamp' => time()
            ]
        ];
    }

    /**
     * Convert symbol to Coinglass format (remove quote currency)
     */
    private function toCoinglassSymbol(?string $symbol): ?string
    {
        if (!$symbol) return $symbol;
        
        $s = strtoupper($symbol);
        
        // Remove common quote currencies
        foreach (['USDT', 'USDC', 'BUSD', 'USD'] as $quote) {
            if (str_ends_with($s, $quote)) {
                return substr($s, 0, -strlen($quote));
            }
        }
        
        // Handle underscore format (BTC_USDT -> BTC)
        if (str_contains($s, '_')) {
            return explode('_', $s)[0];
        }
        
        return $s;
    }
}
