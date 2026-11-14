<?php
namespace Database\Seeders;
use App\Models\{Place, Toda};
use Illuminate\Database\Seeder;

// Sample data around Castilla, Sorsogon. Replace with real terminals and landmarks.
class TodaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Poblacion', 12.9560, 123.8760], ['Public Market', 12.9585, 123.8805],
            ['Barangay Hall', 12.9520, 123.8730], ['Terminal Road', 12.9625, 123.8725],
            ['Town Plaza', 12.9545, 123.8815], ['Zone 5', 12.9660, 123.8780], ['Zone 6', 12.9490, 123.8850],
        ] as [$n, $a, $o]) Place::create(['name' => $n, 'lat' => $a, 'lng' => $o]);

        foreach ([
            ['Poblacion TODA', 12.9562, 123.8758, 'Poblacion, Zone 1 and Zone 2', '0900 000 0001', 42, true],
            ['Public Market TODA', 12.9587, 123.8803, 'Public Market, Zone 3', '0900 000 0002', 36, true],
            ['Barangay Hall TODA', 12.9522, 123.8728, 'Barangay Hall, Zone 4', '0900 000 0003', 28, true],
            ['Terminal Road TODA', 12.9627, 123.8723, 'Terminal Road, Zone 5', '0900 000 0004', 51, true],
            ['Plaza TODA', 12.9547, 123.8813, 'Town Plaza, Zone 6', '0900 000 0005', 24, false],
        ] as [$n, $a, $o, $s, $p, $u, $x]) Toda::create(['name' => $n, 'lat' => $a, 'lng' => $o, 'serves' => $s, 'phone' => $p, 'units' => $u, 'is_active' => $x]);
    }
}
