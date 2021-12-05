<?php

namespace Devsrv\ScheduledAction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Devsrv\ScheduledAction\Models\ModelAction;

class Action
{
    public function emit($poll = 10)
    {
        if(! $receiver = config('scheduled-action.receiver')) return;

        if(! class_exists($receiver) && ! is_callable(new $receiver)) return;

        $callableReceiver = new $receiver;
        call_user_func(
            $callableReceiver, 
            $this->needsToRunToday( $poll )
        );
    }

    protected function getPendingSchedules($date = null, $limit = 10, $orderBy = 'asc') : Collection {
        if($orderBy !== 'asc') $orderBy = 'desc';

        $date = $date ?? now();

        return ModelAction::query()
        ->with('actionable')
        ->pending()
        ->where(function($query) use($date) {
            $query
            ->whereDate('act_date', $date);
        })
        ->orderByRaw('TIME(`act_time`) '. $orderBy)
        ->take($limit)
        ->get();
    }

    /**
     * the list of scheduled pending actions to run today
     * @param int $take optional how many results to show
     * @param string $orderBy optional order by time when to run the process 'asc' | 'desc'
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function needsToRunToday($take = 10, $orderBy = 'asc') : Collection {
        return $this->getPendingSchedules(now(), $take, $orderBy);
    }

    /**
     * the list of scheduled pending actions to run on a given date
     * @param Carbon $date target date
     * @param int $take optional how many results to show
     * @param string $orderBy optional order by time when to run the process 'asc' | 'desc'
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function needsToRunOn(Carbon $date, $take = 10, $orderBy = 'asc') : Collection {
        return $this->getPendingSchedules($date, $take, $orderBy);
    }
}
