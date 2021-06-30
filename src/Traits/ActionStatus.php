<?php

namespace Devsrv\ScheduledAction\Traits;

use Devsrv\ScheduledAction\Enums\Status;


trait ActionStatus
{
    public function scopeisPending() : bool
    {
        return $this->status == Status::PENDING;
    }

    public function scopeisFinished() : bool
    {
        return $this->status == Status::FINISHED;
    }

    public function scopeisCancelled() : bool
    {
        return $this->status == Status::CANCELLED;
    }

    public function scopeisDispatched() : bool
    {
        return $this->status == Status::DISPATCHED;
    }
}