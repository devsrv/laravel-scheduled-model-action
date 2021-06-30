<?php

namespace Devsrv\ScheduledAction\Traits;

use Devsrv\ScheduledAction\Models\ModelAction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActions
{
    public function scheduledActions(): MorphMany
    {
        return $this->morphMany(ModelAction::class, 'actionable');
    }
}