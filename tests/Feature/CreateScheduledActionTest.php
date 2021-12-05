<?php

use Illuminate\Support\Carbon;
use Devsrv\ScheduledAction\Enums\Status;
use Devsrv\ScheduledAction\Models\ModelAction;
use Devsrv\ScheduledAction\Tests\Setup\Models\Member;

it('can create an scheduled action when using relation name', function() {
    $member = Member::factory()->create();

    $action = $member->scheduledActions()->create([
        'action' => 'EMAIL',
        'properties' => ['color' => 'silver'],
        'status' => Status::PENDING,
        'act_date' => now(),
        'act_time' => now()->setHour(11),
    ]);

    test()
    ->assertDatabaseCount('model_actions', 1);

    expect($action->actionable_type)->toEqual(get_class($member));
});

it('can create multiple actions when using relation name', function() {
    $member = Member::factory()->create();

    $actions = $member->scheduledActions()->createMany([
        [
            'action' => 'EMAIL',
            'properties' => ['color' => 'silver'],
            'status' => Status::PENDING,
            'act_date' => now(),
            'act_time' => now()->setHour(11),
        ],
        [
            'action' => 'SMS',
            'properties' => ['color' => 'blue'],
            'status' => Status::PENDING,
            'act_date' => now()->copy()->addDays(7),
            'act_time' => Carbon::createFromTimeString('10:00:00'),
        ]
    ]);

    test()
    ->assertDatabaseCount('model_actions', 2);

    expect($actions)
    ->toHaveCount(2)
    ->sequence(
        fn ($action) => $action
                            ->action->toEqual('EMAIL')
                            ->status->toEqual(Status::PENDING)
                            ->properties->toMatchArray([
                                'color' => 'silver'
                            ]),
        fn ($action) => $action->action->toEqual('SMS'),
    );
});

it('can create schdule fluently direct using ModelAction model and set date time altogether using actAt', function() {
    $member = Member::factory()->create();

    $action = ModelAction::for($member)
    ->actWith('EMAIL')
    ->actAt(now()->tomorrow()->setHour(11))
    ->setExtraProperties(['foo' => 'bar'])
    ->createSchedule();

    test()
    ->assertDatabaseCount('model_actions', 1);

    expect($action)
        ->actionable_type->toEqual(get_class($member))
        ->actionable_id->toEqual($member->id)
        ->properties->toMatchArray([
            'foo' => 'bar'
        ])
        ->act_date->toDateString()->toEqual(now()->tomorrow()->toDateString())
    ->and($action->act_time)->toTimeString()->toEqual('11:00:00')
    ->and($action->actionable())->is($member);
});

it('can create schdule by explicitly defining act time and date using fluent create methods', function() {
    $member = Member::factory()->create();

    $actDate = now()->copy()->addDays(2);

    $action = ModelAction::for($member)
    ->actWith('EMAIL')
    ->actDate($actDate)
    ->actTime(Carbon::createFromTimeString('20:00:00'))
    ->createSchedule();

    expect($action)
    ->act_date->toDateString()->toEqual($actDate->toDateString())
    ->and($action->act_time)->toTimeString()->toEqual('20:00:00')
    ->and($action->actionable())->is($member);
});

it('sets act date to today when skipped while using fluent create methods', function() {
    $member = Member::factory()->create();

    $action = ModelAction::for($member)
    ->actWith('EMAIL')
    ->actTime(Carbon::createFromTimeString('10:00:00'))
    ->createSchedule();

    expect($action)
        ->act_date->toDateString()->toEqual(now()->toDateString())
        ->act_time->toTimeString()->toEqual('10:00:00')
    ->and($action->actionable())->is($member);
});

it('can create schedule using fluent create methods and set status', function() {
    $member = Member::factory()->create();

    $action = ModelAction::for($member)
    ->actWith('EMAIL')
    ->actTime(Carbon::createFromTimeString('10:00:00'))
    ->asDispatched()
    ->createSchedule();

    expect($action)
        ->act_date->toDateString()->toEqual(now()->toDateString())
        ->act_time->toTimeString()->toEqual('10:00:00')
        ->status->toEqual(Status::DISPATCHED)
    ->and($action->actionable())->is($member);
});