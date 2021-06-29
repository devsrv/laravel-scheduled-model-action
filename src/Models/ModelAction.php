<?php

namespace Devsrv\ScheduledAction\Models;

use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModelAction extends Model
{
    use HasFactory;

    protected $table = 'model_actions';

    protected $guarded = [];

    public function actionable()
    {
        return $this->morphTo();
    }

    protected $casts = [
        'status' => Status::class,
        'act_at' => 'datetime',
        'finished_at' => 'datetime'
    ];
}
