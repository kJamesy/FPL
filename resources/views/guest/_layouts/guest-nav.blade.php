<ul class="nav flex-column admin-nav bg-primary">
    <li class="nav-item">
        <a class="nav-link @yield('leagues_active')" href="{{ route('leagues.index') }}">Leagues</a>
    </li>
    @if ( (array_key_exists('read_players', $permissions) && $permissions['read_players']) || ! array_key_exists('read_players', $permissions) )
        <li class="nav-item">
            <a class="nav-link @yield('players_active')" href="{{ route('players.index') }}">Players</a>
        </li>
    @endif
</ul>