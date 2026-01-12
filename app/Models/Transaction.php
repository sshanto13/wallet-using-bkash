<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'trx_id',
        'payment_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /* -------------------------
     | Relationships
     |-------------------------*/
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}