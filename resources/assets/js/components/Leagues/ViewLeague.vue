<template>
    <div class="mt-5">
        <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>

        <template v-if="! fetchingData">
            <div v-if="appUserHasPermission('read')">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th scope="row">League Name</th>
                            <td>{{ resource.name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">FPL ID</th>
                            <td>{{ resource.fpl_id }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Total Players</th>
                            <td>{{ resource.players_count }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Join Code</th>
                            <td>{{ resource.admin_fpl_id }}-{{ resource.fpl_id }}</td>
                        </tr><tr>
                            <th scope="row">Auto-join Link</th>
                            <td><i class="fa fa-external-link"></i> <a target="_blank" v-bind:href="getAutoJoinLink()">{{ getAutoJoinLink() }}</a> </td>
                        </tr>
                        <tr>
                            <th scope="row">Admin</th>
                            <td><i class="fa fa-link"></i> <a v-bind:href="adminTeamUrl">{{ resource.admin_name }}</a></td>
                        </tr>
                        <tr>
                            <th scope="row">Admin's Team</th>
                            <td><i class="fa fa-link"></i> <a v-bind:href="adminTeamUrl">{{ resource.admin_team_name }}</a></td>
                        </tr>
                        <tr>
                            <th scope="row">Fetch Date</th>
                            <td>{{ resource.created_at | dateToTheMinute }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Last Update</th>
                            <td>{{ resource.updated_at | dateToTheMinute }}</td>
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
                this.showResource();
            });
        },
        data() {
            return {
                fetchingData: true,
                resource: {id: '', name: '', fpl_id: '', players_count: '', admin_fpl_id: '', admin_name: '', admin_team_name: '', created_at: '', updated_at: '', admin_id: '', playersUrl: ''}
            }
        },
        computed: {
            adminTeamUrl() {
                let vm = this;
                return vm.resource.playersUrl + '/' + vm.resource.admin_id + '/view';
            }
        },
        methods: {
            showResource() {
                this.appShowResource();
            },
            getAutoJoinLink() {
                return "https://fantasy.premierleague.com?autojoin-code=" + this.resource.admin_fpl_id + '-' + this.resource.fpl_id;
            }
        }
    }
</script>
