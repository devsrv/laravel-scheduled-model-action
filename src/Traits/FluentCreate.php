<?php

namespace Devsrv\ScheduledAction\Traits;

use Carbon\Carbon;
use InvalidArgumentException;
use Devsrv\ScheduledAction\Util;
use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\{Status, Days};

trait FluentCreate
{
    private static $forModel;
    private static $actDate = null;
    private static $actTime;
    private static $actWith;
    private static $actionStatus = null;
    private static $props = [];
    private static $recurringDays = [];

    public static function forModel(Model $model) {
        self::$forModel = $model;
        return new self;
    }

    public static function actAt(Carbon $carbon) {
        self::$actDate = $carbon;
        self::$actTime = $carbon;
        return new self;
    }

    public static function actDate(Carbon $carbon) {
        self::$actDate = $carbon;
        return new self;
    }

    public static function actTime(Carbon $carbon) {
        self::$actTime = $carbon;
        return new self;
    }

    public static function actWith(string $by) {
        self::$actWith = $by;
        return new self;
    }

    public static function asPending() {
        self::$actionStatus = Status::PENDING;
        return new self;
    }

    public static function asFinished() {
        self::$actionStatus = Status::FINISHED;
        return new self;
    }
    public static function asDispatched() {
        self::$actionStatus = Status::DISPATCHED;
        return new self;
    }
    public static function asCancelled() {
        self::$actionStatus = Status::CANCELLED;
        return new self;
    }

    public static function setExtraProperties(array $properties) {
        self::$props = $properties;
        return new self;
    }

    public static function runsOnEvery(array $days) {
        self::$recurringDays = $days;
        return new self;
    }

    public function createSchedule() {
        $this->beforeCreate();

        $is_recurring = (bool) count(self::$recurringDays);

        $action = $this->create([
            'actionable_type' => get_class(self::$forModel),
            'actionable_id' => (self::$forModel)->getKey(),
            'action' => self::$actWith,
            'properties' => self::$props,
            'status' => self::$actionStatus ?? Status::PENDING,
            'act_on' => $is_recurring ? null : ( self::$actDate ? self::$actDate->toDateString() : null ),
            'act_at' => self::$actTime->toTimeString(),
            'recurring' => $is_recurring
        ]);

        if($is_recurring) {
            $action->recurringDays()->createMany(
                collect(self::$recurringDays)->map(fn($d) => ['day' => $d])->all()
            );
        }
    }

    private function beforeCreate() {
        throw_if(! isset(self::$forModel, self::$actWith, self::$actTime), new InvalidArgumentException('model attribute missing'));

        if(count(self::$recurringDays)) { Util::validateDays(self::$recurringDays); }
    }
}