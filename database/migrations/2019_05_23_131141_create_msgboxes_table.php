<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsgboxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('msgboxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('uid');
            $table->string('content')->default('');
            $table->unsignedInteger('from');
            $table->unsignedInteger('from_group')->default(0);
            $table->string('remark')->default('');
            $table->string('href')->default('');
            $table->boolean('read')->default(0);
            $table->string('type')->default('')->comment('加好友或加群');
            $table->dateTime('receive_time')->nullable();
            $table->string('state')->default('')->comment('同意或拒绝');
            $table->text('user')->nullable();
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
        Schema::dropIfExists('msgboxes');
    }
}
