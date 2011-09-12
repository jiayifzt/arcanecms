<?php
    class Loop
    {
        private $index;
        private $elements;
        private $numElements;

        public function __construct()
        {
            $this->index       = 0;
            $this->elements    = func_get_args();
            $this->numElements = func_num_args();
        }

        public function __tostring()
        {
            return (string) $this->get();
        }

        public function get()
        {
            if($this->numElements == 0) return null;

            $val = $this->elements[$this->index];

            if(++$this->index >= $this->numElements)
                $this->index = 0;

            return $val;
        }

        public function rand()
        {
            return $this->elements[array_rand($this->elements)];
        }
    }

    // Example:
    // $db = Database::getDatabase();
    // $color = new Loop('white', 'black');
    //
    // echo "<tr color='$color'/>";
    // echo "<tr color='$color'/>";
    // echo "<tr color='$color'/>";
    //
    // Or
    //
    // while($row = $db->getRow($result))
    //      echo "<tr color'$color'>the row colors will alternate</tr>";
?>
