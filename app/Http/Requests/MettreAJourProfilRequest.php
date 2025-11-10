<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MettreAJourProfilRequest extends FormRequest
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
            'prenom' => 'sometimes|string|min:2|max:50|alpha',
            'nom' => 'sometimes|string|min:2|max:50|alpha',
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $this->user()->getKey() . ',_id',
            'codePin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
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
            'prenom.sometimes' => 'Le prénom est optionnel.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'prenom.max' => 'Le prénom ne peut pas dépasser 50 caractères.',
            'prenom.alpha' => 'Le prénom ne peut contenir que des lettres.',
            'nom.sometimes' => 'Le nom est optionnel.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 50 caractères.',
            'nom.alpha' => 'Le nom ne peut contenir que des lettres.',
            'email.sometimes' => 'L\'email est optionnel.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'codePin.required' => 'Le code PIN est requis pour confirmer les modifications.',
            'codePin.string' => 'Le code PIN doit être une chaîne de caractères.',
            'codePin.size' => 'Le code PIN doit contenir exactement 4 caractères.',
            'codePin.regex' => 'Le code PIN doit contenir uniquement des chiffres.',
        ];
    }
}