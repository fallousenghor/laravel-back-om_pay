<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitierInscriptionRequest extends FormRequest
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
            'prenom' => 'required|string|min:2|max:50|alpha',
            'nom' => 'required|string|min:2|max:50|alpha',
            'numeroTelephone' => 'required|string|regex:/^\+221[0-9]{9}$/|unique:utilisateurs,numero_telephone',
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
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'prenom.max' => 'Le prénom ne peut pas dépasser 50 caractères.',
            'prenom.alpha' => 'Le prénom ne peut contenir que des lettres.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 50 caractères.',
            'nom.alpha' => 'Le nom ne peut contenir que des lettres.',
            'numeroTelephone.required' => 'Le numéro de téléphone est obligatoire.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'numeroTelephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        ];
    }
}