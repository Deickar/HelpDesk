@extends('layouts.app')

@section('title', 'Mis Tickets de Soporte')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Listado de Tickets</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Soporte</a></li>
                            <li class="breadcrumb-item active">Mis Tickets</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    <div class="card-body">
                        <a href="{{ route('tickets.create') }}" class="btn btn-primary mb-3">Nuevo Ticket</a>

                        <table id="tickets-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Categoría</th>
                                    <th>Asignado a</th>
                                    <th>Fecha de creación</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td data-order="{{ $ticket->id }}">#{{ $ticket->id }}</td>
                                        <td>{{ $ticket->subject }}</td>
                                        <td>
                                            <span class="badge {{ $ticket->status->color }}">{{ $ticket->status->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $ticket->priority->color }}">{{ $ticket->priority->name }}</span>
                                        </td>
                                        <td>{{ $ticket->category->name }}</td>
                                        <td>
                                            <span class="badge {{ $ticket->agent ? 'bg-light text-dark' : 'bg-danger' }}">
                                                {{ $ticket->agent ? $ticket->agent->name : 'No asignado' }}
                                            </span>
                                        </td>
                                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($ticket->status_id == 8)
                                                <a href="#" class="btn btn-sm btn-info disabled" aria-disabled="true">Ver</a>
                                            @else
                                                <a href="{{ route('tickets.detalle', $ticket->id) }}" target="_blank" class="btn btn-sm btn-info">Ver</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#tickets-table').DataTable({
            order: [[0, 'desc']], // 👉 ordena por la columna 0 (ID) de forma descendente
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });
    });
</script>
@endpush
