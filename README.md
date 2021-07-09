# Laravel Scheduled Action

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devsrv/laravel-scheduled-action.svg?style=flat-square)](https://packagist.org/packages/devsrv/laravel-inplace)
[![Total Downloads](https://img.shields.io/packagist/dt/devsrv/laravel-scheduled-action.svg?style=flat-square)](https://packagist.org/packages/devsrv/laravel-inplace)

Handle scheduled one time or recurring tasks associated with Eloquent models.

> imagine a job portal where if an application gets rejected the app needs to notify the applicant via automated email after 3 days of that event, during that period the admin may change his mind and prevent the auto mail sending or modify the mail content

For any scheduled task we can directly use Laravel's queue but what if that task needs to be modified is some way before it gets executed?

This package stores all the tasks that needs to run on a future date & time / recurringly and perform a task only before few moments when it is scheduled to run so that we get the chance to modify the task before it gets executed.

It uses Laravel's task scheduling to figure out & handle the tasks that needs to be run for the current day at the specified time for that task, and sends the task payload to a receiver class of your app (configurable). So how to perform the task is totally up to you.


### Installation

```shell
composer require devsrv/laravel-scheduled-action
```

### Setup

#### ✔️ publish migrations
```shell
php artisan vendor:publish --provider="Devsrv\ScheduledAction\ActionableServiceProvider" --tag="migrations"
```

#### ✔️ run migrations
```shell
php artisan migrate
```

#### ✔️ publish config
```shell
php artisan vendor:publish --provider="Devsrv\ScheduledAction\ActionableServiceProvider" --tag="config"
```

```php
// the class that takes care of how to perform the task, the payload will be passed to this invokable class
return [
    'receiver' => \App\Http\AutoAction\ScheduledActionReceiver::class, 👈 needs to be invokable
];
```

#### ✔️ Add Scheduled Task to `app/Console/Kernel.php`
```php
$schedule->command('scheduledaction:poll --tasks=10')->hourly();  // poll pending tasks (10 tasks every hour & sends payload to your receiver, customize as per your app)

$schedule->command('scheduledaction:reset')->dailyAt('12:01'); // resets previously finished recurring tasks' status that needs to run today, skip this if your app donot need recurring task handling
```

### 💡 NOTE :
- this package creates two tables `model_actions` and `model_action_recurring`
- every task has 4 satatus `PENDING` `FINISHED` `CANCELLED` `DISPATCHED`
- `PENDING` tasks gets run at specified date & time, remember to mark the task as `FINISHED` or `CANCELLED` based on how it was handled.
- most likely you'll use queue to run a task at a specified time so after dispatching to a queued job you might want to set the status as `DISPATCHED`

>there are many fluent methods to interact with the tables, check below:-


### Use

#### Get

```php
use Devsrv\ScheduledAction\Models\ModelAction;
use Devsrv\ScheduledAction\Facades\Action;

$model = \App\Models\Candidate::find(10);

$model->scheduledActions()->get();
$model->scheduledActions()->finished()->get();
$model->scheduledActions()->cancelled()->first();
$model->scheduledActions()->pending()->get();
$model->scheduledActions()->dispatched()->paginate();
$model->scheduledActions()->pending()->toActBetween(Carbon $yesterday, Carbon $today)->get();
$model->scheduledActions()->pending()->whereExtraProperty('type', 'info')->get();
$model->scheduledActions()->whereExtraProperty('type', 'success')->get();
$model->scheduledActions()->whereExtraProperties(['type' => 'info', 'applicant' => 27])->get();
$model->scheduledActions()->wherePropertyContains('languages', ['en', 'de'])->get();
$model->scheduledActions()->first()->isPending();
$model->scheduledActions()->first()->getExtraProperty('customProperty');

$action = ModelAction::find(1);

$action->getExtraProperty('customProperty');
$action->act_at; 		// 10:22:15
$action->actionable; 	// associated model

$action->isPending(); 		// bool
$action->isFinished(); 		// bool
$action->isDispatched(); 	// bool
$action->isCancelled(); 	// bool
$action->isRecurring(); 	// bool

ModelAction::finished()->get();
ModelAction::recurring()->get();
ModelAction::for($modlel)->pending()->get();
ModelAction::forClass(Candidate::class)->get();
ModelAction::whereAction('EMAIL')->get();
ModelAction::forClass(Candidate::class)->modelId(10)->get();
ModelAction::modelIdIn([10, 11, 12])->get();
ModelAction::for($modlel)->whereProperty('mailable', \App\Mail\RejectMail::class)->get();
ModelAction::for($modlel)->whereProperties(['type' => 'info', 'applicant' => 27])->get();
ModelAction::wherePropertyContains('languages', ['en', 'de'])->get();
ModelAction::where('properties->type', 'success')->get();

\Devsrv\ScheduledAction\Facades\Action::needsToRunOn(Carbon::now(), 10, 'asc');
\Devsrv\ScheduledAction\Facades\Action::needsToRunToday(10, 'desc');
```

#### Create

```php
$action = $model->scheduledActions()->create([
    'action' => 'EMAIL',
    'properties' => ['color' => 'silver'],
    'status' => \Devsrv\ScheduledAction\Enums\Status::PENDING,
    'recurring' => 1,
    'act_at' => Carbon::now()->addDays(3)->setHour(11),
]);

$action->recurringDays()->create([
  'day' => \Devsrv\ScheduledAction\Enums\Days::SUNDAY
]);

$model->scheduledActions()->createMany([]);

ModelAction::actWith('EMAIL')
  ->forModel(Candidate::find(10))
  ->actAt(Carbon::tomorrow()->setHour(11)) // date + time will be set unless runsOnEvery applied
  ->setExtraProperties(['foo' => 'bar'])
  ->createSchedule();

ModelAction::actWith('EMAIL')
  ->forModel(Candidate::find(10))
  ->actTime(now()->setHour(14)->setMinute(20))
  ->setExtraProperties(['foo' => 'bar'])
  ->runsOnEvery([\Devsrv\ScheduledAction\Enums\Days::SUNDAY, \Devsrv\ScheduledAction\Enums\Days::FRIDAY])
  ->createSchedule();

ModelAction::actWith('EMAIL')->forModel(Candidate::find(10))->actDate($carbon)->actTime($carbon)->createSchedule();
ModelAction::actTime($carbon)->createSchedule();
```

#### Update
```php

$action->setPending()->save();
$action->setCancelled()->save();
$action->setDispatched()->save();
$action->setFinished()->save();								// default sets finished_at as now()
$action->setFinished($carbon)->save();						// set a finished_at time
$action->mergeExtraProperties(['key' => 'val'])->save();  	// merges extra properties
$action->withExtraProperties([])->save();/
$action->setActOn($carbon)->save();							// date and time will be extracted
$action->setActDate($carbon)->save();						// only date will be used
$action->setActTime($carbon)->save();						// only time will be used

$action->markAsRecurringRunsOnEvery([Days::SUNDAY, ..]);    // stores only the given days removeing any existing day that was previously set as recurring day
$action->syncWithoutDetachingRunsOnEvery([Days::SUNDAY, ..]);  // doesnt remove anything yet adds any new days given

$action->setNonRecurring()->setActOn($carbon)->save();
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👋🏼 Say Hi! 
Leave a ⭐ if you find this package useful 👍🏼,
don't forget to let me know in [Twitter](https://twitter.com/srvrksh)  