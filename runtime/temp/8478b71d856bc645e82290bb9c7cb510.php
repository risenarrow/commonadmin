<?php /*a:1:{s:74:"K:\CODE\php\site\commonadmin\application\admin\view\public\form\radio.html";i:1653307516;}*/ ?>
<div class="layui-form-item">
    <label class="layui-form-label"><?php echo htmlentities($label); ?></label>
    <div class="layui-input-block">
        <?php if(is_array($list_arr) || $list_arr instanceof \think\Collection || $list_arr instanceof \think\Paginator): if( count($list_arr)==0 ) : echo "" ;else: foreach($list_arr as $key=>$item): ?>
        <input type="radio" name="<?php echo htmlentities($name); ?>" value="<?php echo htmlentities($item[0]); ?>" title="<?php echo htmlentities($item[1]); ?>" <?php if($value == $item[0]): ?>checked=""<?php endif; ?>>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>