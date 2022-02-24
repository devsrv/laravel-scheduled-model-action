<?php

namespace Devsrv\ScheduledAction\Enums;

enum Status: string
{
    case PENDING = 'PENDING';
    case FINISHED = 'FINISHED';
    case CANCELLED = 'CANCELLED';
    case DISPATCHED = 'DISPATCHED';

    public static function getValues() {
        return array_map(fn($v) => $v->value, static::cases());
    }
}