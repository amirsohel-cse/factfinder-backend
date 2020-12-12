<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->enum('type',['Important','Urgent','Complex']);
            $table->date('completion_date');
            $table->time('completion_time');
            $table->enum('priority',[1,2,3,5,8,13]);
            $table->enum('assignee_type',['Client','Advisor'])->default('Client');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
