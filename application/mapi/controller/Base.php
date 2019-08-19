<?php
namespace app\mapi\controller;
use Think\Controller;
use think\Db;
use think\RedisLink;

//前台父类控制器
class BaseController extends Controller
{
    protected $redis;
    public function __construct()
    {
    		//获取访问者ip
//  		$ip = get_client_ip(0, true);//用户ip
//  		if($ip != '124.207.71.99' && $ip != '0.0.0.0' && $ip != '47.95.203.205' && $ip != '106.39.68.79'){
//  			$ajaxReturn['code'] = 300;
//          $ajaxReturn['msg'] = "登录超时，或未登录！请重新登录";
//          $this->ajaxReturn($ajaxReturn);
//  		}
        parent::__construct();
        $this->redis = \Think\RedisLink::get_instance();
        
        //给钱阅公众号（提现公众号）
		$this->kyqwAppId = 'wx8e41e9a874959146';
		$this->AppSecret = '334ec4dae3c9926daff27fcda809fcbe';

    }
    
    //模仿http请求
    protected function postData($url, $data = null,$post=false){
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            ob_start();
            curl_exec($ch);
            $result = ob_get_contents();
            ob_end_clean();
            return $result;
        } catch (Exception $e){
            throw $e;
        }
    }

	//判断是否登录
    public function IsAppLogin($token)
    {
        $redisResult = json_decode($this->redis->get($token), true);
        if(!$redisResult){
            $ajaxReturn['code'] = 300;
            $ajaxReturn['msg'] = "登录超时，或未登录！请重新登录";
            $this->ajaxReturn($ajaxReturn);
        }else{
            return $redisResult;
        }
    }
	
	//判断参数是否缺失
    function CheckParam($param)
    {
        if (!isset($param) || empty($param)) {
            $ajaxReturn['code'] = 600;
            $ajaxReturn['msg'] = '缺失参数!';
            $this->ajaxReturn($ajaxReturn);
        }
        return true;
    }
    
    //获取用户账户信息
    protected function get_user_account_info($uid){
    		if(!$uid){
    			return array();
    		}
    		$UserAccount = M('UserAccount');
    		$where['uid'] = $uid;
    		$res = $UserAccount->field('total_money,user_money,total_gold,gold,create_time')->where($where)->find();
    		
    		return $res;
    }
    
    //清除“收徒-我的好友，以及二级页面的缓存”
    protected function CleanUserDiscipleList($uid,$type){
    		if(!$uid){
    			return;
    		}
    		if($type == 1){
    			$keys = $this->redis->keys('WT_AppDiscipleList'.$uid.'*');
	        for ($i = 0; $i < count($keys); $i++) {
	            $this->redis->set($keys[$i],null);
	        }
	        $keys = $this->redis->keys('WT_AppDiscipleRewardList'.$uid.'*');
	        for ($i = 0; $i < count($keys); $i++) {
	            $this->redis->set($keys[$i],null);
	        }
    		}elseif($type == 2){
    			$keys = $this->redis->keys('WT_AppDiscipleList'.$uid.'*');
	        for ($i = 0; $i < count($keys); $i++) {
	            $this->redis->set($keys[$i],null);
	        }
    		}
    }
    
    //处理同一标题的文章/视频
	public function TestingIdenticalTitle(){
		$sql = 'SELECT id,title,cid, COUNT(title) AS count FROM `kyd_article` GROUP BY title ORDER BY COUNT(title) DESC';
		$arr = M()->query($sql);
		$c = count($arr);
		$Article = M('Article');
		for($i=0;$i<$c;$i++){
			if($arr[$i]['count'] > 1){
				$where['title'] = $arr[$i]['title'];
				$where['id'] = array('neq',$arr[$i]['id']);
				$Article->where($where)->delete();
			}
		}
	}

    //当访问不存在的方法时，会跳到该方法
    public function _empty(){
    		//重定向到小说网站首页
        redirect('http://www.baidu.com/');
    }
    
}