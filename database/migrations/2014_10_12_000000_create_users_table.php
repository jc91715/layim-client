<?php

use Illuminate\Support\Facades\Schema;
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
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->default('');

            $table->string('openid')->default('');
            $table->string('nickname')->default('');
            $table->string('sex')->default('');
            $table->string('language')->default('');
            $table->string('city')->default('');
            $table->string('province')->default('');
            $table->string('country')->default('');
            $table->string('headimgurl')->default('');

            $table->string('client_id')->default('')->comment('websocket client_id');
            $table->string('sign')->default('');
            $table->string('status')->default('');

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
