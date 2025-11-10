<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitierTransfertRequest extends FormRequest
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
            'telephoneDestinataire' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'montant' => 'required|numeric|min:100|max:1000000',
            'devise' => 'required|string|in:XOF',
            'note' => 'nullable|string|max:100',
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
            'telephoneDestinataire.required' => 'Le numéro de téléphone du destinataire est requis.',
            'telephoneDestinataire.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'telephoneDestinataire.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 100 XOF.',
            'montant.max' => 'Le montant maximum est de 1 000 000 XOF.',
            'devise.required' => 'La devise est requise.',
            'devise.string' => 'La devise doit être une chaîne de caractères.',
            'devise.in' => 'La devise doit être XOF.',
            'note.string' => 'La note doit être une chaîne de caractères.',
            'note.max' => 'La note ne peut pas dépasser 100 caractères.',
        ];
    }
}