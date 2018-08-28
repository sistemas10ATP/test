<?php
require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
require_once (LIBRARY_PATH.'/includes/code128.php');
class ImpresionCotizacionController extends Zend_Controller_Action{
    
    public function init(){
        if(empty(COMPANY)){
            $this->_redirect('/login');
        }
    }

    public function indexAction(){
        $subtotalX = 0;
	$body      = '';
        $flag=false;
        if(COMPANY=="ATP"){$flag=true;}
        // action body
        $this->_helper->layout()->disableLayout();
        $model = new Application_Model_CotizacionModel();
        $log = new Application_Model_Userinfo();
        date_default_timezone_set('America/Chihuahua');
        if(isset($_GET['id'])){
            $ImpCotizacion = filter_input(INPUT_GET,'id');
        }
        else {
            $ImpCotizacion = filter_input(INPUT_POST,'QuotationId');
        }
        if(empty($ImpCotizacion)){ echo "NO SE RECIBIO EL FOLIO DE COTIZACION"; exit();}
        $cargo=$model->getCargo($ImpCotizacion);
        $cargo=  round($cargo[0]['VALUE'], 2);
        $cabecera=$model->getCabezeraCotizacion($ImpCotizacion);
        $idDirec=$cabecera['vendedor'];
        $vendedor=$model->getVendedor($idDirec);
        $Cotizacion = $model->getDireccion($ImpCotizacion);
        $items =$model->getItems($ImpCotizacion);
        $almacen=$model->getAlmacenCotiza($items[0]['almacen']);
	$date2    = new DateTime();
        $date =$date2->format('d/m/Y');
        $moneda=$items[0]['Moneda'];
       	$tipoCambio = $_SESSION['tipoC'];
	$directorio=__DIR__."/../assets/img";
        $body.='<table style="width: 100%">
     <tr>';
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
            <img src="'.$directorio.'/imagepdf/public_1x.jpg"><a href="https://lideart.com.mx/">lideart.com.mx/</a><br>
        </td>';
        }
        $body.='<td style="font-size: 12px;width: 25%">
            <img src="'.$directorio.'/imagepdf/media_1x.jpg" width="200px" height="30px"><br><br>
            <label style="width: 20%; font-size: 18px;"><b>COTIZACION</b></label><br>
            <label>Número: </label><label>'.$ImpCotizacion.'</label><br>
            <label>Fecha: </label><label>'.$cabecera['Fecha'].'</label><br>
            <label>Fecha de Vencimiento: </label><label>'.$cabecera['FechaVencimiento'].'</label><br>
            <label>Pago: </label><label>'.$cabecera['Pago'].'</label>
        </td>
    </tr>
 </table>
<br><br>
<table>
    <tr>
        <td style="font-size: 12px;">
            <b>Direccion de envío:</b><br>
            <label >'.$cabecera['NombreEntrega'].'</label><br>
            <label >'.$Cotizacion['CALLE'].'</label><br>
            <label >'.$Cotizacion['COLONIA'].'</label>
            <label >'.$Cotizacion['CIUDAD'].','. $Cotizacion['ESTADO'] . ', ' . $Cotizacion['PAIS'].'</label>
            <label>'.$Cotizacion['ZIPCODE'].'</label>
        </td>
        <td style="font-size: 12px;">
            <label><b>Observaciones:</b></label><br>
            <label>El tipo de cambio del dia '.$date.' es de  '.$tipoCambio.' por USD</label>
        </td>
            </tr>
        </table>
<br><br>
        <table style="width:100% ;font-size: 12px;" >
            <tr>
                <td style="width: 15%;border-bottom: 2px solid black;">Código de artículo</td>
                <td style="width: 50%;border-bottom: 2px solid black; text-align: left">Descripción</td>
                <td style="width: 5%;border-bottom: 2px solid black; text-align: right;">Cantidad</td>
                <td style="width: 10%;border-bottom: 2px solid black;">Unidad</td>
                <td style="width: 5%;border-bottom: 2px solid black; text-align: right;"> P.Unitario</td>
                <td style="width: 10%;border-bottom: 2px solid black; text-align: right;">Importe</td>
            </tr>';  
            for ($i=0; $i < count($items) ; $i++) {
                $punitario=round($items[$i]['PrecioUnitario'],2)+($items[$i]['PrecioUnitario']*($cargo/100));
                $importe=round($items[$i]['Importe'],2)+($items[$i]['Importe']*($cargo/100));
                if($cargo==0){
                    $punitario=$items[$i]['PrecioUnitario'];
                    $importe=$items[$i]['Importe'];
                }
		$body      .= '	<tr><td style="vertical-align:top;">'.$items[$i]['CodigoArticulo'].'</td>';
		$body      .= '	<td style="vertical-align:top;">'.$items[$i]['DescripcionArticulo'].'</td>';
		$body      .= '	<td style="text-align: right;vertical-align:top;">'.round($items[$i]['Cantidad'],2).'</td>';
		$body      .= '	<td style="vertical-align:top;">'.$items[$i]['Unidad'].'</td>';
		$body      .= '	<td style="text-align:right;vertical-align:top;">'.round($punitario,2).'</td>';
		$body      .= '	<td style="text-align:right;vertical-align:top;">'.round($importe,2).'</td>';
		$body      .= '</tr>';
		$body      .= '<tr>';
		$body      .= '	<td ></td>';
		$body      .= '	<td colspan="2"><b>Cantidad:</b> '.round($items[$i]['Cantidad'], 2).' <b>Sitio:</b> '.$almacen['Sitio'].' <b>Almacen:</b> '.$almacen['Almacen'].' <br><b>Comentario:</b> '.$items[$i]['ComentarioLinea'].'</td>';
		$body      .= '	<td ></td>';
		$body      .= '	<td ></td>';
		$body      .= '	<td ></td></tr>';
                $subtotalX += $importe;
	}                
        $body.='<tr>
                <td colspan="4" style="text-align: right;"><b>Subtotal '.$moneda.'</b></td>
                <td colspan="2" style="text-align: right;">$ '.round($subtotalX,2).'</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right;"><b>IVA '.$moneda.'</b></td>
                <td colspan="2" style="text-align: right;">$ '.round($subtotalX*0.16,2).'</td>
            </tr>
            <tr>
                <td colspan="3">Vendedor: <b>'.$vendedor['NAME'].'</td>
                <td style="text-align: right;"><b>Total '.$moneda.'</b></td>
                <td colspan="2" style="text-align: right;">$ '.round($subtotalX*1.16,3).'</td>
            </tr>
        </table>';
    if($flag){    
        $body.='<table style="width: 100%">
            <tr>
                <td style="width: 100%;">
                    <table  style="width: 100%" cellpading="0" cellspacing="0">
                        <tr>
                            <td colspan="3"><b>Para su mayor comodidad, puede depositarnos en:</b></td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">BANCO</td>
                            <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">CUENTA</td>
                            <td style="width: 40%; font-size: 12px; background-color: #BDBDBD">CLABE INTERBANCARIA</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px;">BANCOMER</td>
                            <td style="width: 30%; font-size: 12px;">0442666073</td>
                            <td style="width: 40%; font-size: 12px;">012150004426660735</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px;">BANCOMER DOLARES</td>
                            <td style="width: 30%; font-size: 12px;">0442666065</td>
                            <td style="width: 40%; font-size: 12px;">012180004426660653</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px;">BANAMEX</td>
                            <td style="width: 30%; font-size: 12px;">673 SUC 4305</td>
                            <td style="width: 40%; font-size: 12px;">002150430500006734</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                            <td style="width: 30%; font-size: 12px;">65174302218</td>
                            <td style="width: 40%; font-size: 12px;">014150651743022180</td>
                        </tr>
                    </table>
                </td> 
            </tr><tr>
                <td style="page-break-after:always;width: 35%;font-size: 11px;">
                    <p><b>Observaciones importantes:</b></p>
                    <ul>
                        <li>Le solicitamos por favor antes de realizar su pago confirmar existencias.</li>
                        <li>Si su pago va a ser por transferencia, nos puede mandar
                            la imagen por correo o WhatsApp (+52 614 117 7072), para
                            embarcar su material, si se recibe antes de las
                            14:00 horas, se embarca ese mismo dia.</li>
                        <li>Si su material viaja por paqueteria será bajo la responsabilidad del destinatario.</li>
                    </ul>
                </td>
            </tr>
        </table>';        
    }
        else{
            $body.='<table style="width: 100%">
            <tr>
                <td style="width: 100%;">
                    <table  style="width: 100%" cellpading="0" cellspacing="0">
                        <tr>
                            <td colspan="3"><b>Para su mayor comodidad, puede depositarnos en:</b></td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">BANCO</td>
                            <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">CUENTA</td>
                            <td style="width: 40%; font-size: 12px; background-color: #BDBDBD">CLABE INTERBANCARIA</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                            <td style="width: 30%; font-size: 12px;">65506305031</td>
                            <td style="width: 40%; font-size: 12px;">014150655063050317</td>
                        </tr>
                        <tr>
                            <td colspan="3"><b>Para pago en OXXO:</b></td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px; background-color: #BDBDBD">BANCO</td>
                            <td colspan="2" style="width: 70%; font-size: 12px; background-color: #BDBDBD">CUENTA</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-size: 12px;">SANTANDER</td>
                            <td colspan="2" style="width: 70%; font-size: 12px;">4913277000212656</td>
                        </tr>
                    </table>
                </td>
            </tr><tr>
                <td style="page-break-after:always;width: 35%;font-size: 11px;">
                    <p><b>Observaciones importantes:</b></p>
                    <ul>
                        <li>Le solicitamos por favor antes de realizar su pago confirmar existencias.</li>
                        <li>Si su pago va a ser por transferencia, nos puede mandar
                            la imagen por correo, para
                            embarcar su material, si se recibe antes de las
                            14:00 horas, se embarca ese mismo dia.</li>
                        <li>Si su material viaja por paqueteria será bajo la responsabilidad del destinatario.</li>
                    </ul>
                </td>
            </tr>
        </table>';
        }
                
                spl_autoload_register('DOMPDF_autoload');
                //$filename=$ImpCotizacion."pdf";
                $pdf=new DOMPDF();
                //$content=$this->view->render($this->getViewScript());
                $pdf->load_html(utf8_decode($body));
                $pdf->set_paper('a4','portrait');
                $pdf->render();
                $pdf->stream($ImpCotizacion.".pdf",array( 'Attachment' => 0 ));
                $log->kardexLog("Impresion cotizacion: ".$ImpCotizacion, $ImpCotizacion,$ImpCotizacion,1,"Impresion cotizacion");
                exit();
        }/*fin de action*/
}

