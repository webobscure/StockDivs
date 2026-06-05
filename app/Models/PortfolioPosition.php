<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'ticker', 'company_name', 'exchange', 'currency', 'quantity', 'average_buy_price', 'purchase_date', 'notes'])]
class PortfolioPosition extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'average_buy_price' => 'decimal:6',
            'purchase_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
