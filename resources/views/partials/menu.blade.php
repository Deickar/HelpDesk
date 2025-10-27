<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index.html" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index.html" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/chimp.svg') }}" alt="" height="100">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="menu-title"><span>Dashboard</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('dashboard') }}">
                        <i class="ri-window-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                {{-- Solo para CLIENT --}}
                @if(auth()->user()->role === 'client')
                    <li class="menu-title"><span>Tickets</span></li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('tickets.create') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Nuevo Ticket</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('tickets.index') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Mis Tickets</span>
                        </a>
                    </li>
                @endif

                {{-- Solo para AGENT --}}
                @if(auth()->user()->role === 'agent')
                    <li class="menu-title"><span>Asignados</span></li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('tickets.asignados') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Mis Tickets Asignados</span>
                        </a>
                    </li>
                @endif

                {{-- Solo para ADMIN --}}
                @if(auth()->user()->role === 'admin')
                    <li class="menu-title"><span>Mantenimiento</span></li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('categories.index') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Categoria</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('users.index') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Usuario</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('departments.index') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Departamentos</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('faqs.index') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Faq</span>
                        </a>
                    </li>

                    <li class="menu-title"><span>Gestión</span></li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('tickets.gestion') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Gestionar Tickets</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('tickets.conformidad') }}">
                            <i class="ri-money-dollar-circle-line"></i> <span>Conformidad Tickets</span>
                        </a>
                    </li>
                @endif

            </ul>
        </div>
    </div>


    <div class="sidebar-background"></div>
</div>
