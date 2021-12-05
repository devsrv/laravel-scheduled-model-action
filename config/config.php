<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Task Receiver
    |--------------------------------------------------------------------------
    |
    | It receives the collection of tasks that need to run next.
    |
    | Note: should be an invikable object (class with __invoke method).
    |
    */

    'receiver' => null, // example: \App\Http\AutoAction\ScheduledActionReceiver::class
];
