<?php

namespace Inwebuz\UzumbankMerchant\Models;

use Illuminate\Database\Eloquent\Model;

class UzumbankTransaction extends Model
{
    const STATUS_FAILED = -1;
    const STATUS_CREATED = 0;
    const STATUS_CONFIRMED = 1;

    protected $table = 'uzumbank_transactions';

    protected $fillable = [
        'name',
    ];
}