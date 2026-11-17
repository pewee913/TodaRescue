<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Toda;
use App\Services\FareService;
use Illuminate\Http\Request;

class TodaController extends Controller
{
    // GET /api/todas?lat=&lng=&dest_lat=&dest_lng=&q=&open=1
    public function index(Request $r, FareService $fare)
    {
        $q = Toda::query();
        if ($r->boolean('open')) $q->where('is_active', true);
        if ($r->filled('q')) {
            $term = '%' . $r->q . '%';
            $hits = (clone $q)->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('serves', 'like', $term));
            if ($hits->exists()) $q = $hits;
        }
        $from = [(float) $r->input('lat', 14.0683), (float) $r->input('lng', 121.3256)];
        $dest = $r->filled('dest_lat') ? [(float) $r->dest_lat, (float) $r->dest_lng] : null;

        $rows = $q->get()->map(function ($t) use ($fare, $from, $dest) {
            $o = $t->toArray();
            $o['km_from_you'] = round($fare->distance($from[0], $from[1], $t->lat, $t->lng), 1);
            if ($dest) {
                $km = $fare->distance($t->lat, $t->lng, $dest[0], $dest[1]);
                $o['km_to_dest'] = round($km, 1);
                $o['fare_to_dest'] = $fare->estimate($km)['per_passenger'];
            }
            return $o;
        })->sortBy($dest ? 'km_to_dest' : 'km_from_you')->values();

        return response()->json($rows);
    }
}
