<?php

namespace Devsrv\ScheduledAction\Tests\Setup\Models;

use Illuminate\Database\Eloquent\Model;
use Devsrv\ScheduledAction\Traits\HasScheduledAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Devsrv\ScheduledAction\Tests\Setup\Factories\MemberFactory;

class Member extends Model
{
    use HasFactory;
    use HasScheduledAction;

    protected $guarded = [];
    public $timestamps = false;

    protected static function newFactory()
    {
        return MemberFactory::new();
    }
}