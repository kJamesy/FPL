@extends('guest._layouts.guest-template')

@section('title', 'Players')

@section('view-header-styles-scripts')
    <script>
        window.permissionsKey = '{!! $permissionsKey !!}';
        window.settingsKey = '{!! $settingsKey !!}';
        window.links = {
            home: '{{ route('players.index') }}',
            base: '{{ explode( $_SERVER['SERVER_NAME'], route('players.index'))[1] }}',
            admin: '{{ route('players.index') }}',
        }
    </script>
@endsection

@section('players_active', 'active')
@section('content')
    <div id="players-app">
        <players>

        </players>
    </div>
@endsection