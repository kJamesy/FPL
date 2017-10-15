<?php

namespace App\Http\Controllers\Guest;

use App\Jobs\StoreLeaguePlayers;
use App\League;
use App\Permissions\UserPermissions;
use App\Settings\UserSettings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

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
        $this->orderByFields = ['fpl_id', 'name', 'email', 'admin_name', 'created_at', 'updated_at'];
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

                            Artisan::call('supervise:queue-worker');

                            $job = (new StoreLeaguePlayers($league))->delay(Carbon::now()->addSeconds(10));
                            dispatch($job);

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
