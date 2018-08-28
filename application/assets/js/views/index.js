function checarDatosCliente(){
        var bandDtCte = true;
        $('.datoEtiquetaCliente').each(function(){
            valor = $(this).val();
            if ($(this).val() === '' || $(this).val() === 'null' || $(this).type === 'undefined'){
                bandDtCte = false;
                return bandDtCte;
            }
        });
        return bandDtCte;
}
function setPayMode(payMent,payMode){
    var payArr=[];
        var formaEntrega=$("#domicilioF").val();
        if(formaEntrega==="DOMICILIO" && payMent==='CONTADO'){
            payArr.push({PAYMMODE: "03", name: "TRANSFERENCIA ELECTRÓNICA DE FONDOS"});
        }
        else if(payMent==='CONTADO'){
            $.each(payModeList,function (i,v){
                if(v.PAYMMODE!='99'){
                   payArr.push(v); 
                }
            });            
        }
        else{
            $.each(payModeList,function (i,v){
                if(v.PAYMMODE===payMode){
                   payArr.push(v); 
                }
            });
            payArr.push({PAYMMODE: "99", name: "OTROS"});
        }
    var html='';
    $('#cargo').html(html);
    $.each(payArr,function (i,v){
        var sel='';
        if(v.PAYMMODE===payMode){sel='selected';}
        html+='<option value="0.00" data-paymmode="'+v.PAYMMODE+'" '+sel+'>'+v.name+'</option>';
    });
    $('#cargo').html(html);
}
function factura2(ov,rem,entrega){
    $('#domicilioF').val(entrega);
    $('#ovLbl').html(ov);
    $('#remLbl').html(rem);
    $('#btnDiarioIndex').remove();
    var factura=$('#loadFacturaSt').text();
    if(factura!==""){
        $('#loadFacturaSt').html('');
        $('#btnFacturar').show();
    }
    $.ajax({url:"index/get-Data-Ov",type: 'POST',data: {ov:ov},dataType: 'json',
        beforeSend: function (xhr) {
        },
        success: function (data, textStatus, jqXHR) {
            var htmlFP='';
            $.each(payTerm,function (i,v){
                var sel='';
                if(v.PAYMTERMID===data[0].PAYMENT){sel='selected=""';}                
                htmlFP+='<option  value="'+v.PAYMTERMID+'" '+sel+'>'+v.PAYMTERMID+' - '+v.DESCRIPTION+'</option>';
            });
            $('#entregaF').html(htmlFP);
            paymode=data[0].PAYMMODE;
            setPayMode(data[0].PAYMENT,paymode);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            Materialize.toast("hubo un error "+catchError(jqXHR,errorThrown),'red');
        }
    });    
    $.ajax({
            url:"inicio/get-Direcciones",type:"post", data:{ov:ov},dataType: 'json',
            beforeSend: function (xhr) {
                $('#btnFacturar').hide();
            },
            success: function (data, textStatus, jqXHR) {
                var str="";
                $('#direccionF').html(str);
                $.each(data,function (i,v){
                    str+='<option value="'+v.RECID+'">'+v.ADDRESS+'</option>';
                });
                $('#direccionF').html(str);
                $('#btnFacturar').show();
            }            
        });
        $('#modalFactura').openModal({dismissible: false});
}
function facturarClose(){
    var factura=$('#loadFacturaSt').text();
    if(factura!==""){
        $('#usuarioov').click();
    }
    $('#modalFactura').closeModal();
}
function facturar(){
    if(havePermision(14) && $('#remLbl').text()===''){
        var ov=$('#ovLbl').text();
        ValidarLimiteCreditoRemision(ov,usuario,'');
    }
    else{
        if(havePermision(1)){
            $.ajax({
                url:"inicio/facturar",type:"post", data:{
                    ov:$('#ovLbl').text(),
                    remision:$('#remLbl').text(),
                    ordenCliente:$('#OrdenCliente').val(),
                    refCliente:$('#ReferenciaCliente').val(),
                    comentariosCabecera:$('#comentariosCabecera').val(),
                    direccion:$('#direccionF').val(),
                    usoCFDi:$('#usoCFDI option:selected').val(),
                    pagoModo:$('#tipoCobro option:selected').attr('data-paymmode'),
                    pago:$('#entregaF').val()
                },dataType: 'json',
                beforeSend: function (xhr) {
                    $('#loadFacturaSt').html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                    if(data.resultado=="ok"){
                        Materialize.toast("Factura "+data.respuesta,10000);
                        $('#loadFacturaSt').html('<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='+$('#ovLbl').text()+'&amp;tipo=CLIENTE" target="_blank">'+data.respuesta+'</a>');
                        $("#btnFacturar").hide();
                        if(havePermision(12)){
                                $('#footerFactura').append('<a onclick="mostrarModalPago()" id="btnDiarioIndex" class="waves-effect light-blue darken-4 white-text btn-flat" style="margin-right: 13px;">Asociar Factura a Diario de Pago</a>');
                            }
                    }
                    else if(data.resultado=="bad"){
                        $('#loadFacturaSt').html('<label style="color:red;">'+data.respuesta+'</label>');
                    }
                    else{
                        $('#loadFacturaSt').html('<a>'+data.respuesta+'</a>');
                    }                
                }            
            });
        }
        else{
            alert('No tiene permisos para facturar');
        }
    }
}
function archivoAdjunto(id,tipo){
    var restriccion='';
    $.ajax({
        url:"inicio/get-Archivo-Adjunto",type: "GET",dataType: "JSON", data: {id:id,transaction:tipo},
        success: function (res){
            console.log(res);
            $('#formDatosAdj')[0].reset();
            $('#descAdj').css('height','100%');
            if (res.length>0){
                $('#modalDatosAdjuntos #fechaAdj').val(res[0].CREATEDDATETIME);
                $('#modalDatosAdjuntos #tipoAdj').val(res[0].TYPEID);
                $('#modalDatosAdjuntos #descAdj').val(res[0].NAME);
                if ( res[0].RESTRICTION == '0' ){
                    restriccion = 'Interno';
                }
                $('#modalDatosAdjuntos #restAdj').val(restriccion);
                $('#modalDatosAdjuntos #notasAdj').val(res[0].NOTES);
                $('#modalDatosAdjuntos').openModal();
            }
            else{
                swal({
                    title: ""+id,
                    text: " no contiene adjuntos",
                    icon: "info",
                    button: "Cerrar"
                 });
            }
            
        }
    });
}
function getGuiaPaq(id,tipo){
    var restriccion='';
    $.ajax({
        url:"inicio/get-Archivo-Adjunto",type: "GET",dataType: "JSON", data: {id:id,transaction:tipo},
        success: function (res){
            console.log(res);
            $('#formDatosAdj')[0].reset();
            $('#descAdj').css('height','100%');
            if (res.length>0){
                $('#modalDatosAdjuntos #fechaAdj').val(res[0].CREATEDDATETIME);
                $('#modalDatosAdjuntos #tipoAdj').val(res[0].TYPEID);
                $('#modalDatosAdjuntos #descAdj').val(res[0].NAME);
                if(res[0].TYPEID=="Paqueteria"){
                    $('#modalDatosAdjuntos #notasAdj').val(res[0].NAME);
                }
                if ( res[0].RESTRICTION == '0' ){
                    restriccion = 'Interno';
                }
                $('#modalDatosAdjuntos #restAdj').val(restriccion);
                $('#modalDatosAdjuntos #notasAdj').val(res[0].NOTES);
                $('#modalDatosAdjuntos').openModal();
            }
            else{
                swal({
                    title: ""+id,
                    text: " no contiene adjuntos",
                    icon: "info",
                    button: "Cerrar"
                 });
            }
            
        }
    });
}
function convertirCot(cot,boton){
    $('#modalLoading').openModal();
    $.ajax({url: "inicio/convertirCotOV",type: "post",dataType: "json",
        data: { "cotizacion": cot },
        success: function (data){
            if(data.status != 'Fallo'){
                $('#misCot').click();
                Materialize.toast('Cotización convertida a Orden de Venta!', 3000);
            }else{
                Materialize.toast('Error en dynamics:'+data.msg, 3000);
            }
            $('#modalLoading').closeModal();
        },
        error: function (data){
            $('#modalLoading').closeModal();
            Materialize.toast('WebService Error!.', 3000);
        }
    });
}
        $(document).on('focusin','.generarOV',function(){
            $(this).removeClass('darken-3');
            $(this).addClass('darken-5');
        });
        $(document).on('focusout','.generarOV',function(){
            $(this).removeClass('darken-5');
            $(this).addClass('darken-3');
        });
        $(document).on('focusin','.generarCTZN',function(){
            $(this).removeClass('darken-3');
            $(this).addClass('darken-5');
        });
        $(document).on('focusout','.generarCTZN',function(){
            $(this).removeClass('darken-5');
            $(this).addClass('darken-3');
        });        
        $('#breadInicio').hide();
        $('#breadArticulos').hide();        
        $('#breadResumen').hide();
        $('#editarDocument').hide();
        if ($('#divInicioSesion').hasClass('s5')){
            $('#divInicioSesion').removeClass('s5');
            $('#divInicioSesion').addClass('s9');
        }else if ($('#divInicioSesion').hasClass('s9')){
           $('#divInicioSesion').removeClass('s9');
           $('#divInicioSesion').addClass('s9');
        }
        //Se muestra todas las ordenes de venta al dar click en dicha opcion
        $("#todas").on("click", function(){
                $('#pasaForm').html('');
                $('#modalLoading').openModal();
                $("nav").find(".active").removeClass("active");
                $(this).parent().addClass("active");                        
                $.ajax({
                    url: "index/todasOV2",type: "POST",dataType: 'json',
                    beforeSend: function (xhr) {
                        $('#modalLoading').openModal();
                        checkTableisDataTable();
                   },
                    success: function (data, textStatus, jqXHR) {
                        $('#ov').DataTable({
                           "destroy": true,
                            "search":true,
                            "paging": true, 
                            "info": false,
                            "order": [[ 1, "desc" ]],
                            data: data,
                            columns: [ 
                                {title: "Detalle"},
                                {title: "Orden de Venta"},
                                {title: "Fecha"},
                                {title: "Código Cliente"},
                                {title: "Cliente"},
                                {title: "Sitio"},
                                {title: "Almacén"},
                                {title: "Modo de Entrega"},
                                {title: "Vendedor"},
                                {title: "Estado"},
                                {title: "Estado de Entrega"},
                                {title: "Imprimir"}
                            ]
                        });
                        $('#modalLoading').closeModal();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        
                    }
                });
            });
        //Se muestran las ordenes de venta del usuario logueado
        $("#usuarioov").on("click", function(){
                $('#pasaForm').html('');
                $("nav").find(".active").removeClass("active");
                $(this).parent().addClass("active"); 
                $('#modalLoading').openModal();
                $.ajax({
                    url: "index/ovUser2",type: "POST",dataType: 'json',
                    beforeSend: function (xhr) {
                        $('#modalLoading').openModal({dismissible: false});
                        checkTableisDataTable();
                    },
                    success: function (data, textStatus, jqXHR) {
                        $('#ov').DataTable({
                            "destroy": true,
                            "search":true,
                            "paging": true, 
                            "info": false,
                            "order": [[ 1, "desc" ]],
                            data: data,
                            columns: [ 
                                {title: "Detalle"},
                                {title: "Orden de Venta"},
                                {title: "Fecha"},
                                {title: "Código Cliente"},
                                {title: "Cliente"},
                                {title: "Sitio"},
                                {title: "Almacén"},
                                {title: "Modo de Entrega"},
                                {title: "Estado"},
                                {title: "Estado de Entrega"},
                                {title: "Generar Remisión"},
                                {title: "Imprimir"}
                            ]
                        });
                        $('#modalLoading').closeModal();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#modalLoading').closeModal();
                    }
                });                
            });
        //Se muestran las cotizaciones del usuario logueado
        $("#misCot").on("click", function(){
            $('#pasaForm').html('');
            $("nav").find(".active").removeClass("active");
            $(this).parent().addClass("active"); 
            $('#modalLoading').openModal();
            $.ajax({
                url: "index/misCOT2",type: "POST",dataType: 'json',
                beforeSend: function (xhr) {
                    $('#modalLoading').openModal({dismissible: false});
                    checkTableisDataTable();
                },
                success: function (data, textStatus, jqXHR) {
                    
                    $('#ov').DataTable({
                        "destroy": true,
                        "search":true,
                        "paging": true, 
                        "info": false,
                        "order": [[ 1, "desc" ]],
                        data: data,
                        columns: [ 
                            {title: "Detalle"},
                            {title: "Cotización"},
                            {title: "Fecha"},
                            {title: "Código Cliente"},
                            {title: "Cliente"},
                            {title: "Sitio"},
                            {title: "Almacén"},
                            {title: "Modo de Entrega"},
                            {title: "Estado"},
                            {title: "Convertir OV"},
                            {title: "Imprimir"}
                        ]
                    });
                    $('#modalLoading').closeModal();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#modalLoading').closeModal();
                }
            });         
        });
//Se muestran todas las cotizaciones
$("#todasCot").on("click", function(){
       $('#pasaForm').html('');
       $('#modalLoading').openModal();
       $("nav").find(".active").removeClass("active");
       $(this).parent().addClass("active");  
       $.ajax({
       url: "index/todasCOT2",type: "POST",dataType: 'json',
       beforeSend: function (xhr) {
           $('#modalLoading').openModal();
            checkTableisDataTable();
       },
       success: function (data, textStatus, jqXHR) {
           $('#ov').DataTable({
               "destroy": true,
               "search":true,
               "paging": true, 
               "info": false,
               "order": [[ 1, "desc" ]],
               data: data,
               columns: [ 
                   {title: "Detalle"},
                   {title: "Cotización"},
                   {title: "Fecha"},
                   {title: "Código Cliente"},
                   {title: "Cliente"},
                   {title: "Sitio"},
                   {title: "Almacén"},
                   {title: "Modo de Entrega"},
                   {title: "Vendedor"},
                   {title: "Estado"},
                   {title: "Convertir OV"},
                   {title: "Imprimir"}
               ]
           });
           $('#modalLoading').closeModal();
       },
       error: function (jqXHR, textStatus, errorThrown) {

       }
   }); 
});


function checkTableisDataTable(){
    if ($.fn.dataTable.isDataTable('#ov')) {
        $('#ov').DataTable().destroy();
        $('#ov').empty();
    }
}
        function GenerarRemisionBtn(condiEntrega,ov,usuario){
            ValidarLimiteCredito(ov,usuario,condiEntrega);
        }
        function imprimirRemision(Remision,edoentrega){
            $('#rem').val(Remision);
            $('#edoentrega').val(edoentrega);
            $('#imprimirRemision').submit();
        }

        function imprimirCotizacion(Cotizacion,NumCotizaciones){
            $('#DCotizacion').val(Cotizacion);
            $('#CountCotizacion').val(NumCotizaciones);
            $('#imprimirCotizacion').submit();
        }
        
       function generarEtiquetas(sitio,ov,proposito,recid){        
        $.ajax({
            url : "index/datosEtiqueta",type : "POST",dataType : "JSON",
            data : {"sitio":sitio,"ov":ov},
            beforeSend: function (xhr) {
                $("#loading").html('<img src="'+loading+'/cargando.gif" style="width: 1em;">');
            },
            success : function(res){
                datosSucu = res.datosSucu;
                datosClte = res.datosCte;
                datosDirs = res.datosDirs;
                datosMonto = res.datosMonto;
                direccionCliente(proposito,datosDirs,recid);
                $(datosSucu).each(function(){
                    $('#tablaEtiqueta .calle').val(this.calle);
                    $('#tablaEtiqueta .colonia').val(this.colonia);
                    $('#tablaEtiqueta .estado').val(this.estado);
                    $('#tablaEtiqueta .telefono').val(this.telefono);
                });                
                clteCompleto = ov + ' - ' + datosClte[0].CUSTACCOUNT + ' - ' + datosClte[0].NOMBRECLIENTE;
                var fecha = new Date();
                
                var monto = '';
                $('#tablaEtiqueta .monto').val(datosMonto.substr(0,( datosMonto.indexOf('.')+3 ) )).formatCurrency({roundToDecimalPlace:'2'});
                monto = $('#tablaEtiqueta .monto').val();
                $('#tablaEtiqueta .monto').val('Monto: ' + monto);
                $('#tablaEtiqueta .cliente-top').val(clteCompleto);
                $('#tablaEtiqueta .rfc-cte').val('RFC: ' + datosClte[0].RFC);
                $('#tablaEtiqueta .correo-cte').val('EMAIL: ' + datosClte[0].EMAIL);
                $('#tablaEtiqueta .tel-cte').val('Telefono: ' + datosClte[0].TELEFONO + ' ext: ' + datosClte[0].EXTENSION);
                $('#tablaEtiqueta .cliente-bot').val(clteCompleto);
                $('#tablaEtiqueta .userEti').html('Usuario: '+user+' ('+datosClte[0].NOMBREVENDEDOR+')');
                $('#tablaEtiqueta .fechaEti').html('Fecha de creacion: '+fecha.toLocaleDateString()+' - '+fecha.toLocaleTimeString());
                $('#tablaEtiqueta .rfc-cte').removeAttr('readonly');
                $('#tablaEtiqueta .tel-cte').removeAttr('readonly');
                $('#tablaEtiqueta').show();
                $("#loading").html('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#loading").html("Ocurrio un error favor de comunicarlo al dpto de sistemas.");
            }
        });
    }
        function setComparativa(){
            $.ajax({
                url:'index',type:'GET',dataType:'JSON',data:{'token':'comparativa'},
                success: function(res){
                    if (res[0] != 'NoResults'){
                        /////////////////////////////////ventas///////////////////////////////////////////////////
                        if (res['WS_VTA'] != '0'){
                            porcentajeVentaWS = eval(res['WS_VTA']) / eval(res['TOTAL_VTA']);
                        }else{
                            porcentajeVentaWS = 0;
                        }
                        blue = porcentajeVentaWS.toFixed(4);
                        if (res['DYN_VTA'] != '0'){
                            porcentajeVentaDYN = eval(res['DYN_VTA']) / eval(res['TOTAL_VTA']);
                        }else{
                            porcentajeVentaDYN = 0;
                        }
                        red = porcentajeVentaDYN.toFixed(4);
                        $('#red').css('color','red');
                        $('#red').css('text-align','center');
                        $('#red').css('width','100%');
                        $('#red').css('height','75px'); 
                        $('#red').css('bottom','0px');
                        $('#red').css('font-size','11px');
                        $('#red').css('background','-webkit-linear-gradient(white '+( (1-red)*100 )+'%, #8A4B08, #8A4B08, #8A4B08');
                        $('#red').html( (red*100).toFixed(2) + '%' );
                        $('#redLabelVta').html('Dynamics<br/><b>('+res['DYN_VTA']+')</b>');
                        $('#blue').css('color','red');
                        $('#blue').css('text-align','center');
                        $('#blue').css('width','100%');
                        $('#blue').css('height','75px'); 
                        $('#blue').css('bottom','0px');
                        $('#blue').css('font-size','11px');
                        $('#blue').css('background','-webkit-linear-gradient(white '+( (1-blue)*100 )+'%, #0404B4, #0404B4, #0404B4');
                        $('#blue').html( (blue*100).toFixed(2) + '%' );
                        $('#blueLabelVta').html('inAX<br/><b>('+res['WS_VTA']+')</b>');
                        ////////////////////cotizaciones/////////////////////////////////////////////////////////////////////
                        if (res['WS_COT'] != '0'){
                            porcentajeCotiWS = eval(res['WS_COT']) / eval(res['TOTAL_COT']);
                        }else{
                            porcentajeCotiWS = 0;
                        }
                        blueCoti = porcentajeCotiWS.toFixed(4);
                        if (res['DYN_COT'] != '0'){
                            porcentajeCotiDYN = eval(res['DYN_COT']) / eval(res['TOTAL_COT']);
                        }else{
                            porcentajeCotiDYN = 0;
                        }
                        redCoti = porcentajeCotiDYN.toFixed(4);
                        $('#redCoti').css('color','red');
                        $('#redCoti').css('text-align','center');
                        $('#redCoti').css('width','100%');
                        $('#redCoti').css('height','75px'); 
                        $('#redCoti').css('bottom','0px');
                        $('#redCoti').css('font-size','11px');
                        $('#redCoti').css('background','-webkit-linear-gradient(white '+( (1-redCoti)*100 )+'%, #8A4B08, #8A4B08, #8A4B08');
                        $('#redCoti').html( (redCoti*100).toFixed(2) + '%' );
                        $('#redLabelCoti').html('Dynamics<br/><b>('+res['DYN_COT']+')</b>');
                        $('#blueCoti').css('color','red');
                        $('#blueCoti').css('text-align','center');
                        $('#blueCoti').css('width','100%');
                        $('#blueCoti').css('height','75px'); 
                        $('#blueCoti').css('bottom','0px');
                        $('#blueCoti').css('font-size','11px');
                        $('#blueCoti').css('background','-webkit-linear-gradient(white '+( (1-blueCoti)*100 )+'%, #0404B4, #0404B4, #0404B4');
                        $('#blueCoti').html( (blueCoti*100).toFixed(2) + '%' );
                        $('#blueLabelCoti').html('inAX<br/><b>('+res['WS_COT']+')</b>');
                    }
                }
            });
        }
function detalleVenta2(ov,trans){
    $('#modalLoading').openModal();
        var html  = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; border-top: solid 1px; border-right: solid 1px; border-left: solid 1px; border-bottom: solid 1px;">';
        html += '<thead>';
        html += '    <th># Linea</th><th>Folio</th><th>Codigo de Articulo</th><th>Nombre</th><th>Cantidad</th><th>Unidad</th>';
        html += '</thead>';
        html += '<tbody>';
        $.ajax({ url:"inicio/detalleVenta", type: "POST", dataType: "json",data: {"ov":ov,"transaction":trans},
            success: function (res){
                $.each(res,function(i,d){
                    var numlinea = eval(d.LINENUM);
                    var qty      = eval(d.QTYORDERED);
                    html += '<tr style="border-bottom:solid 1px">';
                    html += '   <td>'+numlinea.toFixed(2)+'</td>';
                    html += '   <td>'+d.SALESID+'</td>';
                    html += '   <td>'+d.ITEMID+'</td>';
                    html += '   <td>'+d.NAME+'</td>';
                    html += '   <td>'+qty.toFixed(2)+'</td>';
                    html += '   <td>'+d.SALESUNIT+'</td>';
                    html += '</tr>';
                });
                html += '</tbody>';
                html += '</table>';
                $("#detalleDoc").html('');
                $("#modalDocumentContent").html('');
                $('#modalLoading').closeModal();
                $("#detalleDoc").html(ov);
                $("#modalDocumentContent").append(''+html);
                $("#modalDetalleDoc").openModal();
            }
        });
        
}
function detalleVenta(ov,obj,tipo,trans){
            var tr    = $(obj).closest('tr');
            if (tipo == 'ovuser'){
                var table = $('#ov').DataTable();
            }else if (tipo == 'todasov'){
                var table = $('#todasov').DataTable();
            }else if (tipo == 'usercot'){
                var table = $('#usuariocot').DataTable();
            }else if (tipo == 'todascot'){
                var id = $(obj).closest('table').attr('id');
                var table = $('#'+id).DataTable();
            }
            var row   = table.row(tr);            
            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                $(obj).html('add_circle');
                $(obj).attr('style','color:green;cursor:pointer');
            }
            else {
                // Open this row
                $(obj).html('remove_circle');
                $(obj).attr('style','color:red;cursor:pointer');
                $('#modalLoading').openModal();
                var html  = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; border-top: solid 1px; border-right: solid 1px; border-left: solid 1px; border-bottom: solid 1px;">';
                html += '<thead>';
                html += '    <th># Linea</th>';
                if ( trans == 'ORDVTA' ){
                    html += '    <th>Orden de Venta</th>';
                }else{
                    html += '    <th>Cotizacion</th>';
                }
                html += '    <th>Codigo de Articulo</th>';
                html += '    <th>Nombre</th>';
                html += '    <th>Cantidad</th>';
                html += '    <th>Unidad</th>';
                html += '</thead>';
                html += '<tbody>';
                $.ajax({ url:"inicio/detalleVenta", type: "POST", dataType: "json",
                    data: {"ov":ov,"transaction":trans},
                    success: function (res){
                        $(res).each(function(){
                            numlinea = eval(this.LINENUM);
                            qty      = eval(this.QTYORDERED);
                            html += '<tr style="border-bottom:solid 1px">';
                            html += '   <td>'+numlinea.toFixed(2)+'</td>';
                            html += '   <td>'+this.SALESID+'</td>';
                            html += '   <td>'+this.ITEMID+'</td>';
                            html += '   <td>'+this.NAME+'</td>';
                            html += '   <td>'+qty.toFixed(2)+'</td>';
                            html += '   <td>'+this.SALESUNIT+'</td>';
                            html += '</tr>';
                        });
                        html += '</tbody>';
                        html += '</table>';
                        row.child(html).show();
                        tr.addClass('shown');
                        $('#modalLoading').closeModal();
                    }
                });                
            }            
        }

function editarOV(DocumentId,cliente){
    $('#editarCotForm #documentType').val('ORDVTA');
    $('#editarCotForm #DocumentId').val(DocumentId);
    $('#editarCotForm #cliente').val(cliente);
    $('#editarCotForm #editar').val('1');
    $('#editarCotForm').submit();
}
function editarCot(DocumentId,cliente){
    $('#editarCotForm #documentType').val('CTZN');
    $('#editarCotForm #DocumentId').val(DocumentId);
    $('#editarCotForm #cliente').val(cliente);
    $('#editarCotForm #editar').val('1');
    $('#editarCotForm').submit();
}

function direccionCliente(proposito,direcciones,recid){
        var dirMuestra = $.map(direcciones,function(dirs){
            if(dirs.PROPOSITO == proposito && dirs.RECID == recid){
                dirTemp   = dirs.ADDRESS.split("\n");
                dirCalle  = dirs.STREET;
                dirColon  = dirs.COUNTY;
                dirEstad  = dirs.STATE;
                dirCiudad = dirs.CITY;
                dirCodPo  = dirs.ZIPCODE;
                dirPais   = dirs.PAIS;
                direccion = {'calle':dirCalle,'colonia':dirColon,'estado':dirEstad,'ciudad':dirCiudad,'cp':dirCodPo,'pais':dirPais};
                return direccion;
            }
        });
        $(dirMuestra).each(function(){
            if (this.calle != 'NoDefinido'){
                $('#tablaEtiqueta .calle-cte').val(this.calle + ' C.P. '+ this.cp);
            }else{
                $('#tablaEtiqueta .calle-cte').removeAttr('readonly');
            }
            if (this.colonia != 'NoDefinido'){
                $('#tablaEtiqueta .colonia-cte').val(this.colonia);
            }else{
                $('#tablaEtiqueta .colonia-cte').removeAttr('readonly');
            }
            if (this.estado){
                $('#tablaEtiqueta .estado-cte').val( this.ciudad+','+this.estado + ',' +this.pais.toUpperCase() );
            }else{
                $('#tablaEtiqueta .estado-cte').removeAttr('readonly');
            }
        });
    }

function mostrarModalEti(ov,sitio,imgPath){
    $('#modalEtiquetas').openModal({
        ready : function(){
            if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
                $('#tablaEtiquetasPaqueteria').dataTable().fnClearTable();
                    $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
                    $('#tablaEtiquetasPaqueteria tbody').remove();
                    $('.paqyflet').val('');
                    $('.tipoentreseg').val('');
                    $('.comentario').val('');
            }
            if ( $('#previsEtiqueta').is(':checked') ){
                    $('#previsEtiqueta').removeProp('checked');
                    $('#tablaEtiquetasPaqueteria tbody').remove();
                    $('.paqyflet').val('');
                    $('.tipoentreseg').val('');
                    $('.comentario').val('');
                    $('#tablaEtiqueta').hide();
            }
        $('#modalEtiquetas #OVEti').val(ov);
        $('#sitioEtiquetas').html(sitiosList);
        $('#sitioEtiquetas').val(sitio);
        $('#sitioEtiquetas').material_select();
        $('#propositoEtiquetas').material_select('destroy');
        $('#propositoEtiquetas').attr('disabled', 'disabled');
        $('#propositoEtiquetas').html('<option value="">Selecciona...</option>');
        $('#propositoEtiquetas').material_select();
        $.ajax({url : "index/datosPropo",type: "POST",dataType : "JSON",data : {"ov":ov},
                beforeSend: function (xhr){
                        $("#loadheaderid").html('<img src="'+imgPath+'/cargando.gif" style="width: 1em;">');
                    },
                success : function(res){
                    $('#propositoEtiquetas').html(res.optPropo);
                    $('#propositoEtiquetas').removeAttr('disabled');
                    $('#propositoEtiquetas').material_select();
                    var div = $('#propositoEtiquetas').parent('.select-wrapper');
                    var ul = $(div).children('ul').children('li');
                    $(ul).each(function(index){
                        $(this).children('span').css('font-size','14px');
                        $(this).children('span').css('text-transform','uppercase');
                        if ($(this)[0].innerText != 'Selecciona...' && $(this)[0].innerText != 'Otro' ){
                            var proposito = $(this)[0].innerText;
                            var temp  = $('#propositoEtiquetas option')[index];
                            var recid = $(temp).attr('data-recid');
                            var dirMuestra = $.map(res.datosDirs,function(dirs){                                                            
                                if(dirs.PROPOSITO == proposito && dirs.RECID == recid){
                                    dirs.STREET   = dirs.STREET.replace(/[\r\n]/g,' | ');
                                    var direccion = '<strong><u>Calle: </u></strong>' + dirs.STREET + ' <strong><u>Colonia: </u></strong>' + dirs.COUNTY + ' <strong><u>Estado: </u></strong>' + dirs.STATE + ' <strong><u>Ciudad: </u></strong>' + dirs.CITY + ' <strong><u>CodigoPostal: </u></strong>' + dirs.ZIPCODE;
                                    return direccion;
                                }
                            });
                            var content = '<div class="row">'+
                                    '<div class="col l12 m12 s 12">'+
                                    '   <span style="color:#B4B7B7;font-size:12px;">' + dirMuestra[0] + '</span>'+
                                    '</div></div>';                                                        
                            $(this).append(content);
                        }
                    });
                    $("#loadheaderid").html('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#loading").html("Ocurrio un error favor de comunicarlo al dpto de sistemas.");
                }
            });
        }
    });
}
$('#genArchEti').on('click',function(){
        var valido = checarDatosCliente();
        if (valido){
            Materialize.toast('Todo valido puede continuar', 3000,'green');
            var tbody = $('<tbody></tbody>');
            $('#tablaEtiqueta tbody tr').each(function(){
                var td = $(this).find('td').clone();
                var tr = $('<tr></tr');
                $(td).each(function(){
                    if ( !$(this).children().is('input') ){
                        $(tr).append(td);
                    }else{
                        var dato = $(this).children('input').val();
                        $(this).find('input').remove();
                        $(this).html(dato);
                        $(tr).append(this);
                    }
                    $(tbody).append(tr);
                });
            });
            var tr2 = '<tr><td style="width:1%"> </td><td style="width:49%"> </td><td style="width:50%"> </td></tr>';
            var tr3 = '<tr><td style="width:1%"> </td><td style="width:49%">'+company+'</td><td style="width:50%"> </td></tr>';
            var tbodyTemp = $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            $(tbody).html(tbodyTemp);
            $('#tablaEtiquetasPaqueteria tbody').remove();
            $('#tablaEtiquetasPaqueteria').append(tbody);
            dataTableEti();
            tbodyTemp = '';
            $('.btn-etiquetas').trigger('click');
        }else{
            Materialize.toast('Existen datos incompletos, favor de ingresarlos', 3000,'red');
        }
    });
    
    $('#sitioEtiquetas,#propositoEtiquetas').on('change',function(){
        $('#previsEtiqueta').removeProp('checked');
        $('#tablaEtiqueta').hide();
    });
    
    $('#previsEtiqueta').on('change',function(){
        if ($(this).is(':checked')){            
            var sitio = $('#sitioEtiquetas :selected').val();
            var proposito = $('#propositoEtiquetas :selected').val();
            var ov = $('#modalEtiquetas #OVEti').val();
            var recid = $('#propositoEtiquetas :selected').attr('data-recid');
            generarEtiquetas(sitio,ov,proposito,recid);
            if (proposito == 'Otro'){
                $('.datoEtiquetaCliente').not('.tipoentreseg,.paqyflet,.comentario').removeProp('readonly');
                $('.datoEtiquetaCliente').val('');
            }else{
                $('.datoEtiquetaCliente').not('.tipoentreseg,.paqyflet,.comentario').prop('readonly','readonly');
            }            
        }else{
            $('#tablaEtiqueta').hide();
        }
    });

function descargarExcel(variable_conTabla){
    var tmpElemento = document.createElement('a');
    var data_type = 'data:application/vnd.ms-excel';
    var tabla_div = variable_conTabla;
    var tabla_html = tabla_div.replace(/ /g, '%20');
    tmpElemento.href = data_type + ', ' + tabla_html;
    tmpElemento.download = 'Etiqueta.xls';
    tmpElemento.click();
}
function dataTableEti(){
    if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
            $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
    }
    $('#etiquetasPaqueteria .dt-buttons').remove();
    $('#etiquetasPaqueteria .dataTables_filter').remove();
    $('#etiquetasPaqueteria .dataTables_info').remove();
    $('#etiquetasPaqueteria .dataTables_paginate').remove();
    var valido = checarDatosCliente();
    if (valido){
        Materialize.toast('Todo valido puede continuar', 3000,'green');
        var tbody = $('<tbody></tbody>');
        $('#tablaEtiqueta tbody tr').each(function(){
            var td = $(this).find('td').clone();
            var tr = $('<tr></tr>');
            $(td).each(function(){
                if ( !$(this).children().is('input') ){
                    $(tr).append(td);
                }else{
                    var dato = $(this).children('input').val();
                    $(this).find('input').remove();
                    $(this).html(dato);
                    $(tr).append(this);
                }
                $(tbody).append(tr);
            });
        });
        var tr2 = '<tr style="height:20px"><td style="width:49%;height:100%;font-size:12px"></td><td style="width:50%;height:100%;font-size:12px"></td></tr>';
        var tr3 = '<tr style="height:20px"><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black">'+company+'</td><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black"> </td></tr>';
        var tbodyTemp = $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        $(tbody).html(tbodyTemp);
        $('#tablaEtiquetasPaqueteria tbody').remove();
        $('#tablaEtiquetasPaqueteria').append(tbody);
        tbodyTemp = '';
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-right','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-right','5px');
            var style = '<style>'+
                    '   @media print { body { -webkit-print-color-adjust: exact; } }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '</style>';
        var html = $('#etiquetasPaqueteria').html();
        descargarExcel(html);
    }else{
        Materialize.toast('Existen datos incompletos, favor de ingresarlos', 3000,'red');
    }
}

$('#impArchEti').on('click',function(){
        
        if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
            $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
        }
        $('#etiquetasPaqueteria .dt-buttons').remove();
        $('#etiquetasPaqueteria .dataTables_filter').remove();
        $('#etiquetasPaqueteria .dataTables_info').remove();
        $('#etiquetasPaqueteria .dataTables_paginate').remove();
        var valido = checarDatosCliente();
        if (valido){
            Materialize.toast('Todo valido puede continuar', 3000,'green');
            var tbody = $('<tbody></tbody>');
            $('#tablaEtiqueta tbody tr').each(function(){
                var td = $(this).find('td').clone();
                var tr = $('<tr></tr');
                $(td).each(function(){
                    if ( !$(this).children().is('input') ){
                        $(tr).append(td);
                    }else{
                        var dato = $(this).children('input').val();
                        $(this).find('input').remove();
                        $(this).html(dato);
                        $(tr).append(this);
                    }
                    $(tbody).append(tr);
                });
            });
            var tr2 = '<tr style="height:20px"><td style="width:49%;height:100%;font-size:12px"></td><td style="width:50%;height:100%;font-size:12px">. </td></tr>';
            var tr3 = '<tr style="height:20px"><td style="width:49%;height:100%;font-size:12px;">'+company+'</td><td style="width:50%;height:100%;font-size:12px"> </td></tr>';
            var tbodyTemp = $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            tbodyTemp += tr2;
            tbodyTemp += tr3;
            tbodyTemp += $(tbody).html();
            $(tbody).html(tbodyTemp);
            $('#tablaEtiquetasPaqueteria tbody').remove();
            $('#tablaEtiquetasPaqueteria').append(tbody);
            tbodyTemp = '';
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('border','1px solid black');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('border','1px solid black');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('height','22px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('height','22px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('text-transform','uppercase');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('text-transform','uppercase');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('font-size','10px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('font-size','10px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-left','5px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-left','5px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-right','5px');
            $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-right','5px');
            var style = '<style>'+
                        '   @media print { body { -webkit-print-color-adjust: exact; } }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(1){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-left: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(2){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-right: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(1){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-left: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(2){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-right: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(1){'+
                        '       border-bottom: 1px solid black;'+
                        '       border-top: 1px solid black;'+
                        '       border-right: 1px solid black;'+
                        '       background-color:#01579B;'+
                        '       color: #FFFFFF'+
                        '   }'+
                        '</style>';
            var w = window.open('ETIQUETAS','Etiquetas');
            var html = $('#etiquetasPaqueteria').html();
            w.document.write(style+html);
            w.print();
            w.close();
        }else{
            Materialize.toast('Existen datos incompletos, favor de ingresarlos', 3000,'red');
        }
    });
    
    function mostrarModalPago(){
        formaPagoFactura="";
        $('#modalFactura').closeModal();
        $('#diarioPago').openModal({dismissible: false});
        var factura=$("#loadFacturaSt a").text();
        /*falta agregar el monto total*/
        $.ajax({url:'inicio/factura-Data',type: 'POST',data: {factura:factura},
            beforeSend: function (xhr) {
                $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
            },
            success: function (data, textStatus, jqXHR) {
                $('#process').html('');
                $('#diarioMontoFactura').val(Number(data[0][0]).toFixed(3));
                var html='';
                $.each(payModeList,function (i,v){
                    var sel='';
                    if(data[0][2]===v.PAYMMODE){sel='selected="selected"';}
                    html+='<option value="'+v.PAYMMODE+'" '+sel+'>'+v.name+'</option>';
                });
                formaPagoFactura=data[0][2];
                $('#diarioFPago').html(html);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                $('#diarioResult').html(jqXHR.status);
            }
            });
        $('#diarioFacturaFolio').val(factura);
        $.ajax({url:'inicio/cuenta-Contrapartida',type: 'POST',data: {},contentType: 'json',
            beforeSend: function (xhr) {
                $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
            },
            success: function (data, textStatus, jqXHR) {
                $('#process').html('');
                $('#contraPartida').html('');
                $.each(data,function (i,v){
                    var selected='';
                    if(mostrador===v[0]){
                        selected='selected="" ';
                    }
                    $("#contraPartida").append('<option value="'+v[0]+'" '+selected+'>'+v[0]+"</option>");
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                $('#diarioResult').html(jqXHR.status);
            }
        });
        if(mostrador.indexOf('CH')!==-1){
            $.ajax({
                url:'inicio/cuenta-Contrapartida-Linea',type: 'POST',contentType: 'json',
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                    var html='';
                    $('#diarioCuentaContra').html(html);
                    $.each(data,function (i,v){
                        html+='<option value="'+v[0]+'">'+v[0]+'</option>'; 
                    });
                    $('#diarioCuentaContra').html(html);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                    $('#diarioResult').html(jqXHR.status);
                }
            });
        }
        else{
            var html='';
            $('#diarioCuentaContra').html(html);
            $.each(diarioCuentasPago,function (i,v){
                if(v[0]==mostrador){
                    var sel="";
                    if(v[2]===formaPagoFactura){sel="selected";}
                    html+='<option value="'+v[1]+'" '+sel+' data-paymode="'+v[2]+'"=>'+v[1]+'</option>'; 
                }            
            });
            $('#diarioCuentaContra').html(html);             
        }      
    }
    function diarioFormaPago(fp){
        if(mostrador.indexOf('CH')!==-1){}
        else{
            var html='';
            $('#diarioCuentaContra').html(html);
            $.each(diarioCuentasPago,function (i,v){                
                if(v[0]===mostrador ){
                    var pg = v[2].split(",");
                    for(var a=0;a<=pg.length;a++){
                        if(pg[a]===fp){
                            var sel="";
                            html+='<option value="'+v[1]+'" '+sel+' data-paymode="'+v[2]+'">'+v[1]+'</option>';
                        }                         
                    }                   
                }            
            });
            $('#diarioCuentaContra').html(html);
        }        
    }
   function crearDiario(){
        try{
            var formData = $('#diarioPagoForm').serializeArray();
            $.ajax({
                type: 'POST',
                url:'inicio/diario',
                data: formData,
                dataType: 'json',
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                     $('#process').html('');
                     if(data.resultado.resultado==='ok'){
                        totalDiario=data.saldo;
                        if(totalDiario<=0){
                            totalDiario=0;
                            $('#diarioGuardarBtn').hide();
                        }                    
                        $("#folioDiario").val($("#folioDiario").val()+' '+data.resultado.respuesta);
                        swal("Guardado","Diario creado con exito con folio:"+data.resultado.respuesta,"info");
                        $('#diarioMontoFactura').val(totalDiario);
                     }
                     else{
                         $('#process').html(data.resultado.resultado);
                         $('#process').attr("style","color:red");
                     }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                    $('#diarioResult').html(jqXHR.status);
                }
            });
        }
        catch (e){
            console.log(e);
        }        
    }

function showPasa(){
    checkTableisDataTable();
    $("#pasaForm").html('<div class="col s4"><label>Nombre De cliente</label><input id="clienteNPasa"></div>\n\
                        <div class="col s4"><label>Orden de venta</label><input id="ovPasa"></div>\n\
                        <div class="col s4"><button class="btn" style="margin-top: 21px;" onclick="buscarPasa();"><i class="fa fa-search" ></i></button></div>\n\
                        <div class="col s6"><label style="color:red;"><b>***El total NO contiene cargos extras, en caso de ser cheque o tarjeta de crédito</b></label></div>');
    $('#ov').DataTable({
        "destroy": true, 
        "search":true,
        "paging": true, 
        "info": false,
        "order": [[ 0 , "desc" ]],
        data: [],
        columns: [ 
            {title: "Detalle"},
            {title: "Folio"},
            {title: "Fecha"},
            {title: "Código Cliente"},
            {title: "Cliente"},
            {title: "Sitio"},
            {title: "Almacén"},
            {title: "Modo de Entrega"},
            {title: "Estado"},
            {title: "Facturar"}
        ]
    });
}

function buscarPasa(){
    var nombre=$('#clienteNPasa').val();
    var ov=$('#ovPasa').val();
    if(nombre!=="" || ov !==""){
       $('#modalLoading').openModal();
       $("nav").find(".active").removeClass("active");
       $(this).parent().addClass("active");  
       $.ajax({
       url: "index/cliente-Pasa",type: "POST",dataType: 'json',data:{nombre:nombre,ov:ov},
       beforeSend: function (xhr) {
           $('#modalLoading').openModal();
            checkTableisDataTable();
       },
       success: function (data, textStatus, jqXHR) {
           $('#ov').DataTable({
               "destroy": true,
               "search":true,
               "paging": true, 
               "info": false,
               "order": [[ 0, "desc" ]],
               data: data,
               columns: [ 
                   {title: "OV"},
                   {title: "Fecha"},
                   {title: "Código Cliente"},
                   {title: "Cliente"},
                   {title: "Sitio"},
                   {title: "Almacén"},
                   {title: "Modo De Entrega"},
                   {title: "Vendedor"},
                   {title: "Estado"},
                   {title: "Remisión"},
                   {title: "Factura"},
                   {title: "Detalle"}, 
                   {title: "Total"}
               ]
           });
           $('#modalLoading').closeModal();
       },
       error: function (jqXHR, textStatus, errorThrown) {

       }
   }); 
    }
    else{
        swal('Alto!','Debe proporcionar un nombre de cliente o folio de OV','info');
    }
}
