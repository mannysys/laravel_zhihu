<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAnswerUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer_user', function (Blueprint $table) {
            $table->increments('id');
            //指定2张表的主键
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('answer_id');
            //投票，支持票和反对票（存储0或者1）
            $table->unsignedSmallInteger('vote');
            $table->timestamps();

            //指定2个表的外键
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('answer_id')->references('id')->on('answers');
            //指定那些字段组合必须是唯一（指定这3个字段连接起来必须是唯一的）
            $table->unique(['user_id', 'answer_id', 'vote']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('answer_user');
    }
}
