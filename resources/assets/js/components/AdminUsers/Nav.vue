<template>
    <ul class="nav nav-tabs mt-3">
        <li class="nav-item" v-if="appUserHasPermission('read')">
            <router-link v-bind:to="{ name: 'admin_users.index' }" tag="a" class="nav-link" exact><i class="fa fa-home"></i> All Users</router-link>
        </li>
        <li class="nav-item" v-if="appUserHasPermission('create')">
            <router-link v-bind:to="{ name: 'admin_users.create' }" tag="a" class="nav-link" exact><i class="fa fa-pencil"></i> New User</router-link>
        </li>
        <li class="nav-item" v-if="appCurrentRouteIdParam && appUserHasPermission('read')">
            <router-link v-bind:to="{ name: 'admin_users.view', params: { id: appCurrentRouteIdParam }}" class="nav-link" exact><i class="fa fa-user-o"></i> View User</router-link>
        </li>
        <li class="nav-item" v-if="appCurrentRouteIdParam && appUserHasPermission('update')">
            <router-link v-bind:to="{ name: 'admin_users.edit', params: { id: appCurrentRouteIdParam }}" class="nav-link" exact><i class="fa fa-edit"></i> Edit User</router-link>
        </li>
        <li class="nav-item" v-if="appCurrentRouteIdParam && appUserHasPermission('update')">
            <router-link v-bind:to="{ name: 'admin_users.edit_permissions', params: { id: appCurrentRouteIdParam }}" class="nav-link" exact><i class="fa fa-id-badge"></i> Edit Permissions</router-link>
        </li>
    </ul>
</template>