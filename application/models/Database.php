
<?php

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;

    private $connectionOptions;
    private $conn;

    public function __construct(){
        $this->connectionOptions = array(
            'Database' => $this->database,
            'Uid' => $this->user,
            'PWD' => $this->password
        );

        $this->connect();
    }

    private function connect(){
        $this->conn = sqlsrv_connect($this->host, $this->connectionOptions);

        if(!$this->conn){
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public function query($sql)
    {
        $result = sqlsrv_query($this->conn, $sql);

        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return $result;
    }
}
