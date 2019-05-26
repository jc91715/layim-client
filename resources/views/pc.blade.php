@extends('layouts.layim')
@section('styles')
    <style>
        .members-box{
            padding: 10px;
        }
        .members{
            display: flex;
        }
        .img-box{
            width: 50px;
            height: 50px;
            border-radius: 30px;
            overflow: hidden;
        }
        .img-box img{
            width: 50px;
            height: 50px;
        }
    </style>
@endsection
@section('content')
   
    @endsection
@section('scripts')
    <script id="members" type="text/html">
        {{--//查看群员信息模版--}}
        <div class="members-box">
            <div class="members">
                @{{#  layui.each(d.data.members, function(index, item){ }}
                <div class="img-box"><img src="@{{item.headimgurl}}" alt="" onclick='alertMemberInfo(@{{JSON.stringify(item)}})'></div>
                @{{#  }); }}
            </div>
        </div>

    </script>
    <script>
        function alertMemberInfo(item) {
            console.log(item)
        }
    </script>
    <script>
        var client_id;
        // ajax({
        //     url:"", //请求地址
        //     type:'GET',   //请求方式
        //     data:{name:'zhangsan',age :'23',email:'2372734044@qq.com'}, //请求参数
        //     dataType:"json",     // 返回值类型的设定
        //     async:false,   //是否异步
        //     success:function (response,xml) {
        //         console.log(response);   //   此处执行请求成功后的代码
        //     },
        //     fail:function (status) {
        //         console.log('状态码为'+status);   // 此处为执行成功后的代码
        //     }    });
        function getParams(data) {
            var arr = [];
            for (var param in data){
                arr.push(encodeURIComponent(param) + '=' +encodeURIComponent(data[param]));
            }
            arr.push(('randomNumber=' + Math.random()).replace('.'));
            return arr.join('&');
        }
        function ajax(options) {
            /**
             * 传入方式默认为对象
             * */
            options = options || {};
            /**
             * 默认为GET请求
             * */
            options.type = (options.type || "GET").toUpperCase();
            /**
             * 返回值类型默认为json
             * */
            options.dataType = options.dataType || 'json';
            /**
             * 默认为异步请求
             * */
            options.async = options.async || true;
            /**
             * 对需要传入的参数的处理
             * */
            var params = getParams(options.data);
            var xhr;
            /**
             * 创建一个 ajax请求
             * W3C标准和IE标准
             */
            if (window.XMLHttpRequest){
                /**
                 * W3C标准
                 * */
                xhr = new XMLHttpRequest();
            }else{
                /**
                 * IE标准
                 * @type {ActiveXObject}
                 */
                xhr = new ActiveXObject('Microsoft.XMLHTTP')
            }
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4){
                    var status = xhr.status;
                    if (status >= 200 && status < 300 ){
                        options.success && options.success(xhr.responseText,xhr.responseXML);
                    }else{
                        options.fail && options.fail(status);
                    }
                }
            };
            if (options.type == 'GET'){
                xhr.open("GET",options.url + '?' + params ,options.async);
                xhr.send(null)
            }else if (options.type == 'POST'){
                /**
                 *打开请求
                 * */
                xhr.open('POST',options.url,options.async);
                /**
                 * POST请求设置请求头
                 * */
                xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                /**
                 * 发送请求参数
                 */
                xhr.send(params);
            }
        }


    </script>
    <script>
        layui.config({
            version: true
        }).use('layim', function(layim) {
            var userlist = JSON.parse('<?php echo json_encode($userlist); ?>');
            // var mobile = layui.mobile
            //     , layim = mobile.layim
            //     , layer = mobile.layer;

            layim.config({
                isgroup:true,
                copyright:true,
                isNewFriend:true,

                init:userlist,
                //上传图片接口
                uploadImage: {
                    url: '/upload/image' //（返回的数据格式见下文）
                    ,type: '' //默认post
                }

                //上传文件接口
                ,uploadFile: {
                    url: '/upload/file' //（返回的数据格式见下文）
                    ,type: '' //默认post
                },
                msgbox: layui.cache.dir + 'css/modules/layim/html/msgbox.html' //消息盒子页面地址，若不开启，剔除该项即可
                ,chatLog: layui.cache.dir + 'css/modules/layim/html/chatlog.html' //聊天记录页面地址，若不开启，剔除该项即可
            })

            layim.on('sendMessage', function(data){
                console.log(data)
                ajax({
                    url:"layim/sendMessage", //请求地址
                    type:'POST',   //请求方式
                    data:{content:JSON.stringify(data),_token:Laravel.csrfToken,client_id:client_id}, //请求参数
                    dataType:"json",     // 返回值类型的设定
                    async:true,   //是否异步
                    success:function (response,xml) {
                          //   此处执行请求成功后的代码
                    },
                    fail:function (status) {
                        console.log('状态码为'+status);   // 此处为执行成功后的代码
                    }
                });
            });
            layim.on('newFriend', function(){
                //弹出面板
                layim.panel({
                    title: '新的朋友' //标题
                    ,tpl: '<div style="padding: 10px;">自定义模版，@{{d.data.test}}</div>' //模版，基于laytpl语法
                    ,data: { //数据
                        test: '么么哒'
                    }
                });

                //也可以直接跳转页面，如：
                //location.href = './newfriend'
            });

            layim.on('detail', function(data1){
                console.log(data1); //获取当前会话对象，包含了很多所需要的信息
                //以查看群组信息（如成员）为例
                ajax({
                    url:"groups/"+data1.id+"/members", //请求地址
                    type:'GET',   //请求方式
                    data:{}, //请求参数
                    dataType:"json",     // 返回值类型的设定
                    async:true,   //是否异步
                    success:function (response,xml) {
                        var data = JSON.parse(response)
                        layim.panel({
                            title: data1.name + '群员信息' //标题
                            ,tpl: members.innerHTML //模版，基于laytpl语法
                            ,data: { //数据
                                members: data.data.members //假设rows为群组成员
                            }
                        });
                    },
                    fail:function (status) {
                        console.log('状态码为'+status);   // 此处为执行成功后的代码
                    }
                });



            });
            // layim.on('chatChange', function(res){
            //     console.log(res)
            //     var type = res.data.type;
            //     if(type === 'friend'){
            //         layim.setChatStatus('在线'); //模拟标注好友在线状态
            //     }
            // });

            var socket = new WebSocket('ws://192.168.10.10:8282');
            socket.onopen = function(){
                // socket.send('XXX连接成功');
                console.log('连接成功')
            };

            //监听收到的消息
            socket.onmessage = function(res){
                console.log('收到信息')
                console.log(res)

                var data = JSON.parse(res.data)
                switch (data.type) {
                    case 'login':
                        client_id = data.client_id
                        setInterval(function(){
                            socket.send('心跳')
                        },5000*10)
                        ajax({
                            url:"user/bind", //请求地址
                            type:'POST',   //请求方式
                            data:{client_id:data.client_id,_token:Laravel.csrfToken}, //请求参数
                            dataType:"json",     // 返回值类型的设定
                            async:true,   //是否异步
                            success:function (response,xml) {
                                  //   此处执行请求成功后的代码
                            },
                            fail:function (status) {
                                console.log('状态码为'+status);   // 此处为执行成功后的代码
                            }
                        });
                        break;
                    case 'getMessage':
                        layim.getMessage(data['content'])
                        break;
                }

                //res为接受到的值，如 {"emit": "messageName", "data": {}}
                //emit即为发出的事件名，用于区分不同的消息
            };



        })
    </script>
@endsection
