<?php

namespace App\Enums\Shared;

use App\HasEnumHelpers;

enum Province: int
{
    use HasEnumHelpers;

    case Central = 1;
    case Copperbelt = 2;
    case Eastern = 3;
    case Luapula = 4;
    case Lusaka = 5;
    case Muchinga = 6;
    case Northern = 7;
    case NorthWestern =8;
    case Southern = 9;
    case Western = 10;

    public function label(): string
    {
        return match ($this) {
            self::Central => 'Central Province',
            self::Copperbelt => 'Copperbelt Province',
            self::Eastern => 'Eastern Province',
            self::Luapula => 'Luapula Province',
            self::Lusaka => 'Lusaka Province',
            self::Muchinga => 'Muchinga Province',
            self::Northern => 'Northern Province',
            self::NorthWestern => 'North-Western Province',
            self::Southern => 'Southern Province',
            self::Western => 'Western Province',
        };
    }
}
