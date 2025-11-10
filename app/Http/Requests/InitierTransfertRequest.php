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
            'numeroTelephoneDestinataire' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'montant' => 'required|numeric|min:100|max:1000000',
            'devise' => 'sometimes|required|string|in:XOF',
            'description' => 'nullable|string|max:100',
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
            'numeroTelephoneDestinataire.required' => 'Le numéro de téléphone du destinataire est requis.',
            'numeroTelephoneDestinataire.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephoneDestinataire.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 100 XOF.',
            'montant.max' => 'Le montant maximum est de 1 000 000 XOF.',
            'devise.required' => 'La devise est requise.',
            'devise.string' => 'La devise doit être une chaîne de caractères.',
            'devise.in' => 'La devise doit être XOF.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 100 caractères.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Normalize field names - accept both telephoneDestinataire and numeroTelephoneDestinataire
        if ($this->has('telephoneDestinataire') && !$this->has('numeroTelephoneDestinataire')) {
            $this->merge([
                'numeroTelephoneDestinataire' => $this->telephoneDestinataire
            ]);
        }
    }
}