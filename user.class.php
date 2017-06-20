<?php
include_once "friends.class.php";
222222222222222
3333333333333
require_once ROOT_PATH . "modules/mall/mall.class.php";
class userClass extends friendsClass {

	const ERROR = '操作有误，请不要乱操作';
	const TYPE_NAME_NO_EMPTY = '类型名称不能为空';
	const USERLOGIN_USERNAME_NO_EMPTY = '用户名不能为空';
	const USERLOGIN_PASSWORD_NO_EMPTY = '密码不能为空';
	const USERLOGIN_USERNAME_PASSWORD_NO_RIGHT = '用户名或密码错误';
	const USER_ADD_LONG_USERNAME = '用户名长度不能超过30个字符';
	const UESR_UCENTER_NO_RIGHT = 'Ucenter不能同步注册信息';
	const SENDEMAIL_EMAIL_NO_EMPTY = '找不到邮箱';
	const USER_REG_EMAIL_EXIST = '邮箱已经存在';
	const USER_REG_PHONE_EXIST = '手机已经存在';
	const USER_REG_USERNAME_EXIST = '用户名已经存在';
	const USER_REG_ERROR = '用户注册失败，请跟管理员联系';
	const USER_PROTECTION_ANSWER_NO_EMPTY = '用密码保护答案不能为空';

	function userClass() {

		global $mysql, $module;
		$this->mysql = $mysql;
		$this->ip = ip_address();
		$this->is_uc = false;
		$this->is_open_vip = false;
	}

	function check_login($res = "no", $msg = "") {
		global $magic;
		if ($res == "no" && $_SESSION['adminname'] == "") {
			$tpl = "admin_login.html";
			$magic->display($tpl);
			exit;
		}
	}

	function CheckUsernamePassword($data = array()) {
		global $mysql;
		$password = $data['password'];
		$user_id = $data['user_id'];
		$_sql = "";

		$sql = "select * from `{user}` where  user_id = '{$user_id}' and password='" . md5($password) . "'";
		$result = $mysql->db_fetch_array($sql);
		if ($result == false) {
			return false;
		}

		return true;
	}
	//添加手机号和密码对应的检查
	function CheckPhonePassword($phone, $pass) {
		return false;
		global $mysql;
		if (preg_match('/\d{11}/', $phone)) {
			$sql = "select user_id from `{authentication}` where status=1 and phone='" . $phone . "'";
			$u = $mysql->db_fetch_array($sql);
			if ($u['user_id'] > 0) {
				$sql = 'select  `password` as pass from `{user}`  where user_id=' . $u['user_id'];
				$p = $mysql->db_fetch_array($sql);
				if (md5($pass) == $p['pass']) {
					return $u['user_id'];
				} else {
					return false;
				}

			} else {
				return false;
			}

		} else {
			return false;
		}

	}

	function CheckPhone($data = array()) {
		global $mysql;
		$phone = $data['phone'];
		$_sql = "";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql = " and user_id!= {$data['user_id']}";
		}
		$sql = "select * from `{authentication}` where  phone = '{$phone}' $_sql";
		$result = $mysql->db_fetch_array($sql);

		if ($result == false) {
			return false;
		}

		return true;
	}

	function CheckEmail($data = array()) {
		global $mysql;
		$email = $data['email'];
		$_sql = "";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql = " and user_id!= {$data['user_id']}";
		}
		$sql = "select * from `{authentication}` where  email = '{$email}' $_sql";
		$result = $mysql->db_fetch_array($sql);

		if ($result == false) {
			return false;
		}

		return true;
	}

	function CheckInvite($data = array()) {
		global $mysql;
		$invite_no = $data['invite_no'];
		$_sql = "";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql = " and user_id!= {$data['user_id']}";
		}
		$sql = "select * from `{user}` where  invite_no = '{$invite_no}' $_sql";
		$result = $mysql->db_fetch_array($sql);
		if ($result == false) {
			return false;
		}

		return true;
	}

	function CheckUsername($data = array()) {
		global $mysql;
		$username = $data['username'];
		$_sql = "";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql = " and user_id!= {$data['user_id']}";
		}
		$sql = "select * from `{user}` where  username = '{$username}' $_sql";
		$result = $mysql->db_fetch_array($sql);
		if ($result == false) {
			return false;
		}

		return true;
	}

	function CheckIdcard($data = array()) {
		global $mysql;
		$card_id = $data['card_id'];
		$_sql = "";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql = " and user_id!= {$data['user_id']}";
		}
		$sql = "select * from `{authentication}` where  card_id  = '{$card_id}' $_sql";
		$result = $mysql->db_fetch_array($sql);
		if ($result == false) {
			return false;
		}

		return true;
	}

	function CheckUsernameEmail($data = array()) {
		global $mysql;
		$email = $data['email'];
		$username = $data['username'];
		$user_id = $data['user_id'];
		$_sql = "";
		if ($user_id != "") {
			$_sql = " and u.user_id!={$user_id}";
		}
		$sql = "select * from `{user}` as u,`{authentication}` as a where  (a.email = '{$email}' or u.username = '{$username}') and a.user_id=u.user_id  $_sql";
		$result = $mysql->db_fetch_array($sql);
		if ($result == false) {
			return false;
		}

		return true;
	}

	function Login($data = array()) {
		global $mysql;
		$reInfo = $this->chkUserLoginTime($data['username']);
		if ($reInfo !== true) {return $reInfo;}
		$user_id = isset($data['user_id']) ? $data['user_id'] : "";
		$username = isset($data['username']) ? $data['username'] : "";
		$password = isset($data['password']) ? $data['password'] : "";
		$email = isset($data['email']) ? $data['email'] : "";
		if ($password == "") {
			return self::USERLOGIN_PASSWORD_NO_EMPTY;
		}
		//添加手机验证
		#$r_id           = self::CheckPhonePassword( $username , $password) ;
		#if( $r_id > 0 )   $user_id = $r_id ;
		if (isset($data['superadmin']) && $data['superadmin'] == true) {
			$sql = "select p1.*,p2.purview as pur,p2.type,p2.name as typename,p3.* from `{user}` as p1 left join `{user_type}` as p2 on p1.type_id = p2.type_id  left join {authentication} as p3 on p3.user_id=p1.user_id where  p3.email = '{$data['email']}' or p1.user_id = '{$user_id}' or p1.username = '{$username}'";
			return $mysql->db_fetch_array($sql);
		}
		$sql = "
		select
			 p1.*
			,p2.purview as pur
			,p2.type
			,p2.name as typename
			,p3.*
		from `{user}` as p1
			left join `{user_type}` as p2 on p1.type_id = p2.type_id
			left join {authentication} as p3 on p3.user_id=p1.user_id
		where p1.`password` = '" . md5($password) . "'
		  and (
		  	   p3.email = '{$email}'
			or p1.user_id = '{$user_id}'
			or p1.username = '{$username}'
		  )";

		if (isset($data['type']) && $data['type'] != "") {
			$sql .= " and p2.type = '{$data['type']}'";
		}
		$result = $mysql->db_fetch_array($sql);

		if ($result == false) {
			#先检查用户名，验证失败后再验证手机号，以保证先登陆的是手机号是用户名的用户。
			$sql = "
			select
				 p1.*
				,p2.purview as pur
				,p2.type
				,p2.name as typename
				,p3.*
			from `{user}` as p1
				left join `{user_type}` as p2 on p1.type_id = p2.type_id
				left join {authentication} as p3 on p3.user_id=p1.user_id
			where p1.`password` = '" . md5($password) . "'
			  and (
				   p3.phone = '{$username}'
			  )";
			if (isset($data['type']) && $data['type'] != "") {
				$sql .= " and p2.type = '{$data['type']}'";
			}
			$result = $mysql->db_fetch_array($sql);

			if ($result == false) {
				return self::USERLOGIN_USERNAME_PASSWORD_NO_RIGHT;
			}
		}

		$sql = "update `{user}` set logintime = logintime + 1,uptime=lasttime,upip=lastip,lasttime='" . time() . "',lastip='" . ip_address() . "' where username='$username'";
		$mysql->db_query($sql);
		return $result;
	}
	public static function Isuc() {
		global $mysql;
		$sql = "select 1 from `{module}` where code = 'ucenter'";
		$result = $mysql->db_fetch_array($sql);
		return $result == false ? false : true;
	}

//用户列表
	public static function GetList($data = array()) {
		global $mysql;

		$type = isset($data['type']) ? $data['type'] : "";
		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];

		$type_id = isset($data['type_id']) ? $data['type_id'] : "";
		$username = isset($data['username']) ? $data['username'] : "";
		$realname = isset($data['realname']) ? $data['realname'] : "";
		$invite_no = isset($data['invite_no']) ? $data['invite_no'] : "";
		$_order = 'order by u.user_id desc';
		//如果带上级查询
		if ($data['invite_user']) {

			$preuser = $mysql->db_fetch_array('select user_id from rwd_user where  username="' . $data['invite_user'] . '"');
			if ($preuser['user_id'] == '') {
				return array();
			}

		}

		if (isset($data['order'])) {
			if ($data['order'] == "new") {
				$_order = " order by u.addtime desc";
			} elseif ($data['order'] == "integral") {
				$_order = " order by u.integral desc";
			} elseif ($data['order'] == "hits") {
				$_order = " order by u.hits desc";
			} elseif ($data['order'] == "real_status") {
				$_order = " order by ua.real_status desc";
			} elseif ($data['order'] == "user_id") {
				$_order = " order by u.user_id ";
			}
		}

		$_sql = "";

		if ($data['time1'] != '' and $data['time2'] != '') {
			$_sql .= ' and u.addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and u.addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}
		if (isset($data['time1']) && $data['time1'] != "") {
			$_sql .= ' and u.addtime >=' . strtotime($data['time1'] . " 00:00:00") . ' ';
		}
		if (isset($data['time2']) && $data['time2'] != "") {
			$_sql .= ' and u.addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}
		if (isset($data['starttime']) && $data['starttime'] != "") {
			$_sql .= ' and u.addtime >=' . strtotime($data['starttime'] . " 00:00:00") . ' ';
		}
		if (isset($data['endtime']) && $data['endtime'] != "") {
			$_sql .= ' and u.addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}
		//上级邀请人
		if ($data['invite_user'] != "") {
			$_sql .= ' and u.invite_userid=' . $preuser['user_id'] . ' ';
		}
		if (isset($data['invite_name']) && $data['invite_name'] != "") {
			$inviteInfo = $mysql->db_fetch_array("select user_id from {authentication}  where realname='{$data['invite_name']}' ");
			if ($inviteInfo['user_id'] != '') {$_sql .= "and u.invite_userid='{$inviteInfo['user_id']}'";}
		}

		if ($type_id != "") {
			$_sql .= " and u.type_id in ($type_id)";
		}
		if ($username != "") {
			$_sql .= " and u.username like '%$username%'";
		}
		if ($realname != "") {
			$_sql .= " and ua.realname like '%$realname%'";
		}
		if ($invite_no != "") {
			$_sql .= " and u.invite_no=$invite_no";
		}
		/* if (isset($data['email']) && $data['email']!=""){
			$_sql .= " and ua.email like '%{$data['email']}%'";
		*/
		if (isset($data['addtime1']) && $data['addtime1'] != "") {
			$_sql .= " and u.addtime > " . get_mktime($data['addtime1']);
		}
		if (isset($data['addtime2']) && $data['addtime2'] != "") {
			$_sql .= " and u.addtime < " . get_mktime($data['addtime2']);
		}
		if (isset($data['invite_userid']) && $data['invite_userid'] != "") {
			$_sql .= " and u.invite_userid>0 ";
		}
		if (isset($data['vip_status']) && $data['vip_status'] != "") {
			$_sql .= " and uca.vip_status = {$data['vip_status']}";
		}
		if (isset($data['kefu_userid']) && $data['kefu_userid'] != "") {
			$_sql .= " and uca.kefu_userid = {$data['kefu_userid']}";
		}
		if (isset($data['kefu_username']) && $data['kefu_username'] != "") {
			$_sql .= " and uk.username like  '%{$data['kefu_username']}%'";
		}
		if (isset($data['real_status'])) {
			$_sql .= " and ua.real_status in ({$data['real_status']})";
		}
		if (isset($data['phone']) && $data['phone'] != "") {
			$_sql .= " and ua.phone like '%{$data['phone']}%'";
		}
		if (isset($data['qq']) && $data['qq'] != "") {
			$_sql .= " and u.qq like '%{$data['qq']}%'";
		}
		if (isset($data['avatar_status'])) {
			$_sql .= " and ua.avatar_status = {$data['avatar_status']}";
		}
		if (isset($data['phone_status'])) {
			if (isset($data['frompage']) && $data['frompage'] == "all") {
				$_sql .= " and ua.phone_status != 1 and ua.phone != ''";
			} else {
				if ($data['phone_status'] == 1) {
					$_sql .= " and ua.phone_status > {$data['phone_status']}";
				} else {
					$_sql .= " and ua.phone_status = {$data['phone_status']}";
				}
				$_order = " order by ua.phone_time desc ";
			}
		}
		if (isset($data['video_status'])) {
			$_sql .= " and ua.video_status = {$data['video_status']}";
			$_order = " order by ua.video_time desc ";
		}
		if (isset($data['email_status'])) {
			if (isset($data['frompage']) && $data['frompage'] == "all") {
				$_sql .= " and ua.email != ''";
				$_order = " order by ua.email_status asc ";
			} else {
				$_sql .= " and ua.email_status = {$data['email_status']}";
				$_order = " order by ua.email_time desc ";
			}
		}
		if (isset($data['scene_status'])) {
			$_sql .= " and ua.scene_status = {$data['scene_status']}";
			$_order = " order by ua.scene_time desc ";
		}

		$_select = " u.invite_userid,u.user_id,u.username,u.addtime,u.type_id,u.qq,u.cmp_admid,u.invite_no ";

		if (isset($data['frompage']) && ($data['frompage'] == "all" || $data['frompage'] == "realname")) {
			$_select .= ", ua.* ";
			$tabjoin = "left join `{authentication}` as ua on u.user_id = ua.user_id";
		} elseif ($data['frompage'] == "vip") {
			$_select .= ",u.logintime, uca.*,kf.username kefu_username ";
			$tabjoin = "left join `{user_cache}` as uca on u.user_id=uca.user_id left join `{admin}` as kf on uca.kefu_userid=kf.user_id";

		}

		$sql = "select SELECT from `{user}` as u " . $tabjoin;

		$sql .= " where 1=1 $_sql ORDER LIMIT";

		if (isset($data['limit'])) {
			$_limit = "";
			if ($data['limit'] != "all") {
				$_limit = "  limit " . $data['limit'];
			}
			$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $_limit), $sql));
			$list = $list ? $list : array();
			foreach ($list as $key => $value) {
				if ($value['invite_userid'] != "") {
					$sql = "select A.realname as invite_name , U.username as invite_user from `rwd_authentication` A left join `rwd_user` U on U.user_id = A.user_id where U.user_id=" . $value['invite_userid'];
					$re = $mysql->db_fetch_array($sql);
					$list[$key]['invite_name'] = $re['invite_name'];
					$list[$key]['invite_user'] = $re['invite_name'] ? $re['invite_name'] : $re['invite_user'];
				}
			}
			return $list;
		}
		$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));

		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
		$list = $list ? $list : array();
		foreach ($list as $key => $value) {
			if ($value['invite_userid'] != "") {
				$sql = "select A.realname as invite_user from `rwd_authentication` A left join `rwd_user` U on U.user_id = A.user_id where U.user_id=" . $value['invite_userid'];
				$re = $mysql->db_fetch_array($sql);
				$list[$key]['invite_name'] = $re['invite_user'];

			}

			if ($value['user_id'] == "") {
				continue;
			}

			$sql_bank = "select * from `rwd_account_bank` where user_id=" . $value['user_id'];
			$res = $mysql->db_fetch_array($sql_bank);
			$invite_no = $mysql->db_fetch_array("select user_id,invite_no,huifu_uid from `rwd_user` where user_id=" . $value['user_id']);
			$list[$key]['invite_no'] = $invite_no['invite_no'];
			$list[$key]['huifu_uid'] = $invite_no['huifu_uid'];
			$bank_status = empty($res) ? 0 : 1; //绑定为1，位绑定为0
			$list[$key]['bank_status'] = $bank_status;
		}
		return array(
			'list' => $list,
			'total' => $total,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);
	}

	public static function GetAdmin($data = array()) {
		global $mysql;

		$type = isset($data['type']) ? $data['type'] : "";
		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];

		$type_id = isset($data['type_id']) ? $data['type_id'] : "";
		$username = isset($data['username']) ? $data['username'] : "";
		$realname = isset($data['realname']) ? $data['realname'] : "";
		$_sql = "";
		if ($type_id != "") {
			$_sql .= " and u.type_id in ($type_id)";
		}
		if ($username != "") {
			$_sql .= " and u.username like '%$username%'";
		}
		if ($realname != "") {
			$_sql .= " and u.realname like '%$realname%'";
		}
		if (isset($data['email']) && $data['email'] != "") {
			$_sql .= " and u.email like '%{$data['email']}%'";
		}
		if (isset($data['phone']) && $data['phone'] != "") {
			$_sql .= " and u.phone like '%{$data['phone']}%'";
		}
		if (isset($data['qq']) && $data['qq'] != "") {
			$_sql .= " and u.qq like '%{$data['qq']}%'";
		}
		$_select = " u.*";
		$_order = 'order by u.user_id desc';

		if (isset($data['order'])) {
			if ($data['order'] == "new") {
				$_order = " order by u.addtime desc";
			} elseif ($data['order'] == "user_id") {
				$_order = " order by u.user_id ";
			}
		}
		$sql = "select SELECT from `{admin}` as u ";
		$sql .= " where 1=1  $_sql ORDER LIMIT";

		if (isset($data['limit'])) {
			$_limit = "";
			if ($data['limit'] != "all") {
				$_limit = "  limit " . $data['limit'];
			}
			return $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $_limit), $sql));
		}

		$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));

		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
		$list = $list ? $list : array();

		return array(
			'list' => $list,
			'total' => $total,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);

	}

	public static function GetOnes($data) {
		global $mysql;
		$_sql = " where 1=1 ";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql .= " and p1.user_id='{$data['user_id']}'";
		}
		if (isset($data['username']) && $data['username'] != "") {
			$_sql .= " and p1.username='{$data['username']}'";
		}
		if (isset($data['email']) && $data['email'] != "") {
			$_sql .= " and p2.email='{$data['email']}'";
		}
		$sql = "select * from `{user}` as p1 left join {authentication} as p2 on p2.user_id=p1.user_id {$_sql} ";
		$result = $mysql->db_fetch_array($sql);
		return $result;
	}
	public static function CreateInviteTree() {
		global $mysql;
		$data['order'] = "user_id";
		$data['invite_userid'] = '>0';
		$data['limit'] = 'all';
		$con_invite_tc = explode(',', isset($_G['system']['con_invite_tc']) ? $_G['system']['con_invite_tc'] : '0.3,0.1,0.05,0.03,0.02');
		$sql = "SET SQL_SAFE_UPDATES=0;";
		$mysql->db_query($sql);
		$sql = "update {user} set invites=''";
		$mysql->db_query($sql);
		$sql = "SET SQL_SAFE_UPDATES=1;";
		$mysql->db_query($sql);
		$userlist = userClass::GetList($data);
		foreach ($userlist as $key => $value) {
			$u = userClass::GetOnes(array('user_id' => $value['invite_userid']));
			if (is_array($u)) {
				unset($index);
				$invites = $u['invites'];
				if ((strlen($invites)) > 2) {
					$invites = substr($invites, 1, strlen($invites) - 2);
					$arr_no = explode('][', $invites);
					$arr_no = array_slice($arr_no, 0, count($con_invite_tc) - 1);
					$invites = join('][', $arr_no);
					if (strlen($invites) > 0) {
						$index['invites'] = '[' . $value['invite_userid'] . '][' . $invites . ']';
					} else {
						$index['invites'] = '[' . $value['invite_userid'] . ']';
					}
				} else {
					$index['invites'] = '[' . $value['invite_userid'] . ']';
				}
				$index['user_id'] = $value['user_id'];
				self::UpdateUserAll($index);
			}
		}
		return true;
	}

	public static function GetOne($data = array()) {
		global $mysql;
		$user_id = isset($data['user_id']) ? $data['user_id'] : "";
		$username = isset($data['username']) ? $data['username'] : "";
		$invite_no = isset($data['invite_no']) ? $data['invite_no'] : "";
		$password = isset($data['password']) ? $data['password'] : "";
		$email = isset($data['email']) ? $data['email'] : "";
		$type_id = isset($data['type_id']) ? $data['type_id'] : "";

		$cmp_admid = isset($data['invite_no']) ? $data['invite_no'] : ""; //加盟商管理员ID
		/*$sql = "CREATE TABLE IF NOT EXISTS `{user_cache}` (
		 `user_id` int(11) NOT NULL DEFAULT '0')";
	$mysql ->db_query($sql);*/
		if ($user_id == "" && $username == "" && $invite_no == "") {
			return self::ERROR;
		}

		$sql = "select p2.name as typename,p2.type,p3.*,p4.*,p5.*,p1.*,p6.*  from `{user}` as p1
			left join `{user_type}` as p2 on  p1.type_id = p2.type_id
			left join `{user_cache}` as p3 on  p3.user_id = p1.user_id
			left join `{account}` as p4 on  p4.user_id = p1.user_id
			left join `{userinfo}` as p5 on  p5.user_id = p1.user_id
			left join `{authentication}` as p6 on p6.user_id = p1.user_id
			where 1=1 ";
		if ($user_id != "") {
			$sql .= " and p1.user_id = $user_id";
		}

		if ($password != "") {
			$sql .= " and  p1.password = '" . md5($password) . "'";
		}

		if ($username != "") {
			$sql .= " and  p1.username = '$username'";
		}
		if ($invite_no != "") {
			$sql .= " and p1.invite_no ='$invite_no' ";
		}
		if ($email != "") {
			$sql .= " and  p6.email = '$email'";
		}

		if ($type_id != "") {
			$sql .= " and p1.type_id = '$type_id'";
		}

		return $mysql->db_fetch_array($sql);
	}
	public static function GetOneByPhone($data = array()) {
		global $mysql;
		$username = isset($data['username']) ? $data['username'] : "";
		$phone = isset($data['phone']) ? $data['phone'] : "";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			if ($phone == "") {
				return self::ERROR;
			}

			$sql = "select * from `rwd_authentication` a  right join `rwd_user` u on a.user_id=u.user_id where phone_status=1 and a.user_id=" . "'" . $data['user_id'] . "'" . " and phone= " . "'" . $phone . "'";
		} else {
			if ($phone == "") {
				return self::ERROR;
			}

			$sql = "select * from `rwd_authentication` a  right join `rwd_user` u on a.user_id=u.user_id where phone_status=1 and phone= " . "'" . $phone . "'";
		}
		return $mysql->db_fetch_array($sql);

	}
/********************************邀请管理**************************************/
	public static function GetOneUp($data = array()) {
		global $mysql;
		$sql = "select * from {user} where user_id=" . $data['user_id'];
		$result = $mysql->db_fetch_array($sql);

		if ($result['invite_userid'] == "0" || $result['invite_userid'] == "") {

			return false;
		} else {
			$sql1 = "select * from {user} where user_id=" . $result['invite_userid'];
		}
		return $mysql->db_fetch_array($sql1);
	}
	public static function GetMores($data = array()) {
		global $mysql;

		$sql = "select * from {user} where invite_userid=" . $data['user_id'];
		return $mysql->db_fetch_arrays($sql);
	}
	public static function GetMoreAll($data = array()) {
		global $mysql, $_G;

		$sql = "select user_id,username,invite_userid,invites,invite_no from {user}";
		$sql .= " where invites like '%[" . $data['user_id'] . "]%' or user_id=" . $data['user_id'] . " or invite_userid=" . $data['user_id'];
		$result = $mysql->db_fetch_arrays($sql);
		$_result = "";
		if (count($result) > 0) {
			$i = 0;

			$result[0]['invite_userid'] = 0;
			foreach ($result as $key => $value) {
				if ($value['user_id'] == $data['user_id']) {
					$value['invite_userid'] = 0;
					$_result[$i] = $value;
					$_result[$i]['subnum'] = 0;
					$_result[$i]['aname'] = "<b>" . $value['username'] . "</b>";
					$i++;
					foreach ($result as $_key => $_value) {
						if ($_value['invite_userid'] == $value['user_id']) {
							$_result[$i] = $_value;
							$_result[$i - 1]['subnum'] = 1;
							$_result[$i]['aname'] = "-" . $_value['username'];
							$i++;
							foreach ($result as $__key => $__value) {
								if ($__value['invite_userid'] == $_value['user_id']) {
									$_result[$i] = $__value;
									$_result[$i - 1]['subnum'] = 1;
									$_result[$i]['aname'] = "--" . $__value['username'];
									$i++;
									foreach ($result as $___key => $___value) {
										if ($___value['invite_userid'] == $__value['user_id']) {
											$_result[$i] = $___value;
											$_result[$i - 1]['subnum'] = 1;
											$_result[$i]['aname'] = "--" . $___value['username'];

											$i++;
										}
									}
								}
							}
						}
					}
				}


			}
		} else {
			foreach ($result as $key => $value) {
				$_result[$key]['aname'] = $value['username'];
				$_result[$key]['user_id'] = $value['user_id'];
			}
		}

		return $_result;

	}

	public static function friends_log() {
		global $mysql, $_G;
		$log = "select * from {admin} where user_id={$_G['user_id']}";
		return $mysql->db_fetch_array($log);

	}

	public static function Getcheck($movename) {
		global $mysql;
		$sql = "select * from {user} where username='{$movename}' or invite_no='{$movename}'";
		return $mysql->db_fetch_array($sql);
	}
	//移动邀请关系
	public static function Getmove($username, $movename) {
		global $mysql;

		$sql = "select * from {user} where username='{$username}'";
		$user1 = $mysql->db_fetch_array($sql); //原账户
		if (!$movename) {
			$mysql->db_query('update rwd_user set invite_userid="",invites="" where user_id=' . $user1['user_id']);
			return true;
		}
		$sql1 = "select * from {user} where username='{$movename}'";
		$user2 = $mysql->db_fetch_array($sql1); //移动的邀请人

		preg_match_all('/(\d+)\]/', $user2['invites'], $invtnos);
		$invitRep = '[' . $user2['user_id'] . ']';
		$invitRep .= $invtnos[1][0] ? '[' . $invtnos[1][0] . ']' : '';
		$invitRep .= $invtnos[1][1] ? '[' . $invtnos[1][1] . ']' : '';
		//本人
		if ($user2['cmp_admid'] != '') {
			$cmp_admid = $user2['cmp_admid'];
		} else {
			$cmp_admid = 0;
		}
		$mysql->db_query('update rwd_user set cmp_admid=' . $cmp_admid . ' ,invite_userid=' . $user2['user_id'] . ',invites=\'' . $invitRep . '\' where user_id=' . $user1['user_id']);
		//下级
		$mysql->db_query('update rwd_user set invites=concat( substring_index(invites ,\'[' . $user2['user_id'] . ']\',1 ) ,\'' . $invitRep . '\' ) where invites like \'%[' . $user2['user_id'] . '%]\'');
		$mysql->db_query('update rwd_user set invites=substring_index(invites,\'[\',4 ) where invites like \'%[' . $user2['user_id'] . '%]\'');
		//更改成功插入操作记录
		$userlog = userClass::friends_log();
		$jilu = "insert {friends_l_log} value(null,'{$userlog['username']}','{$user1['user_id']}','{$user1['invite_userid']}','{$user2['user_id']}','" . time() . "','" . ip_address() . "')";
		return $mysql->db_query($jilu);
	}
/**********************************************************************/
	function AddUser($data = array()) {
		global $mysql;
		$password = '';
		if (!$data['username'] || !$data['password']) {
			return self::ERROR;
		}
		if (strlen($data['username']) > 30) {
			return self::USER_ADD_LONG_USERNAME;
		}

		if (self::CheckUsername($data)) {
			return self::USER_REG_USERNAME_EXIST;
		}

		$password = $data['password'];
		$data['password'] = md5($data['password']);
		$sql = "insert into `{user}` set `addtime` = '" . time() . "',`addip` = '" . ip_address() . "',`uptime` = '" . time() . "',`upip` = '" . ip_address() . "',`lasttime` = '" . time() . "',`lastip` = '" . ip_address() . "'";

		foreach ($data as $key => $value) {
			$sql .= ",`$key` = '$value'";
		}

		$result = $mysql->db_query($sql);
		if ($result == false) {
			return self::USER_REG_ERROR;
		} else {
			$user_id = $mysql->db_insert_id();

			self::AddUserCache(array("user_id" => $user_id));

			if ($data['invite_userid'] != "") {
				$sql = "insert into `{friends}` set user_id='{$data['invite_userid']}',friends_userid='{$user_id}',type='1',status=1,addtime='" . time() . "'";
				$mysql->db_query($sql);
				$sql = "insert into `{friends}` set friends_userid='{$data['invite_userid']}',user_id='{$user_id}',type='1',status=1,addtime='" . time() . "'";
				$mysql->db_query($sql);
			}

			#$is_uc = moduleClass::GetOne(array("code" => 'ucenter'));
			if ($is_uc) {
				$uc_user_id = UcenterClient::regUser($data['username'], $password, $data['email']);

				if (is_numeric($uc_user_id) && $uc_user_id > 0) {
					$sql = "insert into `{ucenter}`(user_id,uc_user_id) values({$user_id}, {$uc_user_id})";
					$this->mysql->db_query($sql);

					$sql = "insert into " . UC_DBPREFIX . "common_member set uid='{$uc_user_id}',email='" . $data['email'] . "',username='" . $data['username'] . "',password='" . md5($password) . "',status='0',emailstatus='1',avatarstatus='1' ";
					$this->mysql->db_query($sql);
					$sql = "insert into " . UC_DBPREFIX . "common_member_field_forum set uid='{$uc_user_id}',customshow ='26' ";
					$this->mysql->db_query($sql);
					$sql = "insert into " . UC_DBPREFIX . "common_member_field_home set uid='{$uc_user_id}' ";
					$this->mysql->db_query($sql);
				} else {
					$sql = "delete from {user} where user_id=$user_id";
					$this->mysql->db_query($sql);
					return $uc_user_id;
				}
			}
			return $user_id;
		}
	}

	function AddUserauth($data = array()) {
		global $mysql;

		if (self::CheckEmail($data)) {
			unset($data['email']);
		}

		if (isset($data['phone']) and ($data['phone'] != '')) {
			if (self::Checkphone($data)) {
				unset($data['phone']);
			}

		}

		$sql = "insert into `{authentication}` set ";

		foreach ($data as $key => $value) {
			$sql .= "`$key` = '$value',";
		}
		$sql = substr($sql, 0, -1);

		$result = $mysql->db_query($sql);
		if ($result == false) {
			return self::USER_REG_ERROR;
		} else {
			$user_id = $mysql->db_insert_id();
			return $user_id;
		}
	}

	function UpdateUserauth($data = array()) {
		global $mysql;

		//if(self::CheckEmail($data)) unset($data['email']);
		if (isset($data['phone']) and ($data['phone'] != '')) {
			if (self::Checkphone($data)) {
				unset($data['phone']);
			}

		}

		$sql = "update `{authentication}` set ";
		foreach ($data as $key => $value) {
			$sql .= "`$key` = '$value',";
		}

		$sql = substr($sql, 0, -1);

		$result = $mysql->db_query($sql . ' where user_id=' . $data['user_id']);
		if ($result == false) {
			return self::USER_REG_ERROR;
		} else {
			$user_id = $mysql->db_insert_id();
			return $user_id;
		}
	}

	function UpdateUser($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		if (empty($user_id)) {
			return self::ERROR;
		}

		if (isset($data['password'])) {
			if ($data['password'] != "") {
				$data['password'] = md5($data['password']);
			} else {
				unset($data['password']);
			}
		}
		$sql = "update `{user}` set `user_id` = {$user_id}";
		foreach ($data as $key => $value) {
			$sql .= ",`$key` = '$value'";
		}
		$sql .= " where `user_id` = $user_id";
		return $mysql->db_query($sql);
	}

	function ActionUser($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		$order = $data['order'];
		if ($user_id == "" || $order == "") {
			return self::ERROR;
		}

		foreach ($user_id as $key => $id) {
			$sql = "update `{user}` set `order`='" . $order[$key] . "' where user_id=$id";
			$mysql->db_query($sql);
		}
		return true;
	}

	function UpdateUserProtection($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		$answer = $data['answer'];
		if ($user_id == "") {
			return self::ERROR;
		}

		if ($answer == "") {
			return self::USER_PROTECTION_ANSWER_NO_EMPTY;
		}

		$sql = "update `{user}` set `user_id` = {$user_id}";
		foreach ($data as $key => $value) {
			$sql .= ",`$key` = '$value'";
		}
		$sql .= " where `user_id` = $user_id";
		return $mysql->db_query($sql);
	}

	function UpdateUserAll($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		if (empty($user_id)) {
			return self::ERROR;
		}

		$sql = "update `{user}` set `user_id` = {$user_id}";
		foreach ($data as $key => $value) {
			if ($key != 'user_id') {
				$sql .= ",`$key` = '$value'";
			}
		}
		$sql .= " where `user_id` = $user_id";
		return $mysql->db_query($sql);
	}

	function UpdateUserCache($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		if ($user_id == "") {
			return self::ERROR;
		}

		$sql = "update `{user_cache}` set `user_id` = {$user_id}";
		foreach ($data as $key => $value) {
			$sql .= ",`$key` = '$value'";
		}
		$sql .= " where `user_id` = $user_id";
		return $mysql->db_query($sql);
	}

	public static function OrderUser($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		$order = $data['order'];
		if ($user_id == "" || $order == "") {
			return self::ERROR;
		}

		if (is_array($user_id)) {
			foreach ($user_id as $key => $value) {
				$sql = "update `{user}` set `order` = $order[$key] where `user_id` = $value";
				$mysql->db_query($sql);
			}
		}
		return true;
	}

	public static function DeleteUser($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		$type = $data['type'];
		if ($user_id == "") {
			return self::ERROR;
		}

		$_sql = "";
		if ($type != "") {
			$_sql = " and type=$type";
		}

		$sql = "delete u from `{user}` u left join `{user_type}` ut on u.type_id=ut.type_id where u.user_id = $user_id  and ut.type=$type and u.user_id!=1 $_sql";
		return $mysql->db_query($sql);
	}
//接触绑定银行卡
	public static function DeleteBank($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		$type = $data['type'];
		if ($user_id == "") {
			return self::ERROR;
		}

		$sql = "delete from `rwd_account_bank` where user_id=" . $user_id;
		return $mysql->db_query($sql);
	}

	public function add_log($index, $result) {
		global $mysql, $_G;
		$sql = "insert into `{user_log}` set `result`='$result',`user_id`='" . $_G['user_id'] . "',`addtime`='" . time() . "',addip='" . ip_address() . "'";
		if (is_array($index)) {
			foreach ($index as $key => $value) {
				$sql .= ",`$key` = '$value'";
			}
		}
		return $mysql->db_query($sql);
	}

	public function AddLog($index, $result) {
		global $mysql;
		$sql = "insert into `{user_log}` set `result`='$result',`user_id`='" . $_SESSION['user_id'] . "',`addtime`='" . time() . "',addip='" . ip_address() . "'";
		if (is_array($index)) {
			foreach ($index as $key => $value) {
				$sql .= ",`$key` = '$value'";
			}
		}
		return $mysql->db_query($sql);
	}

	public function GetUserName($u_id) {
		$record = $this->mysql->db_fetch_array("select username from `{user}` where user_id={$u_id};");
		if (!$record) {
			return false;
		}
		return $record['username'];
	}

	public function GetUserIdInUCenter($user_id) {
		$record = $this->mysql->db_fetch_array("select uc_user_id from `{ucenter}` where user_id={$user_id};");
		if (!$record) {
			return false;
		}
		return $record['uc_user_id'];
	}

	public static function GetUserCity($data = array()) {
		global $mysql;
		$user_id = $data['user_id'];
		if (empty($user_id)) {
			return self::ERROR;
		}

		$sql = "select a.name from `{user}` u left join {authentication} as p2 on p2.user_id=u.user_id left join {area} a on p2.city=a.id
				where u.user_id={$user_id}";
		$area = $mysql->db_fetch_array($sql);

		return $area['name'];
	}

	public static function GetTypeList($data = array()) {
		global $mysql;
		$_sql = "";
		if (isset($data['where']) && $data['where'] != "") {
			$_sql .= $data['where'];
		}
		if (isset($data['type']) && $data['type'] != "") {
			$_sql .= " and type=" . $data['type'];
		}
		$sql = "select * from `{user_type}` where 1=1 $_sql order by `order` desc";
		$result = $mysql->db_fetch_arrays($sql);
		return $result;
	}

	public static function GetTypeOne($data = array()) {
		global $mysql;
		if ($data['type_id'] == "") {
			return self::ERROR;
		}

		$sql = "select * from `{user_type}` where `type_id` = " . $data['type_id'];
		return $mysql->db_fetch_array($sql);
	}

	public static function Addtype($data = array()) {
		global $mysql;
		if ($data['name'] == "") {
			return self::TYPE_NAME_NO_EMPTY;
		}

		$sql = "insert into `{user_type}` set `addtime` = '" . time() . "',`addip` = '" . ip_address() . "'";
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$sql .= ",`$key` = '$value'";
			}
		}
		return $mysql->db_query($sql);
	}

	public static function UpdateType($data = array()) {
		global $mysql;
		if ($data['name'] == "") {
			return self::TYPE_NAME_NO_EMPTY;
		}

		$type_id = $data['type_id'];
		if ($type_id == "") {
			return self::ERROR;
		}

		$_sql = array();
		$sql = "update `{user_type}` set ";
		foreach ($data as $key => $value) {
			$_sql[] = "`$key` = '$value'";
		}

		$sql .= join(",", $_sql) . " where `type_id` = $type_id";
		return $mysql->db_query($sql);
	}

	public static function DeleteType($data = array()) {
		global $mysql;
		$type_id = $data['type_id'];
		if ($type_id == "") {
			return self::ERROR;
		}

		$sql = "delete from `{user_type}` where `type_id` = $type_id and type_id!=1";
		$mysql->db_query($sql);
		$sql = "delete from `{user}` where `type_id` = $type_id and type_id!=1";
		$mysql->db_query($sql);
		return true;
	}

	function OrderType($data = array()) {
		global $mysql;
		$type_id = $data['type_id'];
		$order = $data['order'];
		if ($type_id == "" || $order == "") {
			return self::ERROR;
		}

		foreach ($type_id as $key => $id) {
			$sql = "update `{user_type}` set `order`='" . $order[$key] . "' where type_id=$id";
			$mysql->db_query($sql);
		}
		return true;
	}

	function SendEmail($data = array()) {
		return true;
		global $mysql;
		require_once ROOT_PATH . 'plugins/mail/mail.php';

		$user_id = isset($data['user_id']) ? $data['user_id'] : '0';
		$title = isset($data['title']) ? $data['title'] : '系统信息';
		$email = isset($data['email']) ? $data['email'] : '';
		$msg = isset($data['msg']) ? $data['msg'] : '系统信息';
		$type = isset($data['type']) ? $data['type'] : 'system';

		if ($email == "") {
			return self::SENDEMAIL_EMAIL_NO_EMPTY;
		}

		$result = Mail::Send($title, $msg, array($email));

		$status = $result ? 1 : 0;

		$mysql->db_query("insert into `{user_sendemail_log}` set email='{$email}',user_id='{$user_id}',title='{$title}',msg='{$msg}',type='{$type}',status='{$status}',addtime='" . time() . "',addip='" . ip_address() . "'");
		return $result;
	}
//发送短信
	function SendMsg($data = array()) {
		global $mysql;
		$user_id = isset($data['user_id']) ? $data['user_id'] : '0';
		$title = isset($data['title']) ? $data['title'] : '长汇财富系统信息';
		$mob = isset($data['phone']) ? $data['phone'] : '';
		$msg = isset($data['msg']) ? $data['msg'] : '系统信息';
		$type = isset($data['type']) ? $data['type'] : 'system';

		if ($mob == "") {
			return self::SENDEMAIL_EMAIL_NO_EMPTY;
		}
		//写入发送队列
		$status = 1;
		#$result ? 1 : 0;
		$mysql->db_query("insert into `{user_sendemail_log}` set email='{$mob}',user_id='{$user_id}',title='{$title}',msg='{$msg}',type='{$type}',status='{$status}',addtime='" . time() . "',addip='" . ip_address() . "'");
		$logid = mysql_insert_id();
		$result = phone::sendsmsQUE($mob, $msg, $logid);
		return $result;
	}

	function ActiveEmail($data = array()) {
		global $mysql;
		$user_id = isset($data['user_id']) ? $data['user_id'] : '';
		if (empty($user_id)) {
			return self::ERROR;
		}

		$mysql->db_query("update `{authentication}` set email_status=1 where user_id=$user_id");
		$result = $mysql->db_fetch_array("select * from `{user}` as p1 left join {authentication} as p3 on p3.user_id=p1.user_id where p1.user_id=$user_id");
		return $result;
	}

	function ActiveAvatar($data = array()) {
		global $mysql;
		$user_id = isset($data['user_id']) ? $data['user_id'] : '';
		if (empty($user_id)) {
			return self::ERROR;
		}

		$mysql->db_query("update `{authentication}` set avatar_status=1 where user_id=$user_id");
		$result = $mysql->db_fetch_array("select * from `{user}` as p1 left join {authentication} as p3 on p3.user_id=p1.user_id where p1.user_id=$user_id");
		return $result;
	}

	public static function GetUserTrend($data = array()) {
		global $mysql;
		$_sql = " where 1=1 ";
		if (isset($data['user_id']) && $data['user_id'] != "") {
			$_sql .= " and user_id in ({$data['user_id']})";
		}
		$_limit = "";
		if (isset($data['limit']) && $data['limit'] != "") {
			$_limit = " limit {$data['limit']}";
		}
		$sql = "select friends_userid  from `{friends}` {$_sql} and status=1";
		$result = $mysql->db_fetch_arrays($sql);
		$_friend_userid = "";
		foreach ($result as $key => $value) {
			$_friend_userid[] = $value['friends_userid'];
		}
		if ($_friend_userid != "") {
			$friend_userid = join(",", $_friend_userid);

			$sql = "select p1.*,p2.username from `{user_trend}` as p1 left join `{user}` as p2 on p1.user_id=p2.user_id where p1.user_id in ({$friend_userid}) order by p1.addtime desc  {$_limit}";
			$result = $mysql->db_fetch_arrays($sql);
			return $result;
		} else {
			return "";
		}
	}

	public static function AddUserTrend($data = array()) {
		global $mysql;
		if (!isset($data['user_id']) || $data['user_id'] == "") {
			return self::ERROR;
		}
		$sql = "insert into `{user_trend}` set user_id='{$data['user_id']}',addtime='" . time() . "',content='{$data['content']}'";
		return $mysql->db_query($sql);
	}

	public static function GetUserCache($data = array()) {
		global $mysql, $_G;

		if (isset($data['user_id']) && $data['user_id'] != "") {
			$sql = "CREATE TABLE IF NOT EXISTS `{user_cache}` (
		 `user_id` int(11) NOT NULL DEFAULT '0')";
			$mysql->db_query($sql);
			$sql = "select p1.*,p3.username as kefu_username,p3.realname as  kefu_realname from `{user_cache}` as p1
			left join `{admin}` as p3 on p1.kefu_userid = p3.user_id
		 where p1.user_id ='{$data['user_id']}'";
			$result = $mysql->db_fetch_array($sql);
			if ($result == false) {

				self::AddUserCache(array("user_id" => $data['user_id']));
				$result = $mysql->db_fetch_array($sql);
			}
		} else {
			$sql = "select * from `{user_cache}` order by user_id desc";
			$result = $mysql->db_fetch_arrays($sql);
		}

		return $result;
	}

	public static function AddUserCache($data = array()) {
		global $mysql, $_G;
		if ($data['user_id'] == "") {
			return self::ERROR;
		}

		$_sql = array();
		$sql = "insert into  `{user_cache}` set ";
		foreach ($data as $key => $value) {
			$_sql[] = "`$key` = '$value'";
		}
		if (isset($_G['system']["con_user_amount"]) && $_G['system']['con_user_amount'] != "") {
			$sql .= "borrow_amount={$_G['system']['con_user_amount']},";
			$_amount = $_G['system']['con_user_amount'];
		} else {
			$sql .= "borrow_amount=2000,";
			$_amount = 2000;
		}
		$mysql->db_query($sql . join(",", $_sql));
		$sql = "insert into  `{user_amount}` set credit={$_amount},credit_use={$_amount},credit_nouse=0,user_id={$data['user_id']}";
		$mysql->db_query($sql);
	}

	public static function ApplyUserVip($data = array()) {
		global $mysql;
		if ($data['user_id'] == "") {
			return self::ERROR;
		}

		$sql = "update `{user_cache}` set kefu_userid = '{$data['kefu_userid']}',kefu_addtime = '" . time() . "',`vip_status`=2,`vip_remark` = '" . $data['vip_remark'] . "' where user_id = {$data['user_id']}";
		return $mysql->db_query($sql);
	}

	function GetUserNum() {
		global $mysql;
		$sql = "select count(*) as num from `{user}`";
		$result = $mysql->db_fetch_array($sql);
		return $result;
	}

	function TypeChange($data) {
		global $mysql;
		$type = isset($data['type']) ? $data['type'] : "new";
		if ($type == "new") {
			$sql = "insert into `{user_typechange}` set old_type='{$data['old_type']}',new_type='{$data['new_type']}',user_id='{$data['user_id']}',addtime='" . time() . "',addip='" . ip_address() . "',content='{$data['content']}',status=0";
			return $mysql->db_query($sql);
		} elseif ($type == "update") {
			$sql = "update `{user_typechange}` set status='{$data['status']}' where id='{$data['id']}' ";
			$mysql->db_query($sql);
			$result = self::TypeChange(array("id" => $data['id'], "type" => "view"));
			if ($data['status'] == 1 && $result['user_id'] != 1) {
				$sql = "update `{user}` set type_id='{$result['new_tyoe']}' where user_id='{$result['user_id']}'";
				$mysql->db_query($sql);
			}
			return true;
		} elseif ($type == "view") {
			$sql = "select * from `{user_typechange}` where id='{$data['id']}'";
			return $mysql->db_fetch_array($sql);

		} elseif ($type == "list") {
			$page = empty($data['page']) ? 1 : $data['page'];
			$epage = empty($data['epage']) ? 10 : $data['epage'];
			$sql = "select SELECT from `{user_typechange}` as p1
				left join `{user}` as p2 on p1.user_id = p2.user_id
				left join `{user_type}` as p3 on p1.old_type = p3.type_id
				left join `{user_type}` as p4 on p1.new_type = p4.type_id
				left join {authentication} as p6 on p6.user_id=p1.user_id
				ORDER LIMIT";
			$_select = "p1.*,p5.realname,p2.username,p3.name as old_typename,p4.name as new_typename";
			$_order = " order by p1.id desc";

			if (isset($data['limit'])) {
				$_limit = "";
				if ($data['limit'] != "all") {
					$_limit = "  limit " . $data['limit'];
				}
				return $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $_limit), $sql));
			}

			$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));

			$total = $row['num'];
			$total_page = ceil($total / $epage);
			$index = $epage * ($page - 1);
			$limit = " limit {$index}, {$epage}";
			$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
			$list = $list ? $list : array();

			return array(
				'list' => $list,
				'total' => $total,
				'page' => $page,
				'epage' => $epage,
				'total_page' => $total_page,
			);
		}
	}

	public static function GetUserBirthday() {
		global $mysql;
		$days = date('t', time());
		$first_time = date("m", time()) . "01";
		$end_time = date("m", time()) . "31";
		$sql = "select p2.birthday,p1.user_id,p1.username,p2.realname from `{user}` as p1 left join {authentication} as p2 on p2.user_id=p1.user_id  ";
		$result = $mysql->db_fetch_arrays($sql);
		$_result = "";
		foreach ($result as $key => $value) {
			if ($value['birthday'] != "") {
				$btime = date("md", $value['birthday']);
				if ($btime > $first_time && $btime < $end_time) {
					$_result[$key]['monthday'] = $btime;
					$_result[$key]['user_id'] = $value['user_id'];
					$_result[$key]['birthday'] = $value['birthday'];
					$_result[$key]['realname'] = $value['realname'];
				}
			}
		}
		sort($_result);
		return $_result;
	}
//2016年2月20日08:43:24 lbn（实现统计部门员工邀请并实名认证的数量）
	public static function GetInviteRealCount($data = array()) {
		global $mysql;
		if ($data['starttime']) {
			$whStart = ' and real_pass_time >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and real_pass_time <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and real_pass_time >=' . strtotime($data['time1'] . " 00:00:00") .
			' and real_pass_time <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		$sql = "select b1.user_id,b1.realname,b2.username from rwd_authentication b1 join rwd_user b2 on b1.user_id=b2.user_id where b1.department > 0";
		$result = $mysql->db_fetch_arrays($sql); //获得所有员工id,用户名,真实姓名
		foreach ($result as $k => $v) {
			$user_id = $v['user_id']; //员工id
			$invited_id = "select count(*) as num  from `rwd_user` U  join `rwd_authentication`  R on R.user_id=U.user_id  where U.invite_userid={$user_id} and R.real_status=1" . $whStart . $whEnd . "  ";
			$invited_id = str_replace('real_pass_time', 'real_pass_time', $invited_id);
			$data = $mysql->db_fetch_array($invited_id);
			$userAll2[] = array('username' => $v['username'], 'realname' => $v['realname'], 'invite_count' => $data['num']);
		}
		$sort = 'invite_count';
		foreach ($userAll2 as $k => $v) {
			$newArr[$k] = $v[$sort];
		}

		array_multisort($newArr, SORT_DESC, $userAll2);
		return $userAll2;
	}

//***hfb 添加部门邀请统计***///
	public static function GetDepartmentInviteCount($data = array()) {
		global $mysql;
		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		$sqlbr = "create TEMPORARY table if not exists `trend_borrow`  select  id   from `rwd_borrow` " .
			" where status=3   " . $whStart . $whEnd . "  ";
		$sqlbr = str_replace('addtime', 'success_time', $sqlbr);
		$mysql->db_fetch_arrays($sqlbr); //创建临时表存储borrow_id
		$sqlct = "create TEMPORARY table if not exists `trend_user`  select  R.user_id ,sum( R.account) as acc  from `rwd_borrow_tender` R , `trend_borrow` T" .
			"  where  R.status=1  and  R.borrow_id=T.id  group by  R.user_id";
		$mysql->db_fetch_arrays($sqlct); //创建临时表存储投过资的人的id
		$sql = "select department,count(department) as count from rwd_authentication where department>0  group by department";
		$result = $mysql->db_fetch_arrays($sql); //统计部门员工数
		$list = array();
		foreach ($result as $key => $value) {
			$ygcount = array();
			$sql_yg = "select p1.user_id,p1.username,p2.realname,p1.invite_no,p2.department from rwd_user p1 join rwd_authentication p2 on p1.user_id=p2.user_id  " .
				"where p2.department='{$result[$key]['department']}' order by p1.user_id";
			$result_yg = $mysql->db_fetch_arrays($sql_yg);
			foreach ($result_yg as $key1 => $value) {
				$user_id = $result_yg[$key1]['user_id'];
				$sql_count = "select count(*) as num from rwd_user where invite_userid={$user_id}" . $whStart . $whEnd;
				$sql_trend_count = "select count(*) as num,sum(T.acc) as acc from rwd_user R left join trend_user T on R.user_id=T.user_id" .
					" where R.invite_userid={$user_id} and T.acc > 0 ";
				$sql_useracc = "select sum(T.acc) as uacc from trend_user T " .
					" where T.user_id={$user_id} and T.acc > 0 ";
				$userCount = $mysql->db_fetch_array($sql_useracc);
				$count = $mysql->db_fetch_array($sql_count);
				$trendCount = $mysql->db_fetch_array($sql_trend_count);
				$ygcount['user'][$key1]['name'] = $result_yg[$key1]['username'];
				$ygcount['user'][$key1]['realname'] = $result_yg[$key1]['realname'];
				$ygcount['user'][$key1]['user_count'] = $count['num'];
				$ygcount['user'][$key1]['user_trend'] = $trendCount['num'];
				$ygcount['user'][$key1]['user_acc'] = intval($trendCount['acc']);
				$ygcount['user'][$key1]['user_uacc'] = intval($userCount['uacc']);
				$ygcount['num'] += $count['num'];
			}

			$ygcount['department'] = $result[$key]['department'];
			$ygcount['count'] = $result[$key]['count'];
			$list[] = $ygcount;
		}

		foreach ($list as $key => $value) {
			$sql_select = "select * from rwd_linkage where id={$value['department']}";
			$result_select = $mysql->db_fetch_array($sql_select);
			if ($result_select['name'] == '无') {
				unset($list[$key]);
			} else {
				$list[$key]['departmentname'] = $result_select['name'];
			}

		}
		//print_r($list);
		return $list;
	}
//部门投资明细
	static function GetDepartmentInviteDetailCount($data = array()) {
		global $mysql;
		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

//存储全部符合条件的标信息
		$sqlbr = "create TEMPORARY table if not exists `trend_borrow`  select  id , time_limit limit_time   from `rwd_borrow` " .
			" where status=3   " . $whStart . $whEnd . "  ";
		$sqlbr = str_replace('addtime', 'success_time', $sqlbr);
		$mysql->db_fetch_arrays($sqlbr); //创建临时表存储borrow_id
		//筛选出初次投资的记录
		$sqlfirstbr = 'create TEMPORARY table if not exists `trend_first` select id,borrow_id,user_id,account from(select * from  `rwd_borrow_tender` where status=1 order by id ) GG group by user_id';
		$mysql->db_fetch_arrays($sqlfirstbr);
		$sqlfirstbr = 'create TEMPORARY table if not exists `trend_first2` select T.id,T.borrow_id,T.user_id,T.account,R.limit_time  from `trend_first` T join `trend_borrow` R on T.borrow_id=R.id ';
		$mysql->db_fetch_arrays($sqlfirstbr);
		//存储全员投资综合分时段统计
		$sqlct = "create TEMPORARY table if not exists `trend_user`  select  R.user_id ,T.limit_time,sum( R.account) as acc  from `rwd_borrow_tender` R , `trend_borrow` T" .
			"  where  R.status=1  and  R.borrow_id=T.id  group by  R.user_id, T.limit_time";
		$mysql->db_fetch_arrays($sqlct);
		$sql = "select department,count(department) as count from rwd_authentication where department>=1  group by department";
		$result = $mysql->db_fetch_arrays($sql); //统计部门员工数
		$list = array();
		foreach ($result as $key => $value) {
			//按部门统计
			$ygcount = array();
			$sql_yg = "select p1.user_id,p1.username,p2.realname,p1.invite_no,p2.department from rwd_user p1 join rwd_authentication p2 on p1.user_id=p2.user_id  " .
				"where p2.department='{$value['department']}' order by p1.user_id";
			$result_yg = $mysql->db_fetch_arrays($sql_yg);
			foreach ($result_yg as $key1 => $value1) {
				//部门员工
				//统计邀请人
				$sql_invite = "select count(*) as num,sum(T.acc) as acc ,T.limit_time from rwd_user R join trend_user T on R.user_id=T.user_id" .
					" where R.invite_userid={$value1[user_id]} and T.acc > 0 group by T.limit_time";
				//首次投资统计
				$sql_invite_first = "select count(*) as num,sum(T.account) as acc ,T.limit_time from  trend_first2 T join rwd_user R on R.user_id=T.user_id" .
					" where R.invite_userid={$value1[user_id]} and T.account > 0 group by T.limit_time";
				//统计本人
				$sql_user = "select sum(T.acc) as uacc , T.limit_time from trend_user T " .
					" where T.user_id={$value1[user_id]} and T.acc > 0 group by limit_time ";
				$userCount = $mysql->db_fetch_arrays($sql_user);
				$userCt = array();
				foreach ($userCount as $k => $v) {$userCt[$v['limit_time']] = $v['uacc'];}
				$inviteCount = $mysql->db_fetch_arrays($sql_invite);
				$inviteCt = array();
				foreach ($inviteCount as $k => $v) {$inviteCt[$v['limit_time']] = $v['acc'];}
				$inviteFirst = $mysql->db_fetch_arrays($sql_invite_first);
				$firstCt = array();
				foreach ($inviteFirst as $k => $v) {$firstCt[$v['limit_time']] = $v['acc'];}
				$ygcount['user'][$key1]['name'] = $value1['username'];
				$ygcount['user'][$key1]['realname'] = $value1['realname'];
				$ygcount['user'][$key1]['user_invite'] = $inviteCt; //邀请总计
				$ygcount['user'][$key1]['user_first'] = $firstCt; //邀请首次投资总计
				$ygcount['user'][$key1]['user_acc'] = $userCt; // 个人总计
			}
			$ygcount['department'] = $value['department']; //部门ID
			$ygcount['count'] = $value['count']; //人数
			$list[] = $ygcount;
		}
		foreach ($list as $key => $value) {
			$sql_select = "select * from rwd_linkage where id={$value['department']}";
			$result_select = $mysql->db_fetch_array($sql_select);
			if ($result_select['name'] == '无') {
				unset($list[$key]);
			} else {
				$list[$key]['departmentname'] = $result_select['name'];
			}

		}
		return $list;

	}
//活动期间内的用户投资+邀请额度人数
	public static function ActiveInviterealCount($data = array()) {
		global $mysql;
		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		$sqlbr = "create TEMPORARY table if not exists `trend_borrow` select  id " .
			"from `rwd_borrow` R   where R.status=3 "; //复审之后的borrow_id
		$mysql->db_fetch_arrays($sqlbr); //临时表
		$sql = "create TEMPORARY table if not exists `trend_user` select  R.user_id ,sum( R.account) as acc " .
			" from `rwd_borrow_tender` R  left  join  `trend_borrow` T  on  R.borrow_id=T.id  where T.id>0 and  R.status=1 " . $whStart . $whEnd . "  group by  R.user_id order by acc desc";
		$mysql->db_fetch_arrays($sql); //id,总金额
		$sql = 'create TEMPORARY table if not exists `userlist`  select   T.user_id, R.invite_userid , T.acc ,R.username,R.addtime  from ' .
			' trend_user T  left join rwd_user R  on T.user_id=R.user_id ';
		$mysql->db_query($sql);
		$result = $mysql->db_fetch_arrays('select *  from `userlist`');
		foreach ($result as $k => $v) {
			$sql = "select count(*) as dd  from `userlist` where acc>= 50 and invite_userid={$v['user_id']} " . $whStart . $whEnd; //投资过的
			$re = $mysql->db_fetch_array($sql);
			$num = $re['dd'];
			$sub = array();
			$sql = "select * from `userlist` where  acc>=300000 and user_id=" . $v['user_id']; //投资额大于30万
			$sql = str_replace('addtime', 'addtime', $sql);
			$sub = $mysql->db_fetch_array($sql);
			if ($num >= 10 and $sub['user_id'] != 0) {
				$_R[] = array('username' => $sub['username'], 'acc' => number_format($sub['acc']), 'num' => $num);
			}

		}
		return $_R;
		//print_r($_R);
	}

//期间内的用户投资排名(区分员工-加盟商-普通客户)
	public static function AllUserInvestCount($data = array()) {
		global $mysql;
		if ($data['time1'] == '' or $data['time2'] == '') {
			return false;
		}

		if ($data['time1']) {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") . ' ';
		}

		if ($data['time2']) {
			$whEnd = ' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		$sqlbr = "create TEMPORARY table if not exists `trend_borrow` select  id,user_id,account " .
			"from `rwd_borrow_tender`    where status=1 " . $whStart . $whEnd; //期间内成功投资记录
		$mysql->db_fetch_arrays($sqlbr); //临时表
		# 0 全部 1 员工 2 加盟商 3 员工+加盟 4 普通客户
		if ($data['type'] > 0) {
			$type = $data['type'];
		} else {
			$type = 0;
		}

		$select = ' sum( t1.account) as acc,t1.user_id,t2.username ,t3.realname,t3.phone ';
		$from = 'from `trend_borrow` t1 left join  `{user}` t2 on t1.user_id=t2.user_id ' .
			'left join `{authentication}` t3 on t1.user_id=t3.user_id ';
		$group = ' group by t1.user_id ';
		if ($type == 0) {
			$sql = 'select   ' . $select . $from;
		} elseif ($type == 1) {
			$sql = 'select   ' . $select . $from . 'where t3.department > 0';
		} elseif ($type == 2) {
			$sql = 'select   ' . $select . $from . 'where t2.cmp_admid > 0';
		} elseif ($type == 3) {
			$sql = 'select   ' . $select . $from . 'where t2.cmp_admid > 0 or t3.department > 0';
		} elseif ($type == 4) {
			$sql = 'select   ' . $select . $from . 'where t2.cmp_admid = 0 and t3.department = 0';
		}
		$userArray = $mysql->db_fetch_arrays($sql . $group);
		foreach ($userArray as $k => $v) {$U2[$k] = $v['acc'];}
		arsort($U2);
		if ($data['limit'] > 0) {
			$limit = $data['limit'];
		} else {
			$limit = 20;
		}

		$ii = 1;
		foreach ($U2 as $k => $v) {
			$U3[$k] = $userArray[$k];
			if ($ii >= $limit) {
				break;
			}

			$ii++;
		}

		return $U3;
	}

//时间段用户投资统计
	public static function UserInvestCount($data = array()) {
		global $mysql;

		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 40 : $data['epage'];

		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}
        
		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}
        if ($data['borrow_id']) {
			$whStart = ' and borrow_id >=' .$data['borrow_id']. ' ';
		}
		if ($data['limit'] != "all") {
			if ($_REQUEST[page]) {
				$limit = ' limit ' . intval(($_REQUEST[page] - 1) * 40) . ', 40';
			} else {
				$limit = ' limit 40';
			}
		}
		$sqlbr = " select  * from `rwd_borrow_tender`   where status=1 " . $whStart . $whEnd . $limit;
		$rows = $mysql->db_fetch_arrays($sqlbr);
		foreach ($rows as $k => $v) {
			$sql = "select * from `rwd_user` where user_id={$v['user_id']}";
			$user = $mysql->db_fetch_array($sql);
			$sql = 'select * from `rwd_borrow` where id=' . $v['borrow_id'];
			$borrow = $mysql->db_fetch_array($sql);
			$sql = "select * from `rwd_authentication` where user_id={$v['user_id']}";
			$auth = $mysql->db_fetch_array($sql);
			$arr[$k]['username'] = $user['username'];
			$arr[$k]['realname'] = $auth['realname'];
			$arr[$k]['department'] = $auth['department'];
			$arr[$k]['borrow_id'] = $borrow['id'];
			$arr[$k]['name'] = $borrow['name'];
			$arr[$k]['money'] = $v['money'];
			$arr[$k]['account'] = $v['account'];
		}
		$row = " select  count(*) as dd, sum(account) as sum from `rwd_borrow_tender`   where status=1 " . $whStart . $whEnd;
		$row = $mysql->db_fetch_array($row);
		//print_r($row);
		return array(
			'list' => $arr,
			'total' => $row['dd'],
			'tongji' => $row,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);
	}
//加盟商用户信息
	public static function GetJiamengList($data) {
		global $mysql;
		//后台中获得前台ID
		$sqlid = "select cmp_admid from `rwd_admin` where user_id=" . $data[bg_userid];
		$cmp_admid = $mysql->db_fetch_array($sqlid);
		$sql = "select p1.user_id,p1.invite_userid,p1.username,p1.addtime,p1.cmp_admid,p2.realname,p2.sex,p2.phone,p2.weixin,p2.card_id  from `rwd_user` p1" .
			" join `rwd_authentication` p2 on p1.user_id=p2.user_id  where p1.cmp_admid={$cmp_admid['cmp_admid']}";
		$rows = $mysql->db_fetch_arrays($sql);
		foreach ($rows as $key => $value) {
			if ($value['user_id'] == $cmp_admid['cmp_admid']) {
				unset($rows[$key]);
				continue;}
			$sql_bank = "select * from `rwd_account_bank` where user_id=" . $value['user_id'];
			$res = $mysql->db_fetch_array($sql_bank);
			$bank_status = empty($res) ? 0 : 1; //绑定为1，位绑定为0
			$rows[$key]['bank_status'] = $bank_status;
		}
		return $rows;
	}
//资金加盟业务统计
	public static function GetJmUserCount($data) {
		global $mysql;

		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		//后台中获得前台ID
		$sqlid = "select cmp_admid from `rwd_admin` where user_id=" . $data[bg_userid];
		$cmp_admid = $mysql->db_fetch_array($sqlid);
		//存储全部符合条件的标信息
		$sqlbr = "create TEMPORARY table if not exists `trend_borrow`  select  id , time_limit limit_time   from `rwd_borrow` " .
			" where status=3" . $whStart . $whEnd . "  ";
		$sqlbr = str_replace('addtime', 'success_time', $sqlbr);
		$mysql->db_fetch_arrays($sqlbr); //创建临时表存储borrow_id
		//筛选出初次投资的记录
		$sqlfirstbr = 'create TEMPORARY table if not exists `trend_first` select id,borrow_id,user_id,account from(select * from  `rwd_borrow_tender` where status=1 order by id ) GG group by user_id';

		$mysql->db_fetch_arrays($sqlfirstbr);
		$sqlfirstbr = 'create TEMPORARY table if not exists `trend_first2` select T.id,T.borrow_id,T.user_id,T.account,R.limit_time  from `trend_first` T join `trend_borrow` R on T.borrow_id=R.id ';
		$mysql->db_fetch_arrays($sqlfirstbr);
		//存储全员投资综合分时段统计
		$sqlct = "create TEMPORARY table if not exists `trend_user`  select  R.user_id ,T.limit_time,sum( R.account) as acc  from `rwd_borrow_tender` R , `trend_borrow` T" .
			"  where  R.status=1  and  R.borrow_id=T.id  group by  R.user_id, T.limit_time";
		$mysql->db_fetch_arrays($sqlct);
		$sql = "select p1.user_id,p1.username,p2.realname,p1.invite_no,p1.cmp_admid,p1.invite_userid from `rwd_user` p1 join `rwd_authentication` p2 on p1.user_id=p2.user_id  " .
			"where p1.cmp_admid={$cmp_admid['cmp_admid']} and (p1.invite_userid={$cmp_admid['cmp_admid']} or p1.user_id={$cmp_admid['cmp_admid']}) order by p1.user_id";
		$result = $mysql->db_fetch_arrays($sql); //详细信息

		$list = array();
		foreach ($result as $key => $value) {
			//加盟商统计
			$ygcount = array();
			//统计邀请人
			$sql_invite = "select count(*) as num,sum(T.acc) as acc ,T.limit_time from rwd_user R join trend_user T on R.user_id=T.user_id" .
				" where R.invite_userid={$value[user_id]} and T.acc > 0 group by T.limit_time";
			//首次投资统计
			$sql_invite_first = "select count(*) as num,sum(T.account) as acc ,T.limit_time from  trend_first2 T join rwd_user R on R.user_id=T.user_id" .
				" where R.invite_userid={$value[user_id]} and T.account > 0 group by T.limit_time";
			//统计本人
			$sql_user = "select sum(T.acc) as uacc , T.limit_time from trend_user T " .
				" where T.user_id={$value[user_id]} and T.acc > 0 group by limit_time ";
			$userCount = $mysql->db_fetch_arrays($sql_user);
			$userCt = array();
			foreach ($userCount as $k => $v) {$userCt[$v['limit_time']] = $v['uacc'];}

			$inviteCount = $mysql->db_fetch_arrays($sql_invite);
			$inviteCt = array();
			if ($value['user_id'] != $cmp_admid['cmp_admid']) {
				foreach ($inviteCount as $k => $v) {$inviteCt[$v['limit_time']] = $v['acc'];}
			} else {
				$inviteCt = array();
			}
			$inviteFirst = $mysql->db_fetch_arrays($sql_invite_first);
			$firstCt = array();
			foreach ($inviteFirst as $k => $v) {$firstCt[$v['limit_time']] = $v['acc'];}

			$ygcount['user'][$key]['name'] = $value['username'];
			$ygcount['user'][$key]['cmp_admid'] = $value['cmp_admid'];
			$ygcount['user'][$key]['realname'] = $value['realname'];
			$ygcount['user'][$key]['invite_userid'] = $value['invite_userid'];
			$ygcount['user'][$key]['user_id'] = $value['user_id'];

			$ygcount['user'][$key]['user_invite'] = $inviteCt; //邀请总计
			$ygcount['user'][$key]['user_first'] = $firstCt; //邀请首次投资总计
			$ygcount['user'][$key]['user_acc'] = $userCt; // 个人总计
			//统计邀请及实名
			$user_num = self::getInviteRealNum($value['user_id'], strtotime($data['time1'] . " 00:00:00"), strtotime($data['time2'] . " 23:59:59"));
			$ygcount['user'][$key]['yq'] = $user_num['yq'];
			$ygcount['user'][$key]['sm'] = $user_num['sm'];
			$list[] = $ygcount;
		}

		if ($data['exp'] == 1) {
			$list2 = $list;
			$list = array();
			foreach ($list2 as $a => $b) {
				$list[$a]['user_id'] = $b['user'][$a]['user_id'];
				$list[$a]['cmp_admid'] = $b['user'][$a]['cmp_admid'];
				$list[$a]['invite_userid'] = $b['user'][$a]['invite_userid'];
				$list[$a]['username'] = $b['user'][$a]['name'];
				$list[$a]['realname'] = $b['user'][$a]['realname'];
				$list[$a]['user_acc'] = implode_add_tag($b['user'][$a]['user_acc'], '月', '元');
				$list[$a]['user_invite'] = implode_add_tag($b['user'][$a]['user_invite'], '月', '元');
				$list[$a]['user_first'] = implode_add_tag($b['user'][$a]['user_first'], '月', '元');
			}
		}

		return $list;

	}
//资金加盟业务实名
	public static function GetJmRealUser($data = array()) {
		global $mysql;

		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['starttime1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['endtime'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		//后台中获得前台ID
		$sqlid = "select cmp_admid from `rwd_admin` where user_id=" . $data['bg_userid'];
		$cmp_admid = $mysql->db_fetch_array($sqlid);
		$sql = "create TEMPORARY table if not exists `user_info`  select user_id from `rwd_user` where cmp_admid={$cmp_admid['cmp_admid']}" . $whStart . $whEnd . " ";
		$mysql->db_fetch_arrays($sql);
		$sqlnum = "select count(A.user_id) as num from `user_info` U join `rwd_authentication` A on U.user_id=A.user_id where A.real_status=1";
		$sqlnum1 = "select count(A.user_id) as total from `user_info` U join `rwd_authentication` A on U.user_id=A.user_id";
		$num = $mysql->db_fetch_array($sqlnum);
		$total = $mysql->db_fetch_array($sqlnum1);
		$res = array();
		$res['num'] = $num['num'];
		$res['total'] = $total['total'];
		return $res;

	}
//统计实名认证和邀请的人数
	static function getInviteRealNum($uid, $stime, $etime) {
		global $mysql;
		$yqsql = " select count(*) as dd from `rwd_user` where invite_userid=" . $uid . " and addtime >" . $stime . " and addtime <" . $etime;
		$re = $mysql->db_fetch_array($yqsql);
		$user['yq'] = intval($re['dd']);
		$yqsql = " select count(*) as dd from `rwd_user` R ,`rwd_authentication` A  where R.invite_userid=" .
			$uid . " and R.addtime >" . $stime . " and R.addtime <" . $etime . " and R.user_id=A.user_id and A.real_status=1";
		$re = $mysql->db_fetch_array($yqsql);
		$user['sm'] = intval($re['dd']);
		return $user;

	}
//自己加盟业务明细
	public static function jmusercount_detail($data = array()) {
		global $mysql;

		if ($data['starttime']) {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") . ' ';
		}

		if ($data['endtime']) {
			$whEnd = ' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		$uid = $data['userid']; //邀请人id
		$sql = "select realname from `rwd_authentication` where user_id={$uid}";
		$n = $mysql->db_fetch_array($sql);
		$sqlbr = "create TEMPORARY table if not exists `trend_borrow`  select  id   from `rwd_borrow` " .
			" where status=3" . $whStart . $whEnd . "  ";
		$sqlbr = str_replace('addtime', 'success_time', $sqlbr);
		$mysql->db_fetch_arrays($sqlbr); //创建临时表存储borrow_id
		$sql = "select p1.username,p1.user_id,p2.realname from `rwd_user` p1 join `rwd_authentication` p2 on p1.user_id=p2.user_id  where p1.invite_userid={$uid}";
		$result = $mysql->db_fetch_arrays($sql);
		$data = array();
		foreach ($result as $k => $v) {
			$sql = "select sum(account) as acc from `rwd_borrow_tender` R join  `trend_borrow` T  on  R.borrow_id=T.id where R.user_id={$v['user_id']}";
			$acc = $mysql->db_fetch_array($sql);
			$data[$k]['user_id'] = $v['user_id'];
			$data[$k]['username'] = $v['username'];
			$data[$k]['realname'] = $v['realname'];
			$data[$k]['acc'] = $acc['acc'];
			$data[$k]['invite_name'] = $n['realname'];
		}
		return $data;
	}
//资产端统计
	public static function jminviteCount($data = array()) {
		global $mysql;

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		} else {
			return;
		}

		//后台管理 cmp_admid
		$sqlid = "select cmp_admid from `rwd_admin` where user_id=" . $data[bg_userid];
		$cmp_admid = $mysql->db_fetch_array($sqlid);
		$sqlbr = "create TEMPORARY table if not exists `trend_borrow`  select  t1.id,t1.account,t2.invite_userid  from `rwd_borrow` t1 join `rwd_user` t2" .
			" on t1.user_id=t2.user_id " . " where t1.status=3 and t2.cmp_admid={$cmp_admid[cmp_admid]} " . $whStart . $whEnd . "  ";
		$sqlbr = str_replace('addtime', 'success_time', $sqlbr);
		$mysql->db_fetch_arrays($sqlbr);
		//获取员工列表
		$sql = "select p1.user_id,p1.username,p2.realname from `rwd_user` p1 join  `rwd_authentication` p2 on p1.user_id=p2.user_id where " .
			"p1.cmp_admid={$cmp_admid['cmp_admid']} and p1.invite_userid={$cmp_admid['cmp_admid']}"; //公司员工id
		$users = $mysql->db_fetch_arrays($sql);
		$data = array();
		foreach ($users as $k => $v) {
			#if( $v[user_id]==$cmp_admid['cmp_admid'] ) continue ;
			$sql = "select sum(account) as acc from `trend_borrow` where  invite_userid={$v['user_id']} "; //统计金额
			$sum = $mysql->db_fetch_array($sql);
			$data[$k]['user_id'] = $v['user_id'];
			$data[$k]['username'] = $v['username'];
			$data[$k]['realname'] = $v['realname'];
			$data[$k]['acc'] = $sum['acc'];
		}
		return $data;
	}

	//资产端统i计详情
	public static function jminvitecount_detail($data = array()) {
		global $mysql;

		if ($data['time1'] != '' and $data['time2'] != '') {
			$whStart = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		$uid = $data['userid']; //邀请人id

		$sqlbr = "create TEMPORARY table if not exists `trend_borrow`  select  id,name,account,user_id   from `rwd_borrow`  " .
			" where status=3" . $whStart . $whEnd . "  ";

		$sqlbr = str_replace('addtime', 'success_time', $sqlbr);
		$mysql->db_fetch_arrays($sqlbr);

		$sql = "select p1.username,p1.user_id,p2.realname from `rwd_user` p1 join `rwd_authentication` p2 on p1.user_id=p2.user_id  where p1.invite_userid={$uid}";
		$result = $mysql->db_fetch_arrays($sql);
		$datas = array();
		foreach ($result as $k => $v) {
			$sql = "select t.id,t.name,t.account,u.username,a.realname from `trend_borrow` t join `rwd_user` u
			on t.user_id=u.user_id join `rwd_authentication`  a
			on t.user_id=a.user_id  where t.user_id={$v['user_id']}";
			$res = $mysql->db_fetch_arrays($sql);
			foreach ($res as $r) {
				$datas[] = $r;
			}

		}
		return $datas;

	}
	//全员营销活动 6-7月
	public static function FullActivity($data = array()) {
		global $mysql;
		if ($data['time1'] == '' or $data['time2'] == '') {
			return false;
		}

		if ($data['time1']) {
			$whStart = ' and U.addtime >=' . strtotime($data['time1'] . " 00:00:00") . ' ';
		}

		if ($data['time2']) {
			$whEnd = ' and U.addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		if ($data['time1']) {
			$whStart1 = ' and addtime >=' . strtotime($data['time1'] . " 00:00:00") . ' ';
		}

		if ($data['time2']) {
			$whEnd1 = ' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		if ($data['time1']) {
			$whStart2 = ' and addtime <=' . strtotime($data['time1'] . " 00:00:00" . '+1 day') . ' ';
		}

		if ($data['time2']) {
			$whEnd2 = ' and addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}

		//0 全部 1 员工 2 加盟商
		if ($data['type'] == 0) {
			$_sql = "A.department > 0 or U.cmp_admid > 0";
		}
		if ($data['type'] == 1) {
			$_sql = "A.department > 0";
		}
		if ($data['type'] == 2) {
			$_sql = "U.cmp_admid > 0";
		}
		$sqlbr = "create TEMPORARY table if not exists `trend_borrow` select  id,user_id,account " .
			"from `rwd_borrow_tender`    where status = 1 "; //期间内成功投资记录
		$mysql->db_fetch_arrays($sqlbr); //临时表
		$sql = "create TEMPORARY table if not exists `trend_user` select  R.user_id ,sum( R.account) as acc " .
			" from `rwd_borrow_tender` R  left  join  `trend_borrow` T  on  R.borrow_id=T.id  where T.id>0 and  R.status=1 " . $whStart1 . $whEnd1 . "  group by  R.user_id order by acc desc";
		$mysql->db_fetch_arrays($sql); //id,总金额
		$sql = 'create TEMPORARY table if not exists `userlist`  select   T.user_id, R.invite_userid, T.acc ,R.username,R.addtime  from ' .
			' trend_user T  left join rwd_user R  on T.user_id=R.user_id ';
		$mysql->db_query($sql);
		$mysql->db_fetch_arrays('select *  from `userlist`');
		//区别员工和加盟商
		$sql = "select U.user_id,A.realname,U.username from `rwd_user` U join `rwd_authentication` A on U.user_id=A.user_id where " . $_sql;
		$result = $mysql->db_fetch_arrays($sql);
		foreach ($result as $k => $v) {
			//邀请个数
			$sqlnum = "select count(*) as num from `rwd_user` U left join `rwd_authentication` A on U.user_id=A.user_id where U.invite_userid=" . $v['user_id'] . $whStart . $whEnd . "and A.real_status =1";
			$result = $mysql->db_fetch_array($sqlnum);
			$num_yao = $result['num'];
			if ($num_yao < 20) {
				$score_yao = $num_yao;
			}
			if ($num_yao >= 20) {
				$score_yao = 20;
			}
			//投资过的
			$sql = "select count(*) as dd  from `userlist` where acc>= 1000 and invite_userid={$v['user_id']} " . $whStart1 . $whEnd1; //投资过的
			$re = $mysql->db_fetch_array($sql);
			$num_tou = $re['dd'];
			if ($num_tou < 5) {
				$score_tou = 6 * $num_tou;
			}
			if ($num_tou == 5) {
				$score_tou = 30;
			}
			if ($num_tou > 5) {
				$score_tou = 30 + ($num_tou - 5);
			}
			//本人待收
			$sql = "select collection from `rwd_account_log` where user_id=" . $v['user_id'] . $whStart2 . 'order by addtime desc limit 1';
			$wait_start = $mysql->db_fetch_array($sql);
			$sql = "select collection from `meiridaishou` where user_id=" . $v['user_id'] . $whEnd2 . 'order by addtime desc limit 1';
			$wait_end = $mysql->db_fetch_array($sql);
			if ($wait_start == false) {
				$wait_start['collection'] = 0;
			}
			if ($wait_end == false) {
				$wait_end['collection'] = 0;
			}
			if ($wait_end['collection'] - $wait_start['collection'] < 0) {
				$wait = 0;
			} else {
				$wait = $wait_end['collection'] - $wait_start['collection'];
			}
			//被邀请人id
			$sql = "select user_id from `rwd_user` where invite_userid=" . $v['user_id'];
			$re = $mysql->db_fetch_arrays($sql);
			foreach ($re as $k2 => $v2) {
				$sql = "select user_id,collection from `rwd_account_log` where user_id=" . $v2['user_id'] . $whStart2 . 'order by addtime desc limit 1';
				$wait_start = $mysql->db_fetch_array($sql);
				$sql = "select user_id,collection from `meiridaishou` where user_id=" . $v2['user_id'] . $whEnd2 . 'order by addtime desc limit 1';
				$wait_end = $mysql->db_fetch_array($sql);
				if ($wait_start == false) {
					$wait_start['collection'] = 0;
				}
				if ($wait_end == false) {
					$wait_end['collection'] = 0;
				}
				if ($wait_end['collection'] - $wait_start['collection'] < 0) {
					$wait_yao = 0;
				} else {
					$wait_yao = $wait_end['collection'] - $wait_start['collection'];
				}
				$total1 += $wait_yao;
			}
			$total = $total1 + $wait;
			$total1 = 0;
			if ($total > 10000 && $total < 250000) {
				$score_money = floor(($total / 10000)) * 2;
			} elseif ($total == 250000) {
				$score_money = 50;
			} elseif ($total > 250000) {
				$score_money = 50 + (floor(($total - 250000) / 10000));
			}
			$score = $score_yao + $score_tou + $score_money;
			$_R[] = array('user_id' => $v['user_id'], 'username' => $v['username'], 'realname' => $v['realname'], 'num_yao' => $num_yao, 'num_tou' => $num_tou, 'total' => $total, 'score' => $score);
			$score_money = 0;
		}
		$p = 'score';
		foreach ($_R as $k => $v) {
			$newArr[$k] = $v[$p];
		}
		array_multisort($newArr, SORT_DESC, $_R);
		$_R = array_slice($_R, 0, 20);
		return $_R;
	}
	//亲子活动用户统计
	public static function userListCount($data = array(), $flag) {
		global $mysql;

		$type = isset($data['type']) ? $data['type'] : "";
		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];
		// $t1 = 1477929600 ;
		// $t2 = 1478793600 ;
		$t1 = 1478854800; //2016.11.11 17:00
		$t2 = 1479052800; //2016.11.14 00:00

		$_order = 'order by BTT.account desc';
		$rechargeUser = "select AR.user_id ,sum(AR.money) as money from {account_recharge} AR where AR.addtime between $t1 and $t2 and AR.payment=57 and AR.type =1 and AR.status=1 group by AR.user_id";
		$tenderUser = "select BT.user_id ,sum(BT.account) as account from {borrow_tender} BT where BT.addtime between $t1 and $t2 and BT.status=1 group by BT.user_id";
		if ($flag == true) {
			//老用户
			$_select = " U.user_id,U.invite_userid,U.username,BTT.account,ATT.money ";
			$tabjoin = ",(" . $tenderUser . ") BTT ";
			$tabjoin .= ",( $rechargeUser ) ATT ";
			$_sql = "U.addtime < $t1 and U.user_id=BTT.user_id and U.user_id=ATT.user_id and ATT.money>=400 and BTT.account>=400";

		} else {
			//新用户
			$_select = " U.user_id,U.invite_userid,U.username,BTT.account ";
			$tabjoin = ",(" . $tenderUser . ") BTT ";
			$_sql = "U.addtime between $t1 and $t2 and U.user_id=BTT.user_id and BTT.account>=400";

		}

		$sql = "select SELECT from `{user}` as U " . $tabjoin;
		$sql .= " where  $_sql ORDER LIMIT";
		$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		if ($data['limit'] == "all") {
			$limit = '';
		}

		$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
		$list = $list ? $list : array();
		foreach ($list as $key => $value) {

			$res1 = $mysql->db_fetch_array("select phone,realname,card_id from rwd_authentication where user_id= {$value['user_id']}");
			$list[$key]['realname'] = $res1['realname'];
			$list[$key]['phone'] = $res1['phone'];
			$list[$key]['card'] = $res1['card_id'];
			if ($value['invite_userid'] == "") {
				continue;
			}

			$res = $mysql->db_fetch_array("select us.user_id,us.username,au.phone,au.realname from rwd_user as us,rwd_authentication as au where au.user_id=us.user_id and us.user_id= {$value['invite_userid']}");
			$list[$key]['invite_name'] = $res['username'];
			$list[$key]['invite_realname'] = $res['realname'];
			$list[$key]['invite_phone'] = $res['phone'];
		}
		return array(
			'list1' => $list,
			'total' => $total,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);
	}
	//感恩活动相关

	//新注册用户与客户邀请
	static public function inviteCount($data, $flag) {
		global $mysql;
		$type = isset($data['type']) ? $data['type'] : "";
		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];
		$t1 = 1479312000; //2016.11-17-00:00:00
		$t2 = 1480521599; //2016.11-30-23:59:59
		//$t3=1479398399;//2016.11-17-23:59:59
		//查询用户投资记录
		$tenderUser = "select BT.user_id ,sum(BT.account) as account from {borrow_tender} BT where BT.addtime between $t1 and $t2 and BT.status=1 group by BT.user_id";
		//提取有效客户
		$usersql = "select user_id from {account} group by user_id";
		//得到在活动时间内注册并已开通汇付且有上级的用户
		$inviteUser = "select invite_userid,count(invite_userid) as coun from rwd_user where invite_userid>0 and addtime between $t1 and $t2 and huifu_uid<>'' group by invite_userid having coun>0 order by coun desc";
		switch ($flag) {
		case 'a':
			$_select = " U.user_id,U.username,BTT.account ";
			$tabjoin = ",(" . $tenderUser . ") BTT ";
			$_sql = "U.addtime between $t1 and $t2 and U.user_id=BTT.user_id and BTT.account>=5000";
			$_order = 'order by BTT.account desc';
			break;
		case 'b':
			$_select = " U.user_id,U.username,BTT.coun ";
			$tabjoin = ",(" . $inviteUser . ") BTT ";
			$_sql = " U.user_id=BTT.invite_userid ";
			$_order = 'order by BTT.coun desc';
			break;
		case 'c':
			$_select = " U.user_id,U.username,BTT.account ";
			$tabjoin = ",(" . $tenderUser . ") BTT ";
			$_sql = " addtime < $t1 and U.user_id=BTT.user_id and BTT.account>=20000";
			$_order = 'order by BTT.account desc';
			break;

		default:
			$_select = " U.user_id,U.username,BTT.account ";
			$tabjoin = ",(" . $tenderUser . ") BTT ";
			$_sql = "U.addtime between $t1 and $t2 and U.user_id=BTT.user_id and BTT.account>=5000";
			$_order = 'order by BTT.account desc';
			break;
		}

		$sql = "select SELECT from `{user}` as U " . $tabjoin;
		$sql .= " where  $_sql ORDER ";
		$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		if ($data['limit'] == "all") {
			$limit = '';
		}

		$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
		$list = $list ? $list : array();
		foreach ($list as $key => $value) {
			if ($flag == c) {
				//查询首投客户
				$res2 = $mysql->db_fetch_array("select count(*) as tong from rwd_borrow_tender where  addtime<$t1 and user_id= {$value['user_id']}");
				if ($res2['tong'] < 1) {
					$list1[$key]['user_id'] = $value['user_id'];
					$list1[$key]['username'] = $value['username'];
					$list1[$key]['account'] = $value['account'];
					$res1 = $mysql->db_fetch_array("select phone,realname,card_id from rwd_authentication where user_id= {$value['user_id']}");
					$list1[$key]['realname'] = $res1['realname'];
					$list1[$key]['phone'] = $res1['phone'];
					$list1[$key]['card'] = $res1['card_id'];
				}
			} else {
				$res1 = $mysql->db_fetch_array("select phone,realname,card_id from rwd_authentication where user_id= {$value['user_id']}");
				$list[$key]['realname'] = $res1['realname'];
				$list[$key]['phone'] = $res1['phone'];
				$list[$key]['card'] = $res1['card_id'];

			}

		}
		if ($flag == 'c') {
			return array(
				'lists' => $list1,
				'total' => $total,
				'page' => $page,
				'epage' => $epage,
				'total_page' => $total_page,
			);
		} else {
			return array(
				'lists' => $list,
				'total' => $total,
				'page' => $page,
				'epage' => $epage,
				'total_page' => $total_page,
			);
		}
	}

	//新增待收
	public static function inviteCount1($data, $sort = SORT_DESC) {
		global $mysql;

		$type = isset($data['type']) ? $data['type'] : "";
		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];
		$t1 = 1479312000; //2016.11-17-00:00:00
		$t2 = 1480521599; //2016.11-30-23:59:59
		//提取有效客户
		$usersql = "select user_id from {account} group by user_id";
		$_select = " U.user_id,U.username";
		$tabjoin = ",(" . $usersql . ") SQ ";
		$_sql = "   U.user_id=SQ.user_id ";
		$_order = 'order by user_id desc';
		$sql = "select SELECT from `{user}` as U " . $tabjoin;
		$sql .= " where  $_sql ORDER ";
		$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array('count(1) as num', '', ''), $sql));
		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		if ($data['limit'] == "all") {
			$limit = '';
		}

		$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array($_select, $_order, $limit), $sql));
		$list = $list ? $list : array();
		foreach ($list as $key => $value) {

			$borrowstalist = $mysql->db_fetch_array("select BT.user_id,sum(BT.wait_account) as account,count(*) as con from rwd_borrow_tender as BT,(select id from rwd_borrow where status=1) as BO where BT.borrow_id=BO.id and status=1 and user_id={$value['user_id']} and addtime<=$t2 ");
			if ($borrowstalist['con'] > 0) {
				$outdaishou = "select collection,user_id from meiridaishou where user_id={$value['user_id']} and addtime<=$t2 order by addtime desc limit 1";
				$stadaishou = $mysql->db_fetch_array("select A.user_id,(B.collection-A.collection) as coll from meiridaishou as A,(" . $outdaishou . ") as B where A.user_id=B.user_id and addtime>=$t1 order by addtime asc  limit 1");
				if ($stadaishou['coll'] + $borrowstalist['account'] >= 100000) {
					$list1[$key]['user_id'] = $value['user_id'];
					$list1[$key]['username'] = $value['username'];
					$list1[$key]['collection'] = number_format($stadaishou['coll'] + $borrowstalist['account'], 2);
					$res1 = $mysql->db_fetch_array("select phone,realname,card_id from rwd_authentication where user_id= {$value['user_id']}");
					$list1[$key]['realname'] = $res1['realname'];
					$list1[$key]['phone'] = $res1['phone'];
					$list1[$key]['card'] = $res1['card_id'];
				}
			} else {

				$outdaishou = "select collection,user_id from meiridaishou where user_id={$value['user_id']} and addtime<=$t2 order by addtime desc limit 1";
				$stadaishou = $mysql->db_fetch_array("select A.user_id,(B.collection-A.collection) as coll from meiridaishou as A,(" . $outdaishou . ") as B where A.user_id=B.user_id and addtime>=$t1 order by addtime asc  limit 1");
				if ($stadaishou['coll'] >= 100000) {
					$list1[$key]['user_id'] = $value['user_id'];
					$list1[$key]['username'] = $value['username'];
					$list1[$key]['collection'] = number_format($stadaishou['coll'], 2);
					$res1 = $mysql->db_fetch_array("select phone,realname,card_id from rwd_authentication where user_id= {$value['user_id']}");
					$list1[$key]['realname'] = $res1['realname'];
					$list1[$key]['phone'] = $res1['phone'];
					$list1[$key]['card'] = $res1['card_id'];
				}
			}
		}
		//var_dump($list1);
		if (is_array($list1)) {
			foreach ($list1 as $row_array) {
				if (is_array($row_array)) {
					$key_array[] = $row_array['collection'];
				} else {
					return FALSE;
				}
			}
		} else {
			return FALSE;
		}

		array_multisort($key_array, $sort, $list1);

		//var_dump($list1);exit;
		return array(
			'lists' => $list1,
			'total' => $total,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);

	}
	function UserEvery_wmy($data = array()) {
		global $mysql;

		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];
		$type = $data['type'];
		if ($type == 1) {
			$select = "M.type,M.account as account,M.week,M.user_id,A.card_id,A.realname,U.username,A.phone ";
			$sql = "select max(week) as week from `meiritouzi`";
			$n = $mysql->db_fetch_array($sql);
			for ($i = 1; $i <= $n['week']; $i++) {
				$_sql1 = " where M.week = '$i'  and year=" . $data['year'];
				$sql1 = "select " . $select . " from `meiritouzi` as M left join `rwd_authentication` as A on M.user_id = A.user_id left join `rwd_user` as U on M.user_id=U.user_id " . $_sql1;
				$list1 = $mysql->db_fetch_arrays($sql1);
				foreach ($list1 as $k => $v1) {
					if (mb_strlen($v1['card_id']) == 18) {
						//年龄
						$birth = strtotime(mb_substr($v1['card_id'], 6, 8));
						$age = intval((time() - $birth) / 86400 / 365);
						$list1[$k]['age'] = $age;
						//性别
						$sexint = (int) mb_substr($v1['card_id'], 16, 1);
						$sex = $sexint % 2 == 0 ? '女' : '男';
						$list1[$k]['sex'] = $sex;
					} else {
						$birth = strtotime(mb_substr($v1['card_id'], 6, 6));
						$age = intval(time() - $birth / 86400 / 365);
						$list1[$k]['age'] = $age;
						//性别
						$sexint = (int) mb_substr($v1['card_id'], 14, 1);
						$sex = $sexint % 2 == 0 ? '女' : '男';
						$list1[$k]['sex'] = $sex;
					}
				}
				$list[$i]['user'] = $list1;
			}

		}
		if ($type == 2) {
			$select = "M.type,M.account as account,M.moth,M.user_id,A.card_id,A.realname,U.username,A.phone ";
			$sql = "select max(moth) as moth from `meiritouzi`";
			$n = $mysql->db_fetch_array($sql);
			for ($i = 1; $i <= $n['moth']; $i++) {
				$_sql1 = " where M.moth = '$i'  and year=" . $data['year'];
				$sql1 = "select " . $select . " from `meiritouzi` as M left join `rwd_authentication` as A on M.user_id = A.user_id left join `rwd_user` as U on M.user_id=U.user_id " . $_sql1;
				$list1 = $mysql->db_fetch_arrays($sql1);
				foreach ($list1 as $k => $v1) {
					if (mb_strlen($v1['card_id']) == 18) {
						//年龄
						$birth = strtotime(mb_substr($v1['card_id'], 6, 8));
						$age = intval((time() - $birth) / 86400 / 365);
						$list1[$k]['age'] = $age;
						//性别
						$sexint = (int) mb_substr($v1['card_id'], 16, 1);
						$sex = $sexint % 2 == 0 ? '女' : '男';
						$list1[$k]['sex'] = $sex;
					} else {
						$birth = strtotime(mb_substr($v1['card_id'], 6, 6));
						$age = intval(time() - $birth / 86400 / 365);
						$list1[$k]['age'] = $age;
						//性别
						$sexint = (int) mb_substr($v1['card_id'], 14, 1);
						$sex = $sexint % 2 == 0 ? '女' : '男';
						$list1[$k]['sex'] = $sex;
					}
				}
				$list[$i]['user'] = $list1;
			}
		}
		if ($type == 3) {
			$select = "M.type,M.account as account,M.year,M.user_id,A.card_id,A.realname,U.username,A.phone ";
			$_sql1 = " where week = 0 and moth = 0 and  year=" . $data['year'];
			$sql1 = "select " . $select . " from `meiritouzi` as M left join `rwd_authentication` as A on M.user_id = A.user_id left join `rwd_user` as U on M.user_id=U.user_id " . $_sql1;
			$list1 = $mysql->db_fetch_arrays($sql1);
			foreach ($list1 as $k => $v1) {
				if (mb_strlen($v1['card_id']) == 18) {
					//年龄
					$birth = strtotime(mb_substr($v1['card_id'], 6, 8));
					$age = intval((time() - $birth) / 86400 / 365);
					$list1[$k]['age'] = $age;
					//性别
					$sexint = (int) mb_substr($v1['card_id'], 16, 1);
					$sex = $sexint % 2 == 0 ? '女' : '男';
					$list1[$k]['sex'] = $sex;
				} else {
					$birth = strtotime(mb_substr($v1['card_id'], 6, 6));
					$age = intval(time() - $birth / 86400 / 365);
					$list1[$k]['age'] = $age;
					//性别
					$sexint = (int) mb_substr($v1['card_id'], 14, 1);
					$sex = $sexint % 2 == 0 ? '女' : '男';
					$list1[$k]['sex'] = $sex;
				}
			}
			$list[0]['user'] = $list1;
		}

		/* $sql="select count(1) as num from `meiritouzi` as M ".$_sql;
			$row = $mysql->db_fetch_array($sql);
			$total = $row['num'];
			$total_page = ceil($total / $epage);
			$index = $epage * ($page - 1);
		*/

		return $list;
	}
	//获得周
	function get_week($year) {
		$year_start = $year . "-01-01";
		$year_end = $year . "-12-31";
		$startday = strtotime($year_start);
		if (intval(date('N', $startday)) != '1') {
			$startday = strtotime("next monday", $startday);
			//获取年第一周的日期
		}
		$year_mondy = date("Y-m-d", $startday); //获取年第一周的日期
		$endday = strtotime($year_end);
		if (intval(date('W', $endday)) == '7') {
			$endday = strtotime("lastsunday", strtotime($year_end));
		}
		$num = intval(date('W', $endday));
		for ($i = 1; $i <= $num; $i++) {
			$j = $i - 1;
			$start_date = date("Y-m-d", strtotime("$year_mondy $j week "));
			$end_day = date("Y-m-d", strtotime("$start_date +7 day"));
			$week_array[$i] = array(
				$start_date, $end_day);
		}
		return $week_array;
	}
	//获得月
	function get_month($year) {
		$s1 = strtotime($year . '-01-01');
		$e1 = strtotime("+1 months", $s1);
		$num1 = intval(date('n', $s1));
		for ($i = 1; $i <= 12; $i++) {
			$month_array[$i] = array($s1, $e1);
			$s1 = $e1;
			$e1 = strtotime("+1 months", $s1);
		}
		return $month_array;
	}
	//更新  每周、月、年 投资统计
	function updateData($data = array()) {
		global $mysql;
		$week = self::get_week($data['year']);
		$month = self::get_month($data['year']);
		$type = $data['type'];

		if ($data['status'] == 1 && time() < mktime('19', '0', '0', date('m'), date('d'), date('y'))) {
			return array('msg' => "请于当天19点后更新！");
		}

		if ($type == 1) {
			//查询最大周数
			$sql = "select max(week) as zhou from `meiritouzi` where year=" . $data['year'];
			$num = $mysql->db_fetch_array($sql);
			if ($n['zhou'] == "") {
				$n['zhou'] = 0;
			}

			//每周
			while ($num['zhou'] < 52) {
				$num['zhou']++;
				if ($data['year'] == date('Y')) {
					if ($num['zhou'] == intval(date('W', time()))) {
						break;
					}

				}

				$start = strtotime($week[$num['zhou']][0]);
				$end = strtotime($week[$num['zhou']][1]);
				//累投
				$sql = "SELECT SUM( account ) AS acc, user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $start . " AND addtime <=" . $end . " AND STATUS =1 GROUP BY user_id order by acc desc limit 0,1";
				$list = $mysql->db_fetch_array($sql);
				if (is_array($list)) {
					$sql = "SELECT SUM( account ) AS acc, user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $start . " AND addtime <=" . $end . " AND STATUS =1  GROUP BY user_id having sum(account)=" . $list['acc'] . " order by acc desc";
					$list1 = $mysql->db_fetch_arrays($sql);
					foreach ($list1 as $key => $value) {
						$sql1 = "insert into `meiritouzi` set year=" . $data['year'] . ", week = " . $num['zhou'] . " ,type = '1' ,`account`=" . $value['acc'] . " , `user_id` = " . $value['user_id'];
						$R['1'] = $mysql->db_query($sql1);
					}
				}
				//单笔
				$sql2 = "SELECT * FROM  `rwd_borrow_tender` WHERE addtime >=" . $start . " AND addtime <= " . $end . " AND STATUS =1 ORDER BY CONVERT(  `account` , SIGNED ) DESC LIMIT 0 , 1";
				$list2 = $mysql->db_fetch_array($sql2);
				//print_r($list2);
				if (is_array($list2)) {
					$sql3 = "SELECT distinct(user_id),account,user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $start . " AND addtime <= " . $end . " AND STATUS =1 and account='" . $list2['account'] . "'";
					$list3 = $mysql->db_fetch_arrays($sql3);

					foreach ($list3 as $key => $value) {
						$sql1 = "insert into `meiritouzi` set year=" . $data['year'] . ", week = " . $num['zhou'] . " ,type = '2' ,`account`=" . $value['account'] . " , `user_id` = " . $value['user_id'];

						$R['2'] = $mysql->db_query($sql1);
					}
				}
			}
		}
		if ($type == 2) {
			//每月
			//查询最大月数
			$sql = "select max(moth) as moth from `meiritouzi` where year=" . $data['year'];
			$n = $mysql->db_fetch_array($sql);
			if ($n['moth'] == "") {
				$n['moth'] = 0;
			}

			while ($n['moth'] < 12) {
				$n['moth']++;
				if ($data['year'] == date('Y')) {
					if ($n['moth'] == intval(date('n', time()))) {
						break;
					}

				}

				$s1 = $month[$n['moth']][0];
				$e1 = $month[$n['moth']][1];
				//月累投
				$sql = "SELECT SUM( account ) AS acc, user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <=" . $e1 . " AND STATUS =1 GROUP BY user_id order by acc desc limit 0,1";
				$list1 = $mysql->db_fetch_array($sql);
				if (is_array($list1)) {
					$sql = "SELECT SUM( account ) AS acc, user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <=" . $e1 . " AND STATUS =1  GROUP BY user_id having sum(account)=" . $list1['acc'] . " order by acc desc";
					$list2 = $mysql->db_fetch_arrays($sql);
					foreach ($list2 as $key => $value) {
						$sql1 = "insert into `meiritouzi` set year=" . $data['year'] . " ,moth = " . $n['moth'] . " ,type = '1' ,`account`=" . $value['acc'] . " , `user_id` = " . $value['user_id'];

						$R['1'] = $mysql->db_query($sql1);
					}
				}
				//月单笔
				$sql2 = "SELECT * FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <= " . $e1 . " AND STATUS =1 ORDER BY CONVERT(  `account` , SIGNED ) DESC LIMIT 0 , 1";
				$listm = $mysql->db_fetch_array($sql2);
				if (is_array($listm)) {
					$sql = "SELECT distinct(user_id),account,user_id  FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <= " . $e1 . " AND STATUS =1 and account =" . $listm['account'];
					$list6 = $mysql->db_fetch_arrays($sql);
					foreach ($list6 as $key => $value) {
						$sql3 = "insert into `meiritouzi` set year=" . $data['year'] . " ,moth = " . $n['moth'] . " ,type = '2' ,`account`=" . $value['account'] . " , `user_id` = " . $value['user_id'];
						$R['2'] = $mysql->db_query($sql3);
					}
				}
			}
		}
		if ($type == 3) {
			/* $sql="select max(year) as year from `meiritouzi` ";
				$n=$mysql->db_fetch_array($sql);
			*/

			$sql = "delete from `meiritouzi` where year=" . $data['year'] . " and moth =0 and 	week =0";
			$mysql->db_query($sql);

			$s1 = strtotime($data['year'] . '-01-01');
			$e1 = strtotime($data['year'] . '-12-31') + 86400;
			//年累投
			$sql = "SELECT SUM( account ) AS acc, user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <=" . $e1 . " AND STATUS =1  GROUP BY user_id order by acc desc limit 0,1";
			$list1 = $mysql->db_fetch_array($sql);
			if (is_array($list1)) {
				$sql = "SELECT SUM( account ) AS acc, user_id FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <=" . $e1 . " AND STATUS =1   GROUP BY user_id having sum(account)=" . $list1['acc'] . " order by acc desc";
				$list2 = $mysql->db_fetch_arrays($sql);
				foreach ($list2 as $key => $value) {
					$sql1 = "insert into `meiritouzi` set year=" . $data['year'] . " ,moth =  0, week = 0, type = '1' ,`account`=" . $value['acc'] . " , `user_id` = " . $value['user_id'];
					$R['1'] = $mysql->db_query($sql1);
				}
			}
			//年单笔
			$sql2 = "SELECT * FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <= " . $e1 . " AND STATUS =1 ORDER BY CONVERT(  `account` , SIGNED ) DESC LIMIT 0 , 1";
			$listy = $mysql->db_fetch_array($sql2);
			if (is_array($listy)) {
				$sql = "SELECT distinct(user_id),account,user_id  FROM  `rwd_borrow_tender` WHERE addtime >=" . $s1 . " AND addtime <= " . $e1 . " AND STATUS =1 and account =" . $listy['account'];
				$list6 = $mysql->db_fetch_arrays($sql);
				foreach ($list6 as $key => $value) {
					$sql3 = "insert into `meiritouzi` set year=" . $data['year'] . " ,moth =  0, week = 0, type = '2' ,`account`=" . $value['account'] . " , `user_id` = " . $value['user_id'];
					$R['2'] = $mysql->db_query($sql3);
				}
			}
		}
		if ($R['1'] && $R['2']) {
			return array('msg' => "更新成功！请重新搜索数据");
		} else {
			return array('msg' => "更新失败！");
		}

	}

	function countInvest($data = array()) {
		global $mysql;
		$stime = strtotime($data['time1'] . " 00:00:00"); //10-1
		$etime = strtotime($data['time2'] . " 23:59:59"); //12-31
		$_order = " order by acc desc";
		$sql_table = "create TEMPORARY table if not exists `temp_trend_user` select R.user_id,U.username,U.first_invest_time,U.addtime,sum(R.account) as acc from `rwd_borrow_tender` R ,`rwd_user` U where R.user_id=U.user_id and R.status=1 and R.addtime >=" . $stime . " and R.addtime <= " . $etime . " group by  R.user_id order by acc desc";
		$mysql->db_query($sql_table); //id,总金额

		$sql1 = "select username,acc,user_id,addtime,first_invest_time  from `temp_trend_user` where first_invest_time >=" . $stime . " and first_invest_time <=" . $etime . $_order;
		$list = $mysql->db_fetch_arrays($sql1);
		foreach ($list as $k => $v) {
			if ($v['addtime'] < $stime) {
				$list[$k]['is_first'] = "早期注册";
			} else {
				$list[$k]['is_first'] = "当月注册";
			}

		}
		return $list;
	}

	//更新用户首次投资时间
	function updateInvestTime($userid = 0, $itime = 0) {
		global $mysql;
		if ($userid == 0) {
			return false;
		}

		if ($itime == 0) {
			$itime = time();
		}

		$uinfo = $mysql->db_fetch_array('select * from rwd_user where user_id=' . $userid);
		if ($uinfo['first_invest_time'] > 0) {
			return true;
		} else {
			$mysql->db_query('update rwd_user set first_invest_time=' . $itime . '  where user_id=' . $userid);
		}

	}
#凡是参与积分抽奖活动的
	#统计邀请人数-邀请投资额-
	function ThreeLottery_draw($data = array()) {
		global $mysql;
		$sdate = date('Y-m-d H:i:s', $data['time1']);
		$user = $mysql->db_fetch_arrays("SELECT DISTINCT user_id FROM  `winner_record`  where  act_id=31 and  atime>'{$sdate}'  limit 200 ");
		foreach ($user as $key => $value) {

			if ($value['user_id'] == "") {
				continue;
			}

			$users[] = $mysql->db_fetch_array("select U.user_id,U.username,U.invite_userid,U1.username as username1,AU.phone from `rwd_user` U
			left join `rwd_authentication` AU on AU.user_id=U.user_id
			left join `rwd_user` U1 on  U1.user_id=U.invite_userid where U.user_id={$value['user_id']}");
		}
		foreach ($users as $key => $val) {

			$invest = $mysql->db_fetch_array("select user_id,sum(account)as acc from `rwd_borrow_tender` where user_id={$val['user_id']} and status=1 and addtime>=" . $data['time1'] . " and addtime<=" . $data['time2']);

			$users[$key]['acc'] = intval($invest['acc']);
			if ($val['invite_userid'] == "") {
				$users[$key]['username1'] = '';
			} else {
				$user1 = $mysql->db_fetch_array("select user_id,username from `rwd_user` where user_id={$val['invite_userid']}");
				//邀请人用户名
				$users[$key]['username1'] = $user1['username'];
			}
			//邀请数量
			$yao = $mysql->db_fetch_array("select count(user_id) as cou from `rwd_user` where invite_userid={$val['user_id']} and  addtime>=" . $data['time1'] . " and addtime<=" . $data['time2']);
			$users[$key]['invite_num'] = intval($yao['cou']);
			//邀请用户共计投资
			$sql = "SELECT  SUM( B.account ) as acc1
			FROM  `rwd_user` U
			right JOIN `rwd_borrow_tender` B ON B.user_id = U.user_id
			WHERE  U.`invite_userid` ={$val['user_id']} and B.status=1 and B.addtime>=" . $data['time1'] . " and B.addtime<=" . $data['time2'];
			$user2 = $mysql->db_fetch_array($sql);
			//$users[$key]['cou ']=$user2['cou '];
			$users[$key]['acc1'] = intval($user2['acc1']);
		}
		return $users;
	}
	//2017年植树节活动
	function Arbor_Day() {
		global $mysql;
		$res = $mysql->db_fetch_arrays("select* from user_zhishujie limit 100");
		return $res;
	}

	//bumen-touzi-tongji
	function departmentyejicount($data = array()) {
		global $mysql;

		if ($data['department'] == '' || $data['department'] == '653') {
			$data['department'] = 0;
			if ($data['khphone'] == '' and $data['ygphone'] == '') {
				return false;
			}

		}
		$page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 20 : $data['epage'];
		if ($data['time1'] != '' && $data['time2'] != '') {
			$whStart = ' BT.addtime >=' . strtotime($data['time1'] . " 00:00:00") .
			' and BT.addtime <=' . strtotime($data['time2'] . " 23:59:59") . ' ';
		}
		if ($data['department'] > 0 && $data['department'] != '653') {
			$sql = "select name from rwd_linkage where id={$data['department']}";
			$resname = $mysql->db_fetch_array($sql);
			$whUser = 'AU.department=' . $data['department'];
		} else {
			$sql = 'select department from `rwd_authentication` where phone=' . $data['ygphone'];
			$r = $mysql->db_fetch_array($sql);
			$sql = "select name from rwd_linkage where id={$r['department']}";
			$resname = $mysql->db_fetch_array($sql);
			$whUser = 'AU.phone="' . $data['ygphone'] . '"';
		}

		$UserRe = $mysql->db_fetch_array("select group_concat( user_id ) users from {authentication} AU where  $whUser");

		$userIds = $UserRe['users'];
		if ($userIds == '') {
			return false;
		}
		$limit = 'limit 100';
		if (isset($data['limit']) && $data['limit'] == 'all') {
			$limit = '';
		}
		$sqltongji = "select  U2.realname,U2.phone,U.user_id,AU.realname as khrealname  ,AU.phone as khphone,AU.card_id,BT.account, B.time_limit,BT.addtime,U.first_invest_time " .
			"  from  {borrow_tender} BT  " .
			"left join {user} U on BT.user_id=U.user_id " .
			" left join {borrow} B on BT.borrow_id=B.id " .
			"left join {authentication} AU  on U.user_id=AU.user_id  " .
			"left join {authentication} U2 on U2.user_id=U.invite_userid ";

		if ($data['khphone'] != '') {
			$sqltongji .= "where   $whStart and  AU.phone='" . $data['khphone'] . "'   LIMIT";
		} else {
			$sqltongji .= "where   $whStart and  U.invite_userid in( $userIds ) LIMIT";
		}

		$result = $mysql->db_fetch_arrays(str_replace('LIMIT', $limit, $sqltongji));
		//部门名称

		foreach ($result as $key => $value) {
			$res = expCardId($value['card_id']);
			$result[$key]['sex'] = $res['sex'];
			$result[$key]['age'] = $res['age'];
			$result[$key]['departmentname'] = $resname['name'];
		}
		return $result;

	}
	//login timer
	function chkUserLoginTime($user) {
		global $mysql;
		#$mysql->debug = 1;
		$sql = 'select * from user_login_control where  `user`="' . $user . '"';
		$U = $mysql->db_fetch_array($sql);
		if ($U) {
			if (time() > $U['ltime']) {
				$U['num']++;
				$sql = 'update user_login_control set num="' . $U['num'] . '" where `user`="' . $user . '"';
				$mysql->db_query($sql);
				if ($U['num'] == 3) {
					$U['ltime'] = time() + 5 * 60;
					$sql = 'update user_login_control set ltime="' . $U['ltime'] . '" where `user`="' . $user . '"';
					$mysql->db_query($sql);
					return true;
				}
				if ($U['num'] == 4) {
					$U['ltime'] = time() + 30 * 60;
					$sql = 'update user_login_control set ltime="' . $U['ltime'] . '" where `user`="' . $user . '"';
					$mysql->db_query($sql);
					return true;
				}
				if ($U['num'] >= 5) {
					$U['ltime'] = time() + 6 * 60 * 60;
					$sql = 'update user_login_control set ltime="' . $U['ltime'] . '" where `user`="' . $user . '"';
					$mysql->db_query($sql);
					return true;
				}
				return true;
			} else {
				return '您尝试的次数过多，请稍后再试(账户锁定' . ($U['ltime'] - time()) . '秒)';}
		} else {
			$sql = 'insert into user_login_control  set  `user`="' . $user . '",ltime=' . time() . ',num=1';
			$mysql->db_query($sql);
			return true;
		}
	}
	//login timer release
	function releaseUserLoginTime($user) {
		global $mysql;
		$sql = 'update  user_login_control  set   num=0 , ltime=0  where   `user`="' . $user . '"';
		$mysql->db_query($sql);
		return true;
	}

	//渠道注册统计
	function RegChannelCount($data=array()){
        global $mysql;
        if($data['addtime1']==''||$data['addtime2']=='') return false;
        $where='where U.channel<>0';
        $page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage'];        
        if (isset($data['addtime1']) && $data['addtime1'] != '') {
        	$time1=strtotime($data['addtime1']);
			$where .= ' and U.addtime>='.$time1;
		}
		if (isset($data['addtime2']) && $data['addtime2'] != '') {
			$time2=strtotime($data['addtime2']);
			$where .= ' and U.addtime<='.$time2;
		}
        $sql='select SELECT from rwd_user U left join rwd_authentication A  on U.user_id=A.user_id '.$where.' ORDER LIMIT';
        if (isset($data['limit'])) {
			$_limit = "";
			if ($data['limit'] != "all") {
				$_limit = "  limit " . $data['limit'];
			}

			$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array(' U.addtime,U.username,U.channel,A.phone,A.realname,U.huifu_user', ' order by U.addtime desc ', $_limit), $sql));

			return $list;
		}
		$row = $mysql->db_fetch_array(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array(' count(1) as num ', '', ''), $sql));
		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		$list = $mysql->db_fetch_arrays(str_replace(array('SELECT', 'ORDER', 'LIMIT'), array(' U.addtime,U.username,U.channel,A.phone,A.realname,U.huifu_user', ' order by U.addtime desc ', $limit), $sql));
		$list = $list ? $list : array();
		return array(
			'list' => $list,
			'total' => $total,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);
	}
	//注册、投资统计
	 function RegInvestCount($data=array()){
	 	global $mysql;
	    $where="where U.cmp_admid=0 and A.department=0  ";
	    $page = empty($data['page']) ? 1 : $data['page'];
		$epage = empty($data['epage']) ? 10 : $data['epage']; 
	     if($data['addtime1']==''||$data['addtime2']=='') {
	      	//默认查询时间段
	       $time1=strtotime(date('Y-m-d'.'00:00:00'));
	       $time2=strtotime(date('Y-m-d'.'23:59:59'));
	       $where.=' and U.addtime between '.$time1.' and '. $time2.' ';
	     }else{
	       $where.=' and U.addtime between '.strtotime($data['addtime1']).' and '. strtotime($data['addtime2']).' ';
	     }
	      $sql='SELECT SELECTS
			FROM  `rwd_user` U
			LEFT JOIN rwd_borrow_tender B ON U.user_id = B.user_id 
			LEFT JOIN `rwd_authentication` A ON U.user_id=A.user_id
			'.$where.' ORDER LIMIT';
		if (isset($data['limit'])) {
			$_limit = "";
			if ($data['limit'] != "all") {
				$_limit = "  limit " . $data['limit'];
			}

			$list = $mysql->db_fetch_arrays(str_replace(array('SELECTS', 'ORDER', 'LIMIT'), array(' COUNT( U.user_id ) regcount , COUNT( U.huifu_uid ) opencount, U.channel, COUNT( B.account ) investcount', 'GROUP BY U.channel desc ', $_limit), $sql));

			return $list;
		}
        $row = $mysql->db_fetch_array(str_replace(array('SELECTS', 'ORDER', 'LIMIT'), array(' count(DISTINCT U.channel) as num ', '', ''), $sql));
		$total = $row['num'];
		$total_page = ceil($total / $epage);
		$index = $epage * ($page - 1);
		$limit = " limit {$index}, {$epage}";
		$list = $mysql->db_fetch_arrays(str_replace(array('SELECTS', 'ORDER', 'LIMIT'), array(' COUNT( U.user_id) regcount , COUNT( U.huifu_uid ) opencount, U.channel, COUNT( B.account ) investcount', 'GROUP BY U.channel desc ', $_limit), $sql));
		$list = $list ? $list : array();
		return array(
			'list' => $list,
			'total' => $total,
			'page' => $page,
			'epage' => $epage,
			'total_page' => $total_page,
		);
		



	 }
}



