<?php

use Carbon\Carbon;
use Devsrv\ScheduledAction\Enums\Status;
use Devsrv\ScheduledAction\Models\ModelAction;

it('change action status to pending', function() {
    $action = ModelAction::factory()->finished()->create();

    $action->setPending()->save();

    expect($action->status)->toEqual(Status::PENDING);
});

it('change action status to finished with target finished time set', function() {
    $action = ModelAction::factory()->pending()->create();

    $finished_at = now()->subMinutes(20);

    $action->setFinished($finished_at)->save();

    expect($action)
    ->status->toEqual(Status::FINISHED)
    ->finished_at->toDateTimeString()->toEqual($finished_at->toDateTimeString());
});

it('sets finished time to current time if nothing specified', function() {
    $action = ModelAction::factory()->pending()->create();

    $action->setFinished()->save();

    expect($action)
    ->status->toEqual(Status::FINISHED)
    ->finished_at->toDateTimeString()->toEqual(now()->toDateTimeString());
});

it('change action status to cancelled', function() {
    $action = ModelAction::factory()->finished()->create();

    $action->setCancelled()->save();

    expect($action)
    ->status->toEqual(Status::CANCELLED)
    ->finished_at->toBeNull();
});

it('change action status to dispatched', function() {
    $action = ModelAction::factory()->finished()->create();

    $action->setDispatched()->save();

    expect($action)
    ->status->toEqual(Status::DISPATCHED)
    ->finished_at->toBeNull();
});

it('change action act date and time along with status by chaining fluent methods', function() {
    $action = ModelAction::factory()->finished()->create();

    $actAt = now()->tomorrow()->setHour(13)->setMinutes(20);
    $action->setPending()->setActAt($actAt)->save();

    expect($action)
    ->status->toEqual(Status::PENDING)
    ->act_date->toDateString()->toEqual($actAt->toDateString())
    ->and($action->act_time)->toTimeString()->toEqual($actAt->toTimeString())
    ->finished_at->toBeNull();
});

it('change action act date', function() {
    $action = ModelAction::factory()->pending()->create();

    $actDate = now()->tomorrow();
    $action->setActDate($actDate)->save();

    expect($action)
    ->act_date->toDateString()->toEqual($actDate->toDateString());
});

it('change action act time', function() {
    $action = ModelAction::factory()->pending()->create();

    $actTime = Carbon::createFromTimeString('18:20:00');
    $action->setActTime($actTime)->save();

    expect($action)
    ->act_time->toTimeString()->toEqual($actTime->toTimeString());
});

test('merge extra properties with exiting', function() {
    $action = ModelAction::factory()->pending()->create([
        'properties' => ['superhero' => 'bat man'],
    ]);

    $action->mergeExtraProperties(['play' => 'cricket'])->save();

    expect($action)
    ->properties->toMatchArray([
        'superhero' => 'bat man',
        'play' => 'cricket'
    ]);
});

test('overwrite previous properties with new', function() {
    $action = ModelAction::factory()->pending()->create([
        'properties' => ['superhero' => 'bat man'],
    ]);

    $action->withExtraProperties(['play' => 'cricket'])->save();

    expect($action)
    ->properties->toMatchArray([
        'play' => 'cricket'
    ]);
});

