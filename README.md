#GXChain DES SDK for PHP

## Install'

You can install this library via Composer:
```bash
composer require gxchain/des-sdk-php
```

## Usage

### Config

```bash
<?php
const ACCOUNT_ID = '1.2.19'; // Account ID
const PRIVATE_KEY = '5Ka9YjFQtfUUX2Ddnqka...'; // Private Key})
```

### Merchant

```bash
<?php
use GXChain\Client\DESMerchantClient;

$DESMerchantClient = new DESMerchantClient();

$testCase = (object)array(
    'name' => 'XXX'
    'idcard' => 'XXXXXXXXXXXXXXXXXX'
);

$DESMerchantClient->createDataExchangeRequest($testCase, 2, function ($res) use ($DESMerchantClient) {
    $requestId = $res->request_id;
    $DESMerchantClient->getResult($requestId, function ($results) {
        echo json_encode($results);
    });
})
```

## Dev Documents

https://doc.gxb.io/des/
