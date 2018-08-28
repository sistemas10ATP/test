<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FacturaModel
 * $factura = $_POST['factura'];
	$Query = "SELECT  
	EINV.XMLDOC 
	,EINV.RECID 
	,EINV.INVOICEDATETIME 
	,(CASE WHEN EINV.REFTABLEID = '592' THEN 'PROYECTO' ELSE 'CLIENTE' END) AS TIPO 
	,(CASE WHEN EINV.REFTABLEID = '592' THEN (SELECT PROJ.INVOICEACCOUNT FROM PROJINVOICEJOUR WHERE RECID = EINV.REFRECID) ELSE CINV.INVOICEACCOUNT END) AS INVOICEACCOUNT 
	,(CASE WHEN EINV.REFTABLEID = '592' THEN (SELECT PROJ.PROJINVOICEID FROM PROJINVOICEJOUR WHERE RECID = EINV.REFRECID) ELSE CINV.INVOICEID END) AS INVOICEID 
	,(CASE WHEN EINV.REFTABLEID = '592' THEN DBO.getLocator(EINV.REFRECID,'Proyecto') ELSE DBO.getLocator(EINV.REFRECID,'Cliente') END) AS LOCATOR
	FROM EINVOICEJOUR_MX EINV 
	LEFT JOIN CUSTINVOICEJOUR CINV ON (CINV.RECID = EINV.REFRECID) 
	LEFT JOIN PROJINVOICEJOUR PROJ ON (PROJ.RECID = EINV.REFRECID) 
	WHERE CINV.INVOICEID = '$factura';";
 *
 * @author sistemas10
 */
class Application_Model_FacturaModel {
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
     * recibe el folio de factura 
     * y regresa el tipo de factura
     * @param type $param
     */
    function getTipo($param) {
        try{
            $query = "SELECT (CASE WHEN EINV.REFTABLEID = '592' THEN 'PROYECTO' ELSE 'CLIENTE' END) AS TIPO
                FROM EINVOICEJOUR_MX EINV 
                LEFT JOIN CUSTINVOICEJOUR CINV ON (CINV.RECID = EINV.REFRECID) 
                LEFT JOIN PROJINVOICEJOUR PROJ ON (PROJ.RECID = EINV.REFRECID) WHERE CINV.INVOICEID = ?;";
            $q = $this->_adapter->prepare($query);
            $q->bindParam(1,$param);
            $q->execute();
            $result = $q->fetch();
            if(empty($result)){$result=array("TIPO"=>"false");}
            return $result;
        } catch (PDOException $exc) {
            return $exc->getTraceAsString();
        }
    }
}
