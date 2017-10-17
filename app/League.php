<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class League extends Model
{
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['fpl_id', 'name', 'admin_fpl_id', 'admin_name', 'admin_team_name'];


    /**
     * Validation rules
     * @var array
     */
    public static $rules = [
        'fpl_id' => 'required',
    ];

	/**
	 * A League belongsToMany Players
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function players()
	{
		return $this->belongsToMany(Player::class);
	}

    /**
     * Find resource by id
     * @param $id
     * @return mixed
     */
    public static function findResource($id)
    {
        return static::withCount('players')->find($id);
    }

    /**
     * Get all resources
     * @param array $selected
     * @param string $orderBy
     * @param string $order
     * @param null $paginate
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getResources($selected = [], $orderBy = 'updated_at', $order = 'desc', $paginate = null)
    {
        $query = static::withCount('players');

        if ( count($selected) )
            $query->whereIn('id', $selected);

        $query->orderBy($orderBy, $order);

        return (int) $paginate ? $query->paginate($paginate) : $query->get();
    }

    /**
     * Get search results
     * @param $search
     * @param int $paginate
     * @return mixed
     */
    public static function getSearchResults($search, $paginate = 25)
    {
        $searchQuery = static::search($search);
        $searchQuery->limit = 5000;
        $results = $searchQuery->get()->pluck('id');

        $query = static::whereIn('id', $results);

        return $query->paginate($paginate);
    }
}
