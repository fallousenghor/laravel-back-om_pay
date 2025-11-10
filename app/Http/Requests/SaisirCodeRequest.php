<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaisirCodeRequest extends FormRequest
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
            'code' => 'required|string|size:8|regex:/^[0-9]{8}$/',
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
            'code.required' => 'Le code est requis.',
            'code.string' => 'Le code doit être une chaîne de caractères.',
            'code.size' => 'Le code doit contenir exactement 8 caractères.',
            'code.regex' => 'Le code doit contenir uniquement des chiffres.',
        ];
    }
}