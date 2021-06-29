<?php

namespace Devsrv\ScheduledAction\Facades;

use Illuminate\Support\Facades\Facade;
use Devsrv\ScheduledAction\Action as ActionCore;

class Action extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActionCore::class;
    }
}
