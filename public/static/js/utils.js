 var y_jquery;typeof $ == 'undefined' ?y_jquery='':y_jquery=$;
 var y_layer;typeof layer == undefined || typeof layer == 'undefined'?y_layer = '':y_layer=layer;

var y_utils = (function(y_jquery,y_layer){
    //把两个js库转换为熟悉的变量名
    var $ = y_jquery;  var layer = y_layer;
    //创建数组 异步请求, 每个页面最多只能30个请求
    var ajax_queue = [];

    var a = {
        // test:function(id,data){
        //     ajax_queue.push(id,data);
        // },
        // consoleMsg:function(){
        //     console.log(ajax_queue);
        // },
        // getQueue:function(id){
        //     var a = ajax_queue.findAjaxById(id);
        //     console.log(a);
        // },




        /**
         * 队列
         * @param int  n   队列元素个数
         * @return array   队列对象
         */
        y_queue:function(n){

            return {
                MaxSize:n+1,
                table:new Array(n),
                first:0,last:0,
                isFull:function(){
                    return (this.last+1)%this.MaxSize == this.first;
                },
                isEmpty:function(){
                    return this.last == this.first;
                },
                add:function(id,data){
                    if(this.isFull()){
                        console.log('队列已满');
                        return false;
                    }
                    this.last = (this.last+1)%this.MaxSize;
                    this.table[this.last] = {id:id,data:data};
                    return true;
                },
                del:function(){
                    if(this.isEmpty()){
                        return false;
                    }
                    this.first = (this.first+1)%this.MaxSize;
                    return true;
                },
                front:function(id){
                    return this.table[(this.first+1)%this.MaxSize];
                },
                rear:function(){
                    return this.table[this.last];
                },

            };
        },


        /*
        * 对话框显示
        *
        * */
        msg:function(msg,param){
            if(layer){
                layer.alert(msg);
            }else{
                alert(msg);
            }
        },


        successMsg:function(msg,cb){
            var cb= this.isUndefined(cb)?function(){}:cb;
            if(layer){
                layer.alert(msg,function(index){
                    cb(); layer.close(index);
                });
            }else{
                alert(msg);cb();
            }
        },
        errorMsg:function(msg,cb){
            var cb= this.isUndefined(cb)?function(){}:cb;
            if(layer){
                layer.alert( msg,function(index){
                    cb();
                    layer.close(index);
                });
            }else{
                alert(msg);cb();
            }
        },



        /*
        * 对ajax封装，简化一点步骤
        *  @param array {url:'',param:{},type:'GET',dataType:'json',beforeSend:callback,syn:1,success:callback(data),error:callback(XMLHttpRequest, textStatus, errorThrown)}
        *
        * */

        request_url:function(conf){

            var that = this;
            /**
             * 查找ajax请求集合中指定的请求
             * @param string id 每个ajax请求的唯一id
             * */
            var findAjaxById = function(id){

                var len = ajax_queue.length;
                if(that.isUndefined(ajax_queue[0])){
                    return null;
                }
                for (var i = 0;i<len;i++){
                    if(ajax_queue[i].id == id){
                        return ajax_queue[i];
                    }
                }

                return null;
            };

            /**
             * 修改某个ajax请求的锁定状态
             * @param string id 每个ajax请求的唯一id
             *
             * */
            var editAjaxById = function(id,data){
                var len = ajax_queue.length;
                for (var i = 0;i<len;i++){
                    if(ajax_queue[i].id == id){
                         ajax_queue[i].data = data;
                         return true;
                    }
                }
                return false;
            };

            /**
             * 增加一个ajax请求到ajax集合
             * @param array  {id:'',data:''} id每个ajax请求的唯一id，data两个状态 0,1
             *
             */
            var addAjaxById = function (param){
                var len = ajax_queue.length;
                if(len>30){
                    console.log('link is full');
                    return false;
                }

                ajax_queue.push(param);return true;

            };

            that.isUndefined(conf) || conf.length<0 ? conf = {
                param:{},
            }:'';
            if(that.isUndefined(conf.url) || conf.url == ""){
                console.log("link is empty");return false;
            }

            conf.syn = that.isUndefined(conf.syn)||conf.syn==""?1:conf.syn;

            //是否开启锁定请求
            if(conf.syn == 1){
                var dat =  findAjaxById(conf.url);

                if(!dat){
                    addAjaxById({id:conf.url,data:1});
                }

                dat =  findAjaxById(conf.url);

                if(dat.data==1){
                    editAjaxById(conf.url,0);
                }else{
                    return false;
                }
            }
			
            //验证数据正确性
            var newParam = {
                type : that.isUndefined(conf.type)||conf.type==""?'POST':conf.type,
                dataType : (that.isUndefined(conf.dataType)) || conf.dataType == ""?'json':conf.dataType,
                beforeSend : (that.isUndefined(conf.beforeSend))?'':conf.beforeSend,
                success : (that.isUndefined(conf.success))?'':conf.success,
                error : (that.isUndefined(conf.error))?'':conf.error,
                url : conf.url,
                param : conf.param
            };

            //是jquery ajax
            if($){
                $.ajax({
                    url:newParam.url ,
                    data: newParam.param,
                    type:newParam.type,
                    dataType:newParam.dataType,
                    beforeSend:function(){
                        newParam.beforeSend?newParam.beforeSend():'';
                    },
                    success:function(data){
                        conf.syn == 1? editAjaxById(conf.url,1):'';
                        newParam.success?newParam.success(data):'';

                    },
                    error:function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        conf.syn == 1?editAjaxById(conf.url,1):'';
                        if(!newParam.error){
                            y_utils.msg('网络请求错误');
                        }else{
                            newParam.error(XMLHttpRequest, textStatus, errorThrown);
                        }

                        // alert(XMLHttpRequest.status);
                        // alert(XMLHttpRequest.readyState);
                        // alert(textStatus);
                    }
                });
            }
            //使用原生ajax
            else{
                //根据提交数据类型选择提交内容类型
                var contentType = 'application/x-www-from-urlcoded';
                if(newParam.dataType.toLowerCase() == 'json'){
                    //如果是POST方式，就要转为json
                    contentType = 'aapplication/json';
                }
                var paramString = '';
                //GET提交
                if(newParam.type == 'GET'){
                    for(var key in newParam.param){
                        paramString += key + '=' + newParam.param[key]+'&';
                    }
                    paramString = paramString.slice(0,-1);
                    if(newParam.url.search(/\?/i)<0){
                        newParam.url += '?' + paramString;
                    }else{
                        newParam.url += '&' + paramString;
                    }
                }
                //post提交
                else{
                    paramString =  JSON.stringify(newParam.param);
                }

                var http=new XMLHttpRequest();
                http.open(newParam.type,newParam.url,true);
                http.setRequestHeader('Content-Type',contentType);
				
				if(newParam.beforeSend){
					newParam.beforeSend();
				}
                //数据请求
                http.send(newParam.type == 'POST'?paramString:null);

                //数据返回
                http.onreadystatechange=function(){
                    if(http.readyState === 4) {
                        //加锁
                        conf.syn == 1?editAjaxById(conf.url,1):'';
                        //请求成功
                        if(http.status === 200){
                            var dat = {};
                            if(that.IsJsonString(http.responseText,function(key,value){
                                if(key){dat[key] = value};
                            })){
                                //定义了success
                                newParam.success?newParam.success(dat):'';
                            }else{
                                //没有定义success
                                newParam.success?newParam.success(http.responseText):'';
                            }
                        }else{
                            //请求失败
                            if(!newParam.error){
                               y_utils.msg('网络请求错误');
                            }else{
                                //定义了error
                                newParam.error(http, http.status );
                            }
                        }
                    }
                }
            }
        },

        /**
         * 判断是否为JSON字符串
         * @param str string 字符串
         * @param obj function 用来转换传入的字符串
         */
        IsJsonString:function(str,obj) {
            try {
                JSON.parse(str,obj);
            } catch(e) {
                return false;
            }
            return true;
         },



        /**
         * 验证数据是否undefined
         * @param data
         *
         * */
        isUndefined:function(data){
            return (typeof data == undefined||typeof data == 'undefined');
        },

        /**
         * 是否在指定数组中
         * @param elem
         * @param arr
         * @return {*}
         */
        inArray:function(elem,arr){
            if($){
               return  $.inArray(elem,arr)>-1?true:false;
            }
            for (var key in arr){
                if(elem == arr[key]){
                    return true;
                }
            }
            return false;
        },

        /**
         *
         * 删除数组中的一个元素
         * @param val
         */
        removeArray : function(val,arr) {
            var index = arr.indexOf(val);
            if (index > -1) {
                arr.splice(index, 1);
            }

            return arr
        }

    };

    return a;
}(y_jquery,y_layer));

