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
        ] as [$n, $a, $o]) {
            Place::create(['name' => $n, 'lat' => $a, 'lng' => $o]);
        }

        Toda::truncate();
        foreach ([
            [
                'name' => 'Santiago 1 TODA',
                'lat' => 14.0683,
                'lng' => 121.3256,
                'serves' => 'Santiago 1 & Santiago 2, San Pablo City',
                'barangays_passed' => 'Brgy. San Gabriel, San Miguel, San Bartolome, Santiago 1, Santiago 2',
                'landmarks' => 'Lumang Libingan, Prosperity Memorial Park',
                'route_waypoints' => [
                    ['name' => 'Brgy. San Gabriel', 'lat' => 14.0585, 'lng' => 121.3200],
                    ['name' => 'Lumang Libingan (San Pablo Cemetery)', 'lat' => 14.0540, 'lng' => 121.3160],
                    ['name' => 'Prosperity Memorial Park', 'lat' => 14.0480, 'lng' => 121.3090],
                    ['name' => 'Brgy. San Miguel / San Bartolome', 'lat' => 14.0380, 'lng' => 121.2980],
                    ['name' => 'Brgy. Santiago 1', 'lat' => 14.0204, 'lng' => 121.2783],
                    ['name' => 'Santiago 2', 'lat' => 14.0090, 'lng' => 121.2738],
                ],
                'phone' => '0900 000 0007',
                'units' => 38,
                'is_active' => true,
            ],
            [
                'name' => 'San Gabriel TODA',
                'lat' => 14.0680,
                'lng' => 121.3250,
                'serves' => 'San Gabriel, San Pablo City',
                'barangays_passed' => 'Brgy. San Gabriel, San Miguel, San Bartolome',
                'landmarks' => 'PNR Riles Crossing, San Gabriel Elementary School',
                'route_waypoints' => [
                    ['name' => 'PNR Riles Crossing', 'lat' => 14.0665, 'lng' => 121.3235],
                    ['name' => 'San Gabriel Elementary School', 'lat' => 14.0640, 'lng' => 121.3218],
                    ['name' => 'Brgy. San Bartolome Boundary', 'lat' => 14.0595, 'lng' => 121.3190],
                ],
                'phone' => '0900 000 0003',
                'units' => 28,
                'is_active' => true,
            ],
            [
                'name' => 'San Rafael TODA',
                'lat' => 14.0683,
                'lng' => 121.3256,
                'serves' => 'San Rafael, San Pablo City',
                'barangays_passed' => 'Brgy. San Roque, San Rafael, San Nicolas',
                'landmarks' => 'Community General Hospital, San Rafael Chapel',
                'route_waypoints' => [
                    ['name' => 'Community General Hospital', 'lat' => 14.0702, 'lng' => 121.3228],
                    ['name' => 'San Rafael Chapel', 'lat' => 14.0740, 'lng' => 121.3210],
                    ['name' => 'San Rafael Barangay Hall', 'lat' => 14.0785, 'lng' => 121.3195],
                ],
                'phone' => '0900 000 0001',
                'units' => 42,
                'is_active' => true,
            ],
            [
                'name' => 'San Francisco TODA',
                'lat' => 14.0683,
                'lng' => 121.3256,
                'serves' => 'San Francisco, San Pablo City',
                'barangays_passed' => 'Brgy. San Francisco, Soledad, Sta. Maria',
                'landmarks' => 'Farconville Stoplight, 7-Up Bottling Plant',
                'route_waypoints' => [
                    ['name' => 'Farconville Stoplight', 'lat' => 14.0725, 'lng' => 121.3265],
                    ['name' => '7-Up Bottling Plant', 'lat' => 14.0780, 'lng' => 121.3295],
                    ['name' => 'Brgy. San Francisco Terminal', 'lat' => 14.0845, 'lng' => 121.3330],
                ],
                'phone' => '0900 000 0002',
                'units' => 36,
                'is_active' => true,
            ],
            [
                'name' => 'San Jose TODA',
                'lat' => 14.0683,
                'lng' => 121.3256,
                'serves' => 'San Jose, San Pablo City',
                'barangays_passed' => 'Brgy. San Jose, San Jose NHA, Banagale',
                'landmarks' => 'San Jose Boundary, NHA Housing',
                'route_waypoints' => [
                    ['name' => 'San Jose Boundary', 'lat' => 14.0692, 'lng' => 121.3188],
                    ['name' => 'NHA Housing Site', 'lat' => 14.0715, 'lng' => 121.3130],
                    ['name' => 'Brgy. San Jose Banagale', 'lat' => 14.0750, 'lng' => 121.3065],
                ],
                'phone' => '0900 000 0004',
                'units' => 51,
                'is_active' => true,
            ],
            [
                'name' => 'Santa Maria TODA',
                'lat' => 14.0683,
                'lng' => 121.3256,
                'serves' => 'Santa Maria, San Pablo City',
                'barangays_passed' => 'Brgy. Sta. Maria Magdalena, Sta. Felomina',
                'landmarks' => 'Sambat Bridge, Sta. Maria Chapel',
                'route_waypoints' => [
                    ['name' => 'Sambat Bridge', 'lat' => 14.0662, 'lng' => 121.3208],
                    ['name' => 'Sta. Maria Chapel', 'lat' => 14.0635, 'lng' => 121.3160],
                    ['name' => 'Santa Maria Magdalena', 'lat' => 14.0585, 'lng' => 121.3115],
                ],
                'phone' => '0900 000 0005',
                'units' => 24,
                'is_active' => false,
            ],
            [
                'name' => 'Del Remedio TODA',
                'lat' => 14.0683,
                'lng' => 121.3256,
                'serves' => 'Del Remedio, San Pablo City',
                'barangays_passed' => 'Brgy. Del Remedio, Sambat, Sta. Maria Magdalena',
                'landmarks' => 'Sambat Bridge Arch, Del Remedio Barangay Hall',
                'route_waypoints' => [
                    ['name' => 'Sambat Bridge Arch', 'lat' => 14.0682, 'lng' => 121.3318],
                    ['name' => 'Del Remedio Barangay Hall', 'lat' => 14.0715, 'lng' => 121.3380],
                    ['name' => 'Del Remedio Boundary', 'lat' => 14.0755, 'lng' => 121.3435],
                ],
                'phone' => '0900 000 0006',
                'units' => 30,
                'is_active' => true,
            ],
        ] as $toda) {
            Toda::create($toda);
        }
    }
}
