<?php
require_once(__DIR__.'/mongo.php');
$direction_enum = array(
    0 => 'up',
    1 => 'down',
    2 => 'left',
    3 => 'right',
    'up' => 0,
    'down' => 1,
    'left' => 2,
    'right' => 3
);
$timeout = 10;

function _2048_search_left($grid) {
    $res = array();
    for ($y = 0; $y < 4; ++$y) {
        for ($x = 0; $x < 4; ++$x) {
            if ($grid[$y][$x] == 0) $res[] = ($y << 2) + $x;
        }
    }
    return $res;
}

function _2048_rand_set($left, $grid) {
    $val = mt_rand(0, 1) ? 4 : 2;
    $where = $left[mt_rand(0, count($left) - 1)];
    $row = $where >> 2;
    $col = $where % 4;
    $grid[$row][$col] = $val;
    return $grid;
}

function _2048_move_up($grid) {
    $done = 0;
    for ($x1 = 0; $x1 < 4; ++$x1) {
        for ($y1 = 0; $y1 < 4; ++$y1) {
            if ($grid[$y1][$x1] == 0) { # 先做trim操作
                $trim = 0;
                for ($y2 = $y1 + 1; $y2 < 4; ++$y2) {
                    if ($grid[$y2][$x1]) {
                        $trim = 1;
                        break;
                    }
                }
                if ($trim) {
                    $done = 1;
                    for ($y2 = $y1 + 1; $y2 < 4; ++$y2) $grid[$y2 - 1][$x1] = $grid[$y2][$x1];
                    $grid[3][$x1] = 0;
                    if ($grid[$y1][$x1] == 0) --$y1;
                }
            }
        }
        for ($y1 = 0; $y1 < 3; ++$y1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1 + 1][$x1]) {
                $done = 1;
                $grid[$y1][$x1] <<= 1;
                if ($grid[$y1][$x1] == 2048) $done = 2;
                for ($y2 = $y1 + 2; $y2 < 4; ++$y2) $grid[$y2 - 1][$x1] = $grid[$y2][$x1];
                $grid[3][$x1] = 0;
            }
        }
    }
    return array($done, $grid);
}

function _2048_move_down($grid)
{
    $done = 0;
    for ($x1 = 0; $x1 < 4; ++$x1) {
        for ($y1 = 3; $y1 > 0; --$y1) {
            if ($grid[$y1][$x1] == 0) { # 先做trim操作
                $trim = 0;
                for ($y2 = $y1 - 1; $y2 >= 0; --$y2) {
                    if ($grid[$y2][$x1]) {
                        $trim = 1;
                        break;
                    }
                }
                if ($trim) {
                    $done = 1;
                    for ($y2 = $y1 - 1; $y2 >= 0; --$y2) $grid[$y2 + 1][$x1] = $grid[$y2][$x1];
                    $grid[0][$x1] = 0;
                    if ($grid[$y1][$x1] == 0) ++$y1;
                }
            }
        }
        for ($y1 = 3; $y1 > 0; --$y1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1 - 1][$x1]) {
                $done = 1;
                $grid[$y1][$x1] <<= 1;
                if ($grid[$y1][$x1] == 2048) $done = 2;
                for ($y2 = $y1 - 2; $y2 >= 0; --$y2) $grid[$y2 + 1][$x1] = $grid[$y2][$x1];
                $grid[0][$x1] = 0;
            }
        }
    }
    return array($done, $grid);
}

function _2048_move_left($grid)
{
    $done = 0;
    for ($y1 = 0; $y1 < 4; ++$y1) {
        for ($x1 = 0; $x1 < 3; ++$x1) {
            if ($grid[$y1][$x1] == 0) { # 先做trim操作
                $trim = 0;
                for ($x2 = $x1 + 1; $x2 < 4; ++$x2) {
                    if ($grid[$y1][$x2]) {
                        $trim = 1;
                        break;
                    }
                }
                if ($trim) {
                    $done = 1;
                    for ($x2 = $x1 + 1; $x2 < 4; ++$x2) $grid[$y1][$x2 - 1] = $grid[$y1][$x2];
                    $grid[$y1][3] = 0;
                    if ($grid[$y1][$x1] == 0) --$x1;
                }
            }
        }
        for ($x1 = 0; $x1 < 3; ++$x1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1][$x1 + 1]) {
                $done = 1;
                $grid[$y1][$x1] <<= 1;
                if ($grid[$y1][$x1] == 2048) $done = 2;
                for ($x2 = $x1 + 2; $x2 < 4; ++$x2) $grid[$y1][$x2 - 1] = $grid[$y1][$x2];
                $grid[$y1][3] = 0;
            }
        }
    }
    return array($done, $grid);
}

function _2048_move_right($grid)
{
    $done = 0;
    for ($y1 = 0; $y1 < 4; ++$y1) {
        for ($x1 = 3; $x1 > 0; --$x1) {
            if ($grid[$y1][$x1] == 0) { # 先做trim操作
                $trim = 0;
                for ($x2 = $x1 - 1; $x2 >= 0; --$x2) {
                    if ($grid[$y1][$x2]) {
                        $trim = 1;
                        break;
                    }
                }
                if ($trim) {
                    $done = 1;
                    for ($x2 = $x1 - 1; $x2 >= 0; --$x2) $grid[$y1][$x2 + 1] = $grid[$y1][$x2];
                    $grid[$y1][0] = 0;
                    if ($grid[$y1][$x1] == 0) ++$x1;
                }
            }
        }
        for ($x1 = 3; $x1 > 0; --$x1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1][$x1 - 1]) {
                $done = 1;
                $grid[$y1][$x1] <<= 1;
                if ($grid[$y1][$x1] == 2048) $done = 2;
                for ($x2 = $x1 - 2; $x2 >= 0; --$x2) $grid[$y1][$x2 + 1] = $grid[$y1][$x2];
                $grid[$y1][0] = 0;
            }
        }
    }
    return array($done, $grid);
}

function _2048_do_action($grid, $dir) {
    GLOBAL $direction_enum;
    switch ($dir) {
    case $direction_enum['up']:
        return _2048_move_up($grid);
    case $direction_enum['down']:
        return _2048_move_down($grid);
    case $direction_enum['left']:
        return _2048_move_left($grid);
    case $direction_enum['right']:
        return _2048_move_right($grid);
    }
}

function _2048_check_action($grid, $oplog) {
    GLOBAL $direction_enum;
    GLOBAL $timeout;
    $grid_res = $grid->findOne();
    $res = $oplog->find(array('type' => 'move', 'time' => array('$gt' => $grid_res['lastmodify'])));
    if ($res->count()) {
        while ($grid_res['status'] != 'done') $grid_res = $grid->findOne();
        if (time() - $grid_res['lastmodify'] < $timeout) return;
        $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'updating')));
        $counts = array('up', 'down', 'left', 'right');
        $max = array('count' => 0); # array('count' => count, 'dir' => dir)
        foreach ($counts as $v) {
            $qry = array('type' => 'move', 'time' => array('$gt' => $grid_res['lastmodify']), 'dir' => $direction_enum[$v]);
            $count = $oplog->count($qry);
            if ($count > $max['count']) {
                $max['count'] = $count;
                $max['dir'] = $direction_enum[$v];
            }
        }
        $g = $grid_res['grid'];
        list($status, $g) = _2048_do_action($g, $max['dir']);
        if ($status) {
            if ($status == 1) {
                $left = _2048_search_left($g);
                $g = _2048_rand_set($left, $g);
                $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'done', 'lastmodify' => time(), 'grid' => $g)));
            } else { # win
                # TODO
            }
        } else {
            $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'done', 'lastmodify' => time())));
        }
    }
}

function _2048_update() {
    $mongo = mongo();
    $db = $mongo->_2048;
    $grid = $db->grid;
    $oplog = $db->oplog;

    _2048_check_action($grid, $oplog);
    do
    {
        $res = $grid->findOne();
        if ($res['status'] == 'done') {
            unset($res['_id']);
            break;
        }
    } while (1);
    return array('stat' => 0, 'data' => $res['grid']);
}

function _2048_action() {
    $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : null;
    if ($dir === null) return array('stat' => '102', 'data' => array(), 'msg' => 'param error');
    $mongo = mongo();
    $db = $mongo->_2048;
    $oplog = $db->oplog;
    $done = $oplog->insert(array('type' => 'move', 'time' => time(), 'dir' => intval($dir)));
    return array('stat' => 0, 'data' => array());
}

