<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: friend.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'friend';
include_once 'home.php';

class mobile_api {

	public static function common() {
	}

	public static function output() {
		global $_G;
		$variable = array(
			'list' => array_values(mobile_core::getvalues($GLOBALS['list'], array('/^.+?$/'), array('uid', 'username'))),
			'count' => $GLOBALS['count'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

<<<<<<< HEAD
=======
?>
>>>>>>> 8cd3387e (migrating from https://gitee.com/kuingggg/DiscuzX/tree/test-0726)
