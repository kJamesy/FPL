<?php

namespace App\Http\Controllers\Guest;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LabController extends Controller
{

	public function computeIbracadabraTotals()
	{


		function fetchScore($fpl_id, $game_week)
		{
			$client = ( env('APP_ENV', 'production') === 'local')
				? new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]])
				: new Client();

			try {
				$res = $client->get("https://fantasy.premierleague.com/drf/entry/{$fpl_id}/event/{$game_week}/picks");
				$fetch = json_decode($res->getBody());

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


		function storeScores($playerData, $current_game_week)
		{
			$player = ['name' => $playerData->player_name, 'team_name' => $playerData->entry_name, 'round_totals' => []];
			$round_totals = 0;

			for ( $game_week = 5; $game_week <= $current_game_week; $game_week++ ) {
				$newScore = fetchScore($playerData->entry, $game_week);

				if ( $newScore['success'] )
					$round_totals += $newScore['net_points'];
//				    $scores[$game_week] = ['gross' => $newScore['raw_points'], 'penalty' => $newScore['points_penalty'], 'net' => $newScore['net_points']];
			}

			$player['round_totals'] = (object) $round_totals;

			$result = $playerData->player_name . ": <span style='color:red'>$round_totals</span> <br />";

			echo($result);
		}


		function fetchPlayer($playerData)
		{
			$client = ( env('APP_ENV', 'production') === 'local')
				? new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]])
				: new Client();

			try {
				$res = $client->get("https://fantasy.premierleague.com/drf/entry/{$playerData->entry}");
				$fetch = json_decode($res->getBody());

				if ( is_object($fetch) ) {
					if ( property_exists($fetch, 'entry') ) {
						$current_gameweek = (int) $fetch->entry->current_event;
						$current_gameweek = ( $current_gameweek > 0 && $current_gameweek < 39 ) ? $current_gameweek : 38;

						storeScores($playerData, $current_gameweek);
					}
				}
			}
			catch (\Exception $e) {
				file_put_contents(public_path('exception.html'), $e);
			}
		}

		function fetchLeague($fpl_id, $pageNum)
		{
			$client = ( env('APP_ENV', 'production') === 'local')
				? new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]])
				: new Client();

			try {
				$res = $client->get('https://fantasy.premierleague.com/drf/leagues-classic-standings/' . (int) $fpl_id . '?ls-page=' . (int) $pageNum);
				$fetch = json_decode($res->getBody());

				if ( is_object($fetch) ) {

					if ( property_exists($fetch, 'standings') ) {
						$playersData = $fetch->standings->results;

						foreach ( $playersData as $playerData ) {
							fetchPlayer($playerData);
						}

						if ( $fetch->standings->has_next ) {
							$pageNum = $pageNum + 1;
							fetchLeague($fpl_id, $pageNum);
						}

					}
				}

			}
			catch (\Exception $e) {
			}
		}

		fetchLeague(28266, 1);
	}
}
