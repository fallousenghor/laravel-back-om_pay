<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InscriptionRequest extends FormRequest
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
            'numeroTelephone' => 'required|string|regex:/^\+221[0-9]{9}$/|unique:utilisateurs,numeroTelephone',
            'prenom' => 'required|string|min:2|max:50|alpha',
            'nom' => 'required|string|min:2|max:50|alpha',
            'email' => 'required|email|unique:utilisateurs,email',
            'codePin' => 'required|string|size:4|regex:/^[0-9]{4}$/|not_in:0000,1111,1234,4321',
            'numeroCNI' => 'required|string|size:13|regex:/^[0-9]{13}$/|unique:utilisateurs,numeroCNI',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numeroTelephone.required' => 'Le numéro de téléphone est obligatoire',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX',
            'numeroTelephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'prenom.required' => 'Le prénom est obligatoire',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères',
            'prenom.max' => 'Le prénom ne peut pas dépasser 50 caractères',
            'prenom.alpha' => 'Le prénom ne peut contenir que des lettres',
            'nom.required' => 'Le nom est obligatoire',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères',
            'nom.max' => 'Le nom ne peut pas dépasser 50 caractères',
            'nom.alpha' => 'Le nom ne peut contenir que des lettres',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'codePin.required' => 'Le code PIN est obligatoire',
            'codePin.size' => 'Le code PIN doit contenir exactement 4 chiffres',
            'codePin.regex' => 'Le code PIN ne peut contenir que des chiffres',
            'codePin.not_in' => 'Ce code PIN n\'est pas autorisé',
            'numeroCNI.required' => 'Le numéro CNI est obligatoire',
            'numeroCNI.size' => 'Le numéro CNI doit contenir exactement 13 chiffres',
            'numeroCNI.regex' => 'Le numéro CNI ne peut contenir que des chiffres',
            'numeroCNI.unique' => 'Ce numéro CNI est déjà utilisé',
        ];
    }
}
