<?php
/**
 * Description of reporteController
 *
 * @author sistemas10
 */
  
class ReporteController extends Zend_Controller_Inax {
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
        if (! isset($_SESSION['userInax'])) {
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        $this->view->fechaSql=$hoyS = date("Ymd");
        $this->view->fechaInput=$hoy = date("Y-m-d");
        $model= new Application_Model_ReporteModel();
        $this->view->tableType="Venta VS Cotizaciones";
        $this->view->ventas="";
        $this->view->cotiza="Ventas VS Cotizaciónes Inax";
        $this->view->result= $model->getUsoReportTableFilter('','','',"$hoyS 00:00:00","$hoyS 23:00:00",'','');
        $this->view->result2= $model->getUsoReportGrafico();
        $this->view->result3 = $model->getCTZN_VS_VTAS();
        $this->view->semaforo = $model->getDataSemaforo();
        $this->view->confirmaciones =$model->getConfirmadosVsCreados();
        $this->view->confirmacionesUsuario =$model->getConfirmadosVsCreadosPorUsuario();
        $this->view->listaNegados = $model->getDataNegadosFilter("$hoyS 00:00:00","$hoyS 23:59",'%');
        $this->view->usoGrafica = $model->getGraficaUSO("$hoyS 00:00:00","$hoyS 23:59");
        $this->view->usrListPermiso=$model->getUsrListPermiso();
        $this->view->permisoList=$model->getData2Array(PERMISO_LISTAR);
        $this->view->usrList=$model->getData2Array(USER_LIST);
        $rep = $this->getDataUsageAction();
        $this->view->useTipoReport = $rep[1];
        $this->view->useDataReport = $rep[0];        
    }
    public function filtrertblAction() {
        if (! isset($_SESSION['userInax'])) {
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        else{
            $model = new Application_Model_ReporteModel();
            $this->_helper->layout->disableLayout(); 
            $folio=  filter_input(INPUT_POST,'folio');
            $usuario=filter_input(INPUT_POST,'usuario');
            $nombre=filter_input(INPUT_POST,'nombre');
            $f2=str_replace("-","",filter_input(INPUT_POST,'fecha2'));
            $f1=str_replace("-","",filter_input(INPUT_POST,'fecha1'));
            $mov=filter_input(INPUT_POST,'tpMov');
            $sucursal=filter_input(INPUT_POST,'sucursal');
            $res=$model->getUsoReportTableFilter($folio,$usuario,$nombre,"$f1 00:00:00","$f2 23:59:59",$mov,$sucursal);
            foreach ($res as $k => $v) {
            if($k==0){$indice=1;}
                echo '<tr><td>'.$indice.'</td><td>'.$v['id_kardex'].'</td><td>'.$v['usuario'].'</td><td>'.$v['nombre'].'</td><td>'.$v['fecha'].'</td><td>'.$v['movimiento'].'</td><td>'.$v['sucursal'].'</td></tr>';
                $indice++;
            }
            exit();
        }
    }
    public function usograficaAction() {
        if (! isset($_SESSION['userInax'])){
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        else{
            $model = new Application_Model_ReporteModel();
            $this->_helper->layout->disableLayout(); 
            $f2=str_replace("-","",filter_input(INPUT_POST,'fecha2'));
            $f1=str_replace("-","",filter_input(INPUT_POST,'fecha1'));
            $res=$model->getGraficaUSO("$f1 00:00:00","$f2 23:59:59");
            foreach ($res as $k => $v) { 
                $data[$k]=array($v['nombre']=>$v['conteo']);
            }
            print_r(json_encode($res));
            exit();
        }
    }
    public function negadostblAction() {
        if (! isset($_SESSION['userInax'])){
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        else{
            $model = new Application_Model_ReporteModel();
            $this->_helper->layout->disableLayout(); 
            $f2=str_replace("-","",filter_input(INPUT_POST,'fecha2'));
            $f1=str_replace("-","",filter_input(INPUT_POST,'fecha1'));
            $vendedor =filter_input(INPUT_POST,'vendedor');
            $res=$model->getDataNegadosFilter("$f1 00:00:00","$f2 23:59:59",$vendedor."%");
            foreach ($res as $k => $v) {
            if($k==0){$indice=1;}
                echo '<tr><td>'.$indice.'</td><td>'.$v['0'].'</td><td>'.$v['1'].'</td><td>'.$v['2'].'</td><td>'.$v['3'].'</td><td>'.$v['4'].'</td><td>'.$v['5'].'</td><td>'.$v['6'].'</td><td>'.$v['7'].'</td><td>'.$v['8'].'</td></tr>';
                $indice++;
            }
            exit();
        }
    }
    public function negadostbl2csvAction() {
        if (! isset($_SESSION['userInax'])){
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        else{
            $model = new Application_Model_ReporteModel();
            $this->_helper->layout->disableLayout(); 
            $f2=str_replace("-","",filter_input(INPUT_POST,'fecha2'));
            $f1=str_replace("-","",filter_input(INPUT_POST,'fecha1'));
            $vendedor =filter_input(INPUT_POST,'vendedor');
            $res=$model->getDataNegadosFilter("$f1 00:00:00","$f2 23:59:59",$vendedor."%");
            $salida_cvs="VENDEDOR,CLAVE,DESCRIPCION,CLIENTE,SUCURSAL,CANTIDAD,UNIDAD,REQUIERE VENTA,FECHA,";
            $salida_cvs .= "\n";
            for($i=0;$i<count($res);$i++){
                for ($j=0;$j<count($res[0]);$j++){
                    $salida_cvs .= str_replace(","," ",$res[$i][$j]).",";
                }
                $salida_cvs .= "\n";
            }
            header("Content-type: application/vnd.ms-excel");
            header( "Content-Disposition: attachment; filename=Negados".$f1."-".$f2.".csv");
            print $salida_cvs;
            exit();
        }
    }
    public function semaforoAction() {
        if (! isset($_SESSION['userInax'])){
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        else{
            $model = new Application_Model_ReporteModel();
            $this->_helper->layout->disableLayout(); 
            echo $model->updateDataSemaforo(filter_input(INPUT_POST,'semaforo'));          
            exit();
        }
    }
    public function semaforostsAction() {
        if (!isset($_SESSION['userInax'])){
            $sesion=false;
            $this->json(array("color"=>"","sesion"=>$sesion));            
        }
        else{
            $sesion=true;
            $model = new Application_Model_ReporteModel();
            $color=$model->getStatusSemaforo(); 
            $this->json(array("color"=>$color,"sesion"=>$sesion,"notificacion"=>''));//json_encode($log->getNotificacionUser())));            
        }
    }
    public function semaforosts2Action() {
        if (!isset($_SESSION['userInax'])){
            $sesion=false;
            $this->json(array("color"=>"","sesion"=>$sesion));            
        }
        else{
            $sesion=true;
            $model = new Application_Model_ReporteModel();
            $log= new Application_Model_Userinfo();
            $color=$model->getStatusSemaforo(); 
            $modelC= new Application_Model_SolicitudescedisModel();
            $this->json(array("color"=>$color,"sesion"=>$sesion,"notificacion"=>$log->setNotificacionEspera($modelC->queryLine(LISTA_TRASPASOS,array(":estatus"=>1)))));
        }
    }
    
    public function deleteRollAction() {
        $model= new Application_Model_ReporteModel();
        $r=$model->sqlPrepare("delete from ".INTERNA.".dbo.usrRollAssigned where id=?",array(filter_input(INPUT_POST,'id')));
        $this->json($r);        
    }
    public function addRollAction() {
        $model= new Application_Model_ReporteModel();
        $r=$model->sqlPrepare("insert into ".INTERNA.".dbo.usrRollAssigned(idUsr,idRoll) values (?,?);",array(filter_input(INPUT_POST,'idUsr'),filter_input(INPUT_POST,'idRoll')));
        $this->json($r); 
    }
    public function getUsuarioAsignedAction() {
        $model= new Application_Model_ReporteModel();
        $arr=$model->getUsrListPermiso();
        $this->json($arr);
    }
    
    public function getDataUsageAction(){
        
        $day = date("d");
        
        $model= new Application_Model_ReporteModel();
        $query = "SELECT count(TIPO) as n, TIPO , DATEPART(hour,FECHA) as hour ".
                            " from ".INTERNA.".dbo.LogInax "
                            ." where year(FECHA)= 2018 and MONTH(FECHA)=4 and DAY(FECHA)=$day "
                            ." group by tipo, year(FECHA),MONTH(FECHA),DAY(FECHA),DATEPART(hour,FECHA)";
        $query = $model->_adapter->prepare($query);
        $query->execute();
       $result=$query->fetchAll();
        
       
        $hrstr = "";
        $minstr = "";
        for( $x = 7 ; $x < 19 ; $x++){
            strlen("".$x) == 1 ? $hrstr = "0".$x : $hrstr = $x;
            for( $y = 0 ; $y < 60 ; $y++){
                strlen("".$y) == 1 ? $minstr = "0".$y : $minstr = $y;
                $hr[] = "$hrstr";
            }
        }
        
        //$this->json($result);
        $tipo = [];
        
        foreach ($result as $w){
            $hrstr = "";
            $minstr = "";
            $x = $w["hour"];
            $y = $w["minute"];
            strlen("".$y) == 1 ? $minstr = "0".$y : $minstr = $y;
            strlen("".$x) == 1 ? $hrstr = "0".$x : $hrstr = $x;
            
            $key = "$hrstr";
            $tipo[$w["TIPO"]] = $w["TIPO"];
            $newArr[$key][$w["TIPO"]] = $w["n"];  
        }
        
        //$this->json($hr);
        
        $y = 0;
        $nAr = [];
        foreach ($hr as $h){
            $x = 0;
            $nAr[$y][$x] = $h;
            foreach ($tipo as $t){
                $x++;
                isset($newArr[$h][$t]) ? $nAr[$y][$x] = (int)$newArr[$h][$t] : $nAr[$y][$x] = 0;
            }
            $y++;
        }
        
        return array($nAr,$tipo);
        
        //$this->json($nAr);
    }
    
}
