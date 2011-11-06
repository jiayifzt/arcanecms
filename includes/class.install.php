<?php
	class Install
	{
		// Singleton object. Leave $me alone.
		private static $me;
		private $footer = '<br /><div class="center">ArcaneCMS - Because Joomla is too mainstream</div>';
		public function __construct()
		{
		}
		
		// Get Singleton object
        public static function getInstance()
        {
            if(is_null(self::$me))
                self::$me = new Install();
            return self::$me;
        }
		public function step1()
		{
			$content = '<div class="center">
			<span class="title">Welcome to ArcaneCMS!</span></div>
			<br />
			<div class="center"><a href="'.ARCANE_SITE_URL.'/install/step2" class="button">Begin</a>
			</div>';
			
			$content .= $this->footer;
			
			$ThemeEngine = ThemeEngine::getInstance();
			$ThemeEngine->set_theme('arcane');
			$ThemeEngine->add_tag('content', $content, 0, false);
			$ThemeEngine->go('ArcaneCMS Installer - Step 1');
			include DOC_ROOT.DS.'themes'.DS.'arcane'.DS.'install.php';
		
		}
		public function step2()
		{
			$Error = Error::getInstance();
			$continue = true;
			$content = '<div class="center">
			<span class="title">Checking Requirements</span></div>
			<br />';
			$version = explode('.', PHP_VERSION);
			if($version[0]>= '5' && $version[1]>= '3')
			{
				$content .= '<div class="check"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/check.png" alt="OK" width="24px" height="24px" /> <span class="title">PHP 5.3</span></div>';
			} else {
				$Error->add('PHP','PHP 5.2.x and lower are NOT supported!<br />');
				$continue = false;
				$content .= '<div class="check"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/cross.png" alt="NO" width="24px" height="24px" /> <span class="title">PHP 5.3</span></div>';
			}
			if(extension_loaded('PDO')) {
				$content .= '<div class="check"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/check.png" alt="OK" width="24px" height="24px" /> <span class="title">PDO</span></div>';
			} else {
				$Error->add('pdo','PHP Data Objects (PDO) module not installed/enabled!<br />');
				$continue = false;
				$content .= '<div class="check"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/cross.png" alt="NO" width="24px" height="24px" /> <span class="title">PDO</span></div>';
			}
			$content .= '<ul>';
			if(extension_loaded('pdo_sqlite'))
			{
				$content .= '<li><div class="subcheck"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/check.png" alt="OK" width="18px" height="18px" /> <span class="title">PDO_sqlite</span></div></li>';
			} else {
				$content .= '<li><div class="subcheck"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/cross.png" alt="NO" width="18px" height="18px" /> <span class="title">PDO_sqlite</span></div></li>';
			}
			if(extension_loaded('pdo_mysql'))
			{
				$content .= '<li><div class="subcheck"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/check.png" alt="OK" width="18px" height="18px" /> <span class="title">PDO_mysql</span></div></li>';
			} else {
				$content .= '<li><div class="subcheck"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/cross.png" alt="NO" width="18px" height="18px" /> <span class="title">PDO_mysql</span></div></li>';
			}
			if(!extension_loaded('pdo_sqlite') && !extension_loaded('pdo_mysql')) {
				$Error->add('dbdrivers','No supported PDO database drivers available!<br />');
				$continue = false;
			}
			$content .= '</ul>';
			if(is_writable(dirname(DOC_ROOT.DS.'includes'.DS.'class.config.php'))) {
				$content .= '<div class="check"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/check.png" alt="OK" width="24px" height="24px" /> <span class="title">Config Writable</span></div>';
			} else {
				$Error->add('config','Config file is not writeable. Please chmod to 777!<br />');
				$continue = false;
				$content .= '<div class="check"><img src="'.ARCANE_SITE_URL.'/themes/arcane/images/cross.png" alt="NO" width="24px" height="24px" /> <span class="title">Config Writable</span></div>';
			}
			
			if($continue) $content .= '<br /><div class="center"><a href="'.ARCANE_SITE_URL.'/install/step1" class="button">'.htmlentities("<< Prev").'</a> <a href="'.ARCANE_SITE_URL.'/install/step3" class="button">'.htmlentities("Next >>").'</a></div>';
			else $content .= '<br /><div class="center"><a href="'.ARCANE_SITE_URL.'/install/step1" class="button">'.htmlentities("<< Prev").'</a> <span class="disabledbutton">'.htmlentities("Next >>").'</span></div>';
			$content .= $this->footer;
			$ThemeEngine = ThemeEngine::getInstance();
			$ThemeEngine->set_theme('arcane');
			$ThemeEngine->add_tag('errors', $Error, 0, false);
			$ThemeEngine->add_tag('content', $content, 0, false);
			$ThemeEngine->go('ArcaneCMS Installer - Step 2');
			include DOC_ROOT.DS.'themes'.DS.'arcane'.DS.'install.php';
		
		}
	}
?>
