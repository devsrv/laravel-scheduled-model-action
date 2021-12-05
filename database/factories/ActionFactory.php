<?php

namespace Database\Factories;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Enums\Status;
use Devsrv\ScheduledAction\Models\ModelAction;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActionFactory extends Factory
{
    protected $model = ModelAction::class;

    public function definition()
    {
        return [
            'actionable_type' => 'App\\Models\\FakeModel',
            'actionable_id' => rand(1, 100),
            'action' => Arr::random(['MAIL', 'SMS']),
            'properties' => ['superhero' => 'bat man'],
            'status' => Status::PENDING,
            'act_date' => $this->faker->date(),
            'act_time' => $this->faker->time(),
            'finished_at' => now()->subDays(3)
        ];
    }

    public function model(Model $model)
    {
        return $this->state(function (array $attributes) use($model) {
            return [
                'actionable_type' => get_class($model),
                'actionable_id' => $model->id,
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Status::PENDING,
                'finished_at' => null,
            ];
        });
    }

    public function finished()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Status::FINISHED,
                'finished_at' => now(),
            ];
        });
    }
}
