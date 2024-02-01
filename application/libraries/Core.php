<?php

    /*  
        Mapaear la url ingresada en el navegador
        1. Controllador
        2. Método
        3. Parámetro

        Ejemplo: /articulos/actualizar/4
    */

    class Core{
        protected $actualController = 'Index';
        protected $actualMethod = 'index';
        protected $parameters = [];


        //Constructor
        public function __construct(){
            //print_r($this->getUrl());
            $url = $this->getUrl();
            
            //Se verifica si el archivo del controlador existe en la carpeta
            if(isset($url[0]) && file_exists('../application/controllers/' . ucwords($url[0]) . '.php' )){
                //Si exiete se setea como controlador por defecto
                $this->actualController = ucwords($url[0]);

                //unset del indice 0
                unset($url[0]);
            }

            //requerir el controlador
            require_once '../application/controllers/' . $this->actualController . '.php';
            $this->actualController =  new $this->actualController;

            //Verificar la segunda parte de la url: método
            if( isset($url[1]) && method_exists($this->actualController, $url[1])){
                //chequeamos el metodo
                $this->actualMethod = $url[1];

                //unset del indice 1
                unset($url[1]);
                
            }

            //echo $this->actualMethod;

            //Obtener los parametros
            $this->parameters = $url ? array_values($url) : [];

            //Llamar call back con parametros array
            call_user_func_array([$this->actualController, $this->actualMethod], $this->parameters); 
            
        }

        public function getUrl(){
            //echo $_GET['url'];
            if(isset($_GET['url'])){
                $url = rtrim($_GET['url'], '/');
                $url = filter_var($url, FILTER_SANITIZE_URL);
                $url = explode('/', $url);
                return $url;
            }
        }

    }

