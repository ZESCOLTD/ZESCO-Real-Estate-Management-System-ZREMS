<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;
use Throwable;

class SpmsPurchaseProcessService
{
    public function __construct() {}

    public static function createPurchaseProcess($reference, $formOrderNum, $account, $user,
        $storeCode = 'ZH',
        $jobCardNo = '66',
        $deliverySite = 'LAND IN CHIPATA WEST'): string
    {
        $results = null;

        try {

            Log::info('Generating Purchase Process For Request '.$reference);

            $transactionType = '04';
            $originSystem = '04';
            $userName = $user->man_no;
            $str = null;

            Log::debug(':p_reference:'.$reference);
            Log::debug(':p_store_code:'.$storeCode);
            Log::debug(':p_user_requesting:'.$userName);
            Log::debug(':p_system_origin:'.$originSystem);
            Log::debug(':p_form_order:'.$formOrderNum);
            Log::debug(':p_req_account:'.$account);
            Log::debug(':p_d'.$deliverySite);
            Log::debug(':p_transaction_type:'.$transactionType);
            Log::debug(':p_current_user:'.$userName);

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare('begin :result := FN_CREATE_PUR_PROCESS_TEST(
            :p_reference,

            :p_store_code,
            :p_user_requesting,

            :p_system_origin,
            :p_form_order,
            :p_req_account,
            :p_delivery_site,
            :p_transaction_type,
            :p_current_user); end;');

            if (! $stmt) {
                throw new RuntimeException('Unable to create purchase process: statement preparation failed.');
            }

            $stmt->bindParam(':p_reference', $reference);

            $stmt->bindParam(':p_store_code', $storeCode);
            $stmt->bindParam(':p_user_requesting', $userName);
            $stmt->bindParam(':p_system_origin', $originSystem);
            $stmt->bindParam(':p_form_order', $formOrderNum);
            $stmt->bindParam(':p_req_account', $account);
            $stmt->bindParam(':p_delivery_site', $deliverySite);
            $stmt->bindParam(':p_transaction_type', $transactionType);
            $stmt->bindParam(':p_current_user', $userName);

//            $stmt->bindValue(':result', $results, PDO::PARAM_STR, 4000);
            $stmt->bindParam(':result', $results, PDO::PARAM_STR, 4000);
            if (! $stmt->execute()) {
                throw new RuntimeException('Unable to create purchase process: statement execution failed.');
            }

            $result = is_array($results) && ! empty($results) ? $results[0] : $results;

            if (! is_string($result) || $result === '') {
                throw new RuntimeException('Unable to create purchase process: empty response from database function.');
            }

            Log::info($result);

            if (str_starts_with($result, '0')) {
                return substr($result, 1);
            }

            return $result;
        } catch (Throwable $e) {
            Log::error('Failed creating purchase process', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return '';
        }
    }
}
