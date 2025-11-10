<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinaliserInscriptionRequest extends FormRequest
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
            'numeroTelephone' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'codeOTP' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'email' => 'required|email|unique:utilisateurs,email',
            'codePin' => 'required|string|size:4|regex:/^[0-9]{4}$/|not_in:0000,1111,1234,4321',
            'numeroCNI' => 'required|string|size:13|regex:/^[0-9]{13}$/|unique:utilisateurs,numeroCNI',
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
            'numeroTelephone.required' => 'Le numéro de téléphone est obligatoire.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'codeOTP.required' => 'Le code OTP est requis.',
            'codeOTP.string' => 'Le code OTP doit être une chaîne de caractères.',
            'codeOTP.size' => 'Le code OTP doit contenir exactement 6 caractères.',
            'codeOTP.regex' => 'Le code OTP doit contenir uniquement des chiffres.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'codePin.required' => 'Le code PIN est obligatoire.',
            'codePin.size' => 'Le code PIN doit contenir exactement 4 chiffres.',
            'codePin.regex' => 'Le code PIN ne peut contenir que des chiffres.',
            'codePin.not_in' => 'Ce code PIN n\'est pas autorisé.',
            'numeroCNI.required' => 'Le numéro CNI est obligatoire.',
            'numeroCNI.size' => 'Le numéro CNI doit contenir exactement 13 chiffres.',
            'numeroCNI.regex' => 'Le numéro CNI ne peut contenir que des chiffres.',
            'numeroCNI.unique' => 'Ce numéro CNI est déjà utilisé.',
        ];
    }
}