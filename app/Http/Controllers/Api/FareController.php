<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\FareService;
use Illuminate\Http\Request;

class FareController extends Controller
{
    // POST /api/fare  (distance comes from the road route measured in the browser)
    public function calculate(Request $r, FareService $fare)
    {
        $v = $r->validate([
            'distance' => 'required|numeric|min:0|max:100',
            'passenger_type' => 'in:regular,student,senior,pwd',
            'special' => 'boolean',
            'passengers' => 'integer|min:1|max:6',
        ]);
        return $fare->estimate($v['distance'], $v['passenger_type'] ?? 'regular', (bool) ($v['special'] ?? false), $v['passengers'] ?? 1);
    }
}
