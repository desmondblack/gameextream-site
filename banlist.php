<?
$players = $db->super_query("SELECT COUNT(*) as count FROM `".TABLE_USERS."`");
$kolvo = $players['count'];
$count = 20;
$error = 0;
$tpl->set('{CONTENT-NAME}','Банлист');

$total = intval(($rows - 1) / $count) + 1;
if( empty( $page ) or $page < 0) $page = 1;
if( $page > $total ) $page = $total;
if($total > 5) $total = 5;
$start = $page * $count - $count;
$zapros = $db->query("SELECT * FROM `banlisted` ORDER BY banID DESC LIMIT ".$start.",".$count." ");
if(!zapros)
{
		$msg = '<div class="box_top_login"><div class="box_top_text_error"> <div class="eTitle">Банов не найдено!</div></div></div>&nbsp';
		$error = 1;
}


$numb = intval($page * $count) - $count;

//do
$nick = $zapros['Nick'];
$ip = $zapros['IP'];
$id = $zapros['ID'];
$zabanil = $zapros['Zabanil'];
$days = $zapros['Days'];
$reason = $zapros['Reason'];
$date = date('дата: d.m.Y <br>время:</br> H.i.s',$zapros['Bantime']);
while($zapros = $db->get_row())
{

	$numb ++;
	$msg.= '
<td style="padding:5px 20px;color:#777777;background:#fff;border-bottom:1px solid #ddd;width:0%;"><b><font color="#777777">'.$numb.'343</font></b></td>
<td style="padding:5px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777">'.$nick.' ['.$id.']GameExtream_Server</font></b></td>
<td style="padding:5px;color:#777777;background:#fff;border-bottom:1px solid #ddd;width:0%;"><b><font color="#777777">'.$ip.'255,255,255,255</font></b></td>
<td style="padding:5px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777">'.$date.'23.10.2015 в 14:34:65</font></b></td>
<td style="padding:5px;color:#777777;background:#fff;border-bottom:1px solid #ddd;width:0%;"><b><font color="#777777">'.$zabanil.'Anticheat</font></b></td>
<td style="padding:5px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777">'.$days.'60</font></b></td>
<td style="padding:5px;color:#777777;background:#fff;border-bottom:1px solid #ddd;width:0%;"><b><font color="#777777">'.$reason.'Нарушение правил Сервера</font></b></td>
					</tr><tr>
	';
}
$players = $db->super_query("SELECT COUNT(*) as count FROM `banlisted`");
$page = intval(($players['count'] - 1) / $count) + 1;
$pagenow = intval($_GET['page']);
if($pagenow < 1) $pagenow = 1;
if($page > 5)
{
	if($pagenow >=4) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page=1">в начало</a></div>';
	if($pagenow == $page) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow - 4).'">'.($pagenow - 4).'</a></div>';
	if($pagenow >= $page-1) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow - 3).'">'.($pagenow - 3).'</a></div>';
	if($pagenow > 2) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow - 2).'">'.($pagenow - 2).'</a></div>';
	if($pagenow > 1)$pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow - 1).'">'.($pagenow - 1).'</a></div>';
	$pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow).'">'.($pagenow).'</a></div>';
	if($pagenow+1 <= $page) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow + 1).'">'.($pagenow + 1).'</a></div>';
	if($pagenow+2 <= $page) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow + 2).'">'.($pagenow + 2).'</a></div>';
	if($pagenow <= 2)$pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow + 3).'">'.($pagenow + 3).'</a></div>';
	if($pagenow == 1)$pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($pagenow + 4).'">'.($pagenow + 4).'</a></div>';
	if($pagenow < $page-2) $pag .= '<div  class="page_1"><a href="index.php?act=banlist&page='.($page).'">в конец</a></div>';
}
else
{
	for($i = 0; $i < $page; $i++)
	{
		$pag .= '<div class="page_1"><a href="index.php?act=banlist&page='.($i + 1).'">'.($i + 1).'</a></div>';
	}
}

		$tpl->set('{CONTENT}','
			<div class= "box_content" style="padding:0px;border-bottom:0px;border:1px solid #dcdcdc;">
			<div style= "height: 44em; overflow: auto" >
			<table style= "margin-bottom:0px;" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tbody>
			<tr>
			<td style="padding:5px 55px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777"> IP</font></b></td>
			<td style="padding:5px 40px; color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777"> Дата</font></b></td>
			<td style="padding:5px; color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777">s</font></b></td>
			<td style="padding:5px 55px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777"> IP</font></b></td>
			<td style="padding:5px 40px; color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777"> Дата</font></b></td>
			<td style="padding:5px; color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777">s</font></b></td>
			<td style="padding:5px 55px;color:#777777;background:#fff;border-bottom:1px solid #ddd; width:0%;"><b><font color="#777777"> IP</font></b></td>
			</tr><tr>
			'.$msg.'
			</tr></tbody></table></div>
			'.$pag.'
		');
?>
