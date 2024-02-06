<?php
    define('app_path', dirname(dirname(__FILE__)));     //Ruta de la app
    define('BASE_PATH', realpath(dirname(__FILE__) . '/../..').'/'); //BASE_PATH del proyecto
    define('base_url', 'http://localhost/sql/'); //Ruta de la url

    define('site_name', 'Base Framework');       //Nombre del sitio

    //Configuración de acceso a la base de datos MongoDB
    //define ('DB_HOST', 'DESKTOP-KDR5KU0');
    define ('DB_HOST', '192.168.3.60,1433');
    define ('DB_USER', 'sa');
    define ('DB_PASSWORD', '181213');
    define ('DB_NAME', 'prueba');


    //Zona horaria
    date_default_timezone_set ('America/Mexico_City');

    session_start();//configuracion de sesiones
?>