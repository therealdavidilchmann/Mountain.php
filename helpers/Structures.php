<?php

    class ReplaceData {
        public $data;
        public $safeData;
        public $loopData;
        public $meta;

        public function __construct($data, $safeData, $loopData, $meta)
        {
            $this->data = $data;
            $this->safeData = $safeData;
            $this->loopData = $loopData;
            $this->meta = $meta;
        }
    }

?>