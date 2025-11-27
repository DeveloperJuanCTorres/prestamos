<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const wrapper  = document.getElementById('select2-custom');
            const dropdown = document.getElementById('select2-dropdown');
            const search   = document.getElementById('select2-search');
            const results  = document.getElementById('select2-results');
            const hidden   = document.getElementById('client_id');
            const text     = document.getElementById('select2-text');

            let timer = null;

            // ✅ ABRIR SELECT
            wrapper.addEventListener('click', function () {
                dropdown.style.display = 'block';
                search.focus();
                fetchClients('');
            });

            // ✅ BUSCAR
            search.addEventListener('keyup', function () {
                clearTimeout(timer);
                let q = this.value.trim();

                timer = setTimeout(() => {
                    fetchClients(q);
                }, 300);
            });

            // ✅ AJAX
            function fetchClients(q) {
                fetch(`/clients/search?q=${q}`)
                    .then(res => res.json())
                    .then(data => {
                        results.innerHTML = '';

                        if (!data.length) {
                            results.innerHTML = `<div class="select2-item disabled">Sin resultados</div>`;
                            return;
                        }

                        data.forEach(client => {
                            let div = document.createElement('div');
                            div.className = 'select2-item';
                            div.textContent = `${client.name} — ${client.numero_doc}`;

                            div.onclick = () => {
                                hidden.value = client.id;
                                text.textContent = div.textContent;
                                dropdown.style.display = 'none';
                            };

                            results.appendChild(div);
                        });
                    });
            }

            // ✅ CERRAR AL HACER CLICK AFUERA
            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

        });
    </script>




    <!-- <script src='assets/plugins/fullcalendar/js/moment.min.js'></script>
    <script src='assets/plugins/fullcalendar/js/fullcalendar.min.js'></script>
    <script src="assets/plugins/fullcalendar/js/fullcalendar-custom-script.js"></script> -->

    @stack('script')
</body>
</html>
