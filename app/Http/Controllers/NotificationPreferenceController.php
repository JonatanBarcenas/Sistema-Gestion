<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    /**
     * Muestra el formulario para editar las preferencias de notificación de un cliente.
     *
     * @param  Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        // Asegurar que el cliente tenga preferencias de notificación
        $preferences = $customer->getOrCreateNotificationPreference();
        
        return view('notifications.preferences', compact('customer', 'preferences'));
    }

    /**
     * Actualiza las preferencias de notificación de un cliente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'project_created' => 'boolean',
            'project_updated' => 'boolean',
            'project_status_changed' => 'boolean',
            'project_comment_added' => 'boolean',
            'project_completed' => 'boolean',
            'email_notifications' => 'boolean',
            'database_notifications' => 'boolean',
        ]);

        // Obtener o crear las preferencias de notificación
        $preferences = $customer->getOrCreateNotificationPreference();
        
        // Actualizar las preferencias
        $preferences->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Preferencias de notificación actualizadas correctamente.');
    }
}