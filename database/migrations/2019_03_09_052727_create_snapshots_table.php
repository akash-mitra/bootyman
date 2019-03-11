<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 30);
            $table->string('provider', 10);
            $table->string('resource_id')->nullable();
            $table->string( 'internal_snapshot_id')->nullable();
            $table->string('source_code');
            $table->string('branch');
            $table->string('commit_id');
            $table->string('type')->default('do-ubuntu-18.04');
            $table->string('status', 30);
            $table->string('env', 10);
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
        Schema::dropIfExists('snapshots');
    }
}
