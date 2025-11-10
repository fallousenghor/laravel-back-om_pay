<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjouterContactRequest extends FormRequest
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
            'nom' => 'required|string|min:2|max:100',
            'numeroTelephone' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'photo' => 'nullable|string', // Base64 ou URL
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
            'nom.required' => 'Le nom est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 100 caractères.',
            'numeroTelephone.required' => 'Le numéro de téléphone est requis.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'photo.string' => 'La photo doit être une chaîne de caractères.',
        ];
    }
}