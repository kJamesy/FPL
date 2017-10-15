@extends('guest._layouts.guest-template')

@section('title', 'Leagues')

@section('view-header-styles-scripts')
    <script>
        window.permissionsKey = '{!! $permissionsKey !!}';
        window.settingsKey = '{!! $settingsKey !!}';
        window.links = {
            home: '{{ route('leagues.index') }}',
            base: '{{ explode( $_SERVER['SERVER_NAME'], route('leagues.index'))[1] }}',
            admin: '{{ route('leagues.index') }}',
        }
    </script>
@endsection

@section('leagues_active', 'active')
@section('content')
    <div id="leagues-app">
        <leagues>

        </leagues>
    </div>
@endsection