<?php
namespace GXChain\Common;

use GXChain\Common\Utils;
use Elliptic\EC;

class PublicKey {
    private $Q;
    private $ec;

    public function __construct($Q) {
        $this->Q = $Q;
        $this->ec = new EC('secp256k1');
    }

    public static function fromString ($public_key) {
        $address_prefix = 'GXC';
        $prefix = substr($public_key,0, strlen($address_prefix));
        if ($address_prefix !== $prefix) {
            throw new \Exception('Expecting key to begin with ' . $address_prefix . ', instead got ' . $prefix);
        }
        $public_key = substr($public_key, strlen($address_prefix));
        $public_key = Utils::base58_decode($public_key);

        $checksum = array_slice($public_key, -4);
        $public_key = array_slice($public_key, 0, -4);

        $new_checksum = hash('ripemd160', Utils::bytes2str($public_key));
        $new_checksum = Utils::str2bytes(Utils::hex2str($new_checksum));
        $new_checksum = array_slice($new_checksum, 0 ,4);

        if (count(array_diff($new_checksum, $checksum)) === 0) {
            return new PublicKey($public_key);
        } else {
            throw new \Exception('Checksum did not match');
        }
    }

    public function keyFromPublic() {
        return $this->ec->keyFromPublic($this->Q);
    }
}