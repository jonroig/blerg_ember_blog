<?



class mysqlConnectionThing
{
	public static $mysql_link = null;

	static public function returnConnection()
	{
		if (self::$mysql_link == null)
		{
			$user = 'root'; //"smersh_jon";
			$password = ''; // "fuckass";
			$db = 'jonroigdotcom'; //"jonroigdata";
			$server =  '127.0.0.1'; // 'db.jonroig.com';
			self::$mysql_link = mysql_connect ($server, $user, $password);
			if (! self::$mysql_link)
				{
				die ("Couldn't connect to mySQL server");
				}
			if (!mysql_select_db ($db, self::$mysql_link) )
				{
				die ("Couldn't open $db: ".mysql_error() );
				}
		}

		return self::$mysql_link;
	}
}

?>