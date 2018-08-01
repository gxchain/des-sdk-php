<?php
namespace GXChain\Common;


class GXSignature
{

    private $r;
    private $s;
    private $i;

    public function __construct($r, $s, $i1) {
        $this->r = $r;
        $this->s = $s;
        $this->i = $i1;
    }

    public function toBuffer () {
        $data = array_merge(
            Utils::str2bytes(pack('C*', $this->i)),
            $this->r->toArray(),
            $this->s->toArray()
        );
        return $data;
    }

}
?>
