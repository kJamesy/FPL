@extends('admin._layouts.admin-template')

@section('title', 'Admin Password Reset')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8 ml-lg-auto mr-lg-auto">
            <div class="card">
                <div class="card-header">Admin Password Reset</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.auth.send_password_reset_email') }}">
                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label for="email" class="col-md-4 form-control-label">Email</label>

                            <div class="col-md-8">
                                <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" >

                                @if ($errors->has('email'))
                                    <small class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 ml-md-auto">
                                <button type="submit" class="btn btn-primary">Send Link</button>
                                <a class="btn btn-link" href="{{ route('admin.auth.show_login') }}">Login</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
