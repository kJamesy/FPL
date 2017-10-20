<?php

namespace App\Jobs;

use App\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreLeaguePlayer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 15;
	public $timeout = 3000;
	protected $playerData;
	protected $leagueId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($playerData, $leagueId)
    {
    	$this->playerData = (object) $playerData;
    	$this->leagueId = (int) $leagueId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( $this->playerData ) {
        	$playerData = $this->playerData;

	        try {
		        $fpl_id = $playerData->entry;
		        $player_name = $playerData->player_name;
		        $team_name = $playerData->entry_name;

		        if ( $fpl_id ) {
			        $player = Player::where('fpl_id', $fpl_id)->first() ?: new Player();
			        $player->fpl_id = $fpl_id;
			        $player->name = $player_name;
			        $player->team_name = $team_name;
			        $player->save();

			        if ( ! in_array($this->leagueId, $player->leagues->pluck('id')->toArray()) ) {
				        $player->leagues()->attach($this->leagueId);
				        $player->touch();
			        }

			        StorePlayerScores::dispatch($player)->onQueue('low');
		        }
	        }
	        catch (\Exception $e) {
		        file_put_contents(public_path('exception.html'), $e);
	        }
        }
    }


}
