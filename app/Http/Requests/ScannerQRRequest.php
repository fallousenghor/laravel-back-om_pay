<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScannerQRRequest extends FormRequest
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
            'donneesQR' => 'required|string',
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
            'donneesQR.required' => 'Les données QR sont requises.',
            'donneesQR.string' => 'Les données QR doivent être une chaîne de caractères.',
        ];
    }
}