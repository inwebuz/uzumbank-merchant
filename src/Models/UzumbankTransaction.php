<?php

namespace Inwebuz\UzumbankMerchant\Models;

use Illuminate\Database\Eloquent\Model;

class UzumbankTransaction extends Model
{
    const STATUS_FAILED = 'FAILED';
    const STATUS_CREATED = 'CREATED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_REVERSED = 'REVERSED';

    protected $table = 'uzumbank_transactions';

    protected $casts = [
        'params' => 'array',
        'failed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    protected $guarded = [];

    public function payable()
    {
        return $this->morphTo();
    }

    public function isCreated()
    {
        return $this->status === self::STATUS_CREATED;
    }

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isReversed()
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function isCancelled()
    {
        return $this->isFailed() || $this->isReversed();
    }
}
