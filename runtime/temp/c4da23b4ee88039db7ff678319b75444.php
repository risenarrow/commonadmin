<?php /*a:1:{s:77:"K:\CODE\php\site\commonadmin\application\admin\view\public\form\checkbox.html";i:1653310132;}*/ ?>
<div class="layui-form-item">
    <label class="layui-form-label"><?php echo htmlentities($label); ?></label>
    <div class="layui-input-block">
        <?php if(is_array($list_arr) || $list_arr instanceof \think\Collection || $list_arr instanceof \think\Paginator): if( count($list_arr)==0 ) : echo "" ;else: foreach($list_arr as $key=>$item): ?>
            <input type="checkbox" name="<?php echo htmlentities($name); ?>[]" title="<?php echo htmlentities($item[1]); ?>" value="<?php echo htmlentities($item[0]); ?>" <?php if(in_array($item[0],$arr_value,true)): ?>checked=""<?php endif; ?>>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>