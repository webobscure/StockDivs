<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticker', 'amount', 'currency', 'ex_dividend_date', 'record_date', 'payment_date', 'declaration_date', 'dividend_yield', 'frequency', 'provider'])]
class Dividend extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:6',
            'dividend_yield' => 'decimal:4',
            'ex_dividend_date' => 'date',
            'record_date' => 'date',
            'payment_date' => 'date',
            'declaration_date' => 'date',
        ];
    }
}
