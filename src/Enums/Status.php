<?php

namespace Devsrv\ScheduledAction\Enums;

use BenSampo\Enum\Enum;

final class Status extends Enum
{
    const PENDING = 'PENDING';
    const FINISHED = 'FINISHED';
    const CANCELLED = 'CANCELLED';
    const DISPATCHED = 'DISPATCHED';
}