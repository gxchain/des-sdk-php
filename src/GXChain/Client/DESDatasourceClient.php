<?php

namespace GXChain\Client;

use GXChain\Http\RequestCore;
use GXChain\Http\ResponseCore;
use GXChain\Common\Utils;
use GXChain\Common\PrivateKey;
use GXChain\Common\Aes;
use GXChain\Config;

class DESDatasourceClient {
    private $privateKey;
    private $account_id;
    private $baseURL;
    private $queryURL;

    public function __construct($privateKey, $accountId, $queryURL) {
        $this->privateKey = $privateKey;
        $this->account_id = $accountId;
        $this->baseURL = Config::BASE_URL;
        $this->queryURL = $queryURL;
    }

    public function heartbeat($products) {
        $timestamp = time() + 3;
        $params = array(
            'account' => $this->account_id,
            'products' => $products,
            'queryUrl' => $this->queryURL,
            'timestamp' => $timestamp,
            'signature' => PrivateKey::fromWif($this->privateKey)->sign($this->account_id . '|' . json_encode($products) . '|' . $this->queryURL . '|' . $timestamp)
        );
        $url = $this->baseURL . '/api/datasource/heartbeat';
        $request = new RequestCore($url);
        $request->set_method('POST');
        $request->add_header('Content-Type', 'application/json');
        $request->set_body(json_encode($params));
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $output = $res->body;
        return $output;
    }

    public function encrypt($params, $publicKey) {
        $nonce = Utils::getRandCode();
        return array(
            'data' => AES::encryptMessage($params, $this->privateKey, $publicKey, $nonce),
            'nonce' => $nonce
        );
    }

    public function decrypt($message, $nonce, $publicKey) {
        return AES::decryptMessage($message, $this->privateKey, $publicKey, $nonce);
    }
}
