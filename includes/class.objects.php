<?php
    // Stick your DBObject subclasses in here (to help keep things tidy).

    class User extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('nid', 'username', 'password', 'level'), $id);
        }
    }
    class BlogPost extends DBObject
    {
    	public function __construct($id = null)
    	{
    		parent::__construct('blog', $id);
    	}
    }
    class Page extends DBObject
    {
        public function __construct($id = null)
        {
            parent::_construct('pages', $id);
        }
    }
?>
