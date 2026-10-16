<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;
use Throwable;

class SpmsDocumentNumberGenerationService
{
    public function __construct() {}

    public static function generateReferenceNumber($module, $user): string
    {
        $results = null;

        Log::debug('Begin generating reference number', ['module' => $module, 'man_no' => $user->man_no ?? null]);

        try {
            if (! isset($module)) {
                throw new RuntimeException('Unable to generate reference number: missing module.');
            }
            if (! isset($user->man_no)) {
                throw new RuntimeException('Unable to generate reference number: missing user man_no.');
            }

            $userManNo = $user->man_no;


            $pdo = DB::getPdo();
            $stmt = $pdo->prepare('begin :result := fn_generate_reference_number(:p_module, :p_user); end;');

            $stmt->bindParam(':p_module', $module);
            $stmt->bindParam(':p_user', $userManNo);
            $stmt->bindParam(':result', $results, PDO::PARAM_STR, 4000);

            if (! $stmt->execute()) {
                throw new RuntimeException('Unable to generate reference number: statement execution failed.');
            }

            $referenceNumber = is_array($results) ? $results[0] : $results;

            if (! is_string($referenceNumber) || $referenceNumber === '') {
                throw new RuntimeException('Unable to generate reference number: empty result returned by database function.');
            }

            Log::debug('Reference Number generated', ['reference_number' => $referenceNumber]);

            return $referenceNumber;
        } catch (Throwable $e) {
            Log::error('Failed generating reference number', [
                'module' => $module,
                'man_no' => $user->man_no ?? null,
                'message' => $e->getMessage(),
            ]);

            throw new RuntimeException('Reference number generation failed.', 0, $e);
        }
    }
}
