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
$status_enum = array(
    'move_success' => 0,
    'move_fail'    => 1,
    'win'          => 2,
    'lose'         => 3
);
$timeout = 3.0;

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

function _2048_can_merge($grid) {
    for ($y = 0; $y < 4; ++$y) {
        for ($x = 0; $x < 4; ++$x) {
            if ($x > 0 && $grid[$y][$x] == $grid[$y][$x - 1]) return 1;
            if ($x < 3 && $grid[$y][$x] == $grid[$y][$x + 1]) return 1;
            if ($y > 0 && $grid[$y][$x] == $grid[$y - 1][$x]) return 1;
            if ($y < 3 && $grid[$y][$x] == $grid[$y + 1][$x]) return 1;
        }
    }
    return 0;
}

function _2048_move_up($grid) {
    GLOBAL $status_enum;
    $done = $status_enum['move_fail'];
    $score = 0;
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
                    $done = $status_enum['move_success'];
                    for ($y2 = $y1 + 1; $y2 < 4; ++$y2) $grid[$y2 - 1][$x1] = $grid[$y2][$x1];
                    $grid[3][$x1] = 0;
                    if ($grid[$y1][$x1] == 0) --$y1;
                }
            }
        }
        for ($y1 = 0; $y1 < 3; ++$y1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1 + 1][$x1]) {
                $done = $status_enum['move_success'];
                $grid[$y1][$x1] <<= 1;
                $score += $grid[$y1][$x1];
                if ($grid[$y1][$x1] == 2048) $done = $status_enum['win'];
                for ($y2 = $y1 + 2; $y2 < 4; ++$y2) $grid[$y2 - 1][$x1] = $grid[$y2][$x1];
                $grid[3][$x1] = 0;
            }
        }
    }
    return array($done, $grid, $score);
}

function _2048_move_down($grid)
{
    GLOBAL $status_enum;
    $done = $status_enum['move_fail'];
    $score = 0;
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
                    $done = $status_enum['move_success'];
                    for ($y2 = $y1 - 1; $y2 >= 0; --$y2) $grid[$y2 + 1][$x1] = $grid[$y2][$x1];
                    $grid[0][$x1] = 0;
                    if ($grid[$y1][$x1] == 0) ++$y1;
                }
            }
        }
        for ($y1 = 3; $y1 > 0; --$y1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1 - 1][$x1]) {
                $done = $status_enum['move_success'];
                $grid[$y1][$x1] <<= 1;
                $score += $grid[$y1][$x1];
                if ($grid[$y1][$x1] == 2048) $done = $status_enum['win'];
                for ($y2 = $y1 - 2; $y2 >= 0; --$y2) $grid[$y2 + 1][$x1] = $grid[$y2][$x1];
                $grid[0][$x1] = 0;
            }
        }
    }
    return array($done, $grid, $score);
}

function _2048_move_right($grid)
{
    GLOBAL $status_enum;
    $done = $status_enum['move_fail'];
    $score = 0;
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
                    $done = $status_enum['move_success'];
                    for ($x2 = $x1 + 1; $x2 < 4; ++$x2) $grid[$y1][$x2 - 1] = $grid[$y1][$x2];
                    $grid[$y1][3] = 0;
                    if ($grid[$y1][$x1] == 0) --$x1;
                }
            }
        }
        for ($x1 = 0; $x1 < 3; ++$x1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1][$x1 + 1]) {
                $done = $status_enum['move_success'];
                $grid[$y1][$x1] <<= 1;
                $score += $grid[$y1][$x1];
                if ($grid[$y1][$x1] == 2048) $done = $status_enum['win'];
                for ($x2 = $x1 + 2; $x2 < 4; ++$x2) $grid[$y1][$x2 - 1] = $grid[$y1][$x2];
                $grid[$y1][3] = 0;
            }
        }
    }
    return array($done, $grid, $score);
}

function _2048_move_left($grid)
{
    GLOBAL $status_enum;
    $done = $status_enum['move_fail'];
    $score = 0;
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
                    $done = $status_enum['move_success'];
                    for ($x2 = $x1 - 1; $x2 >= 0; --$x2) $grid[$y1][$x2 + 1] = $grid[$y1][$x2];
                    $grid[$y1][0] = 0;
                    if ($grid[$y1][$x1] == 0) ++$x1;
                }
            }
        }
        for ($x1 = 3; $x1 > 0; --$x1) {
            if ($grid[$y1][$x1] && $grid[$y1][$x1] == $grid[$y1][$x1 - 1]) {
                $done = $status_enum['move_success'];
                $grid[$y1][$x1] <<= 1;
                $score += $grid[$y1][$x1];
                if ($grid[$y1][$x1] == 2048) $done = $status_enum['win'];
                for ($x2 = $x1 - 2; $x2 >= 0; --$x2) $grid[$y1][$x2 + 1] = $grid[$y1][$x2];
                $grid[$y1][0] = 0;
            }
        }
    }
    return array($done, $grid, $score);
}

function _2048_do_action($grid, $dir) {
    GLOBAL $direction_enum;
    GLOBAL $status_enum;
    switch ($dir) {
    case $direction_enum['up']:
        $res = _2048_move_up($grid);
        break;
    case $direction_enum['down']:
        $res = _2048_move_down($grid);
        break;
    case $direction_enum['left']:
        $res = _2048_move_left($grid);
        break;
    case $direction_enum['right']:
        $res = _2048_move_right($grid);
        break;
    }
    if ($res[0] == $status_enum['move_success']) {
        $left = _2048_search_left($res[1]);
        $grid = _2048_rand_set($left, $res[1]);
        $left = _2048_search_left($grid);
        if (count($left) || _2048_can_merge($grid)) {
            return array($status_enum['move_success'], $grid, $res[2]);
        } else {
            return array($status_enum['lose'], $grid);
        }
    }
    return $res;
}

function _2048_new_game($grid, $history) {
    $grid->remove();
    $table = array(
        'grid' => array(
            array(0, 0, 0, 0),
            array(0, 0, 0, 0),
            array(0, 0, 0, 0),
            array(0, 0, 0, 0)
        ),
        'score' => 0,
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
    $history->insert(array('id' => $table['_id'], 'type' => 'new_game', 'grid' => $table['grid'], 'time' => time()));
}

function _2048_check_action($grid, $oplog, $history) {
    GLOBAL $direction_enum;
    GLOBAL $timeout;
    GLOBAL $status_enum;
    $grid_res = $grid->findOne();
    if ($grid_res['status'] == 'waiting') {
        if (microtime(true) - $grid_res['lastmodify'] < $timeout) return; # 胜利或失败想让他看到结果
        _2048_new_game($grid, $history);
    } else {
        $res = $oplog->find(array('type' => 'move', 'time' => array('$gt' => $grid_res['lastmodify'])));
        if ($res->count()) { # 若有人做了操作
            while ($grid_res['status'] == 'updating') $grid_res = $grid->findOne();
            if (microtime(true) - $grid_res['lastmodify'] < $timeout) return; # 若还没到统计时间
            $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'updating')));
            $counts = array('up', 'down', 'left', 'right');
            $max = array('count' => 0); # array('count' => count, 'dir' => dir)
            foreach ($counts as $v) { # 统计出所选方向最多的那个方向
                $qry = array('type' => 'move', 'time' => array('$gt' => $grid_res['lastmodify']), 'dir' => $direction_enum[$v]);
                $count = $oplog->count($qry);
                if ($count > $max['count']) {
                    $max['count'] = $count;
                    $max['dir'] = $direction_enum[$v];
                }
            }
            $g = $grid_res['grid'];
            $res = _2048_do_action($g, $max['dir']);
            list($status, $g) = $res;
            switch ($status) {
            case $status_enum['move_success']:
                $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'done', 'score' => $grid_res['score'] + $res[2], 'lastmodify' => microtime(true), 'grid' => $g, 'lastaction' => $max['dir'])));
                $history->insert(array('type' => 'move', 'grid' => $g, 'time' => time(), 'dir' => $max['dir'], 'ref' => $grid_res['_id']));
                break;
            case $status_enum['move_fail']:
                $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'done', 'lastmodify' => microtime(true), 'lastaction' => $max['dir'])));
                break;
            case $status_enum['win']:
            case $status_enum['lose']:
                $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('status' => 'waiting', 'lastmodify' => microtime(true), 'grid' => $g)));
                $history->insert(array('type' => ($status == $status_enum['win'] ? 'win' : 'lose'), 'grid' => $g, 'score' => $grid_res['score'], 'time' => time(), 'ref' => $grid_res['_id']));
                break;
            }
        } else if (microtime(true) - $grid_res['lastmodify'] > $timeout) { # 统计时间内没人操作，重设lastmodify
            $grid->update(array('_id' => $grid_res['_id']), array('$set' => array('lastmodify' => microtime(true))));
        }
    }
}

function _2048_update() {
    GLOBAL $direction_enum;
    GLOBAL $timeout;
    $mongo = mongo();
    $db = $mongo->_2048;
    $grid = $db->grid;
    $oplog = $db->oplog;
    $history = $db->history;

    _2048_check_action($grid, $oplog, $history);
    do
    {
        $res = $grid->findOne();
        if ($res['status'] != 'updating') {
            unset($res['_id']);
            break;
        }
    } while (1);
    $last_action = isset($res['lastaction']) ? $res['lastaction'] : $direction_enum['up'];
    return array('stat' => 0, 'data' => array('grid' => $res['grid'], 'score' => $res['score'], 'action' => $last_action, 'left' => floor($timeout - microtime(true) + $res['lastmodify'])));
}

function _2048_history() {
    $mongo = mongo();
    $db = $mongo->_2048;
    $history = $db->history;

    $res = $history->find(array('type' => 'win', 'type' => 'lose'))->sort(array('time' => -1));
    $data = array();
    foreach ($res as $i) {
        $data[] = array('type' => $i['type'], 'score' => $i['score'], 'time' => $i['time']);
    }
    return array('stat' => 0, 'data' => $data);
}

function _2048_action() {
    $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : null;
    if ($dir === null) return array('stat' => '102', 'data' => array(), 'msg' => 'param error');
    $mongo = mongo();
    $db = $mongo->_2048;
    $oplog = $db->oplog;
    $oplog->insert(array('type' => 'move', 'time' => microtime(true), 'dir' => intval($dir)));
    return array('stat' => 0, 'data' => array());
}

