<?php
$mongo = new MongoClient();
$db = $mongo->_2048;

$grid = $db->grid;
$grid->drop();

$history = $db->history;
$history->drop();

$oplog = $db->oplog;
$oplog->drop();

