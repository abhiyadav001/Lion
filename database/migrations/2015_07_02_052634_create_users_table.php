<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('fb_id')->unique();
            $table->string('img_url');
            $table->string('age');
            $table->string('gender');
            $table->string('name');
            $table->string('occupation')->nullable();
            $table->string('city')->nullable();
            $table->string('interests')->nullable();
            $table->string('email')->nullable();
            $table->string('linkedin_id')->nullable();
            $table->string('twitter_id')->nullable();
            $table->string('instagram_id')->nullable();
            $table->enum('linkedin_status', ['on', 'off'])->default('off');
            $table->enum('twitter_status', ['on', 'off'])->default('off');
            $table->enum('instagram_status', ['on', 'off'])->default('off');
            $table->enum('visibility', ['on', 'off'])->default('off');
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
        Schema::drop('users');
    }
}
