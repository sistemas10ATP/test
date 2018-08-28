<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComisionesModel
 *
 * @author sistemas10
 */
class Application_Model_ComisionesModel {
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
    public function getArrayDataListToJson($query,$params =  array()) {
        try{
            $q = $this->_adapter->prepare($query);
            $q->execute($params);
            $result = $q->fetchAll();
            $json=  json_encode($result);
            return str_replace("'",'', $json);
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
    /**
     * 
     * @param type $query
     * @param type $params
     * @return type
     */
    public function getArrayDataList($query,$params =  array()) {
        try{
            $q = $this->_adapter->prepare($query);
            $q->execute($params);
            return  $q->fetchAll();
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
    /**
     * 
     * @param type $nombre
     * @param type $direccion
     * @param type $telefono
     * @param type $estado
     * @param type $ciudad
     * @return boolean
     */
    public function getReporteTbl($array = array()) {
        $q = $this->_adapter->prepare(COMISIONISTA_CLIENTE_REPORTE1);
        $q->execute();
        $q = $this->_adapter->prepare(COMISIONISTA_CLIENTE_REPORTE2);
        $q->execute();
        $result= $this->getArrayDataList(COMISIONISTA_CLIENTE_REPORTE,$array);
        $q = $this->_adapter->prepare(COMISIONISTA_CLIENTE_REPORTE3);
        $q->execute();
        return $result;
    }
    public function newComisionista($nombre,$direccion,$telefono,$estado,$ciudad) {
        try{
            $query = $this->_adapter->prepare(NEW_COMISIONISTA);
            $query->bindParam(1,$nombre);
            $query->bindParam(2,$direccion);
            $query->bindParam(3,$telefono);
            $query->bindParam(4,$estado);
            $query->bindParam(5,$ciudad);
            $res=false;
            if($query->execute()){
             $res=true;   
            }
            return $res;
        }catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
    /**
     * 
     * @param type $mes
     * @param type $articulo
     * @param type $comisionistaData
     * @param type $cliente
     * @param type $comision
     * @return boolean
     */
    public function newComisionistaCliente($mes,$articulo,$comisionistaData, $cliente, $comision){
            try{
            $query = $this->_adapter->prepare(NEW_COMISIONISTA_CLIENTE);
            $query->bindParam(1,$mes);
            $query->bindParam(2,$articulo);
            $query->bindParam(3,$comisionistaData);
            $query->bindParam(4,$cliente);
            $query->bindParam(5,$comision);
            $res=false;
            if($query->execute()){
             $res=true;   
            }
            return $res;
        }catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }    
    }
    /**
     * 
     * @param type $idRow
     * @return boolean
     */
    public function deleteComisionistaCliente($idRow) {
        try{
            $query = $this->_adapter->prepare(DELETE_COMISIONISTA_CLIENTE);
            $query->bindParam(1,$idRow);
            $res=false;
            if($query->execute()){
             $res=true;   
            }
            return $res;
        }catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }    
    }
}
