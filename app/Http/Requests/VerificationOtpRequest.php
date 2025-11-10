<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificationOtpRequest extends FormRequest
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
            'numeroTelephone.required' => 'Le numéro de téléphone est requis.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'codeOTP.required' => 'Le code OTP est requis.',
            'codeOTP.string' => 'Le code OTP doit être une chaîne de caractères.',
            'codeOTP.size' => 'Le code OTP doit contenir exactement 6 caractères.',
            'codeOTP.regex' => 'Le code OTP doit contenir uniquement des chiffres.',
        ];
    }
}