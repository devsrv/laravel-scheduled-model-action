<?php

namespace Devsrv\ScheduledAction\Models;

use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\{Arr, Collection};
use Devsrv\ScheduledAction\Traits\{ActionStatus, FluentUpdate, FluentCreate};
use Devsrv\ScheduledAction\Models\RecurringAction;

class ModelAction extends Model
{
    use HasFactory, ActionStatus, FluentUpdate, FluentCreate;
    
    protected $table = 'model_actions';

    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
        'properties' => 'collection',
        'act_on' => 'datetime:Y-m-d',
        'act_at' => 'datetime',
        'recurring' => 'boolean',
        'finished_at' => 'datetime'
    ];

    private static function today() : string {
        return Carbon::now()->toDateString();
    }

    public function actionable()
    {
        return $this->morphTo();
    }

    public function recurringDays()
    {
        return $this->hasMany(RecurringAction::class, 'schedule_id');
    }

    public function getExtraProperty(string $propertyName): mixed
    {
        return Arr::get($this->properties->toArray(), $propertyName);
    }

    public function scopeToActToday($query)
    {
        return $query->whereDate('act_on', self::today());
    }

    public function scopeToActOn($query, Carbon $date)
    {
        return $query->whereDate('act_on', $date);
    }

    public function scopeToActBetween($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('act_at', [$start->toTimeString(), $end->toTimeString()]);
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
        return $query
        ->orderBy('act_on', $direction !== 'asc' ? 'desc' : 'asc')
        ->orderBy('act_at', $direction !== 'asc' ? 'desc' : 'asc');
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

    public function scopeModelId($query, int $id)
    {
        return $query->where('actionable_id', $id);
    }

    public function scopeModelIdIn($query, Collection|array $ids)
    {
        return $query->whereIn('actionable_id', $ids);
    }
}
