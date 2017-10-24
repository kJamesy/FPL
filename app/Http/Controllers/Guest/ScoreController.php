<?php

namespace App\Http\Controllers\Guest;

use App\League;
use App\Permissions\UserPermissions;
use App\Player;
use App\Score;
use App\Settings\UserSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
	protected $redirect;
	public $rules;
	public $perPage;
	public $orderByFields;
	public $orderCriteria;
	protected $settingsKey;
	protected $policies;
	protected $policyOwnerClass;
	protected $permissionsKey;
	protected $friendlyName;
	protected $friendlyNamePlural;
	protected $latestGameWeek;
	protected $startGw;
	protected $endGw;

	/**
	 * PlayerController constructor.
	 */
	public function __construct()
	{
		$this->redirect = route('leagues.index');
		$this->perPage = 25;
		$this->orderByFields = $this->defineOrderByFields();
		$this->orderCriteria = ['asc', 'desc'];
		$this->settingsKey = 'scores';
		$this->policies = UserPermissions::getPolicies();
		$this->policyOwnerClass = Score::class;
		$this->permissionsKey = UserPermissions::getModelShortName($this->policyOwnerClass);
		$this->friendlyName = 'Score';
		$this->friendlyNamePlural = 'Scores';
		$this->latestGameWeek = Score::findLatestGameWeek();
		$this->startGw = $this->latestGameWeek > 4 ? ($this->latestGameWeek - 3) : 4;
		$this->endGw = $this->latestGameWeek;
	}

	/**
	 * Define order by fields
	 * @return array
	 */
	protected function defineOrderByFields()
	{
		$fields = ['fpl_id', 'name', 'team_name', 'period_total', 'total_points', 'created_at', 'updated_at'];

		for ( $i = 1; $i <= 38; $i++ )
			$fields[] = "game_week_$i";

		return $fields;
	}

	/**
	 * Display a listing of the resource.
	 * @param Request $request
	 * @return $this|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function index(Request $request)
	{
		if ( $request->user()->can('read', $this->policyOwnerClass) ) {
			$user = $request->user();

			$orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : $this->orderByFields[1];
			$order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : $this->orderCriteria[0];
			$perPage = (int) $request->perPage ?: $this->perPage;
			$startGw = (int) $request->startGw ?: $this->startGw;
			$endGw = (int) $request->endGw ?: $this->endGw;
			$latestGameWeek = $this->latestGameWeek;

			if ( ! $request->ajax() ) {
				return view('guest.scores')->with(['settingsKey' => $this->settingsKey, 'permissionsKey' => $this->permissionsKey, 'latestGameWeek' => $latestGameWeek]);
			}
			else {
				$settings = UserSettings::getSettings($user->id, $this->settingsKey, $orderBy, $order, $perPage, true);
				$belongingTo = (int) $request->belongingTo;
				$search = strtolower($request->search);

				$resources = $search
					? Score::getSearchResults($search, $belongingTo, $startGw, $endGw, $settings["{$this->settingsKey}_per_page"] )
					: Score::getResources($belongingTo, [], $startGw, $endGw, $settings["{$this->settingsKey}_order_by"], $settings["{$this->settingsKey}_order"], $settings["{$this->settingsKey}_per_page"] );

				$league = $belongingTo ? League::findResource($belongingTo) : null;
				$leagues = League::getAttachedResources();

//				cache()->forget('start_game_week');
//				cache()->forget('end_game_week');

				cache()->forever('start_game_week', $startGw);
				cache()->forever('end_game_week', $endGw);

				if ( $resources->count() )
					return response()->json(compact('resources', 'league', 'leagues', 'latestGameWeek', 'startGw', 'endGw'));
				else
					return response()->json(['error' => "No $this->friendlyNamePlural found"], 404);
			}
		}
		else {
			if ( $request->ajax() )
				return response()->json(['error' => 'You are not authorised to view this page.'], 403);
			else
				return redirect($this->redirect);
		}
	}

	/**
	 * Show specified resource
	 * @param $id
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id, Request $request)
	{
		$resource = Player::findResource( (int) $id );
		$leaguesUrl = route('leagues.index');
		$currentUser = $request->user();

		if ( $resource ) {
			if ( ! $currentUser->can('read', $this->policyOwnerClass) )
				return response()->json(['error' => 'You are not authorised to perform this action.'], 403);

			$resource->leaguesUrl = $leaguesUrl;

			return response()->json(compact('resource'));
		}

		return response()->json(['error' => "$this->friendlyName does not exist"], 404);
	}
}
