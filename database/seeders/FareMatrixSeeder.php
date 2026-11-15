<?php
namespace Database\Seeders;
use App\Models\{CityBarangay, FareMatrix};
use Illuminate\Database\Seeder;

// Tricycle Fare Matrix, City of San Pablo, City Ordinance 72 s. 2026.
// Fare = 20 + 2 x km, discounted = 80%. km is measured from the exit point to the barangay end point.
class FareMatrixSeeder extends Seeder
{
    public function run(): void
    {
        FareMatrix::truncate(); CityBarangay::truncate();
        $data = [
            'Farconville Stoplight Exit' => [
                'Route to Stmo Rosario (7-up)' => [['San Francisco (D.I.)', 2], ['Soledad', 4], ['Sta Maria', 6], ['Stmo Rosario', 9], ['San Isidro', 11]],
                'Route to San Cristobal' => [['Sto Niño', 3], ['Sta Elena', 5], ['San Cristobal', 7]],
                'Route to Sta Cruz & Sta Ana' => [['Sta Cruz', 6], ['Sta Ana', 8]],
                'Route to Highway' => [['San Francisco', 2], ['San Ignacio (Highway)', 3], ['San Ignacio (Baloc)', 5], ['San Vicente', 6], ['San Antonio 1', 7], ['San Antonio 2', 8]],
                'Route to San Gregorio' => [['San Gregorio', 3], ['San Joaquin', 5]],
                'via Sto Cristo Arch' => [['Sto Cristo', 1]],
            ],
            'Sambat Bridge Exit' => [
                '' => [['Del Remedio', 2], ['Sta Maria Magdalena (Highway)', 3], ['Sta Maria Magdalena (Loob)', 4]],
                'Route to San Crispin' => [['San Juan', 4], ['Sta Felomina', 5], ['San Crispin', 6]],
                'Route to Highway' => [['San Marcos (Highway)', 4], ['San Marcos (Loob)', 5], ['San Mateo', 5]],
            ],
            'San Gabriel (PNR Riles) Exit' => [
                '' => [['San Gabriel', 2], ['San Miguel', 4], ['San Bartolome', 6], ['Santiago I', 7], ['Santiago II', 9], ['Bautista', 12], ['Stmo Rosario', 13], ['San Isidro', 15], ['Atisan', 19]],
            ],
            'Community General Hospital Exit' => [
                'Route to Highway' => [['San Roque', 1], ['San Rafael', 2], ['San Nicolas', 4]],
                'Route to Sta Monica' => [['Sta Monica', 3], ['Sta Veronica', 5], ['San Bartolome', 7]],
            ],
            'Sabang Bridge Exit' => [
                '' => [['San Lucas 1', 1], ['San Lucas 2', 1], ['San Pedro', 2], ['Dolores (Main road)', 4], ['Dolores (Sta Ma. Magdalena road)', 5], ['San Buenaventura', 5], ['Sta Catalina', 6], ['San Lorenzo', 7]],
            ],
            'Boundary of Concepcion Exit' => [
                '' => [['Concepcion', 2], ['Sta Isabel', 3], ['San Diego', 4], ['Sto Angel', 7], ['San Lorenzo', 8]],
            ],
            'Boundary of San Jose Exit' => [
                '' => [['San Jose', 1], ['San Jose (NHA)', 2], ['San Jose (Banagale)', 2]],
            ],
        ];
        foreach ($data as $exit => $routes) foreach ($routes as $route => $stops) foreach ($stops as [$dest, $km]) {
            $fare = 20 + 2 * $km;
            FareMatrix::create(['exit_point' => $exit, 'route' => $route ?: null, 'destination' => $dest, 'km' => $km, 'fare' => $fare, 'discounted_fare' => round($fare * 0.8, 2)]);
        }
        foreach (['I-A', 'I-B', 'II-A (Bagong Bayan)', 'II-A (Triangulo)', 'II-B', 'II-C', 'II-D', 'II-E', 'II-F', 'III-A', 'III-B', 'III-C', 'III-D', 'III-E', 'III-F', 'IV-A', 'IV-B', 'IV-C', 'V-A', 'V-B', 'V-C', 'V-D', 'VI-A', 'VI-B', 'VI-C (Bagong Pook)', 'VI-D', 'VI-E', 'VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E'] as $b)
            CityBarangay::create(['name' => $b]);
    }
}
