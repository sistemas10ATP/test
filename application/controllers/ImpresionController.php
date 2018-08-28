<?php

require_once (LIBRARY_PATH.'/includes/dompdf/dompdf_config.inc.php');
require_once (LIBRARY_PATH.'/includes/code128.php');

class ImpresionController extends Zend_Controller_Inax
{

    public function init(){
        if(empty(COMPANY)){
            $this->_redirect('/login');
        }
        date_default_timezone_set('America/Chihuahua');
    }

    public function indexAction() {
        $datosinicio = new Application_Model_Userinfo();
        $ov = filter_input(INPUT_GET, 'PackingSlipId');
        if($ov!=""){
            $ImpRemision=$datosinicio->getRevision($ov);
        }
        else{
            $ImpRemision = filter_input(INPUT_POST, 'PackingSlipId');
        }        
        if($ImpRemision!=''){
            if (isset($_POST['EstadoEntrega'])) {
            $EdoEntrega = $_POST['EstadoEntrega'];
            } else {
                $EdoEntrega = "";
            }
            $this->_helper->layout()->disableLayout();
            $remision = $datosinicio->ImprimirRemision($ImpRemision);
            $flag=false;
            if(COMPANY=="ATP"){$flag=true;}
            require_once (LIBRARY_PATH . '/includes/code128.php');
            $pdf = new PDF_Code128();
            ///////////////////////////encabezado////////////////////////////////////////
            $pdf->title = utf8_decode('Remisión');
            $pdf->Header("marca de agua");
            $pdf->AliasNbPages();
                    $pdf->AddPage();
            $pdf->Code128(160, 15, $ImpRemision, 47, 9);
            $pdf->SetXY(95, 18);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell(13, 7, 'Sitio: ');
            $pdf->Cell(48, 7, $remision[0]['SitioNombre']);
            $pdf->SetXY(95, 25);
            $pdf->Cell(50, 7, $remision[0]['OrdenVenta']);
            $pdf->SetXY(10, 35);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(30, 7, utf8_decode('Versión: '));
            $pdf->Cell(70, 7, $remision[0]['Remision']);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(30, 7, utf8_decode('Imprime: '));
            $pdf->Cell(70, 7, $_SESSION['userInax']);
            $pdf->SetXY(10, 40);
            $pdf->Cell(30, 7, utf8_decode('Vendedor: '));
            $pdf->MultiCell(70, 5, wordwrap(utf8_decode($remision[0]['Vendedor']), 25, "\n"));
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(50, 7, utf8_decode('Dirección de Envío: '));
            $pdf->Cell(50, 7, utf8_decode('Dirección de Facturación: '));
            $pdf->Ln(5);
            $pdf->SetFont('Arial','', 8);
            $pdf->MultiCell(45, 4, wordwrap(utf8_decode($remision[0]['Cliente'])), 45, "\n");
            $pdf->MultiCell(45, 4, wordwrap(utf8_decode($remision[0]['DireccionEnvio'])), 45, "\n");
            if (strlen($remision[0]['Vendedor']) <= 25) {$y = 50;} else {$y = 55;}
            $pdf->SetXY(60, $y);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->MultiCell(45, 4, wordwrap(utf8_decode($remision[0]['Cliente'])), 45, "\n");
            $pdf->SetFont('Arial', '', 8);
                    $y2 = $pdf->GetY();
            $pdf->SetXY(60, $y2);
            $pdf->MultiCell(45, 4, wordwrap(utf8_decode($remision[0]['DireccionFacturacion'])), 45, "\n");
            $pdf->SetXY(110, 50);
                    $x = $pdf->getX();
                    $y = $pdf->getY();
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell(55, 7, 'Fecha: ');
            $pdf->SetFont('Arial', '', 13);
            $pdf->Cell(70, 7, $remision[0]['FechaCreacion']);
            $pdf->SetXY(110, 55);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell(55, 7, 'Modo de Entrega: ');
            $pdf->SetFont('Arial', '', 13);
            $pdf->Cell(70, 7, $remision[0]['ModoEntrega']);
            $pdf->SetXY(110, 60);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell(55, 7, 'Condiciones de Entrega: ');
            $pdf->SetFont('Arial', '', 13);
            $pdf->Cell(70, 7, $remision[0]['CondicionesEntrega']);
            $pdf->SetXY(110, 65);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell(55, 7, utf8_decode('Fecha/Hora Impresión: '));
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(60, 7, date('d/m/Y h:i:s a'));
            $pdf->SetXY($x, 75);
                    $pdf->Ln(5);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(40, 7, 'Comentarios: ');
            $pdf->MultiCell(120, 4, utf8_decode($remision[0]['comentarioCabecera']));
                    $pdf->Ln(5);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(40, 7, 'OrdenCliente: ');
            $pdf->Cell(70, 7, $remision[0]['ReferenciaCliente']);
                    $pdf->Ln(5);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(40, 7, 'Referencia Cliente: ');
            $pdf->Cell(70, 7, $remision[0]['OrdendeCliente']);
                    $pdf->Ln(10);
            $pdf->SetY($pdf->GetY() + 25);
            $pdf->SetFont('Arial', '', 10);

            $encabezadoArticulos = array(
                'Código Artículo',
                'Descripción',
                'Pedido',
                'Unidad',
                'Entregado'
            );

            foreach ($encabezadoArticulos as $col) {
                if ($col === 'Descripción') {
                    $pdf->Cell(75,7,utf8_decode($col),'B');	
                }else{
                    $pdf->Cell(28.75,7,utf8_decode($col),'B');
                }
            }
            $pdf->Ln(10);
            $anterior = '';
            $lote='t';
            $comAnt=$remision[$i]['Comentarios'];
            for ($i=0; $i < count($remision) ; $i++) {
                $pos =strpos($remision[$i]['Almacen'],'MRMA');
                $pdf->SetFont('Arial','B',9);
                $merma=0;
                if($pos===false){
                    $pdf->SetFillColor(229, 229, 229);
                    
                }
                else{
                    $merma=1;
                    $pdf->SetFillColor(229, 229, 229);
                }
               if (($anterior == $remision[$i]['ClaveArticulo']) && ( $remision[$i]['Cantidad'] != $remision[$i]['Pedido'])&& ($remision[$i]['Comentarios']==$comAnt)){
                    $y2 = $pdf->getY();
                    $pdf->SetXY($x+80,$y);
                    $pdf->Cell(30,7,$remision[$i]['Cantidad']);
                    $pdf->Cell(30,7,$remision[$i]['Unidad']);
                    $pdf->Cell(30,7,$remision[$i]['Cantidad']);
                    $pdf->Ln(5);
                    $pdf->SetFont('Arial','',8);
                    $pdf->SetY($y2); 
                    $pdf->Cell(30);
                    $pdf->Cell(25,4,'Cantidad: '.$remision[$i]['Pedido'],0, 0 , 'L', $pos);
                    $pdf->Cell(18,4,'Sitio: '.$remision[$i]['Sitio'],0, 0 , 'L', $pos);
                    $pdf->Cell(33,4,utf8_decode('Almacén: '.$remision[$i]['Almacen']),0, 0 , 'L', $pos);
                    $pdf->Cell(40,4,utf8_decode('Número de Lote: '.$remision[$i]['Lote']),0, 0 , 'L', $pos);
                    $pdf->Ln(4);
                    $pdf->Cell(30, 4);
                    $pdf->MultiCell(160, 4, wordwrap('Comentarios:' . utf8_decode($remision[$i]['Comentarios'])),117, "\n");
                    $pdf->Ln(7);
                    $anterior = $remision[$i]['ClaveArticulo'];
                    $lote=$remision[$i]['Lote'];
                }
                else {
                   // $pdf->Cell(0,15,'',0, 0 , 'L', $pos);//
                    $x = $pdf->getx();
                    $y = $pdf->getY();	
                     if($merma == 1){
                   
                     $pdf->Rect(($x),($y-2) , 300, 10,"F");
                         $pdf->Cell(30,7-3,$remision[$i]['ClaveArticulo'],0, 0 , 'L', $pos);
                        $pdf->Ln(0);
                        $pdf->Cell(30,10,"       MRMA",0, 0 );
                    } else {
                         $pdf->Cell(30,7-3,$remision[$i]['ClaveArticulo'],0, 0 , 'L', $pos);
                    }
                   
                    $x = $pdf->getx();
                    $y = $pdf->getY();	
                        
                    $pdf->MultiCell(80,4,utf8_decode($remision[$i]['Articulo']),0,'L',$pos);
                    $y2 = $pdf->getY();
                    $pdf->SetXY($x+80,$y);
                    $pdf->Cell(30,7,$remision[$i]['Cantidad'],0, 0 , 'L', $pos);
                    $pdf->Cell(30,7,$remision[$i]['Unidad'],0, 0 , 'L', $pos);
                    $pdf->Cell(30,7,$remision[$i]['Cantidad'],0, 0 , 'L', $pos);                    
                    $pdf->Ln(5);
                    $pdf->SetFont('Arial','',8);
                    $pdf->SetY($y2);
                    $pdf->Cell(30);
                    $pdf->Cell(25,4,'Cantidad: '.$remision[$i]['Pedido'],0, 0 , 'L', $pos);
                    $pdf->Cell(18,4,'Sitio: '.$remision[$i]['Sitio'],0, 0 , 'L', $pos);                    
                    $pdf->Cell(33,4,utf8_decode('Almacén: '.$remision[$i]['Almacen']),$merma, 0 , 'L', $pos);
                    $pdf->Cell(40,4,utf8_decode('Número de Lote: '.$remision[$i]['Lote']));
                    $pdf->Ln(4);
                    $pdf->Cell(30, 4);
                    $pdf->MultiCell(160, 4, wordwrap('Comentarios: ' . utf8_decode($remision[$i]['Comentarios'])),117, "\n");
                    $pdf->Ln(7);
                    $anterior = $remision[$i]['ClaveArticulo'];
                    $lote=$remision[$i]['Lote'];
               }
               $comAnt=$remision[$i]['Comentarios']; 
            }
            $pdf->Ln();
            $pdf->Output();
            $datosinicio->kardexLog("Impresion de remision: ".$ImpRemision." OV: ".$remision[0]['OrdenVenta'], $ImpRemision,$remision[0]['OrdenVenta'],1,"Impresion de remision");
            exit();
        }
    }
    
    
    public function testAction(){
        
        $datosinicio = new Application_Model_Userinfo();
        $ov = filter_input(INPUT_GET, 'PackingSlipId');
        if($ov!=""){
            $ImpRemision=$datosinicio->getRevision($ov);
        }
        else{
            $ImpRemision = filter_input(INPUT_POST, 'PackingSlipId');
        }        
        if($ImpRemision!=''){
            if (isset($_POST['EstadoEntrega'])) {
            $EdoEntrega = $_POST['EstadoEntrega'];
            } else {
                $EdoEntrega = "";
            }
            $this->_helper->layout()->disableLayout();
            $remision = $datosinicio->ImprimirRemision($ImpRemision);
            
            $this->view->remision = $remision;
            
        }
        
        $this->view->ImpRemision = $ImpRemision;
        
        $this->barcode("/tmp/".$ImpRemision.".png",$ImpRemision,50,'horizontal','code128',false);
        
        $body = $this->view->render("impresion/pdf.phtml");
        
        $this->html2pdf($body);
        exit();
    }
    
    public function pdfAction(){
        
        
        $datosinicio = new Application_Model_Userinfo();
        $ov = filter_input(INPUT_GET, 'PackingSlipId');
        if($ov!=""){
            $ImpRemision=$datosinicio->getRevision($ov);
        }
        else{
            $ImpRemision = filter_input(INPUT_POST, 'PackingSlipId');
        }        
        if($ImpRemision!=''){
            if (isset($_POST['EstadoEntrega'])) {
            $EdoEntrega = $_POST['EstadoEntrega'];
            } else {
                $EdoEntrega = "";
            }
            $this->_helper->layout()->disableLayout();
            $remision = $datosinicio->ImprimirRemision($ImpRemision);
            
            $this->view->remision = $remision;
            
        }
        
        
        $body = $this->view->render("impresion/pdf.phtml");
        
        spl_autoload_register('DOMPDF_autoload');
        $pdf=new DOMPDF();
        $pdf->load_html(utf8_decode($body));
        $pdf->set_paper('a4','portrait');
        $pdf->render();
        $pdf->stream($ov.".pdf",array( 'Attachment' => 0 ));
        exit();
        
    }
    
    
 //   barcode( $filepath, $text, $size, $orientation, $code_type, $print, $sizefactor );

    public function barcode( $filepath="", $text="0", $size="20", $orientation="horizontal", $code_type="code128", $print=false, $SizeFactor=1 ) {
	$code_string = "";
	// Translate the $text into barcode the correct $code_type
	if ( in_array(strtolower($code_type), array("code128", "code128b")) ) {
		$chksum = 104;
		// Must not change order of array elements as the checksum depends on the array's key to validate final code
		$code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","\`"=>"111422","a"=>"121124","b"=>"121421","c"=>"141122","d"=>"141221","e"=>"112214","f"=>"112412","g"=>"122114","h"=>"122411","i"=>"142112","j"=>"142211","k"=>"241211","l"=>"221114","m"=>"413111","n"=>"241112","o"=>"134111","p"=>"111242","q"=>"121142","r"=>"121241","s"=>"114212","t"=>"124112","u"=>"124211","v"=>"411212","w"=>"421112","x"=>"421211","y"=>"212141","z"=>"214121","{"=>"412121","|"=>"111143","}"=>"111341","~"=>"131141","DEL"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","FNC 4"=>"114131","CODE A"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
		$code_keys = array_keys($code_array);
		$code_values = array_flip($code_keys);
		for ( $X = 1; $X <= strlen($text); $X++ ) {
			$activeKey = substr( $text, ($X-1), 1);
			$code_string .= $code_array[$activeKey];
			$chksum=($chksum + ($code_values[$activeKey] * $X));
		}
		$code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

		$code_string = "211214" . $code_string . "2331112";
	} elseif ( strtolower($code_type) == "code128a" ) {
		$chksum = 103;
		$text = strtoupper($text); // Code 128A doesn't support lower case
		// Must not change order of array elements as the checksum depends on the array's key to validate final code
		$code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","NUL"=>"111422","SOH"=>"121124","STX"=>"121421","ETX"=>"141122","EOT"=>"141221","ENQ"=>"112214","ACK"=>"112412","BEL"=>"122114","BS"=>"122411","HT"=>"142112","LF"=>"142211","VT"=>"241211","FF"=>"221114","CR"=>"413111","SO"=>"241112","SI"=>"134111","DLE"=>"111242","DC1"=>"121142","DC2"=>"121241","DC3"=>"114212","DC4"=>"124112","NAK"=>"124211","SYN"=>"411212","ETB"=>"421112","CAN"=>"421211","EM"=>"212141","SUB"=>"214121","ESC"=>"412121","FS"=>"111143","GS"=>"111341","RS"=>"131141","US"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","CODE B"=>"114131","FNC 4"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
		$code_keys = array_keys($code_array);
		$code_values = array_flip($code_keys);
		for ( $X = 1; $X <= strlen($text); $X++ ) {
			$activeKey = substr( $text, ($X-1), 1);
			$code_string .= $code_array[$activeKey];
			$chksum=($chksum + ($code_values[$activeKey] * $X));
		}
		$code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

		$code_string = "211412" . $code_string . "2331112";
	} elseif ( strtolower($code_type) == "code39" ) {
		$code_array = array("0"=>"111221211","1"=>"211211112","2"=>"112211112","3"=>"212211111","4"=>"111221112","5"=>"211221111","6"=>"112221111","7"=>"111211212","8"=>"211211211","9"=>"112211211","A"=>"211112112","B"=>"112112112","C"=>"212112111","D"=>"111122112","E"=>"211122111","F"=>"112122111","G"=>"111112212","H"=>"211112211","I"=>"112112211","J"=>"111122211","K"=>"211111122","L"=>"112111122","M"=>"212111121","N"=>"111121122","O"=>"211121121","P"=>"112121121","Q"=>"111111222","R"=>"211111221","S"=>"112111221","T"=>"111121221","U"=>"221111112","V"=>"122111112","W"=>"222111111","X"=>"121121112","Y"=>"221121111","Z"=>"122121111","-"=>"121111212","."=>"221111211"," "=>"122111211","$"=>"121212111","/"=>"121211121","+"=>"121112121","%"=>"111212121","*"=>"121121211");

		// Convert to uppercase
		$upper_text = strtoupper($text);

		for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
			$code_string .= $code_array[substr( $upper_text, ($X-1), 1)] . "1";
		}

		$code_string = "1211212111" . $code_string . "121121211";
	} elseif ( strtolower($code_type) == "code25" ) {
		$code_array1 = array("1","2","3","4","5","6","7","8","9","0");
		$code_array2 = array("3-1-1-1-3","1-3-1-1-3","3-3-1-1-1","1-1-3-1-3","3-1-3-1-1","1-3-3-1-1","1-1-1-3-3","3-1-1-3-1","1-3-1-3-1","1-1-3-3-1");

		for ( $X = 1; $X <= strlen($text); $X++ ) {
			for ( $Y = 0; $Y < count($code_array1); $Y++ ) {
				if ( substr($text, ($X-1), 1) == $code_array1[$Y] )
					$temp[$X] = $code_array2[$Y];
			}
		}

		for ( $X=1; $X<=strlen($text); $X+=2 ) {
			if ( isset($temp[$X]) && isset($temp[($X + 1)]) ) {
				$temp1 = explode( "-", $temp[$X] );
				$temp2 = explode( "-", $temp[($X + 1)] );
				for ( $Y = 0; $Y < count($temp1); $Y++ )
					$code_string .= $temp1[$Y] . $temp2[$Y];
			}
		}

		$code_string = "1111" . $code_string . "311";
	} elseif ( strtolower($code_type) == "codabar" ) {
		$code_array1 = array("1","2","3","4","5","6","7","8","9","0","-","$",":","/",".","+","A","B","C","D");
		$code_array2 = array("1111221","1112112","2211111","1121121","2111121","1211112","1211211","1221111","2112111","1111122","1112211","1122111","2111212","2121112","2121211","1121212","1122121","1212112","1112122","1112221");

		// Convert to uppercase
		$upper_text = strtoupper($text);

		for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
			for ( $Y = 0; $Y<count($code_array1); $Y++ ) {
				if ( substr($upper_text, ($X-1), 1) == $code_array1[$Y] )
					$code_string .= $code_array2[$Y] . "1";
			}
		}
		$code_string = "11221211" . $code_string . "1122121";
	}

	// Pad the edges of the barcode
	$code_length = 20;
	if ($print) {
		$text_height = 30;
	} else {
		$text_height = 0;
	}
	
	for ( $i=1; $i <= strlen($code_string); $i++ ){
		$code_length = $code_length + (integer)(substr($code_string,($i-1),1));
        }

	if ( strtolower($orientation) == "horizontal" ) {
		$img_width = $code_length*$SizeFactor;
		$img_height = $size;
	} else {
		$img_width = $size;
		$img_height = $code_length*$SizeFactor;
	}

	$image = imagecreate($img_width, $img_height + $text_height);
	$black = imagecolorallocate ($image, 0, 0, 0);
	$white = imagecolorallocate ($image, 255, 255, 255);

	imagefill( $image, 0, 0, $white );
	if ( $print ) {
		imagestring($image, 5, 31, $img_height, $text, $black );
	}

	$location = 10;
	for ( $position = 1 ; $position <= strlen($code_string); $position++ ) {
		$cur_size = $location + ( substr($code_string, ($position-1), 1) );
		if ( strtolower($orientation) == "horizontal" )
			imagefilledrectangle( $image, $location*$SizeFactor, 0, $cur_size*$SizeFactor, $img_height, ($position % 2 == 0 ? $white : $black) );
		else
			imagefilledrectangle( $image, 0, $location*$SizeFactor, $img_width, $cur_size*$SizeFactor, ($position % 2 == 0 ? $white : $black) );
		$location = $cur_size;
	}
	
	// Draw barcode to the screen or save in a file
	if ( $filepath=="" ) {
		header ('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	} else {
		imagepng($image,$filepath);
		imagedestroy($image);		
	}
}
    
    
    
    
    
}

