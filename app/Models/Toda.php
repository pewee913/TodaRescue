<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Toda extends Model
{
    protected $fillable = [
        'name',
        'lat',
        'lng',
        'serves',
        'barangays_passed',
        'landmarks',
        'route_waypoints',
        'phone',
        'units',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'route_waypoints' => 'array',
    ];
}
