<?php

    //Este contrlador se encarga de cargar los modelos y las vistas
    class Controller {
        //Cargar modelo
        public function model($modelo){
            //Carga del modelo
            require_once '../application/models/' . $modelo . '.php';
            //echo var_dump(new $modelo);
            //instanciar al modelo
            return new $modelo();

        }
        
        public function view($vista, $data = []){
            //Checar si el archivo de la vista existe
            if( file_exists('../application/views/' . $vista . '.php')){
                require_once '../application/views/' . $vista . '.php';
            }else{
                //si el archivo de la vista no existe
                die('La vista no existe');
            }
            
        } 

    }