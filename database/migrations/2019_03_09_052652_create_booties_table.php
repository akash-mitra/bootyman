<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBootiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('snapshot_id');
            $table->string('order_id');
            $table->string('owner_email');
            $table->string('name')->nullable();
            $table->string('ip', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string( 'internal_machine_id', 30)->nullable();
            $table->string('size', 30)->default( 's-1vcpu-1gb' );
            $table->string('region', 30)->default('sgp1');
            $table->string('backup', 5);
            $table->string('monitoring', 5);
            $table->string('sshkey')->nullable();
            $table->timestamp('ssl_renewed_at')->nullable();
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
        Schema::dropIfExists('booties');
    }
}
