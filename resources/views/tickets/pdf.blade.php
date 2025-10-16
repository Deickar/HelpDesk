<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $ticket->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { color: #2c3e50; margin-bottom: 10px; }
        .section { margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f0f0f0; }
        .msg-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; border-radius: 4px; background: #fafafa; }
        .log-table td { font-size: 11px; }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 120px;
        }
        .header-title {
            flex-grow: 1;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <!-- Cabecera con logo y nombre del sistema -->
    <div class="header">
        <div class="header-logo">
            <img src="{{ public_path('assets/images/logo-color-andercode.png') }}" width="110">
        </div>
        <div class="header-title">
            Sistema de HelpDesk - AnderCode
        </div>
    </div>

    <h1>Ticket #{{ $ticket->id }} - {{ $ticket->subject }}</h1>

    <div class="section">
        <h3>Información general</h3>
        <table>
            <tr>
                <th>Creado por</th>
                <td>{{ $ticket->creator->name }}</td>
                <th>Departamento</th>
                <td>{{ $ticket->creator->department->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>{{ $ticket->status->name }}</td>
                <th>Prioridad</th>
                <td>{{ $ticket->priority->name }}</td>
            </tr>
            <tr>
                <th>Categoría</th>
                <td>{{ $ticket->category->name }}</td>
                <th>Agente</th>
                <td>{{ $ticket->agent->name ?? 'Sin asignar' }}</td>
            </tr>
            <tr>
                <th>Fecha de creación</th>
                <td colspan="3">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Descripción del ticket</h3>
        <div class="msg-box">
            {!! nl2br(e($ticket->description)) !!}
        </div>
    </div>

    <div class="section">
        <h3>Mensajes</h3>
        @forelse ($ticket->messages as $msg)
            <div class="msg-box">
                <strong>{{ $msg->user->name }}</strong>
                <span style="float:right;">{{ $msg->created_at->format('d/m/Y H:i') }}</span><br>
                {!! nl2br(e($msg->message)) !!}
            </div>
        @empty
            <p>No hay mensajes registrados.</p>
        @endforelse
    </div>

    <div class="section">
        <h3>Logs del ticket</h3>
        <table class="log-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ticket->logs as $log)
                    <tr>
                        <td>{{ $log->created_at ?? '—' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->user->name ?? 'Sistema' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No se han registrado logs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Pie de página -->
    <div style="position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 10px; color: #888;">
        Documento generado automáticamente por el Sistema de HelpDesk - AnderCode
    </div>
</body>
</html>
