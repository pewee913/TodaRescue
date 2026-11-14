<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Toda extends Model
{
    protected $fillable = ['name', 'lat', 'lng', 'serves', 'phone', 'units', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
