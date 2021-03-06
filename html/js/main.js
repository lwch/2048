(function () {

/* config */
var debug = 0;
var server_address = "http://2048.q-devel.com/api/";
//var server_address = "http://127.0.0.1/api/";
//var server_address = "http://192.168.66.64/api/";

/* ajax */
var ajax = {
    request: function(req) {
        if (req.api_name == undefined || req.api_name == null) return;

        var xmlhttp;
        if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
        else xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");

        var url = server_address + "?api_name=" + req.api_name + "&c=" + Math.random();
        if (req.params != undefined && req.params != null) {
            for (var key in req.params) {
                url += "&" + key + "=" + req.params[key];
            }
        }

        if (req.callback != undefined && req.callback != null) {
            if (req.error_callback != undefined && req.error_callback != null) {
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) req.callback(eval("(" + xmlhttp.responseText + ")"));
                    else req.error_callback(xmlhttp);
                };
            } else {
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) req.callback(eval("(" + xmlhttp.responseText + ")"));
                }
            }
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        } else {
            xmlhttp.open("GET", url, false);
            xmlhttp.send();
            return eval("(" + xmlhttp.responseText + ")");
        }
    }
};

/* grid */
var grid = {
    map: [
        [0, 0, 0, 0],
        [0, 0, 0, 0],
        [0, 0, 0, 0],
        [0, 0, 0, 0]
    ],
    init: function() {
        this.map = [
            [0, 0, 0, 0],
            [0, 0, 0, 0],
            [0, 0, 0, 0],
            [0, 0, 0, 0]
        ];
    },
    debug: function() {
        console.log(this.map[0][0], this.map[0][1], this.map[0][1], this.map[0][2]);
        console.log(this.map[1][0], this.map[1][1], this.map[1][1], this.map[1][2]);
        console.log(this.map[2][0], this.map[2][1], this.map[2][1], this.map[2][2]);
        console.log(this.map[3][0], this.map[3][1], this.map[3][1], this.map[3][2]);
        console.log('');
    }
};

/* service */
var service = {
    updateTimmerId: undefined,
    leftTimmerId: undefined,
    historyTimmerId: undefined,
    leftTime: 0,
    updateLeft: function() {
        var left_time = document.getElementById("left-time");
        left_time.innerText = service.leftTime + "s";
    },
    updateHistory: function() {
        Date.prototype.format = function(format) {
            var date = {
                "M+": this.getMonth() + 1,
                "d+": this.getDate(),
                "h+": this.getHours(),
                "m+": this.getMinutes(),
                "s+": this.getSeconds(),
                "q+": Math.floor((this.getMonth() + 3) / 3),
                "S+": this.getMilliseconds()
            };
            if (/(y+)/i.test(format)) {
                format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
            }
            for (var k in date) {
                if (new RegExp("(" + k + ")").test(format)) {
                    format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
                }
            }
            return format;
        }
        ajax.request({
            api_name: "2048.history",
            callback: function(res) {
                if (res.stat == 0) {
                    var scores_frame = document.getElementById("scores-frame");
                    var childs = scores_frame.childNodes;
                    for (var i = childs.length - 1; i >= 0; --i) {
                        scores_frame.removeChild(childs[i]);
                    }

                    for (var i = 0; i < res.data.length; ++i) {
                        var li = document.createElement("li");
                        var pre = res.data[i];
                        var time = new Date(pre.time * 1000);
                        if (pre.type == "win") li.className = "win";
                        else li.className = "lose";

                        var div_left = document.createElement("div");
                        div_left.className = "left";
                        div_left.innerText = pre.score;
                        li.appendChild(div_left);

                        var div_right = document.createElement("div");
                        div_right.className = "right";
                        div_right.innerText = time.format("yyyy-MM-dd hh:mm:ss");
                        li.appendChild(div_right);

                        scores_frame.appendChild(li);
                    }
                }
            }
        });
    },
    update: function() {
        ajax.request({
            api_name: "2048.update",
            callback: function(res) {
                if (res.stat == 0) {
                    service.leftTime = res.data.left;
                    var score = document.getElementById("score");
                    score.innerText = res.data.score;
                    var last_poll_action = document.getElementById("last-poll-action");
                    switch (res.data.action) {
                    case direction_enum.up:
                        last_poll_action.innerHTML = "&uarr;";
                        break;
                    case direction_enum.down:
                        last_poll_action.innerHTML = "&darr;";
                        break;
                    case direction_enum.left:
                        last_poll_action.innerHTML = "&rarr;";
                        break;
                    case direction_enum.right:
                        last_poll_action.innerHTML = "&larr;";
                        break;
                    }
                    grid.map = res.data.grid;
                }
                service.emit();
            }
        });
    },
    emit: function() {
        /* clear */
        var tile_frame = document.getElementById("tile-frame");
        var childs = tile_frame.childNodes;
        for (var i = childs.length - 1; i >= 0; --i) {
            tile_frame.removeChild(childs[i]);
        }

        /* show */
        for (var y = 0; y < 4; ++y) {
            for (var x = 0; x < 4; ++x) {
                var val = grid.map[y][x];
                if (val) {
                    var node = document.createElement("div");
                    var tile_val = val > 2048 ? "tile-super" : "tile-" + val;
                    node.className = "tile " + tile_val + " tile-position-" + (y + 1) + "-" + (x + 1);
                    node.innerText = val;
                    tile_frame.appendChild(node);
                }
            }
        }
        /*if (debug) {
            var remove = Math.random() * 10;
            for (var i = 0; i < remove; ++i) {
                var x = parseInt((Math.random() * 10) % 4);
                var y = parseInt((Math.random() * 10) % 4);
                grid.map[y][x] = 0;
            }
            var append = Math.random() * 10;
            for (var i = 0; i < append; ++i) {
                var x = parseInt(Math.random() * 10) % 4;
                var y = parseInt(Math.random() * 10) % 4;
                var val = Math.pow(2, parseInt((Math.random() * 100) % 16) + 1);
                grid.map[y][x] = val;
            }
            //window.clearInterval(this.updateTimmerId);
        }*/
    }
};

/* input_manager */
var direction_enum = {
    up: 0,
    down: 1,
    left: 2,
    right: 3
};

var input_manager = {
    touch_begin: {
        x: 0,
        y: 0
    },
    touch_end: {
        x: 0,
        y: 0
    },
    char_map: {
        38: direction_enum.up,
        40: direction_enum.down,
        37: direction_enum.right,
        39: direction_enum.left,
        87: direction_enum.up,   // W
        83: direction_enum.down, // S
        65: direction_enum.left, // A
        68: direction_enum.righ, // D
        72: 999                  // for debug
    },
    key_down: function(event) {
        //console.log(event.which);
        var modifiers = event.altKey || event.ctrlKey || event.metaKey || event.shiftKey;
        var which = input_manager.char_map[event.which];
        if (!modifiers && which !== undefined) {
            event.preventDefault();
            if (debug && which == 999) grid.debug();
            else input_manager.emit(which);
        }
    },
    touch_down: function(event) {
        event.preventDefault();
        if (!event.touches.length) return;
        var touch = event.touches[0];
        input_manager.touch_begin.x = touch.pageX;
        input_manager.touch_begin.y = touch.pageY;
    },
    touch_move: function(event) {
        event.preventDefault();
        if (!event.touches.length) return;
        var touch = event.touches[0];
        input_manager.touch_end.x = touch.pageX;
        input_manager.touch_end.y = touch.pageY;
    },
    touch_up: function(event) {
        var angle = Math.atan2(input_manager.touch_begin.y - input_manager.touch_end.y, input_manager.touch_end.x - input_manager.touch_begin.x);
        var which = direction_enum.up;
        if (Math.abs(angle) <= Math.PI / 4) which = direction_enum.left;
        else if (Math.abs(angle) <= Math.PI * 3 / 4) {
            if (angle > 0) which = direction_enum.up;
            else which = direction_enum.down;
        } else which = direction_enum.right;
        input_manager.emit(which);
    },
    emit: function(dir) {
        var last_action = document.getElementById("last-action");
        switch (dir) {
        case direction_enum.up:
            last_action.innerHTML = "&uarr;";
            break;
        case direction_enum.down:
            last_action.innerHTML = "&darr;";
            break;
        case direction_enum.left:
            last_action.innerHTML = "&rarr;";
            break;
        case direction_enum.right:
            last_action.innerHTML = "&larr;";
            break;
        }
        ajax.request({
            api_name: "2048.action",
            params: {
                dir: dir
            },
            callback: function(res) {}
        });
    }
};

window.onload = function() {
    document.addEventListener("keydown", input_manager.key_down);

    var gamer = document.getElementById("gamer");
    gamer.addEventListener("touchstart", input_manager.touch_down);
    gamer.addEventListener("touchmove", input_manager.touch_move);
    gamer.addEventListener("touchend", input_manager.touch_up);

    service.updateTimmerId = window.setInterval(service.update, 500);
    service.leftTimmerId = window.setInterval(service.updateLeft, 100);
    service.historyTimmerId = window.setInterval(service.updateHistory, 1000);
}
})();

