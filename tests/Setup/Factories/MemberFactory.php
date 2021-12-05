<?php

namespace Devsrv\ScheduledAction\Tests\Setup\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Devsrv\ScheduledAction\Tests\Setup\Models\Member;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name()
        ];
    }
}
