<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
      'account_id',
      'category_id',
      'remark',
      'debit',
      'credit',
      'transaction_at',
      'is_debt',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // public function category(): HasOne
    // {
    //     return $this->hasOne(Category::class);
    // }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function debt_relations(): HasMany
    {
        return $this->hasMany(TransactionDebtRelation::class);
    }
}
