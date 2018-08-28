<?php

class UserinfoController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        // $this->_helper->layout()->disableLayout();
        $userinfo = new Application_Model_UserinfoMapper();
        // $this->view->entries = $userinfo->fetchAll();
        $this->view->entries = $userinfo->usuarios();
    }
}

