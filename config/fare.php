<?php
return [
    'base' => 20.00,          // pesos for the first base_km
    'base_km' => 1.0,
    'per_km' => 5.00,         // pesos per additional km (billed per 0.5 km)
    'discount' => 0.20,       // student / senior / PWD
    'special_multiplier' => 4,
    'road_factor' => 1.3,     // straight-line to road-distance estimate
];
