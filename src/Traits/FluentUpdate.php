<?php

namespace Devsrv\ScheduledAction\Traits;

use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Devsrv\ScheduledAction\Enums\Status;

trait FluentUpdate
{
    public function setPending()
    {
        $this->status = Status::PENDING;
        $this->finished_at = null;
        return $this;
    }

    public function setFinished(DateTime $at = null)
    {
        $this->status = Status::FINISHED;
        $this->finished_at = $at ?? now();
        return $this;
    }

    public function setCancelled()
    {
        $this->status = Status::CANCELLED;
        $this->finished_at = null;
        return $this;
    }

    public function setDispatched()
    {
        $this->status = Status::DISPATCHED;
        $this->finished_at = null;
        return $this;
    }

    public function setActAt(Carbon $carbon)
    {
        $this->act_date = $carbon->toDateString();
        $this->act_time = $carbon->toTimeString();
        return $this;
    }

    public function setActDate(Carbon $carbon)
    {
        $this->act_date = $carbon->toDateString();
        return $this;
    }

    public function setActTime(Carbon $carbon)
    {
        $this->act_time = $carbon->toTimeString();
        return $this;
    }

    public function mergeExtraProperties($properties)
    {
        if(! $properties instanceof Collection) $properties = collect($properties);

        $this->properties = $this->properties->merge($properties);

        return $this;
    }

    public function withExtraProperties($properties)
    {
        if(! $properties instanceof Collection) $properties = collect($properties);

        $this->properties = $properties;

        return $this;
    }
}