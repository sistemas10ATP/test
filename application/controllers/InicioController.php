<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', '1'); 
ini_set("memory_limit", "1024M");
// server should keep session data for AT LEAST 1 hour
session_set_cookie_params(86400);
date_default_timezone_set('America/Chihuahua');
ini_set('session.gc_maxlifetime', 86400);
class InicioController extends Zend_Controller_Inax{
    private $makeTiket ;
    public function init(){
        if(empty($_SESSION['userInax'])){
            $this->_redirect('/login');
        }
        else{
            require_once (LIBRARY_PATH.'/includes/makeTicket.php');
            $this->makeTiket = new makeTicket();
        }
        
    }
    
    public function indexAction(){
        $date=new DateTime();
        $model= new Application_Model_InicioModel();
        $request = $this->getRequest();
        $token="";
        if ($request->isGet()) {
            $token= filter_input(INPUT_GET,'token');
        }
        else {
            $token= filter_input(INPUT_POST,'token');
            $docType= filter_input(INPUT_POST,'documentType');
        }
        $datosinicio = new Application_Model_Userinfo();
        $datosinicio->_adapter->query(ANSI_NULLS);
        $datosinicio->_adapter->query(ANSI_WARNINGS);
        $this->view->cliente = json_encode($model->getClients(''));
        $this->view->sitios = $datosinicio->Sitios(COMPANY);
        $this->view->sitio = json_encode($this->view->sitios);
        $this->view->cargos = $datosinicio->Cargos();
        $this->view->modosentrega = $datosinicio->ModosEntrega();
        $this->view->editarOV = "NoResults";
        $this->view->origenVenta = $datosinicio->getOrigenesVenta();
        $this->view->map = "Datos De Cliente";
        $this->view->art = json_encode($model->getItems());
        $this->view->art2 = json_encode($model->getItemsCommon());
        $this->view->artNotLocked =  json_encode($model->getArtNotLocked());
        $this->view->usoCFDI=$model->getUsoCDFI();
        $this->view->payTerm= $model->getPayTerm();
        $this->view->payMode=$model->getPayMode();
        $this->view->fechaActual= $date->format('d/m/Y');
        $this->view->sucursalActual = $model->getSucursal();
        $_SESSION['edicion'] = 0;
        if ($request->isPost()) {                
            if (!isset($docType)){ 
                $this->view->documentType = 'ORDVTA';
                $this->view->titulo ="Orden de Venta";
            } 
            else { 
                $this->view->documentType = $docType; 
                if($docType=="ORDVTA"){ $this->view->titulo ="Orden de Venta"; }
                else {$this->view->titulo ="Cotización";}
            }            
            $editar=  filter_input(INPUT_POST,'editar');
            if (!empty($editar)){
                $_SESSION['edicion'] = 1;
                if ($docType == 'CTZN') {
                    $cotizacion = filter_input(INPUT_POST, 'DocumentId');
                    $result=$model->getCTZNDataClient($cotizacion);
                    $result2 = $model->getCTZNDataContent($cotizacion);      
                    $this->view->cuenta_pago=$model->getCuentaPagoTarjeta($cotizacion);
                    if(!empty($result)){ $model->setCTZNDataClient($result); }
                    if (!empty($result2)){                        
                        $clte = $result[0]['CUSTACCOUNT'];
                        $dlvTerm = $result[0]['DLVTERM'];
                        $fecha = strtotime(date('c', time()));
                        $DateTrans = $fecha;
                        $punitario = 0;
                        $cargo = $result[0]['CARGO'];
                        foreach ($result2 as $index => $Data){
                            $parametros = array(
                                '_CustAccount'      => $clte,
                                '_ItemId'       => $Data['ITEMID'],
                                '_SalesPrice'       => (double) $Data['SALESQTY'],
                                '_Date'       => $fecha,
                                '_amountQty'        => (double) $punitario,
                                '_currencyCode'     => $Data['CURRENCYCODE'],
                                '_InventSiteId'     => $Data['INVENTSITEID'],
                                '_InventLocationId' => $Data['INVENTLOCATIONID'],
                                '_PercentCharges'   => (double) $cargo);
                            $precioWS = $model->checarPreciosNew($parametros);
                            if (($precioWS['precio'] != $Data['STF_SALESPRICE']) && ($Data['BLOCKSALESPRICES'] != '1')) {
                                $result2[$index]['CAMBIOWS'] = 1;
                                $result2[$index]['PRECIOWS'] = $precioWS['precio'];
                            }
                        }
                        $json=json_encode($result2);
                        $this->view->editarOV = str_replace("'",'',$json);
                    } 
                    else {
                        $this->view->editarOV = 'NoResults';
                    }
                } 
                else if ($_POST['documentType'] == 'ORDVTA') {
                    $OV = $_POST['DocumentId'];
                    $query = $datosinicio->_adapter->prepare(EDITAR_ORDEN_VENTA);
                    $query->binParam(1,$OV);
                    $query->execute();
                    $result = $query->fetchAll();
                    $query2 = $datosinicio->_adapter->prepare(EDITAR_ORDEN_VENTA_2);
                    $query2->binParam(1,$OV);
                    $query2->execute();
                    $result2 = $query2->fetchAll();
                    if (! empty($result)) {
                        $_POST['PAYMMODE'] = $result[0]['PAYMMODE'];
                        $_POST['INVENTSITEID'] = $result[0]['INVENTSITEID'];
                        $_POST['INVENTLOCATIONID'] = $result[0]['INVENTLOCATIONID'];
                        $_POST['CURRENCYCODE'] = $result[0]['CURRENCYCODE'];
                        $_POST['DLVMODE'] = $result[0]['DLVMODE'];
                        $_POST['DLVTERM'] = $result[0]['DLVTERM'];
                        $_POST['WORKERSALESTAKER'] = $result[0]['WORKERSALESTAKER'];
                        $_POST['WORKERSALESRESPONSIBLE'] = $result[0]['WORKERSALESRESPONSIBLE'];
                    }
                    if (! empty($result2)) {
                        date_default_timezone_set('America/Chihuahua');
                        $clte = $result[0]['CUSTACCOUNT'];
                        $fecha1 = date('c', time());
                        $fecha = strtotime($fecha1);
                        $DateTrans = $fecha;
                        $punitario = 0;
                        $cargo = $result[0]['CARGO'];
                        foreach ($result2 as $index => $Data) {
                            $item = $Data['ITEMID'];
                            $qty = $Data['SALESQTY'];
                            $moneda = $Data['CURRENCYCODE'];
                            $sitio = $Data['INVENTSITEID'];
                            $almacen = $Data['INVENTLOCATIONID'];
                            $parametros = array(
                                '_CustAccount'      => $clte,
                                '_ItemId'       => $item,
                                '_SalesPrice'       => (double) $punitario,
                                '_Date'       => $fecha,
                                '_amountQty'        => (double) $qty,
                                '_currencyCode'     => $moneda,
                                '_InventSiteId'     => $sitio,
                                '_InventLocationId' => $almacen,
                                '_PercentCharges'   => (double) $cargo);

                            $precioWS = $model->checarPreciosNew($parametros);
                            if ($precioWS['precio'] != $Data['STF_SALESPRICE']) {
                                $result2[$index]['CAMBIOWS'] = 1;
                                $result2[$index]['PRECIOWS'] = $precioWS['precio'];
                            }
                        }
                        $json=json_encode($result2);
                        $this->view->editarOV = str_replace("'",'',$json);
                    } else {
                        $this->view->editarOV = 'NoResults';
                    }
                }
            }
            else {
                $this->view->editarOV = 'NoResults';
            }
            if (!is_null($docType) && $token==""){
                $model->setKardex($docType);
            }
        }
        switch ($token) {
            case 'IsPriceBlocked':
                echo json_encode($model->isPriceBloked(filter_input(INPUT_GET, 'item')));
                exit();
            break;
            case 'existenciaLote':
                echo json_encode($model->getExistenciaLote(filter_input(INPUT_GET,'item'), filter_input(INPUT_GET, 'sitio')));
                exit();
            break;
            case 'existenciasFamilia':
                $familia = filter_input(INPUT_GET,'familia');
                $sitio = filter_input(INPUT_GET,'sitio');
                $query = $datosinicio->_adapter->prepare("EXECUTE ExistenciasFamilia '$sitio','$familia'");
                $query->execute();
                $result = $query->fetchAll();
                if (empty($result)) {
                    $ExistenciaFamilia['noresult'] = "Sin Resultados!";
                }
                foreach ($result as $k => $v) {
                    $ExistenciaFamilia[$k] = $v;
                }
                echo json_encode($ExistenciaFamilia);
                exit();
                break;
            case 'generarNegado':                    
                $nombre = str_replace("'", '', $_GET['nombre']);
                $vendedor = $_SESSION['userInax'];
                $comentarioNegado = $_GET['comentarioNegado'];
                $sitioNegado = $_GET['sitio'];
                $unidadNegada = $_GET['unidad'];
                $result="";
                foreach ($_POST['data'] as $k => $v){
                    $query = $datosinicio->_adapter->query("INSERT INTO negados(codigo_articulo,nombre,vendedor,cliente,almacen,fecha,status,cantidad_exist,cantidad_negada,comentario,sitio,uom) 
                    VALUES('".$_POST['data'][$k]['artNegado']."','".str_replace("'", "''",$_POST['data'][$k]['descripcion'])."','$vendedor','".$_POST['data'][$k]['cliente']."','".$_POST['data'][$k]['almacen']."',GETDATE(),'0',".$_POST['data'][$k]['cantDisp'].",".$_POST['data'][$k]['cantNegada'].",'".$_POST['data'][$k]['comentario']."','".$_POST['data'][$k]['sitio']."','".$_POST['data'][$k]['unidad']."');");
                    $result = $query->rowCount();
                }
                if ($result > 0) {
                    $resultado = 'OK';
                } else {
                    $resultado = 'FAIL';
                }
                echo json_encode($resultado);
                exit();
                break;
            
            case 'detDataNegados':
                $item=$model->getDataNegados();
                print_r($item);
                exit();
                break;
        }
        $_SESSION['tipoC']=$model->getTipoCambio();
    }
    public function getArchivoAdjuntoAction() {
        $model= new Application_Model_InicioModel();
        $item=$model->getArchivoAdjunto(filter_input(INPUT_GET,'transaction'),filter_input(INPUT_GET,'id'));
        $this->json($item);
    }
    public function refreshlinesAction(){
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        print_r(json_encode($model->getRefreshLines(filter_input(INPUT_POST,'docType'),filter_input(INPUT_POST, 'docId'))));
        exit();
    }
    public function checaralertaAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        print_r(json_encode($model->getStatusAlerta(filter_input(INPUT_POST,'ov'))));
        exit();
    }
    public function checarbloqueoAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        print_r(json_encode($model->getStatusBloqueo(filter_input(INPUT_GET,'ov'))));
        exit();
    }
    public function getultimasventasAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        print_r($model->getUltimasVentas(filter_input(INPUT_POST,'cliente')));
        exit();
    }
    /**
     * TODO: modificar para que regrese lista co articulo y costo promedio
     */
    public function familiasAction() {
        $this->_helper->layout->disableLayout();        
        $datosinicio = new Application_Model_Userinfo();
        //$model= new Application_Model_InicioModel();
        $familia = filter_input(INPUT_GET,'familia');
        $sitio = filter_input(INPUT_GET,'sitio');
        $query = $datosinicio->_adapter->prepare("EXECUTE ExistenciasFamilia '$sitio','$familia'");
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $ExistenciaFamilia['noresult'] = "Sin Resultados!";
        }
        foreach ($result as $k => $v) {
            $ExistenciaFamilia[$k] = $v;
        }
        echo json_encode($ExistenciaFamilia);
        exit();
    }

    public function detalleventaAction(){
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        print_r(json_encode($model->getUltimasVentas2(filter_input(INPUT_POST,'ov'), filter_input(INPUT_POST,'transaction'), filter_input(INPUT_POST,'sitio'))));
        exit();
    }

    public function getalternativosAction() {
        $this->_helper->layout->disableLayout(); 
        $datosinicio = new Application_Model_InicioModel();
        /* 0260-0020-1235 */
        exit($datosinicio->getAlternativos(filter_input(INPUT_POST,'itemId'), filter_input(INPUT_POST,'sitio')));        
    }
    public function resumentestAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $claveclte = filter_input(INPUT_POST, 'claveclte');
        $direcciones = $model->getInvoiceDeliveryAddress($claveclte);
        if ($direcciones != 'NoResults') {
            $RecIdDelivery = filter_input(INPUT_POST, 'RecIdDireccion');
            $RecIdInvoiced = filter_input(INPUT_POST, 'RecIdDireccion');
            if (!empty($direcciones['entrega'])) { $RecIdDelivery = $direcciones['entrega']; } 
            if (!empty($direcciones['factura'])) { $RecIdInvoiced = $direcciones['factura']; }
        }
        $CurrencyCode = filter_input(INPUT_POST, 'moneda');
        $SiteId = filter_input(INPUT_POST, 'sitio');
        $LocationId = filter_input(INPUT_POST, 'almacen');
        $PaymMode = filter_input(INPUT_POST, 'CargoDesc');
        $DeliveryMode = filter_input(INPUT_POST, 'modoentrega');
        $DeliveryTerm = filter_input(INPUT_POST, 'condiEntrega');
        $WorkerResponsible = filter_input(INPUT_POST, 'responsableventa');
        $WorkerTaker = filter_input(INPUT_POST, 'secretarioventa');
        $User = $_SESSION['userInax'];
        $comentariosCabecera = filter_input(INPUT_POST, 'comentariosCabecera');
        $ocCliente = filter_input(INPUT_POST, 'OrdenCliente');
        $referenciaCliente = filter_input(INPUT_POST,'ReferenciaCliente');
        $documentType = filter_input(INPUT_POST, 'documentType');
        $seguridadCR = filter_input(INPUT_POST, 'digitos');
        $edicion = filter_input(INPUT_POST, 'edit');
        $docId = filter_input(INPUT_POST, 'id');
        // Datos de configuración del WebService
        try {                        
            $options = array('claveclte'=> $claveclte,
                      'company'             => COMPANY,
                      'RecIdDelivery'       => $RecIdDelivery,
                      'RecIdInvoiced'       => $RecIdInvoiced,
                      'CurrencyCode'        => $CurrencyCode,
                      'SiteId'              => $SiteId,
                      'LocationId'          => $LocationId,
                      'PaymMode'            => $PaymMode,
                      'DeliveryMode'        => $DeliveryMode,
                      'DeliveryTerm'        => $DeliveryTerm,
                      'WorkerResponsible'   => $WorkerResponsible,
                      'WorkerTaker'         => $WorkerTaker,
                      '_User'               => $User,
                      'comentariosCabecera' => $comentariosCabecera,
                      'ocCliente'           => $ocCliente,
                      'referenciaCliente'   => $referenciaCliente,
                      'seguridadCR'         => $seguridadCR,
                     'workerTaker'         => $WorkerTaker,
                      'user'                => $User,
                      'idCurrency'          => $CurrencyCode,
                       'documentId'=>$docId,
                    'cliente' =>$claveclte );
            $res=$model->setHeader($options, $documentType);  
            $resultado = array('OV' => $res['OV'],'encabezadoOV' => $res['encabezado'],'documentType' => $documentType);
            echo json_encode($resultado);
        }
        catch (Exception $objError) {
            echo '<b>'.$objError->getMessage().'</b>';                
        }
        exit();
    }
    public function generarremisionAction(){
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $ov = filter_input(INPUT_POST, 'ov');
            $remision = $model->setNewRemision($ov);
            echo $remision;   
        }
        catch (Exception $exc) {
            echo $exc->getMessage();
        }
        exit();
    }

    public function validarlimitecreditoAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $ov = filter_input(INPUT_POST,'ov');
        $usuario = filter_input(INPUT_POST, 'usuario');
        $res=$model->setCotToOv($ov, $usuario);
        $this->json($res);
    }
    public function convertircotovAction() {
        try {
            $this->_helper->layout->disableLayout();
            $cotizacion = filter_input(INPUT_POST, 'cotizacion');
            $noCuenta = filter_input(INPUT_POST, 'cuenta'); 
            $model= new Application_Model_InicioModel();            
            ///////nuevo parametro conversion de cotizacion a OV/////////
            $parametro = array('_QuotationId' => $cotizacion.','.$noCuenta.','.COMPANY);
            $ovQuot = $model->setCot2Ov($parametro);
            /* obtiene forma de pago*/
            $paymode = $model->getPayModeByOV($ovQuot['msg']); 
            if($paymode[0]["PAYMMODE"] == "02" || $paymode[0]["PAYMMODE"] == "99" || $paymode[0]["PAYMMODE"] == "04"){
                /* -- verifica numero de compras del cliente -- */
               $num=$model->getNumeroCompras(filter_input(INPUT_POST,'cliente'));
               $tot=$num[0][0];
               if((integer)$tot<=3){                
                   $cotizacionModel = new Application_Model_CotizacionModel();
                   $items=$cotizacionModel->getItems($cotizacion);
                   /* -- verifica montos de cotizacion */
                   $srt=0;
                   $detalle='<table border="1" ><tr><th style="padding:12px;">Código</th><th style="padding:12px;">Cantidad</th><th style="padding:12px;">Descripción</th></tr>';
                   foreach ($items as $k => $v) {
                       $srt+=$v['Importe'];
                       $detalle.='<tr ><td style="padding:12px;">'.$v['CodigoArticulo'].'</td><td style="padding:12px;" >'.number_format($v['Cantidad'],2).'</td><td style="padding:12px;">'.$v['DescripcionArticulo'].'</td></tr>';
                   }
                   $detalle.='</table>';
                   if($srt>=45000){
                       $user=["tecnologias@avanceytec.com.mx","gerenteventas@avanceytec.com.mx","gerentecomercial@avanceytec.com.mx","caja2@avanceytec.com.mx"];
                       $asunto='Alerta de cliente nuevo';
                       $titulo='Se ha creado una orden de venta '.$ovQuot['msg'].' de la cotizacion: '.$cotizacion;
                       $mensaje=  '<h1 style="color:red">Favor de verificar y dar seguimiento a este proceso.</h1>';                    
                       $mensaje.='<br><b>Usuario:</b>'.$_SESSION['userInax'].'<br><b>Nombre Completo:</b>'.$_SESSION['fullname'];
                       $body = file_get_contents(APPLICATION_PATH.'/configs/traspasosSolicitud.html');
                       $body .= '<br><h4>Datos de Cotizacion:</h4><br>'.$detalle;
                       $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
                       $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
                       $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
                       $bodytag3 = str_replace("<style></style>",'<style>'.$css.'</style>', $bodytag2);
                       $model->sendMail($user,$asunto, $bodytag3);
                   }                
               }
            }
            $this->json($ovQuot);
        } catch (Exception $objError) {
            $this->json($objError->getMessage());
        }
    }
    public function confirmarovAction() {
        $this->_helper->layout->disableLayout();
        $datosinicio = new Application_Model_Userinfo();
        $model= new Application_Model_InicioModel();
        $ctaBanco = filter_input(INPUT_POST, 'ctaBanco');
        $xml = filter_input(INPUT_POST, 'lineaXML');
        $encabezadoov = filter_input(INPUT_POST, 'encabezadoov');
        $metodoPago = filter_input(INPUT_POST, 'metodoPago');
        $origenVenta = filter_input(INPUT_POST, 'origenVenta');
        $OV =$xml;
        if($OV !=""){
            $model->setKardex("ORDVTAC");
            $confirmacion = $OV;
        }
        else {$confirmacion = 'FAIL';}
        /*
         * Query para insertar origen del historial de la venta o cotizacion
         */
        $query = $datosinicio->_adapter->prepare(KARDEX_VENTAS_OV);
        $query->bindParam(1,$confirmacion);
        $query->bindParam(2,$ctaBanco);
        $query->bindParam(3,$xml);
        $query->bindParam(4,$encabezadoov);
        $query->bindParam(5,$metodoPago);
        $query->bindParam(6,$origenVenta);
        $query->bindParam(7,$_SESSION['userInax']);
        $query->execute();
        $this->json($confirmacion);
    }
    
    public function confirmarcotizacionAction() {
        $this->_helper->layout->disableLayout();
        $datosinicio = new Application_Model_Userinfo();
        $confirmacion =  filter_input(INPUT_POST,'lineaXML');
        $encabezadoov = filter_input(INPUT_POST, 'encabezadoov');
        $metodoPago = filter_input(INPUT_POST, 'metodoPago');
        $ctaBanco = filter_input(INPUT_POST, 'ctaBanco');
        $origenVenta = filter_input(INPUT_POST, 'origenVenta');
        /*
         * Query para insertar origen del historial de la venta o cotizacion
         */
        $query = $datosinicio->_adapter->prepare(KARDEX_VENTAS_COT);
        $query->bindParam(1,$confirmacion);
        $query->bindParam(2,$ctaBanco);
        $query->bindParam(3,$confirmacion);
        $query->bindParam(4,$encabezadoov);
        $query->bindParam(5,$metodoPago);
        $query->bindParam(6,$origenVenta);
        $query->bindParam(7,$_SESSION['userInax']);
        $query->execute();
        $this->json($confirmacion);
    }
    public function newdocumentAction() {
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $RecIdDelivery = filter_input(INPUT_POST,'RecIdDireccion');
            if (empty($RecIdDelivery)) {
                $direcciones = $model->getInvoiceDeliveryAddress(filter_input(INPUT_POST, 'claveclte'));
                $RecIdDelivery = $direcciones['entrega'];
                $RecIdDelivery = $direcciones['factura'];
            }
            $docId = filter_input(INPUT_POST,'id');
            if ($docId == 'N/A') { $docId = '';}
            $documentType = filter_input(INPUT_POST,'documentType');                    
            $options = array(
                    'claveclte'=> filter_input(INPUT_POST, 'claveclte'),
                    'RecIdDelivery'       => $RecIdDelivery,
                    'RecIdInvoiced'       => $RecIdDelivery,
                    'CurrencyCode'        => filter_input(INPUT_POST,'moneda'),
                    'SiteId'              => filter_input(INPUT_POST,'sitio'),
                    'LocationId'          => filter_input(INPUT_POST,'almacen'),
                    'PaymMode'            => filter_input(INPUT_POST,'MetodoPago'), 
                    'DeliveryMode'        => filter_input(INPUT_POST,'modoentrega'),
                    'DeliveryTerm'        => filter_input(INPUT_POST,'condiEntrega'),
                    'WorkerResponsible'   => filter_input(INPUT_POST,'responsableventa'),
                    'WorkerTaker'         => filter_input(INPUT_POST,'secretarioventa'),
                    '_User'               => $_SESSION['userInax'],
                    'comentariosCabecera' => filter_input(INPUT_POST,'comentariosCabecera'),
                    'ocCliente'           => filter_input(INPUT_POST,'OrdenCliente'),
                    'referenciaCliente'   => filter_input(INPUT_POST,'ReferenciaCliente'),
                    'seguridadCR'         => filter_input(INPUT_POST,'ctaBanco'),
                    'documentId'          =>$docId,
                    'Payment'             =>filter_input(INPUT_POST,'payment')
                );
            $res=$model->setHeader($options, $documentType); 
            if (is_array($res)){
                $result = array(
                    'CTZN' => $res['OV'],
                    'documentType' => $documentType
                );
            }else{
                $result = 'FAIL';
            }
            $this->json($result);
        } catch (Exception $objError) {
            $this->json('FAIL');
        }
    }
    public function resumentestlineasAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $NumFilas = filter_input(INPUT_POST,'NumFilas');
        foreach ($_POST as $key => $value) {
            $lineas[$key] = $value;
        }
        $moneda = filter_input(INPUT_POST,'monedaLine');
        $DocumentId = filter_input(INPUT_POST,'DocumentId');
        $DocumentType = filter_input(INPUT_POST,'DocumentType');
        $lineasArr=array();                    
        for ($i = 1; $i < ($NumFilas + 1); $i ++) {
            ////////////////////////////calculo de precios T.C//////////////////////////////////////////////////////
            if ( $_POST['PorcentCargo'] == '7.16' || $_POST['PorcentCargo'] == '13.11' ){
                $clte = $_POST['clte'];
                $item = $lineas['item'.$i];
                $qty = $lineas['cantidad'.$i];
                $fecha1 = date('c', time());
                $fecha = strtotime($fecha1);
                $moneda = $_POST['monedaLine'];
                $sitio = $lineas['sitio'.$i];
                $almacen = $lineas['almacen'.$i];
                $punitario = $lineas['punitariolinea'.$i];
                $cargo = number_format(((((($_POST['PorcentCargo']/100) + 1 ) / 1.018) - 1) * 100),3);// aqui se resta el 1.8% de cargo para que dynamics lo calcule 
                $parametros = array(
                '_CustAccount'      => $clte,
                '_ItemId'       => $item,
                '_SalesPrice'       => (double) $punitario,
                '_Date'       => $fecha,
                '_amountQty'        => (double) $qty,
                '_currencyCode'     => $moneda,
                '_InventSiteId'     => $sitio,
                '_InventLocationId' => $almacen,
                '_PercentCharges'   => (double) $cargo);
            
                $precioWS = $model->checarPreciosNew($parametros);
                $lineas['punitariolinea'.$i] = $precioWS['precio'];
            }
            $lineasArr[]=array('numLine'          => $i,
                            'item'            => $lineas['item' . $i],
                            'sitio'           => $lineas['sitio' . $i],
                            'almacen'         => $lineas['almacen' . $i],
                            'localidad'       => $lineas['localidad' . $i],
                            'lote'            => $lineas['lote' . $i] ,
                            'cantidad'        => $lineas['cantidad' . $i],
                            'punitariolinea'  => $lineas['punitariolinea' . $i],
                            'comentariolinea' => $lineas['comentariolinea' . $i]);

        }//fin del for
        $res=$model->setLineas($lineasArr,$DocumentId,$DocumentType);
        if($res !=""){
            $model->setKardex("CTZNC");
            $resultado=array("fail"=>false,"res"=>$res,"art"=>$lineasArr);
        }
        else{
            $resultado=array("fail"=>true,"res"=>$res,"art"=>$lineasArr);
        }                    
        $this->json($resultado);
    }
    public function vercreditoAction() {
        try{
            $this->_helper->layout->disableLayout();
            $datosinicio = new Application_Model_Userinfo();
            $datosinicio->_adapter->query(ANSI_NULLS);
            $datosinicio->_adapter->query(ANSI_WARNINGS);
            $clte = filter_input(INPUT_POST,'cliente');
            $queryLimite =  $datosinicio->_adapter->prepare(PRECIOS_LIMITE);
            $queryLimite->bindParam(1,$clte);
            $queryLimite->execute();
            $resultLimite = $queryLimite->fetchAll();
            $this->json($resultLimite);
        }
        catch (Exception $e){
            echo 'ERROR: '. $e->getMessage();
        }                
        exit();
    }
    public function ticketAction(){
         $this->_helper->layout()->disableLayout();
         $this->makeTiket->setText($_POST["textTicket"]);
         $this->makeTiket->setUser($_SESSION['fullname']);
         $this->makeTiket->loadLog("/var/log/httpd/error_log");
         $this->makeTiket->setElemento($_POST["elemento"]);
         if($_POST["select_send_atachment"] == 1 || $_POST["select_send_atachment"] == "1"){
            $this->makeTiket->setAttachment($this->makeTiket->base64_to_jpeg($_POST["base64"], "/tmp/atachment.png"));
         }
        $res = $this->makeTiket->send();
        $data["woid"] = 0;
        $data["message"] = "error";
        $data["response"] = $res;
        $resp = simplexml_load_string($res);
        if (isset($resp->response->operation->Details[0]->workorderid)) {
            $woId = $resp->response->operation->Details[0]->workorderid;
             $data["woid"] = $woId;
             $data["message"] = "success";
        } 
        /*
         *    TODO:agregar funcion de envio de correo
         */
        header('Content-Type: application/json');
        $this->json($data);
    }
    public function direccionesAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $cliente = filter_input(INPUT_POST, 'cliente');
        $items = $model->getDirecciones($cliente);
        print_r($items); 
        exit();        
    }
    /*
     * regresa la lista de sitios al proporcionarle el estado 
     */
    public function sitiosAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $sitio = filter_input(INPUT_POST, 'sitio');
        $this->json($model->getSitio($sitio));
    }
    /*
     * obtiene el detalle del cliente
     */
    public function clienteAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $cliente = filter_input(INPUT_POST, 'cliente');
        $this->json($model->getClients($cliente));
    }
    /**
     * regresa el detalle del articulo al pasarle el item
     */
    public function productodetalleAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $this->json( $model->getProductDetail(filter_input(INPUT_POST,'articulo')));
    }
    /**
     * regresa las existencias para el modal de existencias
     */
    public function existenciasAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $item = filter_input(INPUT_POST, 'item');
        $sitio = filter_input(INPUT_POST,'sitio');
        $almacen = filter_input(INPUT_POST,'almacen');
        $localidad = '';
        $documenType = filter_input(INPUT_POST,'documenType');
        
        $data = $model->getExistencias($item,$sitio,$almacen,$localidad,$documenType,COMPANY);
        $data = json_decode($data,true);
        $res = $model->getMinimoVenta($item);
        
        if($res){
            $map = [];
            
            foreach ($res as $r){
                $key = $r["INVENTSITEID"];
                $map[$key] = $r["MULTIPLEQTY"];
            }
            
            foreach ($data["datos"] as &$d){
                $key = $d["Sitio"];
                if(isset($map[$key])){
                    $d["minimo"] = $map[$key];
                }else{
                    $d["minimo"] = $map[""];
                }
            }
            
        } else {
            foreach ($data["datos"] as &$d){
                $d["minimo"] = $res;
            }
        }
        
        $this->json($data);
    }
    public function claveclienteAction() {
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $clveclte = filter_input(INPUT_POST, 'clveclte');
        $this->json($model->getClientByClave($clveclte));
    }
    public function fraccionadoAction(){
        $this->_helper->layout->disableLayout();
        $model= new Application_Model_InicioModel();
        $this->json($model->getFraccionado(filter_input(INPUT_POST, 'almacen'),filter_input(INPUT_POST, 'item'))); 
    }
    public function devexistenciasAction() {
        try{
            $datosinicio = new Application_Model_Userinfo();
            $datosinicio->_adapter->query(ANSI_NULLS);
            $datosinicio->_adapter->query(ANSI_WARNINGS);
            $item = filter_input(INPUT_POST,'item');
            $sitio = filter_input(INPUT_POST,'sitio');
            $almacen = filter_input(INPUT_POST,'almacen');
            $localidad = filter_input(INPUT_POST,'localidad');
            $qty =  filter_input(INPUT_POST,'cant');
            /*tipo de articulo*/
            $q = $datosinicio->_adapter->prepare(GET_ITEM_TYPE);
            $q->bindParam(1,$item);
            $q->execute();
            $resq = $q->fetch();
            $itemType=$resq['ITEMTYPE'];
            /*conteo de articulo*/
            $q2 = $datosinicio->_adapter->prepare(GET_ITEM_CONTEO);
            $q2->bindParam(1,$item);
            $q2->bindParam(2,$sitio);
            $q2->bindParam(3,$almacen);
            $q2->execute();
            $resq2 = $q2->fetch();
            $itemConteo=$resq2['CONTEO'];
            /*PROCESO COMPLETO HERE*/
            if ( ($itemType == 0) && ($itemConteo > 0)){
                $queryDisponible = $datosinicio->_adapter->prepare(GET_ITEM_EXISTENCIA);
                $queryDisponible->bindParam(1,$item);
                $queryDisponible->bindParam(2,$sitio); 
                $queryDisponible->bindParam(3,$almacen);
                $queryDisponible->bindParam(4,$localidad);
                $queryDisponible->execute();
                $resultDisponible = $queryDisponible->fetchAll();
                $existencia = 0;
                if ($resultDisponible[0]['Existencia']!=.0000000000000000) {
                    $existencia = $resultDisponible[0]['Existencia'];
                }
                if ($qty > $existencia){
                    $disponible = 'excedido';
                }
                else {
                    $disponible = 'OK';
                }
                $precios=array('disponible' => $disponible,'cantDisp' => $existencia);
                $this->json($precios);
            }
            else{
                $precios=array('disponible' => 'excedido','cantDisp' => 0);
                $this->json($precios);
            }
            
        }
        catch (Exception $e){
            $this->json($e);
        }
    }
    /**
     * test para comparar precios con ws vs db
     */
    public function listadoAction() {
        $model= new Application_Model_InicioModel();
        $items= $model->getClients();
        $this->json($items);
    }
    
    public function preciosAction(){
        try {
            $objError="";
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $item = filter_input(INPUT_POST,'item');
            $clte = filter_input(INPUT_POST,'cliente');
            $qty =  filter_input(INPUT_POST,'qty');
            $fecha1 = date('c', time());
            $fecha = strtotime($fecha1);
            $moneda = filter_input(INPUT_POST,'moneda');
            $cargo = filter_input(INPUT_POST,'cargo');
            $sitio = filter_input(INPUT_POST,'sitio');
            $almacen = filter_input(INPUT_POST,'almacen');
            $punitario = filter_input(INPUT_POST,'punitario');
            $arrOptions = array(
                '_CustAccount'      => $clte,
                '_ItemId'       => $item,
                '_SalesPrice'       => (double) $punitario,
                '_Date'       => $fecha,
                '_amountQty'        => (double) $qty,
                '_currencyCode'     => $moneda,
                '_InventSiteId'     => $sitio,
                '_InventLocationId' => $almacen,
                '_PercentCharges'   => (double) $cargo);
            
            $result = $model->checarPreciosNew($arrOptions);
            $preciounit = $result['precio'];
            $precioiva = $result['precio_iva'];
            $error="";
            if(isset($result['error'])){
                $error=$result['error'];
            }

            $precios = array(
                "preciocargo" => $preciounit,
                "precioiva" => $precioiva,
                "error" =>$error
            );
            //$result=$model->getPriceFromDB(filter_input(INPUT_POST,'cliente'), filter_input(INPUT_POST,'item'),filter_input(INPUT_POST,'moneda'), COMPANY, filter_input(INPUT_POST,'cargo'),$objError);
            //$this->json($result);
            $this->json($precios);
        } catch (Exception $objError) { 
            $model= new Application_Model_InicioModel();
            $result=$model->getPriceFromDB(filter_input(INPUT_POST,'cliente'), filter_input(INPUT_POST,'item'),filter_input(INPUT_POST,'moneda'), COMPANY, filter_input(INPUT_POST,'cargo'),$objError);
            $this->json($result);
        }
    }
    public function emailAction() {
        $this->_helper->layout->disableLayout();
        $user="tecnologias@avanceytec.com.mx";
        $asunto='Alerta de falla InAX';
        $titulo=filter_input(INPUT_POST,'titulo');
        $mensaje=  filter_input(INPUT_POST,'mensaje');
        $body = '';
        $mensaje.='<br><b>Usuario:</b>'.$_SESSION['userInax'].'<br><b>Nombre Completo:</b>'.$_SESSION['fullname'];
        $body .= file_get_contents(APPLICATION_PATH.'/configs/correoBody.php');
        $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $bodytag3 = str_replace("<style></style>",'<style>'.$css.'</style>', $bodytag2);
        $model= new Application_Model_InicioModel();
        echo $model->sendMail($user, $asunto, $bodytag3);
        exit();
    } 
    /**
     * 
     * @param type $param
     */
    public function facturarAction(){
        $model= new Application_Model_InicioModel();
        $ov=  filter_input(INPUT_POST,'ov');
        /*agregar validacion de factura*/ 
        $isInvoice=$model->existFactura($ov);
        if(count($isInvoice)){
            $this->json(["resultado"=>"ok","respuesta"=>$isInvoice[0][0]]);
        }
        else{
            $remision=filter_input(INPUT_POST,'remision');
            $ordenCliente=filter_input(INPUT_POST,'ordenCliente');//aqui
            $refCliente=filter_input(INPUT_POST,'refCliente');//aqui
            $comentariosCabecera=filter_input(INPUT_POST,'comentariosCabecera');//aqui
            $direccion=filter_input(INPUT_POST,'direccion');
            $usoCFDI=filter_input(INPUT_POST,'usoCFDi');
            $modoPago=filter_input(INPUT_POST,'pagoModo');
            $pago=filter_input(INPUT_POST,'pago');
            $this->_helper->layout->disableLayout();
            $_SESSION['totalFactura']=0;
            $this->json($model->setFactura($ov,$remision,$ordenCliente,$refCliente,$comentariosCabecera,$direccion,$usoCFDI,$modoPago,$pago)); 
        }
        
    }
    
    public function getDireccionesAction() {
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->getDireccionesCliente($_SESSION['userInax'],  filter_input(INPUT_POST,'ov')));
        } catch (Exception $exc) {
            $this->json($exc->getTraceAsString());
        }
    }
    public function diarioAction(){
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->crearDiario(
                            filter_input(INPUT_POST,"factura"),
                            filter_input(INPUT_POST,"contrapartida"),
                            filter_input(INPUT_POST,"descripcion"),
                            filter_input(INPUT_POST,"diarioMontoFactura"),
                            filter_input(INPUT_POST,"diarioCuentaContra"),
                            filter_input(INPUT_POST,'diarioFPago')
                        )
                    );
        } catch (Exception $exc) {
           $this->json($exc);
        }
    }
    public function cuentaContrapartidaAction() {
        try{
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->getCuentaContrapartida());
        }
        catch (Exception $e){
            $this->json($e);
        }
    }
    public function cuentaContrapartidaLineaAction() {
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->getCuentaContrapartidaLinea());
        } catch (Exception $exc) {
            $this->json($exc->getTraceAsString());
        }
    }
    public function diarioDataAction() {
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->getCuentaContrapartida());
        }catch (Exception $exc) {
            $this->json($exc->getTraceAsString());
        }
    }
    public function facturaDataAction() {
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->getDataFactura(filter_input(INPUT_POST,'factura')));
        }catch (Exception $exc) {
           $this->json($exc->getTraceAsString());
        }
    }
   /* public function actualiza2Action(){
        try {
            $this->_helper->layout->disableLayout();
            $model= new Application_Model_InicioModel();
            $this->json($model->actualiza());
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }*/
}
