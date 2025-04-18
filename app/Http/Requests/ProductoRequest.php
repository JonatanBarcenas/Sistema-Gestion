<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:electronics,clothing,food,other',
            'base_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'category.required' => 'La categoría es obligatoria.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'base_price.required' => 'El precio base es obligatorio.',
            'base_price.numeric' => 'El precio base debe ser un número.',
            'base_price.min' => 'El precio base debe ser mayor o igual a 0.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.string' => 'El SKU debe ser una cadena de texto.',
            'sku.unique' => 'El SKU ya está en uso.',
        ];
    }

}
