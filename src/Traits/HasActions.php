<?php

namespace Devsrv\ScheduledAction\Traits;

use Devsrv\ScheduledAction\Models\ModelAction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActions
{
    public function modelAction(): MorphMany
    {
        return $this->morphMany(ModelAction::class, 'actionable');
    }
}