<?php

namespace Devsrv\ScheduledAction\Console;
use Devsrv\ScheduledAction\Facades\Action;

use Illuminate\Console\Command;

class PollScheduledAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduledaction:poll
                            {--tasks=10 : How many tasks on each poll}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll scheduled model actions';

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
        $tasks = $this->option('tasks') ?? 10;

        return Action::emit($tasks);
    }
}
