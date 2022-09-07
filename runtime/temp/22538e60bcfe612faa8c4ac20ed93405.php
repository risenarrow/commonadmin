<?php /*a:2:{s:69:"K:\CODE\php\site\commonadmin\application\admin\view\module\index.html";i:1654065205;s:70:"K:\CODE\php\site\commonadmin\application\admin\view\public\layout.html";i:1654065083;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>commonAdmin后台</title>
    <link rel="stylesheet" href="/static/admin/layui/css/layui.css">
    <link rel="stylesheet" href="/static/admin/css/style.css">
    <script src="/static/admin/js/jquery/jquery.min.js"></script>
    <script src="/static/admin/layui/layui.js"></script>
    <script src="/static/admin/js/utils.js" type="text/javascript"></script>
    
    
</head>
<body>
<div class="layui-layout layui-layout-admin">
    
    <div class="layui-header">
        <div class="layui-logo layui-hide-xs layui-bg-black">commonAdmin后台</div>
        <!-- 头部区域（可配合layui 已有的水平导航） -->
        <ul class="layui-nav layui-layout-left">
            <!-- 移动端显示 -->
            <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-header-event="menuLeft">
                <i class="layui-icon layui-icon-spread-left"></i>
            </li>
            <?php if(is_array($topNav) || $topNav instanceof \think\Collection || $topNav instanceof \think\Paginator): if( count($topNav)==0 ) : echo "" ;else: foreach($topNav as $topNav_k=>$item): if($topNav_k < 5): ?>
            <li class="layui-nav-item layui-hide-xs <?php if($topid == $item['id']): ?>layui-this<?php endif; ?>"><a href="<?php echo htmlentities($item['url']); ?>"><?php echo htmlentities($item['name']); ?></a></li>
            <?php endif; if($topNav_k == 5): ?>
            <li class="layui-nav-item">
                <a href="javascript:;">更多</a>
                <dl class="layui-nav-child">
            <?php endif; if($topNav_k >= 5): ?>
                    <dd><a href="<?php echo htmlentities($item['url']); ?>"><?php echo htmlentities($item['name']); ?></a></dd>
                    <?php endif; if($topNav_k == count($topNav)): ?>
                </dl>
            </li>
            <?php endif; ?>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <li class="layui-nav-item layui-hide-xs"><a href="<?php echo url('admin/publics/logout'); ?>">退出</a></li>
        </ul>
        <!--<ul class="layui-nav layui-layout-right">-->
            <!--<li class="layui-nav-item layui-hide layui-show-md-inline-block">-->
                <!--<a href="javascript:;">-->
                    <!--<img src="//tva1.sinaimg.cn/crop.0.0.118.118.180/5db11ff4gw1e77d3nqrv8j203b03cweg.jpg" class="layui-nav-img">-->
                    <!--tester-->
                <!--</a>-->
                <!--<dl class="layui-nav-child">-->
                    <!--<dd><a href="">Your Profile</a></dd>-->
                    <!--<dd><a href="">Settings</a></dd>-->
                    <!--<dd><a href="">Sign out</a></dd>-->
                <!--</dl>-->
            <!--</li>-->
            <!--<li class="layui-nav-item" lay-header-event="menuRight" lay-unselect>-->
                <!--<a href="javascript:;">-->
                    <!--<i class="layui-icon layui-icon-more-vertical"></i>-->
                <!--</a>-->
            <!--</li>-->
        <!--</ul>-->
    </div>
    



    
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="layui-nav layui-nav-tree" lay-filter="test">
                <li class="layui-nav-item <?php if($curController == 'index' and $curAction == 'index'): ?>layui-this<?php endif; ?>">
                    <a href="<?php echo admin_url('admin/index/index'); ?>">首页</a>
                </li>
                <?php if(is_array($leftNav) || $leftNav instanceof \think\Collection || $leftNav instanceof \think\Paginator): if( count($leftNav)==0 ) : echo "" ;else: foreach($leftNav as $key=>$item): ?>
                <li class="layui-nav-item layui-nav-itemed">
                    <a class="<?php if($leftid == $item['id'] && !isset($item['child'])): ?>layui-this<?php endif; ?>" href="javascript:;"><?php echo htmlentities($item['name']); ?></a>
                    <?php if(!empty($item['child'])): ?>
                    <dl class="layui-nav-child">
                        <?php if(is_array($item["child"]) || $item["child"] instanceof \think\Collection || $item["child"] instanceof \think\Paginator): if( count($item["child"])==0 ) : echo "" ;else: foreach($item["child"] as $key=>$subitem): ?>
                        <dd class="<?php if($curAction == $subitem['a'] && $curController == $subitem['c']): ?>layui-this<?php endif; ?>"><a href="<?php echo htmlentities($subitem['url']); ?>"><?php echo htmlentities($subitem['name']); ?></a></dd>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </dl>
                    <?php endif; ?>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <li class="layui-nav-item">
                    <a href="<?php echo url('admin/index/clear'); ?>">清除缓存</a>
                </li>
                <li class="layui-nav-item">
                    <a href="<?php echo url('admin/publics/logout'); ?>">退出</a>
                </li>
                <!-- <li class="layui-nav-item"><a href="javascript:;">click menu item</a></li>
                <li class="layui-nav-item"><a href="">the links</a></li> -->
            </ul>
        </div>
    </div>
    
    
    <div class="layui-body layui-tab-content site-demo site-demo-body">
        <!-- 内容主体区域 -->
        <div class="layui-main">

            <div style="margin: 0 auto; max-width: 1140px;">
                <span class="layui-breadcrumb">
				  <a href="<?php echo admin_url($page_top['m'].'/'.$page_top['c'].'/'.$page_top['a']); ?>"><?php echo htmlentities($page_top['name']); ?></a>
				  <a href="javascript:;"><?php echo htmlentities($page_left['name']); ?></a>
				</span>
                
<div class="layui-tab">
    <div class="layui-tab-content">
        <table class="layui-hide" id="test" lay-filter="test"></table>
    </div>
</div>

<script type="text/html" id="checkboxTpl">
    <!-- 这里的 checked 的状态只是演示 -->
    <input type="checkbox" moduleid={{d.module_name}}  name="status" value="{{d.status}}" title="启用" lay-filter="lockDemo" {{ d.status == 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="barDemo">
    <!--  <a class="layui-btn layui-btn-xs" lay-event="detail">查看</a> -->
    {{# if(d.install == 1){ }}
    <a class="layui-btn layui-btn-danger layui-btn-xs"   lay-event="del">卸载</a>
    {{# }else{ }}
    <a class="layui-btn layui-btn-danger layui-btn-xs"   lay-event="add">安装</a>
    {{#  }  }}
</script>

            </div>

        </div>
    </div>
    



    
    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs">
            Anything you want
        </div>
        <!-- Default to the left -->
        <strong>Copyright &copy; 2016 <a href="#">Company</a>.</strong> All rights reserved.
    </footer>
    
</div>


<script>
    var url = '<?php echo admin_url("admin/module/index"); ?>';
    var delurl = '<?php echo admin_url("admin/module/uninstall"); ?>';
    var addurl = '<?php echo admin_url("admin/module/install"); ?>';
    var statusurl = '<?php echo admin_url("admin/module/status"); ?>';
    layui.use('table', function(){
        var table = layui.table
            ,form = layui.form;;

        table.render({
            elem: '#test'
            ,page:false
            ,url:url
            ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            ,cols: [[
                ,{field:'id',  title: '模块id',hide:true}
                ,{field:'module_title',  title: '模块名称'}
                ,{field:'module_name',  title: '模块标识'}
                ,{field:'author',  title: '开发者'}
                ,{field:'des', title: '描述'}
                ,{field:'status', title: '是否启用',templet: '#checkboxTpl', unresize: true/* , width: '30%', minWidth: 80 */} //minWidth：局部定义当前单元格的最小宽度，layui 2.2.1 新增
                ,{fixed: 'right', title:'操作', toolbar: '#barDemo', minWidth:50}
            ]]

        });

        //监听行工具事件
        //工具条事件
        table.on('tool(test)', function(obj){ //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var tr = obj.tr; //获得当前行 tr 的 DOM 对象（如果有的话）
            if(layEvent === 'del'){ //删除
                layer.confirm('真的卸载么，卸载后该模块数据会清空？', function(index){
                    y_utils.request_url({
                        url:delurl+'?module_name='+data.module_name,
                        success:function(da){
                            if(da.code == 1){
                                return y_utils.successMsg(da.msg,function(){history.go(0)});
                            }
                            return y_utils.errorMsg(da.msg)
                        }
                    });

                });
            }else if(layEvent === 'add'){
                y_utils.request_url({
                    url:addurl+'?module_name='+data.module_name,
                    success:function(da){
                        if(da.code == 1){
                            return y_utils.successMsg(da.msg,function(){history.go(0)});
                        }
                        return y_utils.errorMsg(da.msg)
                    }
                });

            }
        });

        //监听锁定操作
        form.on('checkbox(lockDemo)', function(obj){

            y_utils.request_url({
                url:statusurl,
                param:{module_name:this.getAttribute("moduleid")},
                success:function(da) {
                    if(da.code == 1){
                    return    y_utils.successMsg(da.msg,function(){history.go(0)});
                    }

                      y_utils.errorMsg(da.msg);return false;
                }
            })
            //layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
        });
    });
</script>


</body>
</html>