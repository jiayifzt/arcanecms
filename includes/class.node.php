<?php
	class Node
	{
        // Singleton object. Leave $me alone.
        private static $me;
		
		// Singleton constructor
        private function __construct()
        {
		
		}
		
		// Get Singleton object
        public static function getInstance()
        {
			if(is_null(self::$me))
				self::$me = new Node();
			return self::$me;
		}
		
		public function view($params)
		{
			$page_or_id = $params['id'];
			$db = Database::getInstance();
			$HTTPError = HTTPError::getInstance();
			$ThemeEngine = ThemeEngine::getInstance();
			if(is_int($page_or_id)) $type = 'id';
			else if(is_string($page_or_id)) $type = 'slug';
			else {
				$HTTPError->trigger('400');
				break;
			}
			$page_or_id = $db->escape($page_or_id);
			$result = $db->query("SELECT * FROM pages WHERE $type=$page_or_id");
			$row = $db->getRow($result);
			
			if($row['data']==null) {
				$HTTPError->trigger("404");
			} else {
				// Tell ThemeEngine to start buffering the page, set the page's title
				$ThemeEngine->go(ARCANE_SITE_NAME.' - '.ucwords($row['title']));
				echo $row['data'];
			}
		}
			
		public function index()
		{
			$db = Database::getInstance();
			$HTTPError = HTTPError::getInstance();
			$ThemeEngine = ThemeEngine::getInstance();
			$result = $db->query("SELECT * FROM pages WHERE slug='home'");
			$row = $db->getRow($result);
				
			$ThemeEngine->go(ARCANE_SITE_NAME.' - Home');
	
			if($row['data']==null) {
				echo 'Oh... there\'s nothing here. It looks like you forgot to add a page for home.';
			} else {
				echo $row['data'];
			}
		}
}
?>
