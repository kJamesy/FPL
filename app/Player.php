<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Player extends Model
{
    use Searchable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['fpl_id', 'name', 'team_name'];

	/**
	 * Custom attribute
	 * @var array
	 */
	protected $appends = ['latest_score'];

	/**
	 * Validation rules
	 * @var array
	 */
	public static $rules = [
		'fpl_id' => 'required',
	];

	/**
	 * A Player belongsToMany Leagues
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function leagues()
	{
		return $this->belongsToMany(League::class);
	}

	/**
	 * A Player hasMany Scores
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function scores()
	{
		return $this->hasMany(Score::class)->orderBy('game_week', 'DESC');
	}

	/**
	 * Get latest score
	 * @return null
	 */
	public function getLatestScoreAttribute()
	{
		return $this->scores ? $this->scores->first() : null;
	}

	/**
	 * Scope for players in given leagues
	 * @param $query
	 * @param $leagueIds
	 * @return mixed
	 */
	public function scopeInLeagues($query, $leagueIds)
	{
		return $query->whereHas('leagues', function($q) use ($leagueIds) {
			$q->whereIn('id', (array) $leagueIds);
		});
	}

	/**
	 * Scope for players not attached to any leagues
	 * @param $query
	 * @return mixed
	 */
	public function scopeIsUnattached($query)
	{
		return $query->whereDoesntHave('leagues');
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scopeHasLatestScores($query)
	{
		$latestGameWeek = Score::findLatestScoredGameWeek();

		return $query->whereHas('scores', function($q) use ($latestGameWeek) {
			$q->where('game_week', $latestGameWeek);
		});
	}

	/**
	 * Find resource by id
	 * @param $id
	 * @return mixed
	 */
	public static function findResource($id)
	{
		return static::with('leagues')->with('scores')->find($id);
	}

	/**
	 * Get all resources
	 * @param int $leagueId
	 * @param bool $hasLatestScores
	 * @param array $selected
	 * @param string $orderBy
	 * @param string $order
	 * @param null $paginate
	 *
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getResources($leagueId = 0, $hasLatestScores = false, $selected = [], $orderBy = 'updated_at', $order = 'desc', $paginate = null)
	{
		if ( $orderBy == 'latest_points' || $orderBy == 'total_points' ) {
			$query = static::join('scores', function($join) {
				$join->on('scores.player_id', '=', 'players.id')->whereRaw("game_week = (select max(`game_week`) from `scores`)");
			})->select('players.*', 'scores.net_points AS latest_points', 'scores.total_points AS total_points', 'scores.player_id');
		}
		else
			$query = static::with('leagues')->with('scores');

		if ( $leagueId === -1 )
			$query->isUnattached();
		elseif ( $leagueId )
			$query->inLeagues([$leagueId]);

		if ( $hasLatestScores )
			$query->hasLatestScores();

		if ( count($selected) )
			$query->whereIn('id', $selected);

		$query->orderBy($orderBy, $order);

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
	public static function getSearchResults($search, $leagueId = 0, $hasLatestScores = false, $paginate = 25)
	{
		$searchQuery = static::search($search);
		$searchQuery->limit = 5000;
		$results = $searchQuery->get()->pluck('id');

		$query = static::whereIn('id', $results);

		if ( $leagueId )
			$query->inLeagues([$leagueId]);

		if ( $hasLatestScores )
			$query->hasLatestScores();

		return $query->paginate($paginate);
	}


}
