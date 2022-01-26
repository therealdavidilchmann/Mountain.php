<?php

    class Kernel {
        private Events $events;
        private Application $application;

        public function __construct()
        {
            $this->events = new Events();
            $this->application = new Application();
        }

        public function on(int $event, $callback)
        {
            $this->events->add($event, $callback);
        }

        public function handle()
        {
            $this->application->run($this->events);
        }

        public function routes($path)
        {
            $this->application->registerRoutes($path);
        }
    }

    return new Kernel();

?>