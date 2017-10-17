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


}
