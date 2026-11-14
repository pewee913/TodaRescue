<?php
namespace App\Http\Controllers;
use App\Models\Place;
use App\Services\FareService;

class PageController extends Controller
{
    public function home()       { return view('home', ['places' => Place::orderBy('name')->get()]); }
    public function calculator() { return view('calculator', ['places' => Place::orderBy('name')->get()]); }
    public function find()       { return view('find', ['places' => Place::orderBy('name')->get()]); }

    public function tables(FareService $fare)
    {
        $places = Place::orderBy('id')->get();
        $rows = [];
        foreach ($places as $i => $a) foreach ($places as $j => $b) {
            if ($i >= $j) continue;
            $km = $fare->distance($a->lat, $a->lng, $b->lat, $b->lng);
            $rows[] = [
                'route' => "{$a->name} → {$b->name}", 'km' => $km,
                'regular' => $fare->estimate($km)['per_passenger'],
                'discounted' => $fare->estimate($km, 'student')['per_passenger'],
                'special' => $fare->estimate($km, 'regular', true)['per_passenger'],
            ];
        }
        usort($rows, fn ($x, $y) => $x['km'] <=> $y['km']);
        return view('tables', compact('rows'));
    }
}
