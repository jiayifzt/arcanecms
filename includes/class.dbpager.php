<?php
    class DBPager extends Pager
    {
        private $itemClass;
        private $countSql;
        private $pageSql;

        public function __construct($itemClass, $countSql, $pageSql, $page, $per_page)
        {
            $this->itemClass = $itemClass;
            $this->countSql  = $countSql;
            $this->pageSql   = $pageSql;

            $db = Database::getInstance();
            $num_records = intval($db->getValue($countSql));
            print_r("Num records: $num_records\n");

            parent::__construct($page, $per_page, $num_records);
        }

        public function calculate()
        {
            parent::calculate();
            // load records .. see $this->firstRecord, $this->perPage
            $limitSql = sprintf(' LIMIT %s,%s', $this->firstRecord, $this->perPage);
            $stuff = DBObject::glob($this->itemClass, $this->pageSql . $limitSql);
            print_r("Stuff: $stuff");
            $this->records = array_values($stuff);
        }
        public function results()
        {
        	return $this->records;
        }
    }
?>
