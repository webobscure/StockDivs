<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['base_currency', 'quote_currency', 'rate', 'provider'])]
class ExchangeRate extends Model
{
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
        ];
    }
}
