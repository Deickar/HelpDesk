<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Department;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Exception;

class UserController extends Controller
{
     /**
     * Muestra el listado de usuarios.
     */
    public function index()
    {
        try {
            $users = User::with('department')->get();
            return view('users.index', compact('users'));
        } catch (Exception $e) {
            Log::error('Error en UserController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los usuarios.');
        }
    }

    /**
     * Muestra el formulario de creación de usuario.
     */
    public function create()
    {
        try {
            $departments = Department::all();
            return view('users.create', compact('departments'));
        } catch (Exception $e) {
            Log::error('Error en UserController@create: ' . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Error al cargar el formulario.');
        }
    }

    /**
     * Almacena un nuevo usuario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'role'          => ['required', Rule::in(['admin', 'client', 'agent'])],
        ]);

        try {
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'department_id' => $request->department_id,
                'role'          => $request->role,
            ]);

            SystemLog::register('users', 'create', 'Se creó el usuario: ' . $user->email);

            return redirect()->route('users.index')->with('success', 'Usuario registrado correctamente.');
        } catch (Exception $e) {
            Log::error('Error en UserController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar el usuario.');
        }
    }

    /**
     * Muestra el formulario de edición de un usuario.
     */
    public function edit(User $user)
    {
        try {
            $departments = Department::all();
            return view('users.edit', compact('user', 'departments'));
        } catch (Exception $e) {
            Log::error('Error en UserController@edit: ' . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Error al cargar el usuario.');
        }
    }

    /**
     * Actualiza un usuario existente.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password'      => 'nullable|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'role'          => ['required', Rule::in(['admin', 'client', 'agent'])],
        ]);

        try {
            $user->name          = $request->name;
            $user->email         = $request->email;
            $user->department_id = $request->department_id;
            $user->role          = $request->role;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            SystemLog::register('users', 'update', 'Se actualizó el usuario ID ' . $user->id);

            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
        } catch (Exception $e) {
            Log::error('Error en UserController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar el usuario.');
        }
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            SystemLog::register('users', 'delete', 'Se eliminó el usuario ID ' . $user->id);

            return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
        } catch (Exception $e) {
            Log::error('Error en UserController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el usuario.']);
        }
    }
}
