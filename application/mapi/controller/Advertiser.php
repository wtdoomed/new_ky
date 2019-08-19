<?php
namespace app\mapi\controller;
use think\Db;

//广告管理
class AdvertiserController extends BaseController {
	
	//获取广告列表
	public function getAdLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		$name = I('post.name');//广告名称
		if($name != ''){
			$where['a.name'] = array('like','%'.$name.'%');
		}
		
		$status = I('post.status');//状态1正常0停用
		if($status != ''){
			$where['a.status'] = $status;
		}
		
		$aid = I('post.aid');//广告商id
		if($aid != ''){
			$where['a.aid'] = $aid;
		}
		
		$pid = I('post.pid');//广告位id
		if($pid != ''){
			$where['a.pid'] = $pid;
		}
		
		$cid = I('post.cid');//广告类型id
		if($cid != ''){
			$where['a.cid'] = $cid;
		}
		
		$pic_type = I('post.pic_type');//1大图2单图3三图
		if($pic_type != ''){
			$where['a.pic_type'] = $pic_type;
		}
		
		$ad_type = I('post.ad_type');//广告展示类型 1跳链接 2提示分享3展示内容
		if($ad_type != ''){
			$where['a.ad_type'] = $ad_type;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$Advertisement = M('Advertisement');
		//获取总条数
		$count = $Advertisement->alias('a')
								->field('a.id,a.aid,a.pid,b.name as aname,c.name as pname')
								->join('__ADVERTISER__ b ON a.aid = b.id','LEFT')
								->join('__AD_POSITION__ c ON a.pid = c.id','LEFT')
								->where($where)
								->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $Advertisement->alias('a')
						->field('a.id,a.aid,a.pid,a.cid,a.pic_type,a.name,a.title,a.price,a.money,a.shareprice,a.total_money,a.surplus_money,a.down_money,a.ad_url,a.ad_type,a.rank,a.status,a.create_time,b.name as aname,c.name as pname,d.name as cname')
						->join('__ADVERTISER__ b ON a.aid = b.id','LEFT')
						->join('__AD_POSITION__ c ON a.pid = c.id','LEFT')
						->join('__AD_CLASS__ d ON a.cid = d.id','LEFT')
						->where($where)
						->order('a.rank desc')
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//添加/修改广告
	public function setAdInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告id 修改时传，添加不传
		$name = I('post.name');//广告名称
		$aid = I('post.aid');//广告商id
		$pid = I('post.pid');//广告位id
		$cid = I('post.cid');//广告类型id
		$title = I('post.title');//广告标题
		$desc = I('post.desc','','');//广告内容
		$pic_type = I('post.pic_type');//1大图2单图3三图
		$litpic1 = I('post.litpic1');//图1
		$litpic2 = I('post.litpic2');//图2
		$litpic3 = I('post.litpic3');//图3
		$price = I('post.price');//广告单价
		$shareprice = I('post.shareprice');//阅读单价
		$money = I('post.money');//实际单价
		$down_money = I('post.down_money');//下广告金额
		$ad_url = I('post.ad_url');//广告链接
		$ad_code = I('post.ad_code');//微博用到（一个码）
		$ad_type = I('post.ad_type');//1跳链接 2提示分享3展示内容
		$visitnum = I('post.visitnum');//点击数
		$share_type = I('post.share_type');//可分享类型：0不可分享、1朋友圈、2微信、3qq、4qq空间、5微博、6其他
		
		if($id){
			$data['name'] = $name;
			$data['aid'] = $aid;
			$data['pid'] = $pid;
			$data['cid'] = $cid;
			$data['title'] = $title;
			$data['desc'] = $desc;
			$data['pic_type'] = $pic_type;
			$data['litpic1'] = $litpic1;
			$data['litpic2'] = $litpic2;
			$data['litpic3'] = $litpic3;
			$data['price'] = $price;
			$data['shareprice'] = $shareprice;
			$data['money'] = $money;
			$data['down_money'] = $down_money;
			$data['ad_url'] = html_entity_decode($ad_url);
			$data['ad_code'] = $ad_code;
			$data['ad_type'] = $ad_type;
			$data['visitnum'] = $visitnum;
			$data['share_type'] = $share_type;
			$data['update_time'] = time();
			$where['id'] = $id;
			$Advertisement = M('Advertisement');
			$res = $Advertisement->where($where)->save($data);
		}else{
			$data['name'] = $name;
			$data['aid'] = $aid;
			$data['pid'] = $pid;
			$data['cid'] = $cid;
			$data['title'] = $title;
			$data['desc'] = $desc;
			$data['pic_type'] = $pic_type;
			$data['litpic1'] = $litpic1;
			$data['litpic2'] = $litpic2;
			$data['litpic3'] = $litpic3;
			$data['price'] = $price;
			$data['shareprice'] = $shareprice;
			$data['money'] = $money;
			$data['down_money'] = $down_money;
			$data['ad_url'] = $ad_url;
			$data['ad_code'] = $ad_code;
			$data['ad_type'] = $ad_type;
			$data['visitnum'] = $visitnum;
			$data['share_type'] = $share_type;
			$data['create_time'] = time();
			$data['update_time'] = $data['create_time'];
			$Advertisement = M('Advertisement');
			$res = $Advertisement->add($data);
		}
		
		if($res){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
		}else{
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取广告详情
	public function getAdDetail(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告id
		
		$Advertisement = M('Advertisement');
		$where['id'] = $id;
		$List = $Advertisement->where($where)->select();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//修改广告排序
	public function setAdRankInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告id
		$rank = I('post.rank');//排序值
		
		$data['rank'] = $rank;
		$data['update_time'] = time();
		$where['id'] = $id;
		$Advertisement = M('Advertisement');
		$Advertisement->where($where)->save($data);
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
	//修改广告状态
	public function setAdStaInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告id
		$status = I('post.status');//状态1正常0停用
		
		$data['status'] = $status;
		$data['update_time'] = time();
		$where['id'] = $id;
		$Advertisement = M('Advertisement');
		$Advertisement->where($where)->save($data);
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
	//广告清零
	public function ClearZeroAdinfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告id
		$Advertisement = M('Advertisement');
		
		//获取该广告的余额
		$where['id'] = $id;
		$res = $Advertisement->field('total_money,surplus_money')->where($where)->find();
		if($res){
			$data['total_money'] = $res['total_money'] - $res['surplus_money'];
			$data['surplus_money'] = 0;
			$res1 = $Advertisement->where($where)->save($data);
			if($res1){
				$ajaxReturn['code'] = 200;
				$ajaxReturn['msg'] = 'SUCCESS';
			}else{
				$ajaxReturn['code'] = 500;
				$ajaxReturn['msg'] = '失败，请稍后重试！';
			}
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		
		$this->ajaxReturn($ajaxReturn);
	}
	
	//清除列表以及详情缓存
	public function CleanAdInfoList(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
  		$id = I('post.id');//广告id
  		$pid = I('post.pid');//广告位id
  		
        $keys = $this->redis->keys('WT_AppADInfoLists*');//广告列表
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
        
        $keys = $this->redis->keys('WT_AppADDetailInfo'.$id.'*');//广告详情
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
        
        $keys = $this->redis->keys('WT_AppADInfoSingleData'.$pid.'*');//广告位
        for ($i = 0; $i < count($keys); $i++) {
            $this->redis->set($keys[$i],null);
        }
        
        $ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
	//广告充值
	public function setAdPay(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$aid = I('post.id');//广告id
		$cid = I('post.aid');//广告商id
		$money = I('post.money')*100;//充值金额
		$type = I('post.type');//充值类型 1:直充 2:转充
		$s_type = I('post.s_type');//类型：1实钱 2虚钱
		
		if($money < 0 || $money > 1000000){
			$ajaxReturn['code'] = 401;
			$ajaxReturn['msg'] = '充值金额不合法！';
			$this->ajaxReturn($ajaxReturn);
		}
		
		$data['cid'] = $cid;
		$data['aid'] = $aid;
		$data['money'] = $money;
		$data['type'] = $type;
		$data['s_type'] = $s_type;
		$data['order_number'] = build_order_no();
		$data['create_time'] = time();
		$AdPayRecord = M('AdPayRecord');
		$res = $AdPayRecord->add($data);//添加充值记录
		if($res){
			$Advertisement = M('Advertisement');
			$where2['id'] = $aid;
			$res1 = $Advertisement->where($where2)->setInc('total_money',$money);
			$res1 = $Advertisement->where($where2)->setInc('surplus_money',$money);
			
			$res2 = true;
			if($type == 1){//只有直充的，才累计广告商的充值金额
				$Advertiser = M('Advertiser');
				$where1['id'] = $cid;
				$res2 = $Advertiser->where($where1)->setInc('total_money',$money);
			}
			
			if($res1 && $res2){
				$ajaxReturn['code'] = 200;
				$ajaxReturn['msg'] = 'SUCCESS';
			}else{
				$ajaxReturn['code'] = 500;
				$ajaxReturn['msg'] = '失败，请稍后重试！';
			}
		}else{
			$ajaxReturn['code'] = 500;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取每条广告数据明细
	public function getAdInfoDetatis(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$aid = I('post.id',38);//广告id
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$where['aid'] = $aid;
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdfeeData = M('CountAdfeeData');
		//获取总条数
		$count = $CountAdfeeData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountAdfeeData->field('ad_ipnum,price,ad_money,create_time')
						->where($where)
						->order('create_time desc')
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取广告商列表
	public function getAdverInfoLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		$uname = I('post.uname');//广告主名称
		if($uname != ''){
			$where['a.name'] = array('like','%'.$uname.'%');
		}
		
		$status = I('post.status');//状态1正常0封号
		if($status != ''){
			$where['a.status'] = $status;
		}
		
		$phone = I('post.uphone');//用户绑定手机号
		if($phone != ''){
			$where['a.phone'] = array('like','%'.$phone.'%');
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$Advertiser = M('Advertiser');
		//获取总条数
		$count = $Advertiser->alias('a')->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $Advertiser->alias('a')
						->field('a.id,a.name,a.username,a.phone,a.total_money,a.status,a.create_time')
						->where($where)
						->order('a.create_time desc')
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//添加/修改广告商
	public function setAdverInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告商id 修改时传，添加不传
		$uname = I('post.uname');//广告主名称
		$uphone = I('post.uphone');//手机号
		$username = I('post.username');//用户名
		$password = getPwdEncodeString(I('post.password'));//密码
		
		if($id){
			$data['name'] = $uname;
			$data['phone'] = $uphone;
			if($password){
				$data['password'] = $password;
			}
			$data['update_time'] = time();
			$where['id'] = $id;
			$Advertiser = M('Advertiser');
			$res = $Advertiser->where($where)->save($data);
		}else{
			$data['name'] = $uname;
			$data['phone'] = $uphone;
			$data['username'] = $username;
			$data['password'] = $password;
			$data['create_time'] = time();
			$data['update_time'] = $data['create_time'];
			$Advertiser = M('Advertiser');
			$res = $Advertiser->add($data);
		}
		
		if($res){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
		}else{
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		
		$this->ajaxReturn($ajaxReturn);
	}
	
	//修改状态
	public function setStaInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告商id
		$status = I('post.status');//状态1正常0封号
		
		$data['status'] = $status;
		$data['update_time'] = time();
		$where['id'] = $id;
		$Advertiser = M('Advertiser');
		$Advertiser->where($where)->save($data);
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取广告位列表
	public function getAdposiLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		$name = I('post.name');//广告位名称
		if($name != '') {
			$where['name'] = array('like', '%' . $name . '%');
		}
		$AdPosition = M('AdPosition');
		//获取总条数
		$count = $AdPosition->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $AdPosition
						->field('id,name,create_time')
						->where($where)
						->order('id desc')
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//添加/修改广告位
	public function setAdposiInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//广告位id 修改时传，添加不传
		$name = I('post.name');//广告位名称
		
		if($id){
			$data['name'] = $name;
			$where['id'] = $id;
			$AdPosition = M('AdPosition');
			$res = $AdPosition->where($where)->save($data);
		}else{
			$data['name'] = $name;
			$data['create_time'] = time();
			$AdPosition = M('AdPosition');
			$res = $AdPosition->add($data);
		}
		
		if($res){
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = 'SUCCESS';
		}else{
			$ajaxReturn['code'] = 200;
			$ajaxReturn['msg'] = '失败，请稍后重试！';
		}
		
		$this->ajaxReturn($ajaxReturn);
	}
	
}

