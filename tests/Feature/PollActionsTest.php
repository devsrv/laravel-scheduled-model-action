<?php

use Devsrv\ScheduledAction\Action;
use Devsrv\ScheduledAction\Enums\Status;
use Devsrv\ScheduledAction\Models\ModelAction;
use Devsrv\ScheduledAction\Tests\Setup\Models\Member;

test('get actions needs to run today default next 10 in ascending time order', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->finished()->model($member)->create([
        'act_date' => now()
    ]);

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now(),
        'act_time' => '16:40:00'
    ]);
    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now(),
        'act_time' => '16:50:00'
    ]);

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now()->tomorrow()
    ]);

    $actionManager = new Action;
    $runToday = $actionManager->needsToRunToday();

    expect($runToday)
        ->toHaveCount(2)
        ->sequence(
            fn($action) => $action->act_time->toTimeString()->toEqual('16:40:00'),
            fn($action) => $action->act_time->toTimeString()->toEqual('16:50:00')
        );
});

test('get actions needs to run today limit upcoming 2 in desc time order', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now(),
        'act_time' => '16:40:00'
    ]);
    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now(),
        'act_time' => '16:50:00'
    ]);
    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now(),
        'act_time' => '17:00:00'
    ]);

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now()->tomorrow()
    ]);

    $actionManager = new Action;
    $runToday = $actionManager->needsToRunToday(2, 'desc');

    expect($runToday)
        ->toHaveCount(2)
        ->sequence(
            fn($action) => $action->act_time->toTimeString()->toEqual('17:00:00'),
            fn($action) => $action->act_time->toTimeString()->toEqual('16:50:00')
        );
});

test('get actions needs to run on target date', function() {
    $member = Member::factory()->create();

    $tomorrow = now()->tomorrow();

    ModelAction::factory()->model($member)->create([
        'status' => Status::CANCELLED,
        'act_date' => $tomorrow,
        'act_time' => '16:40:00'
    ]);

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => $tomorrow,
        'act_time' => '16:50:00'
    ]);
    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => $tomorrow,
        'act_time' => '17:00:00'
    ]);

    ModelAction::factory()->pending()->model($member)->create([
        'act_date' => now()
    ]);

    $actionManager = new Action;
    $runTomorrow = $actionManager->needsToRunOn($tomorrow);

    expect($runTomorrow)
        ->toHaveCount(2)
        ->sequence(
            fn($action) => $action->act_time->toTimeString()->toEqual('16:50:00'),
            fn($action) => $action->act_time->toTimeString()->toEqual('17:00:00')
        );
});

