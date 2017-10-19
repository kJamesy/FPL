<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class Score extends Model
{
	use Searchable;

	/**
	 * Cast these attributes
	 * @var array
	 */
	protected $casts = [
		'game_week' => 'integer',
		'raw_points' => 'integer',
		'points_penalty' => 'integer',
		'net_points' => 'integer',
		'total_points' => 'integer',
		'period_total' => 'integer'
	];

	/**
	 * A Score belongsTo a Player
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function player()
	{
		return $this->belongsTo(Player::class);
	}

	/**
	 * Find latest scored gameweek or return 1
	 * @return int
	 */
	public static function findLatestGameWeek()
	{
		return cache()->remember('latest_game_week', 10, function() {
			return static::max('game_week') ?: 1;
		});
	}

	public static function getResources($leagueId = 0, $selected = [], $start_gw = 1, $end_gw = 4, $orderBy = 'name', $order = 'desc', $paginate = null)
	{
		$query = Player::join('scores', 'scores.player_id', '=', 'players.id');
		$rawQuery = '';

		for ( $i = $start_gw; $i <= $end_gw; $i++ )
			$rawQuery .= "COALESCE((SELECT net_points FROM scores WHERE game_week = $i AND player_id = players.id), 0) AS game_week_$i,";

		$rawQuery .= "CAST((SELECT COALESCE(SUM(net_points), 0) FROM scores WHERE (game_week BETWEEN $start_gw AND $end_gw) AND player_id = players.id) AS UNSIGNED) AS period_total";

		$query->select('players.*', DB::raw($rawQuery));

		if ( $leagueId === -1 )
			$query->isUnattached();
		elseif ( $leagueId )
			$query->inLeagues([$leagueId]);

		if ( count($selected) )
			$query->whereIn('id', $selected);

		$query->orderBy($orderBy, $order)->groupBy('players.id');

		return (int) $paginate ? $query->paginate($paginate) : $query->get();
	}


	/**
	 * Get search results
	 * @param $search
	 * @param int $leagueId
	 * @param bool $hasLatestScores
	 * @param int $paginate
	 *
	 * @return mixed
	 */
	public static function getSearchResults($search, $leagueId = 0, $start_gw = 1, $end_gw = 4, $paginate = 25)
	{
		$searchQuery = Player::search($search);
		$searchQuery->limit = 5000;
		$results = $searchQuery->get()->pluck('id');

		$query = Player::join('scores', 'scores.player_id', '=', 'players.id');
		$rawQuery = '';

		for ( $i = $start_gw; $i <= $end_gw; $i++ )
			$rawQuery .= "COALESCE((SELECT net_points FROM scores WHERE game_week = $i AND player_id = players.id), 0) AS game_week_$i,";

		$rawQuery .= "CAST((SELECT COALESCE(SUM(net_points), 0) FROM scores WHERE (game_week BETWEEN $start_gw AND $end_gw) AND player_id = players.id) AS UNSIGNED) AS period_total";

		$query->select('players.*', DB::raw($rawQuery));

		$query->whereIn('players.id', $results);

		if ( $leagueId )
			$query->inLeagues([$leagueId]);

		return $query->groupBy('players.id')->paginate($paginate);
	}

}
