/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$("#barraProgreso").remove();
$("#menuSuperior").remove();
$('input[name=fechaFiltro1]').val(fecha());
$('input[name=fechaFiltro2]').val(fecha());
function fecha(){ 
    var dateObj = new Date();
    var month = dateObj.getMonth() + 1; //months from 1-12
    var day = dateObj.getUTCDate();
    var year = dateObj.getUTCFullYear();
    return year + "-" + month + "-" + day;
}
function consulta(){
    var folio=$('input[name=factura]').val();
    var tipo=$('input[name=tipo]:checked').val();
    if(folio!=''){
        $.ajax({
            url: "errorFactura/filtro",type: "POST",dataType: 'json',data: {'folio': folio,'tipo':tipo},
            beforeSend: function (xhr) {
                $("#resultado").removeClass();
                $("#resultado").addClass("alert alert-info");
                $("#resultado").html('procesando datos... ');
            },
            success: function (data) {
                if(data.MONTO!==0){
                    $('#pant').val('Codigo de error: '+data.errorCFDI.Error.Codigo+'\n'+'Detalle: '+data.errorCFDI.Error.Detalle+'\n'+'Mensaje: '+data.errorCFDI.Error.Mensaje+'\n'+'Resultado: '+data.errorCFDI.Error.MensajeParaCliente+'\n');
                    $('#factura').val(data.FACTURA);
                    $('#ov').val(data.OV);
                    $('#fecha').val(data.FECHA);
                    $('#sucursal').val(data.SITIO);
                    $('#cliente').val(data.CLIENTE);
                    $('#rfc').val(data.RFC);
                    $('#moneda').val(data.MONEDA);
                    $('#monto').val(data.MONTO);
                    $('#usuario').val(data.CREADO);
                    $("#resultado").removeClass();
                    $("#resultado").html('');
                }
                else{
                    $("#resultado").addClass("alert alert-danger alert-dismissable");
                    $("#resultado").html('<button type="button" class="close" data-dismiss="alert">&times;</button><b>NO hay resultados</b> <br>¿Selecciono tipo de documento correcto?');
                }
            },
            error: function (x){
                $("#resultado").removeClass();
                $("#resultado").addClass("alert alert-danger alert-dismissable");
                $("#resultado").html('<button type="button" class="close" data-dismiss="alert">&times;</button><b>Error</b> la petición favor de intentar de nuevo si el problema persiste favor de reportarlo a sistemas');
            }
        });
    }
    else{
        $("#resultado").removeClass();
        $("#resultado").addClass("alert alert-warning alert-dismissable");
        $("#resultado").html('<b>¡Alto!</b> Folio de documento no debe estar en blanco');
        $('input[name=factura]').focus();
    }
}

function reset(){
    $('#pant').val('');
    $('#factura').val('');
    $('#ov').val('');
    $('#fecha').val('');
    $('#sucursal').val('');
    $('#cliente').val('');
    $('#moneda').val('');
    $('#monto').val('');
    $('#usuario').val('');
    $("#resultado").removeClass();
    $("#resultado").html('');
}
function filtroTabla(){
    var fecha1=$('input[name=fechaFiltro1]').val();
    var fecha2=$('input[name=fechaFiltro2]').val();
    var soloError=$('input[name=error]:checked').val();
    if(fecha1 !=="" && fecha2!=="" ){
        $.ajax({
            url: "errorFactura/filtroFecha",type: "POST",dataType: 'json',data: {'fechaFiltro1': fecha1,'fechaFiltro2':fecha2,'error':soloError},
            beforeSend: function (xhr) {
               $('#tabla').DataTable().destroy();
               $("#msj").html('<label style="color:red;">* Esta consulta puede tardar</label><img src="../application/assets/img/cargando.gif" style="width: 1.5em;margin-left:  1em;">');
            },
            success: function (data) {
                $('#tabla').DataTable( {
                    "searching": false,
                    "paging": true, 
                    "info": false,
                    "lengthChange":false ,
                    "order": [[ 2, "desc" ]],
                    data: data,
                    columns: [
                        {title: "Factura"},
                        {title: "OV"},
                        {title: "Fecha"},
                        {title: "Sucursal"},
                        {title: "Cliente"},
                        {title: "RFC"},
                        {title: "Moneda"},
                        {title: "Monto"},
                        {title: "Usuario"},
                        {title: "UUID"},
                        {title: "Detalle"}
                    ],
                    columnDefs: [
                        { targets: [0, 1],"width": "10%"},
                        { targets: '_all', "searchable": true }
                    ]
                } );
                $("#msj").html('');
            },
            error: function (x){
                $("#resultado").removeClass();
                $("#resultado").addClass("alert alert-danger alert-dismissable");
                $("#resultado").html('<button type="button" class="close" data-dismiss="alert">&times;</button><b>Error</b> la petición favor de intentar de nuevo si el problema persiste favor de reportarlo a sistemas');
            }
        });
    }
    else{
        
    }
}