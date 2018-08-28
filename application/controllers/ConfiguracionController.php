<?php

class ConfiguracionController extends Zend_Controller_Inax{
    
    public function init(){
        try {
            $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    
    public function indexAction(){
        
    }
    
}