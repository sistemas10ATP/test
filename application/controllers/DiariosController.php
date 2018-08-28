<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DiariosController extends Zend_Controller_Inax{
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
        $model=new Application_Model_DiariosModel();
        $inicioModel= new Application_Model_InicioModel();
        $this->view->fechaInput = date("Y-m-d");  
        $this->view->facturas= json_encode($model->getFacturasSaldo());
        $this->view->payMode=$inicioModel->getPayMode();        
    } 
    public function getInfoAction(){
        $model=new Application_Model_DiariosModel();
        $this->json($model->getDiarios(filter_input(INPUT_POST,'fechai'),filter_input(INPUT_POST,'fechaf')));
    }
    public function getDetailAction() {
        $model=new Application_Model_DiariosModel();
        $this->json($model->getDiarioDetalle(filter_input(INPUT_POST,'diario')));
    }
    public function saveDiarioAction(){
        $model=new Application_Model_InicioModel();        
        $this->json($model->modificarDiario(filter_input(INPUT_POST,'diario')));
    }
    public function cerrarDiarioAction(){
        $model=new Application_Model_InicioModel();        
        $this->json($model->cerrarDiario(filter_input(INPUT_POST,'diario')));
    }
}