<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SolicitudescedisModel
 *
 * @author sistemas10
 */
class Application_Model_SolicitudescedisModel {
    private $db;
    private $_adapter;
    
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
    
    public function queryLine($query,$params=array()) {
        return $this->db->Query($query, $params);
    }
    public function queryResultSet($query,$params=array()){
        return $this->db->QueryResulSet($query,$params);
    } 
    public function getSolicitudData($id){
        $cabecera=$this->db->QueryResulSet(LISTA_TRASPASOS_ID,array(":id"=>$id));
        $detalle=$this->db->QueryResulSet(LISTA_TRASPASO_DETALLE_CUERPO,array(":id"=>$id));
        return array("cabecera"=>$cabecera,"detalle"=>$detalle);
    }
    public function insertSolicitudNew($clte,$user,$item,$cant,$almacen,$email,$comenta,$motivo,$cantidadVenta){
        $res=$this->db->Insert(INSERT_TRASPASO,array(":cliente"=>$clte,":user"=>$user,":item"=>$item,":cant"=>$cant,":almacen"=>$almacen));
        if($res){
            $id=$this->db->getLastID(INTERNA.'.dbo.traspasosInax','folio');
            $res=$this->insertSolicitudDetalle($id, $cantidadVenta, $item, $email, '',$comenta,$motivo);
        }
        return $res;
    }
    public function insertSolicitudDetalle($folio,$cantidad,$articulo,$vendedor,$usuario,$comenta,$motivo){
        return $this->db->Insert(INSERT_TRASPASO_DETALLE,array(":folio"=>$folio,":cantidad"=>$cantidad,":articulo"=>$articulo,":vendedor"=>$vendedor,":usuario"=>$usuario,":comenta"=>$comenta,":motivo"=>$motivo));
    }    
    public function updateEstatus($id,$st) {
        return $this->db->Insert(UPDATE_TRASPASO_ESTATUS,array(":folio"=>$id,":st"=>$st));
    }
    public function update($query,$params){
        return $this->db->Insert($query, $params);
    }
}
