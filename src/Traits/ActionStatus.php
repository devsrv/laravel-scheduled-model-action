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

    public function scopeisRecurring() : bool
    {
        return $this->recurring;
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

    public function scopeRecurring($query)
    {
        return $query->where('recurring', 1);
    }
}