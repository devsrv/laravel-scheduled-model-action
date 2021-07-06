<?php

namespace Devsrv\ScheduledAction\Console;
use Illuminate\Console\Command;
use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Devsrv\ScheduledAction\Models\ModelAction;

class ResetRecurringAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduledaction:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset finished recurring scheduled model actions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ModelAction::query()
        ->finished()
        ->recurring()
        ->whereHas('recurringDays', fn (Builder $query) =>  $query->where('day', strtoupper(now()->englishDayOfWeek)) )
        ->update(['status' => Status::PENDING, 'finished_at' => null]);
    }
}
