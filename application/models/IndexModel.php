<?php
//ini_set("memory_limit","-1");
/**
* Modelo de index controller en esta parte va todo lo relacionado con al capa de negocio
* como lo por ejemplo metodos en los cuales se hacen consultas directas a la base de datos,
 * web services
 * @author Francisco Delgado <packo6300@gmail.com>
*/
class Application_Model_IndexModel {
    public $db;
    public $_adapter;
    
    public function __construct(array $options = null){
        include (LIBRARY_PATH."/includes/SSP.php");
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }
    public function getDataPaymentOv($ov){
        $query=$this->_adapter->prepare(DATOS_PAGO_OV);
        $query->bindParam(1,$ov);
        $query->execute();
        return $query->fetchAll();
    }
    /**
    * obtiene las direcciones del sitio mandando como parametro el nombre del sitio
     * @param type $sitio nombre del sitio
     * @return type regresa un arreglo con la lista
     * @throws Exception regresa una cadena 'NoResults' 
    */
    public function getDireccionOfSitio($sitio) {
        $querySucu = $this->_adapter->prepare("SELECT * FROM DBO.domicilio_sucursales WHERE sitio = ? ;");
        $querySucu->bindParam(1,$sitio);
        $querySucu->execute();
        $resultSucu = $querySucu->fetchAll();
          if (! empty($resultSucu)) {
              $datosSucu = $resultSucu;
          } else {
              $datosSucu = 'NoResults';
          }
        return $datosSucu;
    }
    /**
    * obtiene el monto de la etiqueta
     * @param type $sitio como la OV
     * @return type regresa un arreglo con la lista
     * @throws Exception regresa un 0 al no haber resultados 
    */
    public function getMontoEtiqueta($sitio) {
        $query = $this->_adapter->prepare(MONTO_ETIQUETA);
        $query->bindParam(1,$sitio);
        $query->execute();
        $resultMonto = $query->fetchAll();  
        $monto = '0';
        if (!empty($resultMonto)) {
            foreach ($resultMonto as $key => $value) {
                $monto +=  strval(($value['MONTO']));
            }            
        }
        return $monto.'';
    }
    /**
    * obtiene datos de la etiqueta
     * @param type $user  
     * @param type $sitio 
     * @param type $ov 
     * @return type regresa un arreglo con los datos de la etiqueta
     * @throws Exception regresa un array con NoResults al no haber resultados 
    */
    public function getDatosEtiqueta($user,$sitio,$ov) {
        $datosSucu=$this->getDireccionofSitio($sitio);
        $monto=$this->getMontoEtiqueta($ov);
        if(empty($monto)){$monto='0.0';} 
        $queryCliente = $this->_adapter->prepare("SELECT SALES.EMAIL,SALES.CUSTACCOUNT,DBO.getClienteNombre(SALES.CUSTACCOUNT) AS 'NOMBRECLIENTE',DBO.vendedorName(DBO.getRecidDynamicUser( ? )) AS 'NOMBREVENDEDOR',CUST.RFC_MX AS RFC,LOGEL.LOCATOR AS TELEFONO,LOGEL.LOCATOREXTENSION AS EXTENSION FROM SALESTABLE SALES INNER JOIN CUSTTABLE CUST ON (CUST.ACCOUNTNUM = SALES.CUSTACCOUNT) LEFT JOIN DIRPARTYTABLE DIRP ON (DIRP.RECID = CUST.PARTY) LEFT JOIN LOGISTICSELECTRONICADDRESS LOGEL ON (LOGEL.RECID = DIRP.PRIMARYCONTACTPHONE) WHERE SALES.SALESID = ? ;");
        $queryCliente->bindParam(1,$user);
        $queryCliente->bindParam(2,$ov);
        $queryCliente->execute();
        $resultOV = $queryCliente->fetchAll();
        if (! empty($resultOV)) {
            $datosOV=$resultOV;
            $queryDirCliente = $this->_adapter->prepare("SELECT T0.ACCOUNTNUM,T5.ADDRESS,T5.RECID,(CASE T6.NAME WHEN 'Business' THEN 'Negocio' WHEN 'Delivery' THEN 'Entrega' WHEN 'Fixed asset' THEN 'Activo Fijo' WHEN 'Home' THEN 'Particular' WHEN 'Other' THEN 'Otros' WHEN 'Invoice' THEN 'Facturacion' WHEN 'Payment' THEN 'Pago' WHEN 'Remit to' THEN 'Remitir a'  WHEN 'Service' THEN 'Servicio' WHEN 'Third party shipping' THEN 'Direccion de envio a terceros' ELSE T6.NAME END) AS 'PROPOSITO',ISNULL(T5.STREET,'NoDefinido') AS STREET,ISNULL(T7.NAME,'NoDefinido') AS COUNTY,ISNULL(T8.NAME,'NoDefinido') AS STATE ,ISNULL(T5.CITY,'NoDefinido') AS CITY,ISNULL(T5.ZIPCODE,'NoDefinido') AS ZIPCODE,ISNULL(T10.SHORTNAME,'NoDefinido') AS PAIS FROM CUSTTABLE T0 INNER JOIN DIRPARTYTABLE T1 ON T0.PARTY=T1.RECID INNER JOIN DIRPARTYLOCATION T2 ON T2.PARTY=T1.RECID INNER JOIN LOGISTICSLOCATION T3 ON T2.LOCATION=T3.RECID INNER JOIN DIRPARTYLOCATIONROLE T4 ON T4.PARTYLOCATION=T2.RECID INNER JOIN LOGISTICSPOSTALADDRESS T5 ON T5.LOCATION=T3.RECID AND T5.VALIDTO >= GETDATE() INNER JOIN LOGISTICSLOCATIONROLE T6 ON T4.LOCATIONROLE=T6.RECID LEFT JOIN LOGISTICSADDRESSCOUNTY T7 ON T7.COUNTYID = T5.COUNTY LEFT JOIN LOGISTICSADDRESSSTATE T8 ON T8.STATEID = T5.STATE  LEFT JOIN LOGISTICSADDRESSCOUNTRYREGION T9 ON T9.COUNTRYREGIONID = T8.COUNTRYREGIONID LEFT JOIN LOGISTICSADDRESSCOUNTRYREGIONTRANSLATION T10 ON T10.COUNTRYREGIONID = T9.COUNTRYREGIONID AND T10.LANGUAGEID = 'es' WHERE T0.ACCOUNTNUM = ? GROUP BY T0.ACCOUNTNUM,T5.[ADDRESS],T5.RECID,T3.DESCRIPTION,T2.ISPRIMARY,T0.PARTY,T6.NAME,T7.NAME,T5.STREET,T5.STATE,T5.CITY,T5.ZIPCODE,T8.NAME,T10.SHORTNAME ORDER BY T2.ISPRIMARY DESC;");
            $queryDirCliente->bindParam(1,$resultOV[0]['CUSTACCOUNT']);
            $queryDirCliente->execute();
            $resultDIRS = $queryDirCliente->fetchAll();
            if (! empty($resultDIRS)) {$datosDIRS = $resultDIRS;} 
            else {$datosDIRS = 'NoResults'; }
        } else {
            $datosOV = 'NoResults'; $datosDIRS = 'NoResults';
        }
        $arrayResult = array('datosSucu' => $datosSucu,'datosCte' => $datosOV,'datosDirs' => $datosDIRS,'datosMonto' => $monto);
        return $arrayResult;
    }
     /**
    * obtiene los datos de proposito
     * @param type $user  
     * @param type $ov 
     * @return type regresa un arreglo con los datos
     * @throws Exception regresa un array con NoResults al no haber resultados 
    */
    public function getDatosPropo($user,$ov){ 
    $queryCliente = $this->_adapter->prepare(DATOS_ETIQUETA);
    $queryCliente->bindParam(1,$user);
    $queryCliente->bindParam(2,$ov);
    $queryCliente->execute();
    $resultOV = $queryCliente->fetchAll();
    $clte = $resultOV[0]['CUSTACCOUNT'];
    $propositos = $this->propositoEtiqueta($clte);
    $optPropo = '<option value="">Selecciona...</option>';$selected = '';
        if ($propositos != 'NoResults') {
            foreach ($propositos as $Data) {
                if ($Data == 'Entrega') { $selected = 'selected'; } else { $selected = '';}
                $optPropo .= '<option value="' . $Data['PROPOSITO'] . '" data-recid="' . $Data['RECID'] . '" ' . $selected . '>' . $Data['PROPOSITO'] . '</option>';
            }
            $optPropo .= '<option value="Otro">Otro</option>';
        }
//    $queryDirCliente = $this->_adapter->prepare(ETIQUETA);
    $queryDirCliente = $this->_adapter->prepare("SELECT T0.ACCOUNTNUM,T5.ADDRESS,T5.RECID,(CASE T6.NAME WHEN 'Business' THEN 'Negocio' WHEN 'Delivery' THEN 'Entrega' WHEN 'Fixed asset' THEN 'Activo Fijo' WHEN 'Home' THEN 'Particular' WHEN 'Other' THEN 'Otros'  WHEN 'Invoice' THEN 'Facturacion' WHEN 'Payment' THEN 'Pago' WHEN 'Remit to' THEN 'Remitir a' WHEN 'Service' THEN 'Servicio' WHEN 'Third party shipping' THEN 'Direccion de envio a terceros' ELSE T6.NAME END) AS 'PROPOSITO',ISNULL(T5.STREET,'NoDefinido') AS STREET,ISNULL(T7.NAME,'NoDefinido') AS COUNTY, ISNULL(T8.NAME,'NoDefinido') AS STATE,ISNULL(T5.CITY,'NoDefinido') AS CITY, ISNULL(T5.ZIPCODE,'NoDefinido') AS ZIPCODE,ISNULL(T10.SHORTNAME,'NoDefinido') AS PAIS FROM CUSTTABLE T0 INNER JOIN DIRPARTYTABLE T1 ON T0.PARTY=T1.RECID INNER JOIN DIRPARTYLOCATION T2 ON T2.PARTY=T1.RECID INNER JOIN LOGISTICSLOCATION T3 ON T2.LOCATION=T3.RECID INNER JOIN DIRPARTYLOCATIONROLE T4 ON T4.PARTYLOCATION=T2.RECID INNER JOIN LOGISTICSPOSTALADDRESS T5 ON T5.LOCATION=T3.RECID AND T5.VALIDTO >= GETDATE() INNER JOIN LOGISTICSLOCATIONROLE T6 ON T4.LOCATIONROLE=T6.RECID LEFT JOIN LOGISTICSADDRESSCOUNTY T7 ON T7.COUNTYID = T5.COUNTY LEFT JOIN LOGISTICSADDRESSSTATE T8 ON T8.STATEID = T5.STATE LEFT JOIN LOGISTICSADDRESSCOUNTRYREGION T9 ON T9.COUNTRYREGIONID = T8.COUNTRYREGIONID LEFT JOIN LOGISTICSADDRESSCOUNTRYREGIONTRANSLATION T10 ON T10.COUNTRYREGIONID = T9.COUNTRYREGIONID AND T10.LANGUAGEID = 'es' WHERE T0.ACCOUNTNUM = '$clte' GROUP BY T0.ACCOUNTNUM,T5.[ADDRESS],T5.RECID,T3.DESCRIPTION,T2.ISPRIMARY,T0.PARTY,T6.NAME ,T7.NAME,T5.STREET,T5.STATE,T5.CITY,T5.ZIPCODE,T8.NAME,T10.SHORTNAME ORDER BY T2.ISPRIMARY DESC;");
    $queryCliente->bindParam(1,$clte);
    $queryDirCliente->execute();
    $resultDIRS = $queryDirCliente->fetchAll();
    if (! empty($resultDIRS)) { $datosDIRS = $resultDIRS;} else { $datosDIRS = 'NoResults'; }
    $result = array('optPropo' => $optPropo, 'datosDirs' => $datosDIRS );
    return $result;
    }
     /**
     * @param type $clte 
     * @return type regresa un arreglo con los datos 
     * @throws Exception regresa un array
    */
    public function propositoEtiqueta($clte){
        $query = $this->_adapter->prepare(ETIQUETA);
        $query->bindParam(1,$clte);
        $query->execute();
        $result = $query->fetchAll();
        if (!empty($result)){
            $datos = $result;
        }else{
            $datos = 'NoResults';
        }
        return $datos;
    }
    /**
     * @deprecated since version 2.0
     * @return type regresa un arreglo con los datos de la comparativa de ventas 
     * @throws Exception regresa un array
    */
    
    public function getComparativa() {
        $fecha = new Date('Y-m-d');
        $queryComparativa = $this->_adapter->prepare("SELECT ISNULL(SUM(CASE WHEN (T0.STF_SALESIDWS = '') THEN 0 ELSE 1 END),0) AS CONTEOWS_VTA , ISNULL(SUM(CASE WHEN (T0.STF_SALESIDWS = '') THEN 1 ELSE 0 END),0) AS CONTEODYN_VTA, COUNT(T0.SALESID) AS TOTAL_VTA FROM SALESTABLE T0 WHERE DATEADD(HOUR,-7,T0.CREATEDDATETIME) BETWEEN ? AND ? AND T0.WORKERSALESTAKER = DBO.getRecidDynamicUser( ? );");
        $queryComparativa->bindParam(1,$fecha.' 00:00:00.00');
        $queryComparativa->bindParam(2,$fecha.' 23:59:59.00');
        $queryComparativa->bindParam(3,$_SESSION['userInax']);
        $queryComparativa->execute();
        $result = $queryComparativa->fetchAll();
        $queryComparativa = $this->_adapter->prepare("SELECT ISNULL(SUM(CASE WHEN (T0.STF_QUOTATIONIDWS = '') THEN 0 ELSE 1 END),0) AS CONTEOWS_COT, ISNULL(SUM(CASE WHEN (T0.STF_QUOTATIONIDWS = '') THEN 1 ELSE 0 END),0) AS CONTEODYN_COT, COUNT(T0.QUOTATIONID) AS TOTAL_COT  FROM SALESQUOTATIONTABLE T0 WHERE DATEADD(HOUR,-7,T0.CREATEDDATETIME) BETWEEN ? AND ? AND T0.WORKERSALESTAKER = DBO.getRecidDynamicUser('" . $_SESSION['userInax'] . "');");
        $queryComparativa->bindParam(1,$fecha.' 00:00:00.00');
        $queryComparativa->bindParam(2,$fecha.' 23:59:59.00');
        $queryComparativa->bindParam(3,$_SESSION['userInax']);
        $queryComparativa->execute();
        $resultCoti = $queryComparativa->fetchAll();
        if (! empty($result)) {
            $datosComp[0] = 'exito';
            $datosComp['WS_VTA'] = $result[0]['CONTEOWS_VTA'];
            $datosComp['DYN_VTA'] = $result[0]['CONTEODYN_VTA'];
            $datosComp['TOTAL_VTA'] = $result[0]['TOTAL_VTA'];
        }
        if (! empty($resultCoti)) {
           $datosComp['WS_COT'] = $resultCoti[0]['CONTEOWS_COT'];
           $datosComp['DYN_COT'] = $resultCoti[0]['CONTEODYN_COT'];
           $datosComp['TOTAL_COT'] = $resultCoti[0]['TOTAL_COT'];
        } 
        else{
            $datosComp[0] = 'NoResults';
        }
        return $datosComp;
    }
    /**
     * @return array regresa un json con los datos de la tabla 
     * @throws Exception regresa un array
    */
    public function getOVporUsuario2($vendedor){
        $queryUser = $this->_adapter->prepare(USUARIO_TO_ID);
        $queryUser->bindParam(1,$vendedor);
        $queryUser->execute();      
        $resultUser = $queryUser->fetchAll(); 
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_1);
        $q = $this->_adapter->prepare(TODAS_OV_PERIODO_USUARIO);
        $q->bindParam(1,$resultUser[0]['recidUsuario']);
        $q->execute();      
        $r = $q->fetchAll();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_3);
        $estatus=array("",'Orden Abierta','Entregado','Facturado','Cancelado');
        $arr=array();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            if($v['dlvmode']=='PAQUETERIA'){
               $entrega="<div class=\"col l12 m12 s12\">
                        <a href=\"javascript:archivoAdjunto('".$v['salesid']."','ORDVTA');\">".$v['dlvmode']."</a>
                        <button class=\"btn flat-btn\" onclick=\"mostrarModalEti('".$v['salesid']."','".$v['inventsiteid']."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i> Etiqueta</button>
                        </div>"; 
            }
            else{
                $entrega=$v['dlvmode'];
            }
            $sst='';
            if($v['SALESSTATUS']>0){
                $sst=$estatus[$v['SALESSTATUS']];
            }
            if ($v['PROCESO'] == 'ENTREGADO'){
                $data = '<div style="text-align: center; background-color: #4caf50;border-radius: 59px;width: 30px;height: 30px;">&nbsp&nbsp&nbsp&nbsp<div/>';
            }else if ( $v['PROCESO'] == 'TERMINADO' || $v['PROCESO'] == 'INICIADO' ){
                $data = '<div style="text-align: center; background-color: gold;border-radius: 59px;width: 30px;height: 30px;">&nbsp&nbsp&nbsp&nbsp<div/>';
            }else if ( $v['PROCESO'] == 'NO INICIA' ){
                $data = '<div style="text-align: center; background-color: red;border-radius: 59px;width: 30px;height: 30px;">&nbsp&nbsp&nbsp&nbsp<div/>';
            }
            $impresion="";
            if($v['PACKINGSLIPIDJOUR']!=''){
                $impresion.="<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirRemision('".$v['PACKINGSLIPIDJOUR']."','Sin Procesar')\"><i class=\"mdi-action-print\"></i></button>";
            }
            $html='';
            if ($v['SALESSTATUS'] === '1' ){ 
                $query = $this->_adapter->query("SELECT SALES.DLVTERM
                                                FROM SALESTABLE SALES                                                             
                                                WHERE SALESID = '".$v['salesid']."';");
                $query->execute();      
                $result=$query->fetchAll(PDO::FETCH_ASSOC);
                $html = '<button class="btn blue-grey darken-3" id="GenerarRemision'.$v['salesid'].'" value="'.$v['salesid'].'" onclick="GenerarRemisionBtn(\''.$result[0]['DLVTERM'].'\',\''.$v['salesid'].'\',\''.$_SESSION['userInax'].'\')">Remisionar</button>';
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==14){
                        $html = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            if($v['SALESSTATUS']==='2'){
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==1){
                        $html = '<button class="btn blue-green darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$entrega.'\')"> <i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</button>';
                    }
                }
            }
            if($v['SALESSTATUS']==='3'){
                $html='<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='.$v['salesid'].'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
            }
           $arr[$k]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['salesid']."','ORDVTA')\">add_circle</i>",
                $v['salesid'],
                $fecha,
                $v['custaccount'],
                $v['salesname'],
                $v['inventsiteid'],
                $v['inventlocationid'],
                $entrega,
                $sst,
                $data,
                $html,
                $impresion); 
        }
        return $arr;
    }
    /**
     * @return array regresa un json con los datos de la tabla 
     * @throws Exception regresa un array
    */
    public function getTotasOVCliente($nombre,$ov){
        $model = new Application_Model_CotizacionModel();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_1);
        if($nombre!=='%%'){
            $q = $this->_adapter->prepare(CLIENTES_PASA_OV);
            $q->bindParam(1,$nombre);
        }
        else{
            $q = $this->_adapter->prepare(CLIENTES_PASA_OV_2);
            $q->bindParam(1,$ov);
        }        
        $q->execute();      
        $r = $q->fetchAll();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_3);
        $entrega="";
        $estatus=array("",'Orden Abierta','Entregado','Facturado','Cancelado');
        $arr=array();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            if($v['dlvmode']=='PAQUETERIA'){
               $entrega="<div class=\"col l12 m12 s12\">
                        <a href=\"javascript:archivoAdjunto('".$v['salesid']."','ORDVTA');\">".$v['dlvmode']."</a>
                        <button class=\"btn flat-btn\" onclick=\"mostrarModalEti('".$v['salesid']."','".$v['inventsiteid']."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i> Etiqueta</button>
                        </div>"; 
            }
            else{
                $entrega=$v['dlvmode'];
            }
            $sst='';
            if($v['SALESSTATUS']>0){
                $sst=$estatus[$v['SALESSTATUS']];
            }
            if ($v['PROCESO'] == 'ENTREGADO'){
                $data = '<div style="text-align: center; background-color: #4caf50;border-radius: 59px;width: 30px;height: 30px;">&nbsp&nbsp&nbsp&nbsp<div/>';
            }
            $impresion="";
            if($v['PACKINGSLIPIDJOUR']!=''){
                $data="<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirRemision('".$v['PACKINGSLIPIDJOUR']."','Sin Procesar')\">Remisión PDF</button>";
            }
            if($v['SALESSTATUS']==='3'){
                $impresion='<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='.$v['salesid'].'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
            }
            if($v['SALESSTATUS']==='2'){
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==1){
                        $impresion = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            $cotizacion=$this->db->Query("SELECT QUOTATIONID FROM SALESTABLE WHERE SALESID = :id ",[":id"=>$v['salesid']]); 
            $cargoId=$model->getCargo($cotizacion[0][0]);            
            $total=$this->db->Query(TOTAL_OV,[":id"=>$v['salesid']]); 
            $montocargo=$total[0][0]+($total[0][0]*($cargoId[0]['VALUE']/100));
            $to=$montocargo*1.16;
            $arr[$k]=array(
                $v['salesid'],
                $fecha,
                $v['custaccount'],
                $v['salesname'],
                $v['inventsiteid'],
                $v['inventlocationid'],
                $entrega,
                $v['VENDEDOR'],
                $sst,
                $data,
                $impresion, 
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['salesid']."','ORDVTA')\">add_circle</i>",
                "$ ".number_format($to,3,'.',','));
        }
        return $arr;
    }
    /**
     * @return array regresa un json con los datos de la tabla 
     * @throws Exception regresa un array
    */
    
    public function getTodasOV2() {
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_1);
        $q = $this->_adapter->prepare(TODAS_OV_PERIODO);
        $q->execute();      
        $r = $q->fetchAll();
        $this->_adapter->query(TODAS_OV_PERIODO_USUARIO_3);
        $entrega="";
        $estatus=array("",'Orden Abierta','Entregado','Facturado','Cancelado');
        $arr=array();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            if($v['dlvmode']=='PAQUETERIA'){
               $entrega="<div class=\"col l12 m12 s12\">
                        <a href=\"javascript:archivoAdjunto('".$v['salesid']."','ORDVTA');\">".$v['dlvmode']."</a>
                        <button class=\"btn flat-btn\" onclick=\"mostrarModalEti('".$v['salesid']."','".$v['inventsiteid']."','".IMAGES_PATH."')\"><i class=\"fa fa-1x fa-eye \"></i> Etiqueta</button>
                        </div>"; 
            }
            else{
                $entrega=$v['dlvmode'];
            }
            $sst='';
            if($v['SALESSTATUS']>0){
                $sst=$estatus[$v['SALESSTATUS']];
            }
            $impresion=""; 
            if($v['PACKINGSLIPIDJOUR']!=''){
                $impresion="<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirRemision('".$v['PACKINGSLIPIDJOUR']."','Sin Procesar')\"><i class=\"mdi-action-print\"></i></button>";
            }
            $data="";
            if ($sst==='Orden Abierta' ){ 
                $query = $this->_adapter->query("SELECT SALES.DLVTERM
                                                FROM SALESTABLE SALES                                                             
                                                WHERE SALESID = '".$v['salesid']."';");
                $query->execute();      
                $result=$query->fetchAll(PDO::FETCH_ASSOC);
                $data = '<button class="btn blue-grey darken-3" id="GenerarRemision'.$v['salesid'].'" value="'.$v['salesid'].'" onclick="GenerarRemisionBtn(\''.$result[0]['DLVTERM'].'\',\''.$v['salesid'].'\',\''.$_SESSION['userInax'].'\')">Remisionar</button>';
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==14){
                        $data = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            if($v['SALESSTATUS']==='3'){
                $impresion='<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='.$v['salesid'].'&amp;tipo=CLIENTE" target="_blank"><i class="fa fa-file-pdf-o" style="color:#840101;"></i> Factura</a>';
            }
            if($v['SALESSTATUS']==='2'){
                foreach ($_SESSION['factura'] as $key => $value) {
                    if($value==1){
                        $impresion = '<button class="btn blue-grey darken-3" onclick="factura2(\''.$v['salesid'].'\',\''.$v['PACKINGSLIPIDJOUR'].'\',\''.$v['dlvmode'].'\')">Facturar</button>';
                    }
                }
            }
            $arr[$k]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['salesid']."','ORDVTA')\">add_circle</i>",
                $v['salesid'],
                $fecha,
                $v['custaccount'],
                $v['salesname'],
                $v['inventsiteid'],
                $v['inventlocationid'],
                $entrega,
                $v['VENDEDOR'],
                $sst,
                $data,
                $impresion);
        }
        return $arr;
    }
    /**
     * @return array regresa un json con los datos de la tabla 
     * @throws Exception regresa un array
    */
     public function getCotPorUsuario2($vendedor){
        $queryUser = $this->_adapter->prepare(USUARIO_TO_ID);
        $queryUser->bindParam(1,$vendedor);
        $queryUser->execute();      
        $resultUser = $queryUser->fetch();
        $arr=array();
        $q = $this->_adapter->prepare(TODAS_COTIZACIONES_USER);
        $q->bindParam(1,$resultUser['recidUsuario']);
        $q->execute();      
        $r = $q->fetchAll();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            $estatus=array('Creado','Enviado','Confirmado','Perdido','Cancelado');
            $sst=$estatus[$v['QUOTATIONSTATUS']];
            if ( $v['QUOTATIONSTATUS'] == '1' || $v['QUOTATIONSTATUS'] == '2' ){
                $data = "";
            }else{
                $data = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"convertirCot('".$v['QUOTATIONID']."',$(this));\"><i class=\"material-icons\">done_all</i>OV</button>";
            }
            if ( ($v['QUOTATIONSTATUS'] == '1' || $v['QUOTATIONSTATUS'] == '2') && $v['SALESIDREF'] !=='' ){
                $impresion = "";
            }else{
                $impresion = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirCotizacion('".$v['QUOTATIONID']."','0')\"><i class=\"mdi-action-print\"></i></button>";
            }
            $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$v['QUOTATIONID'].'</a>';
            if($v['QUOTATIONSTATUS']==0){
                $edita="<a onclick=\"editarCot('".$v['QUOTATIONID']."','".$v['CUSTACCOUNT']."');\">".$v['QUOTATIONID']."</a>";
            }
            $arr[$k]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['QUOTATIONID']."','CTZN')\">add_circle</i>",
                $edita,
                $fecha,
                $v['CUSTACCOUNT'],
                $v['QUOTATIONNAME'],
                $v['INVENTSITEID'],
                $v['INVENTLOCATIONID'],
                $v['DLVMODE'],
                $sst,
                $data,
                $impresion);
        }
        return $arr;
    }
    /**
     * @return array regresa un json con los datos de la tabla 
     * @throws Exception regresa un array
    */
    public function getTodasCot2(){ 
       $arr=array();
        $q = $this->_adapter->prepare(TODAS_COTIZACIONES);
        $q->execute();      
        $r = $q->fetchAll();
        foreach ($r as $k => $v) {
            $fecha2 = $v['CREATEDDATETIME'];
            $nuevafecha = strtotime ( '-6 hour' , strtotime ( $fecha2 ) ) ;
            $fecha = date ( 'd/m/Y H:i:s' , $nuevafecha );
            $estatus=array('Creado','Enviado','Confirmado','Perdido','Cancelado');
            $sst=$estatus[$v['QUOTATIONSTATUS']];
            if ( $v['QUOTATIONSTATUS'] == '1' || $v['QUOTATIONSTATUS'] == '2' ){
                $data = "";
            }else{
                $data = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"convertirCot('".$v['QUOTATIONID']."',$(this));\"><i class=\"material-icons\">done_all</i>OV</button>";
            }
            if ( ($v['QUOTATIONSTATUS'] == '1' || $v['QUOTATIONSTATUS'] == '2') && $v['SALESIDREF'] !=='' ){
                $impresion = "";
            }else{
                $impresion = "<button class=\"btn blue-grey darken-3\" type=\"button\" onclick=\"imprimirCotizacion('".$v['QUOTATIONID']."','0')\"><i class=\"mdi-action-print\"></i></button>";
            }
            $edita='<a style="color:red;" onclick="swal(\'ALTO\', \'No se permite editar\', \'error\');">'.$v['QUOTATIONID'].'</a>';
            if($v['QUOTATIONSTATUS']==0){
                $edita="<a onclick=\"editarCot('".$v['QUOTATIONID']."','".$v['CUSTACCOUNT']."');\">".$v['QUOTATIONID']."</a>";
            }
            
            $arr[$k]=array(
                "<i class=\"material-icons\" style=\"color:green;cursor:pointer\" onclick=\"detalleVenta2('".$v['QUOTATIONID']."','CTZN')\">add_circle</i>",
                $edita,
                $fecha,
                $v['CUSTACCOUNT'],
                $v['QUOTATIONNAME'],
                $v['INVENTSITEID'],
                $v['INVENTLOCATIONID'],
                $v['DLVMODE'],
                $v['VENDEDOR'],
                $sst,
                $data,
                $impresion);
        }
        return $arr;              
    }
    public function condicionesEntrega($ov) {
        return $this->db->Query('select DLVTERM from SALESTABLE where SALESID= :ov',[':ov'=>$ov]);        
    }
}
