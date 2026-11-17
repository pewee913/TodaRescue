<?php
namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\FareMatrix;
use App\Models\CityBarangay;

class PageController extends Controller
{
    public function home()       { return view('home', ['places' => Place::orderBy('name')->get()]); }
    public function find()       { return view('find', ['places' => Place::orderBy('name')->get()]); }

    public function tables()
    {
        $rows = FareMatrix::orderBy('id')->get();
        $exits = FareMatrix::orderBy('id')->pluck('exit_point')->unique()->values();
        $barangays = CityBarangay::orderBy('id')->pluck('name');

        return view('tables', compact('rows', 'exits', 'barangays'));
    }
}