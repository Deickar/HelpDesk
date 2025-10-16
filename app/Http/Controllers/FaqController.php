<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Faq;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;
use Exception;

class FaqController extends Controller
{
    /**
     * Muestra el listado de FAQs.
     */
    public function index()
    {
        try {
            $faqs = Faq::all();
            return view('faqs.index', compact('faqs'));
        } catch (Exception $e) {
            Log::error('Error en FaqController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las FAQs.');
        }
    }

    /**
     * Muestra el formulario de creación de FAQ.
     */
    public function create()
    {
        return view('faqs.create');
    }

    /**
     * Almacena una nueva FAQ.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            $faq = Faq::create($request->only('title', 'description'));

            // Registrar en system_logs
            SystemLog::register('faqs', 'create', 'Se creó la FAQ: ' . $faq->title);

            return redirect()->route('faqs.index')->with('success', 'FAQ registrada correctamente.');
        } catch (Exception $e) {
            Log::error('Error en FaqController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar la FAQ.');
        }
    }

    /**
     * Muestra el formulario de edición de una FAQ.
     */
    public function edit(Faq $faq)
    {
        try {
            return view('faqs.edit', compact('faq'));
        } catch (Exception $e) {
            Log::error('Error en FaqController@edit: ' . $e->getMessage());
            return redirect()->route('faqs.index')->with('error', 'Error al cargar la FAQ.');
        }
    }

    /**
     * Actualiza una FAQ existente.
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            $faq->update($request->only('title', 'description'));

            // Registrar en system_logs
            SystemLog::register('faqs', 'update', 'Se actualizó la FAQ ID ' . $faq->id);

            return redirect()->route('faqs.index')->with('success', 'FAQ actualizada correctamente.');
        } catch (Exception $e) {
            Log::error('Error en FaqController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar la FAQ.');
        }
    }

    /**
     * Elimina una FAQ.
     */
    public function destroy(Faq $faq)
    {
        try {
            $faq->delete();

            // Registrar en system_logs
            SystemLog::register('faqs', 'delete', 'Se eliminó la FAQ ID ' . $faq->id);

            return response()->json(['success' => true, 'message' => 'FAQ eliminada correctamente.']);
        } catch (Exception $e) {
            Log::error('Error en FaqController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar la FAQ.']);
        }
    }
}
