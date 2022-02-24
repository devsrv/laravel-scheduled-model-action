<?php

namespace Devsrv\ScheduledAction\Models;

use Carbon\Carbon;
use Database\Factories\ActionFactory;
use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Devsrv\ScheduledAction\Traits\{ActionStatus, FluentUpdate, FluentCreate};

class ModelAction extends Model
{
    use HasFactory, ActionStatus, FluentUpdate, FluentCreate;
    
    protected $table = 'model_actions';

    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
        'properties' => 'collection',
        'act_date' => 'datetime:Y-m-d',
        'act_time' => 'datetime:H:i:s',
        'finished_at' => 'datetime'
    ];

    protected static function newFactory()
    {
        return ActionFactory::new();
    }

    private static function today() : string {
        return now()->toDateString();
    }

    public function actionable()
    {
        return $this->morphTo();
    }

    public function getExtraProperty(string $propertyName)
    {
        return Arr::get($this->properties->toArray(), $propertyName);
    }

    public function scopeToActBetweenTime($query, Carbon $start, Carbon $end)
    {
        return $query
            ->whereTime('act_time', '>=', $start->toTimeString())
            ->whereTime('act_time', '<=', $end->toTimeString());
    }

    public function scopeWhereExtraProperty($query, $prop, $value)
    {
        return $query->where('properties->' . $prop, $value);
    }

    public function scopeWhereExtraProperties($query, array $properties)
    {
        collect($properties)
        ->each(fn($value, $key) => $query->where('properties->' . $key, $value));

        return $query;
    }

    public function scopeForModel($query, Model $model)
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

    public function scopeModelIdIn($query, array $ids)
    {
        return $query->whereIn('actionable_id', $ids);
    }
}
