<?php

namespace App\Http\Controllers\Guest;

use App\Exporters\ResourceExporter;
use App\Jobs\FetchLeaguePlayers;
use App\League;
use App\Permissions\UserPermissions;
use App\Player;
use App\Settings\UserSettings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LeagueController extends Controller
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

    /**
     * SubscriberController constructor.
     */
    public function __construct()
    {
        $this->redirect = route('leagues.index');
        $this->rules = League::$rules;
        $this->perPage = 25;
        $this->orderByFields = ['fpl_id', 'name', 'admin_fpl_id', 'admin_name', 'admin_team_name', 'players_count', 'created_at', 'updated_at'];
        $this->orderCriteria = ['asc', 'desc'];
        $this->settingsKey = 'leagues';
        $this->policies = UserPermissions::getPolicies();
        $this->policyOwnerClass = League::class;
        $this->permissionsKey = UserPermissions::getModelShortName($this->policyOwnerClass);
        $this->friendlyName = 'League';
        $this->friendlyNamePlural = 'Leagues';
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ( $request->user()->can('read', $this->policyOwnerClass) ) {
            $user = $request->user();

            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : $this->orderByFields[0];
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : $this->orderCriteria[1];
            $perPage = (int) $request->perPage ?: $this->perPage;

            if ( ! $request->ajax() ) {
                return view('guest.leagues')->with(['settingsKey' => $this->settingsKey, 'permissionsKey' => $this->permissionsKey]);
            }
            else {
                $settings = UserSettings::getSettings($user->id, $this->settingsKey, $orderBy, $order, $perPage, true);
                $search = strtolower($request->search);

                $resources = $search
                    ? League::getSearchResults($search, $settings["{$this->settingsKey}_per_page"] )
                    : League::getResources([], $settings["{$this->settingsKey}_order_by"], $settings["{$this->settingsKey}_order"], $settings["{$this->settingsKey}_per_page"] );

                if ( $resources->count() )
                    return response()->json(compact('resources'));
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
     * @return League|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ( $request->user()->can('create', $this->policyOwnerClass) ) {

            $this->validate($request, $this->rules);

            $client = new Client();

            try {
                $res = $client->get('https://fantasy.premierleague.com/drf/leagues-classic-standings/' . (int) $request->fpl_id);
                $fetch = json_decode($res->getBody());

                if ( is_object($fetch) ) {

                    if ( property_exists($fetch, 'league') && property_exists($fetch, 'standings') ) {
                        $name = $fetch->league->name;
                        $admin_entry = (int) $fetch->league->admin_entry;

                        $admin = $this->fetchPlayer($admin_entry);

                        if ( $admin['success'] && $name ) {
                            $league = League::where('fpl_id', (int) $request->fpl_id)->first() ?: new League();
                            $league->fpl_id = (int) $request->fpl_id;
                            $league->name = $name;
                            $league->admin_fpl_id = $admin_entry;
                            $league->admin_name = $admin['name'];
                            $league->admin_team_name = $admin['team_name'];

                            $league->save();

                            FetchLeaguePlayers::dispatch($league)->onQueue('high');

                            return $league;
                        }

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
     * Fetch player of supplied fpl_id
     * @param $fpl_id
     * @return array
     */
    protected function fetchPlayer($fpl_id)
    {
        $client = new Client();

        try {
            $res = $client->get("https://fantasy.premierleague.com/drf/entry/{$fpl_id}");
            $fetch = json_decode($res->getBody());

            if ( is_object($fetch) ) {

                if ( property_exists($fetch, 'entry') ) {
                    $name = $fetch->entry->player_first_name . ' ' . $fetch->entry->player_last_name;
                    $team_name = $fetch->entry->name;

                    $success = true;

                    return compact('success', 'name', 'team_name');

                }
            }

            return ['success' => false];
        }
        catch (\Exception $e) {
            return ['success' => false];
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
		$resource = League::findResource( (int) $id );
		$playersUrl = route('players.index');
		$currentUser = $request->user();

		if ( $resource ) {
			if ( ! $currentUser->can('read', $this->policyOwnerClass) )
				return response()->json(['error' => 'You are not authorised to perform this action.'], 403);

			$admin = Player::where('fpl_id', $resource->admin_fpl_id)->first();

			if ( ! $admin )
				return response()->json(['error' => "$this->friendlyName does not exist"], 404);

			$resource->admin_id = $admin->id;
			$resource->playersUrl = $playersUrl;

			return response()->json(compact('resource'));
		}

		return response()->json(['error' => "$this->friendlyName does not exist"], 404);
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

			$resources = League::getResources($resourceIds);
			$fileName .= count($resourceIds) ? "Specified-{$this->friendlyNamePlural}-" : "All-{$this->friendlyNamePlural}-";
			$fileName .= Carbon::now()->toDateString();

			$exporter = new ResourceExporter($resources, $fileName);
			return $exporter->generateExcelExport('leagues');
		}
		else
			return redirect()->back();
	}

}
