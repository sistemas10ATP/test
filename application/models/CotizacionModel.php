<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Application_Model_CotizacionModel {
    
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
    public function getCargo($cotizacionID){
        $query = $this->_adapter->prepare(GET_CARGOS_COT);
        $query->bindParam(1,$cotizacionID);
        $query->execute();      
        $result=$query->fetchAll();
        return $result;
    }
    /*
     *obtiene la cabcera de la cotizacion 
     */
    public function getCabezeraCotizacion($param) {
        $query = $this->_adapter->prepare(GET_COTIZACION_CABEZERA);
        $query->bindParam(1,$param);
        $query->execute();      
        $result=$query->fetch();
        return $result;
    }
    /**
     * 
     * @param type $id
     * @return Array
     */
    public function getVendedor($id) {
        $query = $this->_adapter->prepare(GET_VENDEDOR);
        $query->bindParam(1,$id);
        $query->execute();      
        $result=$query->fetch();
        return $result;
    }
    /**
     * 
     * @param type $cotizacion
     * @return Array
     */
    public function getDireccion($cotizacion){
        $queryDirecion = $this->_adapter->prepare(QUERY_COTIZACION_2);
        $queryDirecion->bindParam(1,$cotizacion);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetch();
        return  $resultDireccion;
    } 
    /**
     * 
     * @param type $cotizacion
     * @return type
     */
    public function getItems($cotizacion){
        $queryDirecion = $this->_adapter->prepare(GET_ITEMS_COT);
        $queryDirecion->bindParam(1,$cotizacion);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetchAll();
        return  $resultDireccion;
    }
    
    public function getAlmacenCotiza($id) {
        $queryDirecion = $this->_adapter->prepare(GET_ALMACEN_COTIZACION);
        $queryDirecion->bindParam(1,$id);
        $queryDirecion->execute();
        $resultDireccion = $queryDirecion->fetch();
        return  $resultDireccion;
    
    }
}
