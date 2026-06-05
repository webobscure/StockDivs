<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'ticker', 'type', 'quantity', 'price', 'currency', 'transaction_date', 'commission', 'notes'])]
class PortfolioTransaction extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'price' => 'decimal:6',
            'commission' => 'decimal:6',
            'transaction_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
