// 定义一个订阅发布
var Event = {
    EVENT: {},
    emit(eventName, resp) {
        if (this.EVENT[eventName] && Object.prototype.toString.call(this.EVENT[eventName]) === "[object Array]") {
            for (let i = 0, fn; fn = this.EVENT[eventName][i++];) {
                fn(resp)
            }
        }
    },
    on(name, fn) {
        if (this.EVENT[name] && Object.prototype.toString.call(this.EVENT[name]) === "[object Array]") {
            this.EVENT[name].push(fn)
        } else {
            this.EVENT[name] = [fn]
        }
    },
    off(name) {
        this.EVENT[name] = null
    }
}
// Websocket方法
var socket = {
    socket: null,
    token: getCookie('i7_token'),
    init() {
        this.socket = new WebSocket("ws://127.0.0.1:8282");
        this.socket.onopen = () => {
            this.heartCheck();
        }
        this.socket.onmessage = evt => {
            this.message(evt)
        }
        this.socket.onclose = () => {
            this.close()
        }
        this.socket.onerror = err => {
            this.error(err)
        }
    },
    heartCheck() {
        setInterval(function () {
            socket.socket.send("{'type':'ping'}");
        }, 3000);
    },
    error(err) {
        console.log(err)
    },
    close() {
        // 重新连接
        // this.init()
    },
    message(evt) {
        var msg = JSON.parse(evt.data);
        switch (msg.type) {
            case 'init':
                // 发送绑定信息
                var bind = {
                    "type": "init",
                    "msg": {
                        "token": this.token
                    }
                }
                this.socket.send(JSON.stringify(bind));
                return;
            // 服务端ping客户端
            case 'ping':
                break;
            case 'vodRoomJoinTip':
                // 通知进入退出和系统消息
                Event.emit('vodRoomJoinTip', msg)
                return;
            case 'notice':
                Event.emit('notice', msg)
                return;
            case 'vodRoomJoinStatus':
                Event.emit('vodRoomJoinStatus', msg)
                return;
            case 'vodRoomChat': // 影片评论和在线聊天
                Event.emit('vodRoomChat', msg)
                return;
            case 'addCm':
                Event.emit('addCm', msg)
                return;
        }
    }
}
socket.init()
//所有用户进入房间 滚动提示 房间在线人数
// 用户从该组离线后 考虑是否发功一个新的在线人数--ing
Event.on('vodRoomJoinTip', msg => {
    $('#vodRoomTip').append(msg.msgContent);
    $('#onLineNumber').html('(' + msg.onLineNumber + '人)');
})
//房间用户的进入状态
Event.on('vodRoomJoinStatus', msg => {
    $('#vodRoomJoinStatus').html(msg.msgContent);
    if (msg.status == 1) {
        $('#vodRoomJoin').hide()
    }
})
//房间聊天内容
Event.on('vodRoomChat', msg => {
    if (msg.uid == cms.uid) {
        var reverse = 'reverse'
    } else {
        reverse = ''
    }
    var msgTime = getNowFormatDate(msg.time)
    var newMsg = `
        <div class="message-wrapper ${reverse}">
            <img class="message-pp" src="${msg.avatar}" alt="profile-pic">
            ${msg.nickname}
                <div class="message-box-wrapper">
                    <div class="message-box">
                        ${msg.msgContent}
                    </div>
                    <span>${msgTime}</span>
                </div>
        </div>
    `;
    // 监听聊天滚动的位置
    var noticeEle = document.getElementById('notice');
    noticeEle.innerHTML = noticeEle.innerHTML + newMsg;
    noticeEle.scrollTop = noticeEle.scrollHeight; //当前div的滚轮始终保持最下面
})

$('document').ready(function () {
    // 自动进入房间
    var data = {
        "type": "vodRoomJoin",
        "msg": {
            'model': cms.model,
            "controller": cms.controller,
            "action": cms.action,
            "videoId": cms.id,
        }
    };
    socket.socket.send(JSON.stringify(data));
    var sendMsgEle = $("#sendMsgWrapper");
    if (!xianyu.user.islogin()) {
        sendMsgEle.hide();
    }
    $("#sendMsg").click(function () {
        var msgContent = $(".msgContent")
        if (!xianyu.user.islogin()) {
            xianyu.user.loginform();
            return false;
        }
        if (!msgContent.val()) {
            return
        }
        var data = {
            "type": "vodRoomChat",
            "msg": {
                "msgContent": msgContent.val(),
            }
        };
        socket.socket.send(JSON.stringify(data));
        msgContent.val("");
    });


/*    function addChat(data) {
        console.log(data);
        $.post(cms.root + "index.php?s=home-cm-addChat", {"data": data});
    }*/
});

/*
保存cookies
 */
function SetCookie(name, value) {
    var Days = 30 * 12;  //cookie 将被保存一年
    var exp = new Date(); //获得当前时间
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000); //换成毫秒
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

/*
获取cookies
*/
function getCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) {
        return unescape(arr[2]);
    } else {
        return null;
    }
}

/*
删除cookie
 */
function delCookie(name) {
    var exp = new Date(); //当前时间
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}

Date.prototype.Format = function (fmt) { //author: meizz
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "H+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};

function getNowFormatDate() {
    return new Date().Format("yyyy-MM-dd HH:mm:ss");
}