<?php

namespace App\Console\Commands;

use App\Enums\Spms\WorkflowModule;
use App\Models\User;
use App\Services\GenMaterialsService;
use App\Services\SpmsDocumentNumberGenerationService;
use App\Services\SpmsPurchaseProcessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurchaseProcessCommand extends Command
{
    protected $signature = 'purchase:process';

    protected $description = 'Command description';

    public function handle(): int
    {

        $validated = [
            'reference' => 'REF123',
            'reg_no' => 'REG456',
            'store_code' => 'STORE789',
            'user_requesting' => 'john.doe',
            'job_card_no' => 'JOB001',
            'system_origin' => 'SYSTEM_X',
            'form_order' => 'FORM_A',
            'req_account' => 'REQ_ACC_001',
            'delivery_site' => 'SITE_001',
            'transaction_type' => 'TYPE_001',
        ];

        $user = User::where('id', 1)->first();

        // since article is none stock
        $moduleCode = WorkflowModule::PURCHASE_REQUISITION;

        $formOrder = SpmsDocumentNumberGenerationService::generateReferenceNumber(WorkflowModule::STOCK_REQUISITION, $user);
        $purchaseProcessReference = SpmsDocumentNumberGenerationService::generateReferenceNumber(WorkflowModule::PURCHASE_REQUISITION, $user);

        Log::info('Generated purchase process reference: '.$purchaseProcessReference);
        Log::info('Generated form order number: '.$formOrder);

        GenMaterialsService::createMaterials($purchaseProcessReference, $formOrder);

        $purchaseProcessNumber = SpmsPurchaseProcessService::createPurchaseProcess($purchaseProcessReference, $formOrder, '1196000', $user);

        Log::info('Generated purchase process number: '.$purchaseProcessNumber);

        $spmsReferences = $this->extractSpmsReferences($purchaseProcessNumber);

        if ($spmsReferences !== null) {
            $documentReference = sprintf(
                '%s DOCUMENT NO: %s',
                $spmsReferences['office_code'],
                $spmsReferences['document_no']
            );
            $prReference = 'PR NO: '.$spmsReferences['pr_no'];

            Log::info('Extracted SPMS references', [
                ...$spmsReferences,
                'document_reference' => $documentReference,
                'pr_reference' => $prReference,
            ]);
            $this->info($documentReference.' '.$prReference);
        }

        return self::SUCCESS;
    }

    /**
     * Extract the changing SPMS identifiers from the database response.
     *
     * Example input: ... OFFICE CODE: ZH DOCUMENT NO: N01LR1199754 PR NO: A02LR1064433
     */
    private function extractSpmsReferences(string $response): ?array
    {
        $pattern = '/OFFICE\s+CODE:\s*(?<office_code>[A-Z0-9]+)\s+'
            .'DOCUMENT\s+NO:\s*(?<document_no>[A-Z0-9]+)\s+'
            .'PR\s+NO:\s*(?<pr_no>[A-Z0-9]+)/i';

        if (preg_match($pattern, $response, $matches) !== 1) {
            Log::warning('SPMS references were not found in purchase process response.', [
                'response' => $response,
            ]);

            return null;
        }

        return [
            'office_code' => $matches['office_code'],
            'document_no' => $matches['document_no'],
            'pr_no' => $matches['pr_no'],
        ];
    }
}
