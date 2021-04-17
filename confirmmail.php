<?
if($user_class->IsLogin())
{
$tpl->set('{CONTENT-NAME}','Привязка Email');
$error = 0;
if(isset($_POST['enter']) == "enter")
{
	$email = $_POST['email'];
	$ip=$_SERVER['REMOTE_ADDR'];
	$pass = $db->super_query("SELECT `pNick`,`pID` FROM `".TABLE_USERS."` WHERE  WHERE `".TABLE_USERS_NAME."` = '".SESSION_NAME."'");
	//		$password = md5($pass['Key']);
	//	$email=$_POST['email'];
	$nick = $pass['pNick'];
	$id = $pass['pID'];
 	if($user_class->IsExistEmNick($nick) and $error == 0)
	{
		$msg = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Ваш аккаунт уже привязан к почте!</div></div></div>&nbsp';
		$error = 1;
	}
	else if($email == "" and $error == 0)
	{
		$msg = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы не ввели Email!</div></div></div>&nbsp';
		$error = 1;
	}
	else if(!preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $email) and $error == 0)
	{
		$msg = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Некорректный Email!</div></div></div>&nbsp';
		$error = 1;
	}
	else
	{
		//Получаем логин пользователя в EMAIL-сети,Формируем подпись
		//$email_cnx=explode("@",$email);email_cnx[0]
		$checkSum=base64_encode($email.md5($ip.$email.time().$nick));
		$date=time();
		$result = $db->super_query("SELECT * FROM `validate_temp`  WHERE `nick` = '".$nick."' and `status` = '2' ");
		if($result == 0)
		{
			$otpravka = $db->query("INSERT into `validate_temp` (`id`,`nick`,`email`,`ip`,`date`,`status`) VALUES('".$id."','".$nick."','".$email."','".$ip."','".$date."','2')");
		}
		else
		{
			if($date - $result['date'] < 300)
			{
				$msg = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы уже отправляли код менее 5 минут назад! Попробуйте позже!</div></div></div>&nbsp';
				$error = 1;
			}
			else
			{
				$db->query("DELETE FROM `validate_temp` WHERE `nick`  = '".$nick."' and `status` = '2' ");
				$otpravka = $db->query("INSERT into `validate_temp` (`id`,`nick`,`email`,`ip`,`date`,`status`) VALUES('".$id."','".$nick."','".$email."','".$ip."','".$date."','2')");
			}
		}
		if($error == 0)
		{
			$url = "http://gameextream.rurs.net/forum/ucp/index.php?act=setmail&checkSum=$checkSum&email=$email&nick=$nick";
			$title = 'GameExtream - Поддержка';
			$message="Сегодня, ".date('d.m.Y в H.i.s', $date)." вы запросили код на смену email адреса от аккаунта. Для смены пароля перейдите по нижеприведённой ссылке:\n".$url."\nСсылка будет деактивирована: ".date('d.m.Y, H.i.s', $date+3600).", либо сразу после смены пароля.\n-----------------------------\nС уважением администрация GameExtream
			";
			if(mail($email,$title,$message,"Content-type:text/plain; charset = UTF-8\r\nFrom:GameExtream-Support"))
			{
				$msg = '<div class="box_top_success"><div class="box_top_text_success"> <div class="eTitle">Код отправлен на указанный Email. Перенаправляем на главную страницу...</div></div></div>&nbsp';
$error = 2;
			}
			else
			{
				$msg = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Ошибка отправки кода на Email. Перенаправляем на главную страницу...</div></div></div>&nbsp';
$error = 2;
			}
			$msg .= '<meta http-equiv="refresh" content="2; URL= index.php">';
		}//error 0
	}//email norm
}//enter
if($error != 2)
{
	$tpl->set('{CONTENT}','
			<form method="post" action=""><center>
			'.$msg.'<br>
		<table align="center" class="userinfo_table">
<tr><td class="table_user_name"><center>Введите Email:</center></td></tr>
<tr><td class="table_user_input"><input name="email" type="text" class="textbox"></td></tr>
			</table>
			<br>
			<input name="enter" class="input" type="submit" value="Выслать код"></center><br>
			</form>
');
}
else
{
	$tpl->set('{CONTENT}','
			<form method="post" action=""><center>
			'.$msg.' ');
}
}
$db->close ();
?>
