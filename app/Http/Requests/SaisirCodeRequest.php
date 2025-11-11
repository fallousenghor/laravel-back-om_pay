<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaisirCodeRequest extends FormRequest
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
            // Allow alphanumeric merchant codes (letters and digits), exactly 8 chars
            'code' => 'required|string|size:8|regex:/^[A-Za-z0-9]{8}$/',
            'montant' => 'required|numeric|min:100|max:1000000',
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
            'code.required' => 'Le code est requis.',
            'code.string' => 'Le code doit être une chaîne de caractères.',
            'code.size' => 'Le code doit contenir exactement 8 caractères.',
            'code.regex' => 'Le code doit contenir uniquement des lettres et des chiffres (alphanumérique).',
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 100 XOF.',
            'montant.max' => 'Le montant maximum est de 1 000 000 XOF.',
        ];
    }
}