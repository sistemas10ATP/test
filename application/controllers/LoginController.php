<?php
// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 86400);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(86400);

class LoginController extends Zend_Controller_Inax
{

    public $error;

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction(){
        // action body
        $this->_helper->layout()->disableLayout();
        $this->view->headTitle('Avance y Tecnología en Plásticos');
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
                        return $this->_helper->redirector->gotoUrl('../public/');
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

    public function loginAction(){
        // action body
    }
    public function getClientsAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        echo $model->getClients('');
        exit();
    }
}
