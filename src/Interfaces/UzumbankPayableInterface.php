<?php

namespace Inwebuz\UzumbankMerchant\Interfaces;

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
     * @return bool Returns true if payable is already paid.
     */
    public function uzumbankAmount(): float;

    /**
     * Called after transaction is successfully confirmes.
     *
     * You have to set your payable status to "paid".
     * 
     * @return void.
     */
    public function uzumbankSetPaid(): void;

    /**
     * Check if payable can be reversed (cancelled);
     *
     * @return bool Returns true if payable can be reversed.
     */
    public function uzumbankCanBeReversed(): bool;

    /**
     * Called after transaction is successfully reversed.
     *
     * You have to set your payable status to "reversed".
     * 
     * @return bool Returns true if payable is already paid.
     */
    public function uzumbankReverse(): void;
}
