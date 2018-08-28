<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogincedisController
 *
 * @author sistemas10
 */
class LogincedisController extends Zend_Controller_Inax {
    public function int() {
        
    }
    /**
     * 
     */
    public function indexAction(){
       // action body
        $this->_helper->layout()->disableLayout();
        $this->view->headTitle('Avance y Tecnología en Plásticos - CEDIS');
        $log = new Application_Model_Login();
        $this->view->error = 0;
        if (isset($_POST['userLogin']) && isset($_POST['userPassword'])) {
            if ($this->getRequest()->isPost()) {
                $user = $_POST['userLogin'];
                $password = $_POST['userPassword'];
                $result = $log->authenticate($user, $password);
                switch ($result) {
                    case 1:
                        $this->view->error = 1;
                        return $this->view->error;
                        break;
                    case 3:
                        // todo bien
                        $log->loginKardex($_SESSION['userInax'],$_SESSION['nomuser']);
                        $this->_helper->redirector->gotoUrl('../public/solicitudescedis');
                        break;
                    case 4:
                        $this->view->error = 4;
                        return $this->view->error;
                        break;
                    default:
                        $this->view->error = 1;
                        return $this->view->error;
                        break;
                }
            }
        } else {
            $this->view->error = 0;
            // destroy session
            $_SESSION = array();
            unset($_SESSION['userInax'], $_SESSION['access']);
            Zend_Session::destroy();
            return $this->view->error;
        }
    }
}
