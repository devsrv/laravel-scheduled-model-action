<?php

namespace Devsrv\ScheduledAction;

use InvalidArgumentException;
use Devsrv\ScheduledAction\Enums\Days;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;

class Util
{
    public static function validateDays(array $days) {
        $daysEnumCollection = collect($days)->map(function($d) {
            try {
                $casted = Days::fromKey($d);
                return $casted;
            } catch (InvalidEnumKeyException $th) {
                throw new InvalidArgumentException('recurring day must be type '. Days::class);
            }
        });

        return $daysEnumCollection;
    }
}