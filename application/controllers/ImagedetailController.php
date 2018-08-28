<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageDetailController
 *
 * @author sistemas10
 */
class ImageDetailController extends Zend_Controller_Action{
    public function init(){
        try {
           $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }      
    }
    public function indexAction(){
        $model= new Application_Model_MermaModel();
        $this->view->item=filter_input(INPUT_GET,'item');
        $this->view->images=$model->getPictures(filter_input(INPUT_GET,'item'),filter_input(INPUT_GET,'almacen'),filter_input(INPUT_GET,'local'));         
    }
}
