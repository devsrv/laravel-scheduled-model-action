<?php

return [
    /**
     * needs to be an invikable object (class with __invoke method)
     * receives collection of tasks that need to run next
     */

    'receiver' => null, // example: \App\Http\AutoAction\ScheduledActionReceiver::class
];
