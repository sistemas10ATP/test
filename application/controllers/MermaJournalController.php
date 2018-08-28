<?php

class MermaJournalController extends Zend_Controller_Inax{
    
    public function init(){
         /* Initialize action controller here */
        try {
            //$this->_helper->layout()->disableLayout();
           $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function indexAction(){
        $mermaModel = new Application_Model_MermaModel();
        $journalData = $mermaModel->getJournals();
        $tableHTML = "";
        foreach ($journalData as $data){
            $tableHTML .= "<tr>"
                    . "<td>".$data["ID_VENTAMERMA"]."</td>"
                    . "<td>".$data["ITEMID"]."</td>"
                    . "<td>".$data["ALMACEN"]."</td>"
                    . "<td>".$data["LOC"]."</td>"
                    . "<td>".$data["USERNAME"]."</td>"
                    . "<td>".$data["CREATEDATE"]."</td>"
                    . "<td>".$data["DATA"]."</td>"
                    . "</tr>";
        }
        
        $this->view->bodytable = $tableHTML;
    }
}
