<template>
    <div class="mt-5">
        <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>

        <template v-if="! fetchingData">
            <div v-if="appUserHasPermission('read')">
                <h3>
                    {{ resource.name }}
                </h3>
                <div class="row">
                    <div class="col-sm-2 text-sm-right font-weight-bold">FPL ID:</div>
                    <div class="col-sm-10">{{ resource.fpl_id }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-2 text-sm-right font-weight-bold">Team Name:</div>
                    <div class="col-sm-10">{{ resource.team_name }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-2 text-sm-right font-weight-bold" title="These are the leagues that have been fetched from fantasypremierleague.com" data-toggle="tooltip">Leagues:</div>
                    <div class="col-sm-10" v-html="flattenedLeagues"></div>
                </div>
                <div class="row">
                    <div class="col-sm-2 text-sm-right font-weight-bold">First Fetched:</div>
                    <div class="col-sm-10">{{ resource.created_at | dateToTheMinute }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-2 text-sm-right font-weight-bold">Last Fetched:</div>
                    <div class="col-sm-10">{{ resource.updated_at | dateToTheMinute }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-2 text-sm-right font-weight-bold">Export:</div>
                    <div class="col-sm-10"><span class="btn btn-sm" v-on:click.prevent="exportPlayer()"><i class="fa fa-arrow-circle-down"></i></span> </div>
                </div>
                <div v-if="resource.scores.length" class="table-responsive mt-3">
                    <table class="table table-striped">
                        <thead>
                            <tr class="pointer-cursor">
                                <th v-on:click.prevent="changeSort('game_week')">Game-week <span v-html="getSortMarkup('game_week')"></span></th>
                                <th v-on:click.prevent="changeSort('raw_points')">Gross Points <span v-html="getSortMarkup('raw_points')"></span></th>
                                <th v-on:click.prevent="changeSort('points_penalty')">Points Penalty <span v-html="getSortMarkup('points_penalty')"></span></th>
                                <th v-on:click.prevent="changeSort('net_points')">Net Points <span v-html="getSortMarkup('net_points')"></span></th>
                                <th v-on:click.prevent="changeSort('total_points')">Total Points <span v-html="getSortMarkup('total_points')"></span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="score in orderedScores">
                                <th scope="row">Game-week {{ score.game_week }}</th>
                                <td>{{ score.raw_points }}</td>
                                <td>{{ score.points_penalty }}</td>
                                <td>{{ score.net_points }}</td>
                                <td>{{ score.total_points }}</td>
                                <td><i class="fa fa-external-link"></i> <a v-bind:href="getFPLTeamUrl(score.game_week, resource.fpl_id)" target="_blank">Team</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else="">
                <i class="fa fa-warning"></i> {{ appUnauthorisedErrorMessage }}
            </div>
        </template>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.appInitialiseTooltip();
                this.showResource();
            });
        },
        data() {
            return {
                fetchingData: true,
                resource: {id: '', name: '', fpl_id: '', team_name: '', total_points: 0, created_at: '', updated_at: '', scores: [], latest_score: null, leagues: [], leaguesUrl: ''},
                orderBy: 'game_week',
                order: -1
            }
        },
        computed: {
            flattenedLeagues() {
                let vm = this;

                return this.resource.leagues.length
                    ? _.join(_.flatMapDeep(vm.resource.leagues, function(league) {
                        return "<i class='fa fa-link'></i> <a href='" + vm.resource.leaguesUrl + "/" + league.id + "/view'>" + league.name + "</a>";
                    }), ', ')
                    : '-';
            },
            orderedScores() {
                return _.orderBy(this.resource.scores, [this.orderBy, 'game_week'], [this.order === 1 ? 'asc' : 'desc' , 'desc']);
            },
        },
        methods: {
            showResource() {
                this.appShowResource();
            },
            changeSort(attr) {
                let vm = this;
                vm.order = ( _.toLower(vm.orderBy) === _.toLower(attr) ) ? vm.order * -1 : 1;
                vm.orderBy = _.toLower(attr);
            },
            getSortMarkup(attr) {
                let vm = this;
                let html = '';

                if ( _.toLower(vm.orderBy) === _.toLower(attr) )
                    html = ( vm.order === 1 ) ? '&darr;' : '&uarr;';
                return html;
            },
            exportPlayer() {
                let vm = this;
                window.location = vm.appResourceUrl + '/' + vm.resource.id + '/export-single';
            },
            getFPLTeamUrl(gW, fplId) {
                return "https://fantasy.premierleague.com/a/team/" + fplId + "/event/" + gW;
            }
        }
    }
</script>
