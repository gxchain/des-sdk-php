<?php
namespace GXChain\Common;

use GXChain\Common\Utils;
use GXChain\Common\PublicKey;
use GXChain\Common\GXSignature;
use Elliptic\EC;

class PrivateKey {
    private $d;
    private $ec;

    public function __construct($d) {
        $this->d = $d;
        $this->ec = new EC('secp256k1');
    }

    public static function fromWif ($_private_wif) {
        $private_wif = Utils::base58_decode($_private_wif);
        $version = $private_wif[0];
        if ($version !== 128) {
            throw new \Exception('Expected version ' . 128 . ', instead got ' . version);
        }
        $private_key = array_slice($private_wif, 0, -4);
        $checksum = array_slice($private_wif, -4);

        $new_checksum = hash('sha256', Utils::bytes2str($private_key));
        $new_checksum = hash('sha256', Utils::hex2str($new_checksum));

        $new_checksum = Utils::str2bytes(Utils::hex2str($new_checksum));

        $new_checksum = array_slice($new_checksum, 0 ,4);

        if (count(array_diff($new_checksum, $checksum)) === 0) {
            $private_key = array_slice($private_key, 1);
            if (count($private_key) === 32) {
                return new PrivateKey($private_key);
            } else {
                throw new \Exception('WARN: Expecting 32 bytes PrivateKey');
            }
        } else {
            throw new \Exception('Checksum did not match');
        }
    }

    public function keyFromPrivate() {
        return $this->ec->keyFromPrivate($this->d);
    }

    public function getSharedSecret($public_key) {
        $priv = $this->ec->keyFromPrivate($this->d)->getPrivate();
        $pub = PublicKey::fromString($public_key)->keyFromPublic()->getPublic(false, true);
        $pair = $this->ec->keyFromPublic($pub);
        $shared = $this->ec->keyFromPublic(($pair->pub->mul($priv)->encode(true, false)));
        $S = $shared->getPublic()->x->toArray();
        if (count($S) < 32) {
            $S = array_pad($S, -32,0);
        }
        return hash('sha512', Utils::bytes2str($S));
    }

    public function sign($msg) {
        if (is_string($msg)) {
            $msg = hash('sha256', $msg);
        } else {
            $msg = hash('sha256', Utils::bytes2str($msg));
        }
        $sig = null;
        $sigDER = null;
        while (true) {
            $options["pers"] = Utils::str2bytes(Utils::getRandCode());
            $sig = $this->ec->sign($msg, $this->d, $options);
            $sigDER = $sig->toDER();
            $lenR = $sigDER[3];
            $lenS = $sigDER[5 + $lenR];
            if ($lenR === 32 && $lenS === 32) {
                break;
            }
        }
        $GXSignature = new GXSignature($sig->r, $sig->s, $sig->recoveryParam + 31);
        return bin2hex(Utils::bytes2str($GXSignature->toBuffer()));
    }
 }