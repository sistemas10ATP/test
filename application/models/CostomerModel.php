<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CostomerModel
 *
 * @author sistemas10
 */
class Application_Model_CostomerModel {
    
    public $db;
    public $_adapter;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }
    /**
     * regresa los datos despues de un query en un arreglo pasando un parametro
     * @param String $site 
     */
    public function getArrayData($query,$site) {
        try{
            $q = $this->_adapter->prepare($query);
            $q->bindParam(1,$site);
            $q->execute();
            $result = $q->fetchAll();
            $descuento['noresult'] = "No Results";
            if (!empty($result)) {
                $descuento=$result;
            }
            return $descuento;
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
    /**
     * inserta cuando se guarda un cliente
     */
    public function setKardexCliente($type){
       $query=$this->_adapter->query(ANSI_NULLS);
       $query=$this->_adapter->query(ANSI_WARNINGS);
       $query = $this->_adapter->prepare(INSERT_KARDEX);
       $query->bindParam(1,$_SESSION['userInax']);
       $query->bindParam(2,$_SESSION['nomuser']);
       $query->bindParam(3,$type);
       $query->execute();
    }
    /**
     * obtiene todos los paises 
     */
    public function getPais() {
        try{
            $query = $this->_adapter->prepare(GET_PAIS);
            $query->execute();
            $result = $query->fetchAll();
            $pais['noresult'] = "No Results";
            if (!empty($result)) {
                $pais=$result;
            }
            return $pais;
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
    
    /**
     * @param array $param arreglo con los datos necesarios para dar de alta a un cliente
     */
    public function setDataClient($param) {
        $ws= new Metodos();
        $result = $ws->crearCliente($param);
        return $result;       
    }
    /**
     * @param string $rfc verifica si el rfc ya existe en la base de datos
     * @return array regresa los datos del cliente;
     */
    public function isClientExist($rfc,$company){
        try{
            $query = $this->_adapter->prepare(VERIFICA_RFC);
            $query->bindParam(1,$rfc);
            $query->bindParam(2,$company);
            $query->execute();
            $result = $query->fetchAll();
            return $result;
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }       
    }
}
