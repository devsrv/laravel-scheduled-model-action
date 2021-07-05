<?php

namespace Devsrv\ScheduledAction\Models;

use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Days;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\{Arr, Collection};
use Devsrv\ScheduledAction\Models\ModelAction;

class RecurringAction extends Model
{
    use HasFactory;
    
    protected $table = 'model_action_recurring';

    protected $guarded = [];

    protected $casts = [
        'day' => Days::class
    ];

    public function action()
    {
        return $this->belongsTo(ModelAction::class, 'schedule_id');
    }
}
