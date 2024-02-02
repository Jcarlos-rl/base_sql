<?php
    class Index extends Controller{

        //public $Index;

        public function __construct(){
            //$this->Index = $this->model('Indexm');
            $this->database = new Database();
        }
        
        public function index(){

            $query = "SELECT * FROM Users";
            $result = $this->database->query($query);

            $data = $this->database->fetchArray($result);
            $this->database->close();

            var_dump($data);
            return;
            $data = [
            ];

            $this->view('templates/header', $data);
            $this->view('index/index', $data);
            $this->view('templates/footer', $data);
        }
    }
?>