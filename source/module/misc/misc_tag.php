<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_tag.php 32232 2012-12-03 08:57:08Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = explode(',',$_GET['id']);
$type = trim($_GET['type']);
$name = trim($_GET['name']);
$page = intval($_GET['page']);
if($type == 'countitem') {
	$num = 0;
	if($id) {
		$num = C::t('common_tagitem')->count_by_tagid($id);
	}
	include_once template('tag/tag');
	exit();
}
$taglang = lang('tag/template', 'tag');
if(!empty($id) && intval($id[0]) || $name) {
	$tpp = 20;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;
	if($name) {
		$tagname = $name;
		$name = array_map('trim',explode(',', $name));
		foreach ($name as $value) {
			if(!preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $value) || strlen($value) > 20) {
				showmessage('parameters_error');
			}
		}
		$tags = C::t('common_tag')->fetch_info(0, $name);
		if(count($tags) != count($name)) {
			showmessage('label_error');
		}
		$id = array();
		foreach($tags as $tag) {
			$id[] = $tag['tagid'];
		}
	}else{
		$id = array_map('intval',$id);
		$tags = C::t('common_tag')->fetch_info($id);
		if(count($tags) != count($id)) {
			showmessage('label_error');
		}
		$tagnames = array();
		foreach($tags as $tag) {
			$tagnames[] = $tag['tagname'];
		}
		$tagname = implode(',', $tagnames);
	}
	foreach($tags as $tag) {
		if($tag['status'] == 1) {
			showmessage('tag_closed');
		}
	}
	$navtitle = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metakeywords = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metadescription = $tagname ? $taglang.' - '.$tagname : $taglang;


	$count = '';
	$summarylen = 300;

	$showtype = 'thread';
	$tidarray = $threadlist = array();
	$sql_parts = array();
	foreach($id as $tagid) {
		$sql_parts[] = '(SELECT itemid FROM '.DB::table('common_tagitem').' WHERE tagid='.$tagid.' AND idtype=\'tid\')';
	}
	$sql = implode(' INTERSECT ', $sql_parts);
	$count = DB::result_first("SELECT count(*) FROM ($sql) t");
	if($count) {
		$query = DB::fetch_all($sql . ' ORDER BY itemid DESC LIMIT '.$start_limit.','.$tpp);
		foreach($query as $result) {
			$tidarray[$result['itemid']] = $result['itemid'];
		}
		$threadlist = getthreadsbytids($tidarray);
	}
	$multipage = multi($count, $tpp, $page, 'misc.php?mod=tag&id='.implode(',',$id).'&type=thread');

	include_once template('tag/tagitem');

} else {
	$navtitle = $metakeywords = $metadescription = $taglang;
	$viewthreadtags = 1000;
	$tagarray = array();
	$query = C::t('common_tag')->fetch_all_by_status(0, '', $viewthreadtags, 0, 0, 'DESC');
	foreach($query as $result) {
		$tagarray[] = $result;
	}
	include_once template('tag/tag');
}

function getthreadsbytids($tidarray) {
	global $_G;

	$threadlist = array();
	if(!empty($tidarray)) {
		loadcache('forums');
		include_once libfile('function_misc', 'function');
		$fids = array();
		foreach(C::t('forum_thread')->fetch_all_by_tid($tidarray) as $result) {
			if($result['displayorder'] >= 0){
				if(!isset($_G['cache']['forums'][$result['fid']]['name'])) {
					$fids[$result['fid']][] = $result['tid'];
				} else {
					$result['name'] = $_G['cache']['forums'][$result['fid']]['name'];
				}
				$threadlist[$result['tid']] = procthread($result);
			}
		}
		if(!empty($fids)) {
			foreach(C::t('forum_forum')->fetch_all_by_fid(array_keys($fids)) as $fid => $forum) {
				foreach($fids[$fid] as $tid) {
					$threadlist[$tid]['forumname'] = $forum['name'];
				}
			}
		}
	}
	return $threadlist;
}

function getblogbyid($blogidarray) {
	global $_G, $summarylen;

	$bloglist = array();
	if(!empty($blogidarray)) {
		$data_blog = C::t('home_blog')->fetch_all_blog($blogidarray, 'dateline', 'DESC');
		$data_blogfield = C::t('home_blogfield')->fetch_all($blogidarray);

		require_once libfile('function/spacecp');
		require_once libfile('function/home');
		$classarr = array();
		foreach($data_blog as $curblogid => $result) {
			$result = array_merge($result, (array)$data_blogfield[$curblogid]);
			$result['dateline'] = dgmdate($result['dateline']);
			$classarr = getclassarr($result['uid']);
			$result['classname'] = $classarr[$result['classid']]['classname'];
			if($result['friend'] == 4) {
				$result['message'] = $result['pic'] = '';
			} else {
				$result['message'] = getstr($result['message'], $summarylen, 0, 0, 0, -1);
			}
			$result['message'] = preg_replace("/&[a-z]+\;/i", '', $result['message']);
			if($result['pic']) {
				$result['pic'] = pic_cover_get($result['pic'], $result['picflag']);
			}
			$bloglist[] = $result;
		}
	}
	return $bloglist;
}
?>