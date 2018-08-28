<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DB_Conexion
 *
 * @author Jack
 * @author Victor Dominguez
 */
class DB_Conexion_Solaris extends PDO {

    private $tipo_de_base = 'sqlsrv';
    //private $tipo_de_base   = 'SQL Server';
    private $host = 'SQL01\AXR3';
    private $nombre_de_base = 'SolarisProductivo';
    private $usuario = 'sa';
    private $contrasena = 'avanceytec';
    private $db = '';

    public function __construct() {
        //Sobreescribo el método constructor de la clase PDO.
        try {
            $dbh2 = parent::__construct($this->tipo_de_base . ':Server=' . $this->host . ';Database=' . $this->nombre_de_base, $this->usuario, $this->contrasena);
            return $dbh2;
        } catch (PDOException $e) {
            echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
            exit;
        }
    }

}

class DB_Conexion extends PDO {

    private $tipo_de_base = 'sqlsrv';
    //private $tipo_de_base   = 'SQL Server';
    private $host = 'SQL01\AXR3';
    private $nombre_de_base = 'PRODR3';
    private $usuario = 'sa';
    private $contrasena = 'avanceytec';
    private $db = '';

    public function __construct() {
        //Sobreescribo el método constructor de la clase PDO.
        try {
            $dbh2 = parent::__construct($this->tipo_de_base . ':Server=' . $this->host . ';Database=' . $this->nombre_de_base, $this->usuario, $this->contrasena);
            return $dbh2;
        } catch (PDOException $e) {
            echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
            exit;
        }
    }

}
