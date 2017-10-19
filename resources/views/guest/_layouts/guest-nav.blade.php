<ul class="nav flex-column admin-nav bg-primary">
    <li class="nav-item">
        <a class="nav-link @yield('leagues_active')" href="{{ route('leagues.index') }}">Leagues</a>
    </li>
    @if ( (array_key_exists('read_players', $permissions) && $permissions['read_players']) || ! array_key_exists('read_players', $permissions) )
        <li class="nav-item">
            <a class="nav-link @yield('players_active')" href="{{ route('players.index') }}">Players</a>
        </li>
    @endif
    @if ( (array_key_exists('read_scores', $permissions) && $permissions['read_scores']) || ! array_key_exists('read_scores', $permissions) )
        <li class="nav-item">
            <a class="nav-link @yield('scores_active')" href="{{ route('scores.index') }}">Scores</a>
        </li>
    @endif
</ul>