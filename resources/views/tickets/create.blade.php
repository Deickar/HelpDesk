@extends('layouts.app')

@section('title', 'Crear Ticket')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Nuevo Ticket de Soporte</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="#">Soporte</a></li>
                            <li class="breadcrumb-item active">Crear Ticket</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    <div class="container mt-4 mb-4">
                        <a href="{{ route('tickets.index') }}" class="btn btn-primary mb-3">Volver al Listado</a>

                        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticket-form" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="subject" class="form-label">Asunto <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="subject" class="form-control" required>
                                @error('subject')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">Seleccione una categoría</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descripción <span class="text-danger">*</span></label>
                                <div class="snow-editor" style="height: 200px;">{!! old('description') !!}</div>
                                <input type="hidden" name="description" id="description">
                                @error('description')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Archivos adjuntos</label>

                                <!-- Dropzone -->
                                <div class="dropzone">
                                    <div class="fallback">
                                        <input name="files[]" type="file" multiple />
                                    </div>
                                    <div class="dz-message needsclick">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted ri-upload-cloud-2-line"></i>
                                        </div>
                                        <h4>Arrastra archivos aquí o haz clic para subirlos</h4>
                                    </div>
                                </div>

                                <!-- Template para previsualización -->
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

                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Crear Ticket</button>
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let arrDocument = [];
        Dropzone.autoDiscover = false;

        // Inicialización del editor de texto enriquecido Quill
        var snowEditors = document.querySelectorAll(".snow-editor");
        if (snowEditors) {
            Array.from(snowEditors).forEach(function(el) {
                let options = {
                    theme: "snow",
                    modules: {
                        toolbar: [
                            [{
                                font: []
                            }, {
                                size: []
                            }],
                            ["bold", "italic", "underline", "strike"],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
                            [{
                                script: "super"
                            }, {
                                script: "sub"
                            }],
                            [{
                                    header: [false, 1, 2, 3, 4, 5, 6]
                                },
                                "blockquote",
                                "code-block",
                            ],
                            [{
                                    list: "ordered"
                                },
                                {
                                    list: "bullet"
                                },
                                {
                                    indent: "-1"
                                },
                                {
                                    indent: "+1"
                                },
                            ],
                            ["direction", {
                                align: []
                            }],
                            ["link", "image", "video"],
                            ["clean"],
                        ]
                    }
                };
                let quill = new Quill(el, options);

                // Copia el contenido HTML de Quill al input hidden 'description' antes de enviar el formulario.
                document.getElementById('ticket-form').addEventListener('submit', function() {
                    document.getElementById('description').value = el.querySelector('.ql-editor').innerHTML;
                });
            });
        }

        // Configuración de Dropzone para la carga de archivos
        let previewTemplate,
            dropzone,
            dropzonePreviewNode = document.querySelector("#dropzone-preview-list");

        if (dropzonePreviewNode) {
            dropzonePreviewNode.id = "";
            previewTemplate = dropzonePreviewNode.parentNode.innerHTML;
            dropzonePreviewNode.parentNode.removeChild(dropzonePreviewNode);

            const dropzone = new Dropzone(".dropzone", {
                url: "#", // La URL es simbólica, la subida se maneja con Fetch API.
                autoProcessQueue: false,
                uploadMultiple: true,
                previewsContainer: "#dropzone-preview",
                previewTemplate: previewTemplate,
                paramName: "files[]",
                maxFilesize: 5,
                acceptedFiles: ".jpg,.jpeg,.png,.pdf,.doc,.docx"
            });

            // Evento 'addedfile': se dispara cuando se añade un archivo.
            dropzone.on("addedfile", function(file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert("Archivo excede el tamaño máximo de 2 MB.");
                    dropzone.removeFile(file);
                } else {
                    arrDocument.push(file);
                }
            });

            // Evento 'maxfilesexceeded': se dispara si se supera el límite de archivos.
            dropzone.on('maxfilesexceeded', function(file) {
                Swal.fire({
                    title: "Mesa de Ayuda",
                    text: "Solo se permiten un máximo de 5 archivos.",
                    icon: "error",
                    confirmButtonColor: "#5156be",
                });
                dropzone.removeFile(file);
            });

            // Evento 'removedfile': se dispara cuando se elimina un archivo de la vista previa.
            dropzone.on("removedfile", function(file) {
                const i = arrDocument.indexOf(file);
                if (i > -1) arrDocument.splice(i, 1);
            });
        }

        // Captura del evento 'submit' del formulario para manejarlo con Fetch API.
        document.getElementById("ticket-form").addEventListener("submit", function(e) {
            e.preventDefault();

            const form = document.getElementById("ticket-form");
            const formData = new FormData(form);

            // Añadir archivos adjuntos al formData
            arrDocument.forEach((file, index) => {
                formData.append("files[]", file);
            });

            // Muestra una alerta de carga (loader) con SweetAlert2.
            Swal.fire({
                title: 'Enviando ticket...',
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><br><br>Por favor espera mientras se guarda el ticket y se envía el correo.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Envía los datos del formulario usando Fetch API.
            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Si la respuesta es exitosa, muestra un mensaje de éxito y recarga la página.
                    // En caso de error, muestra un mensaje de error.
                    Swal.close(); // Cierra el loader
                    console.log("✅ Ticket guardado correctamente:", data);
                    Swal.fire({
                        title: "Éxito",
                        text: "El ticket ha sido guardado correctamente",
                        icon: "success",
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                        willClose: () => {
                            location.reload(); // se recarga al cerrar automáticamente
                        }
                    });
                })
                .catch(error => {
                    Swal.close(); // Cierra el loader
                    console.error("❌ Error:", error);
                    Swal.fire("Error", "Ocurrió un error al guardar el ticket", "error");
                });
        });
    });
</script>
@endpush