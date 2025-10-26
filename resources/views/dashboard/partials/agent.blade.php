{{-- Inclusión de estilos para FullCalendar --}}
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
@endpush

{{-- Mensaje de bienvenida al agente --}}
<div class="card card-body">
    <h5 class="text-primary">Bienvenido, {{ auth()->user()->name }}</h5>
    <p>Este es tu panel de agente. Aquí podrás ver el estado de tus tickets y tus solicitudes.</p>
</div>

{{-- Formulario para filtrar datos por mes y año --}}
<form method="GET" action="{{ route('dashboard') }}">
    <div class="row mb-4">
        {{-- Selector de mes --}}
        <div class="col-md-3">
            <label for="month">Mes</label>
            <select name="month" id="month" class="form-select">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Selector de año --}}
        <div class="col-md-3">
            <label for="year">Año</label>
            <select name="year" id="year" class="form-select">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>
        {{-- Botón para enviar el formulario de filtro --}}
        <div class="col-md-3 align-self-end">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </div>
</form>

{{-- Sección de estadísticas de tickets --}}
<div class="row">
    @foreach($estados as $estado)
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ $estado['nombre'] }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-muted fs-14 mb-0">
                                {{ $estado['porcentaje'] }}%
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                <span class="counter-value" data-target="{{ $estado['total'] }}">0</span>
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-{{ $estado['color'] }} rounded fs-3">
                                <i class="{{ $estado['icono'] }} text-{{ $estado['color'] }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Sección de gráficos --}}
<div class="row">
    {{-- Gráfico de tickets por categoría --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tickets por Categoría</h4>
                <span class="text-muted">Mes actual: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>
            <div class="card-body">
                <div id="grafico_tickets_categoria" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    {{-- Gráfico de tickets por prioridad --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tickets por Prioridad</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>
            <div class="card-body">
                <div id="grafico_tickets_prioridad" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Calendario de tickets asignados --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Calendario de Tickets Asignados</h4>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

{{-- Inclusión de scripts para el dashboard --}}
@push('dashboard-scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración y renderización del gráfico de barras para 'Tickets por Categoría'
            var optionsCategoria = {
                chart: {
                    type: 'bar',
                    height: 300
                },
                series: [{
                    name: 'Tickets',
                    data: @json($categoriasData->pluck('total'))
                }],
                xaxis: {
                    categories: @json($categoriasData->pluck('nombre')),
                    labels: {
                        rotate: -45
                    }
                },
                colors: ['#556ee6']
            };
            new ApexCharts(document.querySelector("#grafico_tickets_categoria"), optionsCategoria).render();

            // Configuración y renderización del gráfico de barras para 'Tickets por Prioridad'
            var optionsPrioridad = {
                chart: {
                    type: 'bar',
                    height: 300
                },
                series: [{
                    name: 'Tickets',
                    data: @json($prioridadesData->pluck('total'))
                }],
                xaxis: {
                    categories: @json($prioridadesData->pluck('nombre')),
                    labels: {
                        rotate: -45
                    }
                },
                colors: ['#34c38f']
            };
            new ApexCharts(document.querySelector("#grafico_tickets_prioridad"), optionsPrioridad).render();

            // Configuración y renderización del calendario de FullCalendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Vista inicial del mes
                locale: 'es', // Idioma español
                height: 500,
                events: @json($eventosCalendar), // Carga los eventos desde el controlador
                // Manejador de eventos para hacer clic en un evento del calendario
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // Previene el comportamiento por defecto
                    // Si el evento tiene una URL, la abre en una nueva pestaña
                    if (info.event.url) {
                        window.open(info.event.url, '_blank');
                    }
                }
            });
            calendar.render();
        });
    </script>
@endpush
