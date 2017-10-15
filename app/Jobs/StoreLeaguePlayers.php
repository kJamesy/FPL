<?php

namespace App\Jobs;

use App\League;
use App\Player;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreLeaguePlayers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    public $timeout = 7200;


    protected $league;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(League $league)
    {
        $this->league = $league;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( $this->league ) {

            $fetch = $this->fetchLeague($this->league->fpl_id);

            if ( $fetch['success'] ) {
                $playersData = $fetch['playersData'];

                foreach ( $playersData as $playerData ) {
                    $this->savePlayer($playerData);
                }

            }

        }
    }


    /**
     * Refetch League given its FPL ID
     * @param $fpl_id
     * @return array
     */
    protected function fetchLeague($fpl_id)
    {
        $client = new Client();

        try {
            $res = $client->get('https://fantasy.premierleague.com/drf/leagues-classic-standings/' . (int) $fpl_id);
            $fetch = json_decode($res->getBody());

            if ( is_object($fetch) ) {

                if ( property_exists($fetch, 'standings') ) {
                    $playersData = $fetch->standings->results;

                    if ( $fetch->standings->has_next )
                       $playersData = $this->fetchLeaguePlayersDataIterator($fpl_id, 2, $playersData);

                    $success = true;

                    return compact('success', 'playersData');
                }
            }

            return ['success' => false];

        }
        catch (\Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * Iterate through subsequent pages of players data
     * @param $fpl_id
     * @param $pageNum
     * @param $playersData
     * @return array
     */
    protected function fetchLeaguePlayersDataIterator($fpl_id, $pageNum, $playersData)
    {
        $client = new Client();

        try {
            $res = $client->get('https://fantasy.premierleague.com/drf/leagues-classic-standings/' . (int) $fpl_id . '?ls-page=' . (int) $pageNum);
            $fetch = json_decode($res->getBody());

            if ( is_object($fetch) ) {

                if ( property_exists($fetch, 'standings') ) {
                    $playersData = array_merge($playersData, $fetch->standings->results);

                    if ( $fetch->standings->has_next ) {
                        $pageNum = $pageNum + 1;
                        $playersData = $this->fetchLeaguePlayersDataIterator($fpl_id, $pageNum, $playersData);
                    }
                }
            }

        }
        catch (\Exception $e) {

        }

        return $playersData;
    }

    /**
     * Store/amend the player
     *
     * @return void
     */
    protected function savePlayer($playerData)
    {
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

                dispatch(new StorePlayerScores($player));
            }
        }
        catch (\Exception $e) {

        }
    }


}
