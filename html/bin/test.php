<?php
require(__DIR__.'/../api/mongo.php');
for ($i = 0; $i < 100; ++$i) {
    echo uuid(16)."\n";
}

