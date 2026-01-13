<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'agreement_id',
        'token', // Encrypted final agreementId from executeAgreement
        'masked',
        'balance',
        'bkash_url',
        'callback_url',
        'success_callback_url',
        'failure_callback_url',
        'cancelled_callback_url',
        'payer_reference',
        'agreement_status',
        'agreement_create_time',
        'signature',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Encrypt token when setting
     */
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt token when getting
     */
    public function getTokenAttribute($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // If decryption fails, return as-is (might be plain text from old records)
            return $value;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}