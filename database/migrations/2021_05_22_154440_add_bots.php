<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("game", function (Blueprint $blueprint) {
            $blueprint->string("bot")->nullable()->after('dice_dirty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("game", function (Blueprint $blueprint) {
            $blueprint->dropColumn("bot");
        });
    }
}
