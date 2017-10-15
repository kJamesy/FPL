<?php
/**
 * Created by PhpStorm.
 * User: James
 * Date: 14/10/2017
 * Time: 14:55
 */

namespace App\Http\ViewComposers;


use App\Permissions\UserPermissions;
use App\Settings\UserSettings;
use Illuminate\View\View;

class GuestComposer
{
	/**
	 * Bind data to the view.
	 *
	 * @param  View  $view
	 * @return void
	 */
	public function compose(View $view)
	{
		$user = Auth()->guard('web')->user();
		$permissions = $user ? UserPermissions::getCachedUserPermissions($user) : [];
		$settings = $user ? UserSettings::getAllUserSettings($user->id) : [];

		$view->with(compact('user', 'permissions', 'settings'));
	}
}