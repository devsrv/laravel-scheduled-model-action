<?php

namespace Devsrv\ScheduledAction\Traits;

use Carbon\Carbon;
use InvalidArgumentException;
use Devsrv\ScheduledAction\Util;
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

    public function setFinished(DateTime|null $at = null)
    {
        $this->status = Status::FINISHED;
        $this->finished_at = $at ?? Carbon::now();
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

    public function setActOn(Carbon $carbon)
    {
        $this->act_on = $carbon->toDateString();
        $this->act_at = $carbon->toTimeString();
        return $this;
    }

    public function setActDate(Carbon $carbon)
    {
        $this->act_on = $carbon->toDateString();
        return $this;
    }

    public function setActTime(Carbon $carbon)
    {
        $this->act_at = $carbon->toTimeString();
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

    public function setNonRecurring()
    {
        $this->recurring = 0;
        $this->recurringDays()->delete();

        return $this;
    }

    private function switchToRecurring(array $daysList)
    {
        throw_unless(count($daysList), new InvalidArgumentException('at least one recurring day required'));

        $this->recurring = 1;
        $this->act_on = null;
    }

    private function createRecurringDaysIfNeeded(array $days, array $current) {
        $toCreate = array_diff($days, $current);

        if(count($toCreate)) {
            $this->recurringDays()->createMany(
                collect($toCreate)->map(fn($d) => ['day' => $d])->all()
            );
        }
    }

    private function createNewRecurringDays(array $days) {
        $this->recurringDays()->createMany(
            collect($days)->map(fn($d) => ['day' => $d])->all()
        );
    }

    public function markAsRecurringRunsOnEvery(array $daysList)
    {
        $this->switchToRecurring($daysList);

        $days = Util::validateDays($daysList)->all();

        if($this->recurringDays()->exists()) {
            $current = $this->recurringDays()->pluck('day')->all();

            $this->createRecurringDaysIfNeeded($days, $current);

            $toDelete = array_diff($current, $days);

            if(count($toDelete)) {
                $this->recurringDays()->whereIn('day', $toDelete)->delete();
            }

            return;
        }

        $this->createNewRecurringDays($days);

        $this->push();
    }

    public function syncWithoutDetachingRunsOnEvery(array $daysList) {
        $this->switchToRecurring($daysList);

        $days = Util::validateDays($daysList)->all();

        if($this->recurringDays()->exists()) {
            $current = $this->recurringDays()->pluck('day')->all();

            $this->createRecurringDaysIfNeeded($days, $current);

            return;
        }

        $this->createNewRecurringDays($days);

        $this->push();
    }
}