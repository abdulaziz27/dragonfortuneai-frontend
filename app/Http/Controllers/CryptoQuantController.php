<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoQuantController extends Controller
{
    private $apiKey = 'jED5yIBUPyzpeRTodjcSPGiltvvdAaJQmV1op1ED3v4UkDorgm6O20rRTq3yKWloyebmxw';
    private $baseUrl = 'https://api.cryptoquant.com/v1';

    /**
     * Get Exchange Inflow CDD data from CryptoQuant API
     */
    public function getExchangeInflowCDD(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));
            
            // Convert date format from YYYY-MM-DD to YYYYMMDD for CryptoQuant API
            $fromDate = str_replace('-', '', $startDate);
            
            // Calculate limit based on date range (days between start and end)
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $daysDiff = $start->diffInDays($end) + 1;
            $limit = max($daysDiff, 100); // At least 100, or the number of days
            
            $url = "{$this->baseUrl}/btc/flow-indicator/exchange-inflow-cdd";
            
            Log::info('CryptoQuant Request', [
                'url' => $url,
                'from' => $fromDate,
                'limit' => $limit,
                'original_start' => $startDate,
                'original_end' => $endDate
            ]);
            
            // Get exchange parameter from request, default to 'binance'
            $exchange = $request->input('exchange', 'binance');
            
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/json',
            ])->get($url, [
                'exchange' => $exchange,
                'window' => 'day',
                'from' => $fromDate,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('CryptoQuant Response Success', [
                    'data_count' => isset($data['result']['data']) ? count($data['result']['data']) : 0,
                    'sample_data' => isset($data['result']['data'][0]) ? $data['result']['data'][0] : null
                ]);
                
                $transformedData = [];
                if (isset($data['result']['data']) && is_array($data['result']['data'])) {
                    // Filter data to only include dates within the requested range
                    $startTimestamp = strtotime($startDate);
                    $endTimestamp = strtotime($endDate);
                    
                    foreach ($data['result']['data'] as $item) {
                        $itemDate = $item['date'] ?? null;
                        if ($itemDate) {
                            $itemTimestamp = strtotime($itemDate);
                            // Only include if within range
                            if ($itemTimestamp >= $startTimestamp && $itemTimestamp <= $endTimestamp) {
                                $transformedData[] = [
                                    'date' => $itemDate,
                                    'value' => $item['inflow_cdd'] ?? 0
                                ];
                            }
                        }
                    }
                    
                    // Sort by date ascending
                    usort($transformedData, function($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $transformedData,
                    'meta' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'count' => count($transformedData),
                        'source' => 'CryptoQuant API'
                    ]
                ]);
            }

            // API failed - return error
            Log::error('CryptoQuant API Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch data from CryptoQuant API',
                'status' => $response->status(),
                'message' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('CryptoQuant API Exception: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
