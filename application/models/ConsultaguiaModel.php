<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConsultaguiaModel
 *
 * @author sistemas10
 */
class Application_Model_ConsultaguiaModel {
    
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
     * 
     */
    function getListByClient($params){
        return $this->db->Query(CONSULTA_VENTAS_MES, $params);
    }
    
    function getDetailFromOV($params) {
        return $this->db->Query(DETALLE_OV,$params);
    }
}
