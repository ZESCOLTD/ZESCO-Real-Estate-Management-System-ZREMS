<?php

namespace App\Enums\Acquisition;

use App\HasEnumHelpers;

enum Recommendation: int
{
    use HasEnumHelpers;
    case StronglyRecommend = 1;
    case Recommend = 2;
    case SuitableWithConditions = 3;
    case Neutral = 4;
    case NotRecommend = 5;

    public function label(): string
    {
        return match ($this) {
            self::StronglyRecommend => 'Strongly Recommend',
            self::Recommend => 'Recommend',
            self::SuitableWithConditions => 'Suitable with Conditions',
            self::Neutral => 'Neutral',
            self::NotRecommend => 'Not Recommend',
        };
    }
}
