<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FareMatrix extends Model
{
    protected $table = 'fare_matrix';
    protected $fillable = ['exit_point', 'route', 'destination', 'km', 'fare', 'discounted_fare'];
}
