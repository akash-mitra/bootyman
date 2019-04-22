<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['error', 'warning', 'info', 'request']);
            $table->string('order_id')->nullable();
            $table->string('origin')->nullable();
            $table->string('code', 30)->nullable();
            $table->text('message');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('context')->nullable();
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
        Schema::dropIfExists('journals');
    }
}
