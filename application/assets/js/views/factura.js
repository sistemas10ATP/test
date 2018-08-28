/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$("#barraProgreso").remove();
$("#menuSuperior").remove();

function consulta(){
    var folio=$('input[name=factura]').val();
    var tipo=$('select[name=tipo]').val();
    if(folio!=''){
        switch(tipo) {
            
            case 'c':
                  $("#resultado").removeClass();
                  $("#resultado").addClass("alert alert-success alert-dismissable");
                  $("#resultado").html('¡Cotización encontrada! podras consultar en el siguiente enlace.  <a class="btn btn-warning" href="./impresion-cotizacion/?id='+folio+'" target="_blank">'+folio+'</a>');
                break;
            case 'r':
                $("#resultado").removeClass();
                $("#resultado").addClass("alert alert-success alert-dismissable");
                $("#resultado").html('¡Remisión encontrada! podras consultar en el siguiente enlace.  <a class="btn btn-warning" href="./impresion/?PackingSlipId='+folio+'" target="_blank">'+folio+'</a>');
                break;
            case 'f':
                $.ajax({
                    url: "factura/consulta",type: "POST",dataType: 'json', data: {'folio': folio},
                    beforeSend: function (xhr) {
                        $("#resultado").removeClass();
                        $("#resultado").addClass("alert alert-info");
                        $("#resultado").html('procesando datos... ');
                    },
                    success: function (data) {
                        if(data.TIPO !== 'false'){
                            $("#resultado").removeClass();
                            $("#resultado").addClass("alert alert-success alert-dismissable");
                            $("#resultado").html('¡Factura encontrada! podras consultar la factura en el siguiente enlace.  <a class="btn btn-warning" href="http://svr02:8989/FacturacionCajas/PDFFactura.php?factura='+folio+'&tipo='+data.TIPO+'" target="_blank">'+folio+'</a>');
                        }
                        else{
                            $("#resultado").removeClass();
                            $("#resultado").addClass("alert alert-danger alert-dismissable");
                            $("#resultado").html('¡Factura <b>no</b> encontrada! Favor de verificar el folio de factura');

                        }
                    },
                    error: function (x){
                        $("#resultado").removeClass();
                        $("#resultado").addClass("alert alert-danger alert-dismissable");
                        $("#resultado").html('<b>Error</b> la petición favor de intentar de nuevo si el problema persiste favor de reportarlo a sistemas');
                    }
                });
                break;
            default:
                
        }
        if(tipo=='c'){
            
        }
        else{
            
        }
    }
    else{
        $("#resultado").removeClass();
        $("#resultado").addClass("alert alert-warning alert-dismissable");
        $("#resultado").html('<b>¡Alto!</b> Folio de documento no debe estar en blanco');
        $('input[name=factura]').focus();
    }
}