<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReporteFallasWSModel
 *
 * @author sistemas10
 */
class Application_Model_FallasinaxModel {
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
    function getCountPeticiones($f1,$f2) {
        $query = $this->_adapter->prepare(TOTAL_PETICIONES_WS);
        $query->bindParam(1,$f1);
        $query->bindParam(2,$f2);
        $query->execute();
        return $query->fetch();
    }
    function getPeticionesTbl($f1,$f2) {
        $query = $this->_adapter->prepare(TOTAL_PETICIONES_TBL);
        $query->bindParam(1,$f1);
        $query->bindParam(2,$f2);
        $query->execute();
        return $query->fetchAll();
    }
    function getErrorUserTbl($f1,$f2) {
        $query = $this->_adapter->prepare(ERRORES_USUARIO);
        $query->bindParam(1,$f1);
        $query->bindParam(2,$f2);
        $query->execute();
        return $query->fetchAll();
    }
    function getCount($f1,$f2,$q) {
        $query = $this->_adapter->prepare($q);
        $query->bindParam(1,$f1);
        $query->bindParam(2,$f2);
        $query->execute();
        return $query->fetch();
    }
    function getUseByBranchOffice($dateBefore,$dateNow) {
        $countFacturas=  $this->db->QueryResulSet(COUNT_FACTURAS_SUCURSAL,array(":fechaInicial"=>$dateBefore,":fechaActual"=>$dateNow));
        return $countFacturas;
    }
    function getUseByBranchUser($dateBefore,$dateNow) {
        $countFacturas=  $this->db->QueryResulSet(COUNT_FACTURAS_USER,array(":fechaInicial"=>$dateBefore,":fechaActual"=>$dateNow));
        return $countFacturas;
    }
    function getSucursales() {
        return $this->db->Query(DATA_SUCURSAL_LIST);
    }
}
