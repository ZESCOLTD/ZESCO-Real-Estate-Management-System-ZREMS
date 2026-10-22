<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Central',
            'Copperbelt',
            'Eastern',
            'Luapula',
            'Lusaka',
            'Muchinga',
            'Northern',
            'North-Western',
            'Southern',
            'Western',
        ];

        foreach ($names as $name) {
            $normalisedName = strtolower(str_replace('-', ' ', $name));

            $province = Province::withTrashed()
                ->whereRaw(
                    "LOWER(REPLACE(name, '-', ' ')) IN (?, ?)",
                    [$normalisedName, $normalisedName . ' province']
                )
                ->first();

            if ($province) {
                if ($province->trashed()) {
                    $province->restore();
                }

                continue;
            }

            Province::create(['name' => $name]);
        }
    }
}