<?php

namespace GXChain\Client;

use GXChain\Http\RequestCore;
use GXChain\Http\ResponseCore;
use GXChain\Common\Utils;
use GXChain\Common\Validator;
use GXChain\Common\Signature;
use GXChain\Common\PrivateKey;
use GXChain\Common\Aes;
use GXChain\Config;

class DESMerchantClient {
    private $privateKey;
    private $account_id;
    private $baseURL;
    private $timeout;

    public function __construct($privateKey, $accountId) {
        $this->privateKey = $privateKey;
        $this->account_id = $accountId;
        $this->baseURL = Config::BASE_URL;
    }

    /**
     * fetch product info by product id
     * @param $productId
     */
    public function getProduct($productId) {
        $url = $this->baseURL . "/api/product/" . $productId;
        $request = new RequestCore($url);
        $request->set_method('GET');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $output = $res->body;
        return json_decode($output);
    }

    /**
     * create data exchange request
     * @param $params
     * @param $productId
     */
    public function createDataExchangeRequest($params, $productId, $callback) {

        $Signature = new Signature();
        $Validator = new Validator();
        $prod = $this->getProduct($productId);
        $reqBody = array();
        $filteredParams = $Validator->validate($params, $prod->product->input);
        $bodyParam = array('params' => $filteredParams, 'timestamp' => time());
        $expiration = time() + Config::DEFAULT_TIMEOUT;
        foreach ($prod->onlineDatasources as $datasource) {

            $byteDataArr = array(
                'byteFrom'       => $Signature->account($this->account_id),
                'byteTo'         => $Signature->account($datasource->accountId),
                'byteProxy'      => $Signature->account($prod->des->accountId),
                'byteAmount'     => $Signature->amount($prod->product->price->amount, $prod->product->price->assetId),
                'bytePercent'    => $Signature->percent($prod->des->percent),
                'byteMemoLength' => $Signature->memoLength(strlen(md5(json_encode($bodyParam, 320)))),
                'byteMemo'       => $Signature->memo(md5(json_encode($bodyParam, 320))),
                'byteExpiration'       => $Signature->dateTime($expiration),
                'byteSignatures' => [0]
            );

            $byteArr = array_merge(
                $byteDataArr['byteFrom'],
                $byteDataArr['byteTo'],
                $byteDataArr['byteProxy'],
                $byteDataArr['byteAmount'],
                $byteDataArr['bytePercent'],
                $byteDataArr['byteMemoLength'],
                $byteDataArr['byteMemo'],
                $byteDataArr['byteExpiration'],
                $byteDataArr['byteSignatures']
            );

            $signatures = PrivateKey::fromWif($this->privateKey)->sign($byteArr);
            $nonce = Utils::getRandCode();

            array_push($reqBody, array(
                'params' => Aes::encryptMessage($bodyParam, $this->privateKey, $datasource->publicKey , $nonce),
                'nonce' => $nonce,
                'requestParams' => array(
                    'from' => $this->account_id,
                    'to' => $datasource->accountId,
                    'proxyAccount' => $prod->des->accountId,
                    'percent' => $prod->des->percent,
                    'amount' => array('amount' => $prod->product->price->amount, 'assetId' => $prod->product->price->assetId),
                    'expiration' => $expiration,
                    'memo' => md5(json_encode($bodyParam, 320)),
                    'signatures' => [$signatures]
                )
            ));
        }
        $url = $this->baseURL . "/api/request/create/" . $productId;
        $request = new RequestCore($url);
        $request->set_method('POST');
        $request->add_header('Content-Type', 'application/json');
        $request->set_body(json_encode($reqBody));
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        $output = $res->body;
        $callback(json_decode($output));
    }

    /**
     * fetch result by request id
     * @param $requestId
     * @param $timeout
     */
    public function getResult($requestId, $callback, $timeout = 8000) {
        $start = time();
        $this->timeout = $timeout;
        return $this->innerFetch($requestId, $start, null, $callback);
    }


    public function innerFetch($requestId, $start, $latestResult, $callback) {
        $url = $this->baseURL . "/api/request/$requestId";
        $request = new RequestCore($url);
        $request->set_method('GET');
        $request->send_request();
        $res = new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
        if ($res->isOK()) {
            $latestResult = json_decode($res->body);
            if ($latestResult && $latestResult->status !== "IN_PROGRESS") {
                return $this->decryptResult($latestResult, $callback);
            } else {
                if (time() - $start < $this->timeout) {
                    sleep(60 / 1000 );
                    $this->innerFetch($requestId, $start, $latestResult, $callback);
                } else {
                    return $this->decryptResult($latestResult, $callback);
                }
            }
        } else {
            if (time() - $start < $this->timeout) {
                sleep(60 / 1000 );
                $this->innerFetch($requestId, $start, $latestResult, $callback);
            } else {
                return $this->decryptResult($latestResult, $callback);
            }
        }
    }

    /**
     * decrypt result before it returned
     * @param $result
     */
    public function decryptResult($result, $callback) {
        if ($result && $result->datasources) {
            $newArr = array();
            foreach ($result->datasources as $item) {
                $newItem = $item;
                if ($newItem->status === "SUCCESS") {
                    $newItem->data = json_decode(Aes::decryptMessage($newItem->data, $this->privateKey, $newItem->datasourcePublicKey, $newItem->nonce));
                }
                array_push($newArr, $newItem);
            }
            $result->datasources = $newArr;
        }
        $callback($result);
    }
}
