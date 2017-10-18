<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Score extends Model
{
	use Searchable;

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
	public static function findLatestScoredGameWeek()
	{
		return cache()->remember('latest_game_week', 60, function() {
			$score = static::orderBy('game_week', 'desc')->first();
			return $score ? $score->game_week : 1;
		});
	}
}
