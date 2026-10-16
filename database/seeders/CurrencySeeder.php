<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            // Africa
            ['code' => 'ZMW', 'numeric_code' => '967', 'name' => 'Zambian Kwacha', 'symbol' => 'K', 'decimal_places' => 2, 'is_default' => true],
            ['code' => 'ZAR', 'numeric_code' => '710', 'name' => 'South African Rand', 'symbol' => 'R', 'decimal_places' => 2],
            ['code' => 'BWP', 'numeric_code' => '072', 'name' => 'Botswana Pula', 'symbol' => 'P', 'decimal_places' => 2],
            ['code' => 'MWK', 'numeric_code' => '454', 'name' => 'Malawian Kwacha', 'symbol' => 'MK', 'decimal_places' => 2],
            ['code' => 'TZS', 'numeric_code' => '834', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'decimal_places' => 2],
            ['code' => 'KES', 'numeric_code' => '404', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'decimal_places' => 2],
            ['code' => 'UGX', 'numeric_code' => '800', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'decimal_places' => 0],
            ['code' => 'RWF', 'numeric_code' => '646', 'name' => 'Rwandan Franc', 'symbol' => 'RF', 'decimal_places' => 0],
            ['code' => 'BIF', 'numeric_code' => '108', 'name' => 'Burundian Franc', 'symbol' => 'FBu', 'decimal_places' => 0],
            ['code' => 'ETB', 'numeric_code' => '230', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'decimal_places' => 2],
            ['code' => 'GHS', 'numeric_code' => '936', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'decimal_places' => 2],
            ['code' => 'NGN', 'numeric_code' => '566', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'decimal_places' => 2],
            ['code' => 'EGP', 'numeric_code' => '818', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimal_places' => 2],
            ['code' => 'MAD', 'numeric_code' => '504', 'name' => 'Moroccan Dirham', 'symbol' => 'د.م.', 'decimal_places' => 2],
            ['code' => 'DZD', 'numeric_code' => '012', 'name' => 'Algerian Dinar', 'symbol' => 'دج', 'decimal_places' => 2],
            ['code' => 'TND', 'numeric_code' => '788', 'name' => 'Tunisian Dinar', 'symbol' => 'د.ت', 'decimal_places' => 3],
            ['code' => 'LYD', 'numeric_code' => '434', 'name' => 'Libyan Dinar', 'symbol' => 'ل.د', 'decimal_places' => 3],
            ['code' => 'SDG', 'numeric_code' => '938', 'name' => 'Sudanese Pound', 'symbol' => 'ج.س.', 'decimal_places' => 2],
            ['code' => 'SSP', 'numeric_code' => '728', 'name' => 'South Sudanese Pound', 'symbol' => '£', 'decimal_places' => 2],
            ['code' => 'SOS', 'numeric_code' => '706', 'name' => 'Somali Shilling', 'symbol' => 'Sh', 'decimal_places' => 2],
            ['code' => 'DJF', 'numeric_code' => '262', 'name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimal_places' => 0],
            ['code' => 'ERN', 'numeric_code' => '232', 'name' => 'Eritrean Nakfa', 'symbol' => 'Nfk', 'decimal_places' => 2],
            ['code' => 'MUR', 'numeric_code' => '480', 'name' => 'Mauritian Rupee', 'symbol' => '₨', 'decimal_places' => 2],
            ['code' => 'SCR', 'numeric_code' => '690', 'name' => 'Seychellois Rupee', 'symbol' => '₨', 'decimal_places' => 2],
            ['code' => 'MZN', 'numeric_code' => '943', 'name' => 'Mozambican Metical', 'symbol' => 'MT', 'decimal_places' => 2],
            ['code' => 'NAD', 'numeric_code' => '516', 'name' => 'Namibian Dollar', 'symbol' => 'N$', 'decimal_places' => 2],
            ['code' => 'SZL', 'numeric_code' => '748', 'name' => 'Swazi Lilangeni', 'symbol' => 'E', 'decimal_places' => 2],
            ['code' => 'LSL', 'numeric_code' => '426', 'name' => 'Lesotho Loti', 'symbol' => 'L', 'decimal_places' => 2],
            ['code' => 'AOA', 'numeric_code' => '973', 'name' => 'Angolan Kwanza', 'symbol' => 'Kz', 'decimal_places' => 2],
            ['code' => 'CDF', 'numeric_code' => '976', 'name' => 'Congolese Franc', 'symbol' => 'FC', 'decimal_places' => 2],
            ['code' => 'XAF', 'numeric_code' => '950', 'name' => 'Central African CFA Franc', 'symbol' => 'FCFA', 'decimal_places' => 0],
            ['code' => 'XOF', 'numeric_code' => '952', 'name' => 'West African CFA Franc', 'symbol' => 'CFA', 'decimal_places' => 0],

            // Major international currencies
            ['code' => 'USD', 'numeric_code' => '840', 'name' => 'United States Dollar', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'EUR', 'numeric_code' => '978', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
            ['code' => 'GBP', 'numeric_code' => '826', 'name' => 'British Pound Sterling', 'symbol' => '£', 'decimal_places' => 2],
            ['code' => 'CHF', 'numeric_code' => '756', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimal_places' => 2],
            ['code' => 'JPY', 'numeric_code' => '392', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0],
            ['code' => 'CNY', 'numeric_code' => '156', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'decimal_places' => 2],
            ['code' => 'CAD', 'numeric_code' => '124', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimal_places' => 2],
            ['code' => 'AUD', 'numeric_code' => '036', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'decimal_places' => 2],
            ['code' => 'NZD', 'numeric_code' => '554', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'decimal_places' => 2],
            ['code' => 'SGD', 'numeric_code' => '702', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'decimal_places' => 2],
            ['code' => 'HKD', 'numeric_code' => '344', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'decimal_places' => 2],
            ['code' => 'AED', 'numeric_code' => '784', 'name' => 'United Arab Emirates Dirham', 'symbol' => 'د.إ', 'decimal_places' => 2],
            ['code' => 'SAR', 'numeric_code' => '682', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'decimal_places' => 2],
            ['code' => 'QAR', 'numeric_code' => '634', 'name' => 'Qatari Riyal', 'symbol' => 'ر.ق', 'decimal_places' => 2],
            ['code' => 'KWD', 'numeric_code' => '414', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'decimal_places' => 3],
            ['code' => 'BHD', 'numeric_code' => '048', 'name' => 'Bahraini Dinar', 'symbol' => '.د.ب', 'decimal_places' => 3],
            ['code' => 'OMR', 'numeric_code' => '512', 'name' => 'Omani Rial', 'symbol' => 'ر.ع.', 'decimal_places' => 3],
            ['code' => 'INR', 'numeric_code' => '356', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimal_places' => 2],
            ['code' => 'PKR', 'numeric_code' => '586', 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'decimal_places' => 2],
            ['code' => 'BDT', 'numeric_code' => '050', 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'decimal_places' => 2],
            ['code' => 'MYR', 'numeric_code' => '458', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'decimal_places' => 2],
            ['code' => 'THB', 'numeric_code' => '764', 'name' => 'Thai Baht', 'symbol' => '฿', 'decimal_places' => 2],
            ['code' => 'IDR', 'numeric_code' => '360', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'decimal_places' => 2],
            ['code' => 'PHP', 'numeric_code' => '608', 'name' => 'Philippine Peso', 'symbol' => '₱', 'decimal_places' => 2],
            ['code' => 'KRW', 'numeric_code' => '410', 'name' => 'South Korean Won', 'symbol' => '₩', 'decimal_places' => 0],

            // Americas
            ['code' => 'MXN', 'numeric_code' => '484', 'name' => 'Mexican Peso', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'BRL', 'numeric_code' => '986', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'decimal_places' => 2],
            ['code' => 'ARS', 'numeric_code' => '032', 'name' => 'Argentine Peso', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'CLP', 'numeric_code' => '152', 'name' => 'Chilean Peso', 'symbol' => '$', 'decimal_places' => 0],
            ['code' => 'COP', 'numeric_code' => '170', 'name' => 'Colombian Peso', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'PEN', 'numeric_code' => '604', 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'decimal_places' => 2],
            ['code' => 'UYU', 'numeric_code' => '858', 'name' => 'Uruguayan Peso', 'symbol' => '$U', 'decimal_places' => 2],
            ['code' => 'BOB', 'numeric_code' => '068', 'name' => 'Bolivian Boliviano', 'symbol' => 'Bs.', 'decimal_places' => 2],

            // Europe
            ['code' => 'SEK', 'numeric_code' => '752', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'decimal_places' => 2],
            ['code' => 'NOK', 'numeric_code' => '578', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'decimal_places' => 2],
            ['code' => 'DKK', 'numeric_code' => '208', 'name' => 'Danish Krone', 'symbol' => 'kr', 'decimal_places' => 2],
            ['code' => 'PLN', 'numeric_code' => '985', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'decimal_places' => 2],
            ['code' => 'CZK', 'numeric_code' => '203', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'decimal_places' => 2],
            ['code' => 'HUF', 'numeric_code' => '348', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'decimal_places' => 2],
            ['code' => 'RON', 'numeric_code' => '946', 'name' => 'Romanian Leu', 'symbol' => 'lei', 'decimal_places' => 2],
            ['code' => 'BGN', 'numeric_code' => '975', 'name' => 'Bulgarian Lev', 'symbol' => 'лв', 'decimal_places' => 2],
            ['code' => 'ISK', 'numeric_code' => '352', 'name' => 'Icelandic Krona', 'symbol' => 'kr', 'decimal_places' => 0],
            ['code' => 'RSD', 'numeric_code' => '941', 'name' => 'Serbian Dinar', 'symbol' => 'дин.', 'decimal_places' => 2],
            ['code' => 'UAH', 'numeric_code' => '980', 'name' => 'Ukrainian Hryvnia', 'symbol' => '₴', 'decimal_places' => 2],
            ['code' => 'TRY', 'numeric_code' => '949', 'name' => 'Turkish Lira', 'symbol' => '₺', 'decimal_places' => 2],

            // Oceania / Pacific
            ['code' => 'FJD', 'numeric_code' => '242', 'name' => 'Fijian Dollar', 'symbol' => 'FJ$', 'decimal_places' => 2],
            ['code' => 'PGK', 'numeric_code' => '598', 'name' => 'Papua New Guinean Kina', 'symbol' => 'K', 'decimal_places' => 2],
            ['code' => 'WST', 'numeric_code' => '882', 'name' => 'Samoan Tala', 'symbol' => 'T', 'decimal_places' => 2],
            ['code' => 'TOP', 'numeric_code' => '776', 'name' => 'Tongan Paʻanga', 'symbol' => 'T$', 'decimal_places' => 2],
            ['code' => 'VUV', 'numeric_code' => '548', 'name' => 'Vanuatu Vatu', 'symbol' => 'VT', 'decimal_places' => 0],
            ['code' => 'SBD', 'numeric_code' => '090', 'name' => 'Solomon Islands Dollar', 'symbol' => 'SI$', 'decimal_places' => 2],

            // Other commonly encountered currencies
            ['code' => 'ILS', 'numeric_code' => '376', 'name' => 'Israeli New Shekel', 'symbol' => '₪', 'decimal_places' => 2],
            ['code' => 'JOD', 'numeric_code' => '400', 'name' => 'Jordanian Dinar', 'symbol' => 'د.ا', 'decimal_places' => 3],
            ['code' => 'LBP', 'numeric_code' => '422', 'name' => 'Lebanese Pound', 'symbol' => 'ل.ل', 'decimal_places' => 2],
            ['code' => 'IQD', 'numeric_code' => '368', 'name' => 'Iraqi Dinar', 'symbol' => 'ع.د', 'decimal_places' => 3],
            ['code' => 'IRR', 'numeric_code' => '364', 'name' => 'Iranian Rial', 'symbol' => '﷼', 'decimal_places' => 2],
            ['code' => 'AFN', 'numeric_code' => '971', 'name' => 'Afghan Afghani', 'symbol' => '؋', 'decimal_places' => 2],
            ['code' => 'KZT', 'numeric_code' => '398', 'name' => 'Kazakhstani Tenge', 'symbol' => '₸', 'decimal_places' => 2],
            ['code' => 'UZS', 'numeric_code' => '860', 'name' => 'Uzbekistani Som', 'symbol' => 'сўм', 'decimal_places' => 2],
            ['code' => 'GEL', 'numeric_code' => '981', 'name' => 'Georgian Lari', 'symbol' => '₾', 'decimal_places' => 2],
            ['code' => 'AZN', 'numeric_code' => '944', 'name' => 'Azerbaijani Manat', 'symbol' => '₼', 'decimal_places' => 2],
            ['code' => 'AMD', 'numeric_code' => '051', 'name' => 'Armenian Dram', 'symbol' => '֏', 'decimal_places' => 2],
        ];

        $now = now();

        $currencies = array_map(function ($currency) use ($now) {
            return array_merge($currency, [
                'is_default' => $currency['is_default'] ?? false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $currencies);

        DB::table('currencies')->upsert(
            $currencies,
            ['code'],
            [
                'numeric_code',
                'name',
                'symbol',
                'decimal_places',
                'is_default',
                'is_active',
                'updated_at',
            ]
        );
    }
}
