<template>
    <div class="mt-5">
        <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>

        <template v-if="! fetchingData">
            <div v-if="appUserHasPermission('create')">
                <form v-on:submit.prevent='createResource'>
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label" for="fpl_id" title="On your 'Points' screen on fantasypremierleague.com, this is the long number in the URL" data-toggle="tooltip">
                            FPL Player ID
                        </label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="fpl_id" v-model.trim="resource.fpl_id" v-bind:class="validationErrors.fpl_id ? 'is-invalid' : ''">
                            <small class="invalid-feedback">
                                {{ validationErrors.fpl_id }}
                            </small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-9 ml-sm-auto">
                            <button type="submit" class="btn btn-primary btn-outline-primary">Fetch</button>
                        </div>
                    </div>
                </form>
            </div>
            <div v-if="! appUserHasPermission('create')">
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
                this.goTime();
            });
        },
        data() {
            return {
                fetchingData: true,
                resource: {fpl_id: ''},
                validationErrors: {fpl_id: ''},
            }
        },
        methods: {
            goTime() {
                let vm = this;
                let progress = vm.$Progress;

                progress.start();
                vm.appClearValidationErrors();

                progress.finish();
                vm.fetchingData = false;
            },
            createResource() {
                this.appCreateResource();
            },
        },
    }
</script>
