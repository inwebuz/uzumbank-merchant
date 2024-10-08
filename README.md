## Laravel Package for Uzumbank Merchant

This package is a Laravel package for Uzumbank Merchant.

## Installation

You can install the package via composer:

```bash
composer require inwebuz/uzumbank-merchant
```

## Usage

### Publish config and migrations

```bash
php artisan vendor:publish --tag=uzumbankmerchant
```

```bash
php artisan migrate
```

### Add config

Create login and password for Uzumbank Merchant, add service_id in `.env` file.

```php
UZUMBANK_MERCHANT_LOGIN="your_login"
UZUMBANK_MERCHANT_PASSWORD="your_password"
UZUMBANK_MERCHANT_SERVICE_ID="uzumbank_service_id"
```

In uzumbankmerchant.php config file write your payable models

For example:

```php
'payable_models' => [
    'order' => 'App\\Models\\Order',
],
```

Where "order" is parameter name for deeplink

### Add model

Create a payable model that implements `Inwebuz\UzumbankMerchant\Interfaces\UzumbankPayableInterface` interface.

```php
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
```

### Generate deeplink to Uzumbank

For example:

https://www.uzumbank.uz/open-service?serviceId=498619073&type=order&id=9932&amount=100000

type - type of payable model in uzumbankmerchant config payable_models key

id - id of payable model
