# GXChain DES SDK for PHP

## Install

You can install this library via Composer:
```bash
composer require gxchain/des-sdk-php
```

## Usage

### Merchant

```bash
<?php
use GXChain\Client\DESMerchantClient;

$privateKey = '5Ka9YjFQtfUUX2Ddnqka...'; // Private Key})
$accountId = '1.2.19'; // Account ID
$DESMerchantClient = new DESMerchantClient($privateKey, $accountId);

$testCase = (object)array(
    'name' => 'XXX',
    'idcard' => 'XXXXXXXXXXXXXXXXXX'
);

// Async
$DESMerchantClient->createDataExchangeRequest($testCase, 3, function ($res) use ($DESMerchantClient) {
    if ($res->request_id) {
        $requestId = $res->request_id;
        $DESMerchantClient->getResult($requestId, function ($results) {
            echo json_encode($results);
        });
    } else {
        echo json_encode($res);
    }
});

// Sync
$res = $DESMerchantClient->createDataExchangeRequestSync($testCase, 3);
if ($res->request_id) {
    $results = $DESMerchantClient->getResultSync($res->request_id);
    echo json_encode($results);
} else {
    echo json_encode($res);
}
```

### Datasource

```bash
<?php
use GXChain\Client\DESDatasourceClient;
$privateKey = '5Ka9YjFQtfUUX2Ddnqka...'; // Private Key})
$accountId = '1.2.19'; // Account ID
$queryURL = 'https://www.baidu.com/';
$DESDatasourceClient = new DESDatasourceClient($privateKey, $accountId, $queryURL);
```

## Dev Documents

https://doc.gxb.io/des/
