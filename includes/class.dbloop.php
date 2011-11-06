<?php
class DBLoop implements Iterator, Countable
    {
        private $position;
        private $className;
        private $extraColumns;
        private $result;
        private $db;
        private $DBobj;

        public function __construct($class_name, $sql = null, $extra_columns = array())
        {
            $this->position     = 0;
            $this->className    = $class_name;
            $this->extraColumns = $extra_columns;

            // Make sure the class exists before we instantiate it...
            if(!class_exists($class_name))
                return;

            $DBobj = new $class_name;

            // Also, it needs to be a subclass of DBObject...
            if(!is_subclass_of($DBobj, 'DBObject'))
                return;

            if(is_null($sql))
                $sql = "SELECT * FROM {$DBobj->tableName}";

            $db = Database::getInstance();
            $this->result = $db->query($sql);
        }

        public function rewind()
        {
            $this->position = 0;
        }

        public function current()
        {
            $this->result = $db->query("SELECT * FROM {$DBobj->tableName} LIMIT 1, {$this->position}");
            
            $row = $db->getRow($this->result);
            if($row === false)
                return false;

            $o = new $this->className;
            $o->load($row);

            foreach($this->extraColumns as $c)
            {
                $o->addColumn($c);
                $o->$c = isset($row[$c]) ? $row[$c] : null;
            }

            return $o;
        }

        public function key()
        {
            return $this->position;
        }

        public function next()
        {
            $this->position++;
        }

        public function valid()
        {
            if($this->position < $db->numRows($this->result))
                return $db->query("SELECT * FROM {$DBobj->tableName} LIMIT 1, {$this->position}");
            else
                return false;
        }

        public function count()
        {
            return $db->numRows($this->result);
        }
    }
?>
