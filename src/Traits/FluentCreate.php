<?php

namespace Devsrv\ScheduledAction\Traits;

use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;

trait FluentCreate
{
    protected $forModel;
    protected $actDate = null;
    protected $actTime;
    protected $actWith;
    protected $actionStatus = null;
    protected $props = [];
    protected $recurringDays = [];

    public static function for(Model $model) {
        return (new static)->setModel($model);
    }

    public function actAt(Carbon $carbon) {
        $this->actDate = $carbon;
        $this->actTime = $carbon;
        return $this;
    }

    public function actDate(Carbon $carbon) {
        $this->actDate = $carbon;
        return $this;
    }

    public function actTime(Carbon $carbon) {
        $this->actTime = $carbon;
        return $this;
    }

    public function actWith(string $by) {
        $this->actWith = $by;
        return $this;
    }

    public function asPending() {
        $this->actionStatus = Status::PENDING;
        return $this;
    }

    public function asFinished() {
        $this->actionStatus = Status::FINISHED;
        return $this;
    }

    public function asDispatched() {
        $this->actionStatus = Status::DISPATCHED;
        return $this;
    }

    public function asCancelled() {
        $this->actionStatus = Status::CANCELLED;
        return $this;
    }

    public function setExtraProperties(array $properties) {
        $this->props = $properties;
        return $this;
    }

    public function setModel(Model $model) {
        $this->forModel = $model;
        return $this;
    }

    public function createSchedule() {
        $this->validateBeforeCreate();

        $schedule = $this->create([
            'actionable_type' => get_class($this->forModel),
            'actionable_id' => ($this->forModel)->getKey(),
            'action' => $this->actWith,
            'properties' => $this->props,
            'status' => $this->actionStatus ?? Status::PENDING,
            'act_date' => $this->actDate ? $this->actDate->toDateString() : now()->toDateString(),
            'act_time' => $this->actTime->toTimeString()
        ]);

        return $schedule;
    }

    private function validateBeforeCreate() {
        throw_if(! isset($this->forModel, $this->actWith, $this->actTime), new InvalidArgumentException('required model attribute missing'));
    }
}