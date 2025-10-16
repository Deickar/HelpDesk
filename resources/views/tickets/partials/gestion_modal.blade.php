<div class="modal-header">
    <h5 class="modal-title">Gestionar Ticket #{{ $ticket->id }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<form id="formGestionTicket">
    @csrf
    <div class="modal-body">
        <p><strong>Asunto:</strong> {{ $ticket->subject }}</p>
        <p><strong>Descripción:</strong> {!! $ticket->description !!}</p>
        <p><strong>Categoría:</strong> {{ $ticket->category->name }}</p>
        <p><strong>Estado:</strong> {{ $ticket->status->name }}</p>

        <div class="mb-3">
            <label for="assigned_to" class="form-label">Asignar a:</label>
            <select name="assigned_to" id="assigned_to" class="form-select">
                <option value="">-- Seleccionar --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $ticket->agent && $ticket->agent->id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="priority_id" class="form-label">Prioridad:</label>
            <select name="priority_id" id="priority_id" class="form-select">
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->id }}" {{ $ticket->priority_id == $priority->id ? 'selected' : '' }}>
                        {{ $priority->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($ticket->attachments->count())
            <hr>
            <h6>Archivos Adjuntos:</h6>
            <ul>
                @foreach ($ticket->attachments as $file)
                    <li><a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">{{ $file->file_path }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>
