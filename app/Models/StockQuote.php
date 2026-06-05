<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticker', 'price', 'currency', 'change', 'change_percent', 'market_time', 'provider'])]
class StockQuote extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:6',
            'change' => 'decimal:6',
            'change_percent' => 'decimal:4',
            'market_time' => 'datetime',
        ];
    }
}
