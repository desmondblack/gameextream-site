This is my site: https://gameextream.polirek.by
Almost all php functions writed by mine. Examples:

Take a name of the house. Used when you click on house icon on url: https://gameextream.polirek.by/ucp/pages/maps/
```PHP
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
  ```
  
