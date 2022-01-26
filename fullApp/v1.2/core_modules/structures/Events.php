<?php

    class Events {
        public const BEFORE_ROUTING = 0;
        public const ROUTING = 1;
        public const AFTER_ROUTING = 2;

        private array $events;

        public function __construct()
        {
            $this->events = array(self::BEFORE_ROUTING => [], self::ROUTING => [], self::AFTER_ROUTING => []);
        }

        public function add(int $event, $callback)
        {
            if (isset($this->events[$event])) {
                array_push($this->events[$event], $callback);
            }
        }

        public function run(int $event)
        {
            if (isset($this->events[$event])) {
                for ($i=0; $i < count($this->events[$event]); $i++) { 
                    $this->events[$event][$i]();
                }
            }
        }
    }


?>