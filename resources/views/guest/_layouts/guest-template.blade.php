@extends('_layouts.main-template')

@section('header-styles-scripts')
    <script>
        window.user = {!! $user !!};
        window.permissions = {!! json_encode($permissions) !!};
        window.settings = {!! json_encode($settings) !!};
    </script>
    @yield('view-header-styles-scripts')
@endsection

@section('body')
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">{{ strtoupper(config('app.name')) }}</a>
        </div>
    </nav>

    <div class="main-content" style="margin-top: 80px;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3">
                    @include('guest._layouts.guest-nav')
                </div>
                <div class="col-sm-9">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
@endsection