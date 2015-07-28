<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersDeviceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_device_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('token')->unique();
            $table->string('type');
            $table->string('lat');
            $table->string('lng');
            $table->dateTime('last_signup');
            $table->enum('device_status', ['online', 'offline'])->default('online');
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
        Schema::drop('users_device_details');
    }
}
