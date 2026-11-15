<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CityBarangay extends Model
{
    protected $table = 'city_proper_barangays';
    protected $fillable = ['name'];
}
