<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('super_admin_id')->unsigned();
            $table->bigInteger('admin_id')->unsigned();
            $table->bigInteger('max_advisor')->nullable();
            $table->timestamps();
            $table->foreign('admin_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('super_admin_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('admins');
    }
}
