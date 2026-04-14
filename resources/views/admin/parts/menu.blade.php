<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow menu-native-scroll expanded" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto">
                <img height="100" width="100" src="{{Vite::adminImage('logo/logo.svg')}}">
            </li>
            <li class="nav-item nav-toggle d-xl-none"><a class="nav-link modern-nav-toggle pr-0 js-toggle-menu" data-toggle="collapse"><i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i><i class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc" data-ticon="disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}">
                <a class="d-flex align-items-center" href="{{route('admin.dashboard.index')}}">
                    <i data-feather="home"></i>
                    <span class="menu-title text-truncate" data-i18n="Dashboards">Dashboard</span>
                </a>
            </li>

            <li class=" navigation-header">
                <span data-i18n="System">System</span>
            </li>


            <li class="nav-item">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#usersSubmenu" role="button" aria-expanded="false">
                    <i data-feather="user"></i>
                    <span class="menu-title text-truncate">User</span>
                </a>
                <div class="collapse {{(
                    Route::currentRouteName() == 'admin.balances.index'
                    || Route::currentRouteName() == 'admin.users.index'
                    || Route::currentRouteName() == 'admin.assignment-of-trophies.index') ? 'show' : '' }}"
                     style="margin-left: 20px;" id="usersSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item {{ Route::currentRouteName() == 'admin.users.index' ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{route('admin.users.index')}}">
                                <i data-feather="users"></i>
                                <span class="menu-title text-truncate" data-i18n="Users">Profile info</span>
                            </a>
                        </li>

                        <li class="nav-item {{ Route::currentRouteName() == 'admin.balances.index' ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{route('admin.balances.index')}}">
                                <i data-feather='dollar-sign'></i>
                                <span class="menu-title text-truncate">Account balances</span>
                            </a>
                        </li>

                        <li class="nav-item {{ Route::currentRouteName() == 'admin.assignment-of-trophies.index' ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{route('admin.assignment-of-trophies.index')}}">
                                <i data-feather='star'></i>
                                <span class="menu-title text-truncate">Trophy management</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>




            <li class="nav-item {{ Route::currentRouteName() == 'admin.trophies.index' ? 'active' : '' }}">
                <a class="d-flex align-items-center" href="{{route('admin.trophies.index')}}">
                    <i data-feather="award"></i>
                    <span class="menu-title text-truncate" data-i18n="Trophies">Trophies</span>
                </a>
            </li>


            <li class="nav-item {{ Route::currentRouteName() == 'admin.exchanges.index' ? 'active' : '' }}">
                <a class="d-flex align-items-center" href="{{route('admin.exchanges.index')}}">
                    <i data-feather="bar-chart-2"></i>
                    <span class="menu-title text-truncate" data-i18n="Trophies">Exchanges</span>
                </a>
            </li>

            <li class="nav-item {{ Route::currentRouteName() == 'admin.items.index' ? 'active' : '' }}">
                <a class="d-flex align-items-center" href="{{route('admin.items.index')}}">
                    <i data-feather="image"></i>
                    <span class="menu-title text-truncate" data-i18n="Trophies">Items</span>
                </a>
            </li>

            <li class="nav-item {{ Route::currentRouteName() == 'admin.chests.index' ? 'active' : '' }}">
                <a class="d-flex align-items-center" href="{{route('admin.chests.index')}}">
                    <i data-feather="package"></i>
                    <span class="menu-title text-truncate" data-i18n="Trophies">Chests</span>
                </a>
            </li>

            <li class="nav-item {{ Route::currentRouteName() == 'admin.keys.index' ? 'active' : '' }}">
                <a class="d-flex align-items-center" href="{{route('admin.keys.index')}}">
                    <i data-feather="key"></i>
                    <span class="menu-title text-truncate" data-i18n="Trophies">Keys</span>
                </a>
            </li>

            @if(Auth::user()->super_admin)
                <li class="nav-item {{ Route::currentRouteName() == 'admin.admins.index' ? 'active' : '' }}">
                    <a class="d-flex align-items-center" href="{{route('admin.admins.index')}}">
                        <i data-feather="users"></i>
                        <span class="menu-title text-truncate">Administrators</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
<!-- END: Main Menu-->
