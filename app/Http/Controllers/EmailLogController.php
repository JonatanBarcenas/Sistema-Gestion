<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra el listado de correos enviados.
     */
    public function index()
    {
        $emailLogs = EmailLog::orderBy('sent_at', 'desc')->paginate(10);
        return view('emails.index', compact('emailLogs'));
    }

    /**
     * Muestra los detalles de un correo específico.
     */
    public function show(EmailLog $emailLog)
    {
        return view('emails.show', compact('emailLog'));
    }

    /**
     * Elimina un registro de correo.
     */
    public function destroy(EmailLog $emailLog)
    {
        $emailLog->delete();
        return redirect()->route('emails.index')
            ->with('success', 'Registro de correo eliminado correctamente.');
    }
    
    /**
     * Muestra la guía de notificaciones y configuración de correos.
     */
    public function showNotificationGuide()
    {
        return view('emails.notification-guide');
    }
}