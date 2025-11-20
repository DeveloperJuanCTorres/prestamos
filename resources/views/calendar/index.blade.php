@extends('layouts.app')

@section('content')
    <!-- start loader -->
    <div id="pageloader-overlay" class="visible incoming"><div class="loader-wrapper-outer"><div class="loader-wrapper-inner" ><div class="loader"></div></div></div></div>
    <!-- end loader -->
    <!-- Start wrapper-->
    <div id="wrapper">

        @include('partials.sidebar')

        @include('partials.topbar')

        <div class="clearfix"></div>

        <div class="content-wrapper">
            <div class="container-fluid">            
                <div class="mt-3">
                    <div id='calendar'></div>
                </div>
                    
                <!--start overlay-->
                <div class="overlay toggle-menu"></div>
                <!--end overlay-->	
                    
            </div>
            <!-- End container-fluid-->
        </div>
        <!--End content-wrapper-->

        <!--Start Back To Top Button-->
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        <!--End Back To Top Button-->

        @include('partials.footer')
        
        @include('partials.config')
    
    </div>
    <!--End wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',

        selectable: true,
        selectMirror: true,

        select: function (info) {
            let title = prompt("Ingrese el título del recordatorio:");

            if (title) {
                calendar.addEvent({
                    title: title,
                    start: info.startStr,
                    end: info.endStr,
                    allDay: info.allDay
                });
            }

            calendar.unselect();
        }
    });

    calendar.render();
});
</script>
   

@endsection