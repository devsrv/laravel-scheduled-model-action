<?php

namespace Devsrv\ScheduledAction\Traits;

use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use InvalidArgumentException;


trait FluentCreate
{
    private static $forModel;
    private static $actAt;
    private static $actBy;
    private static $actionStatus = null;
    private static $props = [];

    public static function forModel(Model $model) {
        self::$forModel = $model;
        return new self;
    }

    public static function actAt(DateTime $at) {
        self::$actAt = $at;
        return new self;
    }

    public static function actBy(string $by) {
        self::$actBy = $by;
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

    public function createSchedule() {
        $this->beforeCreate();

        $this->create([
            'actionable_type' => get_class(self::$forModel),
            'actionable_id' => (self::$forModel)->getKey(),
            'action' => self::$actBy,
            'properties' => self::$props,
            'status' => self::$actionStatus ?? Status::PENDING,
            'act_at' => self::$actAt,
        ]);
    }

    private function beforeCreate() {
        throw_if(! isset(self::$forModel, self::$actBy, self::$actAt), new InvalidArgumentException('model attribute missing'));
    }
}