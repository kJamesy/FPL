<template>
    <div class="mt-3">
        <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>
        <div v-if="! fetchingData && appResourceCount">
            <div v-if="appUserHasPermission('read')">
                <a href="#" v-on:click.prevent="exportAll" class="btn btn-link pull-right" title="Export All" data-toggle="tooltip"><i class="fa fa-arrow-circle-o-down"></i></a>
                <div class="clearfix mb-2"></div>
                <div class="row">
                    <div class="col-md-6">
                        <form v-on:submit.prevent="appDoSearch">
                            <label class="form-control-label">&nbsp;</label>
                            <div class="form-group">
                                <input type="text" v-model.trim="appSearchText" placeholder="Search" class="form-control" />
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form>
                            <label class="form-control-label" for="leagues">League</label>
                            <select class="custom-select form-control" v-model="league" id="leagues">
                                <option value="0">(All)</option>
                                <option v-for="league in leagues" v-bind:value="league.id">
                                    {{ league.name }}
                                </option>
                                <option value="-1">(Unattached)</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="mt-4 mb-4">
                    <form class="form-inline pull-left" v-if="appSelectedResources.length">
                        <label class="form-control-label mr-sm-2" for="quick-edit">Options</label>
                        <select class="custom-select form-control mb-2 mb-sm-0 mr-sm-5" v-model="appQuickEditOption" id="quick-edit">
                            <option v-for="option in quickEditOptions" v-bind:value="option.value" v-if="appUserHasPermission(option.value)">
                                {{ option.text }}
                            </option>
                        </select>
                    </form>
                    <form class="form-inline pull-right">
                        <span class="mr-3">Page {{ appPagination.current_page }} of {{ appPagination.last_page }} [<b>{{ appPagination.total }} items</b>]</span>
                        <label class="form-control-label mr-sm-2" for="records_per_page">Per Page</label>
                        <select class="custom-select form-control mb-2 mb-sm-0" v-model="appPerPage" id="records_per_page">
                            <option v-for="option in appPerPageOptions" v-bind:value="option.value">
                                {{ option.text }}
                            </option>
                        </select>
                    </form>
                    <div class="clearfix"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr class="pointer-cursor">
                                <th class="normal-cursor" v-if="appUserHasPermission('update')">
                                    <label class="custom-control custom-checkbox mr-0">
                                        <input type="checkbox" class="custom-control-input" v-model="appSelectAll">
                                        <span class="custom-control-indicator"></span>
                                    </label>
                                </th>
                                <th v-on:click.prevent="appChangeSort('name')">Name <span v-html="appGetSortMarkup('name')"></span></th>
                                <th v-on:click.prevent="appChangeSort('fpl_id')">FPL ID <span v-html="appGetSortMarkup('fpl_id')"></span></th>
                                <th v-on:click.prevent="appChangeSort('team_name')">Team Name <span v-html="appGetSortMarkup('team_name')"></span></th>
                                <th v-on:click.prevent="appChangeSort('latest_points')">Game-week {{ latestGameWeek }} Points <span v-html="appGetSortMarkup('latest_points')"></span></th>
                                <th v-on:click.prevent="appChangeSort('total_points')">Total Points <span v-html="appGetSortMarkup('total_points')"></span></th>
                                <th v-on:click.prevent="appChangeSort('updated_at')" >Updated <span v-html="appGetSortMarkup('updated_at')"></span></th>
                                <th v-if="appUserHasPermission('update')"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="resource in orderedAppResources">
                                <td v-if="appUserHasPermission('update')">
                                    <label class="custom-control custom-checkbox mr-0">
                                        <input type="checkbox" class="custom-control-input" v-model="appSelectedResources" v-bind:value="resource.id">
                                        <span class="custom-control-indicator"></span>
                                    </label>
                                </td>
                                <td>{{ resource.name }}</td>
                                <td>{{ resource.fpl_id }}</td>
                                <td>{{ resource.team_name }}</td>
                                <td>{{ resource.latest_score.net_points }}</td>
                                <td>{{ resource.latest_score.total_points }}</td>
                                <td><span v-bind:title="resource.updated_at | dateToTheMinWithDayOfWeek" data-toggle="tooltip">{{ resource.updated_at | dateToTheDay }}</span></td>
                                <td v-if="appUserHasPermission('read')">
                                    <router-link v-bind:to="{ name: 'players.view', params: { id: resource.id }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <pagination :pagination="appPagination" :callback="fetchResources" :options="appPaginationOptions" class="mt-5 mb-3"></pagination>
            </div>
            <div v-if="! appUserHasPermission('read')">
                <i class="fa fa-warning"></i> {{ appUnauthorisedErrorMessage }}
            </div>
        </div>
        <div v-if="! fetchingData && ! appResourceCount" class="mt-5">
            No items found
        </div>

    </div>
</template>

<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.appInitialiseSettings();
                this.appInitialiseTooltip();
                this.belongingTo = this.appBelongingToOrUnattached;
                this.fetchResources();
                this.applyListeners();
            });
        },
        data() {
            return {
                fetchingData: true,
                quickEditOptions: [
                    { text: 'Select Option', value: '' },
                    { text: 'Export', value: 'export' },
                ],
                league: 0,
                leagues: [],
                latestGameWeek: 0
            }
        },
        methods: {
            fetchResources(orderAttr, orderToggle) {
                this.appFetchResources(this, orderAttr, orderToggle);
            },
            quickEditResources() {
                this.appQuickEditResources();
            },
            exportAll() {
                this.appExportAll();
            },
            setOtherData() {
                let vm = this;
                if ( typeof vm.appFetchResponse !== 'undefined' ) {
                    let response = vm.appFetchResponse;

                    if ( response.data.leagues )
                        vm.leagues = response.data.leagues;

                    if ( response.data.latestGameWeek )
                        vm.latestGameWeek = response.data.latestGameWeek;

                    if ( response.data.league )
                        vm.league = response.data.league.id;
                    else if ( vm.appUnattached )
                        vm.league = -1;
                }
            },
            applyListeners() {
                let vm = this;

                vm.$on('successfulfetch', function () {
                    vm.setOtherData();
                });
            }
        },
        watch: {
            league(newVal) {
                let vm = this;

                if ( parseInt(newVal) > 0 )
                    vm.$router.push({ name: 'players.list', params: {leagueId: parseInt(newVal)} });
                else if ( parseInt(newVal) === -1 )
                    vm.$router.push({ name: 'players.unattached' });
                else
                    vm.$router.push({ name: 'players.index' });
            }
        },
    }
</script>
