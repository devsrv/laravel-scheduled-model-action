<?php

namespace Devsrv\ScheduledAction\Models;

use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Devsrv\ScheduledAction\Traits\{ActionStatus, FluentUpdate, FluentCreate};

class ModelAction extends Model
{
    use HasFactory, ActionStatus, FluentUpdate, FluentCreate;
    
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

    public function getExtraProperty(string $propertyName): mixed
    {
        return Arr::get($this->properties->toArray(), $propertyName);
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

    public function scopeWhereExtraProperty($query, $prop, $value)
    {
        return $query->whereJsonContains('properties->' . $prop, $value);
    }

    public function scopeWhereExtraProperties($query, array $properties)
    {
        collect($properties)
        ->each(fn($value, $key) => $query->whereJsonContains('properties->' . $key, $value));

        return $query;
    }

    public function scopeOrderByWhenToAct($query, string $direction = 'asc')
    {
        return $query->orderBy('act_at', $direction !== 'asc' ? 'desc' : 'asc');
    }

    public function scopeFor($query, Model $model)
    {
        return $query
            ->where(function ($query) use($model) {
                $query
                ->where('actionable_type', $model->getMorphClass())
                ->where('actionable_id', $model->getKey());
        });
    }

    public function scopeForClass($query, string $model)
    {
        return $query->whereHasMorph('actionable', $model);
    }
}
