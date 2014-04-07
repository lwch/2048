<?php
$mongo = new MongoClient();
$db = $mongo->_2048;
$grid = $db->grid;
$grid->remove();

$table = array(
    'grid' => array(
        array(0, 0, 0, 0),
        array(0, 0, 0, 0),
        array(0, 0, 0, 0),
        array(0, 0, 0, 0)
    ),
    'status' => 'done'
);

$count = 0;
do
{
    $x = mt_rand(0, 3);
    $y = mt_rand(0, 3);
    $val = pow(2, mt_rand(1, 2));
    if ($table['grid'][$y][$x] == 0) ++$count;
    $table['grid'][$y][$x] = $val;
} while ($count < 2);
$table['lastmodify'] = microtime(true);
$grid->insert($table);

