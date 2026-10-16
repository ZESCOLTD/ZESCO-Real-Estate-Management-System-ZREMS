<?php
/**
 * Sync all service_parents (departments) from local snapshot into target DB.
 * Idempotent: uses updateOrCreate on (department_code, department_name).
 * Safe to run on test/prod repeatedly — will not create duplicates.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;

$rows = [
    ['id' => 1,  'department_code' => 'geomatic_services',            'department_name' => 'Geomatics',                 'service_name' => 'wayleave'],
    ['id' => 2,  'department_code' => 'geomatic_services',            'department_name' => 'GEOMATIC',                  'service_name' => 'Cadastral Survey'],
    ['id' => 4,  'department_code' => 'Lines_Wayleaves',              'department_name' => 'Lines / Wayleaves',         'service_name' => 'First Default Services'],
    ['id' => 5,  'department_code' => 'Spatial Planning Department',  'department_name' => 'Spatial Planning Department','service_name' => 'First Default Services'],
    ['id' => 9,  'department_code' => 'security_services',            'department_name' => 'Security Department',       'service_name' => 'Security Due Diligence Services'],
    ['id' => 16, 'department_code' => 'property_department',          'department_name' => 'Property Department',       'service_name' => 'Conveyance Services'],
    ['id' => 19, 'department_code' => 'Sheq Services',                'department_name' => 'SHEQ Department',           'service_name' => 'SHEQ Checklist'],
    ['id' => 21, 'department_code' => 'valuation',                    'department_name' => 'Valuation',                 'service_name' => 'Valuation Services'],
    ['id' => 22, 'department_code' => 'Litigation Services',          'department_name' => 'Litigation Department',     'service_name' => 'Legal Services'],
    ['id' => 24, 'department_code' => '5',                            'department_name' => 'Finance Department',        'service_name' => 'Fixed Asset Register - Land & Buildings'],
    ['id' => 26, 'department_code' => '78',                           'department_name' => 'Commercial Contracts',      'service_name' => 'Contract Review'],
    ['id' => 28, 'department_code' => '98',                           'department_name' => 'Procurement Department',    'service_name' => 'Default Service'],
    ['id' => 29, 'department_code' => '5',                            'department_name' => 'Finance Department',        'service_name' => 'Payments Services'],
];

$created = 0; $updated = 0;
foreach ($rows as $r) {
    // Match by (department_name + service_name) to keep Finance's two rows distinct
    $existing = ServiceParent::where('department_name', $r['department_name'])
        ->where('service_name', $r['service_name'])
        ->first();

    if ($existing) {
        $existing->update(['department_code' => $r['department_code']]);
        $updated++;
        echo "UPDATED #{$existing->id}  {$r['department_name']} / {$r['service_name']}" . PHP_EOL;
    } else {
        $new = ServiceParent::create([
            'department_code' => $r['department_code'],
            'department_name' => $r['department_name'],
            'service_name'    => $r['service_name'],
        ]);
        $created++;
        echo "CREATED #{$new->id}  {$r['department_name']} / {$r['service_name']}" . PHP_EOL;
    }
}

echo PHP_EOL . "DONE  created={$created}  updated={$updated}  total_parents=" . ServiceParent::count() . PHP_EOL;
