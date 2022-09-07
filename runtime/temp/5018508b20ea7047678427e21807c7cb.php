<?php /*a:2:{s:67:"K:\CODE\php\site\commonadmin\application\admin\view\menu\index.html";i:1652863973;s:70:"K:\CODE\php\site\commonadmin\application\admin\view\public\layout.html";i:1654065083;}*/ ?>
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
                
<div class="nav-tabs-custom" style="cursor: move;">
	<div class="box-body">
		<div class="tab-content no-padding">
			<div class="row">
				<div class="col-sm-12">
					<table id="example2" class="layui-table" role="grid" aria-describedby="example2_info">
						<thead>
						<tr><td colspan="4">

							<a class="layui-btn layui-btn-sm" href="<?php echo url('admin/Menu/add'); ?>">添加</button>

							</td>
						</tr>
						<tr role="row">
							<th class="sorting" tabindex="0"  rowspan="1" colspan="1" >排序</th>
							<th class="sorting_asc" tabindex="0"  rowspan="1" colspan="1" >id</th>
							<th class="sorting" tabindex="0"  rowspan="1" colspan="1" >菜单名称</th>
							<th class="sorting" tabindex="0"  rowspan="1" colspan="1" >操作</th>
						</tr>
						</thead>
						<tbody>
						<?php if($menulist): if(is_array($menulist) || $menulist instanceof \think\Collection || $menulist instanceof \think\Paginator): if( count($menulist)==0 ) : echo "" ;else: foreach($menulist as $key=>$vo): ?>
						<tr role="row" class="odd">
							<td><input class="form-control" style="width: 50px" type="text" name="menu[<?php echo htmlentities($vo['id']); ?>]" value="<?php echo htmlentities($vo['sort']); ?>" /></td>
							<td class="sorting_1"><?php echo htmlentities($vo['id']); ?></td>
							<td><?php echo $vo['name']; ?></td>
							<td>
								<a href="<?php echo url('admin/Menu/add',['parentid'=>$vo['id']]); ?>"  class="layui-btn">添加子菜单</a>
								<a href="<?php echo url('admin/Menu/edit',['id'=>$vo['id']]); ?>"  class="layui-btn">编辑</a>
								<a href="<?php echo url('admin/Menu/del',['id'=>$vo['id']]); ?>" onclick=" return confirm('确定要删除菜单吗？') || false;"  class="layui-btn layui-btn-danger">删除</a>
							</td>
						</tr>
						<?php endforeach; endif; else: echo "" ;endif; else: ?>
						<tr role="row" class="odd">
							<td colspan="5" align="center">暂无数据~!</td>
						</tr>
						<?php endif; ?>
						</tbody>

					</table>
				</div>
			</div>
		</div>
	</div>
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




</body>
</html>