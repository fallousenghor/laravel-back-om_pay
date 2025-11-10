<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListerContactsRequest extends FormRequest
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
            'recherche' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'limite' => 'nullable|integer|min:1|max:50',
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
            'recherche.string' => 'La recherche doit être une chaîne de caractères.',
            'recherche.max' => 'La recherche ne peut pas dépasser 100 caractères.',
            'page.integer' => 'Le paramètre page doit être un entier.',
            'page.min' => 'Le paramètre page doit être au moins 1.',
            'limite.integer' => 'Le paramètre limite doit être un entier.',
            'limite.min' => 'Le paramètre limite doit être au moins 1.',
            'limite.max' => 'Le paramètre limite ne peut pas dépasser 50.',
        ];
    }
}