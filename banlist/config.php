<?
    include ("../../classes/init.php"); 
    $db = mysql_connect(MySQL_HOSTNAME, MySQL_USER, MySQL_PASSWORD);
	if(!$db)
	{
	    return MySQL_HOSTNAME;
        return 'Нет подключения к базе данных, обратитесь к Администрации!';
		exit();	
	}
	mysql_select_db(MySQL_DB, $db);
	mysql_set_charset('utf8');
	date_default_timezone_set('Europe/Minsk');
	class ban
	{
		private $count = '20';
		private $countsearch = '10000';		
		private $table = 'banlisted';
		function ban_list()
		{
			$page = intval($_GET['page']);
			$query = mysql_query("SELECT * FROM `$this->table` WHERE `Show` = '1'");
			$bans = mysql_num_rows($query);
        	if($bans == 0)
        	{
        		return 'Баны не найдены!';
        		mysql_close();
        		exit;
        	}
			$total = intval(($bans - 1) / $this->count) + 1;
			if( empty( $page ) or $page < 0) $page = 1;
			if( $page > $total ) $page = $total;
			if($total > 5) $total = 5;
			$start = $page * $this->count - $this->count;
			$sql = "SELECT * FROM `".$this->table."` WHERE `Show` = '1' ORDER BY banID DESC LIMIT ".$start.",".$this->count."";
			$mysql = mysql_query($sql) or die("ERROR ".mysql_errno()." ".mysql_error()."<br /> Запрос: ".$sql."");
			$r = mysql_fetch_array($mysql);
			$numb = intval($page * $this->count) - $this->count;
			do
			{
				$numb ++;
//				$date = preg_replace("/^([0-9]{1,3000})-([0-9]{1,12})-([0-9]{1,31})$/", "\\3.\\2.\\1", $r['ban_date']);
				$text.= '
					<tr class="table_cont" align="center">
                    	<td>'.$numb.'</td>
                        <td>'.$r['Nick'].' ['.$r['ID'].']</td>
                        <td>'.$r['IP'].'</td>
			<td>'.date('дата: d.m.Y <br>время:</br> H.i.s', $r['Bantime']).'</td>
                        <td>'.$r['Zabanil'].'</td>
                        <td>'.$r['Days'].'</td>
                        <td>'.$r['Reason'].'</td>
                    </tr>
				';
			}
			while($r = mysql_fetch_array($mysql));
			return $text;
		}
		function ban_listsearch()
		{
		    
$foot = '
                <center>
                	<br>
                	<b>Введите ник/IP адрес для поиска</b><br>
                	<form method="post" action="?search=1">
                	<input type="text" required="1" name="username">	<input type="submit" value="поиск">
                	<br>Вид поиска:
                	<input type="radio" name="types" required="1" value="1">Ник
                	<input type="radio" name="types" value="2"> IP
                	<input type="radio" name="types" value="3"> ID аккаунта
                	</form>
                    <form action="?">
                    <input class="btn" type="submit" name="" value="Назад">
                        </form><br>
                    
                </center>
                      </div>
                    <div class="footer">
                        <div class="socialBlock">
                        <div class="blockFooter vk">
                          <a href="https://vk.com/gameextream" target="_blank"><img class="imgFooter" src="/img/vk.png"></a>
                          <a href="https://vk.com/gameextream" target="_blank"><p class="pBlock">Подпишись на нас ВКонтакте</p></a>
                        </div>
                        <div class="blockFooter youtube">
                    
                          <a href="https://www.youtube.com/gameextream-rpg" target="_blank"><img class="imgFooter" src="/img/youtube.png"></a>
                          <a href="https://www.youtube.com/gameextream-rpg" target="_blank"><p class="pBlock">Подпишись на YouTube канал</p></a>
                        </div>
                        <div class="created">
                          <p>Created by <a href="http://gameextream.ru/forum/index.php?/user/149-denisqin/" target="_blank">denisqin</a></p>
                        </div>
                        </div>
                      </div>
                      </body>
                    </html>
            ';		    
		    
        	if($_POST['username'] == "")
        	{
                return '<div class="cont_1">
                        <div class="cont_2">
                            <table width="100%">
                                <tr class="table_top" align="center">
                                    <center>
                                        <b>Вы ничего не ввели в поле для поиска!</b>
                                    </center>
                                </tr>
                            </table>
                        </div>
                    </div>'.$foot;
        		mysql_close();
        		exit;
        	}
        	if((strlen($_POST['username']) < 6) and ($_POST['types'] == "1" or $_POST['types'] == "2") )
        	{
               return '<div class="cont_1">
                        <div class="cont_2">
                            <table width="100%">
                                <tr class="table_top" align="center">
                                    <center>
                                        <b>Длина запроса не может быть менее 6 символов!</b>
                                    </center>
                                </tr>
                            </table>
                        </div>
                    </div>'.$foot;
        		mysql_close();
        		exit;        	    
        	}
        	$page = intval($_GET['page']);
			if($_POST['types'] == "1") $query = mysql_query("SELECT * FROM `$this->table` WHERE (`Nick` like  '".$_POST["username"]."%') and `Show` = '1'");
			if($_POST['types'] == "2") $query = mysql_query("SELECT * FROM `$this->table` WHERE (`IP` like '".$_POST["username"]."%') and `Show` = '1'");
			if($_POST['types'] == "3") $query = mysql_query("SELECT * FROM `$this->table` WHERE (`ID` = '".$_POST["username"]."') and `Show` = '1'");
			$bans = mysql_num_rows($query);
			if($bans == 0)
			{
                return '<div class="cont_1">
                        <div class="cont_2">
                            <table width="100%">
                                <tr class="table_top" align="center">
                                    <center>
                                        <b>Банов по запросу '.$_POST["username"].' не найдено</b>
                                    </center>
                                </tr>
                            </table>
                        </div>
                    </div>'.$foot;
				mysql_close();
				exit;
			}
			$total = intval(($bans - 1) / $this->countsearch) + 1;
			if( empty( $page ) or $page < 0) $page = 1;
			if( $page > $total ) $page = $total;
			if($total > 5) $total = 5;
			$start = $page * $this->countsearch - $this->countsearch;

if($_POST['types'] == "1") $sql = "SELECT * FROM `$this->table` WHERE `Nick` like '".$_POST["username"]."%' and `Show` = '1' ORDER BY banID DESC LIMIT " .$start. "," .$this->countsearch. " ";
if($_POST['types'] == "2") $sql = "SELECT * FROM `$this->table` WHERE `IP` like '".$_POST["username"]."%' and `Show` = '1' ORDER BY banID DESC LIMIT " .$start. "," .$this->countsearch. " ";
if($_POST['types'] == "3") $sql = "SELECT * FROM `$this->table` WHERE `ID` = '".$_POST["username"]."' and `Show` = '1' ORDER BY banID DESC LIMIT " .$start. "," .$this->countsearch. " ";


			$mysql = mysql_query($sql) or die("ERROR ".mysql_errno()." ".mysql_error()."<br /> Запрос: ".$sql."");
			$r = mysql_fetch_array($mysql);
			$numb = intval($page * $this->countsearch) - $this->countsearch;
	        $nachalo = '
                <div class="cont_1">
                <div class="cont_2">
                Найдено: '.$bans.' бан(ов).
                <table width="100%">
                <tr class="table_top" align="center">
                    <td width="50">№</td>
                    <td width="150">Ник [ID аккаунта]</td>
                    <td width="100">IP</td>
                    <td width="200">Дата и время</td>
                    <td width="100">Забанил</td>
                    <td width="20">Дни</td>
                    <td>Причина</td>
                </tr>
             ';			
             $center = '';
             $konec = '                
                </table>
                </div>
                </div>
                ';
			do
			{
			    
    			$numb ++;
    			$center .= '
    				<tr class="table_cont" align="center">
                    	<td>'.$numb.'</td>
    		
                        <td>'.$r['Nick'].' ['.$r['ID'].']</td>
                        <td>'.$r['IP'].'</td>
	                	<td>'.date('дата: d.m.Y <br>время:</br> H.i.s', $r['Bantime']).'</td>
                        <td>'.$r['Zabanil'].'</td>
                        <td>'.$r['Days'].'</td>
                        <td>'.$r['Reason'].'</td>
                    </tr>
    			';
			}
			while($r = mysql_fetch_array($mysql));
			return $nachalo.=$center.=$konec.=$foot;
		}		
		function count_ban()
		{
			$sql = "SELECT * FROM `".$this->table."` WHERE `Show` = '1'";
			$mysql = mysql_query($sql) or die("ERROR ".mysql_errno()." ".mysql_error()."<br /> Запрос: ".$sql."");
			$result = mysql_num_rows($mysql);
			return 'Всего найдено: '.$result.' бана(ов).';
		}
		function page()
		{
			$sql = "SELECT * FROM `".$this->table."` WHERE `Show` = '1'";
			$mysql = mysql_query($sql);
			$bans = mysql_num_rows($mysql);
			$page = intval(($bans - 1) / $this->count) + 1;
			$pagenow = intval($_GET['page']);
			if($pagenow < 1) $pagenow = 1;
			$pagetext = '';
            /////
            if($page > 5)
            {
            	if($pagenow >=4) $pagetext .= '<div class="page_1"><a href="banlist.php?page=1">В начало</a></div>';
            	if($pagenow == $page) $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow - 4).'">'.($pagenow - 4).'</a></div>';
            	if($pagenow >= $page-1) $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow - 3).'">'.($pagenow - 	3).'</a></div>';
            	if($pagenow > 2) $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow - 2).'">'.($pagenow - 2).'</a></div>';
            	if($pagenow > 1)$pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow - 1).'">'.($pagenow - 1).'</a></div>';
            	$pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow).'">'.($pagenow).'</a></div>';
            	if($pagenow+1 <= $page) $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow + 1).'">'.($pagenow + 	1).'</a></div>';
            	if($pagenow+2 <= $page) $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow + 2).'">'.($pagenow + 	2).'</a></div>';			
            	if($pagenow <= 2)$pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow + 3).'">'.($pagenow + 3).'</a></div>';
            	if($pagenow == 1)$pagetext .= '<div class="page_1"><a href="banlist.php?page='.($pagenow + 4).'">'.($pagenow + 4).'</a>			</div>';
            	if($pagenow < $page-2) $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($page).'">В конец</a></div>';
            }
            else
            {
            	for($i = 0; $i < $page; $i++)
            	{
            	    $pagetext .= '<div class="page_1"><a href="banlist.php?page='.($i + 1).'">'.($i + 1).'</a></div>';
            	}
            }
            return $pagetext;
        /////
		}
		function disconect()
		{
			mysql_close();	
		}
	}
?>
