<html>
	<head>
   <title>Авторизация</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
<body>
<?
$stop=0;
$capa = $_SESSION['captch'];
if($capa == 1) 
{
    $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';
}    
if(isset($_POST['enter']) == "enter")
{
	$nick = $_POST['nick'];
	$password = $_POST['password'];
	if($nick == "" && $password == "")
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Заполните все поля!</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';		
		$stop=1;
	}
	if(($nick == "") && $stop == 0)
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы не ввели ник!</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';		
		$stop = 1;
	}
	if($password == "" && $stop == 0)
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы не ввели пароль!</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';		
		$stop = 1;
	}
	if((strlen($password) < 6 || strlen($password) > 32) and $stop == 0)
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Длина пароля должна быть от 6 до 32 символов!</div></div></div>&nbsp';
		$captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';
		$stop = 1;
	}
	if($stop == 0)
	{
		$forbidden = array("'" => true, '/' => true, '\\' => true, '"' => true, '*' => true, ';' => true, '%' => true);
		$len = strlen($password);
		for($i=0;$i<$len;$i++)
		{
			if( $forbidden[ $password[$i] ] == true )
			{
				$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">В пароле имеются запрещенные символы!</div></div></div>&nbsp';
				$captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';
				$stop = 1;
			}
		}
	}
	if($capa == "1") 
	{
        if(isset($_POST['g-recaptcha-response']))
    	{
            $captcha=$_POST['g-recaptcha-response'];
    	}
        if(!$captcha && $stop == 0)
    	{
    		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы не ввели капчу</div></div></div>&nbsp';
            $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';    		
    	    $stop = 1;
        }
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify secret=6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
        if($response.success==false && $stop ==0)
        {
    		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Вы не прошли проверку на капчу</div></div></div>&nbsp';
            $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';    		
                		$stop = 1;
     	}
	}
	else
	{
	    $_SESSION['captch'] = 1; 
	}
	if($user_class->IsLockNick($nick) && $stop ==0)
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Этот аккаунт заблокирован.</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';		
		$stop = 1;
	}
	$proverka = $user_class->IsNoAdmin($nick);
	if($proverka != 1 && $stop ==0)
	{
		if($proverka == 2) $error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Неизвестный IP адрес. Свяжитесь с администрацией сервера, обладающей достаточными полномочиями</div></div></div>&nbsp';
		else $error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Этот аккаунт заблокирован. Свяжитесь с администрацией сервера</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';		
		$stop = 1;
	}
	if((($nick = $user_class->IsValidNick($nick)) == false)&& $stop ==0)
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">В вашем нике найдены запрещенные символы.</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';		
		$stop = 1;
	}
	if($user_class->IsExist($nick) == 0 && $stop ==0)
	{
		$error = '<div class="box_top_error"><div class="box_top_text_error"> <div class="eTitle">Пользователя с таким ником не существует!</div></div></div>&nbsp';
        $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';				
		$stop = 1;
	}
	if($stop == 0)
	{
		if(($user_class->Login($nick,md5($password)) == false) && $stop == 0)
		{
			$error = "<div class='box_top_error'><div class='box_top_text_error'> <div class='eTitle'>Внимание, вход в UCP не был произведен. Возможно, Вы ввели неверное имя пользователя или пароль.</div></div></div>&nbsp";
            $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';					
			$stop = 1;
		}
	}
	if($stop == 0)
	{
		$error .= "<div class='box_top_success'><div class='box_top_text_success'> <div class='eTitle'>Загрузка аккаунта...</div></div></div>&nbsp";
		if($_SESSION['adminlogged'] == -1) 
		{
		    $error .= '<meta http-equiv="refresh" content="2; URL= index.php?act=login2">';
		}    
		else 
		{
		    $error .= '<meta http-equiv="refresh" content="2; URL= index.php?page=login">';
            $captch ='<div class="g-recaptcha" data-sitekey="6LcR3AATAAAAAPwR6L9BGWmRvpEmBWYZLfgJF2C5"></div>';				
		}
		$stop = 2;
	}
}
if($stop != 2)
{
$tpl->set('{CONTENT-NAME}','Авторизация');
$tpl->set('{CONTENT}', '
'.$error.'
		<form method="post" action="">
		<table align="center"class="table_user">
<tr><td class="table_user_name">Ваш ник на сервере:</td>
<tr><td class="table_user_input"><input type="text" name="nick"></td></tr>
<tr><td class="table_user_name">Пароль:</td></tr>
<tr><td class="table_user_input"><input type="password" name="password"></td></tr>
<tr><td class="table_user_submit"><input class="submit" type="submit" name="enter" value="Войти"></td></tr>
<tr><td>
'.$captch.'
<br>
			<center><a href="index.php?act=change1"><font color="black"><u>Забыли пароль?</u></font></a></center>
</td></tr>
		</table>
		</p>
		</p>
		</form>
');
}
else
{
$tpl->set('{CONTENT-NAME}','Авторизация');
$tpl->set('{CONTENT}', '
'.$error.' ');
}
?>

	</body>
</html>
