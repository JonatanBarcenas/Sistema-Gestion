<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TareaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_id' => 'nullable|exists:orders,id',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'type' => 'required|in:design,printing,advertising,packaging,other',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'materials_needed' => 'nullable|array',
            'notes' => 'nullable|string',
            'attachments' => 'nullable|array',
            'checklist' => 'nullable|array',
            'color' => 'nullable|string|max:7',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id'
        ];
    }


    public function messages()
    {
        return [
            'title.required' => 'El título es obligatorio',
            'title.string' => 'El título debe ser texto',
            'title.max' => 'El título no puede exceder los 255 caracteres',
            'order_id.exists' => 'El pedido seleccionado no existe',
            'due_date.required' => 'La fecha de vencimiento es obligatoria',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida',
            'priority.required' => 'La prioridad es obligatoria',
            'priority.in' => 'La prioridad debe ser baja, media o alta',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser pendiente, en progreso o completado',
            'type.required' => 'El tipo es obligatorio',
            'type.in' => 'El tipo debe ser diseño, impresión, publicidad, empaque u otro',
            'estimated_hours.numeric' => 'Las horas estimadas deben ser un número',
            'estimated_hours.min' => 'Las horas estimadas no pueden ser negativas',
            'actual_hours.numeric' => 'Las horas reales deben ser un número',
            'actual_hours.min' => 'Las horas reales no pueden ser negativas',
            'color.max' => 'El color debe tener máximo 7 caracteres',
            'assignees.required' => 'Debe asignar al menos un usuario',
            'assignees.array' => 'Los usuarios asignados deben ser una lista',
            'assignees.min' => 'Debe asignar al menos un usuario',
            'assignees.*.exists' => 'Uno de los usuarios asignados no existe',
            'dependencies.array' => 'Las dependencias deben ser una lista',
            'dependencies.*.exists' => 'Una de las tareas dependientes no existe'
        ];
    }
}