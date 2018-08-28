<?php
ini_set("memory_limit","-1");
class IndexController extends Zend_Controller_Inax{

    public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
        if(COMPANY=='LIN'){
           $this->_redirect('/errorFactura');
        } 
    }
    public function setClientsToFileAction() {
        $model= new Application_Model_InicioModel();
        $model->setClientsToFile();
        $model->setItemsToFile();
        $this->json(array("res"=>"clientes cargado"));
    }
    public function aAction() {
        $model= new Application_Model_InicioModel();
        $this->json($model->getPriceItemClient());
    }
    public function indexAction() {  
        $date=new DateTime();
        $model= new Application_Model_InicioModel();
        $this->view->payTerm= $model->getPayTerm();
        $this->view->usoCFDI=$model->getUsoCDFI();
        $this->view->payMode=$model->getPayMode();
        $datosinicio = new Application_Model_Userinfo();
        $this->view->cargos = $datosinicio->Cargos();
        $this->view->fechaActual= $date->format('d/m/Y');
        $this->view->sucursalActual = $model->getSucursal();
    }
    public function getDataOvAction() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel(); 
        $this->json($model->getDataPaymentOv(filter_input(INPUT_POST, 'ov')));       
    }
    public function datosetiquetaAction(){
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel(); 
        $sitio = filter_input(INPUT_POST, 'sitio');
        $ov = filter_input(INPUT_POST, 'ov');                   
        $this->json($model->getDatosEtiqueta($_SESSION['userInax'], $sitio, $ov));
    }
    public function datospropoAction() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel(); 
        $ov = filter_input(INPUT_POST, 'ov');                
        print_r(json_encode($model->getDatosPropo($_SESSION['userInax'],$ov)));
        exit();
    }
    /***/
    public function todasov2Action() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel();  
        $this->json($model->getTodasOV2());
    }
    public function ovuser2Action() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel();        
        echo json_encode($model->getOVporUsuario2($_SESSION['userInax']));
        exit();
    }
    public function miscot2Action(){
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel();  
        $this->json($model->getCotPorUsuario2($_SESSION['userInax']));        
    }
    public function todascot2Action() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel(); 
        $this->json($model->getTodasCot2());
    }    
    public function emailAction() {
        $titulo=filter_input(INPUT_POST,'titulo');
        $mensaje=  filter_input(INPUT_POST,'mensaje');
        $asunto=filter_input(INPUT_POST,'asunto');
        $formato=filter_input(INPUT_POST,'formato');
        $model='';
        $this->_helper->layout->disableLayout();
        include (LIBRARY_PATH.'/includes/phpMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->Host = '10.168.4.33';
        $mail->Port = 25;
        $mail->SMTPSecure = 'smtp';
        $mail->SMTPAuth = false;
        $mail->Username = "notificaciones@avanceytec.com.mx";
        $mail->FromName = $titulo;      
        if($formato=="fallasinax.php"){
            $model= new Application_Model_FallasinaxModel();
             $mail->addAddress('fdelgado@avanceytec.com.mx'); 
        } 
        $res=false;
        if($formato=='traspasosSolicitud.html'){
            $type=filter_input(INPUT_POST,'type');
            $solicita=  filter_input(INPUT_POST,'solicita');
            $model= new Application_Model_SolicitudescedisModel();
            if($type==0){
                $solicita=filter_input(INPUT_POST,'user');
                $res=$model->insertSolicitudNew(filter_input(INPUT_POST,'cliente'), filter_input(INPUT_POST,'vendedor'),filter_input(INPUT_POST,'item'),filter_input(INPUT_POST,'cant'),filter_input(INPUT_POST,'almacen'), $solicita,  filter_input(INPUT_POST,'comenta'),filter_input(INPUT_POST,'motivo'),filter_input(INPUT_POST,'venta'));
            }
            if($type==1){
                $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),2);
                if($res){
                    $res=$model->update("UPDATE ".INTERNA.".dbo.traspasosinaxDetalle SET usuario= :user , modificacion=GETDATE() where folio= :id ", array(":id"=>filter_input(INPUT_POST,'folio'),":user"=>$_SESSION['userInax']));
                }
            }
            if($type==2){
                $res=$model->update(UPDATE_TRASPASO_DETALLE,array(":id"=>filter_input(INPUT_POST,'folio'),":comentarios"=> filter_input(INPUT_POST,'comentarios'),":user"=>$_SESSION['userInax']));
                if($res){
                    $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),3);
                }
            }
            if($type==3){
                $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),4);
            }
            if ($type==5) {
                $res=$model->update(UPDATE_TRASPASO_DETALLE,array(":id"=>filter_input(INPUT_POST,'folio'),":comentarios"=> filter_input(INPUT_POST,'comentarios'),":user"=>$_SESSION['userInax']));
                if($res){
                    $res=$model->updateEstatus(filter_input(INPUT_POST,'folio'),5);
                }
            }
            if($solicita===''){ $solicita=$_SESSION['email'];}  
            if(CONFIG!=DESARROLLO){                
                $direcciones=array('reabastecimiento2@avanceytec.com.mx','analistacedis@avanceytec.com.mx','asistentealmacenchih@avanceytec.com.mx','embarques2@avanceytec.com.mx','servsucursales1@avanceytec.com.mx','gerenteventas@avanceytec.com.mx','almacencedis@avanceytec.com.mx','sistemas11@avanceytec.com.mx','gerentecedis@avanceytec.com.mx',$solicita); 
            foreach ($direcciones as $key => $value){
                $mail->addAddress($value);
            }
        }
            else {
                 $mail->addAddress($solicita);
            }
        }
        $body = '';           
        $mail->Subject = $asunto;
        $body .= file_get_contents(APPLICATION_PATH.'/configs/'.$formato);
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $bodytag3 = str_replace("<style></style>",'<style></style>', $bodytag2);
        $mail->msgHTML(utf8_decode($bodytag3));
        $mail->AltBody = '';
       if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
           $d='enviado por correo pero no dado de alta en solicitudes';
           if($res){
               $d='enviado';
        }
           echo $d;
        }
        exit();
    }
    public function clientePasaAction() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel();  
        $this->json($model->getTotasOVCliente('%'.  filter_input(INPUT_POST,'nombre').'%','%'.  filter_input(INPUT_POST,'ov').'%'));
    }
    public function condiEntregaAction() {
        $this->_helper->layout->disableLayout();
        $model = new Application_Model_IndexModel();
        $this->json($model->condicionesEntrega(filter_input(INPUT_POST, 'ovta')));
    }
}