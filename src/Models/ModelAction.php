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

    protected $casts = [
        'status' => Status::class,
        'properties' => 'collection',
        'act_at' => 'datetime',
        'finished_at' => 'datetime'
    ];

    public function actionable()
    {
        return $this->morphTo();
    }

    public function scopeFinished($query)
    {
        return $query->where('status', Status::FINISHED);
    }
}
