<?php

namespace App\Jobs;

use App\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DispatchPlayerScoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 15;
	public $timeout = 3000;
	protected $playerData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    Player::chunk(500, function($players) {
		    foreach( $players as $player )
			    StorePlayerScores::dispatch($player);
	    });
    }
}
