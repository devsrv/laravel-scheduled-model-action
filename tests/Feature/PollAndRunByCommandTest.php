<?php

use Devsrv\ScheduledAction\Enums\Status;
use Devsrv\ScheduledAction\Models\ModelAction;
use Devsrv\ScheduledAction\Tests\Setup\Models\Member;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

test('poll and run via command', function() {
    Config::set('scheduled-action.receiver', ActionReceiver::class);

    $member = Member::factory()->create();

    ModelAction::factory()->pending()->model($member)->create([
        'properties' => ['mailable' => 'App\Mail\Foo'],
        'act_date' => now(),
        'act_time' => '16:40:00'
    ]);
    ModelAction::factory()->pending()->model($member)->create([
        'properties' => ['mailable' => 'App\Mail\Foo'],
        'act_date' => now(),
        'act_time' => '16:50:00'
    ]);

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now()->tomorrow(),
        'act_time' => '16:50:00'
    ]);

    Artisan::call('scheduledaction:poll --tasks=1');

    expect(ModelAction::whereActDate(now())->whereStatus(Status::FINISHED)->count())
        ->toEqual(1);

    Artisan::call('scheduledaction:poll --tasks=1');

    expect(ModelAction::whereActDate(now())->whereStatus(Status::FINISHED)->count())
        ->toEqual(2);
});

class ActionReceiver {
    public function __invoke($tasks)
    {
        foreach ($tasks as $task) {
            $model = $task->actionable;
            $mailable = $task->getExtraProperty('mailable');
            $time = $task->act_time;

            // assume action handled successfully
            $task->setFinished()->save();
        }
    }
}