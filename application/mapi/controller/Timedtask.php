<?php
namespace app\mapi\controller;
use think\Db;

//文章&视频主动缓存数据－系统定时任务
class TimedtaskController extends BaseController {
	
	//主动缓存所有类别下的-文章（每天02:10）
	public function SetArticleListCache(){
		//获取所有文章类别
		$Class = M('Class');
		$whe['pid'] = 1;//1文章2视频
		$whe['id'] = array('neq',42);
		$claList = $Class->field('id')->where($whe)->select();
		
		$Article = M('Article');
		for($i=0;$i<count($claList);$i++){
			$where['a.cid'] = $claList[$i]['id'];
			$where['a.status'] = 1;
			$res = $Article->alias('a')
						   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.litpic2,a.litpic3,a.price,a.visitnum,a.publish_time,b.name')
						   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
						   ->where($where)
						   ->order('a.publish_time desc')
						   ->limit(0,400)
						   ->select();
			if($res){
				$this->redis->set('WT_AppArticleList1'.$claList[$i]['id'],json_encode($res));
			}
		}
	}
	
	//主动缓存所有类别下的-视频（每天02:13）(要改为热点类型的)
	public function SetVideoListCache(){
		//获取所有文章类别
		$Class = M('Class');
		$whe['pid'] = 2;//1文章2视频
		$whe['id'] = array('neq',43);
		$claList = $Class->field('id')->where($whe)->select();
		
		$Article = M('Article');
		for($i=0;$i<count($claList);$i++){
			$where['a.cid'] = $claList[$i]['id'];
			$where['a.status'] = 1;
			$res = $Article->alias('a')
						   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.video_url,a.video_long,a.price,a.visitnum,a.publish_time,b.name')
						   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
						   ->where($where)
						   ->order('a.publish_time desc')
						   ->limit(0,400)
						   ->select();
			if($res){
				$this->redis->set('WT_AppArticleList2'.$claList[$i]['id'],json_encode($res));
			}
		}
	}
	
	//设置两个特殊分类 推荐(-1)、最新(-2) －文章（每天02:15）(要改为热点类型的)
	public function SetArticleListCache1(){
		$Article = M('Article');
		//推荐--市场用户(-1)
		$where['a.status'] = 1;
		$where['a.cid'] = array('in','4,6,13,25');
		$res = $Article->alias('a')
					   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.litpic2,a.litpic3,a.price,a.visitnum,a.publish_time,b.name')
					   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
					   ->where($where)
					   ->order('a.publish_time desc')
					   ->limit(0,400)
					   ->select();
		if($res){
			$this->redis->set('WT_AppArticleList1-1',json_encode($res));
		}
		
		//推荐--收徒用户(-2)
		$where['a.cid'] = array('in','4,9,14,16,3');
		$res = $Article->alias('a')
					   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.litpic2,a.litpic3,a.price,a.visitnum,a.publish_time,b.name')
					   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
					   ->where($where)
					   ->order('a.publish_time desc')
					   ->limit(0,400)
					   ->select();
		if($res){
			$this->redis->set('WT_AppArticleList1-2',json_encode($res));
		}
	}
	
	//设置两个特殊分类 推荐(-1)、最新(-2) －视频（每天02:17）
	public function SetVideoListCache1(){
		$Article = M('Article');
		//推荐(-1)
		$where['a.status'] = 1;
		$where['a.cid'] = 43;
		$res = $Article->alias('a')
					   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.video_url,a.video_long,a.price,a.visitnum,a.publish_time,b.name')
					   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
					   ->where($where)
					   ->order('a.publish_time desc')
					   ->limit(0,400)
					   ->select();
		if($res){
			$this->redis->set('WT_AppArticleList2-1',json_encode($res));
		}
	}
	
	//主动缓存所有类别下的-相关推荐-文章（每天02:20）
	public function SetArticleRecommendListCache(){
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		//获取所有文章类别
		$Class = M('Class');
		$whe['pid'] = 1;//1文章2视频
		$claList = $Class->field('id')->where($whe)->select();
		
		$Article = M('Article');
		for($i=0;$i<count($claList);$i++){
			$where['a.cid'] = $claList[$i]['id'];
			$where['a.status'] = 1;
			$where['a.pic_type'] = array('gt',1);
			$where['a.create_time'] = array('lt',$endtime);
			$res = $Article->alias('a')
						   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.litpic2,a.litpic3,a.price,a.visitnum,a.publish_time,b.name')
						   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
						   ->where($where)
						   ->order('a.publish_time desc')
						   ->limit(0,400)
						   ->select();
			if($res){
				$this->redis->set('WT_AppArticleRecommendList1'.$claList[$i]['id'],json_encode($res));
			}
		}
	}
	
	//主动缓存所有类别下的-相关推荐-视频（每天02:25）
	public function SetVideoRecommendListCache(){
		$endtime = strtotime(date('Ymd', time()));//今天开始时间戳
		//获取所有文章类别
		$Class = M('Class');
		$whe['pid'] = 2;//1文章2视频
		$claList = $Class->field('id')->where($whe)->select();
		
		$Article = M('Article');
		for($i=0;$i<count($claList);$i++){
			$where['a.cid'] = $claList[$i]['id'];
			$where['a.status'] = 1;
			$where['a.pic_type'] = 2;
			$where['a.create_time'] = array('lt',$endtime);
			$res = $Article->alias('a')
						   ->field('a.id,a.title,a.cid,a.pic_type,a.litpic1,a.video_url,a.video_long,a.price,a.visitnum,a.publish_time,b.name')
						   ->join('__CLASS__ b ON a.cid = b.id','LEFT')
						   ->where($where)
						   ->order('a.publish_time desc')
						   ->limit(0,400)
						   ->select();
			if($res){
				$this->redis->set('WT_AppArticleRecommendList2'.$claList[$i]['id'],json_encode($res));
			}
		}
	}

}

