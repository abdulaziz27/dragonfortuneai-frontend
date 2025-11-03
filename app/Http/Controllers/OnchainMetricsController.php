<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OnchainMetricsController extends Controller
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.api.base_url', 'https://test.dragonfortune.ai'), '/');
    }

    protected function proxy(Request $request, string $endpoint): JsonResponse
    {
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout(20)
                ->withOptions(['verify' => false])
                ->acceptJson()
                ->get($url, $request->query());

            if ($response->successful()) {
                return response()->json($response->json(), $response->status());
            }

            return response()->json([
                'error' => 'On-chain API request failed',
                'status' => $response->status(),
                'details' => $response->json(),
            ], $response->status());
        } catch (\Throwable $exception) {
            return response()->json([
                'error' => 'On-chain API unavailable',
                'message' => $exception->getMessage(),
            ], 502);
        }
    }

    public function metrics(Request $request): JsonResponse
    {
        return $this->proxy($request, '/api/onchain/metrics');
    }

    public function exchangeFlows(Request $request): JsonResponse
    {
        return $this->proxy($request, '/api/onchain/exchange-flows');
    }

    public function networkActivity(Request $request): JsonResponse
    {
        return $this->proxy($request, '/api/onchain/network-activity');
    }

    public function marketData(Request $request): JsonResponse
    {
        return $this->proxy($request, '/api/onchain/market-data');
    }

    public function availableMetrics(Request $request): JsonResponse
    {
        return $this->proxy($request, '/api/onchain/metrics/available');
    }
}
