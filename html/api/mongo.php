<?php
function mongo() {
    static $m = null;
    if ($m === null) $m = new MongoClient();
    return $m;
}

