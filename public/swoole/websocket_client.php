<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>菜鸟教程(runoob.com)</title>
        <script>
            //var wsServer = 'ws://192.168.152.128:9502';
//            var wsServer = 'ws://192.168.152.128:82/ws';
//            var websocket = new WebSocket(wsServer);
//
//            //心跳检测
//            var heartCheck = {
//                timeout: 3000,
//                timeoutObj: null,
//                serverTimeoutObj: null,
//                start: function () {
//                    console.log('start');
//                    var self = this;
//                    this.timeoutObj && clearTimeout(this.timeoutObj);
//                    this.serverTimeoutObj && clearTimeout(this.serverTimeoutObj);
//                    this.timeoutObj = setTimeout(function () {
//                        //这里发送一个心跳，后端收到后，返回一个心跳消息，
//                        console.log('55555');
//                        ws.send("123456789");
//                        self.serverTimeoutObj = setTimeout(function () {
//                            console.log(111);
//                            console.log(ws);
//                            ws.close();
//                            // createWebSocket();
//                        }, self.timeout);
//
//                    }, this.timeout)
//                }
//            }
//
//            websocket.onopen = function (evt) {
//                console.log("Connected to WebSocket server.");
//                websocket.send('898989');
//            };
//
//            websocket.onclose = function (evt) {
//                console.log("Disconnected");
//            };
//
//            websocket.onmessage = function (evt) {
//                console.log('Retrieved data from server: ' + evt.data);
//            };
//
//            websocket.onerror = function (evt, e) {
//                console.log('Error occured: ' + evt.data);
//            };


            var lockReconnect = false;//避免重复连接
            var wsUrl = "ws://192.168.152.128:82/ws";
            var ws;
            var tt;
            function createWebSocket() {
                try {
                    ws = new WebSocket(wsUrl);
                    init();
                } catch (e) {
                    console.log('catch');
                    reconnect(wsUrl);
                }
            }
            function init() {
                ws.onclose = function () {
                    console.log('链接关闭');
                    //console.log("Disconnected");
                    reconnect(wsUrl);
                };
                ws.onerror = function (evt, e) {
                    console.log('发生异常了');
                    console.log('Error occured: ' + evt.data);
                    reconnect(wsUrl);
                };
                ws.onopen = function () {
                    console.log("Connected to WebSocket server.");
                    
                    ws.send("token:89999");//发送当前用户的token
                    
                    //心跳检测重置
                    heartCheck.start();
                };
                ws.onmessage = function (evt) {
                    console.log('Retrieved data from server: ' + evt.data);
                    //拿到任何消息都说明当前连接是正常的
                    console.log('接收到消息');
                    heartCheck.start();
                }
            }
            function reconnect(url) {
                if (lockReconnect) {
                    return;
                }

                lockReconnect = true;
                //没连接上会一直重连，设置延迟避免请求过多
                tt && clearTimeout(tt);
                tt = setTimeout(function () {
                    createWebSocket(url);
                    lockReconnect = false;
                }, 40000);
            }
            //心跳检测
            var heartCheck = {
                timeout: 30000,
                timeoutObj: null,
                serverTimeoutObj: null,
                start: function () {
                    console.log('====heartCheck');
                    var self = this;
                    this.timeoutObj && clearTimeout(this.timeoutObj);
                    this.serverTimeoutObj && clearTimeout(this.serverTimeoutObj);
                    this.timeoutObj = setTimeout(function () {
                        //这里发送一个心跳，后端收到后，返回一个心跳消息，
                        console.log('====heartCheck===send');
                        ws.send("1");
                        self.serverTimeoutObj = setTimeout(function () {
                            console.log(111);
                            console.log(ws);
                            ws.close();
                            // createWebSocket();
                        }, self.timeout);

                    }, this.timeout)
                }
            }
            createWebSocket(wsUrl);
        </script>
    </head>
    <body>

        <h1>我的第一个 JavaScript 程序</h1>
        <p id="demo">这是一个段落</p>

    </body>
</html>

