<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaguePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('league_player', function (Blueprint $table) {
	        $table->increments('id');
	        $table->integer('league_id')->unsigned();
	        $table->integer('player_id')->unsigned();

	        $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
	        $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('league_player');
    }
}
