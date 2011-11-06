<?php
	class Blog
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
				self::$me = new Blog();
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
			$result = $db->query("SELECT * FROM blog WHERE $type=$page_or_id");
			$row = $db->getRow($result);
			
			if($row['data']==null) {
				$HTTPError->trigger("404");
			} else {
				// Tell ThemeEngine to start buffering the page, set the page's title
				$ThemeEngine->go(ARCANE_SITE_NAME.' - '.ucwords($row['title']));
				echo $row['data'];
			}
		}
		
		public function page($params)
		{
			$page = $params['id'];
			$pager = new DBPager("BlogPost", "SELECT COUNT(*) FROM blog WHERE status = 'published' ORDER BY pubDate", "SELECT * FROM blog WHERE status = 'approved' ORDER BY pubDate DESC", $page, 10);
			$pager->calculate();
			$ThemeEngine = ThemeEngine::getInstance();
			$ThemeEngine->go(ARCANE_SITE_NAME.' - Blog');
			if($page!='1') {
				echo '<p>You are viewing posts '.$pager->firstRecord.' through '.$pager->lastRecord.' of '.$pager->numRecords.' total.</p>';
			}
			$results = $pager->results();
			print_r($results);
			if(!empty($results)) {
				foreach($results as $r) { 
	    			echo '<div class="section">
	    			<div class="title"><span class="title_color1"><a href="{Permalink}">'.$r['title'].'</a></span>
	    			'.$r['data'].'
	    			</div>
	    			';
				}
			} else {
				echo 'There\'s nothing here! :\'(';
			}
		}
		
		public function rss()
		{
			$feed = RSS::getInstance();
			$db = Database::getInstance();
			$result = $db->query("SELECT * FROM blog ORDER BY id DESC LIMIT 10");
			$feed->loadRecordset($result, 'title', 'slug', 'data', 'pubDate');
			$feed->serve();
		}
}
?>
