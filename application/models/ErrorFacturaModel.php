<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Application_Model_ErrorFacturaModel{
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
    public function tableFilterContent($result){
        $date="";
        $arr= array();
        $arrUser= array();
        $q = $this->_adapter->prepare("select ID,NETWORKALIAS from USERINFO;");
        $q->execute();
        $r = $q->fetchALL();
        foreach ($r as $key => $value) {
                $arrUser[$value['ID']]=$value['NETWORKALIAS'];
        }
        foreach ($result as $k => $v) {
            if(empty($v['UUID'])){$v['MENSAJE']="<b>Sin UUID por estatus rechazado</b>";}
            else{$v['MENSAJE']=$v['UUID'];}
            if($v['RESPUESTA']!=''){ $r3=$v['RESPUESTA'];}
            else if($v['STF_RESPUESTA']!=''){$r3=$v['STF_RESPUESTA'];}
            else{$r3="";}            
            $date = new DateTime($v['FECHA']);
            $fecha=$date->format('Y-m-d');
        $arr[$k]=array($v['FACTURA'],$v['OV'],$fecha,$v['SITIO'],$v['CLIENTE'],$v['RFC'],$v['MONEDA'],'$'.money_format('%i', $v['MONTO']),$arrUser[$v['CREADO']],$v['MENSAJE'],$r3);
        }
        $_SESSION['tblContent']=$arr;
        return $arr;
    }
    public function convertToXml($param) {
        $xml2=array();
        $xml = '<?xml version="1.0" ?>'.$param;
        $xmlObj = new SimpleXMLElement($xml);
        if($xmlObj['FullError']!=""){
            $xml2 =new SimpleXMLElement($xmlObj['FullError']);
        }
        return $xml2;
    }
    
    public function getConsulta($type,$param){
        try{
            if($type=='f'){
                $filter='AND C.INVOICEID = ?';
            }
            else{
                $filter='AND C.SALESID  = ? ';
            }
            $q = $this->_adapter->prepare(FACTURA_ERROR.$filter);
            $q->bindParam(1,$param);
            $q->execute();
            $result = $q->fetch();
            $q2 = $this->_adapter->prepare("select NETWORKALIAS from USERINFO WHERE ID=?;");
            $q2->bindParam(1,$result['CREADO']);
            $q2->execute();
            $r2=$q2->fetch();
            $result['CREADO']=$r2['NETWORKALIAS'];
            return $result;
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
    public function getConsultaFecha($fecha1,$fecha2,$error){
        try{
            $err="";
            if($error){
                $err="and E.CFDIUUID = '' ";
            }
            $q = $this->_adapter->prepare(FACTURA_ERROR_FILTRO_FECHA.$err);
            $q->bindParam(1,$fecha1);
            $q->bindParam(2,$fecha2);
            $q->execute();
            $result = $q->fetchAll();
            return $result;
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
}