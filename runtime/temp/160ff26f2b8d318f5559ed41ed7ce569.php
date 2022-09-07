<?php /*a:2:{s:73:"K:\CODE\php\site\commonadmin\application\admin\view\public\form\char.html";i:1653307825;s:74:"K:\CODE\php\site\commonadmin\application\admin\view\public\form\photo.html";i:1653380806;}*/ ?>
<div class="layui-form-item">
    <label class="layui-form-label"><?php echo htmlentities($label); ?></label>
    <div class="layui-input-block">
        <div class="layui-upload">
            <button type="button" class="layui-btn" id="test1_img_<?php echo htmlentities($name); ?>">上传图片</button>
            <div class="layui-upload-list">
                <img class="layui-upload-img" id="demo1_img_<?php echo htmlentities($name); ?>" <?php if($value): ?>src="<?php echo htmlentities($value); ?>"<?php endif; ?>>
                <p id="demoText_img_<?php echo htmlentities($name); ?>"></p>
            </div>
            <div style="width: 95px;">
                <div class="layui-progress layui-progress-big" lay-showpercent="yes" lay-filter="demo_img_<?php echo htmlentities($name); ?>">
                    <div class="layui-progress-bar" lay-percent=""></div>
                </div>
            </div>
            <div class="layui-form-item" style="margin-top: 10px">
                <div class="layui-input-inline">
                    <input type="text" name="<?php echo htmlentities($name); ?>" id="<?php echo htmlentities($name); ?>"  autocomplete="off" placeholder="文件路径" class="layui-input" value="<?php echo htmlentities($value); ?>">
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use(['upload','element'], function() {
        var upload = layui.upload
            ,$ = layui.jquery
            ,element = layui.element;
        //常规使用 - 普通图片上传
        var uploadInst = upload.render({
            elem: '#test1_img_<?php echo htmlentities($name); ?>'
            , url: '<?php if(!isset($uploadUrl)): ?><?php echo admin_url("admin/index/upload"); else: ?><?php echo htmlentities($uploadUrl); ?><?php endif; ?>' //此处用的是第三方的 http 请求演示，实际使用时改成您自己的上传接口即可。
            ,field:'<?php echo htmlentities($name); ?>_new_path'
            , before: function (obj) {
                //预读本地文件示例，不支持ie8
                obj.preview(function (index, file, result) {
                    $('#demo1_img_<?php echo htmlentities($name); ?>').attr('src', result); //图片链接（base64）
                });

                element.progress('demo_img_<?php echo htmlentities($name); ?>', '0%'); //进度条复位
                layer.msg('上传中', {icon: 16, time: 0});
            }
            , done: function (res) {
                //如果上传失败
                if (res.code > 0) {
                    return layer.msg('上传失败'+res.msg);
                }

                //上传成功的一些操作
                $('#<?php echo htmlentities($name); ?>').val(res.data.src);
                //……
                $('#demoText_img_<?php echo htmlentities($name); ?>').html(''); //置空上传失败的状态
            }
            , error: function () {
                //演示失败状态，并实现重传
                var demoText = $('#demoText_img_<?php echo htmlentities($name); ?>');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function () {
                    uploadInst.upload();
                });
            }
            //进度条
            , progress: function (n, elem, e) {
                element.progress('demo_img_<?php echo htmlentities($name); ?>', n + '%'); //可配合 layui 进度条元素使用
                if (n == 100) {
                    layer.msg('上传完毕', {icon: 1});
                }
            }
        });
    });
</script>