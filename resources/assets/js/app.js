require('./bootstrap');

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import VueProgressBar from 'vue-progressbar';
Vue.use(VueProgressBar, { color: '#F8CA00', failedColor: '#FF003C', thickness: '5px'});

//.default to fix bug!!!
Vue.component('pagination', require('vue-bootstrap-pagination').default);


import AppListScreenPlugin from './plugins/AppListScreenPlugin';
Vue.use(AppListScreenPlugin);

import AppCreateScreenPlugin from './plugins/AppCreateScreenPlugin';
Vue.use(AppCreateScreenPlugin);

import AppShowScreenPlugin from './plugins/AppShowScreenPlugin';
Vue.use(AppShowScreenPlugin);

import AppEditScreenPlugin from './plugins/AppEditScreenPlugin';
Vue.use(AppEditScreenPlugin);

import AppHelpers from './plugins/AppHelpers';
Vue.use(AppHelpers);

/**
 * Settings
 */
import Admin from './components/Admin/Admin.vue';
import AdminDashboard from './components/Admin/Dashboard.vue';
import AdminProfile from './components/Admin/Profile.vue';
import AdminEditProfile from './components/Admin/EditProfile.vue';
import AdminEditPassword from './components/Admin/EditPassword.vue';

if ( $('#admin-app').length ) {
    let router = new VueRouter({
        mode: 'history',
        base: links.base,
        linkActiveClass: 'active',
        routes: [
            { path: '/', name: 'settings.index', component: AdminDashboard },
            { path: '/profile', name: 'settings.profile', component: AdminProfile },
            { path: '/edit-profile', name: 'settings.edit_profile', component: AdminEditProfile },
            { path: '/edit-password', name: 'settings.edit_password', component: AdminEditPassword },
            { path: '*', redirect: { name: 'settings.index' } }
        ]
    });

    new Vue({
        el: '#admin-app',
        components: {
            Admin
        },
        router: router
    });
}

/**
 * Users
 */
import AdminUsers from './components/AdminUsers/AdminUsers.vue';
import AdminUsersAll from './components/AdminUsers/AllUsers.vue';
import AdminUsersNew from './components/AdminUsers/NewUser.vue';
import AdminUsersView from './components/AdminUsers/ViewUser.vue';
import AdminUsersEdit from './components/AdminUsers/EditUser.vue';
import AdminUsersEditPermissions from './components/AdminUsers/EditUserPermissions.vue';

if ( $('#admin-users-app').length ) {
    let router = new VueRouter({
        mode: 'history',
        base: links.base,
        linkActiveClass: 'active',
        routes: [
            { path: '/', name: 'admin_users.index', component: AdminUsersAll },
            { path: '/create', name: 'admin_users.create', component: AdminUsersNew },
            { path: '/:id(\\d+)/view', name: 'admin_users.view', component: AdminUsersView },
            { path: '/:id(\\d+)/edit', name: 'admin_users.edit', component: AdminUsersEdit },
            { path: '/:id(\\d+)/permissions', name: 'admin_users.edit_permissions', component: AdminUsersEditPermissions },
            { path: '*', redirect: { name: 'admin_users.index' } }
        ]
    });

    new Vue({
        el: '#admin-users-app',
        components: {
            AdminUsers
        },
        router: router
    });
}

/**
 * Leagues
 */
import Leagues from './components/Leagues/Leagues.vue';
import LeaguesAll from './components/Leagues/AllLeagues.vue';
import LeaguesNew from './components/Leagues/NewLeague.vue';
import LeaguesView from './components/Leagues/ViewLeague.vue';

if ( $('#leagues-app').length ) {
    let router = new VueRouter({
        mode: 'history',
        base: links.base,
        linkActiveClass: 'active',
        routes: [
            { path: '/', name: 'leagues.index', component: LeaguesAll },
            { path: '/create', name: 'leagues.create', component: LeaguesNew },
            { path: '/:id(\\d+)/view', name: 'leagues.view', component: LeaguesView },
            { path: '*', redirect: { name: 'leagues.index' } }
        ]
    });

    new Vue({
        el: '#leagues-app',
        components: {
            Leagues
        },
        router: router
    });
}

/**
 * Players
 */
import Players from './components/Players/Players.vue';
import PlayersAll from './components/Players/AllPlayers.vue';
import PlayersNew from './components/Players/NewPlayer.vue';
import PlayersView from './components/Players/ViewPlayer.vue';
import PlayersEdit from './components/Players/EditPlayer.vue';

if ( $('#players-app').length ) {
    let router = new VueRouter({
        mode: 'history',
        base: links.base,
        linkActiveClass: 'active',
        routes: [
            { path: '/', name: 'players.index', component: PlayersAll },
            { path: '/:leagueId(\\d+)/in-league', name: 'players.list', component: PlayersAll },
            { path: '/unattached', name: 'players.unattached', component: PlayersAll },
            { path: '/create', name: 'players.create', component: PlayersNew },
            { path: '/:id(\\d+)/view', name: 'players.view', component: PlayersView },
            { path: '/:id(\\d+)/edit', name: 'players.edit', component: PlayersEdit },
            { path: '*', redirect: { name: 'players.index' } }
        ]
    });

    new Vue({
        el: '#players-app',
        components: {
            Players
        },
        router: router
    });
}