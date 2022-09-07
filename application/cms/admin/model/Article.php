<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2022/8/3
 * Time: 15:52
 */

namespace app\cms\admin\model;

use app\admin\model\PublicModel;
class Article extends PublicModel
{
    protected $pk = 'id';
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_ARTICLE__';

    /**
     *
     * 获取文章列表
     * @return array
     * @throws \think\exception\DbException
     * @author yang
     * Date: 2022/6/9
     */
    public function getArticleList(){
        $limit = $this->param['limit'];

        $where = [];
        $list = self::where($where);

        if(isset($this->param['order']) && in_array($this->param['order'],['asc','desc'])
            &&isset($this->param['field'])&& in_array($this->param['field'],['addtime'])){
            $list = $list->order($this->param['field'].' '. $this->param['order']);
        }
        $list = $list->paginate($limit)->each(function($item,$key){
            $item->addtime = date('Y-m-d H:i:s',$item->addtime);
        });
        $arr = [];
        foreach($list as $k=>$v){
            $arr[$k] = $v;
        }
        return ['data'=>$arr,'count'=>$list->total()];
    }


    public function add(){
        $data = $this->param;
        $validate =  new \app\cms\admin\validate\Article();
        if(!$validate->check($data)) {
            $this->msg = $validate->getError();return false;
        }
        $arr = [];
        $arr['article_title'] = $data['article_title'];
        $arr['article_desc'] = $data['article_desc'];
        $arr['article_content'] = htmlspecialchars($data['article_content']);
        $arr['cat_id'] = $data['cat_id'];
        $arr['addtime'] = time();
        $arr['updatetime'] = time();
        if(self::insert($arr)){
            $this->msg = '添加成功';return true;
        }
        $this->msg ='添加失败';return false;
    }

}