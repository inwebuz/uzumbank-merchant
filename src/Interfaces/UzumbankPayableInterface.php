<?php

namespace Inwebuz\UzumbankMerchant\Interfaces;

use Inwebuz\UzumbankMerchant\Models\UzumbankTransaction;

interface UzumbankPayableInterface
{
    /**
     * Check if payable is already paid.
     *
     * @return bool Returns true if payable is already paid.
     */
    public function uzumbankIsPaid(): bool;

    /**
     * Check if payable is cancelled.
     *
     * @return bool Returns true if payable is cancelled.
     */
    public function uzumbankIsCancelled(): bool;

    /**
     * Retruns info about payable and transaction
     *
     * @return array An array of key object pairs, each object contains "value" key and value of that key.
     * 
     * Example:
     * [
     *    'type' => [
     *        'value' => 'Order,
     *    ],
     *    'id' => [
     *        'value' => 1,
     *    ],
     * ]
     */
    public function uzumbankPaymentData(): array;

    /**
     * Returns payable amount in UZS tiyins.
     *
     * @return float Returns amount of payable in uzs tiyins or return 0 to allow any amount.
     */
    public function uzumbankAmount(): float;

    /**
     * Called after transaction is successfully confirmes.
     *
     * You have to set your payable status to "paid".
     * 
     * @param UzumbankTransaction $uzumbankTransaction
     * @return void.
     */
    public function uzumbankSetPaid(UzumbankTransaction $uzumbankTransaction): void;

    /**
     * Check if payable can be reversed (cancelled);
     *
     * @param UzumbankTransaction $uzumbankTransaction
     * @return bool Returns true if payable can be reversed.
     */
    public function uzumbankCanBeReversed(UzumbankTransaction $uzumbankTransaction): bool;

    /**
     * Called after transaction is successfully reversed.
     *
     * You have to set your payable status to "reversed".
     * 
     * @param UzumbankTransaction $uzumbankTransaction
     * @return void.
     */
    public function uzumbankReverse(UzumbankTransaction $uzumbankTransaction): void;
}
