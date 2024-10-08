<?php

namespace Inwebuz\UzumbankMerchant\Controllers;

use Inwebuz\UzumbankMerchant\Models\UzumbankTransaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inwebuz\UzumbankMerchant\UzumbankMerchant;

class UzumbankMerchantController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function check()
    {
        $serviceId = $this->request->input('serviceId');
        if (!$this->isServiceIdValid($serviceId)) {
            return $this->checkError(UzumbankMerchant::ERROR_INCORRECT_SERVICE_ID);
        }
        $timestamp = $this->request->input('timestamp');
        $params = $this->request->input('params', []);
        if (empty($params['type']) || empty($params['id'])) {
            return $this->checkError(UzumbankMerchant::ERROR_REQUIRED_PARAMS_MISSING);
        }
        $payable = $this->getPayableByParams($params);
        if (!$payable) {
            return $this->checkError(UzumbankMerchant::ERROR_PAYABLE_NOT_FOUND);
        }
        if (method_exists($payable, 'uzumbankIsPaid') && $payable->uzumbankIsPaid()) {
            return $this->checkError(UzumbankMerchant::ERROR_ALREADY_PAID);
        }
        if (method_exists($payable, 'uzumbankIsCancelled') && $payable->uzumbankIsCancelled()) {
            return $this->checkError(UzumbankMerchant::ERROR_CANCELLED);
        }
        return response()->json([
            'serviceId' => $serviceId,
            'timestamp' => $timestamp,
            'status' => 'OK',
            'data' => method_exists($payable, 'uzumbankPaymentData') ? $payable->uzumbankPaymentData() : [],
        ]);
    }

    public function create()
    {
        $serviceId = $this->request->input('serviceId');
        if (!$this->isServiceIdValid($serviceId)) {
            return $this->createError(UzumbankMerchant::ERROR_INCORRECT_SERVICE_ID);
        }
        $timestamp = $this->request->input('timestamp');
        $transId = $this->request->input('transId');
        $amount = $this->request->input('amount');
        $params = $this->request->input('params', []);
        if (empty($transId) || empty($amount) || empty($params['type']) || empty($params['id'])) {
            return $this->createError(UzumbankMerchant::ERROR_REQUIRED_PARAMS_MISSING);
        }
        $payable = $this->getPayableByParams($params);
        if (!$payable) {
            return $this->createError(UzumbankMerchant::ERROR_PAYABLE_NOT_FOUND);
        }
        if (method_exists($payable, 'uzumbankIsPaid') && $payable->uzumbankIsPaid()) {
            return $this->createError(UzumbankMerchant::ERROR_ALREADY_PAID);
        }
        if (method_exists($payable, 'uzumbankIsCancelled') && $payable->uzumbankIsCancelled()) {
            return $this->createError(UzumbankMerchant::ERROR_CANCELLED);
        }
        if (method_exists($payable, 'uzumbankAmount') && $payable->uzumbankAmount() > 0 && $payable->uzumbankAmount() != $amount) {
            return $this->createError(UzumbankMerchant::ERROR_INCORRECT_AMOUNT);
        }
        $uzumbankTransaction = UzumbankTransaction::where('uzumbank_service_id', $serviceId)->where('uzumbank_trans_id', $transId)->first();
        if ($uzumbankTransaction) {
            return $this->createError(UzumbankMerchant::ERROR_TRANSACTION_ALREADY_CREATED, $uzumbankTransaction->created_at->getTimestampMs());
        }

        $uzumbankTransaction = UzumbankTransaction::create([
            'uzumbank_service_id' => $serviceId,
            'uzumbank_trans_id' => $transId,
            'uzumbank_timestamp' => $timestamp,
            'uzumbank_amount' => $amount,
            'status' => UzumbankTransaction::STATUS_CREATED,
            'params' => $params,
            'payable_id' => $payable->id,
            'payable_type' => get_class($payable),
        ]);

        return response()->json([
            'serviceId' => $serviceId,
            'transId' => $transId,
            'status' => 'CREATED',
            'transTime' => $uzumbankTransaction->created_at->getTimestampMs(),
            'data' => method_exists($payable, 'uzumbankPaymentData') ? $payable->uzumbankPaymentData() : [],
            'amount' => $amount,
        ]);
    }

    public function confirm()
    {
        $serviceId = $this->request->input('serviceId');
        if (!$this->isServiceIdValid($serviceId)) {
            return $this->confirmError(UzumbankMerchant::ERROR_INCORRECT_SERVICE_ID);
        }
        $timestamp = $this->request->input('timestamp');
        $transId = $this->request->input('transId');
        $paymentSource = $this->request->input('paymentSource');
        $tariff = $this->request->input('tariff');
        $processingReferenceNumber = $this->request->input('processingReferenceNumber');
        if (empty($transId)) {
            return $this->confirmError(UzumbankMerchant::ERROR_REQUIRED_PARAMS_MISSING);
        }
        $uzumbankTransaction = UzumbankTransaction::where('uzumbank_service_id', $serviceId)->where('uzumbank_trans_id', $transId)->first();
        if (!$uzumbankTransaction) {
            return $this->confirmError(UzumbankMerchant::ERROR_TRANSACTION_NOT_FOUND);
        }

        $now = now();
        if ($uzumbankTransaction->created_at->diffInMinutes($now) >= config('uzumbankmerchant.confirm_timeout_in_minutes')) {
            $uzumbankTransaction->status = UzumbankTransaction::STATUS_FAILED;
            $uzumbankTransaction->failed_at = $now;
            $uzumbankTransaction->save();
            return $this->confirmError(UzumbankMerchant::ERROR_TRANSACTION_CANCELLED);
        }
        if ($uzumbankTransaction->isCancelled()) { // failed or reversed
            return $this->confirmError(UzumbankMerchant::ERROR_TRANSACTION_CANCELLED);
        }
        if ($uzumbankTransaction->isConfirmed()) {
            return $this->confirmError(UzumbankMerchant::ERROR_TRANSACTION_ALREADY_CONFIRMED, $uzumbankTransaction->confirmed_at->getTimestampMs());
        }

        $payable = $uzumbankTransaction->payable;
        if (!$payable) {
            return $this->createError(UzumbankMerchant::ERROR_PAYABLE_NOT_FOUND);
        }
        if (method_exists($payable, 'uzumbankIsPaid') && $payable->uzumbankIsPaid()) {
            return $this->createError(UzumbankMerchant::ERROR_ALREADY_PAID);
        }
        if (method_exists($payable, 'uzumbankIsCancelled') && $payable->uzumbankIsCancelled()) {
            return $this->createError(UzumbankMerchant::ERROR_CANCELLED);
        }
        if (method_exists($payable, 'uzumbankAmount') && $payable->uzumbankAmount() > 0 && $payable->uzumbankAmount() != $uzumbankTransaction->uzumbank_amount) {
            return $this->createError(UzumbankMerchant::ERROR_INCORRECT_AMOUNT);
        }

        // confirm
        $uzumbankTransaction->update([
            'uzumbank_timestamp' => $timestamp,
            'uzumbank_payment_source' => $paymentSource,
            'uzumbank_tariff' => $tariff,
            'uzumbank_processing_reference_number' => $processingReferenceNumber,
            'status' => UzumbankTransaction::STATUS_CONFIRMED,
            'confirmed_at' => $now,
        ]);
        if (method_exists($payable, 'uzumbankSetPaid')) {
            $payable->uzumbankSetPaid($uzumbankTransaction);
        }

        return response()->json([
            'serviceId' => $serviceId,
            'transId' => $transId,
            'status' => 'CONFIRMED',
            'confirmTime' => $uzumbankTransaction->confirmed_at->getTimestampMs(),
            'data' => method_exists($payable, 'uzumbankPaymentData') ? $payable->uzumbankPaymentData() : [],
            'amount' => $uzumbankTransaction->uzumbank_amount,
        ]);
    }

    public function reverse()
    {
        $serviceId = $this->request->input('serviceId');
        if (!$this->isServiceIdValid($serviceId)) {
            return $this->reverseError(UzumbankMerchant::ERROR_INCORRECT_SERVICE_ID);
        }
        $timestamp = $this->request->input('timestamp');
        $transId = $this->request->input('transId');

        if (empty($transId)) {
            return $this->reverseError(UzumbankMerchant::ERROR_REQUIRED_PARAMS_MISSING);
        }
        $uzumbankTransaction = UzumbankTransaction::where('uzumbank_service_id', $serviceId)->where('uzumbank_trans_id', $transId)->first();
        if (!$uzumbankTransaction) {
            return $this->reverseError(UzumbankMerchant::ERROR_TRANSACTION_NOT_FOUND);
        }
        $payable = $uzumbankTransaction->payable;
        if (!$payable) {
            return $this->reverseError(UzumbankMerchant::ERROR_PAYABLE_NOT_FOUND);
        }
        if (method_exists($payable, 'uzumbankCanBeReversed') && !$payable->uzumbankCanBeReversed($uzumbankTransaction)) {
            return $this->reverseError(UzumbankMerchant::ERROR_TRANSACTION_CANNOT_BE_REVERSED);
        }
        if ($uzumbankTransaction->isReversed()) {
            return $this->reverseError(UzumbankMerchant::ERROR_TRANSACTION_ALREADY_REVERSED, $uzumbankTransaction->reversed_at->getTimestampMs());
        }

        $now = now();

        // reverse / cancel
        if (method_exists($payable, 'uzumbankReverse')) {
            $payable->uzumbankReverse($uzumbankTransaction);
        }
        $uzumbankTransaction->update([
            'uzumbank_timestamp' => $timestamp,
            'status' => UzumbankTransaction::STATUS_REVERSED,
            'reversed_at' => $now,
        ]);

        return response()->json([
            'serviceId' => $serviceId,
            'transId' => $transId,
            'status' => 'REVERSED',
            'reverseTime' => $uzumbankTransaction->reversed_at->getTimestampMs(),
            'data' => method_exists($payable, 'uzumbankPaymentData') ? $payable->uzumbankPaymentData() : [],
            'amount' => $uzumbankTransaction->uzumbank_amount,
        ]);
    }

    public function status()
    {
        $serviceId = $this->request->input('serviceId');
        if (!$this->isServiceIdValid($serviceId)) {
            return $this->statusError(UzumbankMerchant::ERROR_INCORRECT_SERVICE_ID);
        }
        $timestamp = $this->request->input('timestamp');
        $transId = $this->request->input('transId');

        if (empty($transId)) {
            return $this->statusError(UzumbankMerchant::ERROR_REQUIRED_PARAMS_MISSING);
        }
        $uzumbankTransaction = UzumbankTransaction::where('uzumbank_service_id', $serviceId)->where('uzumbank_trans_id', $transId)->first();
        if (!$uzumbankTransaction) {
            return $this->statusError(UzumbankMerchant::ERROR_TRANSACTION_NOT_FOUND);
        }
        $payable = $uzumbankTransaction->payable;
        if (!$payable) {
            return $this->statusError(UzumbankMerchant::ERROR_PAYABLE_NOT_FOUND);
        }

        return response()->json([
            'serviceId' => $serviceId,
            'transId' => $transId,
            'status' => $uzumbankTransaction->status,
            'transTime' => $uzumbankTransaction->created_at->getTimestampMs(),
            'confirmTime' => $uzumbankTransaction->confirmed_at?->getTimestampMs() ?? null,
            'reverseTime' => $uzumbankTransaction->reversed_at?->getTimestampMs() ?? null,
            'data' => method_exists($payable, 'uzumbankPaymentData') ? $payable->uzumbankPaymentData() : [],
            'amount' => $uzumbankTransaction->uzumbank_amount,
        ]);
    }

    private function checkError($errorCode)
    {
        return response()->json([
            'serviceId' => $this->request->input('serviceId'),
            'timestamp' => $this->request->input('timestamp'),
            'status' => 'FAILED',
            'errorCode' => $errorCode,
        ], 400);
    }

    private function createError($errorCode, $transTime = null)
    {
        return response()->json([
            'serviceId' => $this->request->input('serviceId'),
            'transId' => $this->request->input('transId'),
            'status' => 'FAILED',
            'transTime' => $transTime,
            'errorCode' => $errorCode,
        ], 400);
    }

    private function confirmError($errorCode, $confirmTime = null)
    {
        return response()->json([
            'serviceId' => $this->request->input('serviceId'),
            'transId' => $this->request->input('transId'),
            'status' => 'FAILED',
            'confirmTime' => $confirmTime,
            'errorCode' => $errorCode,
        ], 400);
    }

    private function reverseError($errorCode, $reverseTime = null)
    {
        return response()->json([
            'serviceId' => $this->request->input('serviceId'),
            'transId' => $this->request->input('transId'),
            'status' => 'FAILED',
            'reverseTime' => $reverseTime,
            'errorCode' => $errorCode,
        ], 400);
    }

    private function statusError($errorCode, $transTime = null, $confirmTime = null, $reverseTime = null)
    {
        return response()->json([
            'serviceId' => $this->request->input('serviceId'),
            'transId' => $this->request->input('transId'),
            'status' => 'FAILED',
            'transTime' => $transTime,
            'confirmTime' => $confirmTime,
            'reverseTime' => $reverseTime,
            'errorCode' => $errorCode,
        ], 400);
    }

    private function getPayableByParams($params)
    {
        $payable = null;
        $type = $params['type'] ?? null;
        $id = $params['id'] ?? null;
        $payableModels = config('uzumbankmerchant.payable_models');
        if ($type && isset($payableModels[$type]) && class_exists($payableModels[$type]) && is_subclass_of($payableModels[$type], \Illuminate\Database\Eloquent\Model::class)) {
            $payable = $payableModels[$type]::find($id);
        }
        return $payable;
    }

    private function isServiceIdValid($serviceId)
    {
        return config('uzumbankmerchant.service_id') == $serviceId;
    }
}
