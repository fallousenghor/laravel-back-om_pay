<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RechercherHistoriqueRequest extends FormRequest
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
            'q' => 'nullable|string|max:100',
            'type' => 'nullable|in:transfert,paiement',
            'montantMin' => 'nullable|numeric|min:0',
            'montantMax' => 'nullable|numeric|min:0',
            'dateDebut' => 'nullable|date_format:Y-m-d',
            'dateFin' => 'nullable|date_format:Y-m-d',
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
            'q.string' => 'Le paramètre de recherche doit être une chaîne de caractères.',
            'q.max' => 'Le paramètre de recherche ne peut pas dépasser 100 caractères.',
            'type.in' => 'Le type doit être transfert ou paiement.',
            'montantMin.numeric' => 'Le montant minimum doit être un nombre.',
            'montantMin.min' => 'Le montant minimum doit être positif.',
            'montantMax.numeric' => 'Le montant maximum doit être un nombre.',
            'montantMax.min' => 'Le montant maximum doit être positif.',
            'dateDebut.date_format' => 'La date de début doit être au format Y-m-d.',
            'dateFin.date_format' => 'La date de fin doit être au format Y-m-d.',
        ];
    }
}