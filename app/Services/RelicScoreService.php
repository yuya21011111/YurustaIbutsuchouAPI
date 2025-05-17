<?php

namespace App\Services;

class RelicScoreService
{
    public array $defaultWeights = [
        'HPDelta' => 0.0,
        'AttackDelta' => 0.0,
        'DefenceDelta' => 0.0,
        'HPAddedRatio' => 0.0,
        'AttackAddedRatio' => 0.0,
        'DefenceAddedRatio' => 0.0,
        'CriticalChanceBase' => 0.0,
        'CriticalDamageBase' => 0.0,
        'StatusProbabilityBase' => 0.0,
        'BreakDamageAddedRatioBase' => 0.0,
        'StatusResistanceBase' => 0.0,
        'SpeedDelta' => 0.0,
    ];

    public array $maxValues = [
        'HPDelta' => 252,
        'AttackDelta' => 126,
        'DefenceDelta' => 126,
        'HPAddedRatio' => 25.9,
        'AttackAddedRatio' => 25.9,
        'DefenceAddedRatio' => 32.4,
        'CriticalChanceBase' => 19.4,
        'CriticalDamageBase' => 38.8,
        'StatusProbabilityBase' => 25.9,
        'BreakDamageAddedRatioBase' => 38.8,
        'StatusResistanceBase' => 25.9,
        'SpeedDelta' => 15.6,
    ];

    public function calculateMainScore(int $level): float
    {
        return round((($level + 1) / 16) * 1 * 100, 2);
    }

    public function calculateSubScore(string $type, float $value, float $weight = 0.0): float
    {
        if (!isset($this->maxValues[$type])) {
            return 0.0;
        }

        return round(($value / $this->maxValues[$type]) * $weight * 100, 2);
    }

    public function calculateRelicScore(array $relic, array $weights = []): array
    {
        // ユーザー指定の重みを優先し、残りをデフォルトで埋める
        $weights = $weights + $this->defaultWeights;

        $mainScore = $this->calculateMainScore($relic['level'] ?? 0);

        $subScore = 0.0;
        $details = [];

        foreach ($relic['sub_affix'] ?? [] as $sub) {
            $type = $sub['type'];
            $value = $sub['value'];
        
            // 「%」系ステータスの場合は100倍する
            if (!empty($sub['percent']) && $sub['percent'] === true) {
                $value *= 100;
            }
        
            $weight = $weights[$type] ?? 0.0;
            $score = $this->calculateSubScore($type, $value, $weight);
        
            $details[] = [
                'type' => $type,
                'value' => $value,
                'weight' => $weight,
                'score' => $score,
            ];
        
            $subScore += $score;
        }

        $totalScore = round(($mainScore * 0.5) + ($subScore * 0.5), 2);

        return [
            'name' => $relic['name'] ?? 'Unknown',
            'main' => $mainScore,
            'sub' => round($subScore, 2),
            'total' => $totalScore,
            'details' => $details,
        ];
    }
}
