<?php
namespace Database\Seeders;
use App\Models\{Place, Toda};
use Illuminate\Database\Seeder;

// Sample data around San Pablo City, Laguna. Replace with real terminals and landmarks.
class TodaSeeder extends Seeder
{
    public function run(): void
    {
        Place::truncate();
        foreach ( [
            ['San Rafael', 14.0700, 121.3230], ['San Francisco', 14.0710, 121.3260],
            ['San Gabriel', 14.0650, 121.3220], ['San Jose', 14.0690, 121.3190],
            ['San Lucas', 14.0630, 121.3280], ['Santa Maria', 14.0660, 121.3210],
            ['Santa Filomena', 14.0610, 121.3300], ['Santo Angel', 14.0740, 121.3240],
            ['Concepcion', 14.0600, 121.3150], ['Del Remedio', 14.0680, 121.3320],
        ] as [$n, $a, $o]) Place::create(['name' => $n, 'lat' => $a, 'lng' => $o]);

        foreach ([
            ['San Rafael TODA', 14.0702, 121.3228, 'San Rafael, San Pablo City', '0900 000 0001', 42, true],
            ['San Francisco TODA', 14.0712, 121.3258, 'San Francisco, San Pablo City', '0900 000 0002', 36, true],
            ['San Gabriel TODA', 14.0652, 121.3218, 'San Gabriel, San Pablo City', '0900 000 0003', 28, true],
            ['San Jose TODA', 14.0692, 121.3188, 'San Jose, San Pablo City', '0900 000 0004', 51, true],
            ['Santa Maria TODA', 14.0662, 121.3208, 'Santa Maria, San Pablo City', '0900 000 0005', 24, false],
            ['Del Remedio TODA', 14.0682, 121.3318, 'Del Remedio, San Pablo City', '0900 000 0006', 30, true],
        ] as [$n, $a, $o, $s, $p, $u, $x]) Toda::create(['name' => $n, 'lat' => $a, 'lng' => $o, 'serves' => $s, 'phone' => $p, 'units' => $u, 'is_active' => $x]);
    }
}
