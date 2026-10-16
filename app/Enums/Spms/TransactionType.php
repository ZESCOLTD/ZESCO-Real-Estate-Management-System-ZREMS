<?php

namespace App\Enums\Spms;

enum TransactionType: string
{
    case STORES_REQUISITIONS = '01';
    case SERVICE_PURCHASE_REQUISITIONS = '04';
}
