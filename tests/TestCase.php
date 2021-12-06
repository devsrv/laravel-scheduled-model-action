<?php

namespace Devsrv\ScheduledAction\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Devsrv\ScheduledAction\ScheduledActionServiceProvider;

class TestCase extends TestbenchTestCase
{
    use RefreshDatabase;
    
    protected function getPackageProviders($app)
    {
        return [
            ScheduledActionServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        include_once __DIR__ . '/../database/migrations/create_actions_table.php.stub';
        (new \CreateActionsTable)->up();

        include_once __DIR__.'/Setup/migrations/create_test_tables.php.stub';
        (new \CreateTestTables())->up();
    }
}
