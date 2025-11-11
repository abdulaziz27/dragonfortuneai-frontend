<?php

namespace App\Services\Signal;

class AiSignalService
{
    public function __construct(
        protected ModelTrainer $trainer
    ) {
    }

    public function predict(array $featuresPayload): ?array
    {
        $probability = $this->trainer->predict($featuresPayload);
        if ($probability === null) {
            return null;
        }

        return [
            'probability' => $probability,
            'decision' => $probability >= 0.55 ? 'BUY' : ($probability <= 0.45 ? 'SELL' : 'NEUTRAL'),
        ];
    }
}
