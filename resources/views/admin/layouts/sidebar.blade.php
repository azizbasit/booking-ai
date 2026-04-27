<style>
    .main-sidebar {
        display: flex;
        flex-direction: column;
    }

    .logout-button {
        margin-top: auto !important;
        position: absolute;
        bottom: 0px;
        left: 0px;
        width: 100%;
    }
</style>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/admin-page" class="brand-link">
        <img src="{{ asset('admin/dist/img/logo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Booking AI</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('admin/dist/img/avatar.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('appointments') }}"
                        class="nav-link {{ request()->routeIs('appointments') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-table"></i>
                        <p>Appointments</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('calendar') }}"
                        class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                        <i class="nav-icon far fa-calendar-alt"></i>
                        <p>Calendar</p>
                    </a>
                </li>

                <!-- Add this new menu item for Call Summaries -->
                <li class="nav-item">
                    <a href="{{ route('call-summaries') }}"
                        class="nav-link {{ request()->routeIs('call-summaries') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-phone"></i>
                        <p>Call Summaries</p>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Logout Button at Bottom -->
        <div class="logout-button mt-auto p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-block">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
