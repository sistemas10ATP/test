<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ComisionespdfController
 *
 * @author sistemas10
 */
require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
class ComisionespdfController extends Zend_Controller_Inax {
    public function init(){
        if(empty(COMPANY)){
            $this->_redirect('/login');
        }
        date_default_timezone_set('America/Chihuahua');
    }
    function indexAction() {
        $body      = '';
        $flagN=false;
        $directorio=__DIR__."/../assets/img";
        $this->_helper->layout()->disableLayout();
        $ImpCotizacion="Reporte Comisiones";
        $nombre=  filter_input(INPUT_GET,'comisionista');
        if(!empty($nombre)){$flagN=true;}
        $mes=filter_input(INPUT_GET,'mes');
        $comisionista   = new Application_Model_ComisionesModel();
        $data=$comisionista->getReporteTbl(array(':comisionista'=>  '%'.$nombre.'%',':mes1'=>$mes,':mes'=>$mes));
        $totalMXN=0;
        $totalMXNCom=0;
        foreach ($data as $k => $v) {
            $totalMXNCom=$totalMXNCom+(double)$v['TOTALPAGO'];
            $totalMXN=$totalMXN+(double)$v['TOTAL'];
        }
        
        $flag=false;
        if(COMPANY=="ATP"){$flag=true;}
        
        $date2    = new DateTime();
        $date =$date2->format('d/m/Y');
         $body.='<table style="width: 100%"><tr>';
        if($flag){
         $body.='<td style="width: 15%;"><img src="'.$directorio.'/verticalLogo.jpg" width="150px;" height="150px;"></td>
        <td style="font-size: 12px;align-content: center;">
            <label ><b>Avance y Tecnología en Plásticos SA de CV</b></label><br>
            <img src="'.$directorio.'/imagepdf/location_on_1x.jpg"> AV. WASHINGTON #3701 <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
            <img src="'.$directorio.'/imagepdf/call_1x.jpg"> 01 614 432 6100<br>
            <img src="'.$directorio.'/imagepdf/public_1x.jpg"><a href="www.avanceytec.com.mx">www.avanceytec.com.mx</a><br>
        </td>';
        }
        else{
            $body.='<td style="width: 15%;"><img src="'.$directorio.'/lideart200x200.jpg" width="150px;" height="150px;"></td>
        <td style="font-size: 12px;align-content: center;">
            <label ><b>LIDEART INNOVACIÓN S DE R.L DE C.V</b></label><br>
            <img src="'.$directorio.'/imagepdf/location_on_1x.jpg"> CALLE WASHINGTON #3701 INT. 48-H <br> COMPLEJO INDUSTRIAL LAS AMERICAS<br> CHIHUAHUA,CHIH,MEX 31114<br>
            <img src="'.$directorio.'/imagepdf/call_1x.jpg"> 01 614 432 6122<br>
            <img src="'.$directorio.'/imagepdf/public_1x.jpg"><a href="https://lideart.com.mx/">https://lideart.com.mx/</a><br>
        </td>';
        }        
        $body.='<td style="font-size: 12px;width: 25%">
            <label style="width: 20%; font-size: 15px;"><label><b>'.$ImpCotizacion.'</b></label><br>
            <label>Fecha Impresión:       </label><label>         '.$date.'</label> 
        </td>
    </tr>
 </table>';
        $comi='<table style="width: 100%" ><tr><td><label><b></b></label></td><td><label></label></td></tr></table>';
        $body.='<br><br><br><table style="width: 100%" border="0" >';
                if($flagN){
                    $comi='<table style="width: 100%" ><tr><td><label><b>Comisionista</b></label></td><td style="text-align: right;"><label>'.$nombre.'</label></td></tr></table>';
                    
                }             
        $body.='<tr>'
                . '<td>'.$comi.'</td><td><table style="width: 100%" ><tr><td><label><b>Total MXN</b></label></td><td style="text-align: right;"><label>$'.number_format($totalMXN,2).'</label></td></tr></table></td></tr>'
             .'<tr>'
                . '<td>'
                   . '<table style="width: 100%" ><tr><td><label><b>Mes</b></label></td><td style="text-align: right;"><label>'.$_SESSION['MESES_STR'][$mes].'</label></td></tr></table>'
                . '</td>'
                . '<td><table style="width: 100%" ><tr><td><label><b>Total de Comision a Facturar</b></label></td><td style="text-align: right;"><label>$'.number_format($totalMXNCom,2).'</label></td></tr></table></td></tr>'
             . '</table>';
        if($flagN){
         $body.='<br><br><br><table style="width: 100%" border="0" ><tr style="background:#e8e8e8;"><th>Cliente</th><th>Articulo</th><th>Venta MXN</th><th>Comisión</th><th>Total</th></tr>';   
        }
        else{
         $body.='<br><br><br><table style="width: 100%" border="0" ><tr style="background:#e8e8e8;"><th>Comisiónista</th><th>Cliente</th><th>Articulo</th><th>Venta MXN</th><th>Comisión</th><th>Total</th></tr>';   
        }
        foreach ($data as $k => $v) {
            if($flagN){
                $body.='<tr style="font-size: 12px;"><td>'.$v['SALESNAME'].'</td><td>'.$v['ITEM'].'</td><td style="text-align: right;"><label>'.number_format($v['TOTAL'],2).'</label></td><td style="text-align: right;">'.$v['COMISION'].'</td><td style="text-align: right;">'.number_format($v['TOTALPAGO'],2).'</td></tr>';
            }
            else{
                $body.='<tr style="font-size: 12px;"><td>'.$v['NOMBRE'].'</td><td>'.$v['SALESNAME'].'</td><td>'.$v['ITEM'].'</td><td style="text-align: right;"><label>'.number_format($v['TOTAL'],2).'</label></td><td style="text-align: right;">'.$v['COMISION'].'</td><td style="text-align: right;">'.number_format($v['TOTALPAGO'],2).'</td></tr>';
            }
        }
        $body.='</table>';
        spl_autoload_register('DOMPDF_autoload');
        $pdf=new DOMPDF();
        $pdf->load_html(utf8_decode($body));
        $pdf->set_paper('a4','portrait');
        $pdf->render();
        $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
        $log->kardexLog("Impresion comisionista: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"comisiones");
        exit();
    }
}
