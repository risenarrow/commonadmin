<?php /*a:2:{s:66:"K:\CODE\php\site\commonadmin\application\admin\view\menu\edit.html";i:1652867191;s:70:"K:\CODE\php\site\commonadmin\application\admin\view\public\layout.html";i:1654065083;}*/ ?>
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
                
			<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
			    <legend>填写</legend>
			  </fieldset>

			  <div class="layui-tab-content">
			    <form class="layui-form" action="" id="demo1">
					
					
			      <div class="layui-form-item">
			        <label class="layui-form-label">父级菜单</label>
			        <div class="layui-input-block">
			          <select name="data[parentid]" lay-verify="required" >
						  <option value="0" <?php if('0' == $data['parentid']||$parentid == '0'): ?> selected <?php endif; ?>>顶级菜单</option>
						  <?php if(is_array($list_menu) || $list_menu instanceof \think\Collection || $list_menu instanceof \think\Paginator): if( count($list_menu)==0 ) : echo "" ;else: foreach($list_menu as $key=>$item): ?>
						  <option value="<?php echo htmlentities($item['id']); ?>" <?php if($item['id'] == $data['parentid']||$parentid == $item['id']): ?> selected <?php endif; ?>><?php echo $item['name']; ?></option>
						  <?php endforeach; endif; else: echo "" ;endif; ?>
			          </select>
			        </div>
			      </div>
				  
				  
			      <div class="layui-form-item">
			        <label class="layui-form-label">菜单名</label>
			        <div class="layui-input-inline">
			          <input type="text" name="data[name]" lay-verify="required"  placeholder="请输入菜单名" autocomplete="off" class="layui-input" value="<?php echo htmlentities($data['name']); ?>">
			        </div>
			      </div>
				  
				  <div class="layui-form-item">
				    <label class="layui-form-label">模块名</label>
				    <div class="layui-input-inline">
				      <input type="text" name="data[m]" lay-verify="required|enverify"  placeholder="请输入模块名" autocomplete="off" class="layui-input" value="<?php echo htmlentities($data['m']); ?>">
				    </div>
					<div class="layui-form-mid layui-word-aux">必须是英文，下划线，数字</div>
				  </div>
				  
				  <div class="layui-form-item">
				    <label class="layui-form-label">控制器名</label>
				    <div class="layui-input-inline">
				      <input type="text" name="data[c]" lay-verify="required|enverify"  placeholder="请输入控制器名" autocomplete="off" class="layui-input" value="<?php echo htmlentities($data['c']); ?>">
				    </div>
					<div class="layui-form-mid layui-word-aux">必须是英文，下划线，数字</div>
				  </div>
				  
				  <div class="layui-form-item">
				    <label class="layui-form-label">方法名</label>
				    <div class="layui-input-inline">
				      <input type="text" name="data[a]" lay-verify="required|enverify"  placeholder="请输入方法名" autocomplete="off" class="layui-input" value="<?php echo htmlentities($data['a']); ?>">
				    </div>
					<div class="layui-form-mid layui-word-aux">必须是英文，下划线，数字</div>
				  </div>
				  
			      
				<div class="layui-form-item">
				  <label class="layui-form-label">排序</label>
				  <div class="layui-input-inline">
				    <input type="text" name="data[sort]"  autocomplete="off" placeholder="排序" class="layui-input" value="<?php echo htmlentities($data['sort']); ?>">
				  </div>
					<div class="layui-form-mid layui-word-aux">数字越大，越靠前</div>
				</div>
				
				
				<div class="layui-form-item">
				  <label class="layui-form-label">是否显示</label>
				  <div class="layui-input-block">
				    <input type="radio" name="data[show]" value="1" title="显示" <?php if($data['show'] == 1): ?>checked="checked"<?php endif; ?> >
				    <input type="radio" name="data[show]" value="0" title="隐藏" <?php if($data['show'] == '0'): ?>checked="checked"<?php endif; ?> >
				  </div>
				</div>
				
				  

			      <div class="layui-form-item">
			        <div class="layui-input-block">
						<input type="hidden" name="data[id]" value="<?php echo htmlentities($data['id']); ?>" />
			          <button type="submit" class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
			          <button type="reset" class="layui-btn layui-btn-primary">重置</button>
			        </div>
			      </div>
			    </form>
			  </div>

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
var url = '<?php echo url("admin/menu/edit"); ?>';
layui.use(['form', 'layedit', 'laydate'], function(){
  var form = layui.form
  ,layer = layui.layer
  ,layedit = layui.layedit
  ,laydate = layui.laydate;
  
  
  //创建一个编辑器
  var editIndex = layedit.build('LAY_demo_editor');
 
  //自定义验证规则
  form.verify({
    enverify:[
		/^[A-Za-z0-9_]+$/,
		'必须是英文，下划线，数字'
	]
    ,content: function(value){
      layedit.sync(editIndex);
    }
  });
  
  //监听指定开关
  form.on('switch(switchTest)', function(data){
    layer.msg('开关checked：'+ (this.checked ? 'true' : 'false'), {
      offset: '6px'
    });
    layer.tips('温馨提示：请注意开关状态的文字可以随意定义，而不仅仅是ON|OFF', data.othis)
  });
  
  //监听提交
  form.on('submit(demo1)', function(data){
    y_utils.request_url({
		url:url,
		param:data.field,
		success:function (data) {
			if(data.code == 1){
				y_utils.successMsg(data.msg,function(){
					location.href=data.url;
				});
			}else{
				y_utils.errorMsg(data.msg);
			}
		}
	})
    return false;
  });

  
});
</script>


</body>
</html>