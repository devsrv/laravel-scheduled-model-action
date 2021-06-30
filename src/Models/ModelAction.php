<?php

namespace Devsrv\ScheduledAction\Models;

use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

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

    private static function today() : string {
        return Carbon::now()->toDateString();
    }

    public function actionable()
    {
        return $this->morphTo();
    }

    public function scopeFinished($query)
    {
        return $query->where('status', Status::FINISHED);
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PENDING);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::CANCELLED);
    }

    public function scopeDispatched($query)
    {
        return $query->where('status', Status::DISPATCHED);
    }

    public function scopeToActToday($query)
    {
        return $query->whereDate('act_at', self::today());
    }

    public function scopeToActBetween($query, $start, $end)
    {
        return $query->whereBetween('act_at', [$start, $end]);
    }

    public function scopeWhereProperty($query, $prop, $value)
    {
        return $query->whereJsonContains('properties->' . $prop, $value);
    }

    public function scopeWhereProperties($query, array $properties)
    {
        collect($properties)
        ->each(fn($value, $key) => $query->whereJsonContains('properties->' . $key, $value));

        return $query;
    }
}
