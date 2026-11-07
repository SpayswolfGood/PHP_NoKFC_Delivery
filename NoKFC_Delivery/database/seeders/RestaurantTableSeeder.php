<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'NO_KFC Центральный',
                'address' => 'ул. Ленина, 45',
                'phone' => '+7 (495) 100-20-31',
                'opening_time' => '09:00:00',
                'closing_time' => '23:00:00',
                'tables' => [
                    ['table_number' => '1', 'seats' => 2],
                    ['table_number' => '2', 'seats' => 2],
                    ['table_number' => '3', 'seats' => 4],
                    ['table_number' => '4', 'seats' => 4],
                    ['table_number' => '5', 'seats' => 6],
                    ['table_number' => '6', 'seats' => 6],
                ],
            ],
            [
                'name' => 'NO_KFC в ТЦ Галерея',
                'address' => 'пр. Мира, 12 (3 этаж)',
                'phone' => '+7 (495) 100-20-32',
                'opening_time' => '10:00:00',
                'closing_time' => '22:00:00',
                'tables' => [
                    ['table_number' => '1', 'seats' => 2],
                    ['table_number' => '2', 'seats' => 3],
                    ['table_number' => '3', 'seats' => 4],
                    ['table_number' => '4', 'seats' => 4],
                    ['table_number' => '5', 'seats' => 5],
                    ['table_number' => '6', 'seats' => 6],
                ],
            ],
            [
                'name' => 'NO_KFC Уютный',
                'address' => 'ул. Пушкина, 10',
                'phone' => '+7 (495) 100-20-33',
                'opening_time' => '09:00:00',
                'closing_time' => '23:00:00',
                'tables' => [
                    ['table_number' => '1', 'seats' => 2],
                    ['table_number' => '2', 'seats' => 2],
                    ['table_number' => '3', 'seats' => 4],
                    ['table_number' => '4', 'seats' => 6],
                ],
            ],
        ];

        foreach ($branches as $branchData) {
            $tables = $branchData['tables'];
            unset($branchData['tables']);

            $restaurant = Restaurant::query()->updateOrCreate(
                ['address' => $branchData['address']],
                $branchData
            );

            foreach ($tables as $t) {
                RestaurantTable::query()->updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'table_number' => (string) $t['table_number'],
                    ],
                    [
                        'seats' => $t['seats'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
