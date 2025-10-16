@extends('layouts.app')

@section('title', 'Conformidad de Tickets')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Tickets con Cierre/Resolución</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Soporte</a></li>
                        <li class="breadcrumb-item active">Conformidad</li>
                    </ol>
                </div>
            </div>
        </div></div>

        <div class="row"><div class="col-lg-12"><div class="card"><div class="card-body">
            <table id="conformidad-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Categoría</th>
                        <th>Asignado a</th>
                        <th>Creado</th>
                        <th>Ver</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td>#{{ $ticket->id }}</td>
                            <td>{{ $ticket->subject }}</td>
                            <td><span class="badge {{ $ticket->status->color }}">{{ $ticket->status->name }}</span></td>
                            <td><span class="badge {{ $ticket->priority->color }}">{{ $ticket->priority->name }}</span></td>
                            <td>{{ $ticket->category->name }}</td>
                            <td><span class="badge {{ $ticket->agent ? 'bg-light text-dark' : 'bg-danger' }}">{{ $ticket->agent->name ?? 'No asignado' }}</span></td>
                            <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($ticket->status_id == 8)
                                    <a href="#" class="btn btn-sm btn-info disabled" aria-disabled="true">Ver</a>
                                @else
                                    <a href="{{ route('tickets.detalle', $ticket->id) }}" target="_blank" class="btn btn-sm btn-info">Ver</a>
                                @endif
                            </td>
                            <td>
                                @if($ticket->status_id == 6)
                                    <button class="btn btn-success btn-sm btn-conformidad" data-id="{{ $ticket->id }}">Conformidad</button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#conformidad-table').DataTable({
            order: [[0, 'desc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        $('.btn-conformidad').click(function () {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Marcar como resuelto?',
                text: 'Este ticket se marcará como resuelto.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar',
                confirmButtonColor: '#14A44D',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/tickets/${id}/marcar-resuelto`, {_token: '{{ csrf_token() }}'}, function(response) {
                        if (response.success) {
                            Swal.fire('¡Actualizado!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
