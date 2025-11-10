<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RafraichirTokenRequest extends FormRequest
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
            'jetonRafraichissement' => 'required|string',
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
            'jetonRafraichissement.required' => 'Le jeton de rafraîchissement est requis.',
            'jetonRafraichissement.string' => 'Le jeton de rafraîchissement doit être une chaîne de caractères.',
        ];
    }
}