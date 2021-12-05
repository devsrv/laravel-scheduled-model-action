<?php

namespace Devsrv\ScheduledAction\Traits;

use Devsrv\ScheduledAction\Enums\Status;

trait ActionStatus
{
    public function getIsPendingAttribute() : bool
    {
        return $this->status == Status::PENDING;
    }

    public function getIsFinishedAttribute() : bool
    {
        return $this->status == Status::FINISHED;
    }

    public function getIsCancelledAttribute() : bool
    {
        return $this->status == Status::CANCELLED;
    }

    public function getIsDispatchedAttribute() : bool
    {
        return $this->status == Status::DISPATCHED;
    }

    public function scopeFinished($query)
    {
        return $query->where('status', Status::FINISHED);
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PENDING);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::CANCELLED);
    }

    public function scopeDispatched($query)
    {
        return $query->where('status', Status::DISPATCHED);
    }
}