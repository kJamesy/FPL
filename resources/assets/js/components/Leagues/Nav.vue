<template>
    <ul class="nav nav-tabs mt-3">
        <li class="nav-item">
            <router-link v-bind:to="{ name: 'leagues.index' }" tag="a" class="nav-link" exact><i class="fa fa-home"></i>
                All Leagues</router-link>
        </li>
        <li class="nav-item">
            <router-link v-bind:to="{ name: 'leagues.create' }" tag="a" class="nav-link" exact><i class="fa fa-pencil"></i>
                Fetch New League</router-link>
        </li>
        <li class="nav-item" v-if="appCurrentRouteIdParam">
            <router-link v-bind:to="{ name: 'leagues.view', params: { id: appCurrentRouteIdParam }}" class="nav-link" exact><i class="fa fa-eye"></i>
                View League</router-link>
        </li>
    </ul>
</template>
