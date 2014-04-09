# 2048Online

## 数据模型

1. grid

  grid表用于记录当前棋局。

        grid : {
            grid : [
                [x, x, x, x],
                [x, x, x, x],
                [x, x, x, x],
                [x, x, x, x]
            ],
            status : 当前状态,（'done'处理完毕，'waiting'本局完毕等待下局开始，'updating'正则计算中）
            lastmodify : 最后更新时间（精确到微秒）
        }

2. oplog

  oplog表用于记录用户执行的操作。

        type : {
            type : 操作类型,（'move'移动）
            time : 执行操作的时间,（精确到微秒）
            dir : 移动的方向（1为向上，2为向下，3为向左，4为向右）
        }

3. history

  history表用于记录系统所做的操作。

        history : {
            type : 操作类型,（'new_game'，'move'，'win'，'lose'）
            grid : [
                [x, x, x, x],
                [x, x, x, x],
                [x, x, x, x],
                [x, x, x, x]
            ],
            time : 执行操作的时间,（精确到秒）
            dir : 同oplog中的dir,（仅当type为'move'时有）
            ref : 指向history表中某次new_game的_id（仅当type为'move'、'win'和'lose'时有）
        }

