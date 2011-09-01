<?php
	class HTTPError
	{
		// Singleton object. Leave $me alone.
		private static $me;

		public $HTTPerrors; // Array of HTTP errors

		private function __construct()
		{
			$this->HTTPerrors = array(	'400' => '<div class="errorpage"><h1><span class="title_color1">Error 400:</span> <span class="title_color2">Bad Request</span></h1><br />The URL you were trying to access was malformed and the server was unable to process it. If you believe this was in error, please contact us and we will check it out.</div>',
										'401' => '<div class="errorpage"><h1><span class="title_color1">Error 401:</span> <span class="title_color2">Authorization Required</span></h1><br />An authorized session was not found. Please try again. If you believe this was in error, please contact us and we will check it out.</div>',
										'403' => '<div class="errorpage"><h1><span class="title_color1">Error 403:</span> <span class="title_color2">Forbidden</span></h1><br />What you are doing is not allowed. Maybe we should call the cops. If you believe this was in error, please contact us and we will check it out.</div>',
										'404' => '<div class="errorpage"><h1><span class="title_color1">Error 404:</span> <span class="title_color2">File Not Found</span></h1><br />The requested file was not found on the server. It may have been moved or deleted. If you believe this was in error, please contact us and we will check it out. Also, contact the site from which you came to report a broken link.</div>',
										'500' => '<div class="errorpage"><h1><span class="title_color1">Error 500:</span> <span class="title_color2">Internal Server Error</span></h1><br />Something went wrong on our end. Probably something misconfigured. This is usually temporary. Please try again later. If you believe this was in error, please contact us and we will check it out.</div>'
									);
		}

		// Get Singleton object
		public static function getHTTPError()
		{
			if(is_null(self::$me))
				self::$me = new HTTPError();
			return self::$me;
		}
		public function trigger($statuscode)
		{
			if(array_key_exists($statuscode,$this->HTTPerrors)) {
				redirect(ARCANE_SITE_URL . '/error'.$statuscode);
			}
		}
		private function load($statuscode)
		{
			$ThemeEngine = ThemeEngine::getThemeEngine();
			$db = Database::getDatabase(true);

			$statuscode = $db->escape($statuscode);
			$result = $db->query("SELECT * FROM pages WHERE slug='$statuscode'");
			$row = $db->getRow($result);
			
			// Tell ThemeEngine to start buffering the page, set the page's title
			$ThemeEngine->go(ARCANE_SITE_NAME.' - Error '.$statuscode);
				
			if($row['data']==NULL) {
				echo $this->HTTPerrors[$statuscode];
			} else {
				echo $row['data'];
			}
		}
		public function load400()
		{
			$this->load('400');
		}
		public function load401()
		{
			$this->load('401');
		}
		public function load403()
		{
			$this->load('403');
		}
		public function load404()
		{
			$this->load('404');
		}
		public function load500()
		{
			$this->load('500');
		}
	}		
?>
