<?php

    class ReplaceData {
        public $data;
        public $safeData;
        public $loopData;

        public function __construct($data, $safeData, $loopData)
        {
            $this->data = $data;
            $this->safeData = $safeData;
            $this->loopData = $loopData;
        }
    }

?>