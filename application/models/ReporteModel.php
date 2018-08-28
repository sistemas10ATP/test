<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reporteModel
 *
 * @author sistemas10    
 */

class Application_Model_ReporteModel {
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
    public function sqlPrepare($q, $array=array()) {
       $query = $this->_adapter->prepare($q);
       $query->execute($array);
       $result=$query->rowCount();
       return $result ;
    }
    /**
     * 
     * @return ARRAY
     */
    public function getData2Array($q,$array=array()) {
       $query = $this->_adapter->prepare($q);
       $query->execute($array);
       $result=$query->fetchAll();
       return $result ;
    }
    /**
     * 
     * @return type
     */
    public function getUsrListPermiso() {
        $query = $this->_adapter->prepare(USER_LIST_ASIGNED);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    /**
     * 
     * @return type
     */
    public function getUsoReportTable(){
       $query = $this->_adapter->prepare(KARDEX);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    
    /**
     * Regresa resulset de la tabla de kardex con los campos filtrados, los cuales estan definidos por parametros
     * los parametros: $param$ y $param5 son asignados a fecha en el formato "20170821" en ves de "2017-08-21"
     * @param String $kardexFolio
     * @param String $usuario
     * @param String $nombre
     * @param String $fecha1
     * @param String $fecha2
     * @param String $movimiento
     * @param String $sucursal
     * 
     * @return ResultSet content kardex table
     */
    
    public function getUsoReportTableFilter($kardexFolio,$usuario,$nombre,$fecha1,$fecha2,$movimiento,$sucursal) {
       //$query = $this->_adapter->prepare(KARDEX_FILTER);
       $query=$this->_adapter->query("select t1.id_kardex,t1.usuario,t1.nombre,t1.fecha,t1.movimiento,t2.nombre as sucursal from  ".INTERNA.".dbo.kardex t1 right join ".INTERNA.".dbo.sucursal t2 on t2.idsuc=t1.sucursal where t1.id_kardex like '$kardexFolio%' and t1.usuario like '%$usuario%' and t1.nombre like '%$nombre%' and t1.fecha between '$fecha1' and '$fecha2' and t1.movimiento like '$movimiento%' and t2.nombre like '%$sucursal%'  order by t1.fecha desc;");
        $query->bindParam(1,$kardexFolio);
       $query->bindParam(2,$usuario);
       $query->bindParam(3,$nombre);
       $query->bindParam(4,$fecha1);
       $query->bindParam(5,$fecha2);
       $query->bindParam(6,$movimiento);
       $query->bindParam(7,$sucursal);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;        
    }
    public function getUsoReportGrafico(){
       $query = $this->_adapter->prepare(ESTADISTICO_LOGIN);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function getCTZN_VS_VTAS(){
       $query = $this->_adapter->prepare(CTZN_VS_VTAS);
       $query->execute();
       $result=$query->fetchAll();  
       return $result ;
    }
    public function getConfirmadosVsCreados(){
       $query = $this->_adapter->prepare(CONFIRMADOS_VS_CREADOS);
       $query->execute();
       $result=$query->fetchAll();  
       return $result ;
    }
    public function getConfirmadosVsCreadosPorUsuario(){
       $query = $this->_adapter->prepare(CONFIRMADOS_VS_CREADOS_POR_USUSRIO);
       $query->execute();
       $result=$query->fetchAll();  
       return $result ;
    }
    public function getDataNegadosFilter($f1,$f2,$vendedor) {
        $query = $this->_adapter->prepare(GET_DATA_NEGADOS_FILTER);
        $query->bindParam(1,$f1);
       $query->bindParam(2,$f2);
       $query->bindParam(3,$vendedor);
        $query->execute();
        $result = $query->fetchAll();
        return $result;
    }
    public function getGraficaUSO($f1,$f2){
       $query = $this->_adapter->prepare(GRAFICA_SUCURSAL); 
       $query->bindParam(1,$f1);
       $query->bindParam(2,$f2);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function getDataSemaforo(){
       $query = $this->_adapter->prepare(SEMAFORO); 
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function updateDataSemaforo($id) {
       $query1= $this->_adapter->query(SEMAFORO_RESET);
       $query1->execute();
       $query = $this->_adapter->prepare(SEMAFORO_UPDATE); 
       $query->bindParam(1,$id);
       $result=$query->execute();
       return $result; 
    }
    function getStatusSemaforo() {
    $query = $this->_adapter->prepare(SEMAFORO_ACTUAL); 
       $query->execute();
       $result=$query->fetchAll();
       $res="";
       foreach ($result as $key => $value) {
           $res=$value['color'];
       }
       return $res ;
    }
}
