<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReporteFallasWSController
 *
 * @author sistemas10
 */
class FallasinaxController extends Zend_Controller_Inax{
    public function init(){
        /* Initialize action controller here */
        try {
            if(empty($_SESSION['userInax'])){
                $this->_redirect('/login');
            }
            $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    public function indexAction() {
        $this->view->fechaInput=$hoy = date("Y-m-d");
        $model=new Application_Model_FallasinaxModel();
        $this->view->sucursalList = $model->getSucursales();
    }
    public function filterTimeAction() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $totalWS= $model->getCountPeticiones($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $totalWSerror= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_ERROR);
        $totalWSok= $model->getCountPeticiones($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_OK);        
        $totalWSprecio= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_PRECIO);
        $totalWSremision= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_REMISION);
        $totalWSconfirmacion= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_LINEAS);
        $totalWScotov= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_COT_OV);
        $totalWSPrecioComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_PRECIO_COMPLETE);
        $totalWSConfirmacionComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_LINEAS_COMPLETE);
        $totalWSRemisionComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_REMISION_COMPLETE);
        $totalWScotovComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_COT_OV_COMPLETE);
        $newClient=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_CLIENTE_NEW);
        $pdf=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_IMPRESION_PDF);
        $fact=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_FACTURA);
        $efact=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_FACTURA_ERROR);
        $this->json(
            array(
                "total"=>$totalWS['total'],
                "totalError"=>$totalWSerror['total'],
                "totalOk"=>$totalWSok['total'],
                "totalPrecio"=>$totalWSprecio['total'],
                "totalRemision"=>$totalWSremision['total'],
                "totalConfirmacion"=>$totalWSconfirmacion['total'],
                "totalCotOv"=>$totalWScotov['total'],
                "totalWSPrecioComplete"=>$totalWSPrecioComplete['total'],
                "totalWSConfirmacionComplete"=>$totalWSConfirmacionComplete['total'],
                "totalWSRemisionComplete"=>$totalWSRemisionComplete['total'],
                "totalWScotovComplete"=>$totalWScotovComplete['total'],
                "newClient"=>$newClient['total'],
                "pdf"=>$pdf['total'],
                "fact"=>$fact['total'], 
                "efact"=>$efact['total'],
            ) 
        );        
    }
    public function filterFechaListAction() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $lista=$model->getPeticionesTbl($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $arr=array();
        foreach ($lista as $k=>$v) {
            $arr[$k]=array($v['ID'],$v['USUARIO'],$v['FECHA'],$v['TIPO'],$v['IP'],$v['ESTATUS']);
        }
        $this->json($arr);        
    }
    public function filterFechaList2Action() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $lista=$model->getErrorUserTbl($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $arr=array();
        foreach ($lista as $k=>$v) {
            $arr[$k]=array($v['USUARIO'],$v['C']);
        }
        $this->json($arr);        
    }
    public function filterFechaList3Action() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $lista=$model->getUseByBranchUser($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $arr=array();
        foreach ($lista as $k=>$v) {
            $arr[$k]=array($v['USUARIO'],$v['cant']);
        }
        $this->json($arr);        
    }
    public function filterFechaList4Action() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $lista=$model->getUseByBranchOffice($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $sucursalList=$model->getSucursales();
        $sucL=array();
        foreach ($sucursalList as $key => $value) {
            $sucL[$value[0]]=$value[1];
        }
        $arr=array();
        foreach ($lista as $k=>$v) {
            $suc=explode(".",$v['IP']);
            $s=$sucL[$suc[2]];
            if(empty($s)){$s=$suc[2];}
            $arr[$k]=array($s,$v['cant']);
        }
       
        $this->json($arr);        
    }
    public function filterFechaList5Action() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $facturasInax=$model->db->Query(FACTURAS_INAX,array(":fechaInicial"=>$hoy2,":fechaActual"=>$hoy));
        $facturasDyn=$model->db->Query(FACTURAS_DYN,array(":fechaInicial"=>$hoy2,":fechaActual"=>$hoy));
        $totalInax=0;
        foreach ($facturasInax as $key => $value) {
            $totalInax=$totalInax+$value[0];
        }
        $totalDyn=0;
        foreach ($facturasDyn as $key => $value) {
            $totalDyn=$totalDyn+$value[0];
        }       
        $this->json([["InAX",$totalInax],["Dynamics",$totalDyn]]);        
    }
    public function filterFechaList6Action() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $facturasInax=$model->db->Query(FACTURAS_INAX,array(":fechaInicial"=>$hoy2,":fechaActual"=>$hoy));
        $facturasDyn=$model->db->Query(FACTURAS_DYN,array(":fechaInicial"=>$hoy2,":fechaActual"=>$hoy));
        $arr=[];
        foreach ($facturasInax as $key => $value) {
            $arr[]=[$value[1],(integer)$value[0]];
        }       
        $this->json($arr);        
    }
    public function filterFechaList7Action() {
        $hoy2=filter_input(INPUT_POST,'f1');
        $hoy = filter_input(INPUT_POST,'f2');
        $model=new Application_Model_FallasinaxModel();
        $facturasDyn=$model->db->Query(FACTURAS_DYN,array(":fechaInicial"=>$hoy2,":fechaActual"=>$hoy));
        $arr=[];
        foreach ($facturasDyn as $key => $value) {
            $arr[]=[$value[1],(integer)$value[0]];
        }       
        $this->json($arr);        
    }
    public function usoInaxAction() {       
        $model= new Application_Model_ReporteModel();
        $model2= new Application_Model_FallasinaxModel();
        $titulo="Reporte de uso inAX";
        require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
        
        $hoy2=filter_input(INPUT_GET,'f1');
        $hoy = filter_input(INPUT_GET,'f2');
        if(empty($hoy) & empty($hoy2)){
            $hoy=$hoy2=date("Y-m-d");
        }
        $ImpCotizacion="Uso_Inax_".$hoy2."_".$hoy;
        $grafico=$model->getGraficaUSO("$hoy2 00:00:00","$hoy 23:59");
        $data23="";
        $total=0;
        $data=array();
        foreach ($grafico as $k => $v) {
            $total=$total+$v['conteo'];
            if($data[$v['nombre']]){
                $data[$v['nombre']][1]=$data[$v['nombre']][1]+$v['conteo'];
            }
            else{
              $data[$v['nombre']]=[$v['nombre'],$v['conteo']];  
            }            
        }
        $lista=array();
        foreach ($data as $key => $value) {
            $lista[]=[$value[0],$value[1]];
        }
        foreach ($lista as $k => $v) {
            $data23.="<tr><td>".trim($v[0]).'</td><td align="center">'.number_format($v[1],0,'.',',').'</td><td align="right" >'.  number_format(($v[1]*100)/$total,2,'.',',')." %</td></tr>";
        }
        $facturas=$model2->getUseByBranchOffice($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $sucursalList=$model2->getSucursales();
        $sucL=array();
        foreach ($sucursalList as $key => $value) {
            $sucL[$value[0]]=$value[1];
        }
        $arr=array();
        $facturasList="";
        foreach ($facturas as $k=>$v) {
            $suc=explode(".",$v['IP']);
            $s=$sucL[$suc[2]];
            if(empty($s)){$s=$suc[2];}
            if($arr[$s]){
                $arr[$s][1]=$arr[$s][1]+$v['cant'];
            }
            else {
                $arr[$s]=[$s,$v['cant']];
            }
            
        }
        $totalFactura=0;
        foreach ($arr as $key => $value) {
            $totalFactura+=$value[1];
            $facturasList.='<tr><td>'.$value[0].'</td><td align="center">'.$value[1].'</td></tr>';
        }
        $mensaje='<style>
                    td, th {
                        border: 1px solid black;
                    }
                    table{
                        border-collapse: collapse;
                    }
                    th{
                        background-color: #eaeaea;
                    }
                </style>
        <table width="100%" >
        <tr><th colspan="2">Consulta de '.$hoy2.' al '.$hoy.'</th><th>Total:'.$total.'</th></tr>
        <tr><th>Sucursal</th><th>Movimientos(COT,OV)</th><th>Pct. de uso</tr>
        '.$data23.'</table><br><br>'
        . '<table width="100%" >'
        . '<tr><th >Facturas realizadas por sucursal</th><th>Total: '.$totalFactura.'</th></tr>'
        . '<tr><th>Sucursal</th><th>Cantidad </th></tr>'
        .$facturasList
        . '</table>';          
        spl_autoload_register('DOMPDF_autoload');
        $pdf=new DOMPDF();
        $body .= file_get_contents(APPLICATION_PATH.'/configs/traspasosSolicitud.html');
        $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $pdf->load_html(utf8_decode($bodytag2));
        $paper_size = array(0,0,460,360);
        $pdf->set_paper('a4','portrait');
        $pdf->render();
        $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
        //$log->kardexLog("reportePDFFallas: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"ReportePDFFallas");
        exit();        
    }
    public function fallasPdfAction() {
        $log = new Application_Model_Userinfo();
        $titulo="Reporte de fallas inAX";
        require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
        
        $hoy2=filter_input(INPUT_GET,'f1');
        $hoy = filter_input(INPUT_GET,'f2');
        if(empty($hoy) & empty($hoy2)){
            $hoy=$hoy2=date("Y-m-d");
        }
        $ImpCotizacion="Fallas_Inax_".$hoy2."_".$hoy;
        $model=new Application_Model_FallasinaxModel();
        $totalWS= $model->getCountPeticiones($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $totalWSerror= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_ERROR);
        $totalWSok= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_OK);
        $totalWSprecio= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_PRECIO);
        $totalWSremision= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_REMISION);
        $totalWSconfirmacion= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_LINEAS);
        $totalWScotov= $model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_COT_OV);
        $totalWStbl=$model->getPeticionesTbl($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $enewClient=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_CLIENTE_NEW_ERROR);
        $epdf=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_IMPRESION_PDF_ERROR);
        /**/
        $totalWSPrecioComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_PRECIO_COMPLETE);
        $totalWSConfirmacionComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_LINEAS_COMPLETE);
        $totalWSRemisionComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_REMISION_COMPLETE);
        $totalWScotovComplete=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_COT_OV_COMPLETE);
        $newClient=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_CLIENTE_NEW);
        $pdf=$model->getCount($hoy2.' 00:00:00',$hoy.' 23:59:59',TOTAL_PETICIONES_WS_IMPRESION_PDF);
        $errorUser=$model->getErrorUserTbl($hoy2.' 00:00:00',$hoy.' 23:59:59');
        $mensaje='<style>
                    td, th {
                        border: 1px solid black;
                    }
                    table{
                        border-collapse: collapse;
                    }
                    th{
                        background-color: #eaeaea;
                    }
                </style>
        <table width="100%" > 
            <tr>
                <th colspan="4">Totales de peticiones del '.$hoy2.' al '.$hoy.'</th>
            </tr>
            <tr><td>Completados</td><td>';
        $tt=$totalWS['total'];
        $porcen=100;
        $te=$totalWSerror['total'];
        $tot=($te*100)/$tt;
        if($tot==0 & $tt==0){$tot=$porcen;} else {$tot=$porcen-$tot;} 
        $tt=$totalWSerror['total'];
        $mensaje.=round($tot,2).'%</td><td>No completados</td><td>';
        $tt=$totalWS['total'];
        $te=$totalWSerror['total'];
        $tot=($te*100)/$tt;
        $mensaje.=round($tot,2).'%
        </td></tr>
        <tr><td>Total</td><td>'.number_format($totalWS['total'],0,'.',',').'</td><td>Errores</td><td>'.number_format($totalWSerror['total'],0,'.',',').'</td></tr></table>
        <br><br>
        <table  width="100%">
            <tr><th>Tipo movimiento</th><th>Total</th><th>Total Error</th><th>Pct. de error</th></tr>
            <tr><td>Precios</td><td >'.number_format($totalWSPrecioComplete['total'],0,'.',',').'</td><td>'.number_format($totalWSprecio['total'],0,'.',',').'</td><td>'; 
            $te=$totalWSprecio['total'];
            $tot=($te*100)/$totalWSPrecioComplete['total'];
            $mensaje.=number_format($tot,2,'.',',').'%</td></tr>
            <tr><td>Cotización</td><td>'.  number_format($totalWSConfirmacionComplete['total'],0,'.',',').'</td><td>'.number_format($totalWSconfirmacion['total'],0,'.',',').'</td><td>';
            $te = $totalWSconfirmacion['total'];
            $tot = ($te * 100) / $totalWSConfirmacionComplete['total'];
            $mensaje.=round($tot,2).'%</td></tr>
            <tr><td>Remisiones (OV)</td><td >'.number_format($totalWSRemisionComplete['total'],0,'.',',').'</td><td>'. number_format($totalWSremision['total'],0,'.',',').'</td><td>';
            $te=$totalWSremision['total'];
            $tot=($te*100)/$totalWSRemisionComplete['total'];
            $mensaje.=round($tot,2).'%</td></tr>
            <tr><td>Cot a Ov</td><td >'.number_format($totalWScotovComplete['total'],0,'.',',').'</td><td>'.  number_format($totalWScotov['total'],0,'.',',').'</td><td>';
            $te=$totalWScotov['total'];
            $tot=($te*100)/$totalWScotovComplete['total'];
            $mensaje.=round($tot,2).'%</td></tr>
            <tr><td>Cliente Nuevo</td><td >'.number_format($newClient['total'],0,'.',',').'</td><td>'.number_format($enewClient['total'],0,'.',',').'</td><td>'; 
            $te=$enewClient['total'];
            $tot=($te*100)/$newClient['total'];
            $mensaje.=round($tot,2).'%</td></tr>
            <tr><td>Impresión en PDF</td><td >'.number_format($pdf['total'],0,'.',',').'</td><td>'. number_format($epdf['total'],0,'.',',').'</td><td>'; 
            $te=$epdf['total'];
            $tot=($te*100)/$pdf['total'];
            $mensaje.=round($tot,2).'%</td></tr>
        </table>' ;            
        spl_autoload_register('DOMPDF_autoload');
        $pdf=new DOMPDF();
        $body .= file_get_contents(APPLICATION_PATH.'/configs/traspasosSolicitud.html');
        $css =  file_get_contents(BOOTSTRAP_PATH.'css/bootstrap.min.css');
        $bodytag = str_replace("{MENSAJE}", $mensaje, $body);
        $bodytag2 = str_replace("{TITULO}", $titulo, $bodytag);
        $pdf->load_html(utf8_decode($bodytag2));
        $paper_size = array(0,0,460,360);
        $pdf->set_paper('a4','portrait');
        $pdf->set_paper($paper_size);
        $pdf->render();
        $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
        $log->kardexLog("reportePDFFallas: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"ReportePDFFallas");
        exit();
    }
}
