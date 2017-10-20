<?php

namespace App\Http\Controllers\Guest;

use App\Exporters\ResourceExporter;
use App\Jobs\StorePlayerScores;
use App\League;
use App\Permissions\UserPermissions;
use App\Player;
use App\Score;
use App\Settings\UserSettings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlayerController extends Controller
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

	/**
	 * PlayerController constructor.
	 */
	public function __construct()
	{
		$this->redirect = route('leagues.index');
		$this->rules = Player::$rules;
		$this->perPage = 25;
		$this->orderByFields = ['fpl_id', 'name', 'team_name', 'latest_points', 'total_points', 'created_at', 'updated_at'];
		$this->orderCriteria = ['asc', 'desc'];
		$this->settingsKey = 'players';
		$this->policies = UserPermissions::getPolicies();
		$this->policyOwnerClass = Player::class;
		$this->permissionsKey = UserPermissions::getModelShortName($this->policyOwnerClass);
		$this->friendlyName = 'Player';
		$this->friendlyNamePlural = 'Players';
		$this->latestGameWeek = Score::findLatestGameWeek();
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

			$orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : $this->orderByFields[0];
			$order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : $this->orderCriteria[1];
			$perPage = (int) $request->perPage ?: $this->perPage;

			if ( ! $request->ajax() ) {
				return view('guest.players')->with(['settingsKey' => $this->settingsKey, 'permissionsKey' => $this->permissionsKey]);
			}
			else {
				$settings = UserSettings::getSettings($user->id, $this->settingsKey, $orderBy, $order, $perPage, true);
				$belongingTo = (int) $request->belongingTo;
				$search = strtolower($request->search);

				$resources = $search
					? Player::getSearchResults($search, $belongingTo, true, $settings["{$this->settingsKey}_per_page"] )
					: Player::getResources($belongingTo, true, [], $settings["{$this->settingsKey}_order_by"], $settings["{$this->settingsKey}_order"], $settings["{$this->settingsKey}_per_page"] );

				$league = $belongingTo ? League::findResource($belongingTo) : null;
				$leagues = League::getAttachedResources();
				$latestGameWeek = $this->latestGameWeek;

				if ( $resources->count() )
					return response()->json(compact('resources', 'league', 'leagues', 'latestGameWeek'));
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
	 * Store a newly created resource in storage.
	 * @param Request $request
	 * @return Player|\Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		if ( $request->user()->can('create', $this->policyOwnerClass) ) {

			$this->validate($request, $this->rules);

			$client = ( env('APP_ENV', 'production') === 'local')
				? new Client(['curl' => [CURLOPT_SSL_VERIFYPEER => false]])
				: new Client();

			try {
				$fpl_id = (int) $request->fpl_id;

				$res = $res = $client->get("https://fantasy.premierleague.com/drf/entry/" . (int) $fpl_id);
				$fetch = json_decode($res->getBody());

				if ( is_object($fetch) ) {

					if ( property_exists($fetch, 'entry') ) {
						$player = Player::where('fpl_id', $fpl_id)->first() ?: new Player();
						$player->fpl_id = $fpl_id;
						$player->name = $fetch->entry->player_first_name . ' ' . $fetch->entry->player_last_name;
						$player->team_name = $fetch->entry->name;
						$player->save();

						StorePlayerScores::dispatch($player)->onQueue('high');

						return $player;
					}
				}

				return response()->json(['error' => 'A general error occurred.'], 404);

			}
			catch (\Exception $e) {
				$message = $e->getMessage();
				return response()->json(['error' => $message], 404);
			}

		}

		return response()->json(['error' => 'You are not authorised to perform this action.'], 403);
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

	/**
	 * Export single resource to Excel
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|mixed
	 */
	public function exportSingle($id, Request $request)
	{
		$resource = Player::findResource( (int) $id );

		if ( $request->user()->can('read', $this->policyOwnerClass) && $resource ) {
			$fileName = str_slug($resource->name) . '-';
			$fileName .= Carbon::now()->toDateString();

			$exporter = new ResourceExporter($resource, $fileName);
			return $exporter->generateExcelExport('player');
		}
		else
			return redirect()->back();
	}

	/**
	 * Export resources to Excel
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|mixed
	 */
	public function export(Request $request)
	{
		if ( $request->user()->can('read', $this->policyOwnerClass) ) {
			$resourceIds = (array) $request->resourceIds;
			$fileName = '';

			$resources = Player::getResources(0, true, $resourceIds);
			$fileName .= count($resourceIds) ? "Specified-{$this->friendlyNamePlural}-" : "All-{$this->friendlyNamePlural}-";
			$fileName .= Carbon::now()->toDateString();

			$exporter = new ResourceExporter($resources, $fileName);
			return $exporter->generateExcelExport('players');
		}
		else
			return redirect()->back();
	}

}
