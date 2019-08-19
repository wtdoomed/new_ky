<?php
namespace app\mapi\controller;
use think\Db;

//文章管理
class ArticleController extends BaseController {
	
	//获取文章/视频列表
	public function getArticleLists(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$page = I('post.page',1);
		$page_size = I('post.page_size');
		$title = I('post.title');//文章/视频标题
		if($title != ''){
			$where['a.title'] = array('like','%'.$title.'%');
		}
		
		$status = I('post.status');//状态1启用2待审3停用
		if($status != ''){
			$where['a.status'] = $status;
		}
		
		$type = I('post.type');//类型：1文章 2视频
		if($type != ''){
			$where['a.type'] = $type;
		}
		
		$cid = I('post.cid');//类别
		if($cid != ''){
			$where['a.cid'] = $cid;
		}
		
		$pic_type = I('post.pic_type');//1大图2单图3三图
		if($pic_type != ''){
			$where['a.pic_type'] = $pic_type;
		}
		
		$start_time = I('post.start_time');
        $end_time = I('post.end_time');
        if($start_time != '' && $end_time != ''){
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time));
        }
		
		$Article = M('Article');
		//获取总条数
		$count = $Article->alias('a')
								->field('a.cid,b.name as aname')
								->join('__CLASS__ b ON a.cid = b.id','LEFT')
								->where($where)
								->count();
		//总条数除页大小＝总页数（进1取整）
		$total = ceil($count / $page_size);
		//当前页减1乘页大小 ＝ limit的第一个参数（就是从第几条开始取）
		$limitStart = (($page - 1) * $page_size);
		$List = $Article->alias('a')
						->field('a.id,a.cid,a.title,a.pic_type,a.litpic1,a.litpic2,a.litpic3,a.status,a.video_url,a.price,a.rank,a.type,a.create_time,b.name as aname')
						->join('__CLASS__ b ON a.cid = b.id','LEFT')
						->where($where)
//						->order('a.rank desc')
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
	
	//获取文章/视频详情
	public function getArticleDetail(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//文章/视频id
		
		$Article = M('Article');
		$where['id'] = $id;
		$List = $Article->where($where)->find();
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
	//编辑文章/视频
	public function saveArticleInfos(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//文章/视频id
		$title = I('post.title');//标题
		$cid = I('post.cid');//类别id
		$pic_type = I('post.pic_type');//1大图2单图3三图
		$litpic1 = I('post.litpic1');//图1
		$litpic2 = I('post.litpic2');//图2
		$litpic3 = I('post.litpic3');//图3
		$desc = I('post.desc','','');//内容
		$video_url = I('post.video_url');//视频url
		$video_long = I('post.video_long');//视频时长
		$price = I('post.price');//价格（以分为单位）
		$status = I('post.status');//状态1启用2待审3停用
		$rank = I('post.rank');//排序
		$visitnum = I('post.visitnum');//点击数
		$sharenum = I('post.sharenum');//分享数
		$type = I('post.type');//1文章 2视频
		$share_type = I('post.share_type');//可分享类型：0不可分享、1朋友圈、2微信、3qq、4qq空间、5微博、6其他
		
		$data['cid'] = $cid;
		$data['title'] = $title;
		$data['desc'] = $desc;
		$data['pic_type'] = $pic_type;
		$data['litpic1'] = $litpic1;
		$data['litpic2'] = $litpic2;
		$data['litpic3'] = $litpic3;
		$data['price'] = $price;
		$data['video_url'] = $video_url;
		$data['video_long'] = $video_long;
		$data['status'] = $status;
		$data['rank'] = $rank;
		$data['visitnum'] = $visitnum;
		$data['sharenum'] = $sharenum;
		$data['type'] = $type;
		$data['share_type'] = $share_type;
		$data['update_time'] = time();
		$where['id'] = $id;
		$Article = M('Article');
		$res = $Article->where($where)->save($data);
		
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
	public function setArticleStaInfo(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$id = I('post.id');//文章/视频id
		$status = I('post.status');//状态1启用2待审3停用
		
		$data['status'] = $status;
		$data['update_time'] = time();
		$where['id'] = $id;
		$Article = M('Article');
		$Article->where($where)->save($data);
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
	//清除缓存
	public function CleanAdInfoList(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
  		$type = I('post.type');//1：清除文章缓存 2：清除视频缓存
  		if($type == 1){
  			A("Timedtask")->SetArticleListCache();
  			A("Timedtask")->SetArticleListCache1();
  			A("Timedtask")->SetArticleRecommendListCache();
  		}else{
  			A("Timedtask")->SetVideoListCache();
  			A("Timedtask")->SetVideoListCache1();
  			A("Timedtask")->SetVideoRecommendListCache();
  		}
        
        $ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$this->ajaxReturn($ajaxReturn);
	}
	
	//获取昨日爆款热文
	public function getBurstingArticle(){
		$token = I('post.token');//token
		$user_info = $this->IsAppLogin($token);//判断用户是否登录
		
		$title = I('post.title');//文章/视频标题
		if($title != ''){
			$where['b.title'] = array('like','%'.$title.'%');
		}
		
		$starttime = strtotime(date("Y-m-d",strtotime("-1 day")));//昨天开始时间戳
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		$where['a.create_time'] = array(array('egt',$starttime),array('lt',$endtime));
		$ArticleReadRecord = M('ArticleReadRecord');
		$List = $ArticleReadRecord->alias('a')
					->field('count(a.id) as num,a.tid,b.title,b.type')
					->where($where)
					->join('__ARTICLE__ b ON a.tid = b.id','LEFT')
					->group('a.tid')
					->order('count(a.id) desc')
					->limit(0,10)
					->select();
		
		if($List === NULL){
			$List = array();
		}
		
		$ajaxReturn['code'] = 200;
		$ajaxReturn['msg'] = 'SUCCESS';
		$ajaxReturn['data']['lists'] = $List;
		$this->ajaxReturn($ajaxReturn);
	}
	
}

