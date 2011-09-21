<?php
	class Admin
	{
		private static $me;
		
		public function __construct()
        {
            
        }

        public static function getAdmin()
        {
            if(is_null(self::$me))
            {
                self::$me = new Admin();
            }
            return self::$me;
        }
	
	}
?>
