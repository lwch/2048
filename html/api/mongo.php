<?php
function mongo() {
    static $m = null;
    if ($m === null) $m = new MongoClient();
    return $m;
}

function uuid($len) {
    $res = '';
    for ($i = 0; $i < $len; ++$i) {
        $type = mt_rand(0, 2);
        switch ($type) {
        case 0:
            $res .= chr(0x30 + mt_rand(0, 9));
            break;
        case 1:
            $res .= chr(0x61 + mt_rand(0, 25));
            break;
        case 2:
            $res .= chr(0x41 + mt_rand(0, 25));
            break;
        }
    }
    return $res;
}

