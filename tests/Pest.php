<?php

uses(Devsrv\ScheduledAction\Tests\TestCase::class)->in('Feature');

function isLaravel(int $version)
{
    return (int) substr( app()->version(), 0, 1 ) === $version;
}
