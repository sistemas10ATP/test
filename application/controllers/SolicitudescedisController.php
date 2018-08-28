<?php
/**
 * Description of solicitudescedisController
 *
 * @author sistemas10
 */
require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');

class solicitudescedisController  extends Zend_Controller_Inax{
    public function init(){
        try {
            $this->_helper->layout->setLayout('bootstrap_single');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
        if(empty(COMPANY)){
            $this->_redirect('/logincedis');
        }
        date_default_timezone_set('America/Chihuahua');
    }
    /**
     * 
     */
    public function indexAction(){
        $model= new Application_Model_InicioModel();
        $res=$model->getClients('');
        $clientes=array();
        foreach ($res as $key => $value) {
            $clientes[$value['ClaveCliente']]=[$value['Nombre']];
        }
        $this->view->cliente = json_encode($clientes);
    }
    public function tablaListAction() {
        $model= new Application_Model_SolicitudescedisModel();
        $id=  filter_input(INPUT_POST,'id');
        $tipo= filter_input(INPUT_POST,'tipo');
        if($id!=''){
            $this->json($model->queryLine(LISTA_TRASPASOS_ID,array(":id"=>$id)));
        }
        else{
            if($tipo==1){
                $this->json($model->queryLine(LISTA_TRASPASOS_CEDIS));
            }
            else if($tipo==2){
                $this->json($model->queryLine(LISTA_TRASPASOS_ALMACEN));
            }
            else{
                $this->json($model->queryLine(LISTA_TRASPASOS));
            }
        }
    }
    public function solicitudAction(){
        $model= new Application_Model_SolicitudescedisModel();
        $this->json($model->getSolicitudData(filter_input(INPUT_POST,"id")));
    }
    public function solicitudDetalleAction(){
        $model= new Application_Model_SolicitudescedisModel();
        $this->json($model->getSolicitudData(1));
    }
    public function traspasoImprimirAction(){
        try{
        $model= new Application_Model_SolicitudescedisModel();
        $clienteModel= new Application_Model_InicioModel();
        $body='<center><h1>Solicitud de traspaso InAX</h1></center>';
        $data=$model->getSolicitudData(filter_input(INPUT_GET,"id"));
        $cabecera=$data['cabecera'];
        $detalle= $data['detalle'];
        $ImpCotizacion='solicitud';
        $log = new Application_Model_Userinfo();
        foreach($cabecera as $k => $v){
            $cliente=$clienteModel->getClients($v['cliente']);
            $articulo=$clienteModel->getProductDetail($v['articulo']);
            $body.='<table style="width:100%;" border=1 >'
                    . '<tr>'
                        . '<td colspan="4" ><center style="background-color:#C9C9C9">MATERIALES DE CEDIS A ALMACÉN CHIHUAHUA</center></td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td ><b>Folio: </b>'.filter_input(INPUT_GET,"id").'</td>'
                        . '<td ><b>Vendedor : </b>'.$v['solicita'].'</td>'
                        . '<td colspan="2"><b>Fecha de solicitud: </b>'.$v['fecha'].'</td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td colspan="2" ><b>Código de cliente: </b> <label>  '.$v['cliente'].'</label></td>'
                        . '<td colspan="2" ><b>Ultima modificación: </b> <label>  '.$detalle[0]['modificacion'].'</label></td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td colspan="4"><b>Nombre de cliente: </b> '.$cliente[0]['Nombre'].'</td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td colspan="2" ><b>Motivo de solicitud:</b> '.$detalle[0]['motivo'].'</td>'
                        . '<td colspan="2" ><b>Ultimo movimiento por:</b> '.$detalle[0]['usuario'].'</td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td colspan="4"><b>Comentarios Vendedor:</b> '.$detalle[0]['comentarios'].'</td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td colspan="4"><b>Comentarios Cedis:</b> '.$detalle[0]['comentarioscedis'].'</td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td colspan="4"><b>Cantidad de venta:</b> '.$detalle[0]['cantidad'].'</td>'
                    . '</tr>'
                    .'</table>'
                    .'<br><br>'
                    . '<table style="width:100%;" border=-1>'                    
                    . '<tr>'
                        . '<td style="background-color:#C9C9C9">Clave Artículo</td>'
                        . '<td style="background-color:#C9C9C9">Descripción de Artículo</td>'
                        . '<td style="background-color:#C9C9C9">Cantidad</td>'
                        . '<td style="background-color:#C9C9C9">Almacén</td>'
                    . '</tr>'
                    . '<tr>'
                        . '<td>'.$v['articulo'].'</td>'
                        . '<td>'.$articulo[0]['label'].'</td>'
                        . '<td>'.$v['cantidad'].'</td>'
                        . '<td>'.$v['almacen'].'</td>'
                    . '</tr>'
                    . '</table>';         
        }        
        spl_autoload_register('DOMPDF_autoload');
        $pdf=new DOMPDF();
        $pdf->load_html(utf8_decode($body));
        $pdf->set_paper('a4','portrait');
        $pdf->render();
        $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
        $log->kardexLog("Impresion cotizacion: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"Impresion cotizacion");
        exit(); 
        }
        catch (Exception $e){
            var_dump($e);
            exit();
        }
    }
}
