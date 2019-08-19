<?php
/**
 * Created by PhpStorm.
 * User: WT
 * Date: 18/5/20
 * Time: 下午12:04
 */
namespace app\mapi\controller;
use think\Db;

//动态域名管理
class DomainnameController extends BaseController{
    
    //列表
	public function getLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		//  获取值
		$page = I('post.page',1);
		$page_size = 10;
		
		$id = I('post.id');//id搜索
		if($id != ''){
			$where['id'] = $id;
		}
		
		$status = I('post.status');//状态：1、启用2、备用3、停用
		if($status != ''){
			$where['status'] = $status;
		}
		
		$e_type = I('post.e_type');//作用：1、分享2、阅读
		if($e_type != ''){
			$where['e_type'] = $e_type;
		}
		
		$gid = I('post.gid');//所属组1收徒页分享2文章分享
		if($gid != ''){
			$where['gid'] = $gid;
		}
		
		$dname = I('post.dname');//域名名称
		if($dname != ''){
			$where['dname'] = array('like','%'.$dname.'%');
		}

		$ShopModel = M('Domainname');
		$where['isdel'] = 0;
		//获取总条数
		$count = $ShopModel->where($where)->count();

		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);

		// 获取数据
		$result=$ShopModel->where($where)->order('create_time desc')->limit($limitStart,$page_size)->select();

		if($result === NULL){
            $result=array();
        }

		//判断是否有数据
		if($result !== false){
			$data['total_count'] = $count;
			$data['lists'] = $result;
			
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
			$ajaxReturn['data'] = $data;
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = "数据查询失败！";
		}
		
		 $this->ajaxReturn($ajaxReturn);
	}
	
	//获取修改页面数据
    public function infos(){
    		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
        $id = I('post.id');
        $PictureModel = M('Domainname');
        $result = $PictureModel->where('id='.$id)->find();
        if($result){
            $ajaxReturn['code'] = 200;
            $ajaxReturn['msg'] = "SUCCESS";
            $ajaxReturn['data'] = $result;
        }else{
            $ajaxReturn['code'] = 500;
            $ajaxReturn['msg'] = "查询数据失败！";
        }

        $this->ajaxReturn($ajaxReturn);
    }
	
	//保存
    public function save(){
        //检测是否登录
        $token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
        $ShopModel = M('Domainname');

		$id = I('post.id');
		$dname = I('post.dname');
		$status = I('post.status');
		$e_type = I('post.e_type');
		$gid = I('post.gid');
		$desc = I('post.desc');
        //封装数据
		$data['dname']=$dname;
		$data['status']=$status;
		$data['e_type']=$e_type;
		$data['gid']=$gid;
		$data['desc']=$desc;
		
        //修改
        if($id){
        		$data['create_time'] = time();
            //执行修改
            $result = $ShopModel->where('id='.$id)->save($data);
             if($result !== false){
                $ajaxReturn['code'] = 200;
                $ajaxReturn['msg'] = "SUCCESS";
             }else{
                $ajaxReturn['code'] = 500;
                $ajaxReturn['msg'] = "数据更新失败，请稍后再试！";
             }
        }else{
        		$data['create_time'] = time();
            //执行添加
            $lastid = $ShopModel->add($data);
            //判断是否成功
            if($lastid !== false){
                $ajaxReturn['code'] = 200;
                $ajaxReturn['msg'] = "SUCCESS";
            }else{
                $ajaxReturn['code'] = 500;
                $ajaxReturn['msg'] = "数据添加失败，请稍后再试！";
            }
        }
        $this->ajaxReturn($ajaxReturn);
    }
	
	//删除
	public function delete(){
		//检测是否登录
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');
		$ShopModel = M('Domainname');
		$where['id'] = $id;
		$data['isdel'] = 1;
		$result = $ShopModel->where($where)->save($data);
		if($result){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = "SUCCESS";
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = "失败，请稍后重试！";
		}
		
		$this->ajaxReturn($ajaxReturn);
	}
	
	//清除缓存
	public function cleanRedisInfo(){
		$keys = $this->redis->keys('WT_AppGetDomainNameInfo*');
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
        
        $ajaxReturn['code'] = 200;
        $ajaxReturn['msg'] = 'SUCCESS';
        $this->ajaxReturn($ajaxReturn);
	}

}
