<ul class="nav flex-column admin-nav bg-primary">
    <li class="nav-item">
        <a class="nav-link @yield('dashboard_active')" href="{{ route('admin.home') }}">Dashboard</a>
    </li>
    @if ( (array_key_exists('read_users', $permissions) && $permissions['read_users']) || ! array_key_exists('read_users', $permissions) )
        <li class="nav-item">
            <a class="nav-link @yield('users_active')" href="{{ route('users.index') }}">Users</a>
        </li>
    @endif
</ul>