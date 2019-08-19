<?php
namespace app\mapi\controller;
use think\Db;

//提现&封用户
class SealController extends BaseController {
	
	//获取提现列表
	public function getUserWithdrawLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'create_time';
		}
		$rank_type = I('post.rank_type',2);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = 'a.'.$rank_name.' '.$rank_type;
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id != ''){
			$where['b.user_id'] = array('like','%'.$user_id.'%');
		}
		
		$u_status = I('post.u_status');//用户状态1正常2白名单3黑名单4用户封号
		if($u_status != ''){
			$where['b.status'] = $u_status;
		}
		
		$is_withdraw = I('post.is_withdraw');//提现状态1正常0封号
		if($is_withdraw != ''){
			$where['b.is_withdraw'] = $is_withdraw;
		}
		
		$is_pullblack = I('post.is_pullblack');//高价状态：1拉黑 0正常
		if($is_pullblack != ''){
			$where['b.is_pullblack'] = $is_pullblack;
		}
		
		$type = I('post.type');//提现方式1微信2话费
		if($type != ''){
			$where['a.type'] = $type;
			$where1['a.type'] = $type;
		}
		
		$status = I('post.status');//状态 1已打款 2未审核 0审核失败 3拒绝打款
		if($status != ''){
			$where['a.status'] = $status;
			$where1['a.status'] = $status;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
            $where1['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		$UserWithdraw = M('UserWithdraw');
		//获取总条数
		$count = $UserWithdraw->alias('a')
								->field('a.id,a.uid,a.type,a.phone,a.money,a.after_money,a.desc,a.status,a.create_time,b.user_id,b.name')
								->join('__USER__ b ON a.uid = b.id','LEFT')
								->where($where)
								->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $UserWithdraw->alias('a')
						->field('a.id,a.uid,a.type,a.phone,a.money,a.after_money,a.desc,a.status,a.r_status,a.create_time,b.user_id,b.name,b.create_time as register_time,b.status as u_status,b.is_withdraw,b.is_pullblack,b.sealreason')
						->join('__USER__ b ON a.uid = b.id','LEFT')
						->where($where)
						->order($order)
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		$c = count($List);
		for($i=0;$i<$c;$i++){
			$arr = json_decode($this->redis->get('WT_AppUserCauseLists'),true);
			$sealreason = explode(',',$List[$i]['sealreason']);
			$c1 = count($sealreason);
			$c2 = count($arr);
			$str = '';
			for($j=0;$j<$c1;$j++){
				for($k=0;$k<$c2;$k++){
					if($sealreason[$j] == $arr[$k]['id']){
						$str .= $arr[$k]['name'].'，';
						break;
					}
				}
			}
			
			$List[$i]['seal_name'] = $str;
		}
		
		//获取用户上次提现时间
		$cc = count($List);
		for($i=0;$i<$cc;$i++){
			$wherecc['create_time'] = array('lt',$List[$i]['create_time']);
			$wherecc['uid'] = $List[$i]['uid'];
			$arrc = $UserWithdraw->field('create_time')->where($wherecc)->order('create_time desc')->find();
			if($arrc){
				$List[$i]['last_time'] = $arrc['create_time'];//上次提现时间
			}else{
				$List[$i]['last_time'] = 1;//表示第一次提现
			}
		}
		
		//获取总提现金额
		$moneycount = $UserWithdraw->alias('a')->where($where1)->sum('money');
		
		$infos['moneycount'] = $moneycount;
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['infos'] = $infos;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取硬广检测数据
	public function getYingguangDataLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'create_time';
		}
		$rank_type = I('post.rank_type',2);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = 'a.'.$rank_name.' '.$rank_type;
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id != ''){
			$where['b.user_id'] = array('like','%'.$user_id.'%');
		}
		
		$is_pullblack = I('post.is_pullblack');//高价状态：1拉黑 0正常
		if($is_pullblack != ''){
			$where['b.is_pullblack'] = $is_pullblack;
		}

		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		$CountYingguangData = M('CountYingguangData');
		//获取总条数
		$count = $CountYingguangData->alias('a')
								->field('a.uid,a.aid,a.desc,a.create_time,b.is_pullblack,c.title')
								->join('__USER__ b ON a.uid = b.id','LEFT')
								->join('__ADVERTISEMENT__ c ON a.aid = c.id','LEFT')
								->where($where)
								->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountYingguangData->alias('a')
						->field('a.uid,a.aid,a.desc,a.create_time,b.is_pullblack,b.status,b.user_id,b.name,c.title')
						->join('__USER__ b ON a.uid = b.id','LEFT')
						->join('__ADVERTISEMENT__ c ON a.aid = c.id','LEFT')
						->where($where)
						->order($order)
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
	
	//硬广检测数据--
	public function getyingguangData(){
		$AdExpenditureRecord = M('AdExpenditureRecord');
		$data = array();
		$type = I('post.type');//1:获取ip点击次数>=2的  2:获取1个ip点击广告>=4个的
		$uid = I('post.uid');//用户id
		$where['uid'] = $uid;
		$where['type'] = 1;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		//获取ip点击次数>=2的
		if($type == 1){
			$res = $AdExpenditureRecord->field('count(id) as num,user_ip')->where($where)->group('user_ip')->order('count(id) desc')->select();
			$c = count($res);
			for($i=0;$i<$c;$i++){
				if($res[$i]['num'] > 1){
					$data[] = $res[$i];
				}else{
					break;
				}
			}
		}else{
			//获取1个ip点击广告>=4个的
			$res = $AdExpenditureRecord->field('count(distinct(aid)) as num,user_ip')->where($where)->group('user_ip')->order('count(distinct(aid)) desc')->select();
			$c = count($res);
			for($i=0;$i<$c;$i++){
				if($res[$i]['num'] > 3){
					$data[] = $res[$i];
				}else{
					break;
				}
			}
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $data;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//--------------------------------------------封号数据开始--------------------------------------------
	//获取广告费明细 硬广、外链详情、站内
	public function getYingguangClick(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 100;
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'create_time';
		}
		$rank_type = I('post.rank_type',2);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = $rank_name.' '.$rank_type;
		
		$type = I('post.type','');//1硬广2外链详情3站内
		if($type != ''){
			$where['type'] = $type;
		}
		
		$equipment_model = I('post.equipment_model','');//设备信息
		if($equipment_model != ''){
			$where['equipment_model'] = array('like','%'.$equipment_model.'%');
		}
		
		$user_ip = I('post.user_ip','');//用户ip
		if($user_ip != ''){
			$where['user_ip'] = array('like','%'.$user_ip.'%');
		}

//		$tid = I('post.tid','');//文章&视频id
//		if($tid != ''){
//			$where['tid'] = array('like','%'.$tid.'%');
//		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		$AdExpenditureRecord = M('AdExpenditureRecord');
		//获取总条数
		$count = $AdExpenditureRecord->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $AdExpenditureRecord
						->field('aid,pid,price,money,equipment_model,user_ip,uid,tid,type,create_time')
						->where($where)
						->order($order)
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
	
	//广告-设备号/ip对应的点击次数
	public function getAdNumLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid','');//用户id
		$aid = I('post.aid','');//广告id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		if($aid){
			$where['aid'] = $aid;
		}
		
		$page = I('post.page',1);
		$page_size = 100;
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'count(id)';
		}
		$rank_type = I('post.rank_type',1);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = $rank_name.' '.$rank_type;
		
		$s_type = I('post.s_type',1);//1、按ip分组 2、按设备分组
		if($s_type == 1){
			$group = 'user_ip';
			$field = 'user_ip,count(id)';
		}else{
			$group = 'equipment_model';
			$field = 'equipment_model,count(id)';
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }else{
        		$where['create_time'] = array(array('egt',time()-86400),array('lt',time()));
        }
		
		$AdExpenditureRecord = M('AdExpenditureRecord');
		//获取总条数
		$count = $AdExpenditureRecord
								->field($field)
								->where($where)
								->order($order)
								->group($group)
								->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $AdExpenditureRecord
						->field($field)
						->where($where)
						->order($order)
						->group($group)
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
	
	//文章费用明细
	public function getArticleClick(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 100;
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'create_time';
		}
		$rank_type = I('post.rank_type',2);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = $rank_name.' '.$rank_type;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $equipment_model = I('post.equipment_model','');//设备信息
		if($equipment_model != ''){
			$where['equipment_model'] = array('like','%'.$equipment_model.'%');
		}
		
		$user_ip = I('post.user_ip','');//用户ip
		if($user_ip != ''){
			$where['user_ip'] = array('like','%'.$user_ip.'%');
		}

		$tid = I('post.tid','');//文章&视频id
		if($tid != ''){
			$where['tid'] = $tid;
		}
		
		$ArticleReadRecord = M('ArticleReadRecord');
		//获取总条数
		$count = $ArticleReadRecord->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $ArticleReadRecord
						->field('equipment_model,user_ip,uid,tid,create_time')
						->where($where)
						->order($order)
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
	
	//文章/视频-设备号/ip对应的点击次数
	public function getArticelNumLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid','');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 100;
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'count(id)';
		}
		$rank_type = I('post.rank_type',1);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = $rank_name.' '.$rank_type;
		
		$s_type = I('post.s_type',1);//1、按ip分组 2、按设备分组
		if($s_type == 1){
			$group = 'user_ip';
			$field = 'user_ip,count(id)';
		}else{
			$group = 'equipment_model';
			$field = 'equipment_model,count(id)';
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }else{
        		$where['create_time'] = array(array('egt',time()-86400),array('lt',time()));
        }
		
		$ArticleReadRecord = M('ArticleReadRecord');
		//获取总条数
		$count = $ArticleReadRecord
								->field($field)
								->where($where)
								->order($order)
								->group($group)
								->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $ArticleReadRecord
						->field($field)
						->where($where)
						->order($order)
						->group($group)
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
	
	//获取付费ip/设备明细
	public function getArticleChargingInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 100;
		
		//设置排序
		$rank_name = I('post.rank_name','');
		if(!$rank_name){
			$rank_name = 'create_time';
		}
		$rank_type = I('post.rank_type',2);//1倒序2正序
		if($rank_type == 2){$rank_type = 'asc';}else{$rank_type = 'desc';}
		$order = $rank_name.' '.$rank_type;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $equipment_model = I('post.equipment_model','');//设备信息
		if($equipment_model != ''){
			$where['equipment_model'] = array('like','%'.$equipment_model.'%');
		}
		
		$user_ip = I('post.user_ip','');//用户ip
		if($user_ip != ''){
			$where['user_ip'] = array('like','%'.$user_ip.'%');
		}
		
		$ArticleChargingRecord = M('ArticleChargingRecord');
		//获取总条数
		$count = $ArticleChargingRecord->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $ArticleChargingRecord
						->field('equipment_model,user_ip,create_time')
						->where($where)
						->order($order)
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
	
	//获取用户目前账户情况
	public function getUserAccInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$List = $this->get_user_account_info($uid);
		
		if($List === NULL){
			$List = array();
		}
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取徒弟列表
	public function getUserDiscipleList(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['a.masterid'] = $uid;
		$page = I('post.page',1);
		$page_size = 10;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $is_withdraw = I('post.w_status');//提现状态1正常0封号
		if($is_withdraw != ''){
			$where['a.is_withdraw'] = $is_withdraw;
		}
		
		$status = I('post.status');//用户状态1正常2白名单3黑名单4用户封号
		if($status != ''){
			$where['a.status'] = $status;
		}
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id != ''){
			$where['a.user_id'] = array('like','%'.$user_id.'%');
		}
		
		$User = M('User');
		//获取总条数
		$List1 = $User->alias('a')->field('a.id,d.create_time,b.total_money')
						->join('__USER_ACTIVE_RECORD__ d ON a.id = d.uid','LEFT')
						->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')
						->where($where)
						->select();
		$count = count($List1);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $User->alias('a')->field('a.id,a.user_id,a.name,a.litpic,a.sex,a.phone,a.status,a.is_withdraw,a.phone_model,a.device_id,a.create_time,d.create_time as active_time,b.total_money,b.user_money')
						->join('__USER_ACTIVE_RECORD__ d ON a.id = d.uid','LEFT')
						->join('__USER_ACCOUNT__ b ON a.id = b.uid','LEFT')
						->where($where)
						->order('a.create_time desc')
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		//获取师傅信息
		$infos = $User->alias('a')->field('a.masterid,d.user_id,d.name,d.status,d.is_withdraw')
						->join('__USER__ d ON a.masterid = d.id','LEFT')
						->where('a.id='.$uid)
						->find();
		
		//获取徒弟金额>2.5元总数
		$snum = 0;
		for($i=0;$i<$count;$i++){
			if($List1[$i]['total_money'] >= 250){
				$snum += 1;
			}
		}
		//徒弟总收入2.5元的占总徒弟的百分比
		if($snum == 0 || $count == 0){
			$bfb = 0;
		}else{
			$bfb = round($snum / $count,2) * 100;
		}
		
		//获取今天之前收的徒弟(活跃度=昨天之前来的用户，在昨天活跃的，占总用户的比)
		$stime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$where4['a.create_time'] = array('lt',$stime);
		$where4['a.masterid'] = $uid;
		$List2 = $User->alias('a')->field('a.id,d.create_time')
						->join('__USER_ACTIVE_RECORD__ d ON a.id = d.uid','LEFT')
						->where($where4)
						->select();
						
		$cc = count($List2);				
		//获取3天内活跃的徒弟总数
		$anum = 0;
		for($i=0;$i<$cc;$i++){
			if($List2[$i]['create_time'] >= $stime){
				$anum += 1;
			}
		}
		//徒弟3日内活跃的占总徒弟的百分比
		if($anum == 0 || $cc == 0){
			$bfb1 = 0;
		}else{
			$bfb1 = round($anum / $count,2) * 100;
		}

		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['infos'] = $infos;
		$ajaxReturn['data']['centage'] = $bfb;
		$ajaxReturn['data']['active_centage'] = $bfb1;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取徒弟给提成的明细
	public function getDiscipleExtract(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		$page = I('post.page',1);
		$page_size = 10;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		$where['a.cid'] = array('in','4,5');
		$where['a.student_uid'] = array('gt',0);
		$UserMoneyRecord = M('UserMoneyRecord');
		//获取总条数
		$count = $UserMoneyRecord->alias('a')
										->field('sum(a.money) as money,a.student_uid,b.user_id,b.name,b.create_time as register_time,b.status,b.is_withdraw')
										->join('__USER__ b ON a.student_uid = b.id','LEFT')
										->where($where)
										->group('a.student_uid')
										->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $UserMoneyRecord->alias('a')->field('sum(a.money) as money,a.student_uid,b.user_id,b.name,b.create_time as register_time,b.status,b.is_withdraw')
								->join('__USER__ b ON a.student_uid = b.id','LEFT')
								->where($where)
								->group('a.student_uid')
								->order('sum(a.money) desc')
								->limit($limitStart,$page_size)
								->select();
		//获取师傅信息
		$User = M('User');
		$infos = $User->alias('a')->field('a.masterid,d.user_id,d.name,d.status,d.is_withdraw')
						->join('__USER__ d ON a.masterid = d.id','LEFT')
						->where('a.id='.$uid)
						->find();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['infos'] = $infos;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取用户的账户流水明细（钱）
	public function getUserMoneyLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		$page = I('post.page',1);
		$page_size = 10;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//类型
		if($type){
			$where['cid'] = $type;
		}
		
		$User = M('UserMoneyRecord');
		//获取总条数
		$count = $User->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $User->field('student_uid,money,after_money,cid,create_time')
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
	
	//获取用户的账户流水明细（金币）
	public function getUserGoldLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 10;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//类型
		if($type){
			$where['cid'] = $type;
		}
		
		$User = M('UserGoldRecord');
		//获取总条数
		$count = $User->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $User->field('gold,after_gold,cid,create_time')
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
	
	//获取用户每天分享文章记录
	public function getUserShareLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid');//用户id
		$this->CheckParam($uid);//判断参数是否缺失
		$where['uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 10;
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['share_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//类型
		if($type){
			$where['type'] = $type;
		}
		
		$User = M('UserShareRecord');
		//获取总条数
		$count = $User->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $User->field('aid,user_ip,type,share_time as create_time')
						->where($where)
						->order('share_time desc')
						->limit($limitStart,$page_size)
						->select();
		if($List === NULL){
			$List = array();
		}
		
		//获取文章分享总数
		$infos['scount'] = $User->where($where)->count();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$ajaxReturn['data']['infos'] = $infos;
		$ajaxReturn['data']['total_count'] = $count;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取每个用户针对广告费用的带来的总金额
	public function getUserAdLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = 10;
		$AdExpenditureRecord = M('AdExpenditureRecord');

		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//类型:1硬广2外链详情3站内
		if($type){
			$where['a.type'] = $type;
		}
		
		$aid = I('post.aid');//广告id
		if($aid){
			$where['a.aid'] = $aid;
		}
		
		$pid = I('post.pid');//广告位id
		if($pid){
			$where['a.pid'] = $pid;
		}
		
		$status = I('post.status');//用户状态1正常2白名单3黑名单4用户封号
		if($status){
			$where['b.status'] = $status;
		}
		
		$is_withdraw = I('post.is_withdraw');//提现状态1正常0封号
		if($is_withdraw){
			$where['b.is_withdraw'] = $is_withdraw;
		}
		
		$where['price'] = array('gt',0);
		//获取总条数
		$count = $AdExpenditureRecord->alias('a')
										->field('a.uid,b.user_id')
										->join('__USER__ b ON a.uid = b.id','LEFT')
										->where($where)
										->group('uid')
										->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $AdExpenditureRecord->alias('a')->field('a.uid,sum(a.price) as price,sum(a.money) as money,b.user_id,b.name,b.create_time as register_time,b.status,b.is_withdraw')
									->join('__USER__ b ON a.uid = b.id','LEFT')
									->where($where)
									->group('a.uid')
									->order('sum(a.price) desc')
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
	
	//获取每个用户针对每一个广告带来的总金额
	public function getUserAdEveryLast(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid',4);//用户id
		
		$where['a.uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 10;
		$AdExpenditureRecord = M('AdExpenditureRecord');

		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//类型:1硬广2外链详情3站内
		if($type){
			$where['a.type'] = $type;
		}
		
		$where['price'] = array('gt',0);
		//获取总条数
		$count = $AdExpenditureRecord->alias('a')->field('a.id')->where($where)->group('aid')->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $AdExpenditureRecord->alias('a')->field('a.aid,sum(a.price) as price,sum(a.money) as money')
									->where($where)
									->group('a.aid')
									->order('sum(a.price) desc')
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
	
	//获取每个用户针对文章/视频的带来的金额
	public function getUserArticelLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = 10;
		$ArticleReadRecord = M('ArticleReadRecord');

		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		$tid = I('post.tid');//文章/视频id
		if($tid){
			$where['a.tid'] = $tid;
		}
		
		$status = I('post.status');//用户状态1正常2白名单3黑名单4用户封号
		if($status){
			$where['b.status'] = $status;
		}
		
		$is_withdraw = I('post.is_withdraw');//提现状态1正常0封号
		if($is_withdraw){
			$where['b.is_withdraw'] = $is_withdraw;
		}
		
		//获取总条数
		$count = $ArticleReadRecord->alias('a')
									->field('a.uid,b.user_id')
									->join('__USER__ b ON a.uid = b.id','LEFT')
									->where($where)
									->group('uid')
									->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $ArticleReadRecord->alias('a')->field('a.uid,count(a.id) as num,b.user_id,b.name,b.create_time as register_time,b.status,b.is_withdraw')
									->join('__USER__ b ON a.uid = b.id','LEFT')
									->where($where)
									->group('a.uid')
									->order('count(a.id) desc')
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
	
	//获取每个用户针对每一个文章/视频带来的总金额
	public function getUserArticelEveryLast(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$uid = I('post.uid',4);//用户id
		
		$where['a.uid'] = $uid;
		
		$page = I('post.page',1);
		$page_size = 10;
		$ArticleReadRecord = M('ArticleReadRecord');

		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		//获取总条数
		$count = $ArticleReadRecord->alias('a')->field('a.id')->where($where)->group('tid')->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $ArticleReadRecord->alias('a')->field('a.tid,count(a.id) as num')
									->where($where)
									->group('a.tid')
									->order('count(a.id) desc')
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
	
	//系统检测数据
	public function getFoolUserDataLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = 10;
		$CountFooluserData = M('CountFooluserData');

		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//1文章/视频 2广告
		if($type){
			$where['a.type'] = $type;
		}
		
		$s_type = I('post.s_type');//1ip 2设备信息
		if($s_type){
			$where['a.s_type'] = $s_type;
		}
		
		$status = I('post.status');//1已处理0未处理
		if($status){
			$where['a.status'] = $status;
		}
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id){
			$where['b.user_id'] = $user_id;
		}
		
		$u_status = I('post.u_status');//用户状态1正常2白名单3黑名单4用户封号
		if($u_status != ''){
			$where['b.status'] = $u_status;
		}
		
		$is_withdraw = I('post.is_withdraw');//提现状态1正常0封号
		if($is_withdraw != ''){
			$where['b.is_withdraw'] = $is_withdraw;
		}
		
		//获取总条数
		$count = $CountFooluserData->alias('a')
								   	->field('a.uid,b.name')
									->join('__USER__ b ON a.uid = b.id','LEFT')
									->where($where)
								   	->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountFooluserData->alias('a')->field('a.uid,a.num,a.infos,a.type,a.s_type,a.status,a.create_time,b.name,b.user_id,b.create_time as register_time,b.status,b.is_withdraw,b.masterid')
									->join('__USER__ b ON a.uid = b.id','LEFT')
									->where($where)
									->order('a.num desc')
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
	
	//获取每个人的金币明细数据
	public function getUserGoldInfoLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$page = I('post.page',1);
		$page_size = I('post.page_size',10);
		
		$uid = I('post.uid');//用户id
		$where['uid'] = $uid;
		$UserGoldRecord = M('UserGoldRecord');
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
        
        $type = I('post.type');//1、站内阅读 2、签到3、开启宝箱4、做任务5、提交反馈
		if($type){
			$where['cid'] = $type;
		}
		
		//获取总条数
		$count = $UserGoldRecord->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $UserGoldRecord->field('gold,cid,after_gold,create_time')
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
	
	//获取徒弟收徒排行列表
	public function getUserDiscipleLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		$page = I('post.page',1);
		$page_size = I('post.page_size',10);
		$Disciple = M('Disciple');
        
        $type = I('post.type');//1、徒孙 2、徒弟
		if($type == 1){
			$type = 'masterfatherid';
			$field = 'a.student_uid,count(a.id) as num,a.masterfatherid as uid,sum(a.masterfather_money) as masterfather_money,sum(a.master_money) as master_money,b.name,b.user_id,b.status,b.is_withdraw,sum(CASE WHEN c.status=4 THEN 1 ELSE 0 END) AS status_num,sum(CASE WHEN c.is_withdraw=0 THEN 1 ELSE 0 END) AS is_withdraw_num';
			$join = '__USER__ b ON a.masterfatherid = b.id';
			$join1 = '__USER__ c ON a.student_uid = c.id';
			$where['a.masterfatherid'] = array('gt',0);
		}else{
			$type = 'masterid';
			$field = 'a.student_uid,count(a.id) as num,a.masterid as uid,sum(a.masterfather_money) as masterfather_money,sum(a.master_money) as master_money,b.name,b.user_id,b.status,b.is_withdraw,sum(CASE WHEN c.status=4 THEN 1 ELSE 0 END) AS status_num,sum(CASE WHEN c.is_withdraw=0 THEN 1 ELSE 0 END) AS is_withdraw_num';
			$join = '__USER__ b ON a.masterid = b.id';
			$join1 = '__USER__ c ON a.student_uid = c.id';
			$where['a.masterid'] = array('gt',0);
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('lt',$end_time));
        }
		
		$status = I('post.status');//用户状态1正常2白名单3黑名单4用户封号
		if($status){
			$where['b.status'] = $status;
		}
		
		$is_withdraw = I('post.is_withdraw');//提现状态1正常0封号
		if($is_withdraw){
			$where['b.is_withdraw'] = $is_withdraw;
		}
		
		$user_id = I('post.user_id');//用户user_id
		if($user_id){
			$where['b.user_id'] = $user_id;
		}
		
		//获取总条数
		$count = $Disciple->alias('a')
								   	->field($field)
									->join($join,'LEFT')
									->join($join1,'LEFT')
									->where($where)
									->group('a.'.$type)
								   	->select();
		$count = count($count);
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $Disciple->alias('a')->field($field)
									->join($join,'LEFT')
									->join($join1,'LEFT')
									->where($where)
									->group('a.'.$type)
									->order('count(a.id) desc')
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
	
	
	
	//--------------------------------------------封号数据结束--------------------------------------------
	
	//系统自动检测
	public function SysTesting(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$stime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$User = M('User');
		$UserWithdraw = M('UserWithdraw');
		$where['a.status'] = 0;
		$where['b.status'] = array('neq',4);
		$where['b.is_withdraw'] = 1;
		//获取提现没成功的用户，他们的昨天之前收的徒弟个数num,在昨天活跃的a_num，零钱>=250的个数m_num
		$res = $UserWithdraw->alias('a')
				->field("a.uid,count(c.id) as num,c.student_uid,sum(CASE WHEN d.total_money >= 250 THEN 1 ELSE 0 END) as m_num,sum(CASE WHEN e.create_time >= $stime THEN 1 ELSE 0 END) as a_num")
				->where($where)
				->join('__USER__ b ON a.uid = b.id','LEFT')
				->join("__DISCIPLE__ c ON a.uid = c.masterid and c.create_time < $stime",'LEFT')
				->join('__USER_ACCOUNT__ d ON c.student_uid = d.uid','LEFT')
				->join('__USER_ACTIVE_RECORD__ e ON c.student_uid = e.uid','LEFT')
				->group('a.uid')
				->order("sum(CASE WHEN e.create_time >= $stime THEN 1 ELSE 0 END) desc")
				->select();
				
		$c = count($res);		
		for($i=0;$i<$c;$i++){
			//徒弟数大于10
			if($res[$i]['num'] > 10){
				//收益百分比<5%；
				$bfb = $res[$i]['m_num'] / $res[$i]['num'];
				if($bfb < 0.05){
					//执行用户封号
					$where1['id'] = $res[$i]['uid'];
//					$data['is_withdraw'] = 0;
					$data['sealreason'] = '14,15';
					$User->where($where1)->save($data);
					continue;
				}
				//活跃比（昨天之前来的用户在昨天活跃的）<10%
				$bfb1 = $res[$i]['a_num'] / $res[$i]['num'];
				if($bfb1 < 0.1){
					//执行用户封号
					$where1['id'] = $res[$i]['uid'];
//					$data['is_withdraw'] = 0;
					$data['sealreason'] = '14,16';
					$User->where($where1)->save($data);
					continue;
				}
			}
			
			//判断徒弟数大于5的，同1个手机型号，设备号不一样的
			if($res[$i]['num'] > 5){
				$where4['masterid'] = $res[$i]['uid'];
				$res2 = $User->field('phone_model,device_id')->where($where4)->select();
				$cc = count($res2);
				$aa = 1;
				$bb = 1;
				for($j=1;$j<$cc;$j++){
					
					if($res2[$j]['phone_model'] == $res2[0]['phone_model']){
						$aa += 1;
					}
					
					if($res2[$j]['device_id'] != $res2[0]['device_id']){
						$bb += 1;
					}
				}
				
				if($aa == $cc && $bb == $cc){
					//执行用户封号
					$where1['id'] = $res[$i]['uid'];
					$data['is_withdraw'] = 0;
					$data['sealreason'] = '14,17';
					$User->where($where1)->save($data);
					continue;
				}
			}
			
			//文章，单个设备大于100；或者5个设备点击都大于50的
			$ArticleReadRecord = M('ArticleReadRecord');
			$time24 = time()-86400;
			$where2['uid'] = $res[$i]['uid'];
			$where2['create_time'] = array('egt',$time24);
			$res3 = $ArticleReadRecord->field('count(id) as num')->where($where2)->group('equipment_model')->order('count(id) desc')->limit(0,5)->select();
			if($res3[0]['num'] > 100){//单个设备大于100；
				//执行用户封号
				$where1['id'] = $res[$i]['uid'];
				$data['is_withdraw'] = 0;
				$data['sealreason'] = '14,18';
				$User->where($where1)->save($data);
				continue;
			}else{
				$c2 = count($res3);
				$a2 = 0;
				for($j=0;$j<$c2;$j++){
					if($res3[$j]['num'] > 50){
						$a2 += 1;
					}
				}
				
				if($c2 != 0 && $a2 == $c2){//或者5个设备点击都大于50的
					//执行用户封号
					$where1['id'] = $res[$i]['uid'];
					$data['is_withdraw'] = 0;
					$data['sealreason'] = '14,19';
					$User->where($where1)->save($data);
					continue;
				}
			}
			
			//硬广，单个设备大于100；或者5个设备点击都大于50的
			$AdExpenditureRecord = M('AdExpenditureRecord');
			$where3['uid'] = $res[$i]['uid'];
			$where3['create_time'] = array('egt',$time24);
			$res4 = $AdExpenditureRecord->field('count(id) as num')->where($where3)->group('equipment_model')->order('count(id) desc')->limit(0,5)->select();
			if($res4[0]['num'] > 100){//单个设备大于100；
				//执行用户封号
				$where1['id'] = $res[$i]['uid'];
				$data['is_withdraw'] = 0;
				$data['sealreason'] = '14,20';
				$User->where($where1)->save($data);
				continue;
			}else{
				$c2 = count($res4);
				$a2 = 0;
				for($j=0;$j<$c2;$j++){
					if($res4[$j]['num'] > 50){
						$a2 += 1;
					}
				}
				
				if($c2 != 0 && $a2 == $c2){//或者5个设备点击都大于50的
					//执行用户封号
					$where1['id'] = $res[$i]['uid'];
					$data['is_withdraw'] = 0;
					$data['status'] = 4;
					$data['sealreason'] = '14,21';
					$User->where($where1)->save($data);
					continue;
				}
			}
			
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
}

