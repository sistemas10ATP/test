/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ajaxStart(function () {
    $("#loaderBootStrap").modal({keyboard: false,backdrop: 'static'});
}).ajaxStop(function () {
    $("#loaderBootStrap").modal('hide');
});

function getJsonFromUrl(url, dataSet) {
    return $.ajax({
        type: "POST", url: url, data: dataSet,
        dataType: 'JSON',
        async: false,
        success: function (data) {
            return data;
        },
        error: function (jqXHR, textStatus, errorThrown) {
            swal("Error!", jqXHR.responseText, "error");
        }
    });
}

function getDetalle(ov){
    try{
        var detalle=getJsonFromUrl('consultaguia/get-Detail-From-Ov',{ov:ov}).responseJSON;
        var tableCont='';
        $('#folioOv').html(ov);
        $('#detalleOV > tbody').html(tableCont);
        $.each(detalle,function (i,v){
            tableCont+='<tr><td>'+Number(v[0])+'</td><td>'+v[1]+'</td><td>'+v[2]+'</td><td>'+Number(v[3])+'</td><td>'+v[4]+'</td></tr>';
        });
        $('#detalleOV > tbody').html(tableCont);
        $("#detalleDoc").modal();
    }
    catch (e){
        $("#loaderBootStrap").modal('hide');
        swal("Error!", e, "error");
    }
}

function getList(){
    try{
        var ovEstatus={1:'Orden Abierta',2:'Entregado',3:'Facturado',4:'Cancelado'};
        var serialize = $('#inputsFilterTbl').serialize();
        var columnasDef = [{title: "Orden de venta"}, {title: "Cliente"}, {title: "Modo de entrega"}, {title: "Secretario de ventas"}, {title: "Paqueteria"}, {title: "Fecha"}, {title: "Guía"}, {title: "Estatus"}];
        if(serialize!=='cliente='){
            $.ajax({
                type: "POST", url: 'consultaguia/get-list', data: serialize,
                dataType: 'JSON',
                success: function (data) {
                    var dataL=[];
                    $.each(data,function (i,v){
                        dataL.push(
                            [
                                '<a onclick="getDetalle(\''+v[0]+'\')"><span class="fa fa-plus-circle sr-icons "></span> '+v[0]+'</a>',
                                v[1],
                                v[2],
                                v[3],
                                v[4],
                                v[5],
                                v[6],
                                ovEstatus[v[7]]
                            ]
                        );
                    });
                    $("#reporte-tbl").DataTable().destroy();
                    $("#reporte-tbl>thead").remove();
                    $("#reporte-tbl").DataTable({
                        destroy: true,
                        order: [[3, "desc"]],
                        data: dataL,
                        columns: columnasDef,
                        columnDefs: [
                            {targets: [0], "width": "10%"},
                            {targets: '_all', "searchable": true}
                        ]
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#loaderBootStrap").modal('hide');
                    swal("Error!", jqXHR.responseText, "error");
                }
            });
        }
        else{
            swal("¡Alto!","Favor de seleccionar algun cliente", "info");
        }
    }
    catch(e){
        $("#loaderBootStrap").modal('hide');
        swal("Error!", e, "error");
    }    
}