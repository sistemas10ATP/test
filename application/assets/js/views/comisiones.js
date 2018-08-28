/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };

/**
 * 
 * @param {type} id selector
 * @description hace toggle pasandole un id
 * @returns {noting}
 */
function showBlock(id) {
    $(id).toggle();

}

function newPDF(form,url){
    var s=$('#'+form).serialize();
    window.open(url+'?'+s, '_blank');
}
/**
 * 
 * @param {type} form
 * @param {type} url
 * @returns {undefined}
 */
function putData(form, url) {
    $(form).validate({
        rules: {
            mes: {required: true},
            articulo: {required: true, minlength: 2},
            comisionista: {required: true},
            cliente: {required: true},
            comision: {required: true}
        },
        messages: {
            mes: "Debe asignar un mes.",
            articulo: "Debe seleccionar articulo.",
            comisionista: "Debe seleccionar comisionista.",
            cliente: "Debe seleccionar cliente",
            comision: "El campo Mensaje es obligatorio."
        },
        submitHandler: function (form) {
            var dataString = $(form).serialize();
            $.ajax({
                type: "POST",
                url: url,
                data: dataString,
                beforeSend: function (xhr) {
                    $("#gdr-comisionistaAsociarBtn").removeClass('fa-save');
                    $("#gdr-comisionistaAsociarBtn").addClass('fa-pulse fa-spin');
                },
                success: function (data) {
                    $("#gdr-comisionistaAsociarBtn").removeClass('fa-pulse fa-spin');
                    $("#gdr-comisionistaAsociarBtn").addClass('fa-check-square-o text-success');
                    refreshtableComisionistas();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#gdr-comisionistaAsociarBtn").removeClass('fa-pulse fa-spin');
                    $("#gdr-comisionistaAsociarBtn").addClass('fa-close text-danger');
                }
            });
        }
    });
}
/**
 * 
 * @param {type} form
 * @param {type} url
 */
function putDataNewCom(form, url) {
    $(form).validate({
        rules: {
            nombre: {required: true, minlength: 2},
            direccion: {required: true, minlength: 2},
            telefono: {required: true, minlength: 2, maxlength: 15},
            estado: {required: true},
            ciudad: {required: true, minlength: 2}
        },
        messages: {
            nombre: "Debe introducir su nombre.",
            direccion: "Debe introducir alguna direccion.",
            telefono: "El número de teléfono introducido no es correcto.",
            estado: "Debe introducir solo números.",
            ciudad: "El campo Mensaje es obligatorio."
        },
        submitHandler: function (form) {
            var dataString = $(form).serialize();
            $.ajax({
                type: "POST",
                url: url,
                data: dataString,
                beforeSend: function (xhr) {
                    $("#btn-add-comisionista").removeClass('fa-save');
                    $("#btn-add-comisionista").addClass('fa-pulse fa-spin');
                },
                success: function (data) {
                    $("#btn-add-comisionista").removeClass('fa-pulse fa-spin');
                    $("#btn-add-comisionista").addClass('fa-check-square-o text-success');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#btn-add-comisionista").removeClass('fa-pulse fa-spin');
                    $("#btn-add-comisionista").addClass('fa-close text-danger');
                }
            });
        }
    });
}
/**
 * 
 * @param {type} clients
 * @returns {Array|mapClient.clientList}
 */
function mapClient(clients) {
    var clientList = [];
    $.map(clients, function (item) {
        var info = {label: "No hay Resultados", value: " "};
        info["label"] = $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.ClaveCliente);
        info["nombre"] = $.trim(item.Nombre);
        clientList.push(info);
    });
    return clientList;
}
/**
 * 
 * @param {type} articulos
 * @returns {Array|mapArticulo.clientList}
 */
function mapArticulo(articulos) {
    var articuloList = [];
    $.map(articulos, function (item) {
        var info = {label: "No hay Resultados", value: " "};
        info["label"] = $.trim(item.value) + " - " + $.trim(item.label);
        info["value"] = $.trim(item.value);
        info["nombre"] = $.trim(item.label);
        articuloList.push(info);
    });
    return articuloList;
}
/**
 * 
 * @param {type} clients
 * @returns {Array|mapArray.clientList}
 */
function mapArray(clients) {
    var clientList = [];
    $.map(clients, function (item) {
        var info = {label: "No hay Resultados", value: " "};
        info["label"] = $.trim(item.nombre);
        info["value"] = $.trim(item.idComisionista);
        info["nombre"] = $.trim(item.nombre);
        clientList.push(info);
    });
    return clientList;
}
/**
 * 
 * @param {type} valor
 * @param {type} arreglo
 * @param {type} request
 * @returns {Array}
 */
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
/**
 * 
 * @param {type} list
 * @param {type} id
 * @returns {undefined}
 */
function autocompleteSetValue(list, id) {
    $(id).autocomplete({autoFocus: true, minLength: 3,
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
        }
    });
}
/**
 * 
 * @param {type} list
 * @param {type} id
 * @returns {undefined}
 */
function autocompleteSetLabel(list, id) {
    $(id).autocomplete({autoFocus: true, minLength: 3,
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
            $(id + '2').val(ui.item.value);
            $(id).val(ui.item.label);
            console.log(ui.item.label);
        }
    });
}
/**
 * 
 * @param {type} numero
 * @returns {String}
 */
function getNameMonth(numero) {
    var meses = ["N/D", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    return meses[numero];
}
/**
 * 
 * @returns {undefined}
 */
function refreshtableComisionistas() {
    var dataTableComisionistas = [];
    var json = getJsonFromUrl("comisiones/get-Comisionista-Cliente", {idRow: ""}).responseJSON;
    $.each(json, function (i, v) {
        dataTableComisionistas.push([getNameMonth(Number(v.mes)), v.item, v.comisionista, v.cliente + ' ' + v.clientenom, v.comision, '<i id="icon' + v.idcomComisionCliente + '" onclick="deleteComisionistaCliente(' + v.idcomComisionCliente + ');" style="color:red;" class="fa fa-trash"></i>']);
    });
    $("#tblComisionistasAsignados").DataTable({
        destroy: true,
        order: [[2, "desc"]],
        data: dataTableComisionistas,
        columns: [
            {title: "Mes"},
            {title: "Articulo"},
            {title: "Comisionista"},
            {title: "Cliente"},
            {title: "Comisión"},
            {title: ""}
        ],
        columnDefs: [
            {targets: [0, 1, 4], "width": "10%"},
            {targets: '_all', "searchable": true}
        ]
    });
}
/**
 * 
 * @param {type} idRow
 * @returns {undefined}
 */
function deleteComisionistaCliente(idRow) {
    if (idRow && confirm('¿Esta seguo de eliminar la asignación del comisionista?')) {

        $.ajax({
            type: "POST",
            url: 'comisiones/delete-Comisionista-Cliente',
            data: {idRow: idRow},
            async: false,
            beforeSend: function (xhr) {
                $("#icon" + idRow).removeClass('fa-trash');
                $("#icon" + idRow).addClass('fa-pulse fa-spin');
            },
            success: function (data) {
                if (data) {
                    refreshtableComisionistas();
                } else {
                    alert("Ocurrio un error al eliminar la fila :'(");
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                //$("#btn-add-comisionista").removeClass('fa-pulse fa-spin');
                //$("#btn-add-comisionista").addClass('fa-close text-danger');
            }
        });
    }
}
/**
 * 
 * @param {type} url
 * @param {type} dataSet
 * @returns {jqXHR}
 */
function getJsonFromUrl(url, dataSet) {
    return $.ajax({
        type: "POST", url: url, data: dataSet,
        dataType: 'JSON',
        async: false,
        beforeSend: function (xhr) {

        },
        success: function (data) {
            return data;
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert('Error al obtener los datos');
        }
    });
}
/**
 * 
 * @returns {undefined}
 */
function refreshTableReport() {
    var dataTableComisionistas = [];
    var dta = $("#comisionistaFilterTbl").serialize();
    var id="";
    $("#botonDetalle").remove();
    $.ajax({
        type: "POST", url: "comisiones/get-Comisionista-Cliente-Reporte", data: dta,
        dataType: 'JSON',
        beforeSend: function (xhr) {

        },
        success: function (data) {
            var total = 0;
            var totalCom = 0;
            var flag = $("#comisionistaListFilter").val();
            var width = [3, 4, 5];
            var columnasDef = [{title: "Comisionista"}, {title: "# Cliente"}, {title: "Articulo"}, {title: "Venta MXN"}, {title: "Comisión"}, {title: "Total"}];
            if (flag !== '') {
                columnasDef = [{title: "# Cliente"}, {title: "Articulo"}, {title: "Venta MXN"}, {title: "Comisión"}, {title: "Total"}];
                width = [3, 4];
                
            }
            $.each(data, function (i, v) {
                total = total + Number(v.TOTAL);
                totalCom = totalCom + Number(v.TOTALPAGO);
                if (flag !== '') {
                    dataTableComisionistas.push([v.SALESNAME, v.ITEM, '$' + Number(v.TOTAL).formatMoney(2, '.', ','), Number(v.COMISION).toFixed(2), '$' + Number(v.TOTALPAGO).formatMoney(2, '.', ',')]);
                } else {
                    dataTableComisionistas.push(['<a onclick="showDataComisionist(' + v.idComisionista + ')">' + v.NOMBRE + '</a>', v.SALESNAME, v.ITEM, '$' + Number(v.TOTAL).formatMoney(2, '.', ','), Number(v.COMISION).toFixed(2), '$' + Number(v.TOTALPAGO).formatMoney(2, '.', ',')]);
                }
                id=v.idComisionista;
            });            
            $('#total').val(Number(total).formatMoney(2, '.', ','));
            $('#totalCom').val(Number(totalCom).formatMoney(2, '.', ','));
            if (flag !== '') {
                $("#reporte-tbl").DataTable().destroy();
                $("#reporte-tbl>thead").remove();
                $("#reporte-tbl").prepend("<thead><th># Cliente</th><th>Articulo</th><th>Venta MXN</th><th>Comisión</th><th>Total</th>");
                $('#comisionistaFilterTbl').append('<br><button id="botonDetalle" class="btn btn-default" type="button" onclick="showDataComisionist('+id+')"><i class="fa fa-codepen"></i> Detalle Comisionista</button>');
            } else {
                $("#reporte-tbl").DataTable().destroy();
                $("#reporte-tbl>thead").remove();
                $("#reporte-tbl").prepend("<thead><th>Comisionista</th><th># Cliente</th><th>Articulo</th><th>Venta MXN</th><th>Comisión</th><th>Total</th>");
            }
            $("#reporte-tbl").DataTable({
                destroy: true,
                order: [[3, "desc"]],
                data: dataTableComisionistas,
                columns: columnasDef,
                columnDefs: [
                    {targets: width, "width": "10%"},
                    {targets: '_all', "searchable": true}
                ]
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            
        }
    });
}

function showDataComisionist(id) {
    $("#comisionistaTelefono").val('');
    $("#comisionistaDireccion").html('');
    var res = getJsonFromUrl('comisiones/get-Comisionista-Data', {id: id}).responseJSON;
    $("#comisionistaTelefono").val(res[0].telefono);
    $("#comisionistaDireccion").append(res[0].direccion + ' Estado: ' + res[0].estado + ', ciudad:' + res[0].ciudad);
    $('#comisionistaModal').modal();
}

$(document).ajaxStart(function () {
    $("#loaderBootStrap").modal({keyboard: false,backdrop: 'static'});
}).ajaxStop(function () {
    $("#loaderBootStrap").modal('hide');
});
