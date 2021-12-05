<?php

use Carbon\Carbon;
use Devsrv\ScheduledAction\Enums\Status;
use Devsrv\ScheduledAction\Models\ModelAction;
use Devsrv\ScheduledAction\Tests\Setup\Models\Member;

it('can get all schdules of a model', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->count(2)->model($member)->create();

    $actions = $member->scheduledActions()->get();

    expect($actions)->toHaveCount(2);
});

it('can get all schdules with target status by scopes', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->count(2)->model($member)->create();
    ModelAction::factory()->count(3)->model($member)->finished()->create();
    ModelAction::factory()->model($member)->create([
        'status' => Status::CANCELLED
    ]);
    ModelAction::factory(3)->model($member)->create([
        'status' => Status::DISPATCHED
    ]);

    $pendingActions = $member->scheduledActions()->pending()->get();
    $finishedActions = $member->scheduledActions()->finished()->get();
    $cancelledActions = $member->scheduledActions()->cancelled()->get();
    $dispatchedActions = $member->scheduledActions()->dispatched()->get();

    expect($pendingActions)
        ->toHaveCount(2)
        ->each(fn($action) => $action->status->toEqual(Status::PENDING))
    ->and($finishedActions)
        ->toHaveCount(3)
        ->each(fn($action) => $action
                                ->status->toEqual(Status::FINISHED)
                                ->finished_at->not->toBeNull()
        )
    ->and($cancelledActions)
        ->toHaveCount(1)
        ->each(fn($action) => $action->status->toEqual(Status::CANCELLED))
    ->and($dispatchedActions)
        ->toHaveCount(3)
        ->each(fn($action) => $action->status->toEqual(Status::DISPATCHED))
    ->and(ModelAction::finished()->count())->toEqual(3);
});

test('get the actions scheduled to act between two dates only', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->count(2)->model($member)->create([
        'act_date' => now(),
        'act_time' => Carbon::createFromTimeString('20:00:00')
    ]);

    ModelAction::factory()->model($member)->create([
        'act_date' => now(),
        'act_time' => Carbon::createFromTimeString('18:30:00')
    ]);

    ModelAction::factory()->model($member)->create([
        'act_date' => now(),
        'act_time' => Carbon::createFromTimeString('16:10:20')
    ]);

    $actions = $member->scheduledActions()->pending()->toActBetweenTime(Carbon::createFromTimeString('14:00:00'), Carbon::createFromTimeString('16:30:00'))->get();
    $totalActions = $member->scheduledActions()->pending()->toActBetweenTime(Carbon::createFromTimeString('16:00:00'), Carbon::createFromTimeString('20:30:00'))->get();

    expect($actions)->toHaveCount(1)
    ->and($totalActions)->toHaveCount(4);
});

test('get the actions filter by extra property', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->count(2)->model($member)->create([
        'properties' => ['test' => 'phpunit'],
    ]);

    ModelAction::factory()->model($member)->create([
        'properties' => ['test' => 'pest'],
    ]);

    $pest = $member->scheduledActions()->pending()->whereExtraProperty('test', 'pest')->get();
    $phpunit = $member->scheduledActions()->pending()->whereExtraProperty('test', 'phpunit')->get();

    expect($pest)->toHaveCount(1)
    ->and($phpunit)->toHaveCount(2);
});

test('get the actions filter by list of extra properties', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->count(2)->model($member)->create([
        'properties' => ['test' => 'pest', 'plays' => 'crickrt']
    ]);

    $actions = $member->scheduledActions()->pending()->whereExtraProperties(['test' => 'pest', 'plays' => 'crickrt'])->get();
    $missing = $member->scheduledActions()->pending()->whereExtraProperties(['test' => 'pest', 'plays' => 'football'])->get();

    dump(env('DB_CONNECTION'));

    expect($actions)->toHaveCount(2)
    ->and($missing)->toBeEmpty();
});

test('get the actions filter by containing extra properties', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->count(2)->model($member)->create([
        'properties' => ['test' => 'pest', 'plays' => 'crickrt']
    ]);

    $actions = $member->scheduledActions()->pending()->wherePropertyContains('test', ['pest', 'phpunit'])->get();

    expect($actions)->toHaveCount(2);
})->skip(fn() => env('DB_CONNECTION') === 'testing');

test('whether action status pending dispatched cancelled finished', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->pending()->model($member)->create();
    ModelAction::factory()->finished()->model($member)->create();
    ModelAction::factory()->model($member)->create([
        'status' => Status::CANCELLED
    ]);
    ModelAction::factory()->model($member)->create([
        'status' => Status::DISPATCHED
    ]);

    $actions = $member->scheduledActions()->get();

    expect($actions)
        ->sequence(
            fn($action) => $action->isPending->toBeTrue(),
            fn($action) => $action->isFinished->toBeTrue(),
            fn($action) => $action->isCancelled->toBeTrue(),
            fn($action) => $action->isDispatched->toBeTrue()
        );
});

test('get an extra property from a scheduled action', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->model($member)->create([
        'properties' => ['test' => 'pest', 'plays' => 'crickrt']
    ]);

    $property = $member->scheduledActions()->first()->getExtraProperty('test');

    expect($property)->toEqual('pest');
});

test('filter action by model using `forModel` scope', function() {
    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    ModelAction::factory()->pending()->model($member1)->create();
    ModelAction::factory()->model($member2)->create([
        'status' => Status::FINISHED
    ]);

    expect(ModelAction::forModel($member1)->first()->status)->toEqual(Status::PENDING);
    expect(ModelAction::forModel($member2)->first()->status)->toEqual(Status::FINISHED);
});

test('filter action by model class type', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->pending()->model($member)->create();

    expect(ModelAction::forClass(member::class)->first()->status)->toEqual(Status::PENDING);;
});

test('filter action by action name', function() {
    $member = Member::factory()->create();

    ModelAction::factory()->pending()->model($member)->create([
        'action' => 'SMS'
    ]);

    expect(ModelAction::whereAction('SMS')->count())->toEqual(1);
    expect(ModelAction::whereAction('MAIL')->count())->toEqual(0);
});