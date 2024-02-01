<?php
    class Index extends Controller{

        //public $Index;

        public function __construct(){
            //$this->Index = $this->model('Indexm');
            $this->database = new Database();
        }
        
        public function index(){

            $query = "SELECT * FROM User";
            $query = $this->database->query($query);
            echo json_encode($query);
            return;
            $data = [
            ];

            $this->view('templates/header', $data);
            $this->view('index/index', $data);
            $this->view('templates/footer', $data);
        }
    }
?>