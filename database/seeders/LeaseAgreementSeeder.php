<?php

namespace Database\Seeders;

use App\Models\LeaseAgreement;
use App\Models\LeaseStatus;
use App\Models\Province;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeaseAgreementSeeder extends Seeder
{
    public function run(): void
    {
        $officerId = User::query()->orderBy('id')->value('id');
        if (! $officerId) {
            throw new RuntimeException('Create at least one user before seeding sample lease agreements.');
        }

        $statusIds = LeaseStatus::pluck('id', 'code');

        // Reuse existing province rows, including names like "Lusaka Province".
        // Create the two provinces if they are not yet in the table.
        $provinceIds = [];

        foreach (['LUSAKA' => 'Lusaka', 'COPPERBELT' => 'Copperbelt'] as $key => $name) {
            $province = Province::whereRaw(
                'LOWER(name) IN (?, ?)',
                [strtolower($name), strtolower($name) . ' province']
            )->first();

            $provinceIds[$key] = ($province ?? Province::create(['name' => $name]))->id;
        }

        $rows = [
            [
                'description' => 'Contract between ZESCO Limited and NATIONAL PENSION AUTHORITY SCHEME to lease out Shop No: 69A (153.8sqm) on the first floor of Levy Business Park.',
                'landlord' => 'NATIONAL PENSION AUTHORITY SCHEME',
                'phone' => '0977 572367',
                'town' => 'Lusaka',
                'province' => 'LUSAKA',
                'start' => '2017-01-01',
                'end' => '2026-12-31',
                'monthly' => '47675.09',
                'status' => 'ongoing',
                'comment' => 'Lease renewal process initiated. Submited to the landlord for execution on their part',
            ],
            [
                'description' => 'Contract between ZESCO Limited and TOTAL (Z) Limited to lease out Propert no: MAT/73 - Common Wealthy Road - Matero - 1,914 m^2',
                'landlord' => 'TOTAL (Z) Limited',
                'phone' => '0969 587456',
                'town' => 'Lusaka',
                'province' => 'LUSAKA',
                'start' => '2024-01-01',
                'end' => '2029-09-30',
                'monthly' => '16392.20',
                'status' => 'lease_to_be_executed',
                'comment' => null,
            ],
            [
                'description' => 'Contract between ZESCO Limited and AGRICULTURAL And COMMERCIAL COOPERATIVE SOCIETY OF ZAMBIA Limited',
                'landlord' => 'AGRICULTURAL And COMMERCIAL COOPERATIVE SOCIETY OF ZAMBIA Limited',
                'phone' => '0977 809152',
                'town' => 'Lusaka',
                'province' => 'LUSAKA',
                'start' => '2025-01-01',
                'end' => '2025-12-31',
                'monthly' => '7600.00',
                'status' => 'expired',
                'comment' => 'Lease not yet executed',
            ],
            [
                'description' => 'Contract between ZESCO Limited  and ZAMBEZI RIVER AUTHORITY to lease out Shop no:02 ground and first floor, stand no: 32 Chacha Road, Kariba House Lusaka.',
                'landlord' => 'ZAMBEZI RIVER AUTHORITY',
                'phone' => '0211-226950/227970/3',
                'town' => 'Lusaka',
                'province' => 'LUSAKA',
                'start' => '2018-01-01',
                'end' => '2022-12-31',
                'monthly' => '2087.54',
                'status' => 'lease_to_be_renewed',
                'comment' => 'Lease payment in USD',
            ],
            [
                'description' => 'Contract between ZESCO Limited - and ZAMPOST, Chilanga',
                'landlord' => 'ZAMPOST',
                'phone' => '0977 388497',
                'town' => 'Chilanga',
                'province' => 'LUSAKA',
                'start' => '2020-01-01',
                'end' => '2020-12-31',
                'monthly' => '6930.00',
                'status' => 'expired',
                'comment' => null,
            ],
            [
                'description' => 'Contract between ZESCO Limited and IVEN MULENGA Limited to lease out Stand No:3470/20 (House No: 127) Nkhana East Kantanta Street.',
                'landlord' => 'IVEN MULENGA Limited',
                'phone' => '0955 885638',
                'town' => 'Kitwe',
                'province' => 'COPPERBELT',
                'start' => '2025-01-01',
                'end' => '2030-11-30',
                'monthly' => '10400.00',
                'status' => 'awaiting_landlord_execution',
                'comment' => null,
            ],
            [
                'description' => 'Contract between ZESCO Limited - and ZAMBIA INTERNATIONAL TRADE FAIR Leasing of 49 Hectars, Plot No:2735, Stand no: 08, Light Industrial Area, Ndola',
                'landlord' => 'ZAMBIA INTERNATIONAL TRADE FAIR',
                'phone' => '0763 561703',
                'town' => 'Ndola',
                'province' => 'COPPERBELT',
                'start' => null,
                'end' => null,
                'monthly' => '3500.00',
                'status' => 'awaiting_landlord_execution',
                'comment' => null,
            ],
            [
                'description' => 'Contract between ZESCO Limited and Coach Rank Company Limited - Ndola',
                'landlord' => 'Coach Rank Company Limited',
                'phone' => '0966 749511',
                'town' => 'Ndola',
                'province' => 'COPPERBELT',
                'start' => null,
                'end' => null,
                'monthly' => '7200.00',
                'status' => 'executed',
                'comment' => null,
            ],
            [
                'description' => 'Contract between ZESCO Limited and LYBERIA KOYA to lease out House no:2 Presidential Drive Chililabombwe.',
                'landlord' => 'LYBERIA KOYA',
                'phone' => '0971 926567',
                'town' => 'Chilabombwe',
                'province' => 'COPPERBELT',
                'start' => '2026-01-01',
                'end' => '2026-12-31',
                'monthly' => '5000.00',
                'status' => 'executed',
                'comment' => null,
            ],
        ];

        DB::transaction(function () use ($rows, $officerId, $statusIds, $provinceIds) {
            foreach ($rows as $row) {
                if (! isset($statusIds[$row['status']])) {
                    throw new RuntimeException("Missing lease status: {$row['status']}");
                }

                $start = $row['start'] ? new DateTimeImmutable($row['start']) : null;
                $end = $row['end'] ? new DateTimeImmutable($row['end']) : null;

                $duration = $start && $end
                    ? $start->format('d F Y') . ' to ' . $end->format('d F Y')
                    : null;

                // Include the end date, then express the duration to two decimal years.
                $tenureYears = $start && $end
                    ? round($start->diff($end->modify('+1 day'))->days / 365.25, 2)
                    : null;

                $monthlyCents = (int) round((float) $row['monthly'] * 100);
                $annualPayment = number_format(
                    ($monthlyCents * 12) / 100,
                    2,
                    '.',
                    ''
                );

                // Source row numbers are not forced into the database ID column.
                LeaseAgreement::firstOrCreate(
                    ['contract_description' => $row['description']],
                    [
                        'landlord_name' => $row['landlord'],
                        'landlord_contact_phone' => $row['phone'],
                        'town' => $row['town'],
                        'province_id' => $provinceIds[$row['province']],
                        'lease_duration' => $duration,
                        'lease_tenure_years' => $tenureYears,
                        'monthly_payment' => $row['monthly'],
                        'annual_payment' => $annualPayment,
                        'start_date' => $row['start'],
                        'end_date' => $row['end'],
                        'lease_status_id' => $statusIds[$row['status']],
                        'comment' => $row['comment'],
                        'responsible_officer_id' => $officerId,
                    ]
                );
            }
        });
    }
}