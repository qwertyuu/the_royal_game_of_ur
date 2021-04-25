<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiceThrowerToGameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game', function (Blueprint $table) {
            $table->boolean('dice_dirty')->default(true);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
        });
        Schema::table('move', function (Blueprint $table) {
            $table->timestamp('happened_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game', function (Blueprint $table) {
            $table->dropColumn('dice_dirty');
            $table->dropColumn('started_at');
            $table->dropColumn('ended_at');
        });
        Schema::table('move', function (Blueprint $table) {
            $table->dropColumn('happened_at');
        });
    }
}
