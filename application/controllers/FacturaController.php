<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FacturaController extends Zend_Controller_Action{
    public function init(){
       try {
           $this->_helper->layout->setLayout('bootstrap_single');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    function indexAction() {
        
    }
    function consultaAction() {
        $model = new Application_Model_FacturaModel();
        $folio=  filter_input(INPUT_POST,'folio');
        $tipo= $model->getTipo($folio);
        print_r(json_encode($tipo));
        exit();
    }
    
}