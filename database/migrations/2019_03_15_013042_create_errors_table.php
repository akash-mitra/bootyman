<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('errors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 30);
            $table->string('provider', 30);
            $table->string('region', 30);
            $table->string('errorable_type');
            $table->bigInteger('errorable_id');
            $table->mediumText('desc');
            $table->string('status', 30);
            $table->string('token');
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
        Schema::dropIfExists('errors');
    }
}
