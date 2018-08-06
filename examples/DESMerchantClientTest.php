<?php
require __DIR__ . "/../vendor/autoload.php";

use GXChain\Client\DESMerchantClient;

$privateKey = '5Ka9YjFQtfUUX2Ddnqka...'; // Private Key})
$accountId = '1.2.19'; // Account ID
$DESMerchantClient = new DESMerchantClient($privateKey, $accountId);

$testCase = (object)array(
	'name' => 'XXX',
    'idcard' => 'XXXXXXXXXXXXXXXXXX'
);

$DESMerchantClient->createDataExchangeRequest($testCase, 2, function ($res) use ($DESMerchantClient) {
    $requestId = $res->request_id;
    $DESMerchantClient->getResult($requestId, function ($results) {
        echo json_encode($results);
    });
});
