<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActiverBiometrieRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'codePin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'jetonBiometrique' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codePin.required' => 'Le code PIN est requis.',
            'codePin.string' => 'Le code PIN doit être une chaîne de caractères.',
            'codePin.size' => 'Le code PIN doit contenir exactement 4 caractères.',
            'codePin.regex' => 'Le code PIN doit contenir uniquement des chiffres.',
            'jetonBiometrique.required' => 'Le jeton biométrique est requis.',
            'jetonBiometrique.string' => 'Le jeton biométrique doit être une chaîne de caractères.',
        ];
    }
}