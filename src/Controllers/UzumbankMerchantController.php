<?php

namespace Inwebuz\UzumbankMerchant\Controllers;

use Inwebuz\UzumbankMerchant\Models\UzumbankTransaction;
use Inwebuz\UzumbankMerchant\Services\UzumbankMerchant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UzumbankMerchantController extends Controller
{
    public function check(Request $request)
    {
        return response()->json([
            'success' => true,
        ]);
    }

    public function create(Request $request)
    {
        $transaction = new UzumbankTransaction();
        $transaction->save();

        return $transaction;
    }
}