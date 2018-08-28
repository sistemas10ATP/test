<?php 
class NTLMSoapClient extends SoapClient {
    function __doRequest($request, $location, $action, $version) {
        $headers = array(
            'Method: POST',
            'Connection: Keep-Alive',
            'User-Agent: PHP-SOAP-CURL',
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "'.$action.'"',
        );
        $this->__last_request_headers = $headers;
        $ch = curl_init($location);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($ch, CURLOPT_USERPWD, USERPWD);
        $response = curl_exec($ch);
        return $response;
    }

    function __getLastRequestHeaders() {
        return implode("\n", $this->__last_request_headers)."\n";
    }
}

class NTLMStream{
    private $path;
    private $mode;
    private $options;
    private $opened_path;
    private $buffer;
    private $pos;
    /**
     * Open the stream
      *
     * @param unknown_type $path
     * @param unknown_type $mode
     * @param unknown_type $options
     * @param unknown_type $opened_path
     * @return unknown
     */
    public function stream_open($path, $mode, $options, $opened_path) {
        $this->path = $path;
        $this->mode = $mode;
        $this->options = $options;
        $this->opened_path = $opened_path;
        $this->createBuffer($path);
        return true;
    }
    /**
     * Close the stream
     *
     */
    public function stream_close() {
        curl_close($this->ch);
    }
    /**
     * Read the stream
     *
     * @param int $count number of bytes to read
     * @return content from pos to count
     */
    public function stream_read($count) {
        if(strlen($this->buffer) == 0) {
            return false;
        }
        $read = substr($this->buffer,$this->pos, $count);
        $this->pos += $count;
        return $read;
    }
    /**
     * write the stream
     *
     * @param int $count number of bytes to read
     * @return content from pos to count
     */
    public function stream_write($data) {
        if(strlen($this->buffer) == 0) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return true if eof else false
     */
    public function stream_eof() {
        return ($this->pos > strlen($this->buffer));
    }
    /**
     * @return int the position of the current read pointer
     */
    public function stream_tell() {
        return $this->pos;
    }
    /**
     * Flush stream data
     */
    public function stream_flush() {
        $this->buffer = null;
        $this->pos = null;
    }
    /**
     * Stat the file, return only the size of the buffer
     *
     * @return array stat information
     */
    public function stream_stat() {
        $this->createBuffer($this->path);
        $stat = array(
            'size' => strlen($this->buffer),
        );
        return $stat;
    }
    /**
     * Stat the url, return only the size of the buffer
     *
     * @return array stat information
     */
    public function url_stat($path, $flags) {
        $this->createBuffer($path);
        $stat = array(
            'size' => strlen($this->buffer),
        );
        return $stat;
    }
    /**
     * Create the buffer by requesting the url through cURL
     *
     * @param unknown_type $path
     */
    private function createBuffer($path) {
        if($this->buffer) {
            return;
        }
        $this->ch = curl_init($path);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($this->ch, CURLOPT_USERPWD, USERPWD);
        $this->buffer = curl_exec($this->ch);
        $this->pos = 0;
    }
}

class Metodos{
    private $cliente;
    private $db;
    
    public function __construct(){
        
    }
    
     
    private function cortarDecimales($numero,$cantidadDecimales){
        return substr($numero, 0, (strpos($numero, '.')+$cantidadDecimales+1) );
    }
    
    private function cargarConexionDB(){
        include_once APPLICATION_PATH.'/models/Userinfo.php';
        $this->db = new Application_Model_Userinfo();
    }

    public function ConvertirCotizacion($parametro){
        $this->cargarConexionDB();
        try{   
            $this->InicializarWebservice();
            $OV = $this->cliente->SetSalesQuotationToSalesOrder($parametro);
            $this->db->kardexLog("COT a OV parametros: ".json_encode($parametro)." resultado: ".json_encode(array("msg"=>$OV->response)),json_encode($parametro),json_encode(array("msg"=>$OV->response)),1,'convertir COT a OV');
            return array("status"=>"Exito","msg"=>$OV->response);
        }
        catch(Exception $e){ 
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg2="";
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $Data){
                $msg2.=mb_convert_encoding($Data['DESCRIPTION'], "UTF-8");
                $msg .= mb_convert_encoding($Data['DESCRIPTION'], "UTF-8").'<br>';
            }
            $msg .= '</b>';
            $st=1;
            if(empty($msg2)){
                $st=2;
            }
            $this->db->kardexLog("COT a OV parametros: ".json_encode($parametro)." resultado: ".$msg,  json_encode($parametro).' - '.$msg, json_encode($e),$st,'convertir COT a OV');            
            return array("status"=>"Fallo","msg"=>$msg);
        }
    }

    public function getPriceItems($parametros){
        try{
            $this->cargarConexionDB();
            $parametros['_company']=COMPANY;
            $this->InicializarWebservice();
            $precio = $this->cliente->GetSalesPriceItem($parametros);
            $priceTemp = $precio->response;
            $this->db->kardexLog("parametros: ".  json_encode($parametros)." resultado: ".json_encode(array('precio'=>($priceTemp),'precio_iva'=>$priceTemp*1.16)),json_encode($parametros), json_encode(array('precio'=>($priceTemp),'precio_iva'=>$priceTemp*1.16)),1,'precios');
            return array('precio'=>($priceTemp),'precio_iva'=>$priceTemp*1.16);
        }
        catch(Exception $e){
            $this->db->kardexLog(json_encode(array('precio'=>0,'precio_iva'=>0,"error"=>$e->getTraceAsString())), $parametros, json_encode(array('precio'=>0,'precio_iva'=>0,"error"=>$e->getTraceAsString())),2,'precios');
            return array('precio'=>0,'precio_iva'=>0,"error"=>$e->getTraceAsString());
        }       
    }

    public function SetEncabezadoDynamics($parametro,$tipo){
        try{
            $this->InicializarWebservice();
            $this->cargarConexionDB();
            include('includes/Encabezado.php'); 
            $encabezado = new Encabezado($parametro,  $this->db);
            if ($tipo == 'ORDVTA'){
                $documentId = $this->cliente->SetSalesOrderHeader(array('_XMLSalesOrder'=>$encabezado->getEncabezadoXML($tipo)));
            }else{
                $documentId = $this->cliente->SetSalesQuotationOrderHeader(array('_XMLSalesOrder'=>$encabezado->getEncabezadoXML($tipo)));
                $this->db->kardexLog("lineas[".$tipo."] parametros: ".$encabezado->getEncabezadoXML($tipo)." resultado: ".json_encode($documentId),json_encode($parametro),  json_encode($documentId),1,'lineas');
            }
             return array('OV'=>$documentId->response,'encabezado'=>$encabezado);
        }
        catch(Exception $e){
            $this->cargarConexionDB();
            $this->db->kardexLog("lineas[".$tipo."] parametros: ".json_encode($parametro)." resultado: ".$e->getTraceAsString(),json_encode($parametro),  json_encode($e),2,'lineas');
            throw new Exception($e);
        }       
    }

    public function SetLineasDynamics($lineasRef,$OV,$tipo){
        $this->cargarConexionDB();
        try{            
            include('includes/LineaXML.php');   
            $lineas = new LineaXML($tipo,$OV);
            foreach($lineasRef AS $Data){
                $lineas->addLine($Data);                
            }
            $lineas->endLineaXml();
            $lineasArr = array('lineaXML' => $lineas->lineaXML);
            $OVResult = $this->ConfirmarOV($lineasArr,$tipo);
            $this->db->kardexLog("lineas[".$tipo."] parametros: ".json_encode($lineasRef)." idDoc:".$OV." resultado: ".$OVResult,json_encode($lineasRef), $OVResult,1,'lineas');
            return array("error"=>false,"result"=>$OVResult);
        }
        catch(Exception $e){
            $this->db->kardexLog("parametros: ".json_encode($lineasRef)." idDoc:".$OV." resultado: ".$e->getTraceAsString(),json_encode($lineasRef),  json_encode($e),2,'lineas');
            return array("error"=>true,"result"=>$e->getMessage());
        }
    }

    public function ConfirmarOV($lineas,$tipo){
        $this->cargarConexionDB();
        try{
            $OV="";
            $parametroWebService='';
            if ( is_array($lineas) && $lineas['lineaXML'] != '' ){
                $this->InicializarWebservice();
                if ($tipo == 'ORDVTA'){
                    $parametroWebService = array('_XMLSalesLines' =>$lineas['lineaXML']);
                    $OV = $this->cliente->SetSalesOrderLines($parametroWebService);
                }else{
                    $parametroWebService = array('_XMLQuotationLines' => $lineas['lineaXML']);
                    $OV = $this->cliente->SetSalesQuotationOrderLines($parametroWebService);
                }
            }
            return $OV->response;
        }
        catch(Exception $e){
            throw new Exception($e);
        }
    }

    public function SetRemision($parametros){
        $this->cargarConexionDB();
        $params = array('_SalesId'=> $parametros,'_company'=>COMPANY,'_Company'=>COMPANY);
        try{
            $this->InicializarWebservice();
            $remision = $this->cliente->SetSalesOrderPackingSlip($params);
            $this->db->kardexLog("",json_encode($params),json_encode(array("status"=>"Exito","msg"=>$remision->response)),1,'remision');
            return json_encode(array("status"=>"Exito","msg"=>$remision->response));
        }
        catch(SoapFault $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = 'Exception en Dynamics: '; 
            $msg2="";
            foreach ($res AS $Data){
                $msg2.=$Data['DESCRIPTION'];
                $msg .= $Data['DESCRIPTION'].'<br>';
            }
            $st=1;
            if(empty($msg2)){
                $st=2;
            }
            $this->db->kardexLog("",json_encode($params),json_encode(array("status"=>"Fallo","msg"=>$msg)),$st,'remision');
            throw new Exception (json_encode(array("parametros"=>$params,"status"=>"Fallo","msg"=>$msg,"error"=>$e->getMessage())));
        }
       
    }
    
    public function mainAction($encabezado,$lineas,$tipo){
        
        $encabezadoResult = $this->SetEncabezadoDynamics($encabezado,$tipo);
        $enc = $encabezadoResult;
        
        foreach ($lineas as &$line){
            $line['sitio'] =  $enc['encabezado']->SiteId;
            $line['almacen'] = $enc['encabezado']->LocationId;
        }
        //return $lineas;
        $this->SetLineasDynamics($lineas,$enc['OV'],$tipo);
        
        $remision = $this->SetRemision($enc['OV']);
        
        return $remision;
    }

    public function crearCliente($parametros){
        try{
            $this->InicializarWebservice();
            $xml = $this->getCustomerXML($parametros);
            $parametroWebService = array('_XMLCustomer' => $xml);
            $OV = $this->cliente->CreateCustomer($parametroWebService);
            return json_encode(array("status"=>"Exito","msg"=>$OV->response));
        }
        catch(SoapFault $e){
            $this->cargarConexionDB();
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            $msg2 ="";
            foreach ($res AS $Data){
                $msg2.=$Data['DESCRIPTION'];
                $msg .= $Data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
            $st=1;
            if(empty($msg2)){$st=2;}
            $this->db->kardexLog("",json_encode($parametros),json_encode(array("status"=>"Fallo","msg"=>$msg)),$st,'ALTACLIENTE');
            throw new Exception (json_encode(array("status"=>"Fallo","msg"=>$msg,"Exception"=>$e->getMessage())));
        }
        
    }
    
    public function setSalesOrderCreditLimit($_SalesId, $_user){
        try{
            $this->InicializarWebservice();
            $parametroWebService = array('_SalesId' => $_SalesId,'_user' => $_user,'_company'=>COMPANY);
            $OV = $this->cliente->SetSalesOrderCreditLimit($parametroWebService);
            return $OV->response;
        }
        catch(Exception $e){
            $this->cargarConexionDB();
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $Data){
                $msg .= $Data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';            
            throw new Exception (json_encode(array("status"=>"Fallo","msg"=>$msg)));
        }
    }

    private function getCustomerXML($DatosCliente){
        if ( is_array($DatosCliente) && !empty($DatosCliente) ){
            $Address = '';$xml = '';$i = 1;
            foreach($DatosCliente[0]['Address'] AS $address){
                $Address .= "   <Address ";
                $Address .= "       DirLine='".$i."' ";
                $Address .= "       DirName='".$DatosCliente[0]['Name']."' ";
                $Address .= "       DirStreet='".$address['calle'].' '.$address['numero']."' ";
                $Address .= "       DirStreetNum='".$address['numero']."' ";
                $Address .= "       DirCity= '".$address['ciudad']."'";
                $Address .= "       DirCounty='".$address['colonia']."' ";
                $Address .= "       DirZipCode='".$address['cp']."' ";
                $Address .= "       DirRoleType='".$address['proposito']."'>";
                $Address .= "   </Address>";
                $i++;
            }
            $contactos = '';
            $i = 1;
            foreach($DatosCliente[0]['Contact'] AS $datoContactos){
                $contactos .= "   <Contact ";
                $contactos .= "       ContactLine='".$i."' ";
                $contactos .= "       ContactDescription='".$datoContactos['descripcion']."'  ";
                $contactos .= "       ContactType='".$datoContactos['formaContacto']."'  ";
                $contactos .= "       ContactLocator='".$datoContactos['telefono']."' ";
                $contactos .= "       ContactLocatorExtension = '".$datoContactos['extension']."' ";
                $contactos .= "       ContactIsPrimary='".$datoContactos['isPrimary']."'";
                $contactos .= "       ContactRoleType='".$datoContactos['proposito']."'>";
                $contactos .= "   </Contact>";
                $i++;
            }
            $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<Customer Version="1.0">';
            $xml .= '<Company>'.COMPANY.'</Company>';
            $xml .= "<Name>".$DatosCliente[0]['Name']."</Name>";
            $xml .= "<CustGroup>".$DatosCliente[0]['CustGroup']."</CustGroup> ";
            $xml .= "<CurrencyCode>".$DatosCliente[0]['CurrencyCode']."</CurrencyCode> ";
            $xml .= "<CompanyType>".$DatosCliente[0]['CompanyType']."</CompanyType> ";
            $xml .= "<RFC>".$DatosCliente[0]['RFC']."</RFC>";
            $xml .= "<SalesDistrict>".$DatosCliente[0]['SalesDistrict']."</SalesDistrict>";
            $xml .= "<SiteId>".$DatosCliente[0]['SiteId']."</SiteId>";
            $xml .= "<LocationId>".$DatosCliente[0]['LocationId']."</LocationId>";
            $xml .= "<LineDisc>".$DatosCliente[0]['LineDisc']."</LineDisc>";
            $xml .= "<Addresses>".$Address."</Addresses>";
            $xml .= "<Contacts>".$contactos."</Contacts>";
            $xml .= "</Customer>";
        }
        return $xml;
    }

    private function InicializarWebservice(){
        define('USERPWD', 'atp\\aosuser:AdminAX2012');
        // we unregister the current HTTP wrapper
        stream_wrapper_unregister('http');
        // we register the new HTTP wrapper
        $stream = new NTLMStream();
        //echo ('NTLMStream');
        stream_wrapper_register('http', 'NTLMStream') or die("Failed to register protocol");
        // Initialize Soap Client
        $client = new NTLMSoapClient(WEB_SERVICE_URL);
        //regresa la instancia de webservice
        $this->cliente = $client;
    }
    public function createFactura($xml) {
        $this->cargarConexionDB();
        try {
            $resultado=array();
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->invoiceSalesOrderPackingSlip(array('_salesOrder'=>$xml));   
            if($res->response=="No se puede facturar esta remisión para esta OV"){
                $resultado=array("resultado"=>"bad","respuesta"=>$res->response);
            }
            else {
                $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            }
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'factura');
            return $resultado; 
        } 
        catch (Exception $e) {
            
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
            $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'factura');
            throw new Exception ($msg);
        }        
    }
    public function createDiario($xml){
        $this->cargarConexionDB();
        try{
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->journalPayment(array('_salesOrder'=>$xml));   
            $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'diario');
            return $resultado; 
        }
        catch (Exception $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
           $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'diario');
           return $arr=["resultado"=>$msg,"error"=>$e];  
        }
    }
    
    public function editDiario($xml){
        $this->cargarConexionDB();
        try{
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->JournalPaymentEdit(array('_xml'=>$xml));   
            $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'diarioEditar');
            return $resultado;
        }
        catch (Exception $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
           $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'diarioEditar');
           return array("resultado"=>"not","respuesta"=>$msg,"exeption"=>$e);
        }
    }
    public function cerrarDiario($diario) {
        $this->cargarConexionDB();
        try{
            $this->InicializarWebserviceFacturacion();
            $res=$this->cliente->JournalPaymentPost(array('_parm'=>COMPANY."|$diario"));   
            $resultado=array("resultado"=>"ok","respuesta"=>$res->response); 
            $this->db->kardexLog("",json_encode($xml),json_encode($resultado),1,'diarioCerrar');
            return $resultado;
        }
        catch (Exception $e){
            $q= $this->db->db->_adapter->query(LAST_ERROR);
            $q->execute();
            $res=$q->fetchAll(PDO::FETCH_ASSOC);
            $msg = '<b>Exception en Dynamics: ';
            foreach ($res AS $data){
                $msg .= $data['DESCRIPTION'].'<br>';
            }
            $msg .= '</b>';
           $this->db->kardexLog("",json_encode($xml),json_encode($msg),2,'diarioCerrar');
           return array("resultado"=>"not","respuesta"=>$msg,"exeption"=>$e);
        }
    }

    private function InicializarWebserviceFacturacion(){
        define('USERPWD', 'atp\\aosuser:AdminAX2012');
        // we unregister the current HTTP wrapper
        stream_wrapper_unregister('http');
        // we register the new HTTP wrapper
        $stream = new NTLMStream();
        //echo ('NTLMStream');
        stream_wrapper_register('http', 'NTLMStream') or die("Failed to register protocol");
        // Initialize Soap Client
        $client = new NTLMSoapClient(WEB_SERVICE_INVOICE_URL);
        //regresa la instancia de webservice
        $this->cliente = $client;
    }
}
