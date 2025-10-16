<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoryController extends Controller
{
    /**
     * Muestra el listado de categorías.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return view('categories.index', compact('categories'));
        } catch (Exception $e) {
            Log::error('Error en CategoryController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las categorías.');
        }
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Almacena una nueva categoría.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        try {
            $category = Category::create($request->only('name', 'description'));

            // Registrar en system_logs
            SystemLog::register('categories', 'create', 'Se creó la categoría: ' . $category->name);

            return redirect()->route('categories.index')->with('success', 'Categoría registrada correctamente.');
        } catch (Exception $e) {
            Log::error('Error en CategoryController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar la categoría.');
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(Category $category)
    {
        try {
            return view('categories.edit', compact('category'));
        } catch (Exception $e) {
            Log::error('Error en CategoryController@edit: ' . $e->getMessage());
            return redirect()->route('categories.index')->with('error', 'Error al cargar la categoría.');
        }
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        try {
            $category->update($request->only('name', 'description'));

            // Registrar en system_logs
            SystemLog::register('categories', 'update', 'Se actualizó la categoría ID ' . $category->id);

            return redirect()->route('categories.index')->with('success', 'Categoría actualizada correctamente.');
        } catch (Exception $e) {
            Log::error('Error en CategoryController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar la categoría.');
        }
    }

    /**
     * Elimina una categoría.
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();

            // Registrar en system_logs
            SystemLog::register('categories', 'delete', 'Se eliminó la categoría ID ' . $category->id);

            return response()->json(['success' => true, 'message' => 'Categoría eliminada correctamente.']);
        } catch (Exception $e) {
            Log::error('Error en CategoryController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar la categoría.']);
        }
    }
}
