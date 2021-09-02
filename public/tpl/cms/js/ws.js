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
        //请求加载历史评论
        $.post(cms.root + "index.php?s=home-cm-getChat", {
            vod_id: cms.id,
        }, data => {
            chatData = data
            chatRender(chatData)
        });
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
        }
    }
}
socket.init()
var chatData = []
//所有用户进入房间 滚动提示 房间在线人数
// 用户从该组离线后 考虑是否发功一个新的在线人数--ing
Event.on('vodRoomJoinTip', msg => {
    $('#vodRoomTip').append(msg.msgContent);
    $('#onLineNumber').html(msg.onLineNumber);
})
//房间用户的进入状态
Event.on('notice', msg => {
    $('#vodRoomJoinStatus').html(msg.nickname + msg.msgContent);
    if (msg.status == 1) {
        $('#vodRoomJoin').hide()
    }
})
//房间在线人数
Event.on('onLineNumber', msg => {
    $('#onLineNumber').html(msg.onLineNumber);
})
//房间聊天内容
Event.on('vodRoomChat', msg => {
    //放进库中
    $.post(cms.root + "index.php?s=home-cm-addChat", {
            'msgContent':msg.msgContent,
            'nickname':msg.nickname,
            'time':msg.time,
            'uid':msg.uid,
            'videoId': cms.id,
            'token' : getCookie('i7_token')
    });
    chatData.push(msg)
    chatRender(chatData)
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


function timestampToTime(timestamp) {
    var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
    var Y = date.getFullYear() + '-';
    var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    var D = date.getDate() + ' ';
    var h = date.getHours() + ':';
    var m = date.getMinutes() + ':';
    var s = date.getSeconds();
    return Y+M+D+h+m+s;
}

function chatRender(data) {
    var noticeEle = document.getElementById('notice');
    noticeEle.innerHTML = '';
    data.forEach((item, index) => {
        item.reverse = item.uid == cms.uid ?'reverse':''
        var msgTime = timestampToTime(item.time);
        noticeEle.innerHTML +=  `<div class="message-wrapper ${item.reverse?'reverse':''}">
            <img class="message-pp" src="${item.avatar}" alt="profile-pic">
                <div class="message-box-wrapper">
                    <div class="message-box">
                        ${item.msgContent}
                    </div>
                    <span>${msgTime}</span>
                </div>
        </div>  `;
        noticeEle.scrollTop = noticeEle.scrollHeight; //当前div的滚轮始终保持最下面
    })
}