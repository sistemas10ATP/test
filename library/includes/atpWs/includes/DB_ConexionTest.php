<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DB_Conexion
 *
 * @author efrain.campa
 */
class DB_ConexionTest extends PDO {

    private $tipo_de_base = 'sqlsrv';
    private $host = 'SQL03\DB03';
    private $nombre_de_base = 'AXTEST';
    private $usuario = 'sa';
    private $contrasena = 'avanceytec';
    private $db = '';

    public function __construct() {
        //Sobreescribo el método constructor de la clase PDO.
        try {
            $dbh = parent::__construct($this->tipo_de_base . ':Server=' . $this->host . ';Database=' . $this->nombre_de_base, $this->usuario, $this->contrasena);
            return $dbh;
        } catch (PDOException $e) {
            echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
            exit;
        }
    }

}
