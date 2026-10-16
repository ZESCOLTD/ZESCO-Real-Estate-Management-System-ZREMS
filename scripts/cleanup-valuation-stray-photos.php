<?php
/**
 * Remove over-matched photo_* fields that were accidentally added for
 * non-condition rows (condition_scale_note, condition_photos, condition_notes,
 * condition [top-level]). Safe to re-run.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceFormField;

$bad = [
    'photo_condition',
    'photo_condition_scale_note',
    'photo_condition_photos',
    'photo_condition_notes',
];

$n = ServiceFormField::whereIn('field_name', $bad)->delete();
echo "Removed {$n} stray photo field(s).\n";
