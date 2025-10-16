<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Category;
use App\Models\User;
use App\Models\Priority;
use App\Models\Status;
use App\Models\AttachmentTicket;
use App\Models\AttachmentMessage;
use App\Models\Log;

use App\Mail\TicketCreatedMail;
use App\Mail\TicketAssignedToCreator;
use App\Mail\TicketAssignedToAgent;
use App\Mail\TicketReplyToClient;
use App\Mail\TicketReplyToAgent;
use App\Mail\TicketClosedMail;
use App\Mail\TicketReopenedMail;
use App\Mail\TicketCanceledMail;

use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    public function create()
    {
        // Obtenemos las categorías disponibles para mostrar en el formulario
        $categories = Category::all();

        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Validación de campos requeridos en el formulario de ticket
        try {
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'files.*' => 'nullable|file|max:2048', // 2MB por archivo
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Retorna errores de validación si falla alguna regla
            return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Se crea el ticket en la base de datos con estado y prioridad por defecto
            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'subject' => $request->subject,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'status_id' => 1,
                'priority_id' => 1,
                'assigned_to' => null,
            ]);

            // Verifica si el usuario subió uno o varios archivos
            if ($request->hasFile('files')) {
                $archivos = $request->file('files');

                // Asegura que $archivos sea un array
                if (!is_array($archivos)) {
                    $archivos = [$archivos];
                }

                foreach ($archivos as $file) {

                    if ($file && $file->isValid()) {
                        // Guarda el archivo en el almacenamiento público
                        $path = $file->store("attachments/tickets/{$ticket->id}", 'public');

                        // Registra el archivo en la tabla attachments_ticket
                        AttachmentTicket::create([
                            'ticket_id' => $ticket->id,
                            'file_path' => $path,
                            'file_type' => $file->getClientOriginalExtension(),
                        ]);
                    } else {
                        // Aquí podrías agregar un log si el archivo no es válido
                    }
                }
            } else {
                // No se adjuntaron archivos, se puede registrar si deseas.
            }

            DB::commit();

            // Recupera los destinatarios de las notificaciones por correo desde .env
            $destinatarios = explode(',', env('MAIL_TICKET_NOTIFICATION'));

            try {
                // Carga relaciones para usarlas en el correo (nombre del creador, categoría, etc.)
                $ticket->load(['creator.department', 'priority', 'category', 'status']);
                // Envia el correo de notificación de nuevo ticket
                Mail::to($destinatarios)->send(new TicketCreatedMail($ticket));

            } catch (\Exception $e) {
                // Si falla el correo, no se interrumpe el flujo general
            }

            // ✅ Se registra el log en la tabla `logs`
            Log::register(
                $ticket->id,
                "El usuario " . auth()->user()->name . " creó el ticket '{$ticket->subject}' con ID #{$ticket->id}."
            );

            // Retorna respuesta JSON satisfactoria
            return response()->json(['message' => 'Ticket creado con éxito'], 200);

        } catch (\Exception $e) {
            // Reversión de la transacción si ocurre algún error
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el ticket', 'error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        try {
            $tickets = Ticket::with(['status', 'priority', 'category', 'agent'])
                ->where('user_id', Auth::id())
                ->orderByDesc('id') // 👉 Orden descendente
                ->get();

            return view('tickets.index', compact('tickets'));
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al cargar los tickets.');
        }
    }

    public function gestion()
    {
        try {
            // Obtener todos los tickets del sistema, ordenando primero los no asignados
            $tickets = Ticket::with(['agent', 'priority', 'status', 'category'])
                ->orderByRaw('ISNULL(assigned_to) DESC') // Los no asignados primero
                ->orderBy('id', 'DESC')                  // Luego por ID descendente
                ->get();

            // Obtener todos los usuarios disponibles para asignar (por ejemplo, solo agentes o todos)
            $usuarios = User::all(); // Puedes filtrar por rol si lo deseas

            // Obtener todas las prioridades disponibles
            $prioridades = Priority::all();

            return view('tickets.gestion', compact('tickets', 'usuarios', 'prioridades'));
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al cargar la vista de gestión.');
        }
    }

    public function gestiondatos($id)
    {
        $ticket = Ticket::with(['agent', 'priority', 'category', 'attachments'])->findOrFail($id);
        $users = User::where('role', 'agent')->get();
        $priorities = Priority::all();

        return view('tickets.partials.gestion_modal', compact('ticket', 'users', 'priorities'));
    }

    public function actualizarGestion(Request $request, $id)
    {
        // Validación de los campos enviados desde el formulario de gestión
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'priority_id' => 'required|exists:priority,id',
        ]);

        try {
            // Buscar el ticket por su ID
            $ticket = Ticket::findOrFail($id);

            // Guardar los valores antiguos para compararlos
            $oldAssigned = $ticket->assigned_to;
            $oldPriority = $ticket->priority_id;
            $oldStatus = $ticket->status_id;

            // Actualizar datos del ticket con los nuevos valores
            $ticket->assigned_to = $request->assigned_to;
            $ticket->priority_id = $request->priority_id;

            // ✅ Establecer el estado "En Progreso" por defecto
            $ticket->status_id = 2;
            $ticket->save();

            // Cargar relaciones necesarias para mostrar información completa
            $ticket->load(['creator', 'agent', 'priority']);

            // Obtener nombre del usuario que está realizando la acción
            $userName = auth()->user()->name;

            // ✅ Log si se asigna o cambia el agente asignado
            if ($oldAssigned != $ticket->assigned_to) {
                if ($ticket->assigned_to) {
                    $assignedName = $ticket->agent ? $ticket->agent->name : 'Usuario asignado';
                    Log::register($ticket->id, "El usuario {$userName} asignó el ticket al agente {$assignedName}.");
                } else {
                    Log::register($ticket->id, "El usuario {$userName} desasignó el agente del ticket.");
                }
            }

            // ✅ Log si cambia la prioridad
            if ($oldPriority != $ticket->priority_id) {
                $oldPriorityName = optional(\App\Models\Priority::find($oldPriority))->name ?? 'Desconocida';
                $newPriorityName = $ticket->priority->name ?? 'Desconocida';
                Log::register($ticket->id, "El usuario {$userName} cambió la prioridad del ticket de {$oldPriorityName} a {$newPriorityName}.");
            }

            // ✅ Log por el cambio automático de estado
            if ($oldStatus != 2) {
                $oldStatusName = optional(\App\Models\Status::find($oldStatus))->name ?? 'Desconocido';
                $newStatusName = $ticket->status->name ?? 'En Progreso';
                Log::register($ticket->id, "El usuario {$userName} actualizó el estado del ticket de {$oldStatusName} a {$newStatusName}.");
            }

            // ✅ Enviar correo al creador del ticket si tiene email
            if ($ticket->creator && $ticket->creator->email) {
                Mail::to($ticket->creator->email)->send(new TicketAssignedToCreator($ticket));
            }

            // ✅ Enviar correo al agente asignado si tiene email
            if ($ticket->agent && $ticket->agent->email) {
                Mail::to($ticket->agent->email)->send(new TicketAssignedToAgent($ticket));
            }

            return response()->json(['success' => true, 'message' => 'Ticket actualizado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Carga el ticket con sus relaciones
            $ticket = Ticket::with([
                'category',
                'status',
                'priority',
                'agent',
                'attachments',
                'messages.user',                // autor de cada mensaje
                'messages.attachments'          // adjuntos de cada mensaje
            ])->findOrFail($id);

            return view('tickets.detalle', compact('ticket'));

        } catch (\Exception $e) {
            return redirect()->route('tickets.index')->with('error', 'No se pudo cargar el ticket.');
        }
    }

    public function responder(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'message'   => 'required|string',
            'files.*'   => 'nullable|file|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // Crear el nuevo mensaje de respuesta asociado al ticket
            $mensaje = TicketMessage::create([
                'ticket_id' => $request->ticket_id,
                'user_id'   => Auth::id(),
                'message'   => $request->message,
            ]);

            // Adjuntar archivos si existen
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store("attachments/messages/{$mensaje->id}", 'public');

                    AttachmentMessage::create([
                        'ticket_message_id' => $mensaje->id,
                        'file_path'         => $path,
                        'file_type'         => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // Obtener el ticket y la persona que responde
            $ticket = Ticket::with(['creator', 'agent', 'status'])->findOrFail($request->ticket_id);
            $responder = auth()->user();
            $userName = $responder->name;
            $oldStatusId = $ticket->status_id;

            // Determinar el nuevo estado del ticket según el rol de quien responde
            if ($responder->role === 'agent') {
                // Estado: En espera del cliente (id = 3)
                $ticket->status_id = 3;
                $ticket->save();

                // Log: registro del mensaje y cambio de estado
                Log::register($ticket->id, "El agente {$userName} respondió al ticket.");
                Log::register($ticket->id, "El estado del ticket cambió de '{$ticket->status->name}' a 'En espera del cliente'.");

                // Enviar correo al cliente
                if ($ticket->creator && $ticket->creator->email) {
                    Mail::to($ticket->creator->email)->send(new TicketReplyToClient($ticket, $mensaje, $responder));
                    Log::register($ticket->id, "Se notificó al cliente ({$ticket->creator->name}) sobre la respuesta del agente.");
                }

            } elseif ($responder->role === 'client') {
                // Estado: Respuesta del cliente (id = 4)
                $ticket->status_id = 4;
                $ticket->save();

                // Log: registro del mensaje y cambio de estado
                Log::register($ticket->id, "El cliente {$userName} respondió al ticket.");
                Log::register($ticket->id, "El estado del ticket cambió de '{$ticket->status->name}' a 'Respuesta del cliente'.");

                // Enviar correo al agente (si existe)
                if ($ticket->agent && $ticket->agent->email) {
                    Mail::to($ticket->agent->email)->send(new TicketReplyToAgent($ticket, $mensaje, $responder));
                    Log::register($ticket->id, "Se notificó al agente ({$ticket->agent->name}) sobre la respuesta del cliente.");
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Respuesta registrada correctamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar la respuesta.'
            ], 500);
        }
    }

    public function asignados()
    {
        $userId = auth()->id();

        $tickets = Ticket::with(['category', 'priority', 'status'])
                    ->where('assigned_to', $userId)
                    ->latest()
                    ->get();

        return view('tickets.asignados', compact('tickets'));
    }

    public function cerrar($id)
    {
        try {
            // Buscar el ticket por ID, si no existe lanzará una excepción
            $ticket = Ticket::findOrFail($id);
            $ticket->status_id = Status::where('name', 'Cerrado')->first()->id ?? 6; // Ajusta si el ID fijo es 6
            $ticket->closed_at = now();
            $ticket->save();

            // Usuario que cierra el ticket
            $closedBy = auth()->user();
            $userName = $closedBy->name;

            // ✅ Registrar log detallado del cierre
            Log::register(
                $ticket->id,
                "El usuario {$userName} cerró el ticket '{$ticket->subject}' (ID #{$ticket->id}) el " . now()->format('d/m/Y H:i:s') . "."
            );

            // Enviar correo al creador
            if ($ticket->creator && $ticket->creator->email) {
                Mail::to($ticket->creator->email)->send(new TicketClosedMail($ticket, $closedBy));
            }

            // Enviar correo al agente asignado (si existe)
            if ($ticket->agent && $ticket->agent->email) {
                Mail::to($ticket->agent->email)->send(new TicketClosedMail($ticket, $closedBy));
            }

            return redirect()->back()->with('success', 'El ticket ha sido cerrado correctamente.');
        } catch (\Exception $e) {
            // En caso de error, registrar el mensaje y redirigir con error
            return redirect()->back()->with('error', 'Ocurrió un error al intentar cerrar el ticket.');
        }
    }

    public function reabrir($id)
    {
        try {
            // Buscar el ticket con relaciones cargadas
            $ticket = Ticket::with(['creator', 'agent'])->findOrFail($id);

            // Actualizar estado del ticket a "Reabierto" (por ejemplo ID 7)
            $ticket->status_id = 7; // o usa Status::where('name', 'Abierto')->first()->id;
            $ticket->closed_at = null; // Limpiar fecha de cierre
            $ticket->save();

            // Obtener el usuario que reabrió el ticket
            $reopenedBy = auth()->user();
            $userName = $reopenedBy->name;

            // ✅ Log de reapertura
            Log::register(
                $ticket->id,
                "El usuario {$userName} reabrió el ticket '{$ticket->subject}' (ID #{$ticket->id}) el " . now()->format('d/m/Y H:i:s') . "."
            );

            // Enviar correo al creador
            if ($ticket->creator && $ticket->creator->email) {
                Mail::to($ticket->creator->email)->send(new TicketReopenedMail($ticket, $reopenedBy));
                Log::register($ticket->id, "Se notificó al creador del ticket ({$ticket->creator->name}) sobre la reapertura.");
            }

            // Enviar correo al agente
            if ($ticket->agent && $ticket->agent->email) {
                Mail::to($ticket->agent->email)->send(new TicketReopenedMail($ticket, $reopenedBy));
                Log::register($ticket->id, "Se notificó al agente asignado ({$ticket->agent->name}) sobre la reapertura.");
            }

            return redirect()->back()->with('success', 'El ticket ha sido reabierto correctamente.');
        } catch (\Exception $e) {
            // Redireccionar con mensaje de error
            return redirect()->back()->with('error', 'Ocurrió un error al intentar reabrir el ticket.');
        }
    }

    public function cancelarticket($id)
    {
        try {
            // Buscar el ticket por ID
            $ticket = Ticket::findOrFail($id);

            // Validar si el ticket es cancelable:
            // Solo puede cancelarse si está en estado inicial (ID = 1) y sin agente asignado
            if ($ticket->status_id != 1 || !is_null($ticket->assigned_to)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este ticket no puede ser cancelado.'
                ]);
            }

            // Cambiar el estado del ticket a "Cancelado" (ID = 8)
            $ticket->status_id = 8;// Puedes usar: Status::where('name', 'Cancelado')->first()->id ?? 8;
            $ticket->save();

            // Obtener nombre del usuario que cancela
            $userName = auth()->user()->name;

            // ✅ Log de cancelación
            Log::register(
                $ticket->id,
                "El usuario {$userName} canceló el ticket '{$ticket->subject}' (ID #{$ticket->id}) el " . now()->format('d/m/Y H:i:s') . "."
            );

            // Enviar correo al creador
            if ($ticket->creator && $ticket->creator->email) {
                Mail::to($ticket->creator->email)->send(new TicketCanceledMail($ticket));
                Log::register($ticket->id, "Se notificó al creador del ticket ({$ticket->creator->name}) sobre la cancelación.");
            }

            return response()->json([
                'success' => true,
                'message' => 'El ticket fue cancelado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al cancelar el ticket.'
            ], 500);
        }
    }

    public function conformidad()
    {
        $tickets = Ticket::with(['status', 'priority', 'category', 'agent'])
            ->whereIn('status_id', [5, 6])
            ->orderByDesc('created_at')
            ->get();

        return view('tickets.conformidad', compact('tickets'));
    }

    public function marcarResuelto($id)
    {
        try {
            // Buscar el ticket por su ID
            $ticket = Ticket::findOrFail($id);

            // Solo se puede marcar como resuelto si está en estado "Cerrado" (ID = 6)
            if ($ticket->status_id != 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden marcar como resueltos los tickets cerrados.'
                ]);
            }

            // Cambiar el estado a "Resuelto" (ID = 5)
            $ticket->status_id = 5; // Puedes usar: Status::where('name', 'Resuelto')->first()->id ?? 5;
            $ticket->save();

            // Obtener usuario que marcó como resuelto
            $userName = auth()->user()->name;

            // ✅ Registrar log de resolución
            Log::register(
                $ticket->id,
                "El usuario {$userName} marcó el ticket '{$ticket->subject}' (ID #{$ticket->id}) como resuelto el " . now()->format('d/m/Y H:i:s') . "."
            );

            // ✅ Registrar log de resolución
            return response()->json([
                'success' => true,
                'message' => 'Ticket marcado como resuelto correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar marcar el ticket como resuelto.'
            ], 500);
        }
    }

    public function generarPDF($id)
    {
        try {
            // Registrar primero el log, antes de cargar la relación 'logs' para evitar duplicación o conflicto
            $ticketSimple = Ticket::select('id', 'subject')->findOrFail($id);
            $userName = auth()->user()->name;

            // ✅ Registrar log indicando que se ha generado la visualización en PDF
            Log::register(
                $ticketSimple->id,
                "El usuario {$userName} visualizó en formato PDF el ticket '{$ticketSimple->subject}' (ID #{$ticketSimple->id}) el " . now()->format('d/m/Y H:i:s') . "."
            );

            // Buscar el ticket con todas las relaciones necesarias para el PDF
            $ticket = Ticket::with([
                'creator.department',
                'agent',
                'priority',
                'status',
                'category',
                'messages.user',
                'messages.attachments',
                'attachments',
                'logs.user'
            ])->findOrFail($id);

            // Cargar la vista 'tickets.pdf' con los datos del ticket, y definir formato A4 vertical
            $pdf = Pdf::loadView('tickets.pdf', compact('ticket'))
                    ->setPaper('A4', 'portrait');

            // Devolver el PDF en modo visualización (stream)
            return $pdf->download("ticket_{$ticket->id}.pdf");
        } catch (\Exception $e) {
            // Redirigir con mensaje de error
            return redirect()->back()->with('error', 'Ocurrió un error al generar el PDF del ticket.');
        }
    }

}
