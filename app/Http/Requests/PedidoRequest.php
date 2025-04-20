<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PedidoRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',

        ];
    }

    public function messages()
{
    return [
        'customer_id.required' => 'El cliente es obligatorio.',
        'customer_id.exists' => 'El cliente seleccionado no existe.',
        'delivery_date.required' => 'La fecha de entrega es obligatoria.',
        'delivery_date.date' => 'La fecha de entrega debe ser una fecha válida.',
        'delivery_date.after_or_equal'=> 'La fecha debe ser posterior o igual a hoy.',
        'order_number.string' => 'El número de pedido debe ser una cadena de texto.',
        'order_number.max' => 'El número de pedido no puede exceder los 255 caracteres.',
        'order_number.unique' => 'El número de pedido ya está en uso.',
        'status.required' => 'El estado es obligatorio.',
        'status.in' => 'El estado seleccionado no es válido.',
        'products.required' => 'Debe agregar al menos un producto.',
        'products.array' => 'El formato de los productos no es válido.',
        'products.min' => 'Debe agregar al menos un producto.',
        'products.*.product_id.required' => 'El producto es obligatorio.',
        'products.*.product_id.exists' => 'El producto seleccionado no existe.',
        'products.*.quantity.required' => 'La cantidad es obligatoria.',
        'products.*.quantity.integer' => 'La cantidad debe ser un número entero.',
        'products.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
        'products.*.unit_price.required' => 'El precio unitario es obligatorio.',
        'products.*.unit_price.numeric' => 'El precio unitario debe ser un número.',
        'products.*.unit_price.min' => 'El precio unitario debe ser mayor a 0.',

    ];
}

}
