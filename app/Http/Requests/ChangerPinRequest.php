<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangerPinRequest extends FormRequest
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
            'ancienPin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'nouveauPin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'confirmationPin' => 'required|string|same:nouveauPin',
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
            'ancienPin.required' => 'L\'ancien code PIN est requis.',
            'ancienPin.string' => 'L\'ancien code PIN doit être une chaîne de caractères.',
            'ancienPin.size' => 'L\'ancien code PIN doit contenir exactement 4 caractères.',
            'ancienPin.regex' => 'L\'ancien code PIN doit contenir uniquement des chiffres.',
            'nouveauPin.required' => 'Le nouveau code PIN est requis.',
            'nouveauPin.string' => 'Le nouveau code PIN doit être une chaîne de caractères.',
            'nouveauPin.size' => 'Le nouveau code PIN doit contenir exactement 4 caractères.',
            'nouveauPin.regex' => 'Le nouveau code PIN doit contenir uniquement des chiffres.',
            'confirmationPin.required' => 'La confirmation du nouveau code PIN est requise.',
            'confirmationPin.string' => 'La confirmation doit être une chaîne de caractères.',
            'confirmationPin.same' => 'La confirmation doit être identique au nouveau code PIN.',
        ];
    }
}