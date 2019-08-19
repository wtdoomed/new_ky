<?php
namespace app\mapi\controller;
use think\Db;

//后台系统数据统计
class SystemDataController extends BaseController {
	
	//获取渠道数据统计
	public function getChannelDatalists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$uchannel = I('post.channel');//用户渠道
		if($uchannel != ''){
			$where['channel'] = $uchannel;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountChannelData = M('CountChannelData');
		//获取总条数
		$count = $CountChannelData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountChannelData
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
	
	//获取大表数据统计
	public function getBigDatalists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountBigData = M('CountBigData');
		//获取总条数
		$count = $CountBigData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountBigData
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
	
	//获取外链数据统计
	public function getChainDatalists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountChainData = M('CountChainData');
		//获取总条数
		$count = $CountChainData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountChainData
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
	
	//获取系统财务数据
	public function getSystemData(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$type = I('post.type',1);//1每日2每月
		if($type != ''){
			$where['type'] = $type;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountSystemData = M('CountSystemData');
		//获取总条数
		$count = $CountSystemData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountSystemData
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
	
	//获取收徒入口数据
	public function getRouteData(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$one_type = I('post.one_type');//收徒入口
		if($one_type != ''){
			$where['one_type'] = $one_type;
		}
		
		$second_type = I('post.second_type');//收徒方式
		if($second_type != ''){
			$where['second_type'] = $second_type;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountRouteData = M('CountRouteData');
		//获取总条数
		$count = $CountRouteData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountRouteData->field('num,create_time,
										CASE WHEN one_type = 1 THEN "收徒页" 
											 WHEN one_type = 2 THEN "收徒页-面对面" 
											 WHEN one_type = 3 THEN "收徒页-晒收入"  
											 WHEN one_type = 4 THEN "个人中心"  
											 WHEN one_type = 5 THEN "个人中新-面对面" 
											 WHEN one_type = 6 THEN "我的零钱-晒收入" 
											 WHEN one_type = 7 THEN "我的金币-晒收入" 
											 WHEN one_type = 8 THEN "好友列表-唤醒好友" 
											 WHEN one_type = 9 THEN "好友详情-唤醒好友" 
											 WHEN one_type = 10 THEN "开宝箱" 
											 WHEN one_type = 11 THEN "手动绑定" 
											 WHEN one_type = 12 THEN "一键收徒" 
											 WHEN one_type = 13 THEN "外链 “点我领钱”" 
											 WHEN one_type = 14 THEN "外链 “阅读全文”" 
											 WHEN one_type = 15 THEN "外链 “弹窗”" 
											 ELSE "" END as one_name,
										CASE WHEN second_type = 1 THEN "微信" 
											 WHEN second_type = 2 THEN "朋友圈" 
											 WHEN second_type = 3 THEN "qq"  
											 WHEN second_type = 4 THEN "qq空间"  
											 WHEN second_type = 5 THEN "微博" 
											 WHEN second_type = 6 THEN "短信" 
											 WHEN second_type = 7 THEN "复制链接" 
											 WHEN second_type = 8 THEN "邮件" 
											 WHEN second_type = 9 THEN "群发邀请" 
											 WHEN second_type = 10 THEN "系统分享" 
											 WHEN second_type = 11 THEN "扫码" 
											 ELSE "" END as second_name
									')
						->where($where)
						->order('num desc')
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
	
	//获取广告数据
	public function getAdInfoData(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$cid = I('post.cid');//广告类型
		if($cid != ''){
			$where['b.cid'] = $cid;
		}
		
		$pid = I('post.pid');//广告位
		if($pid != ''){
			$where['b.pid'] = $pid;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdfeeData = M('CountAdfeeData');
		//获取总条数
		$count = $CountAdfeeData->alias('a')
						->field('a.aid,b.cid,b.pid,c.name as pname,d.name as cname')
						->where($where)
						->join('__ADVERTISEMENT__ b ON a.aid = b.id','LEFT')
						->join('__AD_POSITION__ c ON b.pid = c.id','LEFT')
						->join('__AD_CLASS__ d ON b.cid = d.id','LEFT')
						->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountAdfeeData->alias('a')
						->field('a.aid,a.ad_pvnum,a.ad_ipnum,a.price,a.ad_money,a.create_time,b.cid,b.pid,b.title,c.name as pname,d.name as cname')
						->where($where)
						->join('__ADVERTISEMENT__ b ON a.aid = b.id','LEFT')
						->join('__AD_POSITION__ c ON b.pid = c.id','LEFT')
						->join('__AD_CLASS__ d ON b.cid = d.id','LEFT')
						->order('a.ad_ipnum desc')
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
	
	//获取广告位数据
	public function getAdposiData(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$pid = I('post.pid');//广告位
		if($pid != ''){
			$where['a.pid'] = $pid;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdpositionData = M('CountAdpositionData');
		//获取总条数
		$count = $CountAdpositionData->alias('a')
						->field('a.pid,c.name as pname')
						->where($where)
						->join('__AD_POSITION__ c ON a.pid = c.id','LEFT')
						->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountAdpositionData->alias('a')
						->field('a.pid,a.pv_num,a.ip_num,a.create_time,c.name as pname')
						->where($where)
						->join('__AD_POSITION__ c ON a.pid = c.id','LEFT')
						->order('a.ip_num desc')
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
	
	//获取广告类型数据
	public function getAdclassData(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$cid = I('post.cid');//广告类型
		if($cid != ''){
			$where['a.cid'] = $cid;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdclassData = M('CountAdclassData');
		//获取总条数
		$count = $CountAdclassData->alias('a')
						->field('a.cid,a.ad_pvnum,a.ad_ipnum,a.price,a.ad_money,a.create_time,d.name as cname')
						->where($where)
						->join('__AD_CLASS__ d ON a.cid = d.id','LEFT')
						->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountAdclassData->alias('a')
						->field('a.cid,a.ad_pvnum,a.ad_ipnum,a.price,a.ad_money,a.create_time,d.name as cname')
						->where($where)
						->join('__AD_CLASS__ d ON a.cid = d.id','LEFT')
						->order('a.ad_ipnum desc')
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
	
	//获取用户行为数据
	public function getUserBehaviorlists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$type = I('post.type');//类型
		if($type != ''){
			$where['type'] = $type;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountUserbehaviorData = M('CountUserbehaviorData');
		//获取总条数
		$count = $CountUserbehaviorData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountUserbehaviorData
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
	
	//获取系统金币支出情况
	public function getSysgoldconsume(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountGoldconsumeData = M('CountGoldconsumeData');
		//获取总条数
		$count = $CountGoldconsumeData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountGoldconsumeData
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
	
	//获取系统零钱支出统计
	public function getSysmoneyconsume(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountMoneyconsumeData = M('CountMoneyconsumeData');
		//获取总条数
		$count = $CountMoneyconsumeData->where($where)->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $CountMoneyconsumeData
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
	
	//----------------------------------导出-------------------------------------------------------------------------------------
	
	//导出系统零钱支出统计
	public function exportSysmoneyconsume(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountMoneyconsumeData = M('CountMoneyconsumeData');
		$res = $CountMoneyconsumeData
						->where($where)
						->order('create_time desc')
						->limit($limitStart,$page_size)
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '外部阅读-文章');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '收徒');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '做任务');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '外部阅读(徒弟)');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '外部阅读(徒孙)');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '金币兑换零钱');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '外部阅读-硬广');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '活动奖励');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '系统奖励');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '总支出');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['type1']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['type2']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['type3']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['type4']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['type5']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['type6']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, ($res[$i-2]['type7']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, ($res[$i-2]['type8']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, ($res[$i-2]['type9']/100));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, ($res[$i-2]['countnum']/100));
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'系统零钱支出统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出系统金币支出情况
	public function exportSysgoldconsume(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountGoldconsumeData = M('CountGoldconsumeData');
		$res = $CountGoldconsumeData
						->where($where)
						->order('create_time desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '站内阅读');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '签到');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '开启宝箱');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '做任务');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '时段奖励');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '总支出');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['type1']/10000));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['type2']/10000));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['type3']/10000));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['type4']/10000));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['type7']/10000));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['countnum']/10000));
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'系统金币支出情况.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出用户行为数据
	public function exportUserBehavior(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$type = I('get.type');//类型
		if($type != ''){
			$where['type'] = $type;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		$CountUserbehaviorData = M('CountUserbehaviorData');
		$res = $CountUserbehaviorData
						->where($where)
						->order('create_time desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '类型');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '注册');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '次日活跃');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '次日活跃比');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '绑定手机号');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '绑定手机号比');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '阅读文章（时间维度：当日）');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '阅读文章比');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '阅读文章拿到奖励（时间维度：当日）');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '阅读文章拿到奖励比');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '分享文章（时间维度：当日）');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '分享文章比');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '分享收徒（时间维度：当日）');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', '分享收徒比');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', '绑定公众号');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', '绑定公众号比');
        $objPHPExcel->getActiveSheet()->setCellValue('R1', '提现1元');
        $objPHPExcel->getActiveSheet()->setCellValue('S1', '提现1元比');
        $objPHPExcel->getActiveSheet()->setCellValue('T1', '总收益1元');
        $objPHPExcel->getActiveSheet()->setCellValue('U1', '总收益1元比');
        $objPHPExcel->getActiveSheet()->setCellValue('V1', '总收益1<x<2元');
        $objPHPExcel->getActiveSheet()->setCellValue('W1', '总收益1<x<2元比');
        $objPHPExcel->getActiveSheet()->setCellValue('X1', '总收益=2元');
        $objPHPExcel->getActiveSheet()->setCellValue('Y1', '总收益=2元比');
        $objPHPExcel->getActiveSheet()->setCellValue('Z1', '总收益>2元');
        $objPHPExcel->getActiveSheet()->setCellValue('AA1', '总收益>2元比');
        $objPHPExcel->getActiveSheet()->setCellValue('AB1', '总金币>=1050');
        $objPHPExcel->getActiveSheet()->setCellValue('AC1', '总金币>=1050比');
        $objPHPExcel->getActiveSheet()->setCellValue('AD1', '总金币<1050');
        $objPHPExcel->getActiveSheet()->setCellValue('AE1', '总金币<1050比');
        $objPHPExcel->getActiveSheet()->setCellValue('AF1', '没有师傅的');
        $objPHPExcel->getActiveSheet()->setCellValue('AG1', '没有师傅的比');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['type'] == 1 ? '市场' : '收徒'));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['register']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['cihuonum']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['cihuobi']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['bdphonenum']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['bdphonebi']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, ($res[$i-2]['readartnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, ($res[$i-2]['readartbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, ($res[$i-2]['readartjlnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, ($res[$i-2]['readartjlbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, ($res[$i-2]['shareartnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, ($res[$i-2]['shareartbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, ($res[$i-2]['sharestnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, ($res[$i-2]['sharestbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, ($res[$i-2]['bggzhnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, ($res[$i-2]['bggzhbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, ($res[$i-2]['txyynum']));
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, ($res[$i-2]['txyybi']));
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, ($res[$i-2]['zsyyynum']));
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, ($res[$i-2]['zsyyybi']));
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, ($res[$i-2]['dyyxyenum']));
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $i, ($res[$i-2]['dyyxyebi']));
            $objPHPExcel->getActiveSheet()->setCellValue('X' . $i, ($res[$i-2]['zsyeynum']));
            $objPHPExcel->getActiveSheet()->setCellValue('Y' . $i, ($res[$i-2]['zsyeybi']));
            $objPHPExcel->getActiveSheet()->setCellValue('Z' . $i, ($res[$i-2]['zsydyenum']));
            $objPHPExcel->getActiveSheet()->setCellValue('AA' . $i, ($res[$i-2]['zsydyebi']));
            $objPHPExcel->getActiveSheet()->setCellValue('AB' . $i, ($res[$i-2]['zjbdywsnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('AC' . $i, ($res[$i-2]['zjbdywsbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('AD' . $i, ($res[$i-2]['zjbxywsnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('AE' . $i, ($res[$i-2]['zjbxywsbi']));
            $objPHPExcel->getActiveSheet()->setCellValue('AF' . $i, ($res[$i-2]['nomastnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('AG' . $i, ($res[$i-2]['nomastbi']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'市场&收徒数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出广告类型数据
	public function exportAdclassData(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$cid = I('get.cid');//广告类型
		if($cid != ''){
			$where['a.cid'] = $cid;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdclassData = M('CountAdclassData');
		$res = $CountAdclassData->alias('a')
						->field('a.cid,a.ad_pvnum,a.ad_ipnum,a.price,a.ad_money,a.create_time,d.name as cname')
						->where($where)
						->join('__AD_CLASS__ d ON a.cid = d.id','LEFT')
						->order('a.ad_ipnum desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '广告类型');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '今日ip');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '今日pv');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '单价');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '广告金额');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['cname']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['ad_ipnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['ad_pvnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['price']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['ad_money']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'广告类型数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出广告位数据
	public function exportAdposiData(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$pid = I('get.pid');//广告位
		if($pid != ''){
			$where['a.pid'] = $pid;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdpositionData = M('CountAdpositionData');
		$res = $CountAdpositionData->alias('a')
						->field('a.pid,a.pv_num,a.ip_num,a.create_time,c.name as pname')
						->where($where)
						->join('__AD_POSITION__ c ON a.pid = c.id','LEFT')
						->order('a.ip_num desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '广告位');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '今日ip');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '今日pv');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['pname']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['ip_num']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['pv_num']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'广告位数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出广告数据
	public function exportAdInfoData(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$cid = I('get.cid');//广告类型
		if($cid != ''){
			$where['b.cid'] = $cid;
		}
		
		$pid = I('get.pid');//广告位
		if($pid != ''){
			$where['b.pid'] = $pid;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountAdfeeData = M('CountAdfeeData');
		$res = $CountAdfeeData->alias('a')
						->field('a.aid,a.ad_pvnum,a.ad_ipnum,a.price,a.ad_money,a.create_time,b.cid,b.pid,b.title,c.name as pname,d.name as cname')
						->where($where)
						->join('__ADVERTISEMENT__ b ON a.aid = b.id','LEFT')
						->join('__AD_POSITION__ c ON b.pid = c.id','LEFT')
						->join('__AD_CLASS__ d ON b.cid = d.id','LEFT')
						->order('a.ad_ipnum desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '广告标题');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '广告类型');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '广告位');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '单价');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '今日ip');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '今日pv');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '广告金额');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['title']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['cname']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['pname']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['price']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['ad_ipnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['ad_pvnum']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, ($res[$i-2]['ad_money']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'广告数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出收徒入口数据
	public function exportRouteData(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$one_type = I('get.one_type');//收徒入口
		if($one_type != ''){
			$where['one_type'] = $one_type;
		}
		
		$second_type = I('get.second_type');//收徒方式
		if($second_type != ''){
			$where['second_type'] = $second_type;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountRouteData = M('CountRouteData');
		$res = $CountRouteData->field('num,create_time,
										CASE WHEN one_type = 1 THEN "收徒页" 
											 WHEN one_type = 2 THEN "收徒页-面对面" 
											 WHEN one_type = 3 THEN "收徒页-晒收入"  
											 WHEN one_type = 4 THEN "个人中心"  
											 WHEN one_type = 5 THEN "个人中新-面对面" 
											 WHEN one_type = 6 THEN "我的零钱-晒收入" 
											 WHEN one_type = 7 THEN "我的金币-晒收入" 
											 WHEN one_type = 8 THEN "好友列表-唤醒好友" 
											 WHEN one_type = 9 THEN "好友详情-唤醒好友" 
											 WHEN one_type = 10 THEN "开宝箱" 
											 WHEN one_type = 11 THEN "手动绑定" 
											 WHEN one_type = 12 THEN "一键收徒" 
											 WHEN one_type = 13 THEN "外链 “点我领钱”" 
											 WHEN one_type = 14 THEN "外链 “阅读全文”" 
											 WHEN one_type = 15 THEN "外链 “弹窗”" 
											 ELSE "" END as one_name,
										CASE WHEN second_type = 1 THEN "微信" 
											 WHEN second_type = 2 THEN "朋友圈" 
											 WHEN second_type = 3 THEN "qq"  
											 WHEN second_type = 4 THEN "qq空间"  
											 WHEN second_type = 5 THEN "微博" 
											 WHEN second_type = 6 THEN "短信" 
											 WHEN second_type = 7 THEN "复制链接" 
											 WHEN second_type = 8 THEN "邮件" 
											 WHEN second_type = 9 THEN "群发邀请" 
											 WHEN second_type = 10 THEN "系统分享" 
											 WHEN second_type = 11 THEN "扫码" 
											 ELSE "" END as second_name
									')
						->where($where)
						->order('num desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '入口');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '方式');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '个数');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['one_name']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['second_name']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['num']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'收徒入口数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出系统财务数据
	public function exportSystemData(){
		$token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$type = I('get.type',1);//1每日2每月
		if($type != ''){
			$where['type'] = $type;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
        
		$CountSystemData = M('CountSystemData');
		$res = $CountSystemData
						->where($where)
						->order('create_time desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '今日注册用户');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '系统总用户');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '提现金额');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '金币支出');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '零钱支出');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '广告费收入');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '利润（广告费-金币-零钱）');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '实际利润（广告费-提现金额）');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['register_user']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['count_user']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['withdraw_money']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['gold_count']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['money_count']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['admoney_count']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, ($res[$i-2]['profit_money']));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, ($res[$i-2]['actual_profit_money']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'系统财务数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
	}
	
	//导出外链数据
	public function exportChainData(){
        $token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		$CountChainData = M('CountChainData');
		$res = $CountChainData
						->where($where)
						->order('create_time desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '文章分享人数');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '视频分享人数');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '收徒分享人数');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '注册人数');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['asharenum']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['vsharenum']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['dsharenum']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['registernum']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'外链数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
	
	//导出大表数据
	public function exportBigData(){
        $token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		$CountBigData = M('CountBigData');
		$res = $CountBigData
						->where($where)
						->order('create_time desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '登录');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '注册');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '签到');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '内部阅读（每次加50金币）');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '收徒');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '做任务');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '平台活动');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '时段奖励');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '分享文章');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '开启宝箱');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '分享收徒页');
        $objPHPExcel->getActiveSheet()->setCellValue('M1', '活跃(未登录)');
        $objPHPExcel->getActiveSheet()->setCellValue('N1', '活跃(已登录)');
        $objPHPExcel->getActiveSheet()->setCellValue('O1', '内部打开文章/视频详情页');
        $objPHPExcel->getActiveSheet()->setCellValue('P1', '分享晒收入');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1', '分享唤醒徒弟');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['type1']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['type2']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['type3']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['type4']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['type5']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['type6']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $res[$i-2]['type7']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, ($res[$i-2]['type8']));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, ($res[$i-2]['type9']));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, ($res[$i-2]['type10']));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, ($res[$i-2]['type11']));
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, ($res[$i-2]['type12']));
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, ($res[$i-2]['type16']));
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, ($res[$i-2]['type13']));
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, ($res[$i-2]['type14']));
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, ($res[$i-2]['type15']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'大表数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
	
	//导出渠道数据
	public function exportChannelData(){
        $token = I('get.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$uchannel = I('get.channel');//用户渠道
		if($uchannel != ''){
			$where['channel'] = $uchannel;
		}
		
		$start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time != '' && $end_time != ''){
            $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$CountChannelData = M('CountChannelData');
		$res = $CountChannelData
						->where($where)
						->order('create_time desc')
						->select();
						
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Writer.PHPExcel_Writer_Excel2007');
        $objPHPExcel = new \PHPExcel();
        //直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        //设置单元格的值
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '渠道名称');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '注册数');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '激活数');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '累计用户');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '文章阅读IP数（站内）');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '文章阅读IP数（站外）');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '广告IP数（站内站外总和）');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '用户产出（广告费）');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '用户成本（金币）');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '用户成本（钱）');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '获取外链给钱的ip（点开全文并阅读的ip）');

        $count = count($res);

        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, date('Y-m-d',$res[$i-2]['create_time']));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($res[$i-2]['name']));
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($res[$i-2]['register_user']));
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($res[$i-2]['active_user']));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($res[$i-2]['count_user']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($res[$i-2]['ar_ip_within']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, ($res[$i-2]['ar_ip_abroad']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $res[$i-2]['ad_ip']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, ($res[$i-2]['ad_money']));
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, ($res[$i-2]['cost_money']));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, ($res[$i-2]['cost_gold']));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, ($res[$i-2]['valid_ip']));

        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.date('Y-m-d',time()).'渠道数据.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

}

