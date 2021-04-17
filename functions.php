<?
date_default_timezone_set('Europe/Minsk');

class user_Functions
{
	function IsBanned($nick)
	{
		$db = new Database;
		$post = $nick;
		$name = $db->safesql( htmlspecialchars( trim( $post ) ) );
		$name = preg_replace('#\s+#i', ' ', $name);
		if($name != "")
		{
			$result = $db->super_query("SELECT * FROM `".TABLE_USERS."` WHERE `".TABLE_USERS_NAME."` = '$name' and `pLock` = '1'");
			if($result != false) return 1;
			else return 0;
		}
	}
	function IsNoAdmin($nick)
	{
		$db = new Database;
		$array = $db->super_query("SELECT `pID`,`pAdmin` FROM `".TABLE_USERS."` WHERE `".TABLE_USERS_NAME."` = '".$nick."'");
		$acid = $array['pID'];
		if($array['pAdmin'] > 0)
		{
			$_SESSION['adminlogged'] = "-1";
			$zapros = $db->super_query("SELECT * FROM `admins` WHERE `ID` = '".$acid."'");
			if(!$zapros)
			{
				return 0;
			}
			if($zapros['IP'] > 0)
			{
				$myip = $_SERVER['REMOTE_ADDR'];
			    if($zapros['IP'] == 1)
			    {
					$arr = explode('.', $myip);
					$findip = $arr[0] .= ".";
					$findip .= $arr[1].= ".*.*";
					$zapr = $db->super_query("SELECT * FROM `adminip` WHERE `ID` = '".$acid."' and `IP`='".$findip."'");
					if(!$zapr)
					{
					    $zapr = $db->super_query("SELECT * FROM `adminip` WHERE `ID` = '".$acid."' and `IP`='".$myip."'");
				    	if(!$zapr)
    					{
						    return 2;
					    }
					}    
				}
			}
			if($zapros['Pass'] == "0")
			{
				$_SESSION['adminlogged'] = 1;
			}
		}
		return 1;
	}
	function IsLockNick($nick)
	{
		$db = new Database;
		if($nick != "")
		{
			$result = $db->super_query("SELECT * FROM `blacklist` WHERE `Nick` = '".$nick."'");
			if($result) return true;
			else return false;
		}
		return true;
	}
	function IsLockMail($mail)
	{
		$db = new Database;
		$post = $mail;
		$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );
		$email = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $post ) ) ) ) );
		if($email != "")
		{
			$result = $db->super_query("SELECT * FROM `black_list` WHERE `email` = '$email'");
			if($result != false) return 1;
			else return 0;
		}
	}

	function IsExist($nick)
	{
		$db = new Database;
		$post = $nick;
		$name = $db->safesql( htmlspecialchars( trim( $post ) ) );
		$name = preg_replace('#\s+#i', ' ', $name);
		if($name != "")
		{
			$result = $db->super_query("SELECT * FROM `".TABLE_USERS."` WHERE `".TABLE_USERS_NAME."` = '$name'");
			if($result != false) return 1;
			else return 0;
		}
	}

	function IsExistID($nick)
	{
		$db = new Database;
		if($nick != "")
		{
			$result = $db->super_query("SELECT `pNick` FROM `users` WHERE `pID` = '$nick'");
			if($result != false) return 1;
			else return 0;
		}
		else return 0;
	}

	function lastpassword($email,$login)
	{
		$db = new Database;
		$post = $login;
		$post2 = $email;
		$name = $db->safesql( htmlspecialchars( trim( $post ) ) );
		$name = preg_replace('#\s+#i', ' ', $name);
		$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );
		$mail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $post2 ) ) ) ) );
		$array = $db->super_query("SELECT `".TABLE_USERS_PASSWORD."` FROM `".TABLE_USERS."` WHERE `".TABLE_USERS_NAME."` = '$name' and `pEmail` = '$mail'");
		$pass = $array[TABLE_USERS_PASSWORD];
		$title = 'GameExtream - Поддержка';
		$letter = 	"Здравствуйте, вы запросили напоминание вашего пароля для аккаунта $login,\nВаш текущий пароль: $pass\nАдминистрация GameExtream напоминает: никому не сообщайте ваш пароль на сервере! Мы настоятельно не рекомендуем использовать простые пароли. Чтобы пароль был надежным, он должен содержать цифры, заглавные и малые буквы латинского алфавита.";
		if(mail ($email,$title,$letter,"Content-type:text/plain; charset = UTF-8\r\nFrom:GameExtream-Support") AND $array) return 1;
		else return 0;
	}

	function SetPassword($password)
	{
		if( ($pass_result = $this->check_pass($password)) != 1 )
		return $pass_result;
		$db = new Database;
		$password = $db->safesql(trim($password));
		$session = $db->safesql(trim($_SESSION['name']));
		$one = $db->query("UPDATE `".TABLE_USERS."` SET `".TABLE_USERS_PASSWORD."`='$password' WHERE `".TABLE_USERS_NAME."`='$session'");
		if(!$one) return false;
		else return true;
	}
	function age($age)
	{
		$db = new Database;
		$age = $db->safesql(trim($age));
		if( ($age >= 10 && $age < 20) || substr($age, -1) == 0 || substr($age, -1) >= 5) $return = ' лет';
		else if(substr($age, -1) == 1) $return = ' год';
		else if(substr($age, -1) > 1) $return = ' года';
		return "$age $return";
	}

	function Login($user_nick,$user_pass)
	{
		$db = new Database;
		$user_nick = $db->safesql(trim($user_nick));
		$user_pass = $db->safesql(trim($user_pass));

		$array = $db->super_query("SELECT * FROM `".TABLE_USERS."` WHERE `".TABLE_USERS_NAME."` = '$user_nick' AND `".TABLE_USERS_PASSWORD."` = '$user_pass'");
		if(!$array) return false;
		
        if ( stristr($_SERVER['HTTP_USER_AGENT'], 'Firefox') ) $brauzer = 1;
		elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Chrome') ) $brauzer = 2;
		elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Safari') ) $brauzer = 3;
		elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Opera') ) $brauzer = 4;
		elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') ) $brauzer = 5;
		elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') ) $brauzer = 6;
		elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0') ) $brauzer = 7;

		
		$_SESSION['id'] = $array[TABLE_USERS_ID];
		$_SESSION['name'] = $user_nick;
		$_SESSION['password'] = md5($user_pass);
		$tm = time();
		$db->query("INSERT INTO `enter_log` (pID,IP,unixtime,Brauzer) VALUES ('".$_SESSION['id']."','".$this->getIP()."','".time()."','$brauzer')");
		return true;
	}
	function GetUnixTime($utime)
	{
		$time = date('d.m.Y в H.i.s', $utime);
		return $time;
	}
	function IsLogin()
	{
		$db = new Database;
		if(isset($_SESSION['name'])) return true;
		else return false;
	}
	function IsLoginAdmin()
	{
			if($_SESSION['adminlogged'] == -1) return false;
			else return true;
	}
function LoginAdmin($user_nick,$user_pass)
{
	$db = new Database;
	$user_nick = $db->safesql(trim($user_nick));
	$user_pass = $db->safesql(trim($user_pass));
	$zapros = $db->super_query("SELECT * FROM `admins` WHERE `Nick` = '$user_nick' AND `Pass` = '$user_pass'");
	if(!$zapros)
	{
	   	if($_SESSION['adminwarning'] <= 3)
		{
			if($_SESSION['adminwarning'] <= 0) $_SESSION['adminwarning'] = 1;
			else if($_SESSION['adminwarning'] == 1) $_SESSION['adminwarning'] = 2;
			else $_SESSION['adminwarning'] = 3;
		}
		return false;
	}
	else
	{
		$_SESSION['adminlogged'] = 1;
		return true;
	}
}
	function getIP()
	{
		if(isset($_SERVER['HTTP_X_REAL_IP']))
		return $_SERVER['HTTP_X_REAL_IP'];
		return $_SERVER['REMOTE_ADDR'];
	}


function GetHiddenLeaderList()
	{
		$db = new Database;
			$list_title = "Скрытые лидеры сервера";
			$list_name = "";
			$array_uc = $db->query("SELECT `pNick`,`pMember` FROM `users` WHERE `pLeader` = '3' ORDER BY `pMember`");
			$history = "";
			while($array_uc = $db->get_row())
			{
			$nick = $array_uc['pNick'];
			
			
                $text = "<a href='index.php?act=admin&name=".$array_uc['pNick']."'>".$array_uc['pNick']." ";
				$history .= "
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:30%;'><b><font color='#777777'><center>".$text."</center></font></b></td>
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:50%;'><b><font color='#777777'><center>".$this->GetFractionName($array_uc['pMember'])."</center></font></b></td>
				</tr><tr>
				";
			}
			$counts = $db->super_query("SELECT COUNT(*) as count FROM `users` WHERE `pLeader` = '3'");
			if($counts['count'] > 0)
			{
				if($counts['count'] >= 10) $overflow = "height: 24em; overflow: auto";
				else $overflow = "height: auto;";
				$list_return = '
				<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
				<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
				</div></div>
				<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
				<div style="padding:2px 5px;" />
				<div style="'.$overflow.'">
				<table style="border-collapse:collapse;width:100%;padding:0px;">
				<tbody>
				<tr>
				<td style="padding:0px;">
				<div class="eMessage">
				<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
				<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
				'.$history.'
				</tr></tbody></table>
				</div>
				</div>
				</div>
				</td></tr></tbody></table>
				</div>
				</div>
				</div></div>
				';
			}
			else
			{
				$list_return = '';
			}


			return $list_return;
	}

function GetAdminList()
	{
		$db = new Database;
			$list_title = "Администрация сервера";
			$list_name = "";
			$array_uc = $db->query("SELECT `pNick`,`pAdmin` FROM `users` WHERE `pAdmin` > '0' ORDER BY `pAdmin` DESC");
			$history = "";
			while($array_uc = $db->get_row())
			{
                $text = "<a href='index.php?act=admin&name=".$array_uc['pNick']."'>".$array_uc['pNick']." ";
				$history .= "
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:30%;'><b><font color='#777777'><center>".$text."</center></font></b></td>
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:50%;'><b><font color='#777777'><center>".$array_uc['pAdmin']."</center></font></b></td>
				</tr><tr>
				";
			}
			$counts = $db->super_query("SELECT COUNT(*) as count FROM `users` WHERE `pAdmin` > '0'");
			if($counts['count'] > 0)
			{
				if($counts['count'] >= 10) $overflow = "height: 24em; overflow: auto";
				else $overflow = "height: auto;";
				$list_return = '
				<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
				<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
				</div></div>
				<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
				<div style="padding:2px 5px;" />
				<div style="'.$overflow.'">
				<table style="border-collapse:collapse;width:100%;padding:0px;">
				<tbody>
				<tr>
				<td style="padding:0px;">
				<div class="eMessage">
				<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
				<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
				'.$history.'
				</tr></tbody></table>
				</div>
				</div>
				</div>
				</td></tr></tbody></table>
				</div>
				</div>
				</div></div>
				';
			}
			else
			{
				$list_return = '';
			}


			return $list_return;
	}

function GetHelperList()
	{
		$db = new Database;
			$list_title = "Хелперы сервера";
			$list_name = "";
			$array_uc = $db->query("SELECT `pNick`,`pHelper` FROM `users` WHERE `pHelper` > '0' ORDER BY `pHelper` DESC");
			$history = "";
			while($array_uc = $db->get_row())
			{
                $text = "<a href='index.php?act=admin&name=".$array_uc['pNick']."'>".$array_uc['pNick']." ";
				$history .= "
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:30%;'><b><font color='#777777'><center>".$text."</center></font></b></td>
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:50%;'><b><font color='#777777'><center>".$array_uc['pHelper']."</center></font></b></td>
				</tr><tr>
				";
			}
			$counts = $db->super_query("SELECT COUNT(*) as count FROM `users` WHERE `pHelper` > '0'");
			if($counts['count'] > 0)
			{
				if($counts['count'] >= 10) $overflow = "height: 24em; overflow: auto";
				else $overflow = "height: auto;";
				$list_return = '
				<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
				<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
				</div></div>
				<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
				<div style="padding:2px 5px;" />
				<div style="'.$overflow.'">
				<table style="border-collapse:collapse;width:100%;padding:0px;">
				<tbody>
				<tr>
				<td style="padding:0px;">
				<div class="eMessage">
				<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
				<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
				'.$history.'
				</tr></tbody></table>
				</div>
				</div>
				</div>
				</td></tr></tbody></table>
				</div>
				</div>
				</div></div>
				';
			}
			else
			{
				$list_return = '';
			}


			return $list_return;
	}


function GetPlayersList()
	{
		$db = new Database;
			$list_title = "Игроки онлайн";
			$list_name = "";
			$array_uc = $db->query("SELECT `pNick`,`pLevel`,`pOnline` FROM `users` WHERE `pOnline` > '0' ORDER BY `pLevel` DESC");
			$history = "";
			while($array_uc = $db->get_row())
			{
                $text = "<a href='index.php?act=admin&name=".$array_uc['pNick']."'>".$array_uc['pNick']." ";
				$history .= "
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:30%;'><b><font color='#777777'><center>".$text."</center></font></b></td>
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:50%;'><b><font color='#777777'><center>".$array_uc['pLevel']."</center></font></b></td>
				</tr><tr>
				";
			}
			$counts = $db->super_query("SELECT COUNT(*) as count FROM `users` WHERE `pOnline` > '0'");
			if($counts['count'] > 0)
			{
				if($counts['count'] >= 10) $overflow = "height: 24em; overflow: auto";
				else $overflow = "height: auto;";
				$list_return = '
				<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
				<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
				</div></div>
				<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
				<div style="padding:2px 5px;" />
				<div style="'.$overflow.'">
				<table style="border-collapse:collapse;width:100%;padding:0px;">
				<tbody>
				<tr>
				<td style="padding:0px;">
				<div class="eMessage">
				<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
				<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
				'.$history.'
				</tr></tbody></table>
				</div>
				</div>
				</div>
				</td></tr></tbody></table>
				</div>
				</div>
				</div></div>
				';
			}
			else
			{
				$list_return = '';
			}


			return $list_return;
	}
	


function GetLeaderList($leaderid)
	{
		$db = new Database;
		$info_account = '<img src="'.TEMPLATE_DIR.'/'.TEMPLATE.'/images/info.png"/>';
			$list_title = "Количество игроков, состоящих в организации (не включая лидеров): ";
			$list_name = "";
			$array_uc = $db->query("SELECT `pNick`,`pRank`,`pSex`,`pVigovor` FROM `users` WHERE `pMember` = '".$leaderid."' and `pLeader` = '0' ORDER BY `pRank`");
// 			$array_uc = $db->query("SELECT `pNick`,`pRank` FROM `users` WHERE `pMember` = '".$leaderid."' and `pLeader` != '".$leaderid."' ORDER BY `pRank` DESC");
			$history = "";
			while($array_uc = $db->get_row())
			{
				//if($list_other == "requests") $url = "<td style='padding:5px 30px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;'><b><font color='#777777'><a href='index.php?".PREFIX."=leader&v=".$array_uc[pNick]."'>$info_account</a></font></b></td>";
				//else
				if($leaderid == "1" || $leaderid == "2" || $leaderid == "3" || $leaderid == "13" || $leaderid == "7")
				{
					$url = "<td style='padding:5px 15px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:10%;'><b><font color='#777777'><center><a href='index.php?".PREFIX."=leader&name=".$array_uc[pNick]."'>$info_account</a></center></font></b></td>";
					$history .= "
					<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:30%;'><b><font color='#777777'><center>".$this->GetName($array_uc['pNick'])."</center></font></b></td>
					<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:50%;'><b><font color='#777777'><center>".$this->GetRankName($leaderid,$array_uc['pRank'],$array_uc['pSex'])."</center></font></b></td>
					<td style='padding:5px 15px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:10%;'><b><font color='#777777'><center>".$array_uc['pVigovor']."/3</center></font></b></td>
					$url
					</tr><tr>
					";
				}
				else
				{
					$url = "<td style='padding:5px 30px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:25%;'><b><font color='#777777'><center><a href='index.php?".PREFIX."=leader&name=".$array_uc[pNick]."'>$info_account</a></center></font></b></td>";
					$history .= "
					<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:25%;'><b><font color='#777777'><center>".$this->GetName($array_uc['pNick'])."</center></font></b></td>
					<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:50%;'><b><font color='#777777'><center>".$this->GetRankName($leaderid,$array_uc['pRank'],$array_uc['pSex'])."</center></font></b></td>
					$url
					</tr><tr>
					";
				}
			}
			$counts = $db->super_query("SELECT COUNT(*) as count FROM `users` WHERE `pMember` = '".$leaderid."' and `pLeader` = '0'");
			if($counts['count'] >= 20) $overflow = "height: 44em; overflow: auto";
			else $overflow = "height: auto;";
			if($counts['count'] > 0)
			{
				if($leaderid == "1" || $leaderid == "2" || $leaderid == "3" || $leaderid == "13" || $leaderid == "7")
				{
					$list_return = '
					<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
					<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
					</div></div>
					<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
					<div style="padding:2px 5px;" />
					<div style="'.$overflow.'">
					<table style="border-collapse:collapse;width:100%;padding:0px;">
					<tbody>
					<tr>
					<td style="padding:0px;">
					<div class="eMessage">
					<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
					<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
					<td style="padding:5px 30px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:30%;"><b><font color="#777777"><center>Ник</center></font></b></td>
					<td style="padding:5px 30px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:50%;"><b><font color="#777777"><center>Ранг</center></font></b></td>
					<td style="padding:5px 15px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:10%;"><b><font color="#777777"><center>Выговоры</center></font></b></td>
					<td style="padding:5px 15px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:10%;"><b><font color="#777777"><center>Действия</center></font></b></td>
					</tr><tr>
					'.$history.'
					</tr></tbody></table>
					</div>
					</div>
					</div>
					</td></tr></tbody></table>
					</div>
					</div>
					</div></div>
					';
				}
				else
				{
					$list_return = '
					<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
					<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
					</div></div>
					<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
					<div style="padding:2px 5px;" />
					<div style="'.$overflow.'">
					<table style="border-collapse:collapse;width:100%;padding:0px;">
					<tbody>
					<tr>
					<td style="padding:0px;">
					<div class="eMessage">
					<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
					<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
					<td style="padding:5px 55px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:25%;"><b><font color="#777777"><center>Ник</center></font></b></td>
					<td style="padding:5px 90px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:50%;"><b><font color="#777777"><center>Ранг</center></font></b></td>
					<td style="padding:5px; color:#777777;background:#fff;border-bottom:1px solid #ddd; width:25%;"><b><font color="#777777"><center>Действия</center></font></b></td>
					</tr><tr>
					'.$history.'
					</tr></tbody></table>
					</div>
					</div>
					</div>
					</td></tr></tbody></table>
					</div>
					</div>
					</div></div>
					';
				}
			}
			else $list_return = "<div class='box_top_error'><div class='box_top_text_error'> <div class='eTitle'>В организации никто не состоит!</div></div></div>&nbsp";


			return $list_return;
	}






function GetHelpersList()
	{
		$db = new Database;
		$info_account = '<img src="'.TEMPLATE_DIR.'/'.TEMPLATE.'/images/info.png"/>';
			$list_title = "Количество хелперов 1-6 уровня: ";
			$list_name = "";
			$array_uc = $db->query("SELECT `pNick`,`pHelper`,`pHelperVigovor` FROM `users` WHERE `pHelper` > '0' and `pHelper` <= '6' ORDER BY `pHelper`");
			$history = "";
			while($array_uc = $db->get_row())
			{
					$url = "<td style='padding:5px 30px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:10%;'><b><font color='#777777'><center><a href='index.php?".PREFIX."=helper&name=".$array_uc[pNick]."'>$info_account</a></center></font></b></td>";
					$history .= "
					<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:70%;'><b><font color='#777777'><center>".$this->GetName($array_uc['pNick'])."</center></font></b></td>
					<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:10%;'><b><font color='#777777'><center>".$this->GetHelperName($array_uc['pHelper'])."</center></font></b></td>
					<td style='padding:5px 15px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:10%;'><b><font color='#777777'><center>".$array_uc['pHelperVigovor']."/3</center></font></b></td>
					$url
					</tr><tr>
					";
			}
			$counts = $db->super_query("SELECT COUNT(*) as count FROM `users` WHERE `pHelper` > '0' and `pHelper` <= '6'");
			if($counts['count'] > 0)
			{
					if($counts['count'] >= 20) $overflow = "height: 44em; overflow: auto";
					else $overflow = "height: auto;";
					$list_return = '
					<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
					<div class="eTitle">'.$list_title.' [ '.$counts['count'].' ]</div>
					</div></div>
					<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
					<div style="padding:2px 5px;" />
					<div style="'.$overflow.'">
					<table style="border-collapse:collapse;width:100%;padding:0px;">
					<tbody>
					<tr>
					<td style="padding:0px;">
					<div class="eMessage">
					<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
					<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
					<td style="padding:5px 30px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:70%;"><b><font color="#777777"><center>Ник</center></font></b></td>
					<td style="padding:5px 30px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:10%;"><b><font color="#777777"><center>Уровень</center></font></b></td>
					<td style="padding:5px 15px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:10%;"><b><font color="#777777"><center>Выговоры</center></font></b></td>
					<td style="padding:5px 15px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:10%;"><b><font color="#777777"><center>Действия</center></font></b></td>
					</tr><tr>
					'.$history.'
					</tr></tbody></table>
					</div>
					</div>
					</div>
					</td></tr></tbody></table>
					</div>
					</div>
					</div></div>
					';
			}
			else $list_return = "<div class='box_top_error'><div class='box_top_text_error'> <div class='eTitle'>В составе нет хелперов 1-6 уровня!</div></div></div>&nbsp";
			return $list_return;
	}

	function show_pass_error($num)
	{
		switch($num)
		{
			case 1: $msg = "Вы можете использовать этот пароль"; break;
			case -1: $msg = "Все поля необходимо заполнить!"; break;
			case -2: $msg = "Длина пароля должна быть не меньше 5 и не больше 20 символов!"; break;
			case -3: $msg = "Найдены запрещённые символы!(' / \\ *) и т.п"; break;
		}
	return $msg;
	}
	function check_pass($pass)
	{
		if($pass == "")
		{
			return -1;
		}
		else if(strlen($pass) < 5 || strlen($pass) > 20)
		{
			return -2;
		}
		else
		{
			$forbidden = array("'" => true, '/' => true, '\\' => true, '"' => true, '*' => true, ';' => true, '%' => true);
			$len = strlen($pass);
			for($i=0;$i<$len;$i++)
			{
				if( $forbidden[ $pass[$i] ] == true )
				{
					return -3;
				}
			}
			return 1;
		}
	}
	function GetName($name)
	{
		$name = str_replace("_"," ", $name);
		return $name;
	}
function GetJobName($i)
{
	switch($i)
	{
		case 1:  $job = "Детектив";break;
		case 2:  $job = "Продавец оружия";break;
		case 3:  $job = "Боксёр";break;
		case 4:  $job = "Автоугонщик";break;
		case 5:  $job = "Механик";break;
		case 6:  $job = "Грабитель";break;
		case 7:  $job = "Водитель автобуса";break;
		case 8:  $job = "Рыболов";break;
		case 9:  $job = "Дальнобойщик";break;
		default: $job = "Безработный";break;
	}
	return $job;
}
function GetFractionName($i)
{
	switch($i)
	{
		case 0: $member = "Гражданский";break;
		case 1: $member = "Полиция";break;
		case 2: $member = "ФБР";break;
		case 3: $member = "Армия";break;
		case 4: $member = "МинЗдрав";break;
		case 5: $member = "Yakuza Mafia";break;
		case 6: $member = "El Coronos Gang";break;
		case 7: $member = "Мэрия Штата";break;
		case 8: $member = "Русская Мафия";break;
		case 9: $member = "Автошкола";break;
		case 10: $member = "La Cosa Nostra Mafia";break;
		case 11: $member = "San Fierro Rifa Gang";break;
		case 12: $member = "Агенство Журналистского центра";break;
		case 13: $member = "Bandidos MC";break;
		case 14: $member = "Агенство Киллеров";break;
		case 15: $member = "Grove Street Gang";break;
		case 16: $member = "Los Santos Vagos Gang";break;
		case 17: $member = "Street Racers Club";break;
		case 18: $member = "Ballas Gang";break;
		default: $member = "Гражданский";break;
	}
	return $member;
}
function SetPlayerFractionSkin($member,$rank,$sex)
{
	switch($member)
	{
		case 1:
		{
			switch($rank)
			{
				case 1:
				case 2:  if($sex < 2) $skin = 266; else $skin = 307;break;
				case 3:
				case 4:  if($sex < 2) $skin = 284; else $skin = 307;break;
				case 5:  if($sex < 2) $skin = 265; else $skin = 306;break;
				case 6:  if($sex < 2) $skin = 280; else $skin = 306;break;
				case 7:
				case 8:
				case 9:
				case 10:
				case 11: if($sex < 2) $skin = 285; else $skin = 306;break;
				case 12:
				case 13: if($sex < 2) $skin = 281; else $skin = 93;break;
				case 14:
				case 15:
				case 16:
				case 17: if($sex < 2) $skin = 17;  else $skin = 93;break;
				case 15:
				case 16: if($sex < 2) $skin = 282; else $skin = 93;break;
				case 18: if($sex < 2) $skin = 283; else $skin = 93;break;
				case 19:
				case 20: if($sex < 2) $skin = 288; else $skin = 309;break;
				default: if($sex < 2) $skin = 266; else $skin = 307;break;
			}
			break;
		}
		case 2:
		{
			switch($rank)
			{
				case 1:
				case 2:  if($sex < 2) $skin = 286; else $skin = 76;break;
				case 3:
				case 4:  if($sex < 2) $skin = 163; else $skin = 76;break;
				case 5:
				case 6:  if($sex < 2) $skin = 164; else $skin = 76;break;
				case 7:
				case 8:
				case 9:
				case 10:
				case 11: if($sex < 2) $skin = 165; else $skin = 76;break;
				case 12:
				case 13:
				case 14:
				case 15:
				case 16:
				case 17:
				case 18: if($sex < 2) $skin = 166; else $skin = 76;break;
				case 19:
				case 20:
				case 21: if($sex < 2) $skin = 295; else $skin = 211;break;
				default: if($sex < 2) $skin = 286; else $skin = 76;break;
			}
			break;
		}
		case 3:
		{
            if($sex < 2)
            {
                switch($rank)
				{
					case 1: $skin = 71;break;
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
					case 11:
					case 12:
					case 13:
					case 14:
					case 15: $skin = 287;break;
					case 16:
					case 17:
					case 18: $skin = 179;break;
					case 19:
					case 20:
					case 21:
					case 22: $skin = 147;break;
					default: $skin = 71;break;
				}
            }
            else
            {
                $skin = 191;break;
            }
			break;
		}
		case 4:
		{
			switch($rank)
			{
				case 1:
				case 2:  if($sex < 2) $skin = 274; else $skin = 308;break;
				case 3:
				case 4:  if($sex < 2) $skin = 71; else $skin = 308;break;
				case 5:
				case 6:  if($sex < 2) $skin = 275; else $skin = 308;break;
				case 7:
				case 8:  if($sex < 2) $skin = 276; else $skin = 308;break;
				case 9:
				case 10:
				case 11: if($sex < 2) $skin = 70; else $skin = 219;break;
				default: if($sex < 2) $skin = 274; else $skin = 308;break;
			}
			break;
		}
		case 5:
		{
			switch($rank)
			{
				case 1:
				case 2:  if($sex < 2) $skin = 203; else $skin = 226;break;
				case 3:
				case 4:  if($sex < 2) $skin = 204; else $skin = 226;break;
				case 5:
				case 6:  if($sex < 2) $skin = 49; else $skin = 226;break;
				case 7:  if($sex < 2) $skin = 117; else $skin = 226;break;
				case 8:
				case 9:  if($sex < 2) $skin = 121; else $skin = 56;break;
				case 10:
				case 11: if($sex < 2) $skin = 122; else $skin = 56;break;
				case 12: if($sex < 2) $skin = 123; else $skin = 56;break;
				case 13: if($sex < 2) $skin = 186; else $skin = 55;break;
				case 14:
				case 15: if($sex < 2) $skin = 120; else $skin = 169;break;
				default: if($sex < 2) $skin = 203; else $skin = 226;break;
			}
			break;
		}
		case 6:
		{
			switch($rank)
			{
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:  if($sex < 2) $skin = 114; else $skin = 192;break;
				case 6:
				case 7:
				case 8:
				case 9:
				case 10: if($sex < 2) $skin = 116; else $skin = 192;break;
				case 11:
				case 12:
				case 13:
				case 14:
				case 15:
				case 16: if($sex < 2) $skin = 292; else $skin = 192;break;
				case 17:
				case 18: if($sex < 2) $skin = 115; else $skin = 298;break;
				default: if($sex < 2) $skin = 114; else $skin = 192;break;
			}
			break;
		}
		case 7:
		{
			switch($rank)
			{
				case 1:  if($sex < 2) $skin = 255; else $skin = 141;break;
				case 2:
				case 3:  if($sex < 2) $skin = 185; else $skin = 141;break;
				case 4:
				case 5:
				case 6:
				case 7:  if($sex < 2) $skin = 187; else $skin = 141;break;
				case 8:  if($sex < 2) $skin = 68; else $skin = 141;break;
				case 9:
				case 10:
				case 11: if($sex < 2) $skin = 57; else $skin = 151;break;
				case 12:
				case 13: if($sex < 2) $skin = 294; else $skin = 150;break;
				default: if($sex < 2) $skin = 255; else $skin = 141;break;
			}
			break;
		}
		case 8:
		{
			switch($rank)
			{
				case 1:
				case 2:
				case 3:  if($sex < 2) $skin = 126; else $skin = 214;break;
				case 4:
				case 5:
				case 6:  if($sex < 2) $skin = 112; else $skin = 214;break;
				case 7:
				case 8:
				case 9:  if($sex < 2) $skin = 111; else $skin = 214;break;
				case 10:
				case 11:
				case 12:
				case 13: if($sex < 2) $skin = 125; else $skin = 214;break;
				case 14: if($sex < 2) $skin = 3; else $skin = 216;break;
				case 15: if($sex < 2) $skin = 46; else $skin = 216;break;
				default: if($sex < 2) $skin = 126; else $skin = 214;break;
			}
			break;
		}
		case 9:
		{
			switch($rank)
			{
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:  if($sex < 2) $skin = 240; else $skin = 11;break;
				case 6:
				case 7:
				case 8:
				case 9:  if($sex < 2) $skin = 189; else $skin = 194;break;
				case 10:
				case 11: if($sex < 2) $skin = 171; else $skin = 172;break;
				default: if($sex < 2) $skin = 240; else $skin = 11;break;
			}
			break;
		}
		case 10:
		{
			switch($rank)
			{
				case 1:
				case 2:
				case 3:  if($sex < 2) $skin = 124; else $skin = 12;break;
				case 4:
				case 5:  if($sex < 2) $skin = 98; else $skin = 12;break;
				case 6:
				case 7:  if($sex < 2) $skin = 258; else $skin = 12;break;
				case 8:  if($sex < 2) $skin = 223; else $skin = 40;break;
				case 9:  if($sex < 2) $skin = 113; else $skin = 40;break;
				default: if($sex < 2) $skin = 124; else $skin = 12;break;
			}
			break;
		}
		case 11:
		{
			switch($rank)
			{
				case 1:
				case 2:  if($sex < 2) $skin = 30; else $skin = 41;break;
				case 3:
				case 4:
				case 5:  if($sex < 2) $skin = 47; else $skin = 41;break;
				case 6:
				case 7:
				case 8:
				case 9:  if($sex < 2) $skin = 175; else $skin = 41;break;
				case 10:
				case 11:
				case 12: if($sex < 2) $skin = 174; else $skin = 41;break;
				case 13:
				case 14:
				case 15: if($sex < 2) $skin = 173; else $skin = 201;break;
				default: if($sex < 2) $skin = 30; else $skin = 41;break;
			}
			break;
		}
		case 12:
		{
			switch($rank)
			{
				case 1:
				case 2:  if($sex < 2) $skin = 2; else $skin = 131;break;
				case 3:
				case 4:  if($sex < 2) $skin = 259; else $skin = 131;break;
				case 5:  if($sex < 2) $skin = 35; else $skin = 131;break;
				case 6:  if($sex < 2) $skin = 36; else $skin = 131;break;
				case 7:
				case 8:
				case 9:
				case 10: if($sex < 2) $skin = 296; else $skin = 91;break;
				case 11: if($sex < 2) $skin = 297; else $skin = 64;break;
				case 12: if($sex < 2) $skin = 249; else $skin = 85;break;
				default: if($sex < 2) $skin = 2; else $skin = 131;break;
			}
			break;
		}
		case 13:
		{
		    if($sex < 2)
		    {
				switch($rank)
				{
					case 1:  $skin = 183;break;
					case 2:  $skin = 247;break;
					case 3:  $skin = 299;break;
					case 4:  $skin = 242;break;
					case 5:  $skin = 161;break;
					case 6:  $skin = 34;break;
					case 7:  $skin = 33;break;
					default: $skin = 183;break;
				}
			}
			else
			{
			    $skin = 198;break;
			}
		}
		case 14:
		{
		    if($sex < 2)
		    {
				switch($rank)
				{
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9: $skin = 127;break;
					case 10: $skin = 181;break;
					case 11: $skin = 294;break;
					default: $skin = 127;break;
				}
			}
			else
			{
			    $skin = 190;break;
			}
			break;
		}
		case 15:
		{
		    if($sex < 2)
		    {
				switch($rank)
				{
					case 1:
					case 2:
					case 3:  $skin = 105;break;
					case 4:
					case 5:
					case 6:  $skin = 106;break;
					case 7:
					case 8:
					case 9:  $skin = 107;break;
					case 10: $skin = 269;break;
					case 11: $skin = 271;break;
					case 12: $skin = 270;break;
					default: $skin = 105;break;
				}
			}
			else
			{
			    $skin = 207;break;
			}
			break;
		}
		case 16:
		{
		    if($sex < 2)
		    {
				switch($rank)
				{
					case 1:
					case 2:
					case 3:
					case 4:  $skin = 108;break;
					case 5:
					case 6:
					case 7:
					case 8:  $skin = 109;break;
					case 9:
					case 10:
					case 11: $skin = 110;break;
					default: $skin = 108;break;
				}
			}
			else
			{
			    $skin = 63;break;
			}
			break;
		}
		case 17:
		{
		    if($sex < 2)
		    {
				switch($rank)
				{
					case 1:
					case 2:  $skin = 60;break;
					case 3:
					case 4:
					case 5:  $skin = 170;break;
					case 6:
					case 7:  $skin = 101;break;
					case 8:
					case 9:  $skin = 177;break;
					case 10: $skin = 180;break;
					default: $skin = 60;break;
				}
			}
			else
			{
				$skin = 193;break;
			}
			break;
		}
		case 18:
		{
		    if($sex < 2)
		    {
				switch($rank)
				{
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:  $skin = 103;break;
					case 6:
					case 7:
					case 8:  $skin = 102;break;
					case 9:
					case 10: $skin = 104;break;
					default: $skin = 103;break;
				}
			}
			else
			{
			     $skin = 195;break;
			}
			break;
		}
		default:
		{
			if($sex < 2)
			{
			    $skin = 23;
			}
			else
			{
			    $skin = 65;
			}
		}
	}
	return $skin;
}
function GetFractionsName($i)
{
	switch($i)
	{
	    case 0: $member = "Гражданский";break;
		case 1:  $member = "Полиции";break;
		case 2:  $member = "ФБР";break;
		case 3:  $member = "Армии";break;
		case 4:  $member = "МинЗдрава";break;
		case 5:  $member = "Yakuza Mafia";break;
		case 6:  $member = "El Coronos Gang";break;
		case 7:  $member = "Мэрии Штата";break;
		case 8:  $member = "Русской Мафии";break;
		case 9:  $member = "Автошколы";break;
		case 10: $member = "La Cosa Nostra Mafia";break;
		case 11: $member = "San Fierro Rifa Gang";break;
		case 12: $member = "Агенства Журналистского центра";break;
		case 13: $member = "Bandidos MC";break;
		case 14: $member = "Агенства Киллеров";break;
		case 15: $member = "Grove Street Gang";break;
		case 16: $member = "Los Lantos Vagos Gang";break;
		case 17: $member = "Street Racers Club";break;
		case 18: $member = "Ballas Gang";break;
		default: $member = "None";break;
	}
	return $member;
}
function GetLeaderRank($i)
{
	switch($i)
	{
		case 3: $rank = "(Скрытый Лидер)"; break;
		case 2: $rank = "(Зам. Лидера)"; break;
		case 1: $rank = "(Лидер)"; break;
		default: $rank=""; break;
	}
	return $rank;
}

function GetMaxRank($rank)
{
    switch($rank)
    {
        case 1: return 20;break;
        case 2: return 21;break;
        case 3: return 21;break;
        case 4: return 11;break;
        case 5: return 15;break;
        case 6: return 18;break;
        case 7: return 13;break;
        case 8: return 15;break;
        case 9: return 11;break;
        case 10: return 9;break;
        case 11: return 15;break;
        case 12: return 12;break;
        case 13: return 10;break;
        case 14: return 11;break;
        case 15: return 12;break;
        case 16: return 11;break;
        case 17: return 10;break;
        case 18: return 10;break;
        default: return 0;break;
    }
}


function GetRankName($member,$rank,$pol)
{
	switch($member)
	{
		case 1:
		{
			switch($rank)
			{
				case 1:  $rankname = "Курсант Полиции (1)";break;
				case 2:  $rankname = "Cержант Полиции (2)";break;
				case 3:  $rankname = "Патрульный Полиции (3)";break;
				case 4:  $rankname = "Офицер Полиции (4)";break;
				case 5:  $rankname = "Лейтенант Полиции (5)";break;
				case 6:  $rankname = "Капитан Полиции (6)";break;
				case 7:  $rankname = "Стажёр S.W.A.T. (7)";break;
				case 8:  $rankname = "Младший Лейтенант S.W.A.T. (8)";break;
				case 9:  $rankname = "Лейтенант S.W.A.T. (9)";break;
				case 10: $rankname = "Старший Лейтенант S.W.A.T. (10)";break;
				case 11: $rankname = "Майор S.W.A.T. (11)";break;
				case 12: $rankname = "Зам. Главы Полицейской Академии (12)";break;
				case 13: $rankname = "Зам. Главы Дорожно-Патрульной службы (13)";break;
				case 14: $rankname = "Зам. Командира S.W.A.T. (14)";break;
				case 15: $rankname = "Глава Полицейской Академии (15)";break;
				case 16: $rankname = "Глава Дорожно-Патрульной службы (16)";break;
				case 17: $rankname = "Командир S.W.A.T. (17)";break;
				case 18: $rankname = "Инспектор Полиции (18)";break;
				case 19: $rankname = "Зам. Шерифа Полиции";break;
				case 20: $rankname = "Шериф Полиции";break;
				default: $rankname = "*** Курсант Полиции ***";break;
			}
			break;
		}
		case 2:
		{
			switch($rank)
			{
				case 1: $rankname = "Стажёр ФБР (1)";break;
				case 2: $rankname = "Младший Агент ФБР (2)";break;
				case 3: $rankname = "Старший Агент ФБР (3)";break;
				case 4: $rankname = "Специальный Агент ФБР (4)";break;
				case 5: $rankname = "Федеральный Агент ФБР (5)";break;
				case 6: $rankname = "Двойной Агент ФБР (6)";break;
				case 7: $rankname = "Агент Национальной Безопасности (7)";break;
				case 8: $rankname = "Агент отдела DEA (8)";break;
				case 9: $rankname = "Агент отдела CID (9)";break;
				case 10: $rankname = "Агент отдела DPD (10)";break;
				case 11: $rankname = "Агент отдела Контроля (11)";break;
				case 12: $rankname = "Зам. Главы отдела DEA (12)";break;
				case 13: $rankname = "Зам. Главы отдела CID (13)";break;
				case 14: $rankname = "Зам. Главы отдела DPD (14)";break;
				case 15: $rankname = "Зам. Главы отдела Контроля (15)";break;
				case 16: $rankname = "Глава отдела DEA (16)";break;
				case 17: $rankname = "Глава отдела CID (17)";break;
				case 18: $rankname = "Глава отдела DPD (18)";break;
				case 19: $rankname = "Глава отдела Контроля (19)";break;
				case 20: $rankname = "Зам. директора ФБР";break;
				case 21: $rankname = "Директор ФБР";break;
				default: $rankname = "*** Стажёр ФБР ***";break;
			}
			break;
		}
		case 3:
		{
			switch($rank)
			{
				case 1: if($pol < 2) $rankname = "Рядовой (1)"; else $rankname = "Рядовая (1)";break;
				case 2: $rankname = "Ефрейтор (2)";break;
				case 3: $rankname = "Младший Сержант (3)";break;
				case 4: $rankname = "Сержант (4)";break;
				case 5: $rankname = "Старший Сержант (5)";break;
				case 6: $rankname = "Старшина (6)";break;
				case 7: $rankname = "Прапорщик (7)";break;
				case 8: $rankname = "Старший Прапорщик (8)";break;
				case 9: $rankname = "Младший Лейтенант (9)";break;
				case 10: $rankname = "Лейтенант (10)";break;
				case 11: $rankname = "Старший Лейтенант (11)";break;
				case 12: $rankname = "Капитан (12)";break;
				case 13: $rankname = "Майор (13)";break;
				case 14: $rankname = "Подполковник (14)";break;
				case 15: $rankname = "Полковник (15)";break;
				case 16: $rankname = "Генерал-Майор (16)";break;
				case 17: $rankname = "Генерал-Лейтенант (17)";break;
				case 18: $rankname = "Генерал ВВС (18)";break;
				case 19: $rankname = "Генерал Армии (19)";break;
				case 20: $rankname = "Зам. Министра Обороны";break;
				case 21: $rankname = "Министр Обороны";break;
				default: if($pol < 2) $rankname = "*** Рядовой ***"; else $rankname = "*** Рядовая ***";break;
			}
			break;
		}
		case 4:
		{
			switch($rank)
			{
				case 1: $rankname = "Интерн (1)";break;
				case 2: if($pol < 2) $rankname = "Медбрат (2)"; else $rankname = "Медсестра (2)";break;
				case 3: $rankname = "Фельдшер (3)";break;
				case 4: $rankname = "Психиатр (4)";break;
				case 5: $rankname = "Терапевт (5)";break;
				case 6: $rankname = "Травматолог (6)";break;
				case 7: $rankname = "Хирург (7)";break;
				case 8: $rankname = "Анестезиолог (8)";break;
				case 9: $rankname = "Главврач (9)";break;
				case 10: $rankname = "Зам. Министрa Здравоохранения";break;
				case 11: $rankname = "Министр Здравоохранения";break;
				default: $rankname = "*** Интерн ***";break;
			}
			break;
		}
		case 5:
		{
			switch($rank)
			{
				case 1: $rankname = "Вакасю (1)";break;
				case 2: $rankname = "Сятей (2)";break;
				case 3: $rankname = "Кёдай (3)";break;
				case 4: $rankname = "Сатейгасира-Хоса (4)";break;
				case 5: $rankname = "Сатейгасира (5)";break;
				case 6: $rankname = "Вакагасира-Хоса (6)";break;
				case 7: $rankname = "Вакагасира (7)";break;
				case 8: $rankname = "Фуку-Хумбите (8)";break;
				case 9: $rankname = "Со-Хумбите (9)";break;
				case 10: $rankname = "Кайкей (10)";break;
				case 11: $rankname = "Шингин (11)";break;
				case 12: $rankname = "Комон (12)";break;
				case 13: $rankname = "Сайко-Комон (13)";break;
				case 14: $rankname = "Кумите Хишо";break;
				case 15: $rankname = "Кумите";break;
				default: $rankname = "*** Вакасю ***";break;
			}
			break;
		}
		case 6:
		{
			switch($rank)
			{
				case 1: $rankname = "Forastero (1)";break;
				case 2: $rankname = "Jovenes (2)";break;
				case 3: $rankname = "Novicio (3)";break;
				case 4: $rankname = "Formando (4)";break;
				case 5: $rankname = "Bandido (5)";break;
				case 6: $rankname = "Soldado (6)";break;
				case 7: $rankname = "Tirador (7)";break;
				case 8: $rankname = "Calavera (8)";break;
				case 9: $rankname = "Verificado (9)";break;
				case 10: $rankname = "Cezados (10)";break;
				case 11: $rankname = "Matador (11)";break;
				case 12: $rankname = "Assesino (12)";break;
				case 13: $rankname = "Autoridad (13)";break;
				case 14: $rankname = "Elegante (14)";break;
				case 15: $rankname = "Capitulo (15)";break;
				case 16: $rankname = "Cabeza (16)";break;
				case 17: $rankname = "El Padre";break;
				case 18: $rankname = "El Padre Supremo";break;
				default: $rankname = "*** Forastero ***";break;
			}
			break;
		}
		case 7:
		{
			switch($rank)
			{
				case 1: $rankname = "Водитель (1)";break;
				case 2: $rankname = "Телохранитель (2)";break;
				case 3: $rankname = "Адвокат (3)";break;
				case 4: $rankname = "Паспортист (4)";break;
				case 5: $rankname = "Министр Финансов (5)";break;
				case 6: $rankname = "Начальник Охраны (6)";break;
				case 7: $rankname = "Начальник паспортного стола (7)";break;
				case 8: $rankname = "Начальник Надзора (8)";break;
				case 9: $rankname = "Мэр города Los Santos (9)";break;
				case 10: $rankname = "Мэр города San Fierro (10)";break;
				case 11: $rankname = "Мэр города Las Venturas (11)";break;
				case 12: $rankname = "Премьер-Министр";break;
				case 13: $rankname = "Президент Штата";break;
				default: $rankname = "*** Водитель ***";break;
			}
			break;
		}
		case 8:
		{
			switch($rank)
			{
				case 1: $rankname = "Шнырь (1)";break;
				case 2: $rankname = "Шестёрка (2)";break;
				case 3: if($pol < 2) $rankname = "Фраер (3)"; else $rankname = "Фифа (3)";break;
				case 4: if($pol < 2) $rankname = "Бык (4)"; else $rankname = "Корова (4)";break;
				case 5: $rankname = "Барыга (5)";
				case 6: if($pol < 2) $rankname = "Рэкетир (6)"; else $rankname = "Рэкетирша (6)";break;
				case 7: if($pol < 2) $rankname = "Блатной (7)"; else $rankname = "Блатная (7)";break;
				case 8: if($pol < 2) $rankname = "Смотрящий (8)"; else $rankname = "Смотрящая (8)";break;
				case 9: if($pol < 2) $rankname = "Свояк (9)"; else $rankname = "Своячка (9)";break;
				case 10: if($pol < 2) $rankname = "Браток (10)"; else $rankname = "Сестричка (10)";break;
				case 11: if($pol < 2) $rankname = "Козырный Фраер (11)"; else $rankname = "Козырныая Фифа (11)";break;
				case 12: if($pol < 2) $rankname = "Жиган (12)"; else $rankname = "Жиганка (12)";break;
				case 13: if($pol < 2) $rankname = "Вор (13)"; else $rankname = "Воровка (13)";break;
				case 14: $rankname = "Авторитет";break;
				case 15: if($pol < 2) $rankname = "Вор в Законе"; else $rankname = "Воровка в Законе";break;
				default: $rankname = "*** Шнырь ***";break;
			}
			break;
		}
		case 9:
		{
			switch($rank)
			{
				case 1: $rankname = "Стажер Инструктора (1)";break;
				case 2: $rankname = "Ассистент Инструктора (2)";break;
				case 3: $rankname = "Инструктор 3-го класса (3)";break;
				case 4: $rankname = "Инструктор 2-го класса (4)";break;
				case 5: $rankname = "Инструктор 1-го класса (5)";break;
				case 6: $rankname = "Cтарший Инструктор (6)";break;
				case 7: $rankname = "Менеджер Автошколы (7)";break;
				case 8: $rankname = "Cтарший Менеджер Автошколы (8)";break;
				case 9: $rankname = "Секретарь Автошколы (9)";break;
				case 10: $rankname = "Зам. Директора Автошколы";break;
				case 11: $rankname = "Директор Автошколы";break;
				default: $rankname = "*** Стажер Инструктора ***";break;
			}
			break;
		}
		case 10:
		{
			switch($rank)
			{
				case 1: if($pol < 2) $rankname = "Новобранец"; else $rankname = "Новобранка";break;
				case 2: if($pol < 2) $rankname = "Проверенный"; else $rankname = "Проверенная";break;
				case 3: $rankname = "Партнёр (3)";break;
				case 4: $rankname = "Боец (4)";break;
				case 5: $rankname = "Солдат (5)";break;
				case 6: $rankname = "Капо (6)";break;
				case 7: $rankname = "Консильери (7)";break;
				case 8: if($pol < 2) $rankname = "Сын Крёстного отца"; else $rankname = "Дочь Крёстного отца";break;
				case 9: if($pol < 2) $rankname = "Крёстный Отец"; else $rankname = "Крёстная Мать";break;
				default: if($pol < 2) $rankname = "*** Новобранец ***"; else $rankname = "*** Новобранка ***";break;
			}
			break;
		}
		case 11:
		{
			switch($rank)
			{
				case 1: $rankname = "Раро (1)";break;
				case 2: $rankname = "Estranio (2)";break;
				case 3: $rankname = "Novato (3)";break;
				case 4: $rankname = "Ordinario (4)";break;
				case 5: $rankname = "Estrimado (5)";break;
				case 6: $rankname = "Latino (6)";break;
				case 7: $rankname = "Amigo (7)";break;
				case 8: $rankname = "Ermano (8)";break;
				case 9: $rankname = "Soldado (9)";break;
				case 10: $rankname = "Probador (10)";break;
				case 11: $rankname = "Entrenador (11)";break;
				case 12: $rankname = "Fuerte (12)";break;
				case 13: $rankname = "Criminal (13)";break;
				case 14: $rankname = "Proximo";break;
				case 15: $rankname = "Padre";break;
				default: $rankname = "*** Раро ***";break;
			}
			break;
		}
		case 12:
		{
			switch($rank)
			{
				case 1: $rankname = "Стажёр журналиста (1)";break;
				case 2: $rankname = "Фотограф (2)";break;
				case 3: $rankname = "Папарацци (3)";break;
				case 4: $rankname = "Корреспондент (4)";break;
				case 5: $rankname = "Репортёр (5)";break;
				case 6: $rankname = "Журналист (6)";break;
				case 7: $rankname = "Радио - DJ (7)";break;
				case 8: $rankname = "Новостной редактор (8)";break;
				case 9: $rankname = "Шеф - редактор (9)";break;
				case 10: $rankname = "Менеджер (10)";break;
				case 11: $rankname = "Зам. Директора журналистского центра";break;
				case 12: $rankname = "Директор журналистского центра";break;
				default: $rankname = "*** Стажёр журналиста ****";break;
			}
			break;
		}
		case 13:
		{
			switch($rank)
			{
				case 1: if($pol < 2) $rankname = "Помощник (1)"; else $rankname = "Помощница (1)";break;
				case 2: if($pol < 2) $rankname = "Бездельник (2)"; else $rankname = "Бездельница (2)";break;
				case 3: if($pol < 2) $rankname = "Молодой волк (3)"; else $rankname = "Молодой волчица (3)";break;
				case 4: if($pol < 2) $rankname = "Гонимый ветром (4)"; else $rankname = "Гонимая ветром (4)";break;
				case 5: $rankname = "Дорожный капитан (5)";break;
				case 6: if($pol < 2) $rankname = "Покорённый ветром (6)"; else $rankname = "Покорённая ветром (6)";break;
				case 7: if($pol < 2) $rankname = "Старый волк (7)"; else $rankname = "Старая волчица (7)";break;				
				case 8: $rankname = "Легенда (8)";break;				
				case 9: $rankname = "Вице Президент";break;
				case 10: $rankname = "Президент";break;
				default: if($pol < 2) $rankname = "*** Сомнительный ***"; else $rankname = "*** Сомнительная ***";break;
			}
			break;
		}
		case 14:
		{
			switch($rank)
			{
				case 1: $rankname = "Начинающий киллер (1)";break;
				case 2: $rankname = "Киллер (2)";break;
				case 3: $rankname = "Профессиональный киллер (3)";break;
				case 4: $rankname = "Убийца (4)";break;
				case 5: if($pol < 2) $rankname = "Тихий убийца (5)"; else $rankname = "Тихая убийца (5)";break;
				case 6: if($pol < 2) $rankname = "Профессиональный убийца (6)"; else $rankname = "Профессиональная убийца (5)";break;
				case 7: $rankname = "Снайпер (7)";break;
				case 8: $rankname = "Агент (8)";break;
				case 9: if($pol < 2) $rankname = "Элитный убийца (9)"; else $rankname = "Элитная убийца (9)";break;
				case 10: $rankname = "Зам. Директора Агентства Киллеров";break;
				case 11: $rankname = "Директор Агентства Киллеров";break;
				default: $rankname = "*** Начинающий киллер ***";break;
			}
			break;
		}
		case 15:
		{
			switch($rank)
			{
				case 1: $rankname = "Новичок (1)";break;
				case 2: if($pol < 2) $rankname = "Проверенный (2)"; else $rankname = "Проверенная (2)";break;
				case 3: $rankname = "Боец (3)";
				case 4: $rankname = "Рэкетир (4)";
				case 5: if($pol < 2) $rankname = "Хулиган (5)"; else $rankname = "Хулиганка (5)";break;
				case 6: $rankname = "Авторитет (6)";
				case 7: $rankname = "Гангстер (7)";
				case 8: $rankname = "Головорез (8)";
				case 9: $rankname = "Реальный гангстер (9)";
				case 10: if($pol < 2) $rankname = "Смотрящий Группировки (10)"; else $rankname = "Смотрящая Группировки (10)";break;
				case 11: $rankname = "Зам. Босса";break;
				case 12: $rankname = "Босс";break;
				default: $rankname = "*** Новичок ***";break;
			}
			break;
		}
		case 16:
		{
			switch($rank)
			{
				case 1: $rankname = "Novato (1)";break;
				case 2: $rankname = "Verificado (2)";break;
				case 3: $rankname = "Amigo (3)";break;
				case 4: $rankname = "Hermano (4)";break;
				case 5: $rankname = "Cutthroat (5)";break;
				case 6: $rankname = "Soldado (6)";break;
				case 7: $rankname = "Veterano (7)";break;
				case 8: $rankname = "Guardian (8)";break;
				case 9: $rankname = "Adjunto (9)";break;
				case 10: $rankname = "El Padre";break;
				case 11: $rankname = "El Padre Supremo";break;
				default: $rankname = "*** Novato ***";break;
			}
			break;
		}
		case 17:
		{
			switch($rank)
			{
				case 1: if($pol < 2) $rankname = "Автолюбитель (1)"; else $rankname = "Автолюбительница (1)";break;
				case 2: if($pol < 2) $rankname = "Начинающий гонщик (2)"; else $rankname = "Начинающая гонщица (2)";break;
				case 3: if($pol < 2) $rankname = "Уличный гонщик (3)"; else $rankname = "Уличная гонщица (3)";break;
				case 4: if($pol < 2) $rankname = "Профессиональный гонщик (4)"; else $rankname = "Профессиональная гонщица (4)";break;
				case 5: if($pol < 2) $rankname = "Начинающий уличный гонщик (5)"; else $rankname = "Начинающая уличная гонщица (5)";break;
				case 6: if($pol < 2) $rankname = "Уличный гонщик (6)"; else $rankname = "Начинающая уличная гонщица (6)";break;
				case 7: if($pol < 2) $rankname = "Опытный уличный гонщик (7)"; else $rankname = "Опытная уличная гонщица (7)";break;
				case 8: if($pol < 2) $rankname = "Профессиональный уличный гонщик (8)"; else $rankname = "Профессиональная уличная гонщица (8)";break;
				case 9: if($pol < 2) $rankname = "Призрачный уличный гонщик"; else $rankname = "Призрачная уличная гонщица";break;
				case 10: if($pol < 2) $rankname = "Король гонщиков"; else $rankname = "Королева гонщиков";break;
				default: if($pol < 2) $rankname = "*** Автолюбитель ***"; else $rankname = "*** Автолюбительница ***";break;
			}
			break;
		}
		case 18:
		{
			switch($rank)
			{
				case 1: if($pol < 2) $rankname = "Новобранец"; else $rankname = "Новобранка";break;
				case 2: if($pol < 2) $rankname = "Куривший"; else $rankname = "Курившая";break;
				case 3: if($pol < 2) $rankname = "Млатший брат"; else $rankname = "Младшая сестра";break;
				case 4: if($pol < 2) $rankname = "Брат"; else $rankname = "Сестра";break;
				case 5: if($pol < 2) $rankname = "Старший брат"; else $rankname = "Старшая сестра";break;
				case 6: if($pol < 2) $rankname = "Наркоман"; else $rankname = "Наркоманка";break;
				case 7: $rankname = "Барыга";break;
				case 8: if($pol < 2) $rankname = "Смотрящий"; else $rankname = "Смотрящая";break;
				case 9: $rankname = "Зам. Биг Вилли";break;
				case 10: $rankname = "Биг Вилли";break;
				default: if($pol < 2) $rankname = "*** Новобранец ***"; else $rankname = "*** Новобранка ***";break;
			}
			break;
		}
		default: $rankname = "Нет";break;
	}
	return $rankname;
}



	function GetVipName($i,$ii)
	{
		switch($i)
		{
			case 0: $member = "Нет"; break;
			case 1: $member = "<img src = '/ucp/templates/vip_light.jpg'/> (навсегда)"; break;
			case 2: $member = "<img src = '/ucp/templates/vip_hard.jpg'/> (навсегда)"; break;
			case 10:
			{
				$member = "Vip Light (временный)<br>";
				$member .= date('Дата окончания: d.m.Y <b>в:</b> H.i.s', $ii);
				break;
			}
			case 20:
			{
				$member = "Vip Hard (временный)<br>";
				$member .= date('Дата окончания: d.m.Y <b>в:</b> H.i.s', $ii);
				break;
			}
			default: $member = "Нет"; break;
		}
		return $member;
	}
	function GetHouseName($i,$s)
	{
		if($i != 0)
		{
			$db = new Database;
			if($i > 24)
			{
				$arrh = $db->query("SELECT `hOwner` FROM `houses` WHERE `hID` = '$i'");
				if($arrh['hOwner'] == $s) $ahm = "Номер $i (владелец)";
				else $ahm= "Номер $i (прописан)";
			}
			else $ahm = "Гостиница 'Пирс Los Santos' (комната $i)";
		}
		else $ahm = "Отсутствует";
		return $ahm;
	}
	function GetBizName($i)
	{
		if($i != 0)
		{
		$db = new Database;
		$arrb = $db->super_query("SELECT `bMessage` FROM `business` WHERE `bID` = '$i'");
		$mess = $arrb['bMessage'];
		$newmess = " (номер $i)";
		return $mess.=$newmess;
		}
		else return  $arrb = "Отсутствует";
	}
	function GetCarName($i)
	{
		$db = new Database;
		$arr = $db->query("SELECT `tModel` FROM `cars` WHERE `tID` = '$i'");
		$model = $arr['tModel'];
		$vehname = array(400 => 'Landstalker', 'Bravura', 'Buffalo', 'Linerunner', 'Pereniel', 'Sentinel', 'Dumper', 'Firetruck', 'Trashmaster', 'Stretch', 'Manana', 'Infernus', 'Voodoo', 'Pony', 'Mule', 'Cheetah', 'Ambulance', 'Leviathan', 'Moonbeam', 'Esperanto', 'Taxi', 'Washington', 'Bobcat', 'Mr Whoopee', 'BF Injection', 'Hunter', 'Premier', 'Enforcer', 'Securicar', 'Banshee', 'Predator', 'Bus',
			'Rhino', 'Barracks', 'Hotknife', 'Trailer', 'Previon', 'Coach', 'Cabbie', 'Stallion', 'Rumpo', 'RC Bandit', 'Romero', 'Packer', 'Monster', 'Admiral', 'Squalo', 'Seasparrow', 'Pizzaboy', 'Tram', 'Trailer', 'Turismo', 'Speeder', 'Reefer', 'Tropic', 'Flatbed', 'Yankee', 'Caddy', 'Solair', 'Berkleys RC Van', 'Skimmer', 'PCJ-600', 'Faggio', 'Freeway',
			'RC Baron', 'RC Raider', 'Glendale', 'Oceanic', 'Sanchez', 'Sparrow', 'Patriot', 'Quad', 'Coastguard', 'Dinghy', 'Hermes', 'Sabre', 'Rustler', 'ZR 350', 'Walton', 'Regina', 'Comet', 'BMX', 'Burrito', 'Camper', 'Marquis', 'Baggage', 'Dozer', 'Maverick', 'News Chopper', 'Rancher', 'FBI Rancher', 'Virgo', 'Greenwood', 'Jetmax', 'Hotring', 'Sandking',
			'Blista Compact', 'Police Maverick', 'Boxville', 'Benson', 'Mesa', 'RC Goblin', 'Hotring A', 'Hotring B', 'Bloodring Banger', 'Rancher', 'Super GT', 'Elegant', 'Journey', 'Bike', 'Mountain Bike', 'Beagle', 'Cropdust', 'Stunt', 'Tanker', 'RoadTrain', 'Nebula', 'Majestic', 'Buccaneer', 'Shamal', 'Hydra', 'FCR-900', 'NRG-500', 'HPV1000', 'Cement Truck', 'Tow Truck', 'Fortune',
			'Cadrona', 'FBI Truck', 'Willard', 'Forklift', 'Tractor', 'Combine', 'Feltzer', 'Remington', 'Slamvan', 'Blade', 'Freight', 'Streak', 'Vortex', 'Vincent', 'Bullet', 'Clover', 'Sadler', 'Firetruck', 'Hustler', 'Intruder', 'Primo', 'Cargobob', 'Tampa', 'Sunrise', 'Merit', 'Utility', 'Nevada', 'Yosemite', 'Windsor', 'Monster A', 'Monster B', 'Uranus',
			'Jester', 'Sultan', 'Stratum', 'Elegy', 'Raindance', 'RC Tiger', 'Flash', 'Tahoma', 'Savanna', 'Bandito', 'Freight', 'Trailer', 'Kart', 'Mower', 'Duneride', 'Sweeper', 'Broadway', 'Tornado', 'AT-400', 'DFT-30', 'Huntley', 'Stafford', 'BF-400', 'Newsvan', 'Tug', 'Trailer A', 'Emperor', 'Wayfarer', 'Euros', 'Hotdog', 'Club', 'Trailer B',
			'Trailer C', 'Andromada', 'Dodo', 'RC Cam', 'Launch', 'Police Car', 'Police Car', 'Police Car', 'Police Ranger', 'Picador','S.W.A.T.', 'Alpha', 'Phoenix', 'Glendale', 'Sadler', 'L Trailer A', 'L Trailer B',
			'Stair Trailer', 'Boxville', 'Farm Plow', 'U Trailer');
		$carname = $vehname[$model];
		$mes = " [id:$i]";
		$carname.=$mes;
		return $carname;
	}
	function getcountry($ip)
	{
		$SxGeo = new SxGeo('SxGeoCity.dat');
		$city = $SxGeo->getcityfull($ip);
		$goroc = $city['country'];
		$gorod = $goroz['name_ru'];
		return $gorod;
	}
	function GetCityInfo($ip)
	{
		$SxGeo = new SxGeo('SxGeoCity.dat');
		$city = $SxGeo->getcityfull($ip);
		$goroz = $city['city'];
		$goroc = $city['country'];
		$aza = $goroz['name_ru'];
		$aaz = $goroc['name_ru'];
		$carname = $vehname[$model];
		$mes = "($aza, $aaz)";
		return $mes;
	}
	function GetCarModel($i)
	{
		$db = new Database;
		$arr = $db->super_query("SELECT `tModel` FROM `cars` WHERE `tID` = '$i'");
		$model = $arr['tModel'];
		return $model;
	}
	function GetSkillName($i,$ii)
	{
		switch($i)
		{
			case 1:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 100) $member = "2";
				else if($ii >= 101 && $ii <= 200) $member = "3";
				else if($ii >= 201 && $ii <= 400) $member = "4";
				else $member = "5";
				break;
			}
			case 2:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 250) $member = "2";
				else if($ii >= 251 && $ii <= 500) $member = "3";
				else if($ii >= 501 && $ii <= 1000) $member = "4";
				else $member = "5";
				break;
			}
			case 3:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 250) $member = "2";
				else if($ii >= 251 && $ii <= 500) $member = "3";
				else if($ii >= 501 && $ii <= 1000) $member = "4";
				else $member = "5";
				break;
			}
			case 4:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 100) $member = "2";
				else if($ii >= 101 && $ii <= 200) $member = "3";
				else if($ii >= 201 && $ii <= 400) $member = "4";
				else $member = "5";
				break;
			}
			case 5:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 100) $member = "2";
				else if($ii >= 101 && $ii <= 200) $member = "3";
				else if($ii >= 201 && $ii <= 400) $member = "4";
				else $member = "5";
				break;
			}
			case 6:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 100) $member = "2";
				else if($ii >= 101 && $ii <= 200) $member = "3";
				else if($ii >= 201 && $ii <= 400) $member = "4";
				else $member = "5";
				break;
			}
			case 7:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 100) $member = "2";
				else if($ii >= 101 && $ii <= 200) $member = "3";
				else if($ii >= 201 && $ii <= 400) $member = "4";
				else $member = "5";
				break;
			}
			case 8:
			{
				if($ii <= 50) $member = "1";
				else if($ii >= 51 && $ii <= 100) $member = "2";
				else if($ii >= 101 && $ii <= 200) $member = "3";
				else if($ii >= 201 && $ii <= 400) $member = "4";
				else $member = "5";
				break;
			}
			default: $member = "1"; break;
		}
		return $member;
	}


	function GetHelperName($i)
	{
		switch($i)
		{
			case 0: $member = "Нет"; break;
			case 1: $member = "1"; break;
			case 2: $member = "2"; break;
			case 3: $member = "3"; break;
			case 4: $member = "4"; break;
			case 5: $member = "5"; break;
			case 6: $member = "6"; break;
			case 7: $member = "7 (Скрытый)"; break;
			case 8: $member = "8 (Глава)"; break;
			default: $member = "Нет"; break;
		}
		return $member;
	}
	function GetRaceName($i)
	{
		switch($i)
		{
			case 0: $Rasa = "Европеец"; break;
			case 1: $Rasa = "Афро-американец"; break;
			case 2: $Rasa = "Азиат"; break;
			case 3: $Rasa = "Латино-американец"; break;
			default: $Rasa = "Error"; break;
		}
		return $Rasa;
	}
	function GetBrauzerName($i)
	{
		switch($i)
		{
			case 1: $brauzer = "браузер Firefox"; break;
			case 2: $brauzer = "браузер Chrome"; break;
			case 3: $brauzer = "браузер Safari"; break;
			case 4: $brauzer = "браузер Opera"; break;
			case 5: $brauzer = "браузер IE 6"; break;
			case 6: $brauzer = "браузер IE 7"; break;
			case 7: $brauzer = "браузер IE 8"; break;
			case -1: $brauzer = "Клиент SA:MP"; break;
			default: $brauzer = "Не определено";
		}
		return $brauzer;
	}
	
	function AddForumPlayers($i,$name)//номер организации лидера/ник
	{
		switch($i)
		{
		    case 2: $groupid = ",38,";$groupid2 = "38,"; $groupidleader = ",37,"; $groupidleader2 = "37,"; $prefix = "leader"; break;//FBI /leader 37
			case 13: $groupid = ",32,"; $groupid2 = "32,"; $groupidleader = ",49,"; $groupidleader2 = "49,"; $prefix = "leader"; break;//destroyers / leader 49
			case 14: $groupid = ",31,"; $groupid2 = "31,"; $groupidleader = ",50,"; $groupidleader2 = "50,"; $prefix = "leader"; break;//killers / leader 50
            case -1: $groupid = ",7,"; $groupid2 = "7,";$groupid3 = "7"; $groupidleader = ",34,"; $groupidleader2 = "34,"; $prefix = "helper"; break;//helpers / leader 34			
            case -2: $groupid = ",30,"; $groupid2 = "30,";$groupid3 = "30"; $groupidleader = ",35,"; $groupidleader2 = "35,"; $prefix = "piar"; break;//piar / leader 34			            
            case -3: $groupid = ",28,"; $groupid2 = "28,";$groupid3 = "28"; $groupidleader = ",4,"; $groupidleader2 = "4,"; $prefix = "admin"; break;//admins / leader 4
			default: return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Нет такой страницы!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php">'; break;//
		}
		if($i != "-3") 
		{
		    $prefixx = "addforum"; 
		    $prefixxx = "forumplayers";
		}
		else 
		{
		    $prefixx = "addforumm"; 
		    $prefixxx = "forumplayerss";
		}    
		if($name == "")
		{
			return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы ничего не ввели!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixx.'=1">';
		}
		if(strlen($name) < 3 or strlen($name) > 26)
		{
			return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Имя пользователя не может быть меньше 3 и больше 26 символов!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixx.'=1">';
		}
        $db = mysql_connect(F_MySQL_HOSTNAME, F_MySQL_USER, F_MySQL_PASSWORD);
		if(!$db)
		{
			echo 'Нет подключения к базе данных, обратитесь к Администрации!';
			exit();
		}
		mysql_select_db(F_MySQL_DB, $db);
		mysql_set_charset('utf8');
		date_default_timezone_set('Europe/Minsk');
		$array_uc = mysql_query("SELECT `mgroup_others`,`member_group_id` FROM `ipb_members` WHERE `name` = '$name'");

		$number = mysql_num_rows($array_uc);
		if($number == 0)
		{
		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователь не найден!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
		}//Игрок не найден
		$r = mysql_fetch_array($array_uc);
		if($r['mgroup_others'] == $groupidleader or (stristr($r['mgroup_others'], $groupidleader) == true))
		{
		   return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователь является модератором подфорума!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
		}//Игрок модератор
		if($r['mgroup_others'] == $groupid or (stristr($r['mgroup_others'], $groupid) == true))
		{
		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователь уже имеет доступ к подфоруму!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
		}//У игрока уже есть доступ
		///
		//Проверка на осн группу (если хелпер)
		if($prefix == "helper")
		{
    		switch($r['member_group_id'])
    		{
    		    case 4: $smena = 0; break; //гл администратор
    		    case 28: $smena = 0; break;//администратор
    		    case 30: $smena = 0; break;//пиар
    		    case 3:  $smena = 1; break;//пользователь
    		    case 5:  $smena = -1; break;//забанен
    		    case 29: $smena = 1; break;//модератор
    		    default: $smena = 0; break;
    		}
    		if($smena == "-1")
    		{
       	        return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Ошибка: форумный аккаунт игрока заблокирован!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		mysql_close();    		   
    		}
    		if($smena == "1" and $r['member_group_id'] == "29")
    		{
        		if($r['mgroup_others'] == "") $groups = ",29,";//29- moder
        		else $groups = $r['mgroup_others'].="29,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();    		    
    		}
    		else if($smena == "1" and $r['member_group_id'] == "3") // 3 - пользователь
    		{
        		if($r['mgroup_others'] == "") $groups = ",3,";//3- user
        		else $groups = $r['mgroup_others'].="3,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();    		    
    		}    		
		}
		//
		if($prefix == "piar")
		{
    		switch($r['member_group_id'])
    		{
    		    case 4: $smena = 0; break; //гл администратор
    		    case 28: $smena = 0; break;//администратор
    		    case 7: $smena = 1; break;//хелпер
    		    case 3:  $smena = 1; break;//пользователь
    		    case 5:  $smena = -1; break;//забанен
    		    case 29: $smena = 1; break;//модератор
    		    default: $smena = 0; break;
    		}
    		if($smena == "-1")
    		{
       	        return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Ошибка: форумный аккаунт игрока заблокирован!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		mysql_close();    		   
    		}
    		if($smena == "1" and ($r['member_group_id'] == "29"))
    		{
        		if($r['mgroup_others'] == "") $groups = ",29,";//29- moder
        		else $groups = $r['mgroup_others'].="29,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();    		    
    		}
    		if($smena == "1" and ($r['member_group_id'] == "7"))
    		{
        		if($r['mgroup_others'] == "") $groups = ",7,";//7- helper
        		else $groups = $r['mgroup_others'].="7,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();    		    
    		}    		
    		else if($smena == "1" and $r['member_group_id'] == "3") // 3 - пользователь
    		{
        		if($r['mgroup_others'] == "") $groups = ",3,";//3- user
        		else $groups = $r['mgroup_others'].="3,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();    		    
    		}    		
		}
//
		if($prefix == "admin")
		{
    		switch($r['member_group_id'])
    		{
    		    case 4: $smena = 0; break; //гл администратор
    		    case 28: $smena = 0; break;//администратор
                case 30:  $smena = 1; break;//piar
    		    case 7: $smena = 1; break;//хелпер
    		    case 3:  $smena = 1; break;//пользователь
    		    case 5:  $smena = -1; break;//забанен
    		    case 29: $smena = 1; break;//модератор
    		    default: $smena = 0; break;
    		}
    		if($smena == "-1")
    		{
       	        return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Ошибка: форумный аккаунт игрока заблокирован!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		mysql_close();    		   
    		}
            if($smena == "1" and ($r['member_group_id'] == "30"))
    		{
        		if($r['mgroup_others'] == "") $groups = ",30,";//30- piar
        		else $groups = $r['mgroup_others'].="30,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		mysql_close();    		    
    		}    		
    		if($smena == "1" and ($r['member_group_id'] == "29"))
    		{
        		if($r['mgroup_others'] == "") $groups = ",29,";//29- moder
        		else $groups = $r['mgroup_others'].="29,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		mysql_close();    		    
    		}
    		if($smena == "1" and ($r['member_group_id'] == "7"))
    		{
        		if($r['mgroup_others'] == "") $groups = ",7,";//7- helper
        		else $groups = $r['mgroup_others'].="7,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		mysql_close();    		    
    		}    		
    		else if($smena == "1" and $r['member_group_id'] == "3") // 3 - пользователь
    		{
        		if($r['mgroup_others'] == "") $groups = ",3,";//3- user
        		else $groups = $r['mgroup_others'].="3,";
        		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '".$groupid3."' WHERE `name` = '$name'");
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&'.$prefixxx.'=1">';
        		}
        		mysql_close();    		    
    		}    		
		}	
//
		///
		if( ($prefix != "helper" and $prefix != "piar" and $prefix != "admin") or $smena == "0")
		{
    		if($r['mgroup_others'] == "") $groups = $groupid;
    		else $groups = $r['mgroup_others'].=$groupid2;
    		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."' WHERE `name` = '$name'");
    		if($zapros)
    		{
    		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$name.' успешно открыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
    		}
    		else
    		{
    		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
    		}
    		mysql_close();
		}
        return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка! Обратитесь к администрации</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&addforum=1">';				    	
	}

	function DelForumPlayers($i,$name)//номер организации лидера/ник
	{
		switch($i)
		{
		    case 2: $groupid = ",38,"; $groupidleader = ",37,"; $prefix = "leader"; break;//FBI
			case 13: $groupid = ",32,"; $groupidleader = ",49,"; $prefix = "leader"; break;//destroyers
			case 14: $groupid = ",31,"; $groupidleader = ",50,"; $prefix = "leader"; break;//killers
            case -1: $groupid = ",7,"; $groupid2 = "7"; $groupidleader = ",34,"; $prefix = "helper"; break;//helpers			
            case -2: $groupid = ",30,"; $groupid2 = "30"; $groupidleader = ",35,"; $prefix = "piar"; break;//piar			            
            case -3: $groupid = ",28,"; $groupid2 = "28"; $groupidleader = ",4,"; $prefix = "admin"; break;//admin			                        
			default: return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Нет такой страницы!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">'; break;//
		}
        $db = mysql_connect(F_MySQL_HOSTNAME, F_MySQL_USER, F_MySQL_PASSWORD);
		if(!$db)
		{
			echo 'Нет подключения к базе данных, обратитесь к Администрации!';
			exit();
		}
		mysql_select_db(F_MySQL_DB, $db);
		mysql_set_charset('utf8');
		date_default_timezone_set('Europe/Minsk');
		$array_uc = mysql_query("SELECT `name`,`mgroup_others`,`member_group_id` FROM `ipb_members` WHERE `member_id` = '$name'");
		$number = mysql_num_rows($array_uc);
		if($number == 0)
		{
            return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователь не найден!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
		}//Игрок не найден
		$r = mysql_fetch_array($array_uc);
		if($r['mgroup_others'] == $groupidleader or (stristr($r['mgroup_others'], $groupidleader) == true))
		{
            return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователь является модератором подфорума!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
		}//Игрок модератор
		if(stristr($r['mgroup_others'], $groupid) === false and $r['member_group_id'] != $groupid2)
		{
	        return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователь не имеет доступа к подфоруму!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
		}//У игрока нет доступа
		////
		if($prefix == "helper")
		{
		    if($r['member_group_id'] == $groupid2)//осн = хелпер
		    {
                if($r['mgroup_others'] == ",29,")//29 - модератор
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '29' WHERE `member_id` = '$name'");
                }
                else if(strpos($r['mgroup_others'],",29,"))
                {
                    $groups = str_replace(array(",29,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '29' WHERE `member_id` = '$name'");
                }
                else if($r['mgroup_others'] == ",5,")//5 - забанен
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '5' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",5,"))
                {
                    $groups = str_replace(array(",5,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '5' WHERE `member_id` = '$name'");
                }                    
                else if($r['mgroup_others'] == ",3,")//3 - пользователь
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '3' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",3,"))
                {
                    $groups = str_replace(array(",3,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '3' WHERE `member_id` = '$name'");
                }                
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();                
		    }
		    else
		    {
                if($r['mgroup_others'] == $groupid) $groups = "";
                else $groups = str_replace(array($groupid), ',', $r['mgroup_others']);
                //		else $groups = str_replace(array($groupid.',', ','.$groupid), '', $r['mgroup_others']);
                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."' WHERE `member_id` = '$name'");
                if($zapros)
                {
                    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
                }
                else
                {
                    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
                }
                mysql_close();		        
		    }
		}
//
		if($prefix == "piar")
		{
		    if($r['member_group_id'] == $groupid2)//осн = piar
		    {
                if($r['mgroup_others'] == ",7,")//3 - хелпер
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '7' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",7,"))
                {
                    $groups = str_replace(array(",7,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '7' WHERE `member_id` = '$name'");
                }   		        
                else if($r['mgroup_others'] == ",29,")//29 - модератор
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '29' WHERE `member_id` = '$name'");
                }
                else if(strpos($r['mgroup_others'],",29,"))
                {
                    $groups = str_replace(array(",29,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '29' WHERE `member_id` = '$name'");
                }
                else if($r['mgroup_others'] == ",5,")//5 - забанен
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '5' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",5,"))
                {
                    $groups = str_replace(array(",5,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '5' WHERE `member_id` = '$name'");
                }                    
                else if($r['mgroup_others'] == ",3,")//3 - пользователь
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '3' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",3,"))
                {
                    $groups = str_replace(array(",3,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '3' WHERE `member_id` = '$name'");
                }         
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
        		}
        		mysql_close();                
		    }
		    else
		    {
                if($r['mgroup_others'] == $groupid) $groups = "";
                else $groups = str_replace(array($groupid), ',', $r['mgroup_others']);
                //		else $groups = str_replace(array($groupid.',', ','.$groupid), '', $r['mgroup_others']);
                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."' WHERE `member_id` = '$name'");
                if($zapros)
                {
                    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
                }
                else
                {
                    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
                }
                mysql_close();		        
		    }
		}
/////
		if($prefix == "admin")
		{
		    if($r['member_group_id'] == $groupid2)//осн = admin
		    {
                if($r['mgroup_others'] == ",30,")//30 - piar
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '30' WHERE `member_id` = '$name'");
                }   		        
                else if(strpos($r['mgroup_others'],",30,"))
                {
                    $groups = str_replace(array(",30,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '30' WHERE `member_id` = '$name'");
                }   	
                else if($r['mgroup_others'] == ",7,")//3 - хелпер
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '7' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",7,"))
                {
                    $groups = str_replace(array(",7,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '7' WHERE `member_id` = '$name'");
                }                           
                else if($r['mgroup_others'] == ",29,")//29 - модератор
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '29' WHERE `member_id` = '$name'");
                }
                else if(strpos($r['mgroup_others'],",29,"))
                {
                    $groups = str_replace(array(",29,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '29' WHERE `member_id` = '$name'");
                }
                else if($r['mgroup_others'] == ",5,")//5 - забанен
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '5' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",5,"))
                {
                    $groups = str_replace(array(",5,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '5' WHERE `member_id` = '$name'");
                }                    
                else if($r['mgroup_others'] == ",3,")//3 - пользователь
                {
                     $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '',`member_group_id` = '3' WHERE `member_id` = '$name'");
                }   
                else if(strpos($r['mgroup_others'],",3,"))
                {
                    $groups = str_replace(array(",3,"), ',', $r['mgroup_others']);
	                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."',`member_group_id` = '3' WHERE `member_id` = '$name'");
                }         
        		if($zapros)
        		{
        		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayerss=1">';
        		}
        		else
        		{
        		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayerss=1">';
        		}
        		mysql_close();                
		    }
		    else
		    {
                if($r['mgroup_others'] == $groupid) $groups = "";
                else $groups = str_replace(array($groupid), ',', $r['mgroup_others']);
                //		else $groups = str_replace(array($groupid.',', ','.$groupid), '', $r['mgroup_others']);
                $zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."' WHERE `member_id` = '$name'");
                if($zapros)
                {
                    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayerss=1">';
                }
                else
                {
                    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayerss=1">';
                }
                mysql_close();		        
		    }
		}
/////
		////
		if($r['mgroup_others'] == $groupid) $groups = "";
		else $groups = str_replace(array($groupid), ',', $r['mgroup_others']);
//		else $groups = str_replace(array($groupid.',', ','.$groupid), '', $r['mgroup_others']);
		$zapros = mysql_query("UPDATE `ipb_members` SET `mgroup_others` = '".$groups."' WHERE `member_id` = '$name'");
		if($zapros)
		{
		    return $mess = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Пользователю '.$r["name"].' был закрыт доступ к скрытому подфоруму.</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
		}
		else
		{
		    return $mess = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестная ошибка, попробуйте позже!</div></div></div>&nbsp <meta http-equiv="refresh" content="2; URL=index.php?act='.$prefix.'&forumplayers=1">';
		}
		mysql_close();
	}
	function GetForumName($i)
	{
        $info_account = '<img src="'.TEMPLATE_DIR.'/'.TEMPLATE.'/images/info.png"/>';
        $db = mysql_connect(F_MySQL_HOSTNAME, F_MySQL_USER, F_MySQL_PASSWORD);
        if(!$db)
        {
        echo 'Нет подключения к базе данных, обратитесь к Администрации!';
        exit();
        }
        mysql_select_db(F_MySQL_DB, $db);
        mysql_set_charset('utf8');
        date_default_timezone_set('Europe/Minsk');
        $array_uc = mysql_query("SELECT `name` FROM `ipb_members` WHERE `member_id` = '".$i."' ");
        $r = mysql_fetch_array($array_uc);
        mysql_close();
		return $r['name'];
	}
	function GetForumID($i)
	{
        $info_account = '<img src="'.TEMPLATE_DIR.'/'.TEMPLATE.'/images/info.png"/>';
        $db = mysql_connect(F_MySQL_HOSTNAME, F_MySQL_USER, F_MySQL_PASSWORD);
        if(!$db)
        {
        echo 'Нет подключения к базе данных, обратитесь к Администрации!';
        exit();
        }
        mysql_select_db(F_MySQL_DB, $db);
        mysql_set_charset('utf8');
        date_default_timezone_set('Europe/Minsk');
        $array_uc = mysql_query("SELECT `member_id` FROM `ipb_members` WHERE `name` = '".$i."' ");
        $r = mysql_fetch_array($array_uc);
        mysql_close();
		return $r['member_id'];
	}
	
	function GetForumPlayers($i)
	{
		switch($i)
		{
		    case 2: $groupid = 38; $prefix = "leader"; break;//FBI
		    case 13: $groupid = 32; $prefix = "leader"; break;//destroyers
		    case 14: $groupid = 31; $prefix = "leader"; break;//killers
		    case -1: $groupid = 7; $prefix = "helper"; break;//helper
            case -2: $groupid = 30; $prefix = "admin"; break;//piar
            case -3: $groupid = 28; $prefix = "admin"; break;//piar            
			default: return 0;
		}
		if($i != "-3") 
		{
    		$prefixx = "addforum";
            $prefixxx = "delforum";		
		}
		else 
		{
		    $prefixx = "addforumm";
            $prefixxx = "dellforum";		    
		}
		$info_account = '<img src="'.TEMPLATE_DIR.'/'.TEMPLATE.'/images/info.png"/>';
		$db = mysql_connect(F_MySQL_HOSTNAME, F_MySQL_USER, F_MySQL_PASSWORD);
		if(!$db)
		{
		echo 'Нет подключения к базе данных, обратитесь к Администрации!';
		exit();
		}
		mysql_select_db(F_MySQL_DB, $db);
		mysql_set_charset('utf8');
		date_default_timezone_set('Europe/Minsk');
		if($i != "-3") $array_uc = mysql_query("SELECT `name`,`member_id`,`mgroup_others` FROM `ipb_members` WHERE `mgroup_others` LIKE '%,".$groupid.",%' or `member_group_id`='".$groupid."' ORDER BY `name`");
		else $array_uc = mysql_query("SELECT `name`,`member_id`,`mgroup_others` FROM `ipb_members` WHERE `mgroup_others` LIKE '%,".$groupid.",%' or `member_group_id`='".$groupid."' ORDER BY `member_id`");
		$number = mysql_num_rows($array_uc);
		$history = "";
		$list_return = "";
		$r = mysql_fetch_array($array_uc);
		if($number > 0)
		{
    		do
    		{
                $url = "<td style='padding:5px 15px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:10%;'><b><font color='#777777'><center><a href='index.php?".PREFIX."=".$prefix."&".$prefixxx."=".$r['member_id']."'>$info_account</a></center></font></b></td>";
				$history .= "
				<td style='padding:5px 20px;color:#777777;background:#fff;border-right:1px solid #ddd;border-bottom:1px solid #ddd; width:30%;'><b><font color='#777777'><center>".$r['name']."</center></font></b></td>
				$url
				</tr><tr>
				";
			}
			while($r = mysql_fetch_array($array_uc));
			if($number >= 20) $overflow = "height: 44em; overflow: auto";
			else $overflow = "height: auto;";
				$list_return = '
				<div style="width: 100%;" class="box_top"><div style="width: 100%;" class="box_top_text">
				<div class="eTitle">Количество игроков, которым доступен скрытый подфорум: ['.$number.']</div>

				</div></div>
				<div style="width: 100%; border:1px solid #cccccc; text-align:justify;" />
				<div style="padding:2px 5px;" />
				<div style="'.$overflow.'">
				<table style="border-collapse:collapse;width:100%;padding:0px;">
				<tbody>
				<tr>
				<td style="padding:0px;">
				<div class="eMessage">
				<div><div class="box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
				<table style="margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
				<td style="padding:5px 30px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:30%;"><b><font color="#777777"><center>Ник на форуме</center></font></b></td>
				<td style="padding:5px 15px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:10%;"><b><font color="#777777"><center>Убрать доступ</center></font></b></td>
				</tr><tr>
				'.$history.'
				</tr></tbody></table>
				</div>
				</div>
				</div>
				</td></tr></tbody></table>
				</div>
				</div>
				</div></div>
				';
		}
		else $list_return = "<div class='box_top_error'><div class='box_top_text_error'> <div class='eTitle'>Нет пользователей для отображения!</div></div></div>&nbsp";
		mysql_close();
		return $list_return.='
		<br>
		<form method="post" action="index.php?act='.$prefix.'&'.$prefixx.'=1">
		<center><input name="enter" class="input" type="submit" value=" Добавить пользователя "></center>
 		<br>
		</form>
	';   
		}


	function GetLeaderVigovor($i)
	{
        $db = new Database;
        if(!$db)
        {
        echo 'Нет подключения к базе данных, обратитесь к Администрации!';
        exit();
        }
        if($i == "None")
        {
            return "None";
        }
        else
        {
            $r = $db->super_query("SELECT `pLeaderVigovor` FROM `users` WHERE `pNick` = '".$i."' ");
            mysql_close();
    		return $r['pLeaderVigovor'].="/3";
        }
	}
	function GetLeaderWeekTime($i)
	{
        $db = new Database;
        if(!$db)
        {
            echo 'Нет подключения к базе данных, обратитесь к Администрации!';
            exit();
        }
        if($i == "None")
        {
            return 0;
        }
        else
        {
            $r = $db->super_query("SELECT `pWeekTime` FROM `users` WHERE `pNick` = '".$i."' ");
            mysql_close();
            if($r['pWeekTime'] < "14")
            {
                $text = "<font color='#ff3030'>";
               return $text.=$r['pWeekTime'].="</font>";
            }
            else
            {
    		    return $r['pWeekTime'];
            }
        }
	}
	
	function GetDiplomation($member)
    {
        $db = new Database;
		$zapros = $db->super_query("SELECT * FROM `obshak` WHERE 1");
       switch($member)
		{
		    case 5:
		    {
				$string = "{00FFFF}Дипломатия: Yakuza Mafia<br>";
				if($zapros['yakuzarm'] == 9 || $zapros['lcnyakuza'] == 9 || $zapros['coronayakuza'] == 9 || $zapros['rifayakuza'] == 9 || $zapros['groveyakuza'] == 9 || $zapros['vagosyakuza'] == 9 || $zapros['streetyakuza'] == 9 || $zapros['ballasyakuza'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['yakuzarm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['lcnyakuza'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
  					if($zapros['coronayakuza'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifayakuza'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['groveyakuza'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosyakuza'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetyakuza'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasyakuza'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['yakuzarm'] > 5 && $zapros['yakuzarm'] < 9 || $zapros['lcnyakuza'] > 5 && $zapros['lcnyakuza'] < 9 || $zapros['rifayakuza'] > 5 && $zapros['rifayakuza'] < 9 || $zapros['coronayakuza'] > 5 && $zapros['coronayakuza'] < 9 || $zapros['groveyakuza'] > 5 && $zapros['groveyakuza'] < 9 || $zapros['vagosyakuza'] > 5 && $zapros['vagosyakuza'] < 9 || $zapros['streetyakuza'] > 5 && $zapros['streetyakuza'] < 9 || $zapros['ballasyakuza'] > 5 && $zapros['ballasyakuza'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['yakuzarm'] > 5 && $zapros['yakuzarm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['lcnyakuza'] > 5 && $zapros['lcnyakuza'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronayakuza'] > 5 && $zapros['coronayakuza'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifayakuza'] > 5 && $zapros['rifayakuza'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['groveyakuza'] > 5 && $zapros['groveyakuza'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosyakuza'] > 5 && $zapros['vagosyakuza'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetyakuza'] > 5 && $zapros['streetyakuza'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasyakuza'] > 5 && $zapros['ballasyakuza'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['yakuzarm'] > 0 && $zapros['yakuzarm'] < 6 || $zapros['lcnyakuza'] > 0 && $zapros['lcnyakuza'] < 6 || $zapros['rifayakuza'] > 0 && $zapros['rifayakuza'] < 6 || $zapros['coronayakuza'] > 0 && $zapros['coronayakuza'] < 6 || $zapros['groveyakuza'] > 0 && $zapros['groveyakuza'] < 6 || $zapros['vagosyakuza'] > 0 && $zapros['vagosyakuza'] < 6 || $zapros['streetyakuza'] > 0 && $zapros['streetyakuza'] < 6 || $zapros['ballasyakuza'] > 0 && $zapros['ballasyakuza'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['yakuzarm'] > 0 && $zapros['yakuzarm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['lcnyakuza'] > 0 && $zapros['lcnyakuza'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronayakuza'] > 0 && $zapros['coronayakuza'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifayakuza'] > 0 && $zapros['rifayakuza'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['groveyakuza'] > 0 && $zapros['groveyakuza'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosyakuza'] > 0 && $zapros['vagosyakuza'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetyakuza'] > 0 && $zapros['streetyakuza'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasyakuza'] > 0 && $zapros['ballasyakuza'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 6:
		    {
				$string = "{00FFFF}Дипломатия: El Coronos Gang<br>";
				if($zapros['coronarm'] == 9 || $zapros['coronalcn'] == 9 || $zapros['coronarifa'] == 9 || $zapros['coronayakuza'] == 9 || $zapros['grovecorona'] == 9 || $zapros['vagoscorona'] == 9 || $zapros['streetcorona'] == 9 || $zapros['ballascorona'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['coronayakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['coronarm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['coronalcn'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarifa'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovecorona'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagoscorona'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetcorona'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballascorona'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronarm'] > 5 && $zapros['coronarm'] < 9 || $zapros['coronalcn'] > 5 && $zapros['coronalcn'] < 9 || $zapros['coronarifa'] > 5 && $zapros['coronarifa'] < 9 || $zapros['coronayakuza'] > 5 && $zapros['coronayakuza'] < 9 || $zapros['grovecorona'] > 5 && $zapros['grovecorona'] < 9 || $zapros['vagoscorona'] > 5 && $zapros['vagoscorona'] < 9 || $zapros['streetcorona'] > 5 && $zapros['streetcorona'] < 9 || $zapros['ballascorona'] > 5 && $zapros['ballascorona'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['coronarm'] > 5 && $zapros['coronarm'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['coronarm'] > 5 && $zapros['coronarm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['coronalcn'] > 5 && $zapros['coronalcn'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarifa'] > 5 && $zapros['coronarifa'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovecorona'] > 5 && $zapros['grovecorona'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagoscorona'] > 5 && $zapros['vagoscorona'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetcorona'] > 5 && $zapros['streetcorona'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballascorona'] > 5 && $zapros['ballascorona'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronarm'] > 0 && $zapros['coronarm'] < 6 || $zapros['coronalcn'] > 0 && $zapros['coronalcn'] < 6 || $zapros['coronarifa'] > 0 && $zapros['coronarifa'] < 6 || $zapros['coronayakuza'] > 0 && $zapros['coronayakuza'] < 6 || $zapros['grovecorona'] > 0 && $zapros['grovecorona'] < 6 || $zapros['vagoscorona'] > 0 && $zapros['vagoscorona'] < 6 || $zapros['streetcorona'] > 0 && $zapros['streetcorona'] < 6 || $zapros['ballascorona'] > 0 && $zapros['ballascorona'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['coronayakuza'] > 0 && $zapros['coronayakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['coronarm'] > 0 && $zapros['coronarm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['coronalcn'] > 0 && $zapros['coronalcn'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarifa'] > 0 && $zapros['coronarifa'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovecorona'] > 0 && $zapros['grovecorona'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagoscorona'] > 0 && $zapros['vagoscorona'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetcorona'] > 0 && $zapros['streetcorona'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballascorona'] > 0 && $zapros['ballascorona'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 8:
		    {
				$string = "{00FFFF}Дипломатия: Русской Мафии<br>";
				if($zapros['coronarm'] == 9 || $zapros['lcnrm'] == 9 || $zapros['rifarm'] == 9 || $zapros['yakuzarm'] == 9 || $zapros['groverm'] == 9 || $zapros['vagosrm'] == 9 || $zapros['streetrm'] == 9 || $zapros['ballasrm'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['yakuzarm'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['lcnrm'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarm'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifarm'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['groverm'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosrm'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetrm'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasrm'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronarm'] > 5 && $zapros['coronarm'] < 9 || $zapros['lcnrm'] > 5 && $zapros['lcnrm'] < 9 || $zapros['rifarm'] > 5 && $zapros['rifarm'] < 9 || $zapros['yakuzarm'] > 5 && $zapros['yakuzarm'] < 9 || $zapros['groverm'] > 5 && $zapros['groverm'] < 9 || $zapros['vagosrm'] > 5 && $zapros['vagosrm'] < 9 || $zapros['streetrm'] > 5 && $zapros['streetrm'] < 9 || $zapros['ballasrm'] > 5 && $zapros['ballasrm'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['yakuzarm'] > 5 && $zapros['yakuzarm'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['lcnrm'] > 5 && $zapros['lcnrm'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarm'] > 5 && $zapros['coronarm'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifarm'] > 5 && $zapros['rifarm'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['groverm'] > 5 && $zapros['groverm'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosrm'] > 5 && $zapros['vagosrm'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetrm'] > 5 && $zapros['streetrm'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasrm'] > 5 && $zapros['ballasrm'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronarm'] > 0 && $zapros['coronarm'] < 6 || $zapros['lcnrm'] > 0 && $zapros['lcnrm'] < 6 || $zapros['rifarm'] > 0 && $zapros['rifarm'] < 6 || $zapros['yakuzarm'] > 0 && $zapros['yakuzarm'] < 6 || $zapros['groverm'] > 0 && $zapros['groverm'] < 6 || $zapros['vagosrm'] > 0 && $zapros['vagosrm'] < 6 || $zapros['streetrm'] > 0 && $zapros['streetrm'] < 6 || $zapros['ballasrm'] > 0 && $zapros['ballasrm'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['yakuzarm'] > 0 && $zapros['yakuzarm'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['lcnrm'] > 0 && $zapros['lcnrm'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarm'] > 0 && $zapros['coronarm'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifarm'] > 0 && $zapros['rifarm'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['groverm'] > 0 && $zapros['groverm'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosrm'] > 0 && $zapros['vagosrm'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetrm'] > 0 && $zapros['streetrm'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasrm'] > 0 && $zapros['ballasrm'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 10:
		    {
				$string = "{00FFFF}Дипломатия: La Cosa Nostra<br>";
				if($zapros['coronalcn'] == 9 || $zapros['lcnrm'] == 9 || $zapros['rifalcn'] == 9 || $zapros['lcnyakuza'] == 9 || $zapros['grovelcn'] == 9 || $zapros['vagoslcn'] == 9 || $zapros['streetlcn'] == 9 || $zapros['ballaslcn'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['lcnyakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['lcnrm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['coronalcn'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifalcn'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovelcn'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagoslcn'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetlcn'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballaslcn'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronalcn'] > 5 && $zapros['coronalcn'] < 9 || $zapros['lcnrm'] > 5 && $zapros['lcnrm'] < 9 || $zapros['rifalcn'] > 5 && $zapros['rifalcn'] < 9 || $zapros['lcnyakuza'] > 5 && $zapros['lcnyakuza'] < 9 || $zapros['grovelcn'] > 5 && $zapros['grovelcn'] < 9 || $zapros['vagoslcn'] > 5 && $zapros['vagoslcn'] < 9 || $zapros['streetlcn'] > 5 && $zapros['streetlcn'] < 9 || $zapros['ballaslcn'] > 5 && $zapros['ballaslcn'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['lcnyakuza'] > 5 && $zapros['lcnyakuza'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['lcnrm'] > 5 && $zapros['lcnrm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['coronalcn'] > 5 && $zapros['coronalcn'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifalcn'] > 5 && $zapros['rifalcn'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovelcn'] > 5 && $zapros['grovelcn'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagoslcn'] > 5 && $zapros['vagoslcn'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetlcn'] > 5 && $zapros['streetlcn'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballaslcn'] > 5 && $zapros['ballaslcn'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronalcn'] > 0 && $zapros['coronalcn'] < 6 || $zapros['lcnrm'] > 0 && $zapros['lcnrm'] < 6 || $zapros['rifalcn'] > 0 && $zapros['rifalcn'] < 6 || $zapros['lcnyakuza'] > 0 && $zapros['lcnyakuza'] < 6 || $zapros['grovelcn'] > 0 && $zapros['grovelcn'] < 6 || $zapros['vagoslcn'] > 0 && $zapros['vagoslcn'] < 6 || $zapros['streetlcn'] > 0 && $zapros['streetlcn'] < 6 || $zapros['ballaslcn'] > 0 && $zapros['ballaslcn'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['lcnyakuza'] > 0 && $zapros['lcnyakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['lcnrm'] > 0 && $zapros['lcnrm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['coronalcn'] > 0 && $zapros['coronalcn'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['rifalcn'] > 0 && $zapros['rifalcn'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovelcn'] > 0 && $zapros['grovelcn'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagoslcn'] > 0 && $zapros['vagoslcn'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetlcn'] > 0 && $zapros['streetlcn'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballaslcn'] > 0 && $zapros['ballaslcn'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 11:
		    {
				$string = "{00FFFF}Дипломатия: San Fierro Rifa Gang<br>";
				if($zapros['coronarifa'] == 9 || $zapros['rifarm'] == 9 || $zapros['rifalcn'] == 9 || $zapros['rifayakuza'] == 9 || $zapros['groverifa'] == 9 || $zapros['vagosrifa'] == 9 || $zapros['streetrifa'] == 9 || $zapros['ballasrifa'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['rifayakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['rifarm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['rifalcn'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarifa'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['groverifa'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosrifa'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetrifa'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasrifa'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronarifa'] > 5 && $zapros['coronarifa'] < 9 || $zapros['rifarm'] > 5 && $zapros['rifarm'] < 9 || $zapros['rifalcn'] > 5 && $zapros['rifalcn'] < 9 || $zapros['rifayakuza'] > 5 && $zapros['rifayakuza'] < 9 || $zapros['groverifa'] > 5 && $zapros['groverifa'] < 9 || $zapros['vagosrifa'] > 5 && $zapros['vagosrifa'] < 9 || $zapros['streetrifa'] > 5 && $zapros['streetrifa'] < 9 || $zapros['ballasrifa'] > 5 && $zapros['ballasrifa'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['rifayakuza'] > 5 && $zapros['rifayakuza'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['rifarm'] > 5 && $zapros['rifarm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['rifalcn'] > 5 && $zapros['rifalcn'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarifa'] > 5 && $zapros['coronarifa'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['groverifa'] > 5 && $zapros['groverifa'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosrifa'] > 5 && $zapros['vagosrifa'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetrifa'] > 5 && $zapros['streetrifa'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasrifa'] > 5 && $zapros['ballasrifa'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['coronarifa'] > 0 && $zapros['coronarifa'] < 6 || $zapros['rifarm'] > 0 && $zapros['rifarm'] < 6 || $zapros['rifalcn'] > 0 && $zapros['rifalcn'] < 6 || $zapros['rifayakuza'] > 0 && $zapros['rifayakuza'] < 6 || $zapros['groverifa'] > 0 && $zapros['groverifa'] < 6 || $zapros['vagosrifa'] > 0 && $zapros['vagosrifa'] < 6 || $zapros['streetrifa'] > 0 && $zapros['streetrifa'] < 6 || $zapros['ballasrifa'] > 0 && $zapros['ballasrifa'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['rifayakuza'] > 0 && $zapros['rifayakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['rifarm'] > 0 && $zapros['rifarm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['rifalcn'] > 0 && $zapros['rifalcn'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['coronarifa'] > 0 && $zapros['coronarifa'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['groverifa'] > 0 && $zapros['groverifa'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['vagosrifa'] > 0 && $zapros['vagosrifa'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetrifa'] > 0 && $zapros['streetrifa'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasrifa'] > 0 && $zapros['ballasrifa'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 15:
		    {
				$string = "{00FFFF}Дипломатия: Grove Street Gang<br>";
				if($zapros['grovecorona'] == 9 || $zapros['groverm'] == 9 || $zapros['grovelcn'] == 9 || $zapros['groveyakuza'] == 9 || $zapros['groverifa'] == 9 || $zapros['vagosgrove'] == 9 || $zapros['streetgrove'] == 9 || $zapros['ballasgrove'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['groveyakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['groverm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['grovelcn'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['groverifa'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['grovecorona'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['vagosgrove'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetgrove'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasgrove'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['grovecorona'] > 5 && $zapros['grovecorona'] < 9 || $zapros['groverm'] > 5 && $zapros['groverm'] < 9 || $zapros['grovelcn'] > 5 && $zapros['grovelcn'] < 9 || $zapros['groveyakuza'] > 5 && $zapros['groveyakuza'] < 9 || $zapros['groverifa'] > 5 && $zapros['groverifa'] < 9 || $zapros['vagosgrove'] > 5 && $zapros['vagosgrove'] < 9 || $zapros['streetgrove'] > 5 && $zapros['streetgrove'] < 9 || $zapros['ballasgrove'] > 5 && $zapros['ballasgrove'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['groveyakuza'] > 5 && $zapros['groveyakuza'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['groverm'] > 5 && $zapros['groverm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['grovelcn'] > 5 && $zapros['grovelcn'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['grovecorona'] > 5 && $zapros['grovecorona'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['groverifa'] > 5 && $zapros['groverifa'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['vagosgrove'] > 5 && $zapros['vagosgrove'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetgrove'] > 5 && $zapros['streetgrove'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasgrove'] > 5 && $zapros['ballasgrove'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['grovecorona'] > 0 && $zapros['grovecorona'] < 6 || $zapros['groverm'] > 0 && $zapros['groverm'] < 6 || $zapros['grovelcn'] > 0 && $zapros['grovelcn'] < 6 || $zapros['groveyakuza'] > 0 && $zapros['groveyakuza'] < 6 || $zapros['groverifa'] > 0 && $zapros['groverifa'] < 6 || $zapros['vagosgrove'] > 0 && $zapros['vagosgrove'] < 6 || $zapros['streetgrove'] > 0 && $zapros['streetgrove'] < 6 || $zapros['ballasgrove'] > 0 && $zapros['ballasgrove'] < 5)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['groveyakuza'] > 0 && $zapros['groveyakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['grovecorona'] > 0 && $zapros['grovecorona'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['grovelcn'] > 0 && $zapros['grovelcn'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['grovecorona'] > 0 && $zapros['grovecorona'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['groverifa'] > 0 && $zapros['groverifa'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['vagosgrove'] > 0 && $zapros['vagosgrove'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['streetgrove'] > 0 && $zapros['streetgrove'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasgrove'] > 0 && $zapros['ballasgrove'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 16:
		    {
				$string = "{00FFFF}Дипломатия: Los Santos Vagos Gang<br>";
				if($zapros['vagoscorona'] == 9 || $zapros['vagosrm'] == 9 || $zapros['vagoslcn'] == 9 || $zapros['vagosyakuza'] == 9 || $zapros['vagosrifa'] == 9 || $zapros['vagosgrove'] == 9 || $zapros['streetvagos'] == 9 || $zapros['ballasvagos'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['vagosyakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['vagosrm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['vagoslcn'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['vagoscorona'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['vagosrifa'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['vagosgrove'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['streetvagos'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasvagos'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['vagoscorona'] > 5 && $zapros['vagoscorona'] < 9 || $zapros['vagosrm'] > 5 && $zapros['vagosrm'] < 9 || $zapros['vagoslcn'] > 5 && $zapros['vagoslcn'] < 9 || $zapros['vagosyakuza'] > 5 && $zapros['vagosyakuza'] < 9 || $zapros['vagosrifa'] > 5 && $zapros['vagosrifa'] < 9 || $zapros['vagosgrove'] > 5 && $zapros['vagosgrove'] < 9 || $zapros['streetvagos'] > 5 && $zapros['streetvagos'] < 9 || $zapros['ballasvagos'] > 5 && $zapros['ballasvagos'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['vagosyakuza'] > 5 && $zapros['vagosyakuza'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['vagosrm'] > 5 && $zapros['vagosrm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['vagoslcn'] > 5 && $zapros['vagoslcn'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['vagoscorona'] > 5 && $zapros['vagoscorona'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['vagosrifa'] > 5 && $zapros['vagosrifa'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['vagosgrove'] > 5 && $zapros['vagosgrove'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['streetvagos'] > 5 && $zapros['streetvagos'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasvagos'] > 5 && $zapros['ballasvagos'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['vagoscorona'] > 0 && $zapros['vagoscorona'] < 6 || $zapros['vagosrm'] > 0 && $zapros['vagosrm'] < 6 || $zapros['vagoslcn'] > 0 && $zapros['vagoslcn'] < 6 || $zapros['vagosyakuza'] > 0 && $zapros['vagosyakuza'] < 6 || $zapros['vagosrifa'] > 0 && $zapros['vagosrifa'] < 6 || $zapros['vagosgrove'] > 0 && $zapros['vagosgrove'] < 6 || $zapros['streetvagos'] > 0 && $zapros['streetvagos'] < 6 || $zapros['ballasvagos'] > 0 && $zapros['ballasvagos'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['vagosyakuza'] > 0 && $zapros['vagosyakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['vagosrm'] > 0 && $zapros['vagosrm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['vagoslcn'] > 0 && $zapros['vagoslcn'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['vagoscorona'] > 0 && $zapros['vagoscorona'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['vagosrifa'] > 0 && $zapros['vagosrifa'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['vagosgrove'] > 0 && $zapros['vagosgrove'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['streetvagos'] > 0 && $zapros['streetvagos'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
					if($zapros['ballasvagos'] > 0 && $zapros['ballasvagos'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 17:
		    {
				$string = "{00FFFF}Дипломатия: Street Racers Club<br>";
				if($zapros['streetcorona'] == 9 || $zapros['streetrm'] == 9 || $zapros['streetlcn'] == 9 || $zapros['streetyakuza'] == 9 || $zapros['streetrifa'] == 9 || $zapros['streetvagos'] == 9 || $zapros['streetgrove'] == 9 || $zapros['ballasstreet'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['streetyakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['streetrm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['streetlcn'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['streetcorona'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['streetrifa'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['streetgrove'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['streetvagos'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['ballasstreet'] == 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['streetcorona'] > 5 && $zapros['streetcorona'] < 9 || $zapros['streetrm'] > 5 && $zapros['streetrm'] < 9 || $zapros['streetlcn'] > 5 && $zapros['streetlcn'] < 9 || $zapros['streetyakuza'] > 5 && $zapros['streetyakuza'] < 9 || $zapros['streetrifa'] > 5 && $zapros['streetrifa'] < 9 || $zapros['streetvagos'] > 5 && $zapros['streetvagos'] < 9 || $zapros['streetgrove'] > 5 && $zapros['streetgrove'] < 9 || $zapros['ballasstreet'] > 5 && $zapros['ballasstreet'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['streetyakuza'] > 5 && $zapros['streetyakuza'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['streetrm'] > 5 && $zapros['streetrm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['streetlcn'] > 5 && $zapros['streetlcn'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['streetcorona'] > 5 && $zapros['streetcorona'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['streetrifa'] > 5 && $zapros['streetrifa'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['streetgrove'] > 5 && $zapros['streetgrove'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['streetvagos'] > 5 && $zapros['streetvagos'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['ballasstreet'] > 5 && $zapros['ballasstreet'] < 9)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				if($zapros['streetcorona'] > 0 && $zapros['streetcorona'] < 6 || $zapros['streetrm'] > 0 && $zapros['streetrm'] < 6 || $zapros['streetlcn'] > 0 && $zapros['streetlcn'] < 6 || $zapros['streetyakuza'] > 0 && $zapros['streetyakuza'] < 6 || $zapros['streetrifa'] > 0 && $zapros['streetrifa'] < 6 || $zapros['streetvagos'] > 0 && $zapros['streetvagos'] < 6 || $zapros['streetgrove'] > 0 && $zapros['streetgrove'] < 6 || $zapros['ballasstreet'] > 0 && $zapros['ballasstreet'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['streetyakuza'] > 0 && $zapros['streetyakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['streetrm'] > 0 && $zapros['streetrm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['streetlcn'] > 0 && $zapros['streetlcn'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['streetcorona'] > 0 && $zapros['streetcorona'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['streetrifa'] > 0 && $zapros['streetrifa'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['streetgrove'] > 0 && $zapros['streetgrove'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['streetvagos'] > 0 && $zapros['streetvagos'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['ballasstreet'] > 0 && $zapros['ballasstreet'] < 6)
					{
						$string .= "East Side Ballas Gang<br>";
					}
				}
				return $string;
			}
		    case 18:
		    {
				$string = "{00FFFF}Дипломатия: East Side Ballas Gang<br>";
				if($zapros['ballascorona'] == 9 || $zapros['ballasrm'] == 9 || $zapros['ballaslcn'] == 9 || $zapros['ballasyakuza'] == 9 || $zapros['ballasrifa'] == 9 || $zapros['ballasvagos'] == 9 || $zapros['ballasgrove'] == 9 || $zapros['ballasstreet'] == 9)
				{
					$string .= "<br>{FFFFFF}Союз:{00FF00}<br>";
					if($zapros['ballasyakuza'] == 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['ballasrm'] == 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['ballaslcn'] == 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['ballasrifa'] == 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['ballascorona'] == 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['ballasgrove'] == 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['ballasvagos'] == 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['ballasstreet'] == 9)
					{
						$string .= "Street Racers Club<br>";
					}
				}
				if($zapros['ballascorona'] > 5 && $zapros['ballascorona'] < 9 || $zapros['ballasrm'] > 5 && $zapros['ballasrm'] < 9 || $zapros['ballaslcn'] > 5 && $zapros['ballaslcn'] < 9 || $zapros['ballasyakuza'] > 5 && $zapros['ballasyakuza'] < 9 || $zapros['ballasrifa'] > 5 && $zapros['ballasrifa'] < 9 || $zapros['ballasvagos'] > 5 && $zapros['ballasvagos'] < 9 || $zapros['ballasgrove'] > 5 && $zapros['ballasgrove'] < 9 || $zapros['ballasstreet'] > 5 && $zapros['ballasstreet'] < 9)
				{
					$string .= "<br>{FFFFFF}Нейтралитет:{FFFF00}<br>";
					if($zapros['ballasyakuza'] > 5 && $zapros['ballasyakuza'] < 9)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['ballasrm'] > 5 && $zapros['ballasrm'] < 9)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['ballaslcn'] > 5 && $zapros['ballaslcn'] < 9)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['ballascorona'] > 5 && $zapros['ballascorona'] < 9)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['ballasrifa'] > 5 && $zapros['ballasrifa'] < 9)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['ballasgrove'] > 5 && $zapros['ballasgrove'] < 9)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['ballasvagos'] > 5 && $zapros['ballasvagos'] < 9)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['ballasstreet'] > 5 && $zapros['ballasstreet'] < 9)
					{
						$string .= "Street Racers Club<br>";
					}
				}
				if($zapros['ballascorona'] > 0 && $zapros['ballascorona'] < 6 || $zapros['ballasrm'] > 0 && $zapros['ballasrm'] < 6 || $zapros['ballaslcn'] > 0 && $zapros['ballaslcn'] < 6 || $zapros['ballasyakuza'] > 0 && $zapros['ballasyakuza'] < 6 || $zapros['ballasrifa'] > 0 && $zapros['ballasrifa'] < 6 || $zapros['ballasvagos'] > 0 && $zapros['ballasvagos'] < 6 || $zapros['ballasgrove'] > 0 && $zapros['ballasgrove'] < 6 || $zapros['ballasstreet'] > 0 && $zapros['ballasstreet'] < 6)
				{
					$string .= "<br>{FFFFFF}Война:{FF0000}<br>";
					if($zapros['ballasyakuza'] > 0 && $zapros['ballasyakuza'] < 6)
					{
						$string .= "Yakuza Mafia<br>";
					}
					if($zapros['ballasrm'] > 0 && $zapros['ballasrm'] < 6)
					{
						$string .= "Русская мафия<br>";
					}
					if($zapros['ballaslcn'] > 0 && $zapros['ballaslcn'] < 6)
					{
						$string .= "La Cosa Nostra<br>";
					}
					if($zapros['ballascorona'] > 0 && $zapros['ballascorona'] < 6)
					{
						$string .= "El Coronos Gang<br>";
					}
					if($zapros['ballasrifa'] > 0 && $zapros['ballasrifa'] < 6)
					{
						$string .= "San Fierro Rifa Gang<br>";
					}
					if($zapros['ballasgrove'] > 0 && $zapros['ballasgrove'] < 6)
					{
						$string .= "Grove Street Gang<br>";
					}
					if($zapros['ballasvagos'] > 0 && $zapros['ballasvagos'] < 6)
					{
						$string .= "Los Santos Vagos Gang<br>";
					}
					if($zapros['ballasstreet'] > 0 && $zapros['ballasstreet'] < 6)
					{
						$string .= "Street Racers Club<br>";
					}
				}
				return $string;
			}
		}		
    }




}

$user_class = new user_Functions;
$db->close ();
















?>
