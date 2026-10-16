<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [

            // =========================================================
            // CENTRAL PROVINCE - 1
            // =========================================================
            1 => [
                'Chibombo',
                'Chisamba',
                'Chitambo',
                'Itezhi-Tezhi',
                'Kabwe',
                'Kapiri Mposhi',
                'Luano',
                'Mkushi',
                'Mumbwa',
                'Ngabwe',
                'Serenje',
                'Shibuyunji',
            ],

            // =========================================================
            // COPPERBELT PROVINCE - 2
            // =========================================================
            2 => [
                'Chililabombwe',
                'Chingola',
                'Kalulushi',
                'Kitwe',
                'Luanshya',
                'Lufwanyama',
                'Masaiti',
                'Mpongwe',
                'Mufulira',
                'Ndola',
            ],

            // =========================================================
            // EASTERN PROVINCE - 3
            // =========================================================
            3 => [
                'Chadiza',
                'Chama',
                'Chasefu',
                'Chipangali',
                'Chipata',
                'Kasenengwa',
                'Katete',
                'Lumezi',
                'Lundazi',
                'Lusangazi',
                'Mambwe',
                'Nyimba',
                'Petauke',
                'Sinda',
                'Vubwi',
            ],

            // =========================================================
            // LUAPULA PROVINCE - 4
            // =========================================================
            4 => [
                'Chembe',
                'Chiengi',
                'Chifunabuli',
                'Chipili',
                'Kawambwa',
                'Lunga',
                'Mansa',
                'Milenge',
                'Mwansabombwe',
                'Mwense',
                'Nchelenge',
                'Samfya',
            ],

            // =========================================================
            // LUSAKA PROVINCE - 5
            // =========================================================
            5 => [
                'Chilanga',
                'Chirundu',
                'Chongwe',
                'Kafue',
                'Luangwa',
                'Lusaka',
                'Rufunsa',
            ],

            // =========================================================
            // MUCHINGA PROVINCE - 6
            // =========================================================
            6 => [
                'Chama',
                'Chinsali',
                'Isoka',
                'Kanchibiya',
                'Lavushimanda',
                'Mafinga',
                'Mpika',
                "Shiwa Ng'andu",
            ],

            // =========================================================
            // NORTHERN PROVINCE - 7
            // =========================================================
            7 => [
                'Chilubi',
                'Kaputa',
                'Kasama',
                'Lunte',
                'Lupososhi',
                'Luwingu',
                'Mbala',
                'Mporokoso',
                'Mpulungu',
                'Mungwi',
                'Nsama',
                'Senga Hill',
            ],

            // =========================================================
            // NORTH-WESTERN PROVINCE - 8
            // =========================================================
            8 => [
                'Chavuma',
                'Ikelenge',
                'Kabompo',
                'Kalumbila',
                'Kasempa',
                'Manyinga',
                'Mufumbwe',
                'Mushindamo',
                'Mwinilunga',
                'Solwezi',
                'Zambezi',
            ],

            // =========================================================
            // SOUTHERN PROVINCE - 9
            // =========================================================
            9 => [
                'Chikankata',
                'Choma',
                'Gwembe',
                'Kalomo',
                'Kazungula',
                'Livingstone',
                'Mazabuka',
                'Monze',
                'Namwala',
                'Pemba',
                'Siavonga',
                'Sinazongwe',
                'Zimba',
            ],

            // =========================================================
            // WESTERN PROVINCE - 10
            // =========================================================
            10 => [
                'Kalabo',
                'Kaoma',
                'Limulunga',
                'Luampa',
                'Lukulu',
                'Mitete',
                'Mongu',
                'Mulobezi',
                'Mwandi',
                'Nalolo',
                'Nkeyema',
                'Senanga',
                'Sesheke',
                'Shangombo',
                'Sikongo',
                'Sioma',
            ],
        ];

        foreach ($districts as $provinceId => $provinceDistricts) {
            foreach ($provinceDistricts as $district) {
                DB::table('districts')->updateOrInsert(
                    [
                        'province_id' => $provinceId,
                        'name' => $district,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
