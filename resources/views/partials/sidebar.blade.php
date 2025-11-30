<!--Start sidebar-wrapper-->
<div id="sidebar-wrapper" data-simplebar="" data-simplebar-auto-hide="true">
    <div class="brand-logo">
        <a href="index.html">
            <img src="{{asset ('assets/images/logo-icon.png')}}" class="logo-icon" alt="logo icon">
            <h5 class="logo-text">Préstamos</h5>
        </a>
    </div>
    <ul class="sidebar-menu do-nicescrol">
        <li class="sidebar-header">NAVEGACIÓN PRINCIPAL</li>
        <li class="{{ Request::is('/') ? 'active' : '' }}">
            <a href="/">
                <i class="zmdi zmdi-view-dashboard"></i> <span>Dashboard</span>
            </a>
        </li>

        <li class="{{ Request::is('clients') || Request::is('clients/*') ? 'active' : '' }}">
            <a href="/clients">
                <i class="zmdi zmdi-accounts-outline"></i> <span>Clientes</span>
            </a>
        </li>

        <li class="{{ Request::is('prestamos') || Request::is('prestamos/*') ? 'active' : '' }}">
            <a href="/prestamos">
                <i class="zmdi zmdi-format-list-bulleted"></i> <span>Mis Préstamos</span>
            </a>
        </li>        

        <li class="{{ Request::is('types') || Request::is('types/*') ? 'active' : '' }}">
            <a href="/types">
                <i class="zmdi zmdi-card-giftcard"></i> <span>Tipos de Pago</span>
            </a>
        </li>

        <li class="{{ Request::is('calendar') || Request::is('calendar/*') ? 'active' : '' }}">
            <a href="/calendar">
                <i class="zmdi zmdi-calendar-check"></i> 
                <span>Calendario</span>
                <small class="badge float-right badge-light">Nuevo</small>
            </a>
        </li>

        <li class="dropdown" class="{{ Request::is('reports') || Request::is('reports/*') ? 'active' : '' }}">
            <a href="#" class="dropdown-toggle" data-toggle="collapse" data-target="#menuReportes">
                <i class="zmdi zmdi-file-text"></i> 
                <span>Reportes</span>
            </a>

            <ul id="menuReportes" class="collapse">
                <li class="py-2">
                    <a href="{{ route('reporte.general') }}" target="_blank" class="btn btn-info text-white">
                        Reporte General
                    </a>
                </li>
                <li class="py-2">
                    <a href="{{ route('reporte.clientes') }}" target="_blank" class="btn btn-info text-white">
                        Reporte de Clientes
                    </a>
                </li>
                <!-- <li class="py-2">
                    <a href="{{ route('reporte.prestamos') }}" target="_blank" class="btn btn-info text-white">
                        Reporte de Préstamos
                    </a>
                </li> -->
                <li class="py-2">
                    <a href="{{ route('reporte.pagos') }}" target="_blank" class="btn btn-info text-white">
                        Reporte de Pagos
                    </a>
                </li>
            </ul>
        </li>       

    </ul>        
</div>
<!--End sidebar-wrapper-->