<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('reciver_id');
            $table->string('message');
            $table->enum('status', array('rejected', 'confirmed'))->nullable();
            $table->string('expired_at');

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
        Schema::drop('notifications');
    }
}
