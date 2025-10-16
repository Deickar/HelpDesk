@extends('layouts.app')

@section('title', 'Detalle del Ticket')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- 📌 Título y breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Detalle del Ticket #{{ $ticket->id }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Soporte</a></li>
                            <li class="breadcrumb-item active">Detalle</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Datos generales y descripción del ticket --}}
        <div class="row">

            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <!-- Ver Logs -->
                    <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalLogs">
                        <i class="fas fa-eye"></i> Ver Logs
                    </button>

                    <!-- Imprimir PDF -->
                    <a href="{{ route('tickets.pdf', $ticket->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                </div>
            </div>

            <br/>
            <br/>

            {{-- 🟦 Columna de datos generales --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Información del Ticket</h5>
                        <p><strong>Asunto:</strong> {{ $ticket->subject }}</p>
                        <p><strong>Estado:</strong> <span class="badge {{ $ticket->status->color }}">{{ $ticket->status->name }}</span></p>
                        <p><strong>Prioridad:</strong> <span class="badge {{ $ticket->priority->color }}">{{ $ticket->priority->name }}</span></p>
                        <p><strong>Categoría:</strong> {{ $ticket->category->name }}</p>
                        <p><strong>Asignado a:</strong> {{ $ticket->agent ? $ticket->agent->name : 'No asignado' }}</p>
                        <p><strong>Creado por:</strong> {{ $ticket->creator->name }}</p>
                        <p><strong>Fecha:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            {{-- 🟩 Columna de descripción en formato HTML de Quill --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Descripción del Ticket</h5>
                        <div class="quill-description">
                            {!! $ticket->description !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📎 Archivos adjuntos del ticket --}}
        @if($ticket->attachments->count() > 0)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Archivos Adjuntos</h5>
                    <ul>
                        @foreach($ticket->attachments as $file)
                            <li><a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">{{ basename($file->file_path) }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- 💬 Comentarios (Mensajes del ticket) en formato Timeline --}}
        <div class="row">
            <div class="col-lg-12">
                <h5 class="card-title">Historial de Mensajes</h5>

                <div class="timeline">

                    @forelse ($ticket->messages as $message)
                        @php
                            $isAgent = $message->user->role === 'agent';
                            $position = $isAgent ? 'right' : 'left';
                            $avatar = $message->user->avatar ?? asset('assets/images/users/user-dummy-img.jpg'); // puedes personalizar esto
                        @endphp

                        <div class="timeline-item {{ $position }}">
                            <i class="icon {{ $isAgent ? 'ri-user-star-line' : 'ri-user-line' }}"></i>
                            <div class="date">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                            <div class="content">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $avatar }}" alt="avatar" class="avatar-sm rounded">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="fs-15">
                                            {{ '@' . $message->user->name }}
                                            <small class="text-muted fs-13 fw-normal"> - {{ $message->created_at->diffForHumans() }}</small>
                                        </h5>
                                        <div class="text-muted mb-2">{!! $message->message !!}</div>

                                        {{-- 📎 Adjuntos del mensaje --}}
                                        @if ($message->attachments->count() > 0)
                                            <div class="row g-2">
                                                @foreach ($message->attachments as $attach)
                                                    <div class="col-sm-6">
                                                        <div class="d-flex border border-dashed p-2 rounded position-relative">
                                                            <div class="flex-shrink-0 avatar-xs">
                                                                <div class="avatar-title bg-soft-info text-info fs-15 rounded">
                                                                    <i class="ri-attachment-line"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-2 overflow-hidden">
                                                                <h6 class="text-truncate mb-0">
                                                                    <a href="{{ asset('storage/' . $attach->file_path) }}" target="_blank" class="stretched-link">
                                                                        {{ basename($attach->file_path) }}
                                                                    </a>
                                                                </h6>
                                                                <small>{{ number_format(Storage::disk('public')->size($attach->file_path) / 1024, 2) }} KB</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>No hay mensajes aún.</p>
                    @endforelse

                </div>
            </div>
        </div>

        {{-- 📨 Formulario para responder el ticket --}}
        <div class="card mt-4">
            @if($ticket->status->name !== 'Cerrado' && $ticket->status->name !== 'Resuelto')

                <div class="card-body">
                    <h5 class="card-title">Responder Ticket</h5>

                    <form id="response-form" method="POST" enctype="multipart/form-data" action="{{ route('tickets.responder') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

                        {{-- Editor Quill --}}
                        <div class="mb-3">
                            <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                            <div class="snow-editor" style="height: 200px;"></div>
                            <input type="hidden" name="message" id="message">
                        </div>

                        {{-- Dropzone de archivos --}}
                        <div class="mb-3">
                            <label class="form-label">Adjuntar Archivos</label>
                            <div class="dropzone"></div>
                            <div class="dropzone-previews mt-3" id="dropzone-preview">
                                <div class="border rounded" id="dropzone-preview-list">
                                    <div class="d-flex p-2">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-sm bg-light rounded">
                                                <img data-dz-thumbnail class="img-fluid rounded d-block" src="#" alt="Preview" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="pt-1">
                                                <h5 class="fs-14 mb-1" data-dz-name>&nbsp;</h5>
                                                <p class="fs-13 text-muted mb-0" data-dz-size></p>
                                                <strong class="error text-danger" data-dz-errormessage></strong>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-3">
                                            <button data-dz-remove class="btn btn-sm btn-danger">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botón enviar --}}
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Enviar Respuesta</button>
                            <button type="button" class="btn btn-danger" id="btnCerrarTicket">
                                Cerrar Ticket
                            </button>
                        </div>
                    </form>

                    {{-- Formulario oculto para cerrar ticket --}}
                    <form id="formCerrarTicket" action="{{ route('tickets.cerrar', $ticket->id) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>

            @else
                {{-- Mostrar mensaje en lugar del formulario --}}
                <div class="card-body">
                    <div class="alert alert-warning mt-4">
                        Este ticket está cerrado y/o resuelto. No se pueden enviar más respuestas.
                    </div>

                    <button type="button" class="btn btn-warning mt-3" id="btnReabrirTicket">
                        Reabrir Ticket
                    </button>

                    {{-- Formulario oculto para reabrir --}}
                    <form id="formReabrirTicket" action="{{ route('tickets.reabrir', $ticket->id) }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                </div>

            @endif
        </div>

    </div>
</div>

<!-- Modal Logs -->
<div class="modal fade" id="modalLogs" tabindex="-1" aria-labelledby="modalLogsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLogsLabel">Historial de Logs del Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @foreach($ticket->logs as $log)
                        <li class="list-group-item">
                            <strong>{{ $log->created_at ?? '—' }}</strong>: {{ $log->action }}
                            <br><small class="text-muted">Por: {{ $log->user->name ?? 'Sistema' }}</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let arrDocument = [];
        Dropzone.autoDiscover = false;

        // 🖊️ Configurar Quill
        const quillEl = document.querySelector(".snow-editor");
        const quill = new Quill(quillEl, {
            theme: "snow",
            modules: {
                toolbar: [
                    [{ font: [] }, { size: [] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ color: [] }, { background: [] }],
                    [{ script: "super" }, { script: "sub" }],
                    [{ header: [false, 1, 2, 3, 4, 5, 6] }, "blockquote", "code-block"],
                    [{ list: "ordered" }, { list: "bullet" }, { indent: "-1" }, { indent: "+1" }],
                    ["direction", { align: [] }],
                    ["link", "image", "video"],
                    ["clean"]
                ]
            }
        });

        // 🎯 Captura de envío del formulario
        document.getElementById("response-form").addEventListener("submit", function (e) {
            e.preventDefault();

            // Copia el contenido de Quill al input hidden
            document.getElementById("message").value = quillEl.querySelector(".ql-editor").innerHTML;

            const form = document.getElementById("response-form");
            const formData = new FormData(form);

            // Adjuntar archivos
            arrDocument.forEach(file => {
                formData.append("files[]", file);
            });

            // Mostrar loader SweetAlert
            Swal.fire({
                title: 'Enviando respuesta...',
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Por favor espera mientras se envía tu respuesta.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar por fetch
            fetch(form.action, {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close(); // Cierra el loader
                if (data.success) {
                    Swal.fire({
                        title: "Éxito",
                        text: data.message,
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false,
                        didClose: () => location.reload()
                    });
                } else {
                    Swal.fire("Error", data.message, "error");
                }
            })
            .catch(error => {
                Swal.close(); // Cierra el loader
                console.error("❌ Error:", error);
                Swal.fire("Error", "Ocurrió un error al enviar la respuesta.", "error");
            });
        });

        // 🗂️ Configuración Dropzone
        let previewTemplate;
        const dropzonePreviewNode = document.querySelector("#dropzone-preview-list");

        if (dropzonePreviewNode) {
            dropzonePreviewNode.id = "";
            previewTemplate = dropzonePreviewNode.parentNode.innerHTML;
            dropzonePreviewNode.parentNode.removeChild(dropzonePreviewNode);

            const dropzone = new Dropzone(".dropzone", {
                url: "#", // No sube, solo visualiza
                autoProcessQueue: false,
                uploadMultiple: true,
                previewsContainer: "#dropzone-preview",
                previewTemplate: previewTemplate,
                paramName: "files[]",
                maxFilesize: 2, // MB
                acceptedFiles: ".jpg,.jpeg,.png,.pdf,.doc,.docx"
            });

            dropzone.on("addedfile", function (file) {
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire("Error", "El archivo excede los 2 MB permitidos.", "error");
                    dropzone.removeFile(file);
                } else {
                    arrDocument.push(file);
                }
            });

            dropzone.on("removedfile", function (file) {
                const i = arrDocument.indexOf(file);
                if (i > -1) arrDocument.splice(i, 1);
            });

            dropzone.on("maxfilesexceeded", function (file) {
                Swal.fire("Error", "Solo se permiten 5 archivos como máximo.", "error");
                dropzone.removeFile(file);
            });
        }

        document.getElementById('btnCerrarTicket').addEventListener('click', function () {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Una vez cerrado, no podrás modificar el ticket.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cerrar ticket',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loader mientras se envía el formulario
                    Swal.fire({
                        title: 'Cerrando ticket...',
                        html: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Por favor espera mientras se cierra el ticket.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Pequeño timeout para que se vea el loader (opcional)
                    setTimeout(() => {
                        document.getElementById('formCerrarTicket').submit();
                    }, 2000);
                }
            });
        });
    });

    document.getElementById('btnReabrirTicket').addEventListener('click', function () {
        Swal.fire({
            title: '¿Deseas reabrir este ticket?',
            text: "Esto lo volverá a dejar activo para respuestas.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, reabrir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader mientras se envía el formulario
                Swal.fire({
                    title: 'Re-abriendo ticket...',
                    html: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Por favor espera mientras se cierra el ticket.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Pequeño timeout para que se vea el loader (opcional)
                setTimeout(() => {
                    document.getElementById('formReabrirTicket').submit();
                }, 2000);
            }
        });
    });
</script>
@endpush

