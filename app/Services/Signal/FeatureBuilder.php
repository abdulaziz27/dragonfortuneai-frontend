<?php

namespace App\Services\Signal;

use App\Repositories\MarketDataRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeatureBuilder
{
    public function __construct(
        protected MarketDataRepository $marketData
    ) {
    }

    /**
     * Build a complete feature snapshot ready for scoring.
     */
    public function build(string $symbol = 'BTC', string $pair = 'BTCUSDT', string $interval = '1h', ?int $timestampMs = null): array
    {
        $timestampMs = $timestampMs ?? now('UTC')->valueOf();
        $now = Carbon::createFromTimestampMs($timestampMs)->setTimezone('UTC');

        $funding = $this->buildFundingFeatures($pair, $timestampMs);
        $openInterest = $this->buildOpenInterestFeatures($symbol, $interval, $timestampMs);
        $whale = $this->buildWhaleFeatures($symbol, $now, $timestampMs);
        $etf = $this->buildEtfFeatures($timestampMs);
        $sentiment = $this->buildSentimentFeatures($timestampMs);
        $micro = $this->buildMicrostructureFeatures($symbol, $pair, $interval, $timestampMs);
        $liquidations = $this->buildLiquidationFeatures($symbol, $interval, $timestampMs);

        return [
            'symbol' => strtoupper($symbol),
            'pair' => strtoupper($pair),
            'interval' => $interval,
            'generated_at' => $now->toIso8601ZuluString(),
            'funding' => $funding,
            'open_interest' => $openInterest,
            'whales' => $whale,
            'etf' => $etf,
            'sentiment' => $sentiment,
            'microstructure' => $micro,
            'liquidations' => $liquidations,
        ];
    }

    protected function buildFundingFeatures(string $pair): array
    {
        $preferredInterval = '1h';
        $series = $this->marketData->latestFundingRates($pair, $preferredInterval, [], 200);

        if ($series->isEmpty()) {
            $preferredInterval = '1m';
            $series = $this->marketData->latestFundingRates($pair, $preferredInterval, [], 500);
        }

        $grouped = $series->groupBy('exchange');
        $exchangeSnapshots = $grouped->map(function (Collection $rows) {
            $latest = $rows->first();
            $window = $rows->take(60)->pluck('close')->map(fn ($value) => $this->toFloat($value));
            $mean = $window->avg();
            $std = $this->stdDev($window);
            $zScore = $this->zScore($this->toFloat($latest->close), $mean, $std);

            return [
                'latest' => $this->toFloat($latest->close),
                'mean' => $mean,
                'std' => $std,
                'z_score' => $zScore,
            ];
        });

        $heatScore = $exchangeSnapshots->avg('z_score');
        $latestConsensus = $exchangeSnapshots->avg('latest');

        return [
            'interval' => $preferredInterval,
            'heat_score' => $heatScore,
            'consensus' => $latestConsensus,
            'exchanges' => $exchangeSnapshots,
        ];
    }

    protected function buildOpenInterestFeatures(string $symbol, string $interval, ?int $timestampMs = null): array
    {
        $series = $this->marketData->latestOpenInterest($symbol, $interval, 'usd', 240, $timestampMs);

        if ($series->isEmpty()) {
            return [];
        }

        $latest = $series->first();
        $valuesAsc = $series->sortBy('time')->pluck('close')->map(fn ($v) => $this->toFloat($v));

        $ema = $this->ema($valuesAsc, 6);
        $pct6h = $this->percentChangeFromIndex($series, 6);
        $pct24h = $this->percentChangeFromIndex($series, 24);

        return [
            'latest' => $this->toFloat($latest->close),
            'pct_change_6h' => $pct6h,
            'pct_change_24h' => $pct24h,
            'ema_6' => $ema,
        ];
    }

    protected function buildWhaleFeatures(string $symbol, Carbon $now, ?int $timestampMs = null): array
    {
        $lookbackTs = $now->copy()->subDays(7)->timestamp;
        $upperBound = $timestampMs ? intdiv($timestampMs, 1000) : null;
        $raw = $this->marketData->latestWhaleTransfers($symbol, $lookbackTs, 2000, $upperBound);

        if ($raw->isEmpty()) {
            $raw = $this->marketData->latestWhaleTransfers($symbol, null, 2000, $upperBound);
        }

        if ($raw->isEmpty()) {
            return [
                'window_24h' => $this->aggregateWhaleFlows(collect()),
                'window_7d' => $this->aggregateWhaleFlows(collect()),
                'pressure_score' => null,
                'sample_size' => ['d24' => 0, 'd7' => 0],
                'is_stale' => true,
            ];
        }

        $lastDayTs = $now->copy()->subDay()->timestamp;
        $window7d = $raw->filter(fn ($row) => (int) $row->block_timestamp >= $lookbackTs);

        $stale = false;
        if ($window7d->isEmpty()) {
            $window7d = $raw;
            $stale = true;
        }

        $daily = $window7d->filter(fn ($row) => (int) $row->block_timestamp >= $lastDayTs);

        $agg7d = $this->aggregateWhaleFlows($window7d);
        $agg24h = $this->aggregateWhaleFlows($daily);

        $dayBuckets = max($window7d->map(fn ($row) => Carbon::createFromTimestamp($row->block_timestamp)->toDateString())->unique()->count(), 1);
        $avgDailyMagnitude = $dayBuckets > 0
            ? ($agg7d['inflow_usd'] + $agg7d['outflow_usd']) / $dayBuckets
            : 0.0;
        $baseline = max($avgDailyMagnitude, 1.0);
        $pressure = $agg24h['net_usd'] / $baseline;

        return [
            'window_24h' => $agg24h,
            'window_7d' => $agg7d,
            'pressure_score' => $pressure,
            'sample_size' => [
                'd24' => $daily->count(),
                'd7' => $window7d->count(),
            ],
            'is_stale' => $stale || $daily->isEmpty(),
        ];
    }

    protected function buildEtfFeatures(?int $timestampMs = null): array
    {
        $series = $this->marketData->latestEtfFlows(60, $timestampMs);

        if ($series->isEmpty()) {
            return [];
        }

        $latest = $series->first();
        $ma7 = $this->movingAverage($series, 7);
        $ma30 = $this->movingAverage($series, 30);

        return [
            'latest_flow' => $this->toFloat($latest->flow_usd),
            'ma7' => $ma7,
            'ma30' => $ma30,
        ];
    }

    protected function buildSentimentFeatures(?int $timestampMs = null): array
    {
        $history = $this->marketData->fearGreedHistory(60, $timestampMs);

        if ($history->isEmpty()) {
            return [];
        }

        $latest = $history->first();

        return [
            'value' => (int) $latest->value,
            'classification' => $latest->value_classification,
            'ma7' => $history->take(7)->avg(fn ($row) => (int) $row->value),
            'ma30' => $history->take(30)->avg(fn ($row) => (int) $row->value),
        ];
    }

    protected function buildMicrostructureFeatures(string $symbol, string $pair, string $interval, ?int $timestampMs = null): array
    {
        $orderbook = $this->marketData->latestSpotOrderbook($symbol, '1m', 120, $timestampMs);
        $taker = $this->marketData->latestSpotTakerVolume($symbol, $interval, [], 120, $timestampMs);
        $prices = $this->marketData->latestSpotPrices($pair, $interval, 120, $timestampMs);

        $orderbookLatest = $orderbook->first();
        $takerLatest = $taker->first();
        $priceLatest = $prices->first();

        $takerAgg = $this->aggregateTakerVolumes($taker->take(24));
        $bidDepth = $orderbookLatest ? $this->toFloat($orderbookLatest->aggregated_bids_usd) : null;
        $askDepth = $orderbookLatest ? $this->toFloat($orderbookLatest->aggregated_asks_usd) : null;
        $imbalance = $this->orderbookImbalance($bidDepth, $askDepth);

        return [
            'orderbook' => [
                'bid_depth' => $bidDepth,
                'ask_depth' => $askDepth,
                'imbalance' => $imbalance,
                'bid_quantity' => $orderbookLatest ? $this->toFloat($orderbookLatest->aggregated_bids_quantity) : null,
                'ask_quantity' => $orderbookLatest ? $this->toFloat($orderbookLatest->aggregated_asks_quantity) : null,
            ],
            'taker_flow' => [
                'buy_volume' => $takerAgg['buy'],
                'sell_volume' => $takerAgg['sell'],
                'buy_ratio' => $takerAgg['ratio'],
            ],
            'price' => [
                'last_close' => $priceLatest ? $this->toFloat($priceLatest->close) : null,
                'pct_change_24h' => $this->percentChangeFromIndex($prices, 24),
            ],
        ];
    }

    protected function buildLiquidationFeatures(string $symbol, string $interval, ?int $timestampMs = null): array
    {
        $series = $this->marketData->latestLiquidations($symbol, $interval, 120, $timestampMs);

        if ($series->isEmpty()) {
            return [];
        }

        $latest = $series->first();
        $longTotal = $series->take(24)->sum(fn ($row) => $this->toFloat($row->aggregated_long_liquidation_usd));
        $shortTotal = $series->take(24)->sum(fn ($row) => $this->toFloat($row->aggregated_short_liquidation_usd));

        return [
            'latest' => [
                'longs' => $this->toFloat($latest->aggregated_long_liquidation_usd),
                'shorts' => $this->toFloat($latest->aggregated_short_liquidation_usd),
            ],
            'sum_24h' => [
                'longs' => $longTotal,
                'shorts' => $shortTotal,
            ],
        ];
    }

    protected function percentChangeFromIndex(Collection $series, int $hours): ?float
    {
        if ($series->count() <= $hours) {
            return null;
        }

        $latest = $this->toFloat($series->first()->close);
        $reference = $this->toFloat($series->slice($hours, 1)->first()->close ?? null);

        return $this->percentChange($latest, $reference);
    }

    protected function percentChange(?float $current, ?float $previous): ?float
    {
        if ($current === null || $previous === null || $previous == 0.0) {
            return null;
        }

        return (($current - $previous) / $previous) * 100;
    }

    protected function ema(Collection $values, int $period): ?float
    {
        if ($values->isEmpty()) {
            return null;
        }

        $k = 2 / ($period + 1);
        $ema = $values->first();

        foreach ($values->slice(1) as $value) {
            $ema = ($value * $k) + ($ema * (1 - $k));
        }

        return $ema;
    }

    protected function movingAverage(Collection $series, int $length): ?float
    {
        if ($series->isEmpty()) {
            return null;
        }

        return $series->take($length)->avg(fn ($row) => $this->toFloat($row->flow_usd));
    }

    protected function stdDev(Collection $values): ?float
    {
        $values = $values->filter(fn ($value) => $value !== null);
        $count = $values->count();

        if ($count <= 1) {
            return null;
        }

        $mean = $values->avg();
        $variance = $values->map(fn ($value) => pow($value - $mean, 2))->sum() / ($count - 1);

        return sqrt($variance);
    }

    protected function zScore(?float $value, ?float $mean, ?float $std): ?float
    {
        if ($value === null || $mean === null || !$std) {
            return null;
        }

        return ($value - $mean) / $std;
    }

    protected function aggregateWhaleFlows(Collection $rows): array
    {
        $totals = [
            'inflow_usd' => 0.0,
            'outflow_usd' => 0.0,
            'count_inflow' => 0,
            'count_outflow' => 0,
        ];

        foreach ($rows as $row) {
            $amount = $this->toFloat($row->amount_usd);
            if ($this->isExchangeLabel($row->to_address)) {
                $totals['inflow_usd'] += $amount;
                $totals['count_inflow']++;
            } elseif ($this->isExchangeLabel($row->from_address)) {
                $totals['outflow_usd'] += $amount;
                $totals['count_outflow']++;
            }
        }

        $totals['net_usd'] = $totals['inflow_usd'] - $totals['outflow_usd'];

        return $totals;
    }

    protected function aggregateTakerVolumes(Collection $rows): array
    {
        $buy = $rows->sum(fn ($row) => $this->toFloat($row->aggregated_buy_volume_usd));
        $sell = $rows->sum(fn ($row) => $this->toFloat($row->aggregated_sell_volume_usd));
        $total = $buy + $sell;

        return [
            'buy' => $buy,
            'sell' => $sell,
            'ratio' => $total > 0 ? $buy / $total : null,
        ];
    }

    protected function orderbookImbalance(?float $bid, ?float $ask): ?float
    {
        if ($bid === null || $ask === null || ($bid + $ask) == 0.0) {
            return null;
        }

        return ($bid - $ask) / ($bid + $ask);
    }

    protected function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return (float) $value;
    }

    protected array $exchangeKeywords = [
        'binance',
        'coinbase',
        'kraken',
        'bitfinex',
        'bitstamp',
        'bybit',
        'okx',
        'okex',
        'deribit',
        'kucoin',
        'mexc',
        'huobi',
        'gate',
        'gemini',
    ];

    protected function isExchangeLabel(?string $label): bool
    {
        if (!$label) {
            return false;
        }

        $label = Str::lower($label);

        foreach ($this->exchangeKeywords as $keyword) {
            if (Str::contains($label, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
