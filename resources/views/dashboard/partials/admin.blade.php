@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
@endpush

<div class="card card-body">
    <h5 class="text-primary">Bienvenido, {{ auth()->user()->name }}</h5>
    <p>Este es tu panel de administrador. Aquí podrás ver el estado de tus tickets y tus solicitudes.</p>
</div>

<form method="GET" action="{{ route('dashboard') }}">
    <div class="row mb-4">
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
        <div class="col-md-3 align-self-end">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </div>
</form>

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

<div class="row">
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

<div class="col-xl-12 mt-4">
    <div class="card">
        <div class="card-header border-0 d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Tickets por Departamento</h4>
            <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
        </div>

        <div class="card-body">
            <div id="grafico_tickets_departamento" class="apex-charts" style="min-height: 300px;"></div>
        </div>
    </div>
</div>

<div class="row mt-4">

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Distribución por Estado</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>

            <div class="card-body">
                <div id="grafico_tickets_estado" class="apex-charts" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tickets Asignados por Agente</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>
            <div class="card-body">
                <div id="grafico_tickets_agentes" class="apex-charts" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>

</div>

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


@push('dashboard-scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gráfico por Categoría
        new ApexCharts(document.querySelector("#grafico_tickets_categoria"), {
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
                labels: { rotate: -45 }
            },
            colors: ['#556ee6']
        }).render();

        // Gráfico por Prioridad
        new ApexCharts(document.querySelector("#grafico_tickets_prioridad"), {
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
                labels: { rotate: -45 }
            },
            colors: ['#34c38f']
        }).render();

        // Gráfico por Departamento
        new ApexCharts(document.querySelector("#grafico_tickets_departamento"), {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Tickets',
                data: @json($departamentosData->pluck('total'))
            }],
            xaxis: {
                categories: @json($departamentosData->pluck('nombre')),
                labels: { rotate: -45 }
            },
            colors: ['#f1b44c']
        }).render();

        //Calendar
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 500,
            events: @json($eventosCalendar),
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                }
            }
        });

        calendar.render();

        // Gráfico de Dona por Estado
        new ApexCharts(document.querySelector("#grafico_tickets_estado"), {
            chart: {
                type: 'donut',
                height: 400
            },
            series: @json($estados->pluck('total')),
            labels: @json($estados->pluck('nombre')),
            colors: [
                @foreach($estados as $estado)
                    getComputedStyle(document.documentElement)
                        .getPropertyValue('--vz-{{ $estado["color"] }}') || '{{ $estado["color"] }}',
                @endforeach
            ],
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " ticket(s)";
                    }
                }
            }
        }).render();

        // Gráfico de Barras Horizontales por Agente
        new ApexCharts(document.querySelector("#grafico_tickets_agentes"), {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4
                }
            },
            series: [{
                name: 'Tickets asignados',
                data: @json($agentesData->pluck('total'))
            }],
            xaxis: {
                categories: @json($agentesData->pluck('nombre')),
                title: { text: 'Cantidad de Tickets' }
            },
            colors: ['#3b76e1'],
            tooltip: {
                y: {
                    formatter: val => `${val} ticket(s)`
                }
            }
        }).render();

    });
</script>
@endpush
