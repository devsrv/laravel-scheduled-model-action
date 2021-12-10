# Laravel Scheduled Action

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devsrv/laravel-scheduled-action.svg?style=flat-square)](https://packagist.org/packages/devsrv/laravel-scheduled-action)
[![Total Downloads](https://img.shields.io/packagist/dt/devsrv/laravel-scheduled-action.svg?style=flat-square)](https://packagist.org/packages/devsrv/laravel-scheduled-action)
[![GitHub Tests Action Status](https://github.com/devsrv/laravel-scheduled-model-action/actions/workflows/test.yaml/badge.svg)](https://github.com/devsrv/laravel-scheduled-model-action/actions/workflows/test.yaml)

Handle scheduled tasks associated with Eloquent models.

<p align="center">
  <img src="https://binarymesh.dev/assets/package/actions-create-task11.svg" width="350" alt="create schedule" />
  <img src="https://binarymesh.dev/assets/package/actions-poll-011.svg" width="380" alt="poll schedules" />
</p>
<br/>

For any scheduled task we can directly use Laravel's [queue](https://laravel.com/docs/8.x/queues) but what if that task needs to be modified in some way before it gets executed?

This package stores all the tasks that needs to run on a future date & time and executes each only on the day when it is scheduled to run so that we get the chance to modify the task before it gets executed.

It uses Laravel's task [scheduling](https://laravel.com/docs/8.x/scheduling) to figure out & handle the tasks that needs to be run for the current day at the specified time for that task, and sends the task payload to a [receiver class](https://github.com/devsrv/laravel-scheduled-model-action#step---3-) of your app ([configurable](#%EF%B8%8F-publish-config)). So how to perform the task is totally up to you.

## 💡 How it works:
- This package creates one table `model_actions`
- Every task has 4 satatus `PENDING` `FINISHED` `CANCELLED` `DISPATCHED`
- The `scheduledaction:poll` artisan command polls `PENDING` tasks for the present day and passes the tasks payload to your receiver class.
- Set how often you want the poll to happen and how many tasks needs to be passed to your receiver (the above [example](#%EF%B8%8F-add-scheduled-task-to-appconsolekernelphp) shows 10 per hour)
- `PENDING` tasks gets run at specified date & time, remember to mark the task as `FINISHED` or `CANCELLED` based on how it was handled [check example](#step---4-).
- Most likely you'll use queue to run a task at a specified time so after dispatching to a queued job you might want to set the status as `DISPATCHED`

## Installation

```shell
composer require devsrv/laravel-scheduled-action
```

## Setup

#### ✔️ publish migrations
```shell
php artisan vendor:publish --provider="Devsrv\ScheduledAction\ScheduledActionServiceProvider" --tag="migrations"
```

#### ✔️ run migrations
```shell
php artisan migrate
```

#### ✔️ publish config
```shell
php artisan vendor:publish --provider="Devsrv\ScheduledAction\ScheduledActionServiceProvider" --tag="config"
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
```

#### ✔️ Use the `HasScheduledAction` trait in your models
```php
use Devsrv\ScheduledAction\Traits\HasScheduledAction;

class Candidate extends Model
{
    use HasFactory, HasScheduledAction;
    
    ...
}
```

## There are many fluent methods to interact with the scheduled action


### Get

```php
use Devsrv\ScheduledAction\Models\ModelAction;
use Devsrv\ScheduledAction\Facades\Action;

$model = \App\Models\Candidate::find(10);

$model->scheduledActions()->get();
$model->scheduledActions()->finished()->get();
$model->scheduledActions()->cancelled()->first();
$model->scheduledActions()->pending()->get();
$model->scheduledActions()->dispatched()->paginate();
$model->scheduledActions()->pending()->toActBetweenTime(Carbon::createFromTimeString('14:00:00'), Carbon::createFromTimeString('16:30:00'))->get();
$model->scheduledActions()->pending()->whereExtraProperty('prefers', 'mail')->get();
$model->scheduledActions()->whereExtraProperty('channel', 'slack')->get();
$model->scheduledActions()->whereExtraProperties(['prefers' => 'mail', 'applicant' => 27])->get();
$model->scheduledActions()->wherePropertyContains('languages', ['en', 'de'])->get();
$model->scheduledActions()->first()->isPending();
$model->scheduledActions()->first()->getExtraProperty('customProperty');

$task = ModelAction::find(1);

$task->getExtraProperty('customProperty');
$task->act_time;
$task->action;
$task->actionable; 	// associated model

$task->isPending;	    // bool
$task->isFinished; 	    // bool
$task->isDispatched; 	    // bool
$task->isCancelled; 	    // bool
$task->isRecurring; 	    // bool

ModelAction::finished()->get();
ModelAction::forModel($modlel)->pending()->get();
ModelAction::forClass(Candidate::class)->get();
ModelAction::whereAction('EMAIL')->get();
ModelAction::forClass(Candidate::class)->modelId(10)->get();
ModelAction::modelIdIn([10, 11, 12])->get();
ModelAction::forModel($modlel)->whereProperty('mailable', \App\Mail\RejectMail::class)->get();
ModelAction::forModel($modlel)->whereProperties(['type' => 'info', 'applicant' => 27])->get();
ModelAction::wherePropertyContains('languages', ['en', 'de'])->get();
ModelAction::where('properties->type', 'success')->get();

\Devsrv\ScheduledAction\Facades\Action::needsToRunOn(now()->tomorrow(), 10, 'asc');
\Devsrv\ScheduledAction\Facades\Action::needsToRunToday(3);
```

### Create

```php
$action = $model->scheduledActions()->create([
    'action' => 'EMAIL',
    'properties' => ['color' => 'silver'],
    'status' => \Devsrv\ScheduledAction\Enums\Status::PENDING,
    'act_date' => now(),
    'act_time' => now()->setHour(11),
]);

$model->scheduledActions()->createMany([]);

ModelAction::for(Candidate::find(10))
    ->actWith('EMAIL')
    ->actAt(Carbon::tomorrow()->setHour(11))    // date + time will be set together
    ->setExtraProperties(['foo' => 'bar'])
    ->createSchedule();

ModelAction::for($model)
    ->actWith('EMAIL')
    ->actDate($actDate)
    ->actTime(Carbon::createFromTimeString('20:00:00'))
    ->setExtraProperties(['foo' => 'bar'])
    ->createSchedule();

ModelAction::for($model)->actWith('EMAIL')->actTime($carbon)->asDispatched()->createSchedule();
```

### Update
```php

$action->setPending()->save();
$action->setCancelled()->save();
$action->setDispatched()->save();
$action->setFinished()->save();							// default sets finished_at as now()
$action->setFinished($carbon)->save();						// set a finished_at time
$action->mergeExtraProperties(['key' => 'val'])->save();  			// merges extra properties
$action->withExtraProperties([])->save();                   			// overwrites existing
$action->setPending()->setActAt($actAt)->save();
$action->setActAt($carbon)->save();						// date and time will be extracted
$action->setActDate($carbon)->save();						// only date will be used
$action->setActTime($carbon)->save();						// only time will be used
```

## Example

> imagine a job portal where an application is auto approved after 3 days of apply unless it was auto rejected by some qualifier checks by bot, during that period the admin may override the application status or prevent the auto mail sending or modify the mail content

#### Step - 1 :
<details>
<summary>Some event happend and a task is created to execute on future day & time</summary>

```php
ModelAction::for($application)
->actWith('MAIL')
->actAt($inThreeDays)
->setExtraProperties([
  'mailable' => ApproveApplication::class,
  'template' => $template
])
->createSchedule();
```
</details>

#### Step - 2 :
<details>
<summary>admin decides to alter the task</summary>

```php
public function modifyScheduledTask() {
    $this->validate();

    $this->task
        ->setActDate(Carbon::createFromFormat('m/d/Y', $this->act_date))
        ->setActTime(Carbon::createFromTimeString($this->act_time))
        ->mergeExtraProperties([
            'template' => $this->templateid,
            'extra_data' => $this->role
        ])
        ->save();

    $this->info('schedule updated');
}
    
public function cancelSchedule() {
    $this->task->setCancelled()->save();
    $this->info('schedule cancelled');
}
```
</details>

#### Step - 3 :
<details>
<summary>receiver class gets task payload & passes the task to classes based on task action (for this example sending email)</summary>

```php
<?php

namespace App\Http\AutoAction;

use Facades\App\Http\Services\AutoAction\Mailer;

class ScheduledActionReceiver
{
    public function __invoke($tasks)
    {
        foreach ($tasks as $task) {
            match($task->action) {
                'MAIL'    => MailTaskHandler::handle($task),
                ...

                default   => activity()->log('auto action task unhandled')
            };
        }
    }
}
```
</details>

#### Step - 4 :
<details>
<summary>email sending task payload gets received via previous receiver class and mail is sent</summary>

```php
class MailTaskHandler
{
	public function handle($task) {
        [$user, $mailable, $extras, $time] = $this->extractData($task);

        Mail::to($user)
        ->later(
            Carbon::now()->setTimeFromTimeString($time),
            new $mailable($user, $extras)
        );

        $task->setFinished()->save();	// 👈 marking the task finished here though it is actually not, because for mail there is no easy way to execute this code after that mail is actually sent
    }

    private function extractData($task) {
        $model = $task->actionable;
        $user = null;

        $mailable = $task->getExtraProperty('mailable');
        $template = $task->getExtraProperty('template');

        $extras = [];

        if($mailable === ApproveApplication::class) {
            $extras['role'] = $task->getExtraProperty('role');

            $user = $model->applicant;
        }

        $time = $task->act_time;

        return [$user, $mailable, $template, $extras, $time];
    }
}
```
</details>

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👋🏼 Say Hi! 
Leave a ⭐ if you find this package useful 👍🏼,
don't forget to let me know in [Twitter](https://twitter.com/srvrksh)  
