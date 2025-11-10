<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistoriqueTransactionsRequest extends FormRequest
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
            'page' => 'sometimes|integer|min:1',
            'limite' => 'sometimes|integer|min:1|max:100',
            'type' => 'sometimes|in:transfert,paiement,tous',
            'dateDebut' => 'sometimes|date_format:Y-m-d',
            'dateFin' => 'sometimes|date_format:Y-m-d',
            'statut' => 'sometimes|in:en_attente,termine,echoue,annule',
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
            'page.integer' => 'Le paramètre page doit être un entier.',
            'page.min' => 'Le paramètre page doit être au moins 1.',
            'limite.integer' => 'Le paramètre limite doit être un entier.',
            'limite.min' => 'Le paramètre limite doit être au moins 1.',
            'limite.max' => 'Le paramètre limite ne peut pas dépasser 100.',
            'type.in' => 'Le paramètre type doit être transfert, paiement ou tous.',
            'dateDebut.date_format' => 'Le paramètre dateDebut doit être au format Y-m-d.',
            'dateFin.date_format' => 'Le paramètre dateFin doit être au format Y-m-d.',
            'statut.in' => 'Le paramètre statut doit être en_attente, termine, echoue ou annule.',
        ];
    }
}