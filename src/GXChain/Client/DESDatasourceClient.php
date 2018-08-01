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

    public function __construct() {
        $this->privateKey = Config::PRIVATE_KEY;
        $this->account_id = Config::ACCOUNT_ID;
        $this->baseURL = Config::BASE_URL; 
    }

    public function heartbeat($products) {
        $timestamp = time() + 3;
        $params = array(
            'account' => $this->account_id,
            'products' => $products,
            'timestamp' => $timestamp,
            'signature' => PrivateKey::fromWif($this->privateKey)->sign($this->account_id . '|' . json_encode($products) . '|' . $timestamp)
        );

        var_dump($params);exit;
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

?>
