<?php

namespace App\Services\Signal;

class SignalEngine
{
    public function score(array $features): array
    {
        $score = 0.0;
        $reasons = [];
        $factors = [];

        $fundingHeat = $features['funding']['heat_score'] ?? null;
        $fundingConsensus = $features['funding']['consensus'] ?? null;
        $fundingTrend = $features['funding']['trend_pct'] ?? null;
        $oiPct24 = $features['open_interest']['pct_change_24h'] ?? null;
        $oiPct6 = $features['open_interest']['pct_change_6h'] ?? null;
        $whalePressure = $features['whales']['pressure_score'] ?? null;
        $whaleCexRatio = $features['whales']['cex_ratio'] ?? null;
        $etfFlow = $features['etf']['latest_flow'] ?? null;
        $etfMa7 = $features['etf']['ma7'] ?? null;
        $etfStreak = $features['etf']['streak'] ?? null;
        $sentimentValue = $features['sentiment']['value'] ?? null;
        $takerRatio = $features['microstructure']['taker_flow']['buy_ratio'] ?? null;
        $orderImbalance = $features['microstructure']['orderbook']['imbalance'] ?? null;
        $volatility = $features['microstructure']['price']['volatility_24h'] ?? null;
        $liq = $features['liquidations']['sum_24h'] ?? null;

        $this->contribute(
            $fundingHeat !== null && $fundingHeat > 1.5,
            -2,
            "Funding overheated (z {$this->formatFloat($fundingHeat)})",
            $score,
            $reasons,
            $factors,
            ['heat' => $fundingHeat, 'consensus' => $fundingConsensus]
        );

        $this->contribute(
            $fundingHeat !== null && $fundingHeat < -1.5,
            2,
            "Funding deeply discounted (z {$this->formatFloat($fundingHeat)})",
            $score,
            $reasons,
            $factors,
            ['heat' => $fundingHeat, 'consensus' => $fundingConsensus]
        );

        $this->contribute(
            $fundingTrend !== null && $fundingTrend > 15,
            0.6,
            'Funding momentum turning higher',
            $score,
            $reasons,
            $factors,
            ['trend_pct' => $fundingTrend]
        );

        $this->contribute(
            $fundingTrend !== null && $fundingTrend < -15,
            -0.6,
            'Funding momentum rolling over',
            $score,
            $reasons,
            $factors,
            ['trend_pct' => $fundingTrend]
        );

        $this->contribute(
            $oiPct24 !== null && $fundingHeat !== null && $oiPct24 > 2 && $fundingHeat > 0.5,
            -1.5,
            'Leverage build-up with positive funding',
            $score,
            $reasons,
            $factors,
            ['oi_pct_24h' => $oiPct24, 'funding_heat' => $fundingHeat]
        );

        $this->contribute(
            $oiPct24 !== null && $oiPct24 < -2,
            1.0,
            'Open interest flushing (de-leverage)',
            $score,
            $reasons,
            $factors,
            ['oi_pct_24h' => $oiPct24]
        );

        $this->contribute(
            $whalePressure !== null && $whalePressure > 1.2,
            -1.5,
            'Whale inflow into exchanges',
            $score,
            $reasons,
            $factors,
            ['pressure_score' => $whalePressure]
        );

        $this->contribute(
            $whalePressure !== null && $whalePressure < -1.2,
            1.5,
            'Whale accumulation off-exchange',
            $score,
            $reasons,
            $factors,
            ['pressure_score' => $whalePressure]
        );

        $this->contribute(
            $whaleCexRatio !== null && $whaleCexRatio > 0.65,
            -0.6,
            'Whale inflow concentrated on exchanges',
            $score,
            $reasons,
            $factors,
            ['cex_ratio' => $whaleCexRatio]
        );

        $this->contribute(
            $whaleCexRatio !== null && $whaleCexRatio < 0.35,
            0.6,
            'Whales distributing to cold storage',
            $score,
            $reasons,
            $factors,
            ['cex_ratio' => $whaleCexRatio]
        );

        $this->contribute(
            $etfFlow !== null && $etfFlow > 0 && $etfMa7 !== null && $etfFlow > $etfMa7,
            1.2,
            'ETF net inflow above weekly average',
            $score,
            $reasons,
            $factors,
            ['latest_flow' => $etfFlow, 'ma7' => $etfMa7]
        );

        $this->contribute(
            $etfFlow !== null && $etfFlow < 0 && $etfMa7 !== null && $etfFlow < $etfMa7,
            -1.2,
            'ETF outflow pressure',
            $score,
            $reasons,
            $factors,
            ['latest_flow' => $etfFlow, 'ma7' => $etfMa7]
        );

        $this->contribute(
            $etfStreak !== null && $etfStreak >= 3,
            0.9,
            'ETF inflow streak',
            $score,
            $reasons,
            $factors,
            ['streak' => $etfStreak]
        );

        $this->contribute(
            $etfStreak !== null && $etfStreak <= -3,
            -0.9,
            'ETF outflow streak',
            $score,
            $reasons,
            $factors,
            ['streak' => $etfStreak]
        );

        $this->contribute(
            $sentimentValue !== null && $sentimentValue >= 70,
            -1.0,
            'Extreme greed zone',
            $score,
            $reasons,
            $factors,
            ['sentiment' => $sentimentValue]
        );

        $this->contribute(
            $sentimentValue !== null && $sentimentValue <= 30,
            1.0,
            'Fear zone (contrarian bullish)',
            $score,
            $reasons,
            $factors,
            ['sentiment' => $sentimentValue]
        );

        $this->contribute(
            $takerRatio !== null && $takerRatio > 0.55,
            0.8,
            'Aggressive buyers dominating order flow',
            $score,
            $reasons,
            $factors,
            ['taker_buy_ratio' => $takerRatio]
        );

        $this->contribute(
            $takerRatio !== null && $takerRatio < 0.45,
            -0.8,
            'Aggressive sellers dominating order flow',
            $score,
            $reasons,
            $factors,
            ['taker_buy_ratio' => $takerRatio]
        );

        $this->contribute(
            $orderImbalance !== null && $orderImbalance > 0.1,
            0.5,
            'Bid-side liquidity stacked',
            $score,
            $reasons,
            $factors,
            ['orderbook_imbalance' => $orderImbalance]
        );

        $this->contribute(
            $orderImbalance !== null && $orderImbalance < -0.1,
            -0.5,
            'Ask-side liquidity stacked',
            $score,
            $reasons,
            $factors,
            ['orderbook_imbalance' => $orderImbalance]
        );

        $this->contribute(
            $volatility !== null && $volatility > 5 && $takerRatio !== null && $takerRatio < 0.45,
            -0.6,
            'High volatility with aggressive sellers',
            $score,
            $reasons,
            $factors,
            ['volatility_24h' => $volatility, 'taker_buy_ratio' => $takerRatio]
        );

        $this->contribute(
            $volatility !== null && $volatility < 1.5 && $takerRatio !== null && $takerRatio > 0.55,
            0.5,
            'Calm flow with buyers in control',
            $score,
            $reasons,
            $factors,
            ['volatility_24h' => $volatility, 'taker_buy_ratio' => $takerRatio]
        );

        if ($liq) {
            $longs = $liq['longs'] ?? null;
            $shorts = $liq['shorts'] ?? null;
            $this->contribute(
                $longs !== null && $shorts !== null && $longs > $shorts * 1.5,
                0.8,
                'Long liquidation flush (potential rebound)',
                $score,
                $reasons,
                $factors,
                ['long_liq_24h' => $longs, 'short_liq_24h' => $shorts]
            );
            $this->contribute(
                $longs !== null && $shorts !== null && $shorts > $longs * 1.5,
                -0.8,
                'Short liquidation spike (potential exhaustion)',
                $score,
                $reasons,
                $factors,
                ['long_liq_24h' => $longs, 'short_liq_24h' => $shorts]
            );
        }

        $signal = $this->determineSignal($score);
        $confidence = min(abs($score) / 5, 1);

        return [
            'signal' => $signal,
            'score' => round($score, 2),
            'confidence' => round($confidence, 3),
            'reasons' => $reasons,
            'factors' => $factors,
        ];
    }

    protected function determineSignal(float $score): string
    {
        if ($score >= 1.5) {
            return 'BUY';
        }

        if ($score <= -1.5) {
            return 'SELL';
        }

        return 'NEUTRAL';
    }

    protected function contribute(
        bool $condition,
        float $weight,
        string $reason,
        float &$score,
        array &$reasons,
        array &$factors,
        array $context = []
    ): void {
        if (!$condition) {
            return;
        }

        $score += $weight;
        $reasons[] = $reason;
        $factors[] = [
            'reason' => $reason,
            'weight' => $weight,
            'context' => $context,
        ];
    }

    protected function formatFloat(?float $value): string
    {
        return $value === null ? 'n/a' : number_format($value, 2);
    }
}
