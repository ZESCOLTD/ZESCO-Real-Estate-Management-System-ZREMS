<?php
/**
 * Run ALL new service seeders for the September deployment.
 * Idempotent — each individual seeder uses updateOrCreate + skips if fields exist.
 *
 * Usage on test server after `git pull`:
 *   php scripts/run-all-september-seeders.php
 */

$scripts = [
    // Property Department
    'seed-property-wayleave-registration.php',
    'seed-property-easement-registration.php',
    'reseed-property-easement-registration.php',
    'seed-property-mortgage-securities.php',
    'reseed-property-mortgage-securities.php',
    'seed-property-compliance-governance.php',
    'seed-property-right-of-use.php',
    'seed-property-sublease-management.php',
    'seed-property-asset-lease-management.php',
    'seed-property-land-disposal.php',
    'seed-property-change-of-use.php',
    'seed-property-construction-permit.php',
    // Security Department
    'seed-security-encroachment.php',
];

$base = __DIR__;
$failures = [];

foreach ($scripts as $s) {
    $path = $base . DIRECTORY_SEPARATOR . $s;
    if (!file_exists($path)) {
        echo "SKIP (not found): {$s}\n";
        continue;
    }
    echo "\n============================================================\n";
    echo "▶ Running: {$s}\n";
    echo "============================================================\n";
    $cmd = 'php ' . escapeshellarg($path);
    passthru($cmd, $code);
    if ($code !== 0) {
        $failures[] = $s;
        echo "  ⚠ Exit code {$code}\n";
    }
}

echo "\n\n============================================================\n";
if (empty($failures)) {
    echo "✅ All seeders completed successfully.\n";
} else {
    echo "⚠ The following seeders reported failures:\n";
    foreach ($failures as $f) echo "   - {$f}\n";
}
echo "============================================================\n";
