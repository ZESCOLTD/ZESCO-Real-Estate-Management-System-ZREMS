<?php declare(strict_types=1);

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self pending()
 * @method static self inProgress()
 * @method static self extended()
 * @method static self closed()
 */
final class TaskStatus extends Enum
{
    protected static function values(): array
    {
        return [
            'pending' => 0,
            'inProgress' => 1,
            'extended' => 2,
            'closed' => 3,
        ];
    }

    protected static function labels(): array
    {
        return [
            'pending' => 'Pending',
            'inProgress' => 'In Progress',
            'extended' => 'Extended',
            'closed' => 'Closed',
        ];
    }
}
