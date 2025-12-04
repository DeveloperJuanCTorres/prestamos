<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CREDI-ANRO') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

   
    <!-- loader-->
    <link href="{{asset ('assets/css/pace.min.css')}}" rel="stylesheet"/>
    <script src="{{asset ('assets/js/pace.min.js')}}"></script>
    <!--favicon-->
    <link rel="icon" href="{{asset ('assets/images/favicon.ico')}}" type="image/x-icon">
    <!--Full Calendar Css-->
    <link href="{{asset ('assets/plugins/fullcalendar/css/fullcalendar.min.css')}}" rel='stylesheet'/>
    <!-- Vector CSS -->
    <!-- <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/> -->
    <!-- simplebar CSS-->
    <link href="{{asset ('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet"/>
    <!-- Bootstrap core CSS-->
    <link href="{{asset ('assets/css/bootstrap.min.css')}}" rel="stylesheet"/>
    
    <!-- animate CSS-->
    <link href="{{asset ('assets/css/animate.css')}}" rel="stylesheet" type="text/css"/>
    <!-- Icons CSS-->
    <link href="{{asset ('assets/css/icons.css')}}" rel="stylesheet" type="text/css"/>
    <!-- Sidebar CSS-->
    <link href="{{asset ('assets/css/sidebar-menu.css')}}" rel="stylesheet"/>
    <!-- Custom Style-->
    <link href="{{asset ('assets/css/app-style.css')}}" rel="stylesheet"/>

    <link href="{{asset ('assets/css/styles.css')}}" rel="stylesheet"/>


 
    

    <!-- Scripts -->
    <!-- vite(['resources/sass/app.scss', 'resources/js/app.js']) -->
</head>
<body class="bg-theme bg-theme9">
    <div id="app">
        <main>
            @yield('content')
            
        </main>
    </div>
    


    <!-- Bootstrap core JavaScript-->
    <script src="{{asset ('assets/js/jquery.min.js')}}"></script>
    <script src="{{asset ('assets/js/popper.min.js')}}"></script>
    <script src="{{asset ('assets/js/bootstrap.min.js')}}"></script>
        
    <!-- simplebar js -->
    <script src="{{asset ('assets/plugins/simplebar/js/simplebar.js')}}"></script>
    <!-- sidebar-menu js -->
    <script src="{{asset ('assets/js/sidebar-menu.js')}}"></script>
    <!-- loader scripts -->
    <!-- <script src="assets/js/jquery.loading-indicator.js"></script> -->
    <!-- Custom scripts -->
    <script src="{{asset ('assets/js/app-script.js')}}"></script>
    <!-- Chart js -->
    
    <script src="{{asset ('assets/plugins/Chart.js/Chart.min.js')}}"></script>
    
    <!-- Index js -->
    <!-- <script src="assets/js/index.js"></script> -->

    <!-- Full Calendar -->
     <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            $('#client_id').select2({
                width: '100%',
                placeholder: '-- Seleccionar cliente --',
                allowClear: true,
                minimumInputLength: 0,

                language: {
                    searching: () => "Buscando...",
                    noResults: () => "No se encontraron resultados",
                    inputTooShort: () => "Escriba para buscar...",
                    searchPlaceholder: "Buscar por nombre o DNI..." // ✅ ESTE ES EL QUE FUNCIONA
                },

                ajax: {
                    url: '/clients/search',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            q: params.term ?? ''
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(item => ({
                                id: item.id,
                                text: item.name + ' — ' + item.numero_doc
                            }))
                        };
                    }
                }
            });
            

        });
    </script>

    <script>
        (function () {

            function initSidebarAutoClose() {
                var wrapper = document.getElementById('wrapper');
                var sidebar = document.getElementById('sidebar-wrapper'); // ✅ TU MENÚ REAL
                var overlay = document.querySelector('.overlay');
                var toggleBtn = document.querySelector('.toggle-menu');

                if (!wrapper || !sidebar) return;

                function closeSidebar() {
                    if (wrapper.classList.contains('toggled')) {
                        wrapper.classList.remove('toggled');
                        if (overlay) overlay.classList.remove('active');
                    }
                }

                // ✅ BLOQUEAR TOTALMENTE CLICKS DENTRO DEL MENÚ
                sidebar.addEventListener('click', function (e) {
                    // Si es un botón de collapse (submenu), dejar que Bootstrap actúe
                    if (e.target.closest('[data-toggle="collapse"], [data-bs-toggle="collapse"]')) {
                        return; // ✅ permite que se despliegue el submenú
                    }
                    // Para cualquier otro click dentro del menú, no cerrar
                    e.stopPropagation();
                }, true);

                sidebar.addEventListener('touchstart', function (e) {
                    if (e.target.closest('[data-toggle="collapse"], [data-bs-toggle="collapse"]')) {
                        return;
                    }
                    e.stopPropagation();
                }, true);

                // ✅ EL OVERLAY SÍ CIERRA
                if (overlay) {
                    overlay.addEventListener('click', function () {
                        closeSidebar();
                    });
                }

                // ✅ SOLO CIERRA SI EL CLICK ES FUERA
                function globalCloseHandler(e) {
                    var target = e.target;

                    if (!wrapper.classList.contains('toggled')) return;

                    // ❌ NO cerrar si el click fue dentro del menú
                    if (sidebar.contains(target)) return;

                    // ❌ NO cerrar si el click fue el botón de abrir
                    if (toggleBtn && toggleBtn.contains(target)) return;

                    // ✅ SI ES FUERA, SE CIERRA
                    closeSidebar();
                }

                document.addEventListener('click', globalCloseHandler, true);
                document.addEventListener('touchstart', globalCloseHandler, true);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebarAutoClose);
            } else {
                initSidebarAutoClose();
            }

        })();
    </script>







    <!-- <script src='assets/plugins/fullcalendar/js/moment.min.js'></script>
    <script src='assets/plugins/fullcalendar/js/fullcalendar.min.js'></script>
    <script src="assets/plugins/fullcalendar/js/fullcalendar-custom-script.js"></script> -->

    @stack('script')
</body>
</html>
