<?php

namespace App\Jobs;

use App\League;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchLeaguePlayers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 15;
	public $timeout = 3000;
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
	    if ( $this->league )
		    $this->fetchLeague($this->league->id, $this->league->fpl_id, 1);
    }

	/**
	 * Fetch league
	 * @param $id
	 * @param $fpl_id
	 * @param $page_num
	 */
	protected function fetchLeague($id, $fpl_id, $page_num)
	{
		$client = new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]]);

		try {
			$res = $client->get('https://fantasy.premierleague.com/drf/leagues-classic-standings/' . (int) $fpl_id . '?ls-page=' . (int) $page_num);
			$fetch = json_decode($res->getBody());

			if ( is_object($fetch) ) {

				if ( property_exists($fetch, 'standings') ) {
					$data = $fetch->standings->results;

					foreach ( $data as $playerData )
						StoreLeaguePlayer::dispatch($playerData, $id)->onQueue('medium');

					if ( $fetch->standings->has_next ) {
						$page_num = $page_num + 1;
						$this->fetchLeague($id, $fpl_id, $page_num);
					}
				}
			}

		}
		catch (\Exception $e) {
			file_put_contents(public_path('exception.html'), $e);
		}
	}

}
