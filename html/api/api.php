<?php
require_once(__DIR__.'/mongo.php');
require_once(__DIR__.'/2048.php');

if (!isset($_REQUEST['api_name'])) {
    echo json_encode(array('stat' => 101, 'data' => array(), 'msg' => 'error call'));
    exit;
}
$api_name = $_REQUEST['api_name'];
switch ($api_name) {
case '2048.update':
    $res = _2048_update();
    break;
case '2048.action':
    $res = _2048_action();
    break;
}
if (empty($res)) $res = array('stat' => 101, 'data' => array(), 'msg' => 'empty return');
echo json_encode($res);

