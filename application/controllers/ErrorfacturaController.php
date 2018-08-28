<?php
error_reporting(E_ALL ^ E_NOTICE^ E_WARNING);
ini_set('display_errors', '1');

class ErrorFacturaController extends Zend_Controller_Action{
    
    public function init() {
        $this->_helper->layout->setLayout('bootstrap');
        if(COMPANY=='LIN'){
            $this->_helper->layout->setLayout('lideart');
        }
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
    }
    public function indexAction() { 
     
    }
    public function filtroAction() {
        try{
            $model=new Application_Model_ErrorFacturaModel();
            $result=$model->getConsulta(filter_input(INPUT_POST, 'tipo'),filter_input(INPUT_POST,'folio'));
            if($result['RESPUESTA']!=''){
                $result['errorCFDI']=$model->convertToXml($result['RESPUESTA']);
            }
            else if($result['STF_RESPUESTA']!=''){
                $result['errorCFDI']=$model->convertToXml($result['STF_RESPUESTA']);
            }
            else{
                $result['errorCFDI']=array('Error'=>array('Codigo'=>'N/A','Detalle'=>'N/A','Mensaje'=>'Timbrado Correctamente','MensajeParaCliente'=>$result['UUID']));
            }
            $result['MONTO']=round($result['MONTO'], 2);
            $date = new DateTime($result['FECHA']);
            $result['FECHA']=$date->format('Y-m-d');
            echo json_encode($result);
            exit();
        }
        catch (Exception $e){
            echo json_encode(array($e->getMessage()));
            exit();
        }
    }
    
    public function filtrofechaAction() {
        try{
            $model=new Application_Model_ErrorFacturaModel();
            $result=$model->getConsultaFecha(filter_input(INPUT_POST, 'fechaFiltro1'),filter_input(INPUT_POST,'fechaFiltro2'),filter_input(INPUT_POST,'error'));
            echo json_encode($model->tableFilterContent($result));
            exit();
        }
        catch (Exception $e){
            echo json_encode($e);
            exit();
        }
    }
    
    public function filtrofechaexcelAction() {
        $arr=$_SESSION['tblContent'];
        header("Content-type: application/vnd.ms-excel");
        header( "Content-Disposition: attachment; filename=concentradoFacturas.csv");
        $salida_cvs="Factura,OV,Fecha,Sucursal,Cliente,RFC,Moneda,Monto,Usuario,UUID,Detalle del error,";
        if(!empty($arr)){
            $salida_cvs .= "\n";
            for($i=0;$i<count($arr);$i++){
                for ($j=0;$j<count($arr[0]);$j++){
                    $salida_cvs .= str_replace(","," ",$arr[$i][$j]).",";
                }
                $salida_cvs .= "\n";
            }           
        }
        print $salida_cvs;
        exit();
    }
    
}