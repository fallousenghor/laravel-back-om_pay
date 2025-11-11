<?php

namespace App\DTOs\Portefeuille;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HistoriqueTransactionsDTO
{
    public array $transactions;
    public array $pagination;

    public function __construct(array $transactions, array $pagination)
    {
        $this->transactions = $transactions;
        $this->pagination = $pagination;
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        $transactions = $paginator->map(function ($transaction) {
            return new TransactionDTO(
                $transaction->id,
                $transaction->type,
                $transaction->montant,
                $transaction->devise,
                $transaction->destinataire,
                $transaction->marchand,
                $transaction->statut,
                $transaction->date_transaction->toISOString(),
                $transaction->reference,
                $transaction->frais
            );
        })->toArray();

        $pagination = [
            'pageActuelle' => $paginator->currentPage(),
            'totalPages' => $paginator->lastPage(),
            'totalElements' => $paginator->total(),
            'elementsParPage' => $paginator->perPage(),
        ];

        return new self($transactions, $pagination);
    }
}