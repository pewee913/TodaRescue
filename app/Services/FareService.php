<?php
namespace App\Services;

class FareService
{
    public function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = M_PI / 180;
        $a = sin(($lat2 - $lat1) * $r / 2) ** 2
           + cos($lat1 * $r) * cos($lat2 * $r) * sin(($lng2 - $lng1) * $r / 2) ** 2;
        return 12742 * asin(sqrt($a)) * config('fare.road_factor');
    }

    public function estimate(float $km, string $type = 'regular', bool $special = false, int $n = 1): array
    {
        $c = config('fare');
        $extraKm = max(0, ceil(($km - $c['base_km']) * 2) / 2);
        $extra = $extraKm * $c['per_km'];
        $sub = ($c['base'] + $extra) * ($special ? $c['special_multiplier'] : 1);
        $discount = $type !== 'regular' ? $sub * $c['discount'] : 0;
        $per = $sub - $discount;
        return [
            'distance' => round($km, 1), 'minutes' => (int) round($km * 3 + 2),
            'base' => $c['base'], 'extra_km' => $extraKm, 'extra' => $extra,
            'special' => $special ? $sub - $c['base'] - $extra : 0,
            'discount' => round($discount, 2), 'per_passenger' => round($per, 2),
            'total' => round($per * $n, 2), 'passengers' => $n,
        ];
    }
}
