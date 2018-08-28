<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Application_Model_DiariosModel {
    public $db;
    public $_adapter;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
    }
    public function getDiarioDetalle($diario){         
        return $this->db->Query(DIARIO_DETALLE,[":diario"=>$diario]);
    }
    public function getDiarios($fechai,$fechaf) {
        return $this->db->Query(DIARIO_LIST);//,[":fechai"=>$fechai,":fechaf"=>$fechaf]);
    }
    public function getFacturasSaldo(){
        try{
            ini_set('memory_limit', '-1');
            $arr=$this->db->Query(FACTURAS_SALDO);
            $res=[];
            foreach ( $arr as $key => $value) {
                $total=(double)$value[1];
                if($total >0){
                    $res[]=$value;
                }
            }
            return $res;
        }
        catch (Exception $e){
            return $e;
        }
        
    }
}