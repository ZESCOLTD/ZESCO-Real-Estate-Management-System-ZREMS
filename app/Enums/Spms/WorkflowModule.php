<?php

namespace App\Enums\Spms;

enum WorkflowModule: string
{
    const DRIVER_ONBOARDING = 'DRV_ONBOARD';

    const FUEL_REQUISITION = 'FUEL_REQ';

    const WORKSHOP_REQUISITION = 'SPARES_REQ';

    const STOCK_REQUISITION = 'STR';

    const PURCHASE_REQUISITION = 'PUR';

    const ACCIDENT_REPORT = 'ACC_RPT';

    const IMPREST_BUY = 'IMP_BUY';
}
