<?php

namespace App\Enums\Asset;

use App\HasEnumHelpers;

enum BuiltAssetType: int
{
    use HasEnumHelpers;
    case Building = 1;
    case Office = 2;
    case Substation = 3;
    case TransmissionTower = 4;
    case Machinery = 5;
    case Equipment = 6;
    case Vehicle = 7;

    case Other = 8;

    public function label(): string
    {
        return match ($this) {
            self::Building => 'Building',
            self::Office => 'Office',
            self::Substation => 'Substation',
            self::TransmissionTower => 'Transmission Tower',
            self::Machinery => 'Machinery',
            self::Equipment => 'Equipment',
            self::Vehicle => 'Vehicle',
            self::Other => 'Other',
        };
    }
}
