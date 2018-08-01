<?php
namespace GXChain\Common;

class Utils
{
    public static function getRandCode()
    {
        $charts = "123456789";
        $max = strlen($charts) - 1;
        $noncestr = "";
        for($i = 0; $i < 15; $i++)
        {
            $noncestr .= $charts[mt_rand(0, $max)];
        }
        return $noncestr;
    }

    public static function str2hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    public static function hex2str($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    public static function hex2bytes($hex)
    {
        $arr   = str_split($hex);
        $count = count($arr);
        $data  = [];
        for ($i = 0; $i < $count; $i += 2) {
            $a            = hexdec($arr[$i]);
            $b            = hexdec($arr[$i + 1]);
            $data[$i / 2] = ((($a << 4) + $b)) & 0xff;
        }
        $str = '';
        foreach ($data as $key => $value) {
            $str .= pack("C*", $value);
        }
        $byte = unpack("C*", $str);
        $byte = array_values($byte);
        return $byte;
    }

    public static function bytes2str($arr) {
        $str = '';
        foreach ($arr as $key => $value) {
            $str .= pack("C*", $value);
        }
        return $str;
    }

    public static function str2bytes($str) {
        $byteArr = unpack("C*", $str);
        return $byteArr;
    }

    public static function base58_decode($str)
    {
        $ALPHABET     = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $ALPHABET_ARR = str_split($ALPHABET);
        $BASE         = count($ALPHABET_ARR);
        $LEADER       = $ALPHABET_ARR[0];
        $ALPHABET_MAP = [];
        for ($i = 0; $i < $BASE; $i++) {
            $ALPHABET_MAP[$ALPHABET[$i]] = $i;
        }
        $str_arr = str_split($str);
        $bytes   = [0];
        for ($i = 0; $i < count($str_arr); $i++) {
            $value = $ALPHABET_MAP[$str_arr[$i]];
            $carry = $value;
            for ($j = 0; $j < count($bytes); ++$j) {
                $carry += $bytes[$j] * $BASE;
                $bytes[$j] = $carry & 0xff;
                $carry >>= 8;
            }
            while ($carry > 0) {
                $bytes[] = $carry & 0xff;
                $carry >>= 8;
            }
        }
        for ($k = 0; $k === $LEADER && $k < count($str_arr) - 1; ++$k) {
            $bytes[] = 0;
        }
        $bytes = array_reverse($bytes);
        return $bytes;
    }
}
?>
