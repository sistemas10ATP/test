<?php
class Application_Model_Userinfo{
	var $sitios;
	var $usuarios;
	public $db;
	public $_adapter;
 
	public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        return $this->_adapter;
    }
    public function Sitios($company){
        $query = $this->_adapter->prepare(SITIOS);
        $query->bindParam(1,$company);
        $query->execute();
        $result=$query->fetchAll();
        if(empty($result)){
            $sitios[0] = "NoResults";
        }else{
            $sitios = $result;
        }
        return  $sitios;
    }
    public function getCuentaPagoMostrador($user) {
        return $this->db->Query(USUARIO_DIARIO,[":usuario"=>$user]);
    }
    public function getCuentasPago(){
        return $this->db->Query(CUENTAS_PAGO);
    }

    public function userExist() {
        $query = $this->_adapter->prepare("select * from ".INTERNA.".dbo.usrUsr where Usuario=? ");
        $query->bindParam(1,$_SESSION['userInax']);
        $query->execute();
        $result=$query->fetchAll();
        if(empty($result)){
            $query = $this->_adapter->prepare("insert into ".INTERNA.".dbo.usrUsr(Usuario)values(?)");
            $query->bindParam(1,$_SESSION['userInax']);
            $query->execute();
        }
        
    }
    
    /**
     * 
     * @author Javier Delgado <packo6300@gmail.com>
     * @see Generar cotizacion,Generar Orden de Venta
     * @uses getOrigenesVenta() obtiene la lista de origenes de la venta. 
     */
    public function getOrigenesVenta(){
        try{
            $query = $this->_adapter->query(ORIGEN_VENTA);
            $query->execute();
            $result=$query->fetchAll();
            return $result;
        }
        catch (PDOException $e){
            echo $e->getMessage();
            exit();
        }
    }

    public function usuarios($user){
        try{
            $query =$this->_adapter->prepare("SELECT  T2.NAME,T3.RECID
               , CASE WHEN (T3.RECID = DBO.getRecidDynamicUser('$user')) THEN 'selected' ELSE '' END AS SEL
               , CASE WHEN (DBO.getRecidDynamicUser('$user') IS NOT NULL) THEN 'registrado' ELSE 'noRegistrado' END AS REGISTRADO
            FROM DIRPARTYTABLE T2  INNER JOIN HCMWORKER T3 ON T2.RECID=T3.PERSON ORDER BY T2.NAME;");
            $query ->bindParam(1,$user);
            $query ->bindParam(2,$user);
            $query->execute();
            $result=$query->fetchAll();
            $optSitio="";
            $flag=0;
            $res=array("response"=>"ok","reg"=>$flag,"res"=>$optSitio);
            if(!empty($result)){
                foreach ($result as $data) {
                    if($data['REGISTRADO']!="registrado"){ $flag=1; }
                    $optSitio .= '<option value="' . $data['RECID'] . '" '.$data['SEL'].'>' . $data['NAME'] . '</option>';
                }
            $res=array("response"=>"ok","reg"=>$flag,"res"=>$optSitio);
            }            
            return  $res;
        }
        catch (Exception $e){
            echo $e->getMessage();
            exit();
        }
    }

    public function Cargos(){
        $query = $this->_adapter->query(QUERY_CARGOS);
        $query->execute();
        $result=$query->fetchAll();
        if(empty($result)){
            $cargos[0] = "NoResults";
        }
        foreach ($result as $k => $v){
            $cargos[$k]=$v;
        }
        return  $cargos;
    }

    public function ModosEntrega(){
        $query = $this->_adapter->query(FORMA_ENTREGA);
        $query->execute();
        $result=$query->fetchAll();
        if(empty($result)){
            $modosentrega[0] = "NoResults";
        }
        foreach ($result as $k => $v){
            $modosentrega[$k]=$v;
        }
        return  $modosentrega;
    }
    public function ov2rem($param) {
        
    }
    public function ImprimirRemision($remision){
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(QUERY_REMISION2);
        $query->bindParam(1,$remision);  
        $query->execute();      
        $result=$query->fetchAll();
        if(empty($result)){ $Datosremision[0] = "NoResults";}
        foreach ($result as $k => $v){ $Datosremision[$k]=$v;}
        return  $result;
    }
    /**
     * 
     * @param type $param
     */
    public function getRevision($param) {
         $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(GET_OV_REV);
        $query->bindParam(1,$param);  
        $query->execute();      
        $result=$query->fetchAll();
        $r=$result[0]['PACKINGSLIPID'];
        return  $r;
    }
    
    public function setLoginKardex($usuario,$nombre) {
       try{
            $t='inicio de sesion';
            $query=$this->_adapter->query(ANSI_NULLS);
            $query=$this->_adapter->query(ANSI_WARNINGS);
            $query = $this->_adapter->prepare(INSERT_KARDEX);
            $query->bindParam(1,$usuario);
            $query->bindParam(2,$nombre);
            $query->bindParam(3,$t);
            $query->execute();
       }catch (PDOException $err){
           echo $err;
           exit();
       }
    }

    public function getConteoNegados(){
        $user = $_SESSION['userInax'];
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query = $this->_adapter->prepare(NEGADOS);
        $query->bindParam(1,$user);
        $query->execute();
        $result = $query->fetchAll();
        return $result[0]['conteoNegado'];
    }
    public function kardexLog($txt,$parametros,$response,$status,$mov) {
        try {
            $ip = $_SERVER["REMOTE_ADDR"]; 
            $query = $this->_adapter->prepare("insert into ".INTERNA.".dbo.LogInax (USUARIO,FECHA,TIPO,IP,COMPANY,PARAMETROS,RESPONSE,ESTATUS) VALUES ('".$_SESSION['userInax']."',getdate(),'$mov','$ip','".COMPANY."','$parametros','$response',$status);");
            $query->execute();
        } catch (Exception $exc) {
             throw new Exception ($exc);
        }         
    }

    public function test($newArr){
        $query = $this->_adapter->query("insert into ".INTERNA.".dbo.LogInax "
                    ."(USUARIO,FECHA,TIPO,IP,COMPANY,PARAMETROS,RESPONSE,ESTATUS) VALUES "
                    ."('".$newArr["user"]."','".$newArr["date"]."','".$newArr["action"]."','".$newArr["ip"]."','".$newArr["company"]."','".iconv('','UTF-8',$newArr["parametros"])."','".iconv('','UTF-8',$newArr["resultado"])."',".$newArr["estatus"].");");
            $query->execute();
    }
    public function getPermissions() {
        $query = $this->_adapter->prepare(USER_PERMISSIONS);
        $query->bindParam(1,$_SESSION['userInax']);
        $query->execute();
        $r= $query->fetchAll();
        $arr=array();
        foreach ($r as $key => $v) {
            $arr[]= (int)$v['idRoll'];
        }
        return $arr;
    }
    function setNotificacionEspera($params=array()){
        $notificacion=':D';
        if(!empty($params)){
            $nombre_archivo = $_SESSION['user'].'.ini';
            $dir = __DIR__.'/../alerts/';
            $notificacion=$this->escribe_ini($params, $dir.$nombre_archivo);
}
        return $notificacion;            
    }
    function getNotificacionUser(){
        $nombre_archivo = $_SESSION['user'].'.ini';
        $dir = __DIR__.'/../alerts/';
        return parse_ini_file($dir.$nombre_archivo);
    }
    function escribe_ini($matriz, $archivo, $multi_secciones = true, $modo = 'w') {
        $salida = '';
        define('SALTO', "\n"); 
        if (!is_array(current($matriz))) {
            $tmp = $matriz;
            $matriz['tmp'] = $tmp; # no importa el nombre de la sección, no se usará
            unset($tmp);
        }
        foreach($matriz as $clave => $matriz_interior) {
            if ($multi_secciones) {
                $salida .= '['.$clave.']'.SALTO;
            }
            foreach($matriz_interior as $clave2 => $valor){
                $salida .= $clave2.' = "'.$valor.'"'.SALTO;
            }
            if ($multi_secciones) {
                $salida .= SALTO;
            }
        }
        $puntero_archivo = fopen($archivo, $modo);
        if ($puntero_archivo !== false) {
            $escribo = fwrite($puntero_archivo, $salida);
            if ($escribo === false) {
                $devolver = -2;
            } else {
                $devolver = $escribo;
            }
            fclose($puntero_archivo);
        } 
        else {
            $devolver = -1;
        } 
    return $devolver;
 }
 function leer_ini($param) {
     
 }
 
}
