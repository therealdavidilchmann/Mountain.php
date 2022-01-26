<?php

    class CMD {
        public const COMMAND = 0;

        private $argv;

        public function __construct($argv)
        {
            $this->argv = $argv;
        }

        public function handle()
        {
            switch ($this->argv[0]) {
                case 'controller':
                    $name = $this->argv[1];
                    $controller = fopen("./controllers/" . $name . "Controller.php", "w");
                    fwrite($controller, "<?php\n\n\tclass $name implements CRUD {\n\t\t// Show content\n\t\tpublic function index() {\n\n\t\t}\n\n\t\t// Edit content\n\t\tpublic function update() {\n\n\t\t}\n\n\t\t// Delete content\n\t\tpublic function delete() {\n\n\t\t}\n\n\t\t// Delete content\n\t\tpublic function create() {\n\n\t\t}\n\n\t}\n\n?>");
                    break;
                default:
                    break;
            }
        }
    }

?>