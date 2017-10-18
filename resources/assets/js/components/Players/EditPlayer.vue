<template>
    <div class="mt-5">
        <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>

        <template v-if="! fetchingData">
            <div v-if="appUserHasPermission('update')">
                <h3 class="mb-5">
                    <i class="fa fa-edit"></i> {{ name }}
                </h3>

                <form v-on:submit.prevent='updateResource' v-if="! fetchingData ">
                    <div class="form-group row">
                        <label class="col-md-4 form-control-label" for="first_name">First Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="first_name" v-model.trim="resource.first_name" v-bind:class="validationErrors.first_name ? 'is-invalid' : ''">
                            <small class="invalid-feedback">
                                {{ validationErrors.first_name }}
                            </small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-4 form-control-label" for="last_name">Last Name</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="last_name" v-model.trim="resource.last_name" v-bind:class="validationErrors.last_name ? 'is-invalid' : ''">
                            <small class="invalid-feedback">
                                {{ validationErrors.last_name }}
                            </small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-4 form-control-label" for="email">Email</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="email" v-model.trim="resource.email" v-bind:class="validationErrors.email ? 'is-invalid' : ''">
                            <small class="invalid-feedback">
                                {{ validationErrors.email }}
                            </small>
                        </div>
                    </div>
                    <div class="form-group row checkbox">
                        <div class="col-md-8 ml-md-auto">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" v-model="resource.active">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row" v-if="mailing_lists.length">
                        <label class="col-md-4 form-control-label">Mailing Lists</label>
                        <div class="col-md-8">
                            <v-select :options="sortedMailingLists" label="name" placeholder="No Mailing Lists" v-model="selected_mailing_lists" multiple></v-select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-8 ml-md-auto">
                            <button type="submit" class="btn btn-primary btn-outline-primary">Update</button>
                            <form action="" class="form-inline pull-right">
                                <label class="form-control-label mr-sm-2" for="more-options">More Options</label>
                                <select class="custom-select form-control mb-2 mb-sm-0" v-model="moreOption" id="more-options">
                                    <option v-for="option in moreOptions" v-bind:value="option.value" v-if="appUserHasPermission(option.value)">
                                        {{ option.text }}
                                    </option>
                                </select>
                            </form>
                        </div>
                    </div>
                </form>
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
                this.getResource();
            });
        },
        data() {
            return {
                fetchingData: true,
                resource: {first_name: '', last_name: '', email: '', active: '', mailing_lists: []},
                validationErrors: {first_name: '', last_name: '', email: ''},
                mailing_lists: [],
                selected_mailing_lists: [],
                listRoute: 'admin_subscribers.index',
                moreOptions: [
                    { text: 'Select Option', value: '' },
                    { text: 'Delete Subscriber', value: 'delete' },
                ],
                moreOption: ''
            }
        },
        computed: {
            sortedMailingLists() {
                return _.sortBy(this.mailing_lists, ['name']);
            },
            flattenedMLists() {
                return _.flatMapDeep(this.selected_mailing_lists, function(mList) {
                    return mList.id;
                });
            },
            name() {
                return this.resource.first_name + ' ' + this.resource.last_name;
            }
        },
        methods: {
            getResource() {
                let vm = this;
                let progress = vm.$Progress;

                progress.start();
                vm.appClearValidationErrors();

                vm.$http.get(vm.appResourceUrl + '/' + vm.id + '/edit').then(function(response) {
                    if ( response.data ) {
                        if ( response.data.mailing_lists && response.data.mailing_lists.length )
                            vm.mailing_lists = response.data.mailing_lists;
                        vm.resource = response.data.resource;

                        if ( vm.resource.mailing_lists ) {
                            _.forEach(vm.resource.mailing_lists, function(mailing_list) {
                                vm.selected_mailing_lists.push({id: mailing_list.id, name: mailing_list.name});
                            });
                        }
                    }

                    progress.finish();
                    vm.fetchingData = false;
                }, function(error) {
                    if ( error.status && error.status === 403 && error.data )
                        vm.appCustomErrorAlert(error.data.error);
                    else
                        vm.appGeneralErrorAlert();

                    progress.fail();
                    vm.fetchingData = false;
                });
            },
            updateResource() {
                this.appUpdateResource();
            },
            deleteResource() {
                this.appDeleteResource();
            },
        },
        watch: {
            moreOption(action) {
                let vm = this;

                if ( action.length ) {
                    if ( action === 'delete' && vm.appUserHasPermission(action) ) {
                        swal({title: 'Hey, are you sure about this?', type: "warning", showCancelButton: true, confirmButtonText: _.capitalize(action)}, function (confirmed) {
                            if (confirmed)
                                vm.deleteResource();
                            else
                                vm.moreOption = '';
                        });
                    }
                }
            },
            'selected_mailing_lists': function(newVal) {
                this.resource.mailing_lists = this.flattenedMLists;
            },
        }
    }
</script>
