<template>
    <div class="mt-5">
        <i class="fa fa-spinner fa-spin" v-if="fetchingData"></i>

        <form v-on:submit.prevent='updatePassword' v-if="! fetchingData">
            <div class="form-group row" v-bind:class="validationErrors.current_password ? 'has-danger' : ''">
                <label class='col-md-4 ' for="current_password">Current Password</label>
                <div class="col-md-8">
                    <input type="password" class="form-control" id="current_password" v-model="profile.current_password" v-bind:class="validationErrors.current_password ? 'is-invalid' : ''">
                    <small class="invalid-feedback">
                        {{ validationErrors.current_password }}
                    </small>
                </div>
            </div>
            <div class="form-group row" v-bind:class="validationErrors.password ? 'has-danger' : ''">
                <label class='col-md-4 ' for="password">New Password</label>
                <div class="col-md-8">
                    <input type="password" class="form-control" id="password" v-model="profile.password" v-bind:class="validationErrors.password ? 'is-invalid' : ''">
                    <small class="invalid-feedback">
                        {{ validationErrors.password }}
                    </small>
                </div>
            </div>
            <div class="form-group row" v-bind:class="validationErrors.password_confirmation ? 'has-danger' : ''">
                <label class='col-md-4 ' for="password_confirmation">New Password Confirmation</label>
                <div class="col-md-8">
                    <input type="password" class="form-control" id="password_confirmation" v-model="profile.password_confirmation" v-bind:class="validationErrors.password_confirmation ? 'is-invalid' : ''">
                    <small class="invalid-feedback">
                        {{ validationErrors.password_confirmation }}
                    </small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-8 ml-md-auto">
                    <button type="submit" class="btn btn-primary btn-outline-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.goTime();
            });
        },
        data() {
            return {
                fetchingData: true,
                profile: {current_password: '', password: '', password_confirmation: '', password_update: true},
                validationErrors: {current_password: '', password: '', password_confirmation: ''}
            }
        },
        methods: {
            goTime() {
                let vm = this;
                vm.$Progress.start();
                
                vm.$Progress.finish();
                vm.fetchingData = false;
            },
            updatePassword() {
                let vm = this;
                let progress = vm.$Progress;
                progress.start();
                vm.fetchingData = true;
                vm.appClearValidationErrors();

                vm.$http.put(vm.appResourceUrl + '/' + vm.appUser.id, vm.profile).then(function(response) {
                    vm.appCustomSuccessAlert('Password updated');
                    progress.finish();
                    vm.fetchingData = false;
                }, function(error) {
                    if ( error.status && error.status === 422 && error.data && error.data.errors ) {
                        vm.appValidationErrorAlert();

                        _.forEach(error.data.errors, function(message, field) {
                            if ( _.includes(message[0], 'confirmation') )
                                vm.$set(vm.validationErrors, 'password_confirmation', message[0]);
                            else
                                vm.$set(vm.validationErrors, field, message[0]);
                        });
                    }

                    else if ( error.status && error.status === 404 )
                        vm.appCustomErrorAlert(error.data.error);
                    else
                        vm.appGeneralErrorAlert();
                    progress.fail();
                    vm.fetchingData = false;
                });
            }
        }
    }
</script>
