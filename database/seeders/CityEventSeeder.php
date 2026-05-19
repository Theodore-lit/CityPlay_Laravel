<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\City;
use App\Models\CityEvent;

class CityEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cotonou = City::where('name', 'Cotonou Vibrante')->first();
        $ouidah = City::where('name', 'Ouidah Historique')->first();
        $portoNovo = City::where('name', 'Porto-Novo Impériale')->first();

        if ($cotonou) {
            CityEvent::updateOrCreate(
                ['title' => 'Festival International de Cotonou', 'city_id' => $cotonou->id],
                [
                    'description' => 'Un événement majeur célébrant l\'art, la musique et la gastronomie béninoise au cœur de la capitale économique.',
                    'images' => [
                        'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&q=80&w=800'
                    ],
                    'event_date' => now()->addDays(15),
                    'location_name' => 'Esplanade de l\'Amazone',
                ]
            );
        }

        if ($ouidah) {
            CityEvent::updateOrCreate(
                ['title' => 'Fête du Vodoun', 'city_id' => $ouidah->id],
                [
                    'description' => 'La célébration annuelle des cultes traditionnels à Ouidah, attirant des visiteurs du monde entier.',
                    'images' => [
                        'https://images.unsplash.com/photo-1514525253344-f814d0743b1a?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=800'
                    ],
                    'event_date' => now()->addMonths(2),
                    'location_name' => 'Plage de la Porte du Non-Retour',
                ]
            );
        }

        if ($portoNovo) {
            CityEvent::updateOrCreate(
                ['title' => 'Carnaval de Porto-Novo', 'city_id' => $portoNovo->id],
                [
                    'description' => 'Une explosion de couleurs et de rythmes dans les rues de la capitale administrative.',
                    'images' => [
                        'https://images.unsplash.com/photo-1551972251-12070d63502a?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&q=80&w=800'
                    ],
                    'event_date' => now()->addDays(45),
                    'location_name' => 'Place Toffa 1er',
                ]
            );
        }

        $parakou = City::where('name', 'Parakou la Cité des Princes')->first();
        if ($parakou) {
            CityEvent::updateOrCreate(
                ['title' => 'Gani de Parakou', 'city_id' => $parakou->id],
                [
                    'description' => 'La fête traditionnelle de la cité des Koburu, marquée par des parades de chevaux et des danses rituelles.',
                    'images' => [
                        'https://images.unsplash.com/photo-1524115011691-c3875272d443?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&q=80&w=800'
                    ],
                    'event_date' => now()->addMonths(3),
                    'location_name' => 'Palais Royal de Parakou',
                ]
            );
        }

        $abomey = City::where('name', 'Abomey la Royale')->first();
        if ($abomey) {
            CityEvent::updateOrCreate(
                ['title' => 'Pèlerinage à Goho', 'city_id' => $abomey->id],
                [
                    'description' => 'Un hommage solennel aux rois du Dahomey sur la place historique de Goho.',
                    'images' => [
                        'https://images.unsplash.com/photo-1523805081446-99b256619ebf?auto=format&fit=crop&q=80&w=800',
                        'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&q=80&w=800'
                    ],
                    'event_date' => now()->addDays(20),
                    'location_name' => 'Place de Goho',
                ]
            );
        }
    }
}
