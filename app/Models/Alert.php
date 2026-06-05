<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'ticker', 'type', 'target_value', 'is_active', 'triggered_at'])]
class Alert extends Model
{
    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:6',
            'is_active' => 'boolean',
            'triggered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
