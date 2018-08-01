<?php
require __DIR__ . "/../vendor/autoload.php";

use GXChain\Client\DESMerchantClient;

$DESMerchantClient = new DESMerchantClient();

$testCase = (object)array(
	'name' => 'XXX',
    'idcard' => 'XXXXXXXXXXXXXXXXXX'
);

$DESMerchantClient->createDataExchangeRequest($testCase, 2, function ($res) use ($DESMerchantClient) {
    $requestId = $res->request_id;
    $DESMerchantClient->getResult($requestId, function ($results) {
        echo json_encode($results);
    });
})

?>