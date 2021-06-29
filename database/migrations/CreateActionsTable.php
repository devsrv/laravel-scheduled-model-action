<?php

use Devsrv\ScheduledAction\Enums\Status;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_actions', function (Blueprint $table) {
            $table->id();
            $table->morphs('actionable');
            $table->string('action', 20);
            $table->json('properties')->nullable();
            $table->enum('status', array_keys((new ReflectionClass(Status::class))->getConstants()))->default(Status::PENDING);
            $table->timestamp('act_at', $precision = 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_actions');
    }
}
