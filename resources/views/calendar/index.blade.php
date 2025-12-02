@extends('layouts.app')

@section('content')
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        overflow-x: auto;
    }
</style>
    <!-- start loader -->
    <div id="pageloader-overlay" class="visible incoming"><div class="loader-wrapper-outer"><div class="loader-wrapper-inner" ><div class="loader"></div></div></div></div>
    <!-- end loader -->
    <!-- Start wrapper-->
    <div id="wrapper">

        @include('partials.sidebar')

        @include('partials.topbar')

        <div class="clearfix"></div>

        <div class="content-wrapper">
            <div class="container-fluid mt-3">            
                <div class="table-responsive">
                    <div id="calendar"></div>
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

    <!-- Modal Crear Evento -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-theme bg-theme-modal">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Recordatorio</h5>
                <button type="button" class="btn btn-danger btn-close" data-dismiss="modal">x</button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="eventId">

                <div class="mb-3">
                <label>Título</label>
                <input type="text" class="form-control" id="eventTitle">
                </div>

                <div class="mb-3">
                <label>Fecha Inicio</label>
                <input type="datetime-local" class="form-control" id="eventStart">
                </div>

                <div class="mb-3">
                <label>Fecha Fin</label>
                <input type="datetime-local" class="form-control" id="eventEnd">
                </div>

                <div class="form-check">
                <input class="form-check-input" type="checkbox" id="eventAllDay">
                <label class="form-check-label">Todo el día</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteEvent" style="display:none;">Eliminar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveEvent">Guardar</button>
            </div>

            </div>
        </div>
    </div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        let calendarEl = document.getElementById('calendar');
        let modal = new bootstrap.Modal(document.getElementById('eventModal'));

        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            selectMirror: true,
            height: 'auto',
            dayMaxEventRows: true,
            events: '/events',
            displayEventTime: true,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            },

            // Responsive: cambiar vista según ancho
            windowResize: function() {
                if(window.innerWidth < 768){
                    calendar.changeView('listWeek');
                } else {
                    calendar.changeView('dayGridMonth');
                }
            },

            // Crear evento
            select: function(info) {
            let modalTitle = document.getElementById('modalTitle');
            if(modalTitle) modalTitle.innerText = "Nuevo Recordatorio";

            document.getElementById('eventId').value = '';
            document.getElementById('eventTitle').value = '';

            // Prellenar fecha inicio y fin con la fecha seleccionada, hora por defecto
            let startDate = info.startStr.substring(0,10) + "T00:00"; // hora inicial por defecto 09:00
            let endDate   = info.startStr.substring(0,10) + "T00:00"; // hora final por defecto 10:00

            document.getElementById('eventStart').value = startDate;
            document.getElementById('eventEnd').value   = endDate;

            document.getElementById('eventAllDay').checked = false;
            document.getElementById('deleteEvent').style.display = 'none';

            modal.show();
        },

            // Editar / Ver evento
            eventClick: function(info) {
                let event = info.event;
                let modalTitle = document.getElementById('modalTitle');
                if(modalTitle) modalTitle.innerText = "Editar Recordatorio";

                document.getElementById('eventId').value = event.id;
                document.getElementById('eventTitle').value = event.title;
                document.getElementById('eventStart').value = event.startStr.substring(0,16);
                document.getElementById('eventEnd').value = event.endStr ? event.endStr.substring(0,16) : event.startStr.substring(0,16);
                document.getElementById('eventAllDay').checked = event.allDay;
                document.getElementById('deleteEvent').style.display = 'inline-block';
                modal.show();
            }
        });

        calendar.render();

        // Guardar / actualizar
        document.getElementById('saveEvent').addEventListener('click', function () {
            let id     = document.getElementById('eventId').value;
            let title  = document.getElementById('eventTitle').value;
            let start  = document.getElementById('eventStart').value;
            let end    = document.getElementById('eventEnd').value;
            let allDay = document.getElementById('eventAllDay').checked;

            if(!title || !start){ alert('Debe ingresar título y fecha de inicio'); return; }

            let url    = id ? `/events/${id}` : '/events';
            let method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ title, start, end, allDay })
            })
            .then(res => res.json())
            .then(event => {
                if(id){
                    let existingEvent = calendar.getEventById(id);
                    existingEvent.setProp('title', event.title);
                    existingEvent.setStart(event.start);
                    existingEvent.setEnd(event.end);
                    existingEvent.setAllDay(event.allDay);
                } else {
                    calendar.addEvent(event);
                }
                modal.hide();
            })
            .catch(err => console.error(err));
        });

        // Eliminar evento
        document.getElementById('deleteEvent').addEventListener('click', function () {
            if(!confirm('¿Eliminar este evento?')) return;
            let id = document.getElementById('eventId').value;
            fetch(`/events/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(() => {
                let event = calendar.getEventById(id);
                event.remove();
                modal.hide();
            })
            .catch(err => console.error(err));
        });

    });
</script>

   

@endsection