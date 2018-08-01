<?php
namespace GXChain\Common;

use GXChain\Common\Utils;
use GXChain\Common\PrivateKey;
use GXChain\Common\PublicKey;

class Aes{
    private $iv;
    private $key;

    public function __construct($iv, $key) {
        $this->iv = $iv;
        $this->key = $key;
    }

    public static function encryptMessage ($message, $privateKey, $publicKey, $nonce) {
        // Getting nonce bytes
        $nonce = Utils::str2bytes($nonce);

        // Getting shared secret
        $S = PrivateKey::fromWif($privateKey)->getSharedSecret($publicKey);
        $S = Utils::str2bytes($S);

        // Calculating checksum
        $aes = self::fromSeed(array_merge($nonce, $S));

        $message = json_encode($message, 320);
        $checksum = Utils::str2bytes(Utils::hex2str(hash('sha256', $message)));
        $checksum = array_slice($checksum, 0 , 4);

        // Concatenating checksum + message bytes
        $payload = array_merge($checksum, Utils::str2bytes($message));

        // Applying encryption
        return $aes->encrypt($payload);
    }

    public static function decryptMessage ($message, $privateKey, $publicKey, $nonce) {
        // Getting nonce bytes
        $nonce = Utils::str2bytes($nonce);

        // Getting shared secret
        $S = PrivateKey::fromWif($privateKey)->getSharedSecret($publicKey);
        $S = Utils::str2bytes($S);
        $seed = array_merge($nonce, $S);

        return self::fromSeed($seed)->decrypt($message);;
    }

    public function encrypt($payload) {
        $payload = Utils::bytes2str($payload);
        $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $this->key,OPENSSL_RAW_DATA, $this->iv);
        return bin2hex($encrypted);
    }

    public function decrypt($sStr) {
        $sStr = Utils::hex2str($sStr);
        $decrypted   = openssl_decrypt($sStr, 'aes-256-cbc', $this->key,OPENSSL_RAW_DATA, $this->iv);
        $checksum = substr($decrypted, 0, 4);
        $decrypted = substr($decrypted, 4);

        $new_checksum = Utils::hex2bytes(hash('sha256', $decrypted));
        $new_checksum = array_slice($new_checksum, 0 ,4);
        $new_checksum = Utils::bytes2str($new_checksum);

        if ($checksum !== $new_checksum) {
            throw new \Exception("Invalid key, could not decrypt message(2)");
        }

        return $decrypted;
    }


    public static function fromSeed ($seed) {
        if (is_null($seed)) {
            throw new \Exception('seed is required');
        }
        $hash = hash('sha512', Utils::bytes2str($seed));
        return self::fromSha512($hash);
    }

    public static function fromSha512 ($hash) {
        if ( strlen($hash) !== 128 ) {
            throw new \Exception('A Sha512 in HEX should be 128 characters long, instead got ' . strlen($hash));
        }

        $iv = substr($hash, 64, 32);
        $key = substr($hash, 0, 64);

        $iv = Utils::hex2str($iv);
        $key = Utils::hex2str($key);

        return new Aes($iv, $key);
    }
}
?>