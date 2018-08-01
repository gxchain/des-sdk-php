<?php
namespace GXChain\Common;

class Signature
{

    public function dateTime($time)
    {
        $byte = $this->writeUnsignedSize($time);
        return $byte;
    }

    public function memoLength($length)
    {
        $byte   = [];
        $byte[] = ($length & 0xff);
        return $byte;
    }

    public function memo($memo)
    {
        $byte = unpack('C*', $memo);
        return $byte;
    }

    public function percent($percent)
    {
        $byte   = [];
        $byte[] = ($percent & 0xff);
        $byte[] = ($percent >> 8 & 0xff);
        return $byte;
    }

    public function amount($amount, $assetId)
    {
        $byte    = unpack("C*", pack("L*", $amount));
        for ($i = count($byte); $i < 8; $i++) {
            $byte[] = 0;
        }
        $assetId = $this->account($assetId);
        foreach ($assetId as $key => $val) {
            $byte[] = $val;
        }
        return $byte;
    }

    public function account($accountid)
    {
        $arr    = explode(".", $accountid);
        $number = $arr[2];
        $byte   = $this->writeUnsignedVarLong($number);
        return $byte;
    }

	public function writeUnsignedVarLong($number)
    {
        $byte = [];
        while (($number & -128) != 0) {
            $byte[] = $number & 127 | 128;
            $number = $this->uright($number, 7);
        }
        $byte[] = $number & 127;
        $str    = '';
        foreach ($byte as $key => $value) {
            $str .= pack("C*", $value);
        }
        $byte = unpack("C*", $str);
        return $byte;
    }

    public function uright($a, $n)
    {
        $c = 2147483647 >> ($n - 1);
        return $c & ($a >> $n);
    }

    public function writeUnsignedSize($val)
    {
        $byte   = array();
        $byte[] = ($val & 0xff);
        $byte[] = ($val >> 8 & 0xff);
        $byte[] = ($val >> 16 & 0xff);
        $byte[] = ($val >> 24 & 0xff);
        $str    = '';
        foreach ($byte as $key => $value) {
            $str .= pack("C*", $value);
        }
        $byte = unpack("C*", $str);
        return $byte;
    }
}
?>
