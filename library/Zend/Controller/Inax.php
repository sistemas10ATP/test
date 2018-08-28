<?php

abstract class Zend_Controller_Inax extends Zend_Controller_Action {
    public function json($data){
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}