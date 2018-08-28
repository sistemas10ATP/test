/* 
 * creado 02/05/2018
 * Francisco javier delgado 
 * 
 */

/**
 * Actualiza la lista de traspasos pendientes
 * @returns null
 */
var contNuevo = "<div id='nuevo' class='tab-pane fade'>" +
                    "<div class='panel-body'>" +
                        "<table class='table table-striped' id='tblNuevo' style='width: 100%'>" +
                            "<thead>" +
                                "<tr>" +
                                    "<th>Folio</th>" +
                                    "<th>Fecha</th>" +
                                    "<th>Artículo</th>" +
                                    "<th>Descripción</th>" +
                                    "<th>Estatus <input class='form-control' style='max-width: 100px;' ></th>" +                    
                                "</tr>" +
                            "</thead>" +
                            "<tbody></tbody>" +
                        "</table>" +
                    "</div>" +
                "</div>";
var contRecibido = "<div id='recibido' class='tab-pane fade'>" +
                        "<div class='panel-body'>" +
                            "<table class='table table-striped' id='tblRecibido' style='width: 100%'>" +
                                "<thead>" +
                                    "<tr>" +
                                        "<th>Folio</th>" +
                                        "<th>Fecha</th>" +
                                        "<th>Artículo</th>" +
                                        "<th>Descripción</th>" +
                                        "<th>Estatus <input class='form-control' style='max-width: 100px;' ></th>" +                    
                                    "</tr>" +
                                "</thead>" +
                                "<tbody></tbody>" +
                            "</table>" +
                        "</div>" +
                    "</div>";
var contEnviado = "<div id='enviado' class='tab-pane fade'>" +
                        "<div class='panel-body'>" +
                            "<table class='table table-striped' id='tblEnviado' style='width: 100%'>" +
                                "<thead>" +
                                    "<tr>" +
                                        "<th>Folio</th>" +
                                        "<th>Fecha</th>" +
                                        "<th>Artículo</th>" +
                                        "<th>Descripción</th>" +
                                        "<th>Estatus <input class='form-control' style='max-width: 100px;' ></th>" +                    
                                    "</tr>" +
                                "</thead>" +
                                "<tbody></tbody>" +
                            "</table>" +
                        "</div>" +
                    "</div>";
var contRegistrado = "<div id='registrado' class='tab-pane fade'>" +
                        "<div class='panel-body'>" +
                            "<table class='table table-striped' id='tblRegistrado' style='width: 100%'>" +
                                 "<thead>" +
                                    "<tr>" +
                                        "<th>Folio</th>" +
                                        "<th>Fecha</th>" +
                                        "<th>Artículo</th>" +
                                        "<th>Descripción</th>" +
                                        "<th>Estatus <input class='form-control' style='max-width: 100px;' ></th>" +                    
                                    "</tr>" +
                                "</thead>" +
                                "<tbody></tbody>" +
                            "</table>" +
                        "</div>" +
                    "</div>";
var contCancelado = "<div id='cancelado' class='tab-pane fade'>" +
                        "<div class='panel-body'>" +
                            "<table class='table table-striped' id='tblCancelado' style='width: 100%'>" +
                                 "<thead>" +
                                    "<tr>" +
                                        "<th>Folio</th>" +
                                        "<th>Fecha</th>" +
                                        "<th>Artículo</th>" +
                                        "<th>Descripción</th>" +
                                        "<th>Estatus <input class='form-control' style='max-width: 100px;' ></th>" +                    
                                    "</tr>" +
                                "</thead>" +
                                "<tbody></tbody>" +
                            "</table>" +
                        "</div>" +
                    "</div>";
function getFilterData(id){
    $("#allContent").empty();
    try{
        var tipo=0;
        var nombrePer='consulta';
        if(havePermision(10)){
            nombrePer='CEDIS';
            tipo=1;
        }
        else if(havePermision(11)){
            nombrePer='almacén';
            tipo=2;
        }
        else{
            nombrePer='consulta';
            tipo=0;
        }
        $("#tituloLbl").html(nombrePer);
        $.ajax({
                url: "solicitudescedis/tabla-List",type: "POST",cache: false,dataType: 'json',data: {'id': id,tipo:tipo}, 
                beforeSend: function (xhr) {
                    $('#tblReport').empty();
                    $('#tblReport').DataTable().destroy();
                },
                success: function (data) {
                    var btn='';
                    var estatus=['',
                        '<button class="btn btn-primary  btn-md dropdown-toggle" type="button" data-toggle="dropdown" style="width: 10em;"> Nuevo ',
                        '<button class="btn btn-warning  btn-md dropdown-toggle" type="button" data-toggle="dropdown" style="width: 10em;"> Recibido ',
                        '<button class="btn btn-info  btn-md dropdown-toggle" type="button" data-toggle="dropdown" style="width: 10em;"> Enviado ',
                        '<button class="btn btn-success  btn-md dropdown-toggle" type="button" data-toggle="dropdown" style="width: 10em;"> Registrado ',
                        '<button class="btn btn-danger  btn-md dropdown-toggle" type="button" data-toggle="dropdown" style="width: 10em;"> Cancelado '];
                    var estatusN=['Nuevo','Recibir','Enviar','Registrar'];
                    var estatusN2=['',' style="color: #048bff;">Detalles',' style="color: #048bff;">Detalles',' style="color: #048bff;">Detalles',' style="color: #048bff;">Detalles',' style="color: #048bff;">Detalles'];
                    var dataArrNuevo=[];
                    var dataArrRecibido=[];
                    var dataArrEnviado=[];
                    var dataArrRegistrado=[];
                    var dataArrCancelado=[];
                    $.each(data,function (i,v){
                        var action='<li><a onclick="editarTraspaso('+v[0]+','+v[5]+');"><i class="fa fa-edit"></i> '+estatusN[v[5]]+'</a></li>';
                        if(!estatusN[v[5]]){action='';}
                        if(v[5]==="2"){action+='<li><a onclick="editarTraspaso('+v[0]+',5);"><i class="fa fa-trash"></i> Cancelar</a></li>';}
                        var  l='<a class="pull-right" target="_blank" href="solicitudescedis/traspaso-Imprimir?id='+v[0]+'"'+estatusN2[v[5]]+'</a>';
                        if(id==='' && tipo!==0){
                            l='<div class="btn-group pull-right">'
                                    +estatus[v[5]]+'<i class="fa fa-chevron-down pull-right"></i></button>'
                                    +'<ul class="dropdown-menu info">'
                                    +action
                                    +'<li><a target="_blank" href="solicitudescedis/traspaso-Imprimir?id='+v[0]+'"><i class="fa fa-print"></i> Imprimir</a></li>'
                                    +'</ul></div>';
                        }
                        var st=v[5];
                        if(tipo===1 && v[5]==="3"){
                            l='<a class="pull-right" target="_blank" href="solicitudescedis/traspaso-Imprimir?id='+v[0]+'"'+estatusN2[v[5]]+'</a>';
                        }
                        
                        if(v[5] == 1){
                            dataArrNuevo.push([v[0],v[4],v[6],v[2]+'  '+cliente[v[2]] ,v[3],v[1],v[8],l]);
                        } else if(v[5] == 2){
                            dataArrRecibido.push([v[0],v[4],v[6],v[2]+'  '+cliente[v[2]] ,v[3],v[1],v[8],l]);
                        } else if(v[5] == 3){
                            dataArrEnviado.push([v[0],v[4],v[6],v[2]+'  '+cliente[v[2]] ,v[3],v[1],v[8],l]);
                        } else if(v[5] == 4){
                            dataArrRegistrado.push([v[0],v[4],v[6],v[2]+'  '+cliente[v[2]] ,v[3],v[1],v[8],l]);
                        } else if(v[5] == 5){
                            dataArrCancelado.push([v[0],v[4],v[6],v[2]+'  '+cliente[v[2]] ,v[3],v[1],v[8],l]);
                        }
                        
                    });
                    
                    if(tipo == 2){
                        $("#allContent").append(contEnviado + contRegistrado + contCancelado);  
                        
                        $("#liNavEnviado").addClass('active');
                        $("#enviado").removeClass('fade');
                        $("#enviado").addClass('fade-in active');
                        
                        $("#liNavEnviado").show();
                        $("#liNavRegistrado").show();
                        $("#liNavCancelado").show();
                        
                       
                    } else{
                        
                        $("#allContent").append(contNuevo + contRecibido + contEnviado + contRegistrado + contCancelado);
                        
                        if($("#liNavRecibido").hasClass('active')){
                            
                            $("#liNavNuevo").removeClass('active');
                            $("#nuevo").addClass('fade');
                            $("#nuevo").removeClass('fade-in active'); 
                            
                            $("#recibido").removeClass('fade');
                            $("#recibido").addClass('fade-in active'); 
                            
                        } else {
                            
                            $("#liNavNuevo").addClass('active');
                            $("#nuevo").removeClass('fade');
                            $("#nuevo").addClass('fade-in active'); 
                            
                        }
                        
                        $("#navStatus").children().show();
                    }
                    
                    $("#tblNuevo").DataTable().destroy();
                    $("#tblNuevo>thead").remove();
                    $('#tblNuevo').DataTable({
                        destroy:true,
                        "order": [[ 1, "asc" ]],
                        data: dataArrNuevo,
                        columns: [
                            {title: "Folio"},{title: "Articulo"},{title: "Cantidad"},
                            {title: "Cliente"},{title: "Solicita"},
                            {title: "Creado"},{title: "Modificado"},{title: "Estatus"}
                        ]
                    });
                    
                    $("#tblRecibido").DataTable().destroy();
                    $("#tblRecibido>thead").remove();
                    $('#tblRecibido').DataTable({
                        destroy:true,
                        "order": [[ 1, "asc" ]],
                        data: dataArrRecibido,
                        columns: [
                            {title: "Folio"},{title: "Articulo"},{title: "Cantidad"},
                            {title: "Cliente"},{title: "Solicita"},
                            {title: "Creado"},{title: "Modificado"},{title: "Estatus"}
                        ]
                    });
                    
                    $("#tblEnviado").DataTable().destroy();
                    $("#tblEnviado>thead").remove();
                    $('#tblEnviado').DataTable({
                        destroy:true,
                        "order": [[ 1, "asc" ]],
                        data: dataArrEnviado,
                        columns: [
                            {title: "Folio"},{title: "Articulo"},{title: "Cantidad"},
                            {title: "Cliente"},{title: "Solicita"},
                            {title: "Creado"},{title: "Modificado"},{title: "Estatus"}
                        ]
                    });
                    
                    $("#tblRegistrado").DataTable().destroy();
                    $("#tblRegistrado>thead").remove();
                    $('#tblRegistrado').DataTable({
                        destroy:true,
                        "order": [[ 1, "asc" ]],
                        data: dataArrRegistrado,
                        columns: [
                            {title: "Folio"},{title: "Articulo"},{title: "Cantidad"},
                            {title: "Cliente"},{title: "Solicita"},
                            {title: "Creado"},{title: "Modificado"},{title: "Estatus"}
                        ]
                    });   
                    
                    $("#tblCancelado").DataTable().destroy();
                    $("#tblCancelado>thead").remove();
                    $('#tblCancelado').DataTable({
                        destroy:true,
                        "order": [[ 1, "asc" ]],
                        data: dataArrCancelado,
                        columns: [
                            {title: "Folio"},{title: "Articulo"},{title: "Cantidad"},
                            {title: "Cliente"},{title: "Solicita"},
                            {title: "Creado"},{title: "Modificado"},{title: "Estatus"}
                        ]
                    });
                    
                },
                error: function (x){
                    $('#tblNuevo').DataTable();
                    $('#tblRecibido').DataTable();
                    $('#tblEnviado').DataTable();
                    $('#tblRegistrado').DataTable();
                    $('#tblCancelado').DataTable();
                }
            });
    }
    catch (err){
        console.log(err);
    }
}
function editarTraspaso(id,st){
    try{
        if(st===5){
            $.ajax({
                url:'solicitudescedis/solicitud',type: 'POST',data:{id:id},
                success: function (data, textStatus, jqXHR) {
                    swal({
                        title: 'Agregar comentario',
                        html:'<input id="swal-input1" name="comentarios" class="swal2-input" placeholder="COMENTARIOS">',
                        showCancelButton: true,
                        confirmButtonText:'Aceptar',
                        cancelButtonText:'Cerrar'
                    }).then(function (result){
                        var arr={
                            titulo:"Estatus de solicitud de traspaso",
                            mensaje:'<h1>Cedis ha <b>Cancelado la solicitud</b> de traspaso folio #'+data.cabecera[0].folio+'</h1>\n\
                                    <br><b>Folio: </b>#'+data.cabecera[0].folio+' \n\
                                    <br><b>Cliente:</b> '+data.cabecera[0].cliente+' '+cliente[data.cabecera[0].cliente]+'\n\
                                    <br><b>Articulo:</b> '+data.cabecera[0].articulo+' enviado a CHIHCONS\n\
                                    <br><b>Cantidad de venta:</b></label>'+data.detalle[0].cantidad+'\n\
                                    <br><b>Comentarios:</b></label><br>'+$('#swal-input2').val()+'\n\
                                    <br><label><b>Puede consultar en el siguiente enlace: </b></label><a href="http://intra/inax/public/solicitudescedis?id='+id+'">Consultar</a>',
                            asunto:"Cancelacion de traspaso con folio #"+data.cabecera[0].folio+" de cliente: "+data.cabecera[0].cliente,
                            formato:"traspasosSolicitud.html",
                            type:5,
                            cedis:usuario,
                            comentarios:$('#swal-input1').val(),
                            folio:data.cabecera[0].folio,
                            cliente:data.cabecera[0].cliente,
                            solicita:data.detalle[0].vendedor,
                            cantidad:data.cabecera[0].cantidad,
                            articulo:data.cabecera[0].articulo
                        };
                        enviacorreo(arr); 
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);   
                }
            });
        }
        else{
           $.ajax({
                url:'solicitudescedis/solicitud',type: 'POST',data:{id:id},
                success: function (data, textStatus, jqXHR) {
                    var estatusN=['Nuevo','Recibido','Enviado','Registrado','Cancelado'];
                    if(Number(data.cabecera[0].estatus)==1){
                        var arr={
                                titulo:"Estatus de solicitud de traspaso",
                                mensaje:'<h1>Cedis ha recibido la solicitud de traspaso folio #'+data.cabecera[0].folio+'</h1><br><label>puede consultar en el siguiente enlace: </label><a href="http://intra/inax/public/solicitudescedis?id='+id+'">Consultar</a>',
                                asunto:"Actualizacion de traspaso con folio #"+data.cabecera[0].folio+" de cliente: "+data.cabecera[0].cliente,
                                formato:"traspasosSolicitud.html",
                                type:1,
                                folio:data.cabecera[0].folio,
                                solicita:data.detalle[0].vendedor
                            };
                        enviacorreo(arr);                    
                    }
                    if(Number(data.cabecera[0].estatus)==2){
                        swal({
                            title: 'Agregar comentario',
                            html:'<input id="swal-input1" name="comentarios" class="swal2-input" placeholder="COMENTARIOS">'+
                                 '<input id="swal-input2" class="swal2-input" placeholder="DIARIO DE TRASPASO">'+
                                 '<select id="swal-input3" required="" class="swal2-input" name="motivo">'+
                                    '<option value="FÍSICO">FÍSICO</option>'+
                                    '<option value="VIRTUAL">VIRTUAL</option>'+
                                  '</select>',
                            showCancelButton: true,
                            confirmButtonText:'Enviar',
                            cancelButtonText:'Cancelar'
                        }).then(function (result){
                            var arr={
                                titulo:"Estatus de solicitud de traspaso",
                                mensaje:'<h1>Cedis ha enviado la solicitud de traspaso folio #'+data.cabecera[0].folio+'</h1>\n\
                                        <br><b>Folio: </b>#'+data.cabecera[0].folio+' \n\
                                        <br><b>Cliente:</b> '+data.cabecera[0].cliente+' '+cliente[data.cabecera[0].cliente]+'\n\
                                        <br><b>Articulo:</b> '+data.cabecera[0].articulo+' enviado a CHIHCONS\n\
                                        <br><b>Cantidad de venta:</b></label>'+data.detalle[0].cantidad+'\n\
                                        <br><b>Comentarios:</b></label><br>'+$('#swal-input2').val()+'\n\
                                        <br><label><b>Puede consultar en el siguiente enlace: </b></label><a href="http://intra/inax/public/solicitudescedis?id='+id+'">Consultar</a>',
                                asunto:"Actualizacion de traspaso con folio #"+data.cabecera[0].folio+" de cliente: "+data.cabecera[0].cliente,
                                formato:"traspasosSolicitud.html",
                                type:2,
                                cedis:usuario,
                                comentarios:$('#swal-input1').val(),
                                diario:$('#swal-input2').val(),
                                fisico:$('#swal-input3').val(),
                                folio:data.cabecera[0].folio,
                                cliente:data.cabecera[0].cliente,
                                solicita:data.detalle[0].vendedor,
                                cantidad:data.cabecera[0].cantidad,
                                articulo:data.cabecera[0].articulo
                            };
                            enviacorreo(arr); 
                        });                    
                    }
                    if(Number(data.cabecera[0].estatus)==3){
                        var arr={
                                titulo:"Estatus de solicitud de traspaso",
                                mensaje:'<h1>Almacén Chihuahua ha registrado la solicitud de traspaso folio #'+data.cabecera[0].folio+'</h1><br>\n\
                                         <br><label>Folio: #'+data.cabecera[0].folio+' </label>\n\
                                        <br><label>Cliente: '+data.cabecera[0].cliente+' '+cliente[data.cabecera[0].cliente]+'</label>\n\
                                        <br><label>Articulo: '+data.cabecera[0].articulo+' enviado a CHIHCONS</label>\n\
                                        <br><b>Cantidad de venta:</b></label>'+data.detalle[0].cantidad+'\n\
                                        <br><label>Comentarios:</label><br><b>'+data.detalle[0].comentarioscedis+'</b>\n\
                                        <label>puede consultar en el siguiente enlace: </label><a href="http://intra/inax/public/solicitudescedis?id='+id+'">Consultar</a>',
                                asunto:"Actualizacion de traspaso con folio #"+data.cabecera[0].folio+" de cliente: "+data.cabecera[0].cliente,
                                formato:"traspasosSolicitud.html",
                                type:3,
                                cedis:usuario,
                                folio:data.cabecera[0].folio,
                                cliente:data.cabecera[0].cliente,
                                solicita:data.detalle[0].vendedor,
                                cantidad:data.cabecera[0].cantidad,
                                articulo:data.cabecera[0].articulo
                            };
                        enviacorreo(arr);  
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);   
                }
            });   
        }
              
    }
    catch(e){
        $('#editar').modal('hide');
        console.log(e);
    }
    
}

function enviacorreo(data){
    $.ajax({
        url:"index/email",type:"post", 
        data:data,
        success: function (data, textStatus, jqXHR) {
            if(data=="enviado"){
                swal({
                    type: 'success',
                    html: 'Correo enviado favor de verificar en la bandeja d entrada de correo',
                    showCancelButton: false,
                    confirmButtonText:'OK'
                }).then(function (result) {
                    getFilterData('');
                });
            }
            else{
                swal({
                    type: 'error',
                    html: 'Correo no enviado favor de intentar de nuevo <br>'+data
                });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            swal({
                type: 'error',
                html: textStatus+'    '+errorThrown
            });
        }
    });  
}

function solicitaTraspaso(item,desc,existencia,id,almacen){
    if(existencia>=$('#'+id).val()){
        swal({
            title: 'Seleccione un motivo',
            html:
                    '<select id="swal-input1" required="" class="browser-default" name="motivo">'+
                        '<option value="NO HAY EXISTENCIA">NO HAY EXISTENCIA</option>'+
                        '<option value="MATERIAL EN PEDACERIA">MATERIAL EN PEDACERIA</option>'+
                        '<option value="MATERIAL CADUCO">MATERIAL CADUCO</option>'+
                        '<option value="EN EL SISTEMA SI HAY, FISICAMENTE NO ESTA">EN EL SISTEMA SI HAY, FISICAMENTE NO ESTA</option>'+
                    '</select>'
                    +'<input id="swal-input3" readonly="" name="solicitado" value="'+$('#'+id).val()+'" class="swal2-input" placeholder="CANTIDAD SOLICITADA">'
                    +'<input id="swal-input2" name="comentarios" class="swal2-input" placeholder="COMENTARIOS">',
            showCancelButton: true,
            confirmButtonText:'Enviar solicitud',
            cancelButtonText:'Cancelar'
        }).then(function (result) {
            var f = new Date();
            var msj='<table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                '<tbody>'+
                 '  <tr style="background-color: rgb(222, 222, 222);font-weight: bold;">'+
                 '      <td style="text-align: center;">'+
                 '         <span>SOLICITUD DE TRASPASO DE MATERIALES DE CEDIS A ALMACEN CHIHUAHUA</span>'+
                 '      </td>'+
                 '  </tr>'+
                   '<tr>'+
                    '   <td>'+
                     '      <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                      '         <tbody><tr>'+
                       '                <td style="text-align: center;color: #333;font-weight: bold;">'+
                        '                   FECHA DE SOLICITUD:'+
                         '              </td>'+
                          '             <td>'+f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear()+'</td>'+
                           '        </tr>'+
                            '       <tr>'+
                             '          <td style="text-align: center;color: #333;font-weight: bold;text-align: center;">CÓDIGO DE CLIENTE:</td>'+
                              '         <td>'+$('#claveclte').val()+'</td>'+
                               '    </tr>'+
                                '   <tr>'+
                                 '      <td style="color: #333;font-weight: bold;text-align: center;">'+
                                  '         NOMBRE DEL CLIENTE:'+
                                   '    </td>'+
                                    '   <td>'+$('#cliente').val()+'</td>'+
                                   '</tr>'+
                                  ' <tr>'+
                                   '    <td style="text-align: center;color: #333;font-weight: bold;">'+
                                    '       MOTIVO DE LA SOLICITUD:'+
                                     '  </td>'+
                                      ' <td style="background-color: #ff9800;">'+$('#swal-input1').val()+'</td>'+
                                   '</tr>'+
                               '</tbody>'+
                           '</table>'+
                       '</td>'+
                   '</tr>'+
                   '<tr>'+
                    '     <td>'+
                    '       <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5>'+
                    '         <tr style="background-color:#cccccc;font-weight: bold;"><td>CLAVE ARTÍCULO</td><td>DESCRIPCIÓN DEL ARTICULO</td><td>CANTIDAD</td><td>ALMACEN</td></tr>'+
                    '         <tr><td>'+item+'</td><td>'+decodeURI(desc)+'</td><td style="text-align: center;">'+$('#swal-input3').val()+'</td><td>'+almacen+'</td></tr>'+
                    '       </table>'+
                    '     </td>'+
                   '</tr>'+
                   '<tr>'+
                      '  <td>'+
                    '       <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                    '           <tbody>'+
                     '              <tr>'+
                      '                 <td style="text-align: center;color: #333;font-weight: bold;text-align: center;">COMENTARIOS</td>'+
                       '                <td>'+$('#swal-input2').val()+'</td>'+
                        '           </tr>'+
                         '          <tr>'+
                          '             <td style="text-align: center;color: #333;font-weight: bold;text-align: center;">VENDEDOR</td>'+
                           '            <td>'+usuario+' - '+userName+'</td>'+
                            '       </tr>'+
                             '  </tbody>'+
                           '</table>'+
                       '</td>'+
                   '</tr>'+
               '</tbody>'+
           '</table>';
           $.ajax({
               url:"index/email",type:"post", 
               data:{
                   titulo:"Solicitud de traspaso",
                   mensaje:msj,
                   asunto:"Solicitud de traspaso "+item,
                   formato:"traspasosSolicitud.html"
                },
                success: function (data, textStatus, jqXHR) {
                    if(data=="enviado"){
                        swal({
                            type: 'success',
                            html: 'Correo enviado favor de verificar en la bandeja d entrada de correo'
                        });
                    }
                    else{
                        swal({
                            type: 'error',
                            html: 'Correo no enviado favor de intentar de nuevo <br>'+data
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    swal({
                        type: 'error',
                        html: textStatus+'    '+errorThrown
                    });
                }
            });        
        });
    }
    else{
        swal({
            type: 'info',
            html: 'Cantidad a solicitar <b>es mayor</b> que la existencia actual'
        });
    }
}
