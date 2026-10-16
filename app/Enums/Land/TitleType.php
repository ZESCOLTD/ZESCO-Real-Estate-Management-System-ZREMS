<?php

namespace App\Enums\Land;

use App\HasEnumHelpers;

enum TitleType: int
{
    use HasEnumHelpers;
    case Deed = 1;
    case Certificate = 2;
    case Title = 3;
    case OfferLetter = 4;
    case None = 5;

    public function label(): string
    {
        return match ($this) {
            self::Deed => 'Deed',
            self::Certificate => 'Certificate',
            self::Title => 'Title',
            self::OfferLetter => 'Offer Letter',
            self::None => 'No Title',
        };
    }
}
