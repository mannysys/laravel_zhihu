<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');  //指定id为主键自增不为空非负整型
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable(); //可以为空
            $table->text('avatar_url')->nullable();
            $table->string('phone')->unique()->nullable(); // +86 188172356568
            $table->string('password');
            $table->text('intro')->nullable();
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
