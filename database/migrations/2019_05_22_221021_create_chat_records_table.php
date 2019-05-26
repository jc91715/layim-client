<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('send_id');
            $table->unsignedBigInteger('receive_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('type')->default('');
            $table->text('content')->nullable();
            $table->boolean('if_read')->default(0)->comment('是否读');
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
        Schema::dropIfExists('chat_records');
    }
}
