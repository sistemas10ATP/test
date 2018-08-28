/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('#tabla').DataTable();
$(document).ajaxStart(function () {
    $("#loaderBootStrap").modal({keyboard: false,backdrop: 'static'});
}).ajaxStop(function () {
    $("#loaderBootStrap").modal('hide');
}).ajaxError(function (){
    $("#loaderBootStrap").modal('hide');
});

function filter(){ 
    var f1=$("#f1").val();
    var f2=$("#f2").val();
    $.ajax({
            url: "diarios/get-Info",type: "POST",cache: false,dataType: 'json',data: {fechai: f1,fechaf:f2},
            beforeSend: function (xhr) {
                $('#tabla').empty();
               $('#tabla').DataTable().destroy();
            },
            success: function (data) {
                var lista=[];
                $.each(data,function (i,v){
                    var fecha=v[3];
                    var editar='';
                    if(fecha.indexOf('1900') != -1){ 
                        fecha='no contabilizado';
                        editar='<a onclick="getDataForEdit(\''+v[0]+'\')"><i  style="color:green" class="fa fa-edit"></i>&nbsp;Editar</a> <a onclick="cerrarDiario(\''+v[0]+'\')"><i  style="color:green" class="fa fa-lock"></i>&nbsp;Cerrar diario</a>';
                    }
                    lista.push(['<a onclick="getDetalle(\''+v[0]+'\')">'+v[0]+'</a> &nbsp;&nbsp;'+editar,v[1],v[2],fecha]);                   
                });
                $('#tabla').DataTable( {
                    destroy:true,
                    "order": [[ 0, "desc" ]],
                    data: lista,
                    columns: [
                        {title: "Diario"},
                        {title: "Descripción"},
                        {title: "Nombre"},
                        {title: "Fecha"}
                    ]
                } );
            },
            error: function (x){
                console.log(x);
                $('#tabla').DataTable();
            }
        });        
}

function getDetalle(diario){
    var accountType=[];
    accountType[0]='Libro mayor';
    accountType[1]='Cliente';
    accountType[2]='Vendedor';
    accountType[3]='Proyecto';
    accountType[5]='Activos fijos';
    accountType[6]='Banco';
    accountType[12]='Activo fijo';
    accountType[13]='Avance titular';
    accountType[14]='Aplazamientos';
    accountType[15]='Dinero para gastos menores';
    $("#diarioFolio").html(diario);
    $.ajax({
        url: "diarios/get-Detail",type: "POST",cache: false,dataType: 'json',data: { diario : diario},
        beforeSend: function (xhr) {
            
        },
        success: function(data){
            var lista=[];
            $.each(data,function (i,v){
               lista.push([v[0],accountType[v[1]],v[2],v[3],v[4],Number(v[5]).toFixed(3),accountType[v[6]],v[7],v[8]]);
            });
            $('#detalleDiario').modal('show');
            $('#diarioDetalle').DataTable({
                destroy:true,
                order : [[ 3, "desc" ]],
                data: lista,
                columns: [
                    {title: "Fecha"},
                    {title: "Tipo De Cuenta"},
                    {title: "Cuenta"},
                    {title: "Factura"},
                    {title: "Descripción"},
                    {title: "Crédito"},
                    {title: "Tipo De Cuenta De Contrapartida"},
                    {title: "Cuenta De Contrapartida"}
                ]
            } );
        },
        error: function (x){
            console.log(x);
            $('#diarioDetalle').DataTable({destroy:true});
        }
    });
}

function getDataForEdit(diario){
    $("#diarioHidden").val(diario);
    var accountType=[];
    accountType[0]='Libro mayor';
    accountType[1]='Cliente';
    accountType[2]='Vendedor';
    accountType[3]='Proyecto';
    accountType[5]='Activos fijos';
    accountType[6]='Banco';
    accountType[12]='Activo fijo';
    accountType[13]='Avance titular';
    accountType[14]='Aplazamientos';
    accountType[15]='Dinero para gastos menores';
    $("#diarioFolioEditar").html(diario);
    $.ajax({
        url: "diarios/get-Detail",type: "POST",cache: false,dataType: 'json',data: { diario : diario},
        beforeSend: function (xhr) {
            
        },
        success: function(data){
            var lista='';
            $.each(data,function (i,v){
               lista+=  '<tr>'
                            //+'<td><input type="checkbox" name="elimina[]"></td>'
                            +'<td><input type="hidden" name="LineNum[]" value="'+v[9]+'"><input type="hidden" name="LedgerDimension[]" value="'+v[2]+'">'+v[2]+'</td>'
                            +'<td><input type="hidden" name="MarkedInvoice[]" value="'+v[3]+'">'+v[3]+'</td>'
                            +'<td><input class="form-control" type="text" name="Txt[]" value="'+v[4]+'"></td>'
                            +'<td><input class="form-control" type="number" name="AmountCurCredit[]" value="'+Number(v[5]).toFixed(3)+'"></td>'
                            +'<td><input type="hidden" name="PaymMode[]" value="'+v[8]+'">'+v[8]+'</td>'
                            +'<td><input type="hidden" name="OffsetLedgerDimension[]" value="'+v[7]+'">'+v[7]+'</td>'
                        +'</tr>'; 
            });
            $('#editarDiario').modal('show');
            $('#diarioDetalleEditar>tbody').html(lista);
        },
        error: function (x){
            console.log(x);
            $('#diarioDetalle').DataTable({destroy:true});
        }
    });
}

function guardarDiario(){
    var form= $("#diarioForm").serialize();
    $.ajax({
        url: "diarios/save-Diario",type: 'POST',dataType: 'json',data:form,
        beforeSend: function (xhr){
            
        },
        success: function (data, textStatus, jqXHR){
            if(data.resultado==='ok'){
                swal("Diario guardado!", data.respuesta, "success");
            }
            else{
                swal("No se guardo!", data.respuesta, "info");
            }
            filter();
        },
        error: function (jqXHR, textStatus, errorThrown){
            catchError(jqXHR,textStatus);
        }
    });
}
function nuevoDiarioModalOpen(){
    $.ajax({url:'inicio/cuenta-Contrapartida',type: 'POST',contentType: 'json',
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
            diarioFormaPago("01");             
        } 
    var html='';
    $.each(payModeList,function (i,v){
       html+='<option value="'+v.PAYMMODE+'">'+v.name+'</option>';
    });
    $('#diarioFPago').html(html);
}
function mapeoClte(valor, arreglo, request) {
    return $.map(arreglo, function (clte) {
        var posNombre = clte.nombre.indexOf(valor.toUpperCase());
        var posArticu = clte.value.indexOf(valor.toUpperCase());
        if (request.term.indexOf('*') < 0) {
            if ((posNombre >= 0) || (posArticu >= 0)) {
                return clte;
            }
        } else {
            if ((posNombre >= 0)) {
                return clte;
            }
        }
    });
}
function mapArray(clients) {
    var clientList = [];
    $.map(clients, function (item) {
        var info = {label: "No hay Resultados", value: " "};
        info["label"] = $.trim(item[0]);
        info["value"] = $.trim(item[0]);
        info["nombre"] = $.trim(Number(item[1]));
        clientList.push(info);
    });
    return clientList;
}
function autocompleteSetLabel(list, id) {
    $(id).autocomplete({autoFocus: true, minLength: 9,
        source: function (request, response) {
            var buscar = request.term.split(' ');
            var itemClte = [];
            $(buscar).each(function (index, valor) {
                if (index == 0) {
                    itemClte = mapeoClte(valor, list, request);
                } else {
                    itemClte = mapeoClte(valor, itemClte, request);
                }
            });
            response(itemClte);
        },
        response: function (event, ui) {
            if (ui.content.length === 0) {
                ui.content.push({label: "No hay Resultados", value: "1"});
            }
        },
        select: function (event, ui) {
            event.preventDefault();
            $(id).val(ui.item.label);
            $("#diarioMontoFactura").val(ui.item.nombre);
        }
    });
}
function crearDiario(){
        try{
            var formData = $('#diarioPagoForm').serializeArray();
            $.ajax({
                type: 'POST',url:'inicio/diario',data: formData, dataType: 'json',
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                     $('#process').html('');
                     if(data.resultado.resultado==='ok'){
                         $('#folioDiario').val(data.resultado.respuesta);
                         $('#diarioGuardarBtn').hide();
                         swal("Guardado","Diario creado con exito con folio:"+data.resultado.respuesta,"info");
                     }
                     else{
                         var str=data.resultado.resultado;                        
                         swal("Alto!",str,"error");
                         $('#diarioMontoFactura').val(data.saldo);
                     }
                     filter();
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
function cerrarDiario(diario){
    $.ajax({
        url: "diarios/cerrar-Diario",data:{diario:diario},type: 'POST',
        beforeSend: function (xhr) {
            
        },
        success: function (data, textStatus, jqXHR) {
             if(data.resultado==='ok'){
                swal("Diario Registrado!", data.respuesta, "success");
            }
            else{
                swal("No se guardo!", data.respuesta, "info");
            }
            filter();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            
        }
    });
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