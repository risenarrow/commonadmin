<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/6/9
 * Time: 8:42
 */

namespace app\simplebook\admin\validate;

use think\Validate;
class Project extends Validate
{
    protected $rule = [
        'project_title'  =>  'require|unique:simplebook_project',
    ];

    protected $message = [
        'project_title.require' => '项目名称不能为空',
        'project_title.unique' =>'项目已存在'
    ];

}