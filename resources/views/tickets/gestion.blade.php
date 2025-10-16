@extends('layouts.app')

@section('title', 'Gestión de Tickets')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Gestión de Tickets</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="#">Soporte</a></li>
                            <li class="breadcrumb-item active">Gestionar Tickets</li>
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
                        <div class="table-responsive">
                            <table id="gestion-tickets-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Asunto</th>
                                        <th>Estado</th>
                                        <th>Prioridad</th>
                                        <th>Categoría</th>
                                        <th>Asignado a</th>
                                        <th>Fecha de creación</th>
                                        <th></th>
                                        <th>Acción</th>
                                        <th></th>
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
                                            <td data-order="{{ $ticket->agent ? 1 : 0 }}">
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
                                            <td>
                                                @if($ticket->status_id == 8)
                                                    <button class="btn btn-sm btn-primary disabled" disabled>Gestionar</button>
                                                @else
                                                    <button class="btn btn-sm btn-primary btn-gestionar" data-id="{{ $ticket->id }}">
                                                        Gestionar
                                                    </button>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ticket->status_id == 1 && is_null($ticket->assigned_to))
                                                    <button class="btn btn-sm btn-danger btn-cancelar-ticket" data-id="{{ $ticket->id }}" title="Cancelar Ticket">
                                                        <i class="ri-close-circle-line"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">—</span>
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

        {{-- Modal de Gestión --}}
        <div class="modal fade" id="modalGestionar" tabindex="-1" aria-labelledby="modalGestionarLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" id="modalGestionarContent">
                <!-- Aquí se insertará el contenido vía AJAX -->
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#gestion-tickets-table').DataTable({
            order: [[5, 'asc']], // columna 5 = Asignado a
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });

        $('.btn-gestionar').click(function () {
            const ticketId = $(this).data('id');

            // Llama al backend para obtener el contenido del modal
            $.ajax({
                url: `/tickets/gestion/${ticketId}`,
                type: 'GET',
                success: function (html) {
                    $('#modalGestionarContent').html(html);
                    $('#modalGestionar').modal('show');
                },
                error: function () {
                    Swal.fire("Error", "No se pudo cargar la información del ticket.", "error");
                }
            });
        });

        $(document).on('submit', '#formGestionTicket', function (e) {
            e.preventDefault();
            const id = $('.btn-gestionar').data('id'); // o mejor usar un hidden input en el formulario

            // Mostrar loader con SweetAlert2
            Swal.fire({
                title: 'Guardando cambios...',
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Por favor espera mientras se actualiza el ticket.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/tickets/actualizar/${id}`,
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    Swal.close(); // Cierra el loader
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.close(); // Cierra el loader
                    Swal.fire('Error', 'No se pudo actualizar el ticket.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-cancelar-ticket', function () {
            const ticketId = $(this).data('id');

            Swal.fire({
                title: '¿Cancelar este ticket?',
                text: 'Esta acción marcará el ticket como cancelado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {

                    // Mostrar loader con SweetAlert2
                    Swal.fire({
                        title: 'Guardando cambios...',
                        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Por favor espera mientras se actualiza el ticket.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`/tickets/${ticketId}/cancelar`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close(); // Cierra el loader
                        if (data.success) {
                            Swal.fire('Cancelado', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(() => {
                        Swal.close(); // Cierra el loader
                        Swal.fire('Error', 'No se pudo cancelar el ticket.', 'error');
                    });
                }
            });
        });
    });
</script>
@endpush
