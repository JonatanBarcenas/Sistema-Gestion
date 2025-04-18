<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
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
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|regex:/^[0-9]+$/|unique:customers,phone|size:10',
            'address' => 'required|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'email.unique' => 'El correo electrónico ya está en uso. Por favor, elige otro.',
            'phone.regex' => 'El teléfono debe contener solo números y el formato debe ser correcto.',
            'phone.required' => 'El teléfono es obligatorio.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.size' => 'El teléfono debe tener exactamente 10 dígitos.',
            'phone.unique' => 'El teléfono ya está en uso. Por favor, elige otro.',
            'address.max' => 'La dirección no puede exceder los 255 caracteres.',
            'address.required' => 'La dirección es obligatoria.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser "active" o "inactive".',
        ];
    }

}
