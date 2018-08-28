<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComisionesController
 *
 * @author sistemas10
 */
class ComisionesController extends Zend_Controller_Inax{
    public function init(){
        try {
            if(empty($_SESSION['userInax'])){
                $this->_redirect('/login');
            }
            $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    public function indexAction() {
        $date = new DateTime();
        $clienteModel = new Application_Model_InicioModel();
        $comisionista = new Application_Model_ComisionesModel();
        $this->view->fechaInput = $date->format('Y-m-d');
        $date->modify('-1 month');
        $this->view->fechaInput2= $date->format('Y-m-d');
        $this->view->clienteList= json_encode($clienteModel->getClients());
        $this->view->comisionistaList =  $comisionista->getArrayDataListToJson(COMISIONISTA_LISTAR);
        $this->view->articuloList =  $comisionista->getArrayDataListToJson(GET_ITEMS2);
        $this->view->comisionistaAsignadoList =  $comisionista->getArrayDataList(COMISIONISTA_CLIENTE_LISTAR);
        
    }
    public function altaClienteAction(){
       $nombre      = filter_input(INPUT_POST,'nombre');
       $direccion   = filter_input(INPUT_POST,'direccion');
       $telefono    = filter_input(INPUT_POST,'telefono');
       $estado      = filter_input(INPUT_POST,'estado');
       $ciudad      = filter_input(INPUT_POST,'ciudad');
       $comisionista = new Application_Model_ComisionesModel();
       $res=$comisionista->newComisionista($nombre,$direccion,$telefono,$estado,$ciudad);
       $this->json($res);
    }
    public function altaComisionistaClienteAction(){
        $mes            = filter_input(INPUT_POST,'mes');
        $articulo       = filter_input(INPUT_POST,'articulo');
        $comisionistaData   = filter_input(INPUT_POST,'comisionista');
        $cliente        = filter_input(INPUT_POST,'cliente');
        $comision       = filter_input(INPUT_POST,'comision');
        $comisionista   = new Application_Model_ComisionesModel();
        $res=$comisionista->newComisionistaCliente($mes,$articulo,$comisionistaData, $cliente, $comision);
        $this->json($res);        
    }
    public function deleteComisionistaClienteAction(){
        $comisionista   = new Application_Model_ComisionesModel();
        $res=$comisionista->deleteComisionistaCliente(filter_input(INPUT_POST,'idRow'));
        $this->json($res);        
    }
    public function getComisionistaClienteAction(){
        $comisionista   = new Application_Model_ComisionesModel();
        $this->json($comisionista->getArrayDataList(COMISIONISTA_CLIENTE_LISTAR));        
    }
    public function getComisionistaClienteReporteAction(){
        $comisionista   = new Application_Model_ComisionesModel();
        $this->json($comisionista->getReporteTbl(array(':comisionista'=>  '%'.filter_input(INPUT_POST, 'comisionista').'%',':mes1'=>filter_input(INPUT_POST,'mes'),':mes'=>filter_input(INPUT_POST,'mes'))));        
    }
    public function getComisionistaDataAction() {
        $comisionista   = new Application_Model_ComisionesModel();
        $this->json($comisionista->getArrayDataList(GET_COMISIONISTA_DATA,array(':id'=>  filter_input(INPUT_POST,'id'))));
    }
}
