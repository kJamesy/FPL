@extends('guest._layouts.guest-template')

@section('title', 'Scores')

@section('view-header-styles-scripts')
    <script>
        window.permissionsKey = '{!! $permissionsKey !!}';
        window.settingsKey = '{!! $settingsKey !!}';
        window.links = {
            home: '{{ route('scores.index') }}',
            base: '{{ explode( $_SERVER['SERVER_NAME'], route('scores.index'))[1] }}',
            admin: '{{ route('scores.index') }}',
        };
        latestGameWeek = '{!! $latestGameWeek !!}'
    </script>
@endsection

@section('scores_active', 'active')
@section('content')
    <div id="scores-app">
        <scores>

        </scores>
    </div>
@endsection