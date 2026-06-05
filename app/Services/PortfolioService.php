<?php

namespace App\Services;

use App\Models\PortfolioTransaction;
use App\Models\User;

class PortfolioService
{
    public function createTransaction(User $user, array $data): PortfolioTransaction
    {
        return PortfolioTransaction::create([
            ...$data,
            'ticker' => strtoupper($data['ticker']),
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'user_id' => $user->id,
        ]);
    }

    public function updateTransaction(PortfolioTransaction $transaction, array $data): PortfolioTransaction
    {
        if (isset($data['ticker'])) {
            $data['ticker'] = strtoupper($data['ticker']);
        }

        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        $transaction->update($data);

        return $transaction;
    }
}
