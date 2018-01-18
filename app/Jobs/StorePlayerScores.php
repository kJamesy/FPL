<?php

namespace App\Jobs;

use App\Player;
use App\Score;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StorePlayerScores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 15;
	public $timeout = 3000;
    protected $player;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( $this->player )
            $this->refetchPlayer($this->player);
    }


    /**
     * Refetch the given player from FPL
     * @param $player
     * @return void
     */
    protected function refetchPlayer($player)
    {
//        $client = ( env('APP_ENV', 'production') === 'local')
//	        ? new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]])
//	        : new Client();

        try {
//            $res = $client->get("https://fantasy.premierleague.com/drf/entry/{$player->fpl_id}");
//            $fetch = json_decode($res->getBody());
	        $curl = curl_init();
	        curl_setopt($curl, CURLOPT_URL, "https://fantasy.premierleague.com/drf/entry/{$player->fpl_id}");

	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        $response = curl_exec($curl);
	        curl_close($curl);

	        $fetch = json_decode($response);

            if ( is_object($fetch) ) {
                if ( property_exists($fetch, 'entry') ) {
                    $player->name = $fetch->entry->player_first_name . ' ' . $fetch->entry->player_last_name;
                    $player->team_name = $fetch->entry->name;
                    $player->save();

                    $current_gameweek = (int) $fetch->entry->current_event;
                    $current_gameweek = ( $current_gameweek > 0 && $current_gameweek < 39 ) ? $current_gameweek : 38;

                    $this->storeScores($player, $current_gameweek);

                }
            }
        }
        catch (\Exception $e) {
	        file_put_contents(public_path('exception.html'), $e);
        }
    }

    /**
     * Store all gameweek scores for given player
     * @param $player
     * @param $current_game_week
     * @return void
     */
    protected function storeScores($player, $current_game_week)
    {
        for ( $game_week = 1; $game_week <= $current_game_week; $game_week++ ) {
            $newScore = $this->fetchScore($player->fpl_id, $game_week);

            if ( $newScore['success'] ) {
                $score = Score::where('player_id', $player->id)->where('game_week', $game_week)->first() ?: new Score();
                $score->player_id = $player->id;
                $score->game_week = $game_week;
                $score->raw_points = $newScore['raw_points'];
                $score->points_penalty = $newScore['points_penalty'];
                $score->net_points = $newScore['net_points'];
                $score->total_points = $newScore['total_points'];

                $score->save();
            }
        }

    }

    /**
     * Attempt to fetch the score of player of given id for given gameweek
     * @param $fpl_id
     * @param $game_week
     * @return array
     */
    protected function fetchScore($fpl_id, $game_week)
    {
//	    $client = ( env('APP_ENV', 'production') === 'local')
//		    ? new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]])
//		    : new Client();

        try {
//            $res = $client->get("https://fantasy.premierleague.com/drf/entry/{$fpl_id}/event/{$game_week}/picks");
//            $fetch = json_decode($res->getBody());

	        $curl = curl_init();
	        curl_setopt($curl, CURLOPT_URL, "https://fantasy.premierleague.com/drf/entry/{$fpl_id}/event/{$game_week}/picks");

	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        $response = curl_exec($curl);
	        curl_close($curl);

	        $fetch = json_decode($response);

            if ( is_object($fetch) ) {
                if ( property_exists($fetch, 'entry_history') ) {

                    $raw_points = (int) $fetch->entry_history->points;
                    $points_penalty = (int) $fetch->entry_history->event_transfers_cost;
                    $net_points = $raw_points - $points_penalty;
                    $total_points = $fetch->entry_history->total_points;

                    $success = true;

                    return compact('success', 'raw_points', 'points_penalty', 'net_points', 'total_points');
                }
            }

            return ['success' => false];
        }
        catch (\Exception $e) {
            return ['success' => false];
        }
    }
}
