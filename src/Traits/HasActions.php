<?php

namespace Devsrv\ScheduledAction\Traits;

use Devsrv\ScheduledAction\Models\ModelAction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActions
{
    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($model) {
            $model->scheduledActions()->delete();
        });
    }

    public function scheduledActions(): MorphMany
    {
        return $this->morphMany(ModelAction::class, 'actionable');
    }
}