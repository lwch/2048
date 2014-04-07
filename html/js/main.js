(function () {

/* config */
var debug = 0;
var server_address = "http://2048.q-devel.com/api/";

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
    leftTime: 0,
    updateLeft: function() {
        var left_time = document.getElementById("left-time");
        left_time.innerText = service.leftTime + "s";
    },
    update: function() {
        ajax.request({
            api_name: "2048.update",
            callback: function(res) {
                if (res.stat == 0) {
                    service.leftTime = res.data.left;
                    var last_poll_action = document.getElementById("last-poll-action");
                    switch (res.data.action) {
                    case direction_enum.up:
                        last_poll_action.innerHTML = "&uarr;";
                        break;
                    case direction_enum.down:
                        last_poll_action.innerHTML = "&darr;";
                        break;
                    case direction_enum.left:
                        last_poll_action.innerHTML = "&larr;";
                        break;
                    case direction_enum.right:
                        last_poll_action.innerHTML = "&rarr;";
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
    char_map: {
        38: direction_enum.up,
        40: direction_enum.down,
        37: direction_enum.left,
        39: direction_enum.right,
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
            if (debug && which == 999) grid.debug();
            else input_manager.emit(which);
        }
    },
    emit: function(dir) {
        var last_action = document.getElementById("last-action");
        switch (dir) {
        case direction_enum.up:
            var tmp = "↑";
            last_action.innerHTML = "&uarr;";
            break;
        case direction_enum.down:
            last_action.innerHTML = "&darr;";
            break;
        case direction_enum.left:
            last_action.innerHTML = "&larr;";
            break;
        case direction_enum.right:
            last_action.innerHTML = "&rarr;";
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
    service.updateTimmerId = window.setInterval(service.update, 500);
    service.leftTimmerId = window.setInterval(service.updateLeft, 100);
}
})();

