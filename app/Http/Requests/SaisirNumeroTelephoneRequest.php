<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaisirNumeroTelephoneRequest extends FormRequest
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
            // Phone number validation for Senegal format (accepts both with and without country code)
            'numeroTelephone' => 'required|string|min:9|max:13',
            'montant' => 'required|numeric|min:100|max:1000000',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $numeroTelephone = $this->input('numeroTelephone');

            // Check if the phone number matches the expected Senegal format
            if (!preg_match('/^(?:\+221|221)?([7][0-8][0-9]{7})$/', $numeroTelephone)) {
                $validator->errors()->add('numeroTelephone', 'Le numéro de téléphone doit être un numéro valide au Sénégal (ex: 771234567, +221771234567 ou 221771234567).');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numeroTelephone.required' => 'Le numéro de téléphone est requis.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephone.min' => 'Le numéro de téléphone est trop court.',
            'numeroTelephone.max' => 'Le numéro de téléphone est trop long.',
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 100 XOF.',
            'montant.max' => 'Le montant maximum est de 1 000 000 XOF.',
        ];
    }
}