<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Status;
use App\Models\Category;
use App\Models\User;

class DashboardController extends Controller
{
    public function dashboardRouter(Request $request)
    {
        $role = auth()->user()->role;

        return match ($role) {
            'client' => $this->clientDashboard($request),
            'agent'  => $this->agentDashboard($request),
            'admin'  => $this->adminDashboard($request),
            default  => abort(403, 'Rol no autorizado.'),
        };
    }

    public function adminDashboard(Request $request)
    {
        $mes = $request->input('month', now()->month);
        $anio = $request->input('year', now()->year);

        $tickets = Ticket::with(['category', 'priority', 'status', 'creator'])
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->get();

        $totalTickets = $tickets->count();

        $statusList = Status::orderBy('id')->get();
        $estilos = [
            1 => ['color' => 'warning',   'icono' => 'bx bx-help-circle'],
            2 => ['color' => 'primary',   'icono' => 'bx bx-loader-circle'],
            3 => ['color' => 'info',      'icono' => 'bx bx-user-voice'],
            4 => ['color' => 'info',      'icono' => 'bx bx-user-pin'],
            5 => ['color' => 'success',   'icono' => 'bx bx-check-circle'],
            6 => ['color' => 'dark',      'icono' => 'bx bx-lock-alt'],
            7 => ['color' => 'secondary', 'icono' => 'bx bx-history'],
            8 => ['color' => 'danger',    'icono' => 'bx bx-x-circle'],
        ];

        $estados = $statusList->map(function ($status) use ($tickets, $totalTickets, $estilos) {
            $cantidad = $tickets->where('status_id', $status->id)->count();
            return [
                'id'         => $status->id,
                'nombre'     => $status->name,
                'total'      => $cantidad,
                'color'      => $estilos[$status->id]['color'] ?? 'secondary',
                'icono'      => $estilos[$status->id]['icono'] ?? 'bx bx-circle',
                'porcentaje' => $totalTickets > 0 ? round(($cantidad / $totalTickets) * 100, 2) : 0,
            ];
        });

        $categoriasData = $tickets->groupBy('category_id')->map(function ($group, $catId) {
            return [
                'nombre' => optional($group->first()->category)->name ?? 'Sin Categoría',
                'total'  => $group->count(),
            ];
        })->values();

        $prioridadesData = $tickets->groupBy('priority_id')->map(function ($group, $priorityId) {
            $prioridad = $group->first()->priority;
            return [
                'nombre' => $prioridad->name ?? 'Sin Prioridad',
                'total'  => $group->count(),
                'color'  => $prioridad->color ?? '#6c757d',
            ];
        })->values();

        $eventosCalendar = Ticket::with('creator:id,name')
            ->select('id', 'user_id', 'created_at')
            ->get()
            ->map(function ($ticket) {
                return [
                    'title' => "ID {$ticket->id} - {$ticket->creator->name}",
                    'start' => $ticket->created_at->toDateString(),
                    'url' => route('tickets.detalle', $ticket->id),
                ];
            });

        $departamentosData = $tickets->groupBy(function ($ticket) {
            return optional($ticket->creator->department)->id;
        })->map(function ($group, $depId) {
            return [
                'nombre' => optional($group->first()->creator->department)->name ?? 'Sin Departamento',
                'total'  => $group->count(),
            ];
        })->values();

        $agentesData = User::where('role', 'agent')
            ->withCount(['assignedTickets' => function ($query) use ($mes, $anio) {
                $query->whereMonth('created_at', $mes)->whereYear('created_at', $anio);
            }])
            ->get()
            ->map(function ($agente) {
                return [
                    'nombre' => $agente->name,
                    'total' => $agente->assigned_tickets_count,
                ];
            });

        return view('dashboard', [
            'view' => 'admin',
            'estados' => $estados,
            'categoriasData' => $categoriasData,
            'prioridadesData' => $prioridadesData,
            'eventosCalendar' => $eventosCalendar,
            'departamentosData' => $departamentosData,
            'agentesData' => $agentesData,
        ]);
    }

    public function clientDashboard(Request $request)
    {
        $userId = auth()->id();
        $mes = $request->input('month', now()->month);
        $anio = $request->input('year', now()->year);

        // Obtener todos los estados
        $statusList = Status::orderBy('id')->get();

        // Obtener los tickets del usuario filtrados por mes y año
        $tickets = Ticket::where('user_id', $userId)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->get();

        $totalTickets = $tickets->count();

        // Estilos personalizados por ID de estado
        $estilos = [
            1 => ['color' => 'warning',   'icono' => 'bx bx-help-circle'],
            2 => ['color' => 'primary',   'icono' => 'bx bx-loader-circle'],
            3 => ['color' => 'info',      'icono' => 'bx bx-user-voice'],
            4 => ['color' => 'info',      'icono' => 'bx bx-user-pin'],
            5 => ['color' => 'success',   'icono' => 'bx bx-check-circle'],
            6 => ['color' => 'dark',      'icono' => 'bx bx-lock-alt'],
            7 => ['color' => 'secondary', 'icono' => 'bx bx-history'],
            8 => ['color' => 'danger',    'icono' => 'bx bx-x-circle'],
        ];

        // Armar la colección de estados con los datos contados
        $estados = $statusList->map(function ($status) use ($tickets, $totalTickets, $estilos) {
            $cantidad = $tickets->where('status_id', $status->id)->count();
            $id = $status->id;

            return [
                'id'         => $id,
                'nombre'     => $status->name,
                'total'      => $cantidad,
                'color'      => $estilos[$id]['color'] ?? 'secondary',
                'icono'      => $estilos[$id]['icono'] ?? 'bx bx-circle',
                'porcentaje' => $totalTickets > 0 ? round(($cantidad / $totalTickets) * 100, 2) : 0,
            ];
        });

        $categoriasData = $tickets->groupBy('category_id')->map(function ($group, $catId) {
            return [
                'nombre' => optional($group->first()->category)->name ?? 'Sin Categoría',
                'total'  => $group->count(),
            ];
        })->values(); // limpia claves

        $prioridadesData = $tickets->groupBy('priority_id')->map(function ($group, $priorityId) {
            return [
                'nombre' => optional($group->first()->priority)->name ?? 'Sin Prioridad',
                'total'  => $group->count(),
            ];
        })->values(); // limpia claves

        return view('dashboard', [
            'view' => 'client',
            'estados' => $estados,
            'categoriasData' => $categoriasData,
            'prioridadesData' => $prioridadesData,
        ]);
    }

    public function agentDashboard(Request $request)
    {
        $userId = auth()->id();
        $mes = $request->input('month', now()->month);
        $anio = $request->input('year', now()->year);

        $tickets = Ticket::with(['category', 'priority', 'status'])
            ->where('assigned_to', $userId)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->get();

        $totalTickets = $tickets->count();

        // Listado de estados
        $statusList = Status::orderBy('id')->get();

        $estilos = [
            1 => ['color' => 'warning',   'icono' => 'bx bx-help-circle'],
            2 => ['color' => 'primary',   'icono' => 'bx bx-loader-circle'],
            3 => ['color' => 'info',      'icono' => 'bx bx-user-voice'],
            4 => ['color' => 'info',      'icono' => 'bx bx-user-pin'],
            5 => ['color' => 'success',   'icono' => 'bx bx-check-circle'],
            6 => ['color' => 'dark',      'icono' => 'bx bx-lock-alt'],
            7 => ['color' => 'secondary', 'icono' => 'bx bx-history'],
            8 => ['color' => 'danger',    'icono' => 'bx bx-x-circle'],
        ];

        $estados = $statusList->map(function ($status) use ($tickets, $totalTickets, $estilos) {
            $cantidad = $tickets->where('status_id', $status->id)->count();
            return [
                'id'         => $status->id,
                'nombre'     => $status->name,
                'total'      => $cantidad,
                'color'      => $estilos[$status->id]['color'] ?? 'secondary',
                'icono'      => $estilos[$status->id]['icono'] ?? 'bx bx-circle',
                'porcentaje' => $totalTickets > 0 ? round(($cantidad / $totalTickets) * 100, 2) : 0,
            ];
        });

        $categoriasData = $tickets->groupBy('category_id')->map(function ($group, $catId) {
            return [
                'nombre' => optional($group->first()->category)->name ?? 'Sin Categoría',
                'total'  => $group->count(),
            ];
        })->values();

        $prioridadesData = $tickets->groupBy('priority_id')->map(function ($group, $priorityId) {
            $prioridad = $group->first()->priority;
            return [
                'nombre' => $prioridad->name ?? 'Sin Prioridad',
                'total'  => $group->count(),
                'color'  => $prioridad->color ?? '#6c757d',
            ];
        })->values();

        // Calendario: todos los tickets asignados al agente (sin filtro de mes/año)
        $eventosCalendar = Ticket::with('creator:id,name')
        ->where('assigned_to', $userId)
        ->select('id', 'user_id', 'created_at')
        ->get()
        ->map(function ($ticket) {
            return [
                'title' => "ID {$ticket->id} - {$ticket->creator->name}",
                'start' => $ticket->created_at->toDateString(),
                'url' => route('tickets.detalle', $ticket->id),
            ];
        });

        return view('dashboard', [
            'view' => 'agent',
            'eventosCalendar' => $eventosCalendar,
            'estados' => $estados,
            'categoriasData' => $categoriasData,
            'prioridadesData' => $prioridadesData,
        ]);
    }


}
