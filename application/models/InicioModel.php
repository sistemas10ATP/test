<?php

/**
* Modelo de inicio controller en esta parte va todo lo relacionado con al capa de negocio
* como lo por ejemplo metodos en los cuales se hacen consultas directas a la base de datos,
 * web services
 * @author Francisco Delgado <packo6300@gmail.com>
* @param array $result con los datos necesarios para llenar los datos del cliente.
*/
set_time_limit(0);
class Application_Model_InicioModel {
    public $db;
    public $_adapter;
    public $log;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->log = new Application_Model_Userinfo();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }
    /**
     * 
     * @return type
     */
    public function getItems(){
        $query = $this->_adapter->query(GET_ITEMS2);
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $result['noresult'] = "NoResults";
        }
        return $result;
    }
    /**
     * 
     * @return type
     */
    public function getUsoCDFI(){
        $query = $this->_adapter->query("select a.CFDIUSECODE, a.DESCRIPTION from STF_CFDI_CFDIuse a;");
        $query->execute();
        $re=$query->fetchAll();
        return $re;        
    }
    /**
     * 
     * @return type
     */
    public function getPayTerm(){
        $query = $this->_adapter->query("select p.PAYMTERMID,p.DESCRIPTION from PaymTerm p where p.DATAAREAID='".COMPANY."';");
        $query->execute();
        $re=$query->fetchAll();
        return $re;  
    }
    public function getPayMode() {
        $query = $this->_adapter->query("select p.PAYMMODE,p.name from CustPaymModeTable p where p.DATAAREAID='".COMPANY."' and p.PAYMMODE not in('30','na');");
        $query->execute();
        $re=$query->fetchAll();
        return $re;
    }
    /**
     */
    public function getSucursal(){
        try{
            return $this->db->Query("select nombre from ".INTERNA.".dbo.sucursal where idsuc = :id",array(":id"=>SUCURSAL));
        }
        catch(Exception $e){
            return $e;
        }
    }

    /**
     * 
     * @return type
     */
    public function getItemsCommon(){
        $query = $this->_adapter->query(GET_ITEMS_COMMON);
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $result['noresult'] = "NoResults";
        }
        return json_encode($result);
    }
    /**
     * 
     * @param type $articulo
     * @return type
     */
    public function getProductDetail($articulo){
        $query = $this->_adapter->prepare(GET_PRODUCT_DETAIL);
        $query->bindParam(1,$articulo);
        $query->execute();
        return $query->fetchAll();
    }
    /**
     * 
     * @param type $cliente
     * @return type
     */
    public function getClients($cliente = '') {
        if ($cliente!='') {
            $query = $this->_adapter->prepare(CLIENTE_POR_CODIGO);
            $query->bindParam(1,$cliente);
        } 
        else {
            if(empty(COMPANY)){
                $CLIENTES="SELECT DISTINCT T0.accountnum 'ClaveCliente' FROM custtable T0 INNER JOIN dirpartytable T1 ON T0.party = T1.recid LEFT JOIN logisticslocation T2 ON T1.primaryaddresslocation = T2.recid AND T2.ispostaladdress = '1' LEFT JOIN logisticspostaladdress T3 ON T2.recid = T3.location AND Dateadd(hour, -7, T3.validfrom) < Getdate() AND T3.validto > Getdate() where DATAAREAID='ATP';";
                $query = $this->_adapter->prepare($CLIENTES);
            }
            else{
                $query = $this->_adapter->prepare(CLIENTES_INICIAL);
            }
        }
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)){$result['noresult'] = "NoResults";} 
        return $result;
    }
    /**
     * inserta en la tabla de kardex para poder llevar estadisticas de uso de   inax
     * @param String $type ya sea CTZN para cotizacion o ORDVTA orden de venta
     */
    public function setKardex($type) {
       try{
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(INSERT_KARDEX);
        $query->bindParam(1,$_SESSION['userInax']);
        $query->bindParam(2,$_SESSION['nomuser']);
        $query->bindParam(3,$type);
        $query->execute();
       } catch (Exception $ex) {
           echo $ex->getMessage();
       }
       
    }
    /**
     * 
     * @return string
     */
    public function getSitios() {
        $optSitio = '<option value="">Selecciona...</option>';
        if ($this->view->sitios[0] != 'NoResults') {
            foreach ($this->view->sitios as $Data) {
                $optSitio .= '<option value="' . $Data['SITEID'] . '">' . $Data['NAME'] . '</option>';
            }
        } else {
            $optSitio = '<option value="">Error (sin registros)</option>';
        }
        return $optSitio;
    }
    /**
     * 
     * @param type $sitio
     * @return type
     */
    public function getSitio($sitio) {
        $query = $this->_adapter->prepare(SITIO_FILTRO);
        $query->bindParam(1,$sitio); 
        $query->execute();
        $result = $query->fetchAll();
        $almacenes[0] = "NoResults";
        if (!empty($result)){
            foreach ($result as $k => $v) { $almacenes[$k] = $v;}
        }
        return $almacenes;
    }
    /**
     * 
     * @param type $clave
     * @return type
     */
    public function getClientByClave($clave) {
        $query = $this->_adapter->prepare(INICIO_CLVECLTE);
        $query->bindParam(1,$clave); 
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) { 
             $result['noresult'] = "NoResults";       
        }
        return $result;
    }
    /**
     * 
     * @param type $almacen
     * @param type $item
     * @return type
     */
    public function getFraccionado($almacen,$item) {
        $query = $this->_adapter->prepare(GET_FRACCIONADOS);
        $query->bindParam(1,$almacen); 
        $query->bindParam(2,$item); 
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $fraccionado['noresult'] = "NoResults";
        }
        foreach ($result as $k => $v) { $fraccionado[$k] = $v; }
        return $fraccionado;        
    }
    /**
     * 
     * @param type $cliente
     * @return type
     */
    public function getDirecciones($cliente) {
        $query = $this->_adapter->prepare(INICIO_CLVECLTE_DETALLE);
        $query->bindParam(1,$cliente);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $resultado = array('resultado' => 'NoResults');
        if (!empty($result)) {
            $resultado = array('resultado' => '1','data' => $result,'size' => count($result));
        }
        return json_encode($resultado);        
    }
    /**
     * 
     * @param type $ov
     * @return type
     */
    public function getCredito($ov) {
        $query = $this->_adapter->prepare(GET_CREDITO);
        $query->bindParam(1,$ov);
        $query->execute();
        $result = $query->fetchAll();
        return $result;
    }
    /**
     * 
     * @param type $cliente
     * @return string
     */
    public function getUltimasVentas($cliente) {
        $query = $this->_adapter->prepare(LAST_VENTAS);
        $query->bindParam(1,$cliente);
        $query->execute();
        $result = $query->fetchAll();
        if (! empty($result)) {
            $result=json_encode($result);
        } else {
            $result='NoResults';
        }
        $this->log->kardexLog("ULTIMAS VENTAS parametros: ".$cliente." resultado: ", $cliente,'',1,'Ultimas Ventas');
        return $result;
    } 
    /**
     * 
     * @param type $type
     * @param type $doc
     * @return string
     */
    public function getArchivoAdjunto($type,$doc) {
        if ($type == 'CTZN') {
            $query = $this->_adapter->prepare(GET_ARCHIVO_ADJUNTO_COT);
        } 
        else {
            $query = $this->_adapter->prepare(GET_ARCHIVO_ADJUNTO_OV);
        }
        $query->bindParam(1,$doc);
        $query->execute();
        $result = $query->fetchAll();
        return $result;                
    }
    /**
     * 
     * @return type
     */
    public function getDataNegados() {
        $query = $this->_adapter->prepare(GET_DATA_NEGADOS);
        $query->execute();
        $result = $query->fetchAll();
        if (!empty($result)) {
           $res=json_encode(array('result' => 'OK', 'data' => json_encode($result)));
        } else {
           $res=json_encode(array('result' => 'FAIL','data' => '["NoResults","NoResults","NoResults","NoResults","NoResults","NoResults","NoResults","NoResults","NoResults"]'));
        }
        return $res;
    }
    /**
     * 
     * @param type $docType
     * @param type $docId
     * @return string
     */
    public function getRefreshLines($docType,$docId) {
        if ($docType == 'CTZN') {
            $query = $this->_adapter->prepare(GET_REFRESH_LINES_COT);
        } else {
            $query = $this->_adapter->prepare(GET_REFRESH_LINES_OV);
        }
        $query->bindParam(1,$docId);
        $query->execute();
        $result = $query->fetchAll();
        if (! empty($result)) {
            $datos = $result;
        } else {
            $datos[0] = 'SinResultados';
        }
        return $datos;
    }
    /**
     * 
     * @param type $ov
     * @return string
     */
    public function getStatusAlerta($ov) {
        $query = $this->_adapter->prepare(GET_STATUS_ALERTA);
        $query->bindParam(1,$ov);
        $query->execute();
        $result = $query->fetchAll();
        if (! empty($result)) {
            $resultado = array('resultado' => $result);
        } else {
            $resultado = array('resultado' => 'NoResults');
        }
        return $resultado;
    }
    /**
     * 
     * @param type $ov
     * @return string
     */
    public function getStatusBloqueo($ov) {
        $query = $this->_adapter->prepare(GET_STATUS_BLOQUEO);
        $query->bindParam(1,$ov);
        $query->execute();
        $result = $query->fetchAll();
        if (! empty($result)) { $resultado = array('resultado' => $result ); } 
        else { $resultado = array('resultado' => 'NoResults' ); }
        return $resultado;
    }
    /**
     * 
     * @return type
     */
    public function getTipoCambio() {
        $query = $this->_adapter->query(TIPO_CAMBIO);
        $query->execute();
        $result = $query->fetchAll(); 
        $tp=$result[0]['tipoCambio'];
        return $tp;
    }
    /**
     * 
     * @param type $cliente
     * @return string
     */
    public function getInvoiceDeliveryAddress($cliente){
        $query = $this->_adapter->prepare(GET_INVOICE_DELIVERY_ADDRESS);
        $query->bindParam(1,$cliente);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if (! empty($result)) {
            $dire = array( 'factura' => '','entrega' => '');
            foreach ($result as $dirs) {
                if ($dirs['NAME'] == 'Invoice') {
                    $dire['factura'] = $dirs['RECID'];
                }
                if ($dirs['NAME'] == 'Delivery') {
                    $dire['entrega'] = $dirs['RECID'];
                }
            }
            return $dire;
        } else {
            return 'NoResults';
        }
    }
    /**
     * 
     * @param type $item
     * @param type $sitio
     * @return type
     */
    public function getExistenciaLote($item,$sitio) {
        $query = $this->_adapter->prepare(EXISTENCIA_LOTE);
        $query->bindParam(1,$item);
        $query->bindParam(2,$sitio);
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)) {
            $datos[0]['Articulo'] = "";
        }
        foreach ($result as $k => $v) {
            $datos[$k] = $v;
        }
        return $datos;
    }
    
    public function getMinimoVenta($item){
        $queryStr = "select T1.ITEMID, T2.INVENTSITEID,T1.MULTIPLEQTY from INVENTITEMSALESSETUP t1
                left JOIN INVENTDIM t2 on t2.INVENTDIMID = t1.INVENTDIMID
                where t1.DATAAREAID = '".COMPANY."'
                and t2.DATAAREAID = '".COMPANY."'
                and ITEMID = ?
                and t1.MULTIPLEQTY != 0.0000000;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$item);
        $query->execute();
        $result = $query->fetchAll();
         if (empty($result)) {
            return false;
        }
        foreach ($result as $k => $v) {
            $datos[$k] = $v;
        }
        return $datos;
    }
    
    /**
     *regresa las existencias segun el articulo con sus variantes
     * @return JSON con los datos
    */
    public function getExistencias($item,$sitio,$almacen,$localidad,$documenType,$company) {
        $query = $this->_adapter->prepare("EXECUTE devGetExistenciasExiCompany ?,?,?,?,?,?;");
        $query->bindParam(1,$item);
        $query->bindParam(2,$sitio);
        $query->bindParam(3,$almacen);
        $query->bindParam(4,$localidad);
        $query->bindParam(5,$documenType);
        $query->bindParam(6,$company);
        $query->execute();
        $result = $query->fetchAll();
        if (empty($result)){ $datos['noresult'] = "NoResults"; }
        foreach ($result as $k => $v) { $datos[$k] = $v; }
        return  json_encode(array('datos' => $datos));
    }
    /**
    * obtiene los datos del cliente de la cotizacion
    * @param array $param con los datos necesarios para llenar los datos del cliente.
    * @return array  con los datos del cliente
    */
    public function getCTZNDataClient($param) {
        $query = $this->_adapter->prepare(GET_COTIZACION_CLIENT_DATA);
        $query->bindParam(1,$param);
        $query->bindParam(2,$param);
        $query->execute();
        $r=$query->fetchAll();
        return $r;
    }
    /**
    * trae los datos de la cotizacion 
    * @abstract
    * @param array $param con los datos necesarios para obtener los datos 
    * @return decimal
    * @throws Exception si la operacion falla regresara cero '0'
    */
    public function getCTZNDataContent($param){
        $query2 = $this->_adapter->prepare(GET_COTIZACION_CONTENT_DATA);
        $query2->bindParam(1,$param);
        $query2->execute();
        $result =$query2->fetchAll();
        return $result;
    }
    /**
     * 
     */
    public function getPriceFromDB($cliente,$item,$moneda,$company,$cargo,$err=null) {
        $listaPrecios=$listaDescuento=$porcentaje=$precioLista='';
        // consulta lista de precios y descuento del cliente
        $q = $this->_adapter->prepare("select t0.PRICEGROUP,t0.LINEDISC from CUSTTABLE t0 where t0.ACCOUNTNUM=?;");
        $q->bindParam(1,$cliente);
        $q->execute();
        $r =$q->fetchAll();
        foreach ($r as $k => $v) {
            $listaPrecios=$v['PRICEGROUP'];
            $listaDescuento=$v['LINEDISC'];
        }
        //consulta precio base 
        $q2 = $this->_adapter->prepare("select top 1 t0.AMOUNT from PriceDiscTable t0 where t0.ITEMRELATION= ? and t0.ACCOUNTRELATION= ? and t0.DATAAREAID= ? order by t0.MODIFIEDDATETIME desc;");
        $q2->bindParam(1,$item);
        $q2->bindParam(2,$listaPrecios);
        $q2->bindParam(3,$company);
        $q2->execute();
        $r2 =$q2->fetchAll();
        foreach ($r2 as $k => $v) {
            $precioLista=$v['AMOUNT'];
        }
        // 
        $q3 = $this->_adapter->prepare("select t0.PERCENT1,t0.FROMDATE,t0.TODATE,t0.QUANTITYAMOUNTFROM,t0.QUANTITYAMOUNTTO from PriceDiscTable t0 where t0.ITEMRELATION=? and t0.ACCOUNTRELATION=? and t0.CURRENCY=? and t0.QUANTITYAMOUNTFROM=0;");
        $q3->bindParam(1,$item);
        $q3->bindParam(2,$listaDescuento);
        $q3->bindParam(3,$moneda);
        $q3->execute();
        $r3 =$q3->fetchAll();
        foreach($r3 as $k => $v){
            $porcentaje=$v['PERCENT1'];
        }
        $price=($precioLista-($precioLista*($porcentaje/100)));
        if((integer)$cargo!=0){
            $price=$price+($price*((double)$cargo/100)); 
        }
        $e="";
        if(!empty($err)){
            $e=$err->getTraceAsString();
        }
        return array('preciocargo'=>($price),'precioiva'=>$price*1.16,"error"=>$e,"lista_precios"=>$listaPrecios,"descuento"=>$listaDescuento,"precio_lista"=>$precioLista); //round($price, 3);
    }
    /**
    * setea los datos del cliente en la cotizacion
    * @param array $result con los datos necesarios para llenar los datos del cliente.
    */
    public function setCTZNDataClient($result){
        $_POST['PAYMENT']=$result[0]['PAYMENT'];
        $_POST['PAYMMODE'] = $result[0]['PAYMMODE'];
        $_POST['INVENTSITEID'] = $result[0]['INVENTSITEID'];
        $_POST['INVENTLOCATIONID'] = $result[0]['INVENTLOCATIONID'];
        $_POST['CURRENCYCODE'] = $result[0]['CURRENCYCODE'];
        $_POST['DLVMODE'] = $result[0]['DLVMODE'];
        $_POST['DLVTERM'] = $result[0]['DLVTERM'];
        $_POST['WORKERSALESTAKER'] = $result[0]['WORKERSALESTAKER'];
        $_POST['WORKERSALESRESPONSIBLE'] = $result[0]['WORKERSALESRESPONSIBLE'];
        $_POST['STF_OBSERVATIONS'] = $result[0]['STF_OBSERVATIONS'];
        $_POST['CUSTOMERREF'] = $result[0]['CUSTOMERREF'];
        $_POST['CUSTPURCHASEORDER'] = $result[0]['CUSTPURCHASEORDER'];
        $_POST['BANKACCOUNT'] = $result[0]['BANKACCOUNT'];
    }
    /**
     * @param array $params arreglo con los datos necesarios para consultar precios desde el web service directo de dynamics
     * @return array tipo JSON con el precio y el precio con iva
     * @throws Exception SOAP Exception si la operacion falla regresara cero '0'
     */
    public function checarPreciosNew($params){
        try {
            $ws= new Metodos();
            $params['_company']=COMPANY; 
            $result = $ws->getPriceItems($params);
            return $result;
        } catch (Exception $objError) {
           throw new Exception ($objError->getTraceAsString());
           //return $this->getPriceFromDB($params['_CustAccount'], $params['_ItemId'],$params['_currencyCode'],COMPANY, $params['_PercentCharges'],$objError);
        }
    }
    /**
     * @param String $param Recibe un String con la OV a convertir 
     * @return String Regresa el id de la remision generada
     */
    public function setNewRemision($param) {
        $ws= new Metodos();
        $ov = $ws->SetRemision($param);
        return $ov;
    }
    /**
     * 
     * @param type $params
     * @param type $tipo
     * @return int
     */    
    public function setHeader($params,$tipo){
        try {
            $ws= new Metodos();
            $ov = $ws->SetEncabezadoDynamics($params,$tipo);
            return $ov;
        } catch (Exception $objError) {
            throw new Exception($objError);
        }
    }
    public function setFactura($ov,$remision,$ordenCliente,$refCliente,$comentariosCabecera,$direccion,$usoCFDI,$modoPago,$pago){
        try {
            $ws= new Metodos();            
            $lines=$this->getInvoiceLines($ov);
            $line="";
            $user=$this->getIdByNetWorkAlias($_SESSION['userInax']);
            foreach ($lines as $k => $v) {
                $line.='<Line  LineNum="'.$v['LINENUM'].'" SalesPrice="'.$v['SALESPRICE'].'" LinePercent="'.$v['LINEPERCENT'].'" TaxGroup="'.$v['TAXGROUP'].'" ></Line>';
            }        
            $xml=  '<?xml version="1.0" encoding="utf-8"?>
                    <SalesOrder>
                        <Table>
                            <Company>' . COMPANY . '</Company><SalesId>'.$ov.'</SalesId><PackingSlipId>'.$remision.'</PackingSlipId><PurchOrderFormNum>'.$ordenCliente.'</PurchOrderFormNum><CustomerRef>'.$refCliente.'</CustomerRef>
                            <Observations>'.$comentariosCabecera.'</Observations><Description>'.$direccion.'</Description><STF_CFDIuseCode>'.$usoCFDI.'</STF_CFDIuseCode><STF_RelType></STF_RelType><UUID></UUID><PaymMode>'.$modoPago.'</PaymMode><Payment>'.$pago.'</Payment>
                            <User>'.$user[0]['ID'].'</User>
                        </Table>
                        <Lines>'.$line.'</Lines>
                    </SalesOrder>';
            return $ws->createFactura($xml);
        } catch (Exception $objError) {
            return array("resultado"=>"bad","respuesta"=>$objError->getMessage());
        }   
    }
    /**
     * 
     * @param type $params
     * @param type $idDoc
     * @param type $tipo
     * @return string
     */
    public function setLineas($params,$idDoc,$tipo) {
         try {
            $ws= new Metodos();
            $ov = $ws->SetLineasDynamics($params,$idDoc,$tipo); 
            return $ov['result'];
        } catch (Exception $objError) {
            $d=$objError;
            return 'FAIL';
        }       
    }
    /**
    * Obtiene los ultimos 4 digitos de la tarjeta con la cual se esta cotizando,
    * de la tabla kardexventas en vase a el documento original
    * @example path  $model->getCuentaPagoTarjeta('ATP-842638');
    * @param string $document con los datos necesarios para verificar un precio 
    * @return string
    */
    public function getCuentaPagoTarjeta($document) {
        $query2 = $this->_adapter->prepare(TARJETA_PAGO_COTIZACION);
        $query2->bindParam(1,$document);
        $query2->execute();
        $result =$query2->fetchAll();
        return $result[0]['CPAGO'];
    }
    /**
    * obtiene los datos necesarios para editar la OV
    * @example path  $model->getDataForEditOV('ATP-842638');
    * @param string $ov con los datos necesarios para verificar un precio 
    * @return string
    */
    public function getDataForEditOV($ov){
        $query = $this->_adapter->prepare(EDITAR_OV);
        $query->bindParam(1,$ov);
        $query->execute();
        $result=$query->fetchAll();
        if(empty($result)){
            $result[0] = "NoResults";
        }
        return  $result;
    }
    /**
     * 
     * @param array $param
     */
    public function setCot2Ov($param) {
           $ws= new Metodos();
           $ov = $ws->ConvertirCotizacion($param);            
           return $ov;  
    }
    /**
    * obtiene los datos necesarios para editar la OV
    * @example path      
    * @param string $item 
    * @return string
    */
    public function getDataForAutoComplete($item){
        $query = $this->_adapter->prepare("SELECT TOP 100 T0.ITEMID 'Articulo',T1.NAME 'Nombre',T2.UNITID 'Unidad',T4.BLOCKSALESPRICES 'PrecioBloqueado', '1' Ordenacion FROM INVENTTABLE T0 INNER JOIN ECORESPRODUCTTRANSLATION T1 ON T0.PRODUCT=T1.PRODUCT INNER JOIN INVENTTABLEMODULE T2 ON T0.ITEMID=T2.ITEMID INNER JOIN INVENTITEMGROUPITEM T3 ON T0.ITEMID=T3.ITEMID AND T3.ITEMDATAAREAID='".COMPANY."'  INNER JOIN INVENTITEMGROUP T4 ON T3.ITEMGROUPID=T4.ITEMGROUPID AND T4.DATAAREAID='".COMPANY."' WHERE T0.DATAAREAID='".COMPANY."' AND T2.MODULETYPE='2' AND T0.ITEMID LIKE ('?%') UNION SELECT TOP 100 T0.ITEMID 'Articulo',T1.NAME 'Nombre',T2.UNITID 'Unidad',T4.BLOCKSALESPRICES 'PrecioBloqueado', '2' Ordenacion FROM INVENTTABLE T0 INNER JOIN ECORESPRODUCTTRANSLATION T1 ON T0.PRODUCT=T1.PRODUCT INNER JOIN INVENTTABLEMODULE T2 ON T0.ITEMID=T2.ITEMID INNER JOIN INVENTITEMGROUPITEM T3 ON T0.ITEMID=T3.ITEMID AND T3.ITEMDATAAREAID='".COMPANY."' INNER JOIN INVENTITEMGROUP T4 ON T3.ITEMGROUPID=T4.ITEMGROUPID AND T4.DATAAREAID='".COMPANY."' WHERE T0.DATAAREAID='".COMPANY."' AND T2.MODULETYPE='2'  AND T1.NAME LIKE '%?%' ORDER BY 5,T0.ITEMID;");
        $query->bindParam(1,$item);
        $query->bindParam(2, $item);
        $query->execute();
        $result = $query->fetchAll();                    
        $datos['noresult'] = "NoResults";
        if (!empty($result)) {
            $datos=$result;
        }
        return  $datos;
    }
    /**
     * funcion para convertir una cotizacion a una orden de venta   
     * @param String $doc folio del documento
     * @param String $usuario Nombre de usuario de la sesion   
     */
    public function setCotToOv($doc,$usuario){
        try {
            $st="";
            $ws= new Metodos();
            $credito = $ws->setSalesOrderCreditLimit($doc,$usuario);
            if ($credito === "") {
                $result = $this->getCredito($doc);
                $bloqueo = $result[0]['BLOCKED'];
                if($bloqueo === '0') {
                    $st = 'OK';
                } else if ($bloqueo == '1') {
                    $st = 'FAIL_BLOCK';
                    }
            }else {
                $st="bloqueado";
            }
            $this->log->kardexLog("Limite de credito parametros: ".$doc."-".$usuario." resultado: ".  json_encode(array("res"=>$st,"msj"=>$credito)),$doc,json_encode(array("res"=>$st,"msj"=>$credito)),1,'Limite de credito');
            return array("res"=>$st,"msj"=>$credito);
        } catch (Exception $e) {
            $this->log->kardexLog("Limite de credito parametros: ".$doc."-".$usuario." resultado: ".  json_encode(array("res"=>'error',"msj"=>'Intente de nuevo si el problema persiste verifique con sistemas','exception'=>$e)),$doc,json_encode(array("res"=>$st,"msj"=>$credito)),1,'Limite de credito');
            return array("res"=>'error',"msj"=>'Intente de nuevo si el problema persiste verifique con sistemas','exception'=>$e);
        }
    }
    /**
     * regresa si el precio esta bloqueado
     */
    public function isPriceBloked($item){
        $query = $this->_adapter->prepare(IS_PRICE_BLOKED);
        $query->bindParam(1,$item);
        $query->execute();
        $b="NO";
        $result = $query->fetchAll();
        if (empty($result)){
            $b="SI";
            $result=array("ITEMID"=>"$item","BLOCKSALESPRICES"=>"1");
        }
        $this->log->kardexLog("Precio Bloqueado: ".$item." resultado: ".$b, $item,  json_encode($result),1,'PrecioBloqueado');
        return $result;
    }
    /**
     * 
     * @param type $ov
     * @param type $trans
     * @param type $sitio
     * @return type
     */
    public function getUltimasVentas2($ov,$trans,$sitio=null) {
        if ($trans == 'ORDVTA') {
            $query = $this->_adapter->prepare(ORDVTA);
            $query->bindParam(1,$ov);
        } else if ($trans == 'CTZN') {
            $query = $this->_adapter->prepare(CTZN);
            $query->bindParam(1,$ov);
            
        } else if ($trans == 'ORDVTADET') {
            $query = $this->_adapter->prepare("SELECT SALES.LINENUM,SALES.SALESID,SALES.ITEMID,SALES.NAME,SALES.QTYORDERED,SALES.SALESUNIT, SUM(INVSUM.AVAILPHYSICAL) FisicaDisponible FROM SALESLINE SALES 
                            INNER JOIN INVENTSUM INVSUM ON (INVSUM.ITEMID = SALES.ITEMID) INNER JOIN INVENTDIM INVDIM ON (INVDIM.INVENTDIMID = INVSUM.INVENTDIMID)
                            WHERE SALES.SALESID = '$ov' AND INVDIM.INVENTSITEID= '$sitio' AND INVDIM.INVENTLOCATIONID LIKE '%CONS' GROUP BY SALES.LINENUM,SALES.SALESID,SALES.ITEMID,SALES.NAME,SALES.QTYORDERED,SALES.SALESUNIT;");
        }
        $query->execute();
        $result = $query->fetchAll();
        return $result;
    }
    /**
     * 
     * @param type $user
     * @param type $ov
     * @return type
     */
    public function getDireccionesCliente($user,$ov) {
         $queryCliente = $this->_adapter->prepare(DATOS_ETIQUETA);
         $queryCliente->bindParam(1,$user);
         $queryCliente->bindParam(2,$ov);
         $queryCliente->execute();
         $resultOV = $queryCliente->fetchAll();
         $clte = $resultOV[0]['CUSTACCOUNT'];
         $query = $this->_adapter->prepare(GET_INVOICE_DIREC);
         $query->bindParam(1,$clte);
         $query->execute();
         $direcc=$query->fetchAll();
         return $direcc;
    }
    /**
     * 
     * @param type $ov
     * @return type
     */
    public function getInvoiceLines($ov) {
         $queryCliente = $this->_adapter->prepare(GET_INVOICE_LINES);
         $queryCliente->bindParam(1,$ov);
         $queryCliente->execute();
         return $queryCliente->fetchAll();   
    }
    
    public function getPayModeByOV($ov){        
        return $this->db->QueryResulSet(GET_PAYMODE_FROM_OV, [":id"=>$ov]);
    }
    
    /**
     * 
     * @param type $user
     * @return type
     */
    function getIdByNetWorkAlias($user) {
        $queryCliente = $this->_adapter->prepare(USER_BY_NETWORK_ALIAS);
         $queryCliente->bindParam(1,$user);
         $queryCliente->execute();
         return $queryCliente->fetchAll(PDO::FETCH_ASSOC);
    }
    function crearDiario($factura,$journalName,$descripcion,$montoFactura,$diarioCuentaContra,$diarioFPago) {
            try{
                $saldoQ=$this->db->Query("SELECT SUM(T0.INVOICEAMOUNT - T1.SETTLEAMOUNTCUR) 'SALDO' FROM CUSTINVOICEJOUR T0 INNER JOIN CUSTTRANS T1 ON T1.VOUCHER=T0.LEDGERVOUCHER WHERE T1.INVOICE LIKE 'FV%' AND T0.DATAAREAID = '".COMPANY."' AND T1.DATAAREAID = '".COMPANY."' and T0.INVOICEID = :factura GROUP BY T1.INVOICE", [":factura"=>$factura]);
                //$saldoQ=$this->db->Query("SELECT SUM(T0.INVOICEAMOUNTMST) AS 'SALDO' from CUSTINVOICEJOUR T0 WHERE T0.DATAAREAID='".COMPANY."' and T0.INVOICEID = :factura GROUP BY T0.INVOICEID ", [":factura"=>$factura]);
                $saldo=  floatval($saldoQ[0][0]);  
                if(count($saldoQ)==0){$saldo=$montoFactura;}
                $arr=["resultado"=>["resultado"=>"El importe a pagar es mayor al saldo de la factura: $".$saldo,"saldo"=>$saldo]];
                if($saldo>=$montoFactura && $montoFactura >0 ){
                    $facturaData=  $this->db->Query("select T1.INVOICEAMOUNT,T1.INVOICEACCOUNT,T0.PAYMMODE,T0.PAYMENT from SALESTABLE T0 inner join CUSTINVOICEJOUR T1 on T0.SALESID=T1.SALESID where T1.INVOICEID= :id ",array(":id"=>$factura));
                    $ws= new Metodos();  
                    $credCont="false";
                    if($facturaData[0][3]=="CONTADO"){ $credCont="true"; }
                    $xml='<?xml version="1.0" encoding="utf-8"?>
                            <JournalPayment>
                                 <Table>
                                <Company>'.COMPANY.'</Company>
                                    <JournalName>'.$journalName.'</JournalName>
                                    <Name>'.$descripcion.'</Name>
                                    <Post>'.$credCont.'</Post>
                                 </Table>
                                 <Lines>
                                   <Line LedgerDimension="'.$facturaData[0][1].'" MarkedInvoice="'.$factura.'" Txt="'.$descripcion.'" AmountCurCredit="'.$montoFactura.'" PaymMode="'.$diarioFPago.'" OffsetLedgerDimension="'.$diarioCuentaContra.'"></Line>                               
                                 </Lines>
                            </JournalPayment>';
                    if(count($saldoQ)===0){$saldo=$facturaData[0][0]-$montoFactura;}
                    else{
                        $saldo=$saldo-$montoFactura;
                    }
                    
                    if($credCont==="false"){
                        $flagCredito=$this->db->Query("select diario from ".INTERNA.".dbo.diariosCredito where cajero= :cajero and fecha = :fecha ;",[":cajero"=>$journalName,":fecha"=>date("Y-m-d")]);
                        if(count($flagCredito)==0){
                            $r=0;
                            $diario=$ws->createDiario($xml);
                            if($diario['resultado']=='ok'){
                                $r=$this->db->Insert("insert into  ".INTERNA.".dbo.diariosCredito values( :diario ,:name,GETDATE())",[":diario"=>$diario['respuesta'],":name"=>$journalName]);
                            }                            
                            $arr=["resultado"=>$diario,"saldo"=>$saldo,"insert"=>$r,"metodo"=>$credCont]; 
                        }
                        else{
                           $arr=["resultado"=>["respuesta"=>$this->addLinesToDiario($flagCredito[0][0],$facturaData[0][1],$factura,$descripcion,$montoFactura,$diarioFPago,$diarioCuentaContra),"resultado"=>"ok"],"saldo"=>$saldo,"metodo"=>$credCont]; 
                        }
                    }
                    else{
                       //crea un diario de contado
                       $arr=["resultado"=>$ws->createDiario($xml),"saldo"=>$saldo,"metodo"=>$credCont]; 
                    }                    
                }
                return $arr;                 
            }   
            catch (Exception $e){
                throw new Exception ($e); 
            }
        }
    function modificarDiario($JournalNum) {
        try{
            $ws= new Metodos();
            $xml='<?xml version="1.0" encoding="utf-8"?><JournalPayment><Table><Company>'.COMPANY."</Company><JournalNum>".$JournalNum."</JournalNum></Table><Lines>";
            foreach ($_POST['LineNum'] as $key => $value) {
                $xml.='<Line LineNum = "'.(integer)$value.'" Delete = "False"  LedgerDimension="'.$_POST['LedgerDimension'][$key].'" MarkedInvoice="'.$_POST['MarkedInvoice'][$key].'" Txt="'.$_POST['Txt'][$key].'" AmountCurCredit="'.$_POST['AmountCurCredit'][$key].'" PaymMode="'.$_POST['PaymMode'][$key].'" OffsetLedgerDimension="'.$_POST['OffsetLedgerDimension'][$key].'"></Line>';
            }                
            $xml.='</Lines></JournalPayment>';
            return $ws->editDiario($xml);
        }
        catch (Exception $e){
            return $e;
        }            

    }
    function cerrarDiario($diario) {
        try{
            $ws= new Metodos();
            return $ws->cerrarDiario($diario);
        }
        catch (Exception $e){
            return $e;
        }            

    }
    function getCuentaContrapartida() {
        try{
            //return $this->db->Query("select cuenta from ".INTERNA.".dbo.ctaContrapartida where id=:id order by cuenta",array(":id"=>SUCURSAL)); 
            return $this->db->Query(DIARIO_NAME);             
        }
        catch (Exception $e){
            return $e;
        }
    }
    function getCuentaContrapartidaLinea() {
        try{
            return $this->db->Query(ACCOUNT_CONTRA_PARTIDA);            
        }
        catch (Exception $e){
            return $e;
        }
    }
    /**
     * 
     * @param string $factura
     * @return array regresa array simplificado
     */
    function getDataFactura($factura){
        try{
            return $this->db->Query("select T1.INVOICEAMOUNT,T1.INVOICEACCOUNT,T0.PAYMMODE from SALESTABLE T0 inner join CUSTINVOICEJOUR T1 on T0.SALESID=T1.SALESID where T1.INVOICEID= :id ",array(":id"=>$factura)); 
        }
        catch (Exception $e){
            return $e;
        }
       
    }
    /**
     * 
     * @param string $client
     * @return Resultset con el numero de compras
     */
    function getNumeroCompras($client) {
        try{
            return $this->db->Query(NUM_CLIENT_SALES,array(":cliente"=>$client));
        }
        catch(Exception $e){
            return $e;
        }
    }
    function getArtNotLocked() {
        try{
            return $this->db->Query(GET_ITEMS_NOT_BLOKED);
        }
        catch(Exception $e){
            return $e ;
        }
    }
    function sendMail($adress,$asunto,$body) {
        try{
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
            $mail->FromName = "Notificaciones Avance";  
            if(is_array($adress)){
                foreach ($adress as $k=>$v){
                    $mail->addAddress($v);
                }
            }
            else{
                $mail->addAddress($adress);
            }            
            $mail->Subject = $asunto;
            $mail->msgHTML(utf8_decode($body));
            $mail->AltBody = 'Notificaciones automaticas';
            if (!$mail->send()) {
                return "Mailer Error: " . $mail->ErrorInfo;
            } else {
                return "enviado";
            }
        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }
    function addLinesToDiario($diario,$cliente,$factura,$txt,$monto,$formaPago,$cuentaContra) {
        try{
            $model=new Application_Model_DiariosModel();
            $lineas=$model->getDiarioDetalle($diario);
            $ws= new Metodos();
            $xml='<?xml version="1.0" encoding="utf-8"?><JournalPayment><Table><Company>'.COMPANY."</Company><JournalNum>".$diario."</JournalNum></Table><Lines>";
            $linea=1;
            foreach ($lineas as $key => $value) {
                $linea++;
                $xml.='<Line LineNum = "'.(integer)$value[9].'" Delete = "False"  LedgerDimension="'.$value[2].'" MarkedInvoice="'.$value[3].'" Txt="'.$value[4].'" AmountCurCredit="'.$value[5].'" PaymMode="'.$value[8].'" OffsetLedgerDimension="'.$value[7].'"></Line>';
            }
            $xml.='<Line LineNum = "" Delete = "False"  LedgerDimension="'.$cliente.'" MarkedInvoice="'.$factura.'" Txt="'.$txt.'" AmountCurCredit="'.$monto.'" PaymMode="'.$formaPago.'" OffsetLedgerDimension="'.$cuentaContra.'"></Line>';
            $xml.='</Lines></JournalPayment>';
            //return $xml;
            $res= $ws->editDiario($xml);
            if($res['resultado']=='ok'){
                $res=$diario;
            }
            else{
                $res=$res['resultado'];
            }
            return $res;
        }
        catch (Exception $e){
            return $e->getMessage();
        }       
    }
    
    function getAlternativos($item,$sitio) {
        try{ 
            $html="";
            $this->_adapter->query(GET_ALTERNATIVOS_1);
            $result = $this->db->Query(GET_ALTERNATIVOS_2,[":item"=>$item,":sitio"=>$sitio]);
            $this->_adapter->query(GET_ALTERNATIVOS_3);
            if (!empty($result)){                    
                $html .= '<tr><td>&nbsp;<br/></td></tr>';
                $html .= '<tr><td>&nbsp;<br/></td></tr>';
                $html .= '<tr style="border-bottom:solid 1px #BDBDBD"><td>&nbsp;<br/></td></tr>';
                $html .= '<tr>';
                $html .= '  <td colspan="6">';
                $html .= '      <div class="col l12 m12 s12" style="width:100%;padding:0">';
                $html .= '          <h5 style="font-size:25px;background: #FE642E;text-align:center;padding:10px 0 10px 0"><span class="blk" style="color:white">Productos Alternativos</span></h5>';
                $html .= '      </div>';
                $html .= '  </td>';
                $html .= '</tr>';
                $html .= '<tr><td colspan="6">** Por el momento no tenemos existencia del producto <b>' . $item . '</b>, pero podemos ofrecerle: <br/></td></tr>';
                $html .= '<tr>';
                $html .= '	<th>Código de Articulo</th>';
                $html .= '	<th>Nombre del Producto</th>';
                $html .= '	<th>Sitio</th>';
                $html .= '	<th>Almacen</th>';
                $html .= '	<th>Existencia</th>';
                $html .= '	<th></th>';
                $html .= '</tr>';
                $i = 0;
                foreach ($result as $i => $data) {
                    $html .= '<tr>';
                    $html .= '	<td class="itemid">' . $data[0] . '</td>';
                    $html .= '	<td class="namealias">' . $data[1] . '</td>';
                    $html .= '	<td class="sitio" data-localidad="GRAL">' . $sitio . '</td>';
                    $html .= '	<td class="almacen">' . $sitio . 'CONS</td>';
                    $html .= '	<td>' .  number_format($data[2], 2). '</td>';
                    $html .= '  <td><p><input type="radio" name="RadioExist" value="' . $i . '" class="ExistRadio alternativo" id="radio'.$i.'Alt"><label for="radio' . $i . 'Alt"></label></p></td>';
                    $html .= '</tr>';
                }
            }
            return $html;            
        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }
    function existFactura($ov){
        return $this->db->Query("select INVOICEID from CustInvoiceJour where SALESID= :ov",[":ov"=>$ov]);
    }
}   
