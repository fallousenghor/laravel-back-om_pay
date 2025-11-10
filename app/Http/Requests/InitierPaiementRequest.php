<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitierPaiementRequest extends FormRequest
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
            'idScan' => 'required_without:idCode|string',
            'idCode' => 'required_without:idScan|string',
            'idMarchand' => 'required|string',
            'montant' => 'required|numeric|min:50|max:500000',
            'modePaiement' => 'required|in:qr_code,code',
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
            'idScan.required_without' => 'L\'ID du scan est requis si l\'ID du code n\'est pas fourni.',
            'idScan.string' => 'L\'ID du scan doit être une chaîne de caractères.',
            'idCode.required_without' => 'L\'ID du code est requis si l\'ID du scan n\'est pas fourni.',
            'idCode.string' => 'L\'ID du code doit être une chaîne de caractères.',
            'idMarchand.required' => 'L\'ID du marchand est requis.',
            'idMarchand.string' => 'L\'ID du marchand doit être une chaîne de caractères.',
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 50 XOF.',
            'montant.max' => 'Le montant maximum est de 500 000 XOF.',
            'modePaiement.required' => 'Le mode de paiement est requis.',
            'modePaiement.in' => 'Le mode de paiement doit être qr_code ou code.',
        ];
    }
}