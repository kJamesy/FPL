@extends('admin._layouts.admin-template')

@section('title', 'Admin Login')

@section('active-login', 'active')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8 ml-lg-auto mr-lg-auto">
            <div class="card">
                <div class="card-header">Admin Login</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.auth.process_login') }}">
                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label for="username" class="col-md-4">Username or Email</label>

                            <div class="col-md-8">
                                <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" >

                                @if ($errors->has('username'))
                                    <small class="invalid-feedback">
                                        {{ $errors->first('username') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 form-control-label">Password</label>

                            <div class="col-md-8">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">

                                @if ($errors->has('password'))
                                    <small class="invalid-feedback">
                                        {{ $errors->first('password') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 ml-md-auto">
                                <div class="checkbox">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class="custom-control-indicator"></span>
                                        <span class="custom-control-description">Remember Me</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 ml-md-auto">
                                <button type="submit" class="btn btn-primary">Login</button>
                                <a class="btn btn-link" href="{{ route('admin.auth.show_password_reset') }}">Forgot Your Password?</a> |
                                <a class="btn btn-link" href="{{ route('admin.auth.show_registration') }}">Register</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
