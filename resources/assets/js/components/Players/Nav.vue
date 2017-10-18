<template>
    <ul class="nav nav-tabs mt-3">
        <li class="nav-item" v-if="appUserHasPermission('read')">
            <router-link v-bind:to="{ name: 'players.index' }" tag="a" class="nav-link" exact><i class="fa fa-home"></i>
                All Players</router-link>
        </li>
        <li class="nav-item" v-if="appUserHasPermission('read') && appBelongingToLeague">
            <router-link v-bind:to="{ name: 'players.list', params: { leagueId: appBelongingToLeague } }" tag="a" class="nav-link" exact><i class="fa fa-folder-open"></i>
                Players in League</router-link>
        </li>
        <li class="nav-item" v-if="appUserHasPermission('read') && appUnattached">
            <router-link v-bind:to="{ name: 'players.unattached'}" tag="a" class="nav-link" exact><i class="fa fa-exclamation-circle"></i>
                Unattached Players</router-link>
        </li>
        <li class="nav-item" v-if="appUserHasPermission('create')">
            <router-link v-bind:to="{ name: 'players.create' }" tag="a" class="nav-link" exact><i class="fa fa-cloud-download"></i>
                (Re)Fetch Player</router-link>
        </li>
        <li class="nav-item" v-if="appCurrentRouteIdParam && appUserHasPermission('read')">
            <router-link v-bind:to="{ name: 'players.view', params: { id: appCurrentRouteIdParam }}" class="nav-link" exact><i class="fa fa-eye"></i>
                View Player</router-link>
        </li>
    </ul>
</template>
