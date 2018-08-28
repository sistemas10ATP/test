/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* global edicion, docID */
$(document).on('click','#CancelarExistencias',function(){
        $('#F2pressed').val('0');
        var seleccionado = $('#LineaArticulo').val();
        var flag=$("#edicionExis").val();
        if(flag!='edit'){
            $('#descripcion'+seleccionado).val('');
            $('#cantidad'+seleccionado).val('');
            $('#unidad'+seleccionado).val('');
            $('#item'+seleccionado).val('');
        }
        $('#item'+seleccionado).focus();
        $('#modal1').closeModal();
    });
    $(document).on('click','.checkFam, .checkFamDetVta',function(e){
        var itemFam = {};
        var row = '';
        var cantFam = $('.checkFam:checked').length;
        var cantFamDet = $('.checkFamDetVta:checked').length;
        if (cantFam > 0){
                $('#agregarItemsFamilia').html('Agregar');
        }else{
            $('#agregarItemsFamilia').html('Cerrar');
        }
        if (cantFamDet > 0){
            $('#btnAgregarDetVta').html('Agregar');
        }else{
            $('#btnAgregarDetVta').html('Cerrar');
        }
        if ( $(this).is(':checked') && $(this).hasClass('checkFam') ){
            row = $(this).parents('tr');
            itemFam = {
                item : $(row).find('td').eq(0).html(),
		nombre : $(row).find('td').eq(1).html(),
		sitio : $("#sitioLineas").val(),
		almacen : $(row).find('td').eq(2).html(),
		cliente : $("#claveclte").val(),
		qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index()
            };
            familiaArr.push(itemFam);
        }
        else if ( $(this).is(':checked') && $(this).hasClass('checkFamDetVta') ){
            row = $(this).closest('tr');
            itemFam = {
                item : $(row).find('td').eq(2).html(),
                nombre : $(row).find('td').eq(4).html(),
                sitio : $("#sitioLineas").val(),
                almacen : $(row).find('td').eq(3).html(),
                cliente : $("#claveclte").val(),
                qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index()
            };
            familiaArr.push(itemFam);
        }
        else if ( !$(this).is(':checked') && $(this).hasClass('checkFamDetVta') ){
            row = $(this).closest('tr');
            itemFam = {					
                item : $(row).find('td').eq(2).html(),
                nombre : $(row).find('td').eq(4).html(),
                sitio : $("#sitioLineas").val(),
                almacen : $(row).find('td').eq(3).html(),
                cliente : $("#claveclte").val(),
                qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index(),
            };
            var elementPos = familiaArr.map(function(x) {return x.item; }).indexOf(itemFam.item);
            familiaArr.splice(elementPos,1);
        }
        else if ( !$(this).is(':checked') && $(this).hasClass('checkFam') ){
            row = $(this).parents('tr');
            itemFam = {					
                item : $(row).find('td').eq(0).html(),
                nombre : $(row).find('td').eq(1).html(),
                sitio : $("#sitioLineas").val(),
                almacen : $(row).find('td').eq(2).html(),
                cliente : $("#claveclte").val(),
                qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index(),
            };
            var elementPos = familiaArr.map(function(x) {return x.item; }).indexOf(itemFam.item);
            familiaArr.splice(elementPos,1);
        }
    });
    $("#familia").on('keydown',function(ev){
        if (ev.which == 13){
            $("#BuscarFamilia").click();
        }
    });
    $(document).on('click',"#ConvertirCot-Ov",function(e){
        verifica();
        if(localStorage.getItem('bandera')=='si' && !$( "#ConvertirCot-Ov" ).hasClass( "disabled" ) ){
            var cot = $("#DocumentoConfirmado").val();
            $('#ATP_Cot').val(cot);
            var cta = $("#ctaBanco").val();
            $('#preloaderConv').show();
            $.ajax({url: "inicio/convertirCotOV",type: "POST",dataType: "json",
                data: {"cliente":$('#claveclte').val(),"cotizacion": cot,'cuenta': cta,'origenVenta': $("#origenV").val(),'encabezadoov': $("#cabeceraOriginal").val(),'metodoPago' : $("#pagolineas").val()},
            beforeSend: function (xhr) {
                Materialize.toast('Procesando Orden de venta', 3000);
                 $("#ConvertirCot-Ov").addClass("disabled");
            },    
            success: function (data){
                if(data.status != 'Fallo'){                    
                        $("#ConvertirCot-Ov").hide();
                        $("#ConvertirCot-Ov").removeClass("disabled");
                        Materialize.toast('Cotización convertida a Orden de Venta!', 3000);
                        $("#OrdenVentaRem").val(data.msg);                    
                        $('#DocumentId2').val(data.msg);
                        $('#DocumentIdResumen').html(data.msg);
                        $('#tipoDocument').html('Orden de Venta:');
                        $('#resumenDivTitle').html('Resumen de la Orden de Venta: '+$('#DocumentId2').val());
                        $("#cuentaCompletoOV").html($('#DocumentId2').val()+':Cliente '+$("#claveclte").val()+' - '+$("#cliente").val());
                        $('#ATP_Cot').val($('#DocumentId2').val());
                        $("#DocumentType").val('ORDVTA');
                        $('#ConvertirCot-Ov').removeClass('disable_a_href');
                        if(havePermision(14)){
                            $("#Facturar").show();  
                        }
                        else{
                            $("#GenerarREM").show();
                        }
                        refreshLines();
                }else{
                    Materialize.toast('Algo salio mal: '+data.msg, 3000);
                }
                $('#preloaderConv').hide();
            },
            error: function (jqHKR,exception){
                Materialize.toast('WebService Error!.'+catchError(jqHKR,exception), 3000);
                $("#ConvertirCot-Ov").removeClass("disabled");
            }
            });
        }
    });
$("#entregalineas").on('change',function(){
    $("#LineasEntrega").val($(this).val());	
});
$(document).on('click','.BatchAvailable',function(){
    var $tr = $(this).closest('tr');
    var myRow = $tr.index() + 1;
    var item = $('#item'+myRow).val();
    var sitio = $('#sitio'+myRow).val();
    if (item === "" || sitio==="") {
        $("#dropdownLote"+myRow).removeClass("active");
        $("#dropdownLote"+myRow).css('display', "none");
        Materialize.toast('Artículo o Sitio vacios, favor de ingresar los datos para visualizar los lotes!', 3000);	
    } else{
        DisponibleLote(item,sitio,myRow);
    }			
});
                $(document).on('click','.lote-dropdown-content', function(event){
		    //The event won't be propagated to the document NODE and 
		    // therefore events delegated to document won't be fired
		    event.stopPropagation();
		});

		$(document).on('click','.comentarios-dropdown-content', function(event){
		    //The event won't be propagated to the document NODE and 
		    // therefore events delegated to document won't be fired
		    event.stopPropagation();
		});

		$(document).on('click','.punitario-dropdown-content', function(event){
		    //The event won't be propagated to the document NODE and 
		    // therefore events delegated to document won't be fired
		    event.stopPropagation();
		});
                
                $(document).on('click','.dropdown-button',function(event){
			//click del text area de las lineas
			var elem = document.getElementById($(this).attr('id'));
			pos = elem.getBoundingClientRect();
			str = $(this).parent().find('.coment').attr('style');
			if (elem.id != 'cliente'){
				var opc = $.map(str.split(';'), function(item,index){
					var strtemp = '';
					if ( item.indexOf('left') >= 0 ){
						var temp = item.split(':');
						temp[1] = pos.left+'px !important';
						var  t2 = temp.join();
						t2 = t2.replace(',',':');
						strtemp += t2;
					}else if( item.indexOf('top') >= 0 ){
						var tempTop = item.split(':');
						tempTop[1] = pos.top - 19;
						var t3 = tempTop.join();
						t3 = t3.replace(',',':');
						strtemp += t3;
					}else{
						strtemp += item;
					}
					return strtemp;
				});
				opc = opc.join();
				opc = opc.replace(/,/g,';');
				$(this).parent().find('.coment').attr('style',opc);
			}
		});
                $(document).keyup(function(e) {
			if (e.which === 17){
				CtrlPressed = false;           		
        		$("#item"+ $("#NumFilas").val()).focus();		
        	}
		});
                ////////////////////////drop direcciones/////////////////////////////////////////////////////////
		$(document).on('click','.dirsClte',function(){
			activates  = $(this).attr('data-activates');
			offsetTop  = $(this)[0].getBoundingClientRect().bottom;
			offsetLeft = $(this)[0].getBoundingClientRect().left;
			offsetDiv  = $('#articulosDiv').offset().top;
			elemTop    = offsetTop - offsetDiv;
			if ( !$('#'+activates).is(':visible') ){
				$('#'+activates).css('position','fixed');
				$('#'+activates).css('opacity','1');
				$('#'+activates).css('top',offsetTop);
				$('#'+activates).css('left',offsetLeft);
				$('#'+activates).show();
			}
                        else{
				$('#'+activates).hide();
				$('#'+activates).css('position','');
			}
		});
                 ///////////////////////////drop cambio precio//////////////////////////////////////////////////////
		$(document).on('click','.preciovta',function(){
			activates = $(this).attr('data-activates');
			offsetTop = $(this).parents('td')[0].getBoundingClientRect().bottom;
			offsetDiv = $('#articulosDiv').offset().top;
			elemTop   = offsetTop - offsetDiv;
			$('#'+activates).css('position','fixed');
			$('#'+activates).css('top',elemTop);
			$('#'+activates).show();
		});
                $(document).on('keyup','.punitario-dropdown-content',function(ev){
			if (ev.which == 27){
				$(this).hide();
				$(this).css('position','');
			}
		});
                $(document).on('click','.closeCambioPrecio',function(){
			$(this).parents('div.punitario-dropdown-content').hide();
			$(this).parents('div.punitario-dropdown-content').css('position','');
		});
                ////////////////////////drop comentarios/////////////////////////////////////////////////////////
		$(document).on('click','.tacoment',function(){
			activates = $(this).attr('data-activates');
			offsetTop = $(this).parents('td')[0].getBoundingClientRect().bottom;
			offsetDiv = $('#articulosDiv').offset().top;
			elemTop   = offsetTop - offsetDiv;
			$('#'+activates).css('position','fixed');
			$('#'+activates).css('top',elemTop);
			$('#'+activates).show();
		});
                $(document).on('keyup','.comentarios-dropdown-content',function(ev){
			if (ev.which == 27){
				$(this).hide();
				$(this).css('position','');
			}
		});

		$(document).on('click','.closeComentLinea',function(){
			$(this).parents('div.comentarios-dropdown-content').hide();
			$(this).parents('div.comentarios-dropdown-content').css('position','');
		});
                
		///////////////////////end drop comentario/////////////////////////////////////////////////////////
		////////////////////////drop lote/////////////////////////////////////////////////////////
		$(document).on('click','.BatchAvailable',function(){
			activates = $(this).attr('data-activates');
			offsetTop = $(this).parents('td')[0].getBoundingClientRect().bottom;
			offsetDiv = $('#articulosDiv').offset().top;
			elemTop   = offsetTop - offsetDiv;
			$('#'+activates).css('position','fixed');
			$('#'+activates).css('top',elemTop);
			$('#'+activates).show();
		});

		$(document).on('keyup','.lote-dropdown-content',function(ev){
			if (ev.which == 27){
				$(this).hide();
				$(this).css('position','');
			}
		});

		$(document).on('click','.closeLoteLinea',function(){
			$(this).parents('div.lote-dropdown-content').hide();
			$(this).parents('div.lote-dropdown-content').css('position','');
		});
		///////////////////////end drop lote/////////////////////////////////////////////////////////
		
		$(window).resize(function(){
			var scrsize = $(window).width() - 250;
			scrsize = scrsize+'px';
			$('#rowResumenPartidas').css('max-width',scrsize);
		});
		/////iniciar el panel navegar//////////////////
                $('#breadInicio').show(function(){
                    $(this).attr('style','display:inline;color:white');
                });
                $('#breadResumen').hide();
                $('#editarDocument').hide();
                if ($('#divInicioSesion').hasClass('s5')){
                    $('#divInicioSesion').removeClass('s5');
                    $('#divInicioSesion').addClass('s9');
                }else if ($('#divInicioSesion').hasClass('s9')){
                   $('#divInicioSesion').removeClass('s9');
                   $('#divInicioSesion').addClass('s5');
                }
                $('.modal-trigger').leanModal();
                $('#articulosDiv').hide();
                $('#resumenTest').hide();
                $('#claveclte').focus();
                function dobleItem(item){
                    var band = false;
			var rows = $('#articulos tbody').find('tr').filter(':visible').filter(':not(.emptyRow)');
			$(rows).each(function(){
				var item1 = $(this).find('td').find('.item').val();
				if (item1 === item){
					band = true;
				}
			});
			return band;
		}
                //Evita que al dar Enter se envíe el formulario	
                $(document).keydown(function(event){
		    if(event.keyCode == 13) {
		      event.preventDefault();
		      return false;
		    }
		});
                //Accesos directos a agregar y quitar linea
		var CtrlPressed = false;
		$(document).keydown(function(e) {
			if (e.which === 17){
				CtrlPressed = true; 
                            }
        	if (CtrlPressed && e.which === 45){
        		agregarLinea(autocomp_opt,bandAgregarFam);
        	}            	
        	else if (CtrlPressed && e.which === 46){                       	
        		quitarLinea();
        	}            	
		});
                //Manda a llamar la función de agregar linea
		$("#AgregarLinea").on('click',function(){
			agregarLinea(autocomp_opt,bandAgregarFam);
		});

		//manda llamar la funcion de quitar linea
		$("#QuitarLinea").on('click',function(){			
			quitarLinea();
		});
function buscarFamilia(){                    
    $("#ExistenciaFamilia > tbody").html("");
        var len = $("#familia").val().length;
        if (len>=4) {
            var sitio = $("#sitioLineas").val();
            var familia = $("#familia").val();
            if (sitio != ''){
                $.ajax({url: "inicio",type: "get",dataType: "json",data: { "familia": familia, "sitio": sitio, "token":"existenciasFamilia" },
                    beforeSend: function (xhr) {
                        Materialize.toast('Buscando familias!', 2000);
                    },
                    success: function (data){
                        $("#ExistenciaFamilia > tbody").html("");
                        if (!data["noresult"]){
                            for (var i=0;i<data.length;i++){
                                var disponible = parseFloat(data[i].Existencia);
                                $("#ExistenciaFamilia > tbody").append('<tr><td>' + data[i].Articulo + '</td><td>' + data[i].DescripcionArticulo +'</td><td>' + data[i].Almacen +'</td><td>' + disponible.toFixed(2) +'</td><td><input type="checkbox" data-item="'+data[i].Articulo+'" data-unidad="'+data[i].Unidad+'" class="col l1 s1 m1 checkFam" id="itemCheckFam'+i+'"/><label for="itemCheckFam'+i+'"></label></td></tr>');
                            }					        
                        }
                        else{
                            $("#ExistenciaFamilia > tbody").append('<tr><td colspan="5" class="center-align">'+ data["noresult"] +'</td></tr>');
                        }
                    }
                });
            }else{
                Materialize.toast('Favor de seleccionar sitio para cliente!.',3000);
            }
        }else{
            Materialize.toast('La familia debe ser de minimo 4 caracteres!.',3000);
        }
}
//Realiza el cambio de moneda para su posterior actualización de la cabecera y si existen lineas actualiza los precios
$("#monedalineas").on('change',function(){
    //Se obtiene el numero de artículos capturados
    var rows = $("#articulos > tbody >tr").length;
    for (var i = 1; i < rows + 1; i++) {
        var ev = $.Event({'type':'input','enviarExistencias':'1'});
        $("#cantidad"+i).trigger(ev);
    }
});
//Realiza el cambio de forma de pago para su posterior actualización de la cabecera y si existen lineas actualiza los precios segun el cargo
$("#pagolineas").on('change',function(e){
    var rows = $("#articulos > tbody >tr").length;
    if (rows === 0) {
        $("#PorcentCargo").val($(this).val());
        $("#FormaPagoLineas").val($("#pagolineas option:selected").text());
    } else{
        for (var i = 1; i < rows + 1; i++) {
            if ( $('#item'+i).val() !== '' ){
                $("#PorcentCargo").val($(this).val());
                var ev = $.Event({'type':'input','enviarExistencias':'1'});
                $("#cantidad"+i).trigger(ev);
                $("#FormaPagoLineas").val($("#pagolineas option:selected").text());
            }else{
                Materialize.toast('Artículo o Sitio vacios, favor de ingresar los datos para aplicar el cargo!', 3000);						
            }
        }	
    }
});
function selectAll(){
    var t=$('#articulos >tbody >tr').length;
    for(var i=0;t>=i;i++){
        if(i>0){ 
            $("#LblNumLinea"+i).click();
        }
    }
}
function imprimirRemi(html,Remi){
    var styleprint = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
    +'<style>.tr {display: flex;justify-content: space-between;font-size: 12px;font-weight: bold;}'+
    '.clave {width: 15%;}'+
    '.description { width: 63%;}'+
    '.pedido {width: 7%;}'+
    '.unidad {width: 7%;}'+
    '.entregado {width: 8%;}'+
    '.tr > div {text-align: center;}'+
    '*{font-family: arial !important;}'+
    '.description {text-align: left !important;}'+
    '.xxx strong {text-decoration: underline;}'+
    '.xxx span {font-weight: normal !important;}'+
    '.description p:last-child {font-weight: normal;}'+
    '.articulo {margin: 0 0 5px 0;}'+
    '.description > p {margin: 0 0 5px 0;}'+
    '.xxx > span {font-size: 10px;}'+
    'svg#remiTemp {}svg#remiTemp {max-width: 10% !important;transform: translateY(-28%);}</style>';
    html = styleprint+html;
    $("#inputHtml").val(html);
    $('#testForm input#remision').val(Remi);
    $("#testForm").submit();
    $('#preloaderRemi').hide();
}

function rightclick() {
    var e = window.event;       
    var cantThinkOfAName = document.getElementById("rightclicked");
    cantThinkOfAName.style.display = "block";
    cantThinkOfAName.style.left = mouseX(e) + "px";
    cantThinkOfAName.style.top = mouseY(e) + "px";
}
function mouseX(evt) {
    if (evt.pageX) {
        return evt.pageX;
    } else if (evt.clientX) {
        return evt.clientX + (document.documentElement.scrollLeft ?
            document.documentElement.scrollLeft :
            document.body.scrollLeft);
    } else {
        return null;
    }
}

function mouseY(evt) {
    if (evt.pageY) {
        return evt.pageY;
    } else if (evt.clientY) {
        return evt.clientY + (document.documentElement.scrollTop ?
        document.documentElement.scrollTop :
        document.body.scrollTop);
    } else {
        return null;
    }
}
function mapeoClte(valor,arreglo,request){
    return $.map(arreglo,function(clte){
        posNombre = clte.nombre.indexOf(valor.toUpperCase());
	posArticu = clte.value.indexOf(valor.toUpperCase());
	if (request.term.indexOf('*') < 0){
            if ( (posNombre >= 0) || (posArticu >= 0) ){
                return clte;
            }
	}
        else{
            if ( (posNombre >= 0) ){						
		return clte;
            }
	}
    });
}

function existe(arreglo,valor){ 
    var band = false;
    $(arreglo).each(function(){
        if (this[0].LINENUM == valor){
            band = true;
	}
    });
    return band;
}
$(document).keypress(function (evt){
    var evento=evt.keyCode;
    switch(evento){
        case 17: $('#SubmitHeader').click();
            break;
    }
});
    /////////////////////////etiquetas//////////////////////////////////////////////////////////////////////
    function mostrarModalEti(){
        var sitio = $("#sitioclte").val();
        var ov    = $('#DocumentId2').val();
        $('#modalEtiquetas').openModal({
            ready : function(){
                if ( $.fn.DataTable.isDataTable( '#tabla ' ) ){
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
                var htmlsitio="";
                var selected="";
                for(var k in sitios2){
                    if(sitios2[k].SITEID==sitio){selected="selected";}else{selected="";}
                    htmlsitio+='<option value="'+sitios2[k].SITEID+'" '+selected+'>'+sitios2[k].NAME+'</option>';
                }
                $('#sitioEtiquetas').html(htmlsitio);
                $('#sitioEtiquetas').val(sitio);
                $('#propositoEtiquetas').material_select('destroy');
                $('#propositoEtiquetas').attr('disabled', 'disabled');
                $('#propositoEtiquetas').html('<option value="">Selecciona...</option>');
                $('#propositoEtiquetas').material_select();
                $.ajax({ url : "index/datosPropo", type: "POST",dataType : "JSON", data : {"ov":ov},
                    beforeSend: function (xhr) {
                         $("#loadheaderid").html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
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
                                dirMuestra = $.map(res.datosDirs,function(dirs){
                                    if(dirs.PROPOSITO == proposito && dirs.RECID == recid){
                                        dirs.STREET   = dirs.STREET.replace(/[\r\n]/g,' | ');
                                        var direccion = '<strong><u>Calle: </u></strong>' + dirs.STREET + ' <strong><u>Colonia: </u></strong>' + dirs.COUNTY + ' <strong><u>Estado: </u></strong>' + dirs.STATE + ' <strong><u>Ciudad: </u></strong>' + dirs.CITY + ' <strong><u>CodigoPostal: </u></strong>' + dirs.ZIPCODE;
                                    }
                                    return direccion;
                                });
                                var content = '<div class="row">'+
                                                '<div class="col l12 m12 s 12">'+
                                                '   <span style="color:#B4B7B7;font-size:12px;">' + dirMuestra[0] + '</span>'+
                                                '</div>'+
                                              '</div>';                                                        
                                $(this).append(content);                                                        
                            }
                        });
                         $("#loadheaderid").html('');
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        
                    }
                });
            }
        });
    }
    
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

 function generarEtiquetas(sitio,ov,proposito,recid){
        $.ajax({ url : "index/datosEtiqueta",type : "POST",dataType : "JSON",
            data : {"sitio":sitio,"ov":ov},
            beforeSend: function (xhr) {
                $("#loading").html('<img src="'+loading+'/cargando.gif" style="width: 1em;">');
            },
            success : function(res){
                var datosSucu = res.datosSucu;
                var datosClte = res.datosCte;
                var datosDirs = res.datosDirs;
                var datosMonto = res.datosMonto;
                direccionCliente(proposito,datosDirs,recid);
                $(datosSucu).each(function(){
                    $('#tablaEtiqueta .calle').val(this.calle);
                    $('#tablaEtiqueta .colonia').val(this.colonia);
                    $('#tablaEtiqueta .estado').val(this.estado);
                    $('#tablaEtiqueta .telefono').val(this.telefono);
                });                
                var clteCompleto = ov + ' - ' + datosClte[0].CUSTACCOUNT + ' - ' + datosClte[0].NOMBRECLIENTE;
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
                $('#tablaEtiqueta .userEti').html('Usuario: '+usuario+' ('+datosClte[0].NOMBREVENDEDOR+')');
                $('#tablaEtiqueta .fechaEti').html('Fecha de creacion: '+fecha.toLocaleDateString()+' - '+fecha.toLocaleTimeString());
                $('#tablaEtiqueta .rfc-cte').removeAttr('readonly');
                $('#tablaEtiqueta .tel-cte').removeAttr('readonly');
                $('#tablaEtiqueta').show();
            }
        });
    }
    
function direccionCliente(proposito,direcciones,recid){
        var dirMuestra = $.map(direcciones,function(dirs){
                        if(dirs.PROPOSITO == proposito && dirs.RECID == recid){
                            var dirTemp   = dirs.ADDRESS.split("\n");
                            var dirCalle  = dirs.STREET;
                            var dirColon  = dirs.COUNTY;
                            var dirEstad  = dirs.STATE;
                            var dirCiudad = dirs.CITY;
                            var dirCodPo  = dirs.ZIPCODE;
                            var dirPais   = dirs.PAIS;
                            var direccion = {'calle':dirCalle,'colonia':dirColon,'estado':dirEstad, 'ciudad':dirCiudad,'cp':dirCodPo,'pais':dirPais};
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
function checarNegados(){
    var cantNegados = negados.itemGroup.items.length;
    if (eval(cantNegados) > 0){
        agregarNegados();
        $('#modalNegados').openModal({dismissible : false});
        return true;
    }else{
        return false;
    }
}
function goBack(act,ant){
    $(act).hide();
    $(ant).show(); 
}
function miScroll() { $('#rightclicked').hide();}

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
    document.body.addEventListener('click', function () {
        document.getElementById("rightclicked").style.display = "none";
    });
    document.body.addEventListener('contextmenu', function () {
        document.getElementById("rightclicked").style.display = "none";
    });
    document.getElementById("OV-Cliente").addEventListener('contextmenu', function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        rightclick();
        return false;
    }, false);
    $('.contextMenuA').tooltip();
    $('.contextMenuA').on("mouseenter", function () {
        $(this).css('background-color', '#337AB7'); 
        $(this).css('color', 'white');        
        $('.tooltip').addClass('col-md-12');
    });
    $('.contextMenuA').on("mouseleave", function () {
        $(this).css('background-color', 'white');
        $(this).css('padding','3px 15px');
        $(this).css('color', '#0275D8');
    });
    ///////////funcion para esconder el context en el scroll del body///////////
    $('body').attr('onscroll','miScroll()');
    $(".dropdown-button").dropdown();
    $.validator.setDefaults({errorClass: 'invalid',validClass: "valid",
        errorPlacement: function (error, element) {
            $(element).closest("form").find("label[for='" + element.attr("id") + "']").attr('data-error', error.text());
        }});
    $('#mapaCliente').removeClass('default');
    $('#mapaCliente').addClass('success');
    $('#mapaCliente2').removeClass('default');
    $('#mapaCliente2').addClass('success');
    $(document).hotkey('alt+n', function(e){ $('#newCustomer').click(); });
    $(document).hotkey('alt+q', function(e){ $('#SubmitHeader').click(); });
    $(document).on('click','.checkopc',function(){
        var row    = $(this).closest('tr');
        var indice = $(row).index(); 
        $('.row'+(indice +1)).removeProp('checked');
        $(this).prop('checked','checked');
    });
    //Se evita que al dar click en el objeto se desaparezca del form
    $(document).on('click','.dir-dropdown-content', function(event){
        //The event won't be propagated to the document NODE and 
        // therefore events delegated to document won't be fired
        event.stopPropagation();
    });
    $("#ElegirDireccion").on('click',function(){
        //Se obtiene la linea seleccionada
        var lineasel = $('input[name="RadioDir"]:checked').val();			
        var recid = $("#RecIdDir"+lineasel).val();
        var nombDir = $("#NombreDir"+lineasel).text();
        var direccion = $("#Dir"+lineasel).text();

        $("#cliente").val(nombDir);
        $("#direccion").val(direccion);
        $("#RecIdDireccion").val(recid);

        //Se remueven las clases que hacen visible el cuadro de direcciones
        $("#dropdownDirecciones").removeClass("active");
        $("#dropdownDirecciones").css("display","none");
    });  
function quitarLinea(){
    var rowchecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').length;
    if (rowchecked > 0){
            $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').remove();
            var numeroLineas = $('#articulos >tbody >tr').length;
            $("#NumFilas").val(numeroLineas);	
            UpdateRowId();
            var subtot = subtotal();
            var tot = total();
            $("#subtotal").html(subtot.toFixed(3)).formatCurrency();		        	
            $("#total").html(tot.toFixed(3)).formatCurrency();		    
            var iva = tot - subtot;
            $("#iva").html(iva.toFixed(3)).formatCurrency();
            $('#QuitarLinea').hide();
    }else{
            Materialize.toast('Debe seleccionar al menos una linea al borrar.', 3000);
    }
}
//Funcion que actualiza las filas de la orden de venta
function UpdateRowId(){
	var numeroLineas = $('#articulos >tbody >tr').length;
	var contador = 1;
	$("#articulos > tbody > tr [id*=numLinea]").each(function(){							
		$(this).attr("id","numLinea" + contador);
		$(this).attr("value",contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=LblNumLinea]").each(function(){									
		$(this).text(contador);
		$(this).attr("id","LblNumLinea" + contador);
		$(this).attr("for","numLinea" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=item]").each(function(){							
		$(this).attr("id","item" + contador);
		$(this).attr("name","item" + contador);
		contador += 1;
	});

	var contador = 1;
	$("#articulos > tbody > tr [id*=descripcion]").each(function(){							
		$(this).attr("id","descripcion" + contador);
		$(this).attr("name","descripcion" + contador);
		$(this).attr("data-activates","dropdownComentarioLinea" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=cantidad]").each(function(){							
		$(this).attr("id","cantidad" + contador);
		$(this).attr("name","cantidad" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=unidad]").each(function(){							
		$(this).attr("id","unidad" + contador);
		$(this).attr("name","unidad" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=sitio]").each(function(){							
		$(this).attr("id","sitio" + contador);
		$(this).attr("name","sitio" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=almacen]").each(function(){							
		$(this).attr("id","almacen" + contador);
		$(this).attr("name","almacen" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=lote]").each(function(){							
		$(this).attr("id","lote" + contador);
		$(this).attr("name","lote" + contador);
		$(this).attr("data-activates","dropdownLote" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=localidad]").each(function(){							
		$(this).attr("id","localidad" + contador);
		$(this).attr("name","localidad" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr .preciovta").each(function(){							
		$(this).attr("id","preciovta" + contador);
		$(this).attr("name","preciovta" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr .preciovtaiva").each(function(){							
		$(this).attr("id","preciovtaiva" + contador);
		$(this).attr("name","preciovtaiva" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=montocargo]").each(function(){							
		$(this).attr("id","montocargo" + contador);
		$(this).attr("name","montocargo" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=montoiva]").each(function(){							
		$(this).attr("id","montoiva" + contador);
		$(this).attr("name","montoiva" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=dropdownLote]").each(function(){							
		$(this).attr("id","dropdownLote" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=DisponibleLote]").each(function(){							
		$(this).attr("id","DisponibleLote" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=dropdownComentarioLinea]").each(function(){							
		$(this).attr("id","dropdownComentarioLinea" + contador);
		$(this).attr("name","dropdownComentarioLinea" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=comentariolinea]").each(function(){							
		$(this).attr("id","comentariolinea" + contador);
		$(this).attr("name","comentariolinea" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=ElegirLote]").each(function(){							
		$(this).attr("id","ElegirLote" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=punitariolinea]").each(function(){	
		$(this).attr("id","punitariolinea" + contador);
		$(this).attr("name","punitariolinea" + contador);
		contador += 1;
	});
}

function confirmarCot(){   
    $('#articulos tbody tr input.valido').each(function(){
        if (this.value == ''){
            var row = $(this).closest('tr');
            $(row).remove();
            var numLinea = $('#articulos >tbody >tr').length;
            $("#NumFilas").val(numLinea);                
        }});
        UpdateRowId();
        var cantRows = $('#articulos tbody tr input.valido').length;
        if (cantRows > 0){            
        var str = $('#FormCabecera').serialize();        
        var token = 'inicio/newDocument';
        if (setTipo === 'ORDVTA'){ token = 'inicio/resumenTest';}       
        var numero = $('#seguridad3Digitos').val();
        if (numero === ''){numero = '0000';}
        var id= '&edit=0&id=""';
        if (edicion == 1){ id = '&edit='+edicion+'&id='+$('#DocumentId2').val();}
        if (token!==''){
            $.ajax({url:token,type:"POST",dataType: "json",
                data: str+'&modoentrega='+$('#entregalineas').val()+'&digitos='+numero+id+'&origenVenta='+$("#origenV").val()+'&payment='+$("#paytermArt").val()+'&MetodoPago='+$("#pagolineas option:selected").attr('data-paymmode')+'&moneda='+$('#monedalineas').val()+'&almacen='+$('#almacen1').val()+'&sitio='+$('#sitio1').val(),
                beforeSend: function(req) {
                    $('#loadTarjeta').html('<center><img style="width: 4%;" src="../application/assets/img/cargando.gif"><br>procesando...</center>');
                },
                success: function (data){
                    if (setTipo === 'ORDVTA'){
                        $('#DocumentId2').val(data.OV); }
                    else{$('#DocumentId2').val(data.CTZN);}
                    var response=$('#DocumentId2').val();
                    if(response!==''){
                        $('body').removeClass('theme');
                        $('#articulosDiv').hide();
                        $('#resumenTest').show(function(){
                            $(this).attr('style','display:inline;color:white');
                            $('#breadInicio').attr('onclick','');
                            $('#breadArticulos').attr('onclick','');
                        });
                        $('#breadResumen').show(function(){
                            $(this).attr('style','display:inline;color:white');
                            $('#breadInicio').css('color','rgba(255,255,255,0.7)');
                            $('#breadArticulos').css('color','rgba(255,255,255,0.7)');
                            $('#editarDocument').show();
                        });                       
                        $("#cabeceraOriginal").val(data.encabezadoOV);
                        $("#DocumentType").val(data.documentType);
                        $("#DocumentId").val($('#DocumentId2').val());
                        initResumenTestDiv();
                    }
                    else{
                        Materialize.toast('Error de confirmacion', 3000);
                        var f={responseText:"No confirma"};
                        catchError(f,"respuesta vacia");
                        $('#loadNext').html('');
                    }
                },
                error: function (jqXHR, exception) {            
                    Materialize.toast('Error de confirmacion', 3000);
                    catchError(jqXHR,exception);
                    $('#loadNext').html('');
                }
            });
        }
        else{
            Materialize.toast('Tipo de Documento desconocido', 3000);
        }        
    }
}
function scrollinDropdown() {
    var selects = $('select').not('#secretarioventa,#responsableventa');
    $(selects).each(function(){
        $(this).material_select(function() {
            $('input.select-dropdown').trigger('close');
        });
    var onMouseDown = function(e) {
    if (e.clientX >= e.target.clientWidth || e.clientY >= e.target.clientHeight) {e.preventDefault();}};
    $(this).siblings('input.select-dropdown').on('mousedown', onMouseDown);
    });
}

function nuevoDocumento(){
    var tipo = $('#documentTypeOrigen').val();
    $('#newDocument #documentType').val(tipo);
    $('#newDocument').submit();
}
function refreshLines(){
    $('#resumenTestTablaLineas tbody').empty();
    $('#totalResumen').empty();
    var docId = $('#DocumentId2').val();
    var docType = $("#DocumentType").val();
    if(docId!==""){
        $.ajax({ url:"inicio/refreshLines",type: "POST",	dataType: "JSON",
        data: {'docId':docId,'docType':docType},
        success: function (data){
            var bodyTable  = '';
            var qty = '';
            var montocargo = '';
            var cargoN=Number($("#pagolineas").val());
            var montocargoiva = '';
            var suma = 0;
            if(data.length>0){
                $(data).each(function(index){
                    qty = Number(this.SALESQTY);
                    montocargo = Number(this.MONTOCARGO); 
                    montocargo=montocargo+(montocargo*(cargoN/100));
                    montocargoiva = montocargo*1.16;
                    suma += montocargoiva;
                    bodyTable += '<tr>';
                    bodyTable += '	<td>'+(index+1)+'</td>';
                    bodyTable += '	<td>'+this.ITEMID+'</td>';
                    bodyTable += '	<td>'+this.NAME+'</td>';
                    bodyTable += '	<td>'+qty.toFixed(2)+'</td>';
                    bodyTable += '	<td style="text-transform:uppercase">'+this.SALESUNIT+'</td>';
                    bodyTable += '	<td>'+this.INVENTSITEID+'</td>';
                    bodyTable += '	<td>'+this.INVENTLOCATIONID+'</td>';
                    bodyTable += '	<td> $'+montocargo.toFixed(2)+'</td>';
                    bodyTable += '	<td> $'+montocargoiva.toFixed(2)+'</td>';
                    bodyTable += '</tr>';
                    bodyTable += '<tr>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td>Comentarios: '+this.STF_OBSERVATIONS+'</td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '</tr>';
                });
                $('#resumenTestTablaLineas tbody').html(bodyTable);
                $('#totalResumen').html('$'+suma.toFixed(2));
            }
            else{
                swal('¡Alto!','No se han registrado los artículos en Dynamics, favor de reportar a sistemas que Dynamics esta borrando partidas','info');
            }
        }
    });
    }
    else{
        swal('¡Alto!','No se han registrado un folio de cotización u orden de venta en Dynamics, favor de verificar contra Dynamics','info');
    }
}
        
function agregarNegados(){
    var table = '';
    var body  = '';
    table  = '<table class="table" id="articulosNegados">';
    table += '	<thead>';
    table += '		<tr style="height:100px;">';
    table += '			<th>Articulo</th>';
    table += '			<th>Cantidad</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">Surtir material  cliente lo empezara a usar.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, solo cotizo.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, cliente lo requería en el momento no puede esperar su envío.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">Surtir, cliente requiere material por temporada.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, compra excepcional.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, se envió de otra sucursal.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">Surtir material para venta exclusiva de proyecto.</th>';
    table += '		</tr>';
    table += '	</thead>';
    table += '	<tbody>';
    var arrNeg = negados.itemGroup.items
   
    $(arrNeg).each(function(index){
            var unidad      = this.unidad;
            var sitio       = this.sitio;
            var almacen     = this.almacen;
            var articulo    = this.articulo;
            var descripcion = this.descripcion;
            var cliente     = this.cliente;
            var qty         = this.cantidad;
            var disp        = this.disponible;
            var cantNegada  = Number(qty) - Number(disp);
            body  += '		<tr data-articulo="'+articulo+'" data-cantnegada="'+cantNegada+'" data-sitio="'+sitio+'" data-almacen="'+almacen+'" data-disponible="'+disp+'" data-unidad="'+unidad+'" data-descripcion="'+descripcion+'" data-cliente="'+cliente+'">';
            body  += '			<td><span>'+articulo+'</span></td>';
            body  += '			<td><span>'+cantNegada+'</span></td>';
            body  += '			<td><input type="checkbox" id="opc1-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc1-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc2-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;" checked/><label for="opc2-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc3-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc3-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc4-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc4-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc5-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc5-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc6-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc6-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc7-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc7-'+(index+1)+'"></label></td>';
            body  += '		</tr>';
    });
    table += body;
    table += '	</tbody>';
    table += '</table>';
    $('#modalNegados .modal-body #contenedorNegado').html(table);
}

function checarStatusBloqueo(ov){
    $.ajax({url: "inicio/checarBloqueo",type: "POST", data: { "ov": ov },
        success: function (data){
            data = JSON.parse(data);
            var bloqueo = data.resultado[0].BLOCKED;
            if (bloqueo != 0){
                setTimeout(function(){ checarStatusBloqueo(ov); }, 2000);
            }else{
                $('#alertaBloqueo').removeClass('orange darken-3');
                $('#alertaBloqueo').addClass('teal lighten-1');
                $('#alertMsjBloq').addClass('off');
                $('#alertMsjDetBloq').addClass('off');
                $('#alertMsjSuccessBloq').removeClass('off');
                $('#iconWaitBloq').hide();
                $('#iconSuccessBloq').show();
                $('#GenerarREM').removeClass('disabled');
            }
        }
    });
}
function tab1Click(){
    $(document).find('#ExistenciasSitioClte tbody').find('tr').first().focus();
}
function tab2Click(){
    $(document).find('#ExistenciasTodosSitios tbody').find('tr').first().focus();
}

function obtenerHora(){
    var d = new Date();
    var day = d.getDate();
    var month = d.getMonth()+1;
    var year = d.getFullYear();
    var hora = d.getHours();
    var minutos = d.getMinutes();
    var segundos = d.getSeconds();
	if (day < 10){ day = '0'+day;}
	if (month < 10){ month = '0'+month;}
	if (hora < 10){	hora = '0'+hora; }
	if (minutos < 10){ minutos = '0'+minutos; }
	if (segundos < 10){ segundos += '0'; }
    var fecha = day+'/'+month+'/'+year+' '+hora+':'+minutos+':'+segundos;
    return fecha;
}
function detalleClienteVenta(salesId,obj){		
    var table = $('#UltimasVentas').DataTable();
    var tr = $(obj).closest('tr');
    var row   = table.row(tr);
    var sitio = $("#sitioclte").val();
    if ( row.child.isShown() ) {
        row.child.hide();
        tr.removeClass('shown');
        $(obj).html('add_circle');
        $(obj).attr('style','color:green;cursor:pointer');
    }
    else {
        $('#waitingDivModalUV').css('display','block');
        var html  = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; border-top: solid 1px; border-right: solid 1px; border-left: solid 1px; border-bottom: solid 1px;">';
            html += '<thead>';
            html += '    <th># Linea</th>';
            html += '    <th>Orden de Venta</th>';
            html += '    <th>Codigo Art.</th>';
            html += '    <th>Almacen</th>';
            html += '    <th>Nombre</th>';
            html += '    <th>Cantidad</th>';
            html += '    <th>Fisica Disponible</th>';
            html += '    <th>Unidad</th>';
            html += '    <th>Agregar</th>';
            html += '</thead>';
            html += '<tbody>';
             $.ajax({ url:"inicio/detalleVenta", type: "POST", dataType: "json",
             data: {"ov":salesId, "token":"detalleVenta","transaction":'ORDVTADET','sitio': sitio},
            success: function (res){
                    $(res).each(function(index){
                    var numlinea   = eval(this.LINENUM);
                    var qty        = eval(this.QTYORDERED);
                    var disponible = eval(this.FisicaDisponible);
                    html += '<tr style="border-bottom:solid 1px">';
                    html += '   <td>'+numlinea.toFixed(2)+'</td>';
                    html += '   <td>'+this.SALESID+'</td>';
                    html += '   <td>'+this.ITEMID+'</td>';
                    html += '   <td>'+sitio+'CONS</td>';
                    html += '   <td>'+this.NAME+'</td>';
                    html += '   <td>'+qty.toFixed(2)+'</td>';
                    html += '   <td>'+disponible.toFixed(2)+'</td>';
                    html += '   <td>'+this.SALESUNIT+'</td>';
                    html += '   <td><input type="checkbox" data-item="'+this.ITEMID+'" data-unidad="'+this.SALESUNIT+'" class="col l1 s1 m1 checkFamDetVta" id="itemCheckFamDetVta'+this.SALESID+'-'+index+'"/><label for="itemCheckFamDetVta'+this.SALESID+'-'+index+'"></label></td>';
                    html += '</tr>';
                });
                html += '</tbody>';
                html += '</table>';
                row.child(html).show();
                tr.addClass('shown');
                $('#waitingDivModalUV').css('display','none');
                $(obj).html('remove_circle');
                    $(obj).attr('style','color:red;cursor:pointer');
            }
        });
    }
}

function ultimasVentas(){
    var cliente = $("#claveclte").val();
    if(cliente!==""){
        $.ajax({url: "inicio/getUltimasVentas",type:"POST",dataType: "json",data: { "cliente": cliente },
            beforeSend: function (xhr) {
                $('#modalLoading').openModal({dismissible: false});
            },
            success: function (data){
                if (data != 'NoResults'){
                    $('#UltimasVentas').dataTable().fnClearTable();
                    $('#UltimasVentas').DataTable().destroy();
                    $('#UltimasVentas').dataTable({ "pageLength" : 8}).fnAddData(data); 
                    $('.dataTables_length').hide();
                    $('#UltimasVentasModal').openModal({dismissible: false,ready : function(){$('#btnAgregarDetVta').html('Cerrar');}});
                }
                $('#modalLoading').closeModal();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#modalLoading').closeModal();
                swal(':(','algo salio mal: '+textStatus,'error');
            }
        });
    }
    else{
        Materialize.toast("Debe seleccionar un cliente",3000,"red");
    }
}
//////////////////comportamiento de navegar con tabs/////////////////////////////////////////////////
	$(document).on('keydown','.collapsible-header',function(event) {
		if (event.which === 9){
			$(this).trigger('click');
		}
	});
/////////////////////////////////////////////////////////////////////////////////////////////////////////	

		function bread1() {
			$('#BusExi').addClass('hide');
                        $('#divInicioSesion').addClass('offset-s5');
			$('#BusFam').addClass('hide');
			$('#UltVen').addClass('hide');
			$('#Agrlin').addClass('hide');
			$('#EliLis').addClass('hide');
			$('#cabeceraDiv').show();
			$('#articulosDiv').hide();
			$('#breadArticulos').hide();
			$('#editarDocument').hide();
		}

		function bread2() {
			$('#cabeceraDiv').hide();
			$('#articulosDiv').show();
			$('#editarDocument').show();
		}

		function bread3() {
			//docId = sessionStorage.getItem('DocumentId');
			$('#BusExi').addClass('hide');
                        $('#divInicioSesion').addClass('offset-s5');
			$('#BusFam').addClass('hide');
			$('#UltVen').addClass('hide');
			$('#Agrlin').addClass('hide');
			$('#EliLis').addClass('hide');
			editarDocumento();
		}
/**
 * junta las 2 listas de articulos en una sola esto para detectar nombre comunes y los asocia al codigo del articulo,
 * se hace en 2 consultas para no dar tanta carga a la base de datos
 * */
function cargarItems(art,comunes){
    var itemsMap = [],
    itemsMap2 = [];
    items = art;
    $.each(items,function(i, val){
        itemsMap[items[i].value] = items[i].value+' - '+items[i].label;
    });
    artArray=itemsMap;
    var a=items.length;
    var items2=JSON.parse(comunes);//este es el arreglo para los articulos relacionados
    var b=items2.length;
    var i=0;
    for(var l=items.length;l<(a+b);l++){
        if( typeof itemsMap2[items2[i].value]==='undefined'){
            itemsMap2[items2[i].value] = ' - '+items2[i].label+'';
        }else{
             itemsMap2[items2[i].value] = '  '+itemsMap2[items2[i].value]+' - '+items2[i].label+'';
        }
        i++;
    }
    $.each(items,function(i, val){
        if( typeof itemsMap2[items[i].value]==='undefined'){
            itemsMap2[items[i].value]=" ";
        }
        //items[i].label= itemsMap[items[i].value]+itemsMap2[items[i].value];
        items[i].label= itemsMap[items[i].value];
        items[i].nameAlias = itemsMap[items[i].value]+itemsMap2[items[i].value];
    });
    
}
function submitHeader(){
    verifica();
    setTimeout(function (){  
        var band=localStorage.getItem('bandera'); 
        var cliente=$("#claveclte").val();
        if(band=='si' && cliente.indexOf('C0')!=-1){
            $('#opcionesTeclas').removeClass('hide');
            $('#UltVen').removeClass('hide');
            $('#Agrlin').removeClass('hide');
            $('#EliLis').removeClass('hide');
            if('#resumenTitle'){
               $('#resumenTitle').remove();
               $('#clienteTitle').remove();
            }
            if(edicion === '1'){			
                $('#DocumentId2').val(docID);
                $('#articulos tbody').empty();
            }
            $('#paytermArt').trigger('change');
            $('#FormCabecera').submit();
            if ($('#FormCabecera').validate().form()){
                $('#BusExi').removeClass('hide');
                $('#divInicioSesion').removeClass('offset-s5');
                $('#articulos2').removeClass('default');
                $('#articulos2').addClass('success');
                $('#mapaCliente2 a').addClass('link');
                $('#mapaCliente2').click(function(){ 
                    $("#articulosDiv").hide();
                    $("#cabeceraDiv").show();
                    $("#mapaArticulos").remove();
                    $("#opcionesTeclas").addClass('hide');
                    $("#correosList").remove();
                    $("#listClient").remove();
                    $("#baina1").remove();
                    $('#articulos2').removeClass('success');
                    $('#articulos2').addClass('default');
                });
                $('#articulosDiv').show();
                $('#cabeceraDiv').hide();
                cabeceraOn = 1;			
                initArticulosDiv(lineasArr);
                $('#breadArticulos').show(function(){
                    $(this).attr('style','display:inline;color:white');
                    if ($('#breadInicio').is(':visible')){
                        $('#breadInicio').css('color','rgba(255,255,255,0.7)');
                    }
                });
                if ($('#articulos tbody tr').length === 0){
                    $('#AgregarLinea').trigger('click');
                }
                $('body').addClass('theme');
                $('#AgregarLinea').appendTo($('#articulos'));
                $('#QuitarLinea').html('<i class="mdi-action-delete"></i>Eliminar');
                $('#QuitarLinea').addClass('centerFlex');
                $('#QuitarLinea').hide();
                $(document).on('click', '#articulos tbody tr td:first-child label',rowSelected);
                $('#OrderLine-Header ul').prepend('<li class="col m12" id="aqui"></li>');
                $('#EnviarForm').appendTo('#aqui');
                $('#AgregarLinea').attr('data-shortcut', 'Ctrl + Insert');
                $('form#FormArticulos > .row + .row').prepend('<h4 id="resumenTitle"><i class="mdi-content-content-paste left tooltipped" data-name="mdi-file-document-box"></i> Resumen</h4>');
                $('form#FormArticulos > .row + .row').append('<h4 id="clienteTitle" class="mt"><i class="mdi-action-account-box left tooltipped" data-name="mdi-file-document-box"></i> Cliente</h4>');
                $('#articulos').colResizable({resizeMode:'overflow'});
                /////////////////////////////////////////////////////////////////////////////////////////
            }
        }
        else{
            swal('Error!','Favor se asegurarse de colocar clave de cliente y no el nombre','error');
            $("#claveclte").focus();
        }        
    },500);    
}
function agregarLinea(autocomp_opt,band){
    $('#AgregarLinea').addClass('load');
    var tmpBandAgregar = band ? band : false;
    if ( ($('.emptyRow').length < 1) || tmpBandAgregar ){
        var numLinea = $('#articulos >tbody >tr').length + 1;
        $("#NumFilas").val(numLinea);
        var newLine = $('<tr class="emptyRow"><td><p id="LineaCheck"><input type="checkbox" id="numLinea'+ numLinea + '" value="'+ numLinea + '" /><label id="LblNumLinea'+ numLinea + '" for="numLinea'+ numLinea + '" onclick="rowSelected()">'+ numLinea + '</label></p></td><td><input class="item input-table valido" type="text" name="item'+ numLinea + '" id="item'+ numLinea + '"/></td><td><button class="btn-copiar btn-copiar-no-list" type="button" data-copy><i class="mdi-content-content-copy" data-name="mdi-file-document-box"></i> Copiar</button><textarea rows="1" cols="26" class="input-table valido tacoment"  data-activates="dropdownComentarioLinea'+numLinea+'" readonly style="width: 300px;" type="text" id="descripcion'+ numLinea + '" name="descripcion'+ numLinea + '"></textarea></td><td><input class="input-table getPrice center-align valido" min="1" id="cantidad'+ numLinea + '" name="cantidad'+ numLinea + '" data-disp="" value=""/></td><td><input class="input-table valido" readonly type="text" id="unidad'+ numLinea + '" name="unidad'+ numLinea + '" style="text-transform:uppercase" /></td><td><input class="input-table cambioSitio valido" readonly type="text" id="sitio'+ numLinea + '" name="sitio'+ numLinea + '" /></td><td><input class="input-table cambioAlmacen valido" type="text" readonly data-almacen="" id="almacen'+ numLinea + '" value="" name="almacen'+ numLinea + '" /></td><td><input class="input-table BatchAvailable"  data-activates="dropdownLote'+numLinea+'" type="text" id="lote'+ numLinea + '" name="lote'+ numLinea + '" /></td><td><input class="input-table valido" readonly type="text" id="localidad'+ numLinea + '" name="localidad'+ numLinea + '" /></td><td style="text-align: right !important;"><input class="input-table right-align IsPriceBlocked preciovta valido"  data-activates="dropdownPUnitarioLinea'+numLinea+'"  type="text" readonly id="preciovta'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td><td style="text-align: right !important;"><input class="input-table right-align preciovtaiva valido" type="text" readonly id="preciovtaiva'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td><td style="text-align: right !important;"><input class="input-table right-align valido" type="text" readonly name="montocargo'+ numLinea + '" id="montocargo'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td><td style="text-align: right !important;"><input class="input-table right-align valido" type="text" readonly name="montoiva'+ numLinea + '" id="montoiva'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td></tr>');
        $(".item",newLine).autocomplete(autocomp_opt);
        $("#articulos").append(newLine);
        $("#preciovta" + numLinea).parent('td').append('<div id="dropdownPUnitarioLinea' + numLinea +'" class="punitario-dropdown-content coment"><div class="input-field" style="margin-top:20px;"><input type="number" id="punitariolinea'+numLinea+'" name="punitariolinea'+numLinea+'" readonly min="0" value="0" /><label for="punitariolinea'+numLinea+'">Precio Unitario<button class="btn-small red right closeCambioPrecio" type="button">X</button></label></div></div>');
        $("#descripcion" + numLinea).parent('td').append('<div id="dropdownComentarioLinea' + numLinea +'" class="comentarios-dropdown-content coment"><div class="input-field" style="margin-top:20px;"><textarea id="comentariolinea'+numLinea+'" name="comentariolinea'+numLinea+'"  class="materialize-textarea"></textarea><label for="comentariolinea'+numLinea+'">Comentarios<button class="btn-small red right closeComentLinea" type="button">X</button></label></div></div>');
        $("#lote" + numLinea).parent('td').append('<div id="dropdownLote' + numLinea +'" class="lote-dropdown-content coment"><div class="row"><h5>Disponible<button class="btn-small red right closeLoteLinea" type="button">X</button></h5><div class="divider"></div><table id="DisponibleLote'+numLinea+'"><thead><tr><th>Almacén</th><th>Número de Lote</th><th>Física Disponible</th><th></th></tr></thead><tbody></tbody></table></div><br/><div class="divider"></div><div class="row" style="margin-top:20px; margin-bottom:10px;"><a class="btn light-blue darken-4 white-text right" id="ElegirLote'+numLinea+'">Aceptar</a></div></div>');	
        $(".dropdown-button").dropdown();
        $("#item"+numLinea).focus();
        bandAgregarFam = false;
        $('#AgregarLinea').removeClass('load');
        setTimeout(function(){$('#AgregarLinea').removeClass('load');}, 500);	
    }else{
        $('.emptyRow').find('.item').focus();
        $('#AgregarLinea').removeClass('load');
    }
}
function getProductDetail (myRow,ui){
    var art         = ui.item.value;
    var excedido    = ( $('#cantidad' + myRow).hasClass('excedido') ) ? 'excedido' : 'ok';
    var cliente     = $("#cliente").val();
    $.ajax({
        url:"inicio/productoDetalle",type:"post", data:{"token":"productdetail","articulo":art},
        async: true,
        beforeSend: function (xhr) {
            Materialize.toast('Procesando detalles de articulo: '+art,3000);
        },
        success: function (data, textStatus, jqXHR) {
            var d=data;
            if(data.length>0){
                $.each(d,function (i,v){
                    $("#item" + myRow).val(art);
                    $("#item" + myRow).removeClass('invalid');
                    $("#descripcion" + myRow).val(d[i].label);
                    $("#cantidad" + myRow).val("1");			    					    			   						
                    $("#unidad" + myRow).val(d[i].unidad);
                    $('#modal1 #preloaderExistencias').show();
                    $('#ExistenciasSitioClte tbody').html('');
                    $('#ExistenciasTodosSitios tbody').html('');
                    $('#modal1 #cancExistLinea').val(myRow);
                    getExistencias(art,myRow,d[i].unidad,d[i].label,excedido,cliente);//Manda a llamar la funcion de existencias
                    $('#modal1').openModal({dismissible: false});
                    $('#lean-overlay').remove();
                    $('ul.tabs').tabs('select_tab', 'test1');
                    ValidarFraccionado(art,myRow);
                    $('.lean-overlay').remove();//Se manda a llamar la función que muestra el control de pedacería (FRACCIONADO)
                    $("input[name='cantidadSol']").focus();
                });
            }
            else{
                swal("Alto","Artículo no existe.",'info');
            }
        },
        error: function (jqXHR,exep){
           Materialize.toast('Error!'+catchError(jqXHR,exep), 3000);
        }                                            
    });
}
function solicitaTraspaso(item,desc,existencia,id,almacen,diferencia){
    
    var almacenDestino='CEDSCONS';
    if(almacen==='CHIHMRMA'){
        almacenDestino='CEDSMRMA';
    }
    if(almacen==='CHIHESPC'){
        almacenDestino='CEDSESPC';
    }
    if(almacen==='CHIHEQPS'){
        almacenDestino='CEDSEQPS';
    }
    var solicitar=$('#'+id).val();
    if(diferencia>0){
        solicitar=diferencia;
    }
    if(existencia>=solicitar){        
        
        swal({
            title: 'Seleccione un motivo',
            html:   '<select id="swal-input1" required="" class="browser-default" name="motivo">'+
                        '<option value="NO HAY EXISTENCIA">NO HAY EXISTENCIA</option>'+
                        '<option value="MATERIAL EN PEDACERIA">MATERIAL EN PEDACERIA</option>'+
                        '<option value="MATERIAL CADUCO">MATERIAL CADUCO</option>'+
                        '<option value="EN EL SISTEMA SI HAY, FISICAMENTE NO ESTA">EN EL SISTEMA SI HAY, FISICAMENTE NO ESTA</option>'+
                    '</select>'
                    +'<input id="swal-input3"  name="solicitado" value="'+solicitar+'" class="swal2-input" placeholder="CANTIDAD SOLICITADA">'
                    +'<input id="swal-input2" name="comentarios" class="swal2-input" placeholder="COMENTARIOS">',
            showCancelButton: true,
            confirmButtonText:'Enviar solicitud',
            cancelButtonText:'Cancelar'
        }).then(function (result){ 
            var f = new Date();
            var msj='<table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                '<tbody>'+
                 '   <tr style="background-color: rgb(222, 222, 222);font-weight: bold;">'+
                 '      <td style="text-align: center;">'+
                 '         <span>SOLICITUD DE TRASPASO DE MATERIALES DE CEDIS A ALMACEN CHIHUAHUA</span>'+
                 '      </td>'+
                 '</tr>'+
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
                                   ' <tr>'+
                                   '    <td style="text-align: center;color: #333;font-weight: bold;">'+
                                    '       MOVIMIENTO:'+
                                     '  </td>'+
                                      ' <td>'+almacenDestino+' A '+almacen+'</td>'+
                                   '</tr>'+
                                   ' <tr>'+
                                   '    <td style="text-align: center;color: #333;font-weight: bold;">'+
                                    '       CANTIDAD PARA VENTA:'+
                                     '  </td>'+
                                      ' <td>'+$('#'+id).val()+'</td>'+
                                   '</tr>'+
                               '</tbody>'+
                           '</table>'+
                       '</td>'+
                   '</tr>'+
                   '<tr>'+
                  '     <td>'+
                    '        <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5>'+
                    '           <tr style="background-color: rgb(222, 222, 222);font-weight: bold;"><th>CLAVE ARTÍCULO</th><th>DESCRIPCIÓN DEL ARTICULO</th><th>CANTIDAD</th></tr>'+
                     '          <tr><td>'+item+'</td><td>'+decodeURI(desc)+'</td><td style="text-align: center;">'+$('#swal-input3').val()+'</td></tr>'+
                      '     </table>'+
                       '</td>'+
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
                    formato:"traspasosSolicitud.html",
                    type:0,
                    cliente:$('#claveclte').val(),
                    user:userMail,
                    item:item,
                    venta:$('#'+id).val(),
                    cant:$('#swal-input3').val(),
                    almacen:almacenDestino+' A '+almacen,
                    vendedor:usuario+' - '+userName,
                    comenta:$('#swal-input2').val(),
                    motivo:$('#swal-input1').val()
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

function guardarNegados(edicion,documentType){
	bandNegados = false;
	$('#modalNegados').closeModal({
		complete : bandNegados = true
	});
	var comentariosNegados  = [];
	comentariosNegados[1]   = 'Surtir material  cliente lo empezara a usar.';
	comentariosNegados[2]   = 'No surtir, solo cotizo.';
	comentariosNegados[3]   = 'No surtir, cliente lo requería en el momento no puede esperar su envío.';
	comentariosNegados[4]   = 'Surtir, cliente requiere material por temporada.';
	comentariosNegados[5]   = 'No surtir, compra excepcional.';
	comentariosNegados[6]   = 'No surtir, se envió de otra sucursal.';
	comentariosNegados[7]   = 'Surtir material para venta exclusiva de proyecto.';
	if (bandNegados){
		var tr              = $('#articulosNegados tbody tr');
                var arr = [];
		$.each(tr,function(v,index){
                    var obj ={ };
			obj.cantNegada  = $(this).attr('data-cantnegada');
			obj.artNegado   = $(this).attr('data-articulo');
			obj.almacen     = $(this).attr('data-almacen');
			obj.sitio       = $(this).attr('data-sitio');
			obj.cantDisp    = $(this).attr('data-disponible');
			obj.unidad      = $(this).attr('data-unidad');
			obj.descripcion = $(this).attr('data-descripcion');
			obj.cliente     = $("#claveclte").val();
			var chk         = $(this).find('td').find('input:checked');
			id              = $(chk).attr('id');
			id              = id.split('-');
			id              = id[0].replace('opc','');
			obj.comentario  = comentariosNegados[id];
                        arr.push(obj);
                    });
                    $.ajax({url: "inicio",type: "post",dataType: "json",
	    	    data: {'token':'generarNegado','data':arr},
	    	    success: function (data){
	    	    	if (data == 'OK'){
	    	    		Materialize.toast('Articulo(s) agregado con exito a negados',3000);
	    	    	}else{
	    	    		Materialize.toast('Articulo no agregado a negados',3000,'red');
	    	    	}
	    	    	var preciosEnCero = checarPrecios();
                        if (!preciosEnCero){
                            $('#seguridad3Digitos').val('0000');
                            confirmarCot(edicion,documentType);
                        }else{
                                swal({
                                    type: 'error',
                                    html: 'Existen precios en cero, favor de verificar.'
                                });
                        }
	    	    }	    	
		});
	}
}
$(document).on('click','#agregarItemsFamilia, #btnAgregarDetVta',function(e){
        if ( $(this).attr('id') == 'agregarItemsFamilia' ){
                $('#ExistenciaFamiliaModal').closeModal();
        }else if ( $(this).attr('id') == 'btnAgregarDetVta'  ){
                $('#UltimasVentasModal').closeModal();
        }   
     $(familiaArr).each(function(){
            var lineaActual = $('#NumFilas').val();
            if ( $('#item'+lineaActual).val() != '' ){
                    bandAgregarFam = true;
                    $('#AgregarLinea').trigger('click');
                    lineaActual = $('#NumFilas').val();
            }
            $('#item'+lineaActual).val(this.item);
            $('#descripcion'+lineaActual).val(this.nombre);
            $('#cantidad'+lineaActual).val(this.qty);
            $('#cantidad'+lineaActual).attr("data-lastvalue",this.qty);
            $('#unidad'+lineaActual).val(this.unidad);
            $('#sitio'+lineaActual).val(this.sitio);
            $('#almacen'+lineaActual).val(this.almacen);
            $('#localidad'+lineaActual).val(this.localidad);
            $('#cantidad'+lineaActual).trigger('change');
        });
        familiaArr = [];
    });
//////////////////script articulos//////////////////////////////////////////////////////

	$('#prestashop1').click(function(){
		var prestashop = 'x';
		url = 'http://svr01:8080/prestashop'+prestashop+'/inicio/'+urlAmigable+'?token=iframe#image-block';
		$('#iframePrestashop').attr('src',url);
	});

	$('#prestashop2').click(function(){
		var prestashop = 'y';
		url = 'http://svr01:8080/prestashop'+prestashop+'/inicio/'+urlAmigable+'?token=iframe#image-block';
		$('#iframePrestashop').attr('src',url);
	});

	$('#prestashop3').click(function(){
		var prestashop = 'z';
		url = 'http://svr01:8080/prestashop'+prestashop+'/inicio/'+urlAmigable+'?token=iframe#image-block';
		$('#iframePrestashop').attr('src',url);
	});
        var urlAmigable = '';
function checarSesion(){
    $.ajax({ url: "inicio",type: "get",dataType: "json",data: { "token":"checarSesion" },
        success: function (data){}
    });
}
        
/////////no ejecuta el submit de la forma///////////////
$('#FormCabecera').on('submit',function(event){
    event.preventDefault();
    if (!$(this).validate().form()){
        if ($('#secretarioventa').val() === ''){
            swal("Alto","El campo Secretario de Venta es requerido!.",'info');
            $('#secretarioventa').focus();
        }
        if ($('#responsableventa').val() === ''){
            swal("Alto","El campo Responsable de venta es requerido!.",'info');
            $('#responsableventa').focus();
        }
        if ($('#origenV').val() === ''){
            swal("Alto",'El campo origen de venta es requerido!.','info');
            $('#origenV').focus();
        }                         
    }
});
function cargarUsuarios(data){  
    if(data.response=="ok"){
        $('#secretarioventa').html(data.res);
        $('#secretarioventa').select2();
        $('#responsableventa').html(data.res);
        $('#responsableventa').select2();    
    }                            
    if (data.reg ==1){
        $('#administrarDiv').trigger('click');
        $('#admonMsg').show();
    }else{
        $('#admonMsg').hide();
    }
    $('#modalLoading').closeModal();
}
/**
 * cambia el sitio del docmento
 */
$(document).on('change','#sitioLineas',function(){
    var valor = this.value;
    $("#almacenes").val(valor+'CONS');
    $("#almacenes").val(valor+'CONS');			
    $('.cambioSitio').val(valor);
    $('.cambioAlmacen').each(function(){
            var almac = this.value.slice(-4);
            this.value = valor+almac;
    });
    $('#pagolineas').change();
});
$(document).on('click',"#GenerarREM",function(e){
    verifica();
    if(localStorage.getItem('bandera')=='si'){
        $('#preloaderRemision').show();
        var ov = $("#OrdenVentaRem").val();
        var condi = $("#condiEntrega").val();
        if(condi!=="CONTADO"){ 
            ValidarLimiteCredito(ov,usuario,condi);
        }
        else{
            generarRemision(ov); 
        }        
    }
});

$(document).on('click',"#Facturar",function(e){
    verifica();
    if(localStorage.getItem('bandera')=='si'){
        cargaDatos2Factura();         
    }
});
    function cargaDatos2Factura(){
        $.ajax({
            url:"inicio/get-Direcciones",type:"post", data:{ov:$("#DocumentId2").val()},dataType: 'json',
            beforeSend: function (xhr) {
                $("#btnFacturar").hide();
                $('#direccionF').html("");
            },
            success: function (data, textStatus, jqXHR) {
                var str="";
                $('#direccionF').html(str);
                $.each(data,function (i,v){
                    str+='<option value="'+v.RECID+'">'+v.ADDRESS+'</option>';
                });
                $('#direccionF').html(str);   
                $("#btnFacturar").show();
            }            
        });
        $('#modalFactura').openModal({dismissible: false});
    }
    function setPayMode(payMode){
        var payArr=[];
        if(payMode=='CONTADO'){
            $.each(cargoOptions,function (i,v){
                if(v.PaymMode!=='99'){
                   payArr.push(v); 
                }
            });
        }
        else{
            payArr.push({STF_PercentageCharges:'0.0',PaymMode: "99", NAME: "OTROS"});
        }
        var html='';
        $.each(payArr,function (i,v){
            var selected='';
            if(paymmodedoc===v.PaymMode){ 
                selected ='selected';
            }
            html+='<option value="'+Number(v.STF_PercentageCharges)+'" data-paymmode="'+v.PaymMode+'" '+selected+'>'+v.NAME+'</option>';
        });
        $('#pagolineas').html(html);
        if ($("#condiEntrega").val() === 'CREDITO') {
            $('#pagolineas option').val('0.00');
        }
    }
    function crearDiario(){
        try{
            var formData = $('#diarioPagoForm').serialize();
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
                        $('#diarioMontoFactura').val(data.saldo);
                     }
                     else{
                         $('#process').html(data.resultado.resultado);
                         $('#diarioMontoFactura').val(data.resultado.saldo);
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
    function mostrarModalPago(){
        formaPagoFactura="";
        if(totalDiario===0){
            var total=$('#totalResumen').text();
            total=total.substr(1);
            totalDiario=Number(total);
        }
        $('#modalFactura').closeModal();
        $('#diarioPago').openModal({dismissible: false});
        var factura=$("#folioFactura").text();
        $('#diarioFacturaFolio').val(factura);
        $('#diarioMontoFactura').val(totalDiario);        
        var html='';
        $.each(payModeList,function (i,v){
            var sel='';
            if($('#pagolineas option:selected').attr('data-paymmode')===v.PAYMMODE){
                sel='selected="selected"';
                formaPagoFactura=v.PAYMMODE;
            }
            html+='<option value="'+v.PAYMMODE+'" '+sel+'>'+v.name+'</option>';
        });
        $('#diarioFPago').html(html);
        //crea un diario nuevo pagolineas
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
            var html='';
            $('#diarioCuentaContra').html(html);
            $.each(diarioCuentasPago,function (i,v){                
                if(v[0]===mostrador ){
                    var pg = v[2].split(",");
                    for(var a=0;a<=pg.length;a++){
                        if(pg[a]===formaPagoFactura){
                            html+='<option value="'+v[1]+'" data-paymode="'+v[2]+'">'+v[1]+'</option>';
                        }                         
                    }                   
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
    function facturar(){
        if(havePermision(14)){
          $('#GenerarREM').click();
        }
        else{
            $.ajax({ 
                url:"inicio/facturar",type:"post", data:{
                    ov:$("#DocumentId2").val(),
                    remision:$('#PackingSlipId2PDF').val(),
                    ordenCliente:$('#OrdenCliente').val(),
                    refCliente:$('#ReferenciaCliente').val(),
                    comentariosCabecera:$('#comentariosCabecera').val(),
                    direccion:$('#direccionF').val(),
                    usoCFDi:$('#usoCFDI option:selected').val(),
                    pagoModo:$('#pagolineas option:selected').attr('data-paymmode'),
                    pago:$('#paytermArt').val()
                },dataType: 'json',
                beforeSend: function (xhr) {
                    $('#loadFacturaSt').html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                    if(data.resultado==="ok"){
                        var pago=$('#entregaF').val();
                        $('#loadFacturaSt').html('<a id="folioFactura" href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='+$("#DocumentId2").val()+'&amp;tipo=CLIENTE" target="_blank">'+data.respuesta+'</a>');
                        $("#btnFacturar").hide();
                        refreshLines();
                        if(havePermision(12)){
                            $('#footerFactura').append('<a onclick="mostrarModalPago()" class="waves-effect light-blue darken-4 white-text btn-flat" style="margin-right: 13px;">Asociar Factura a Diario de Pago</a>');
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
               
    }
$(document).on('click','#actionArticulos',function(e){
    verifica();
    if(localStorage.getItem('bandera')=='si'){
        var registros = $("#item1").val();
        var entregaF= $("#entregalineas").val();
        var flag=false;
        if(registros!=""){flag=true;}
        if(entregaF===null){flag=false;}
        if(flag==true){
            $('#confirmarDocumento2').removeClass('default');
            $('#confirmarDocumento2').addClass('success');
            $('#editar2').removeClass('default');
            $('#editar2 a').addClass('link');
            $('#mapaCliente2 a').removeClass('link');
            $('#editar2').addClass('success');
            $('#mapaCliente2').unbind('click');
            $('#editar2').click(function (){
                bread3();
            });
            var negados = checarNegados();
            if (!negados){
                    var preciosEnCero = checarPrecios();
                    if (!preciosEnCero){			
                            tipoCargo = $("#pagolineas option:selected").attr('data-paymmode');
                            Materialize.toast('Comenzando proceso de confirmacion',2000);
                            $('#loadNext').html('<center><img style="width: 12%;" src="../application/assets/img/cargando.gif"></center>');
                            $('#seguridad3Digitos').val('0000');
                            confirmarCot(); 
                            $('#confirmarDocumento2').removeClass('default');
                            $('#confirmarDocumento2').addClass('success');
                            $('#editar2').removeClass('default');
                            $('#editar2').addClass('success');
                            $('#imprimirDocumento2').removeClass('default');
                            $('#imprimirDocumento2').addClass('final');
                    }else{
                            Materialize.toast('Existen precios en cero, favor de verificar.',5000,'red');
                    }
            }
        }
        else{
            swal('¡Alto!','Debe agregar un artículo o definir modo de entrega','info');
        }
    }
});
 function confirmaDocumento(lineaXML){
    var token='';
    if (setTipo === 'ORDVTA') {token= "inicio/confirmarOV";}
    else { token= "inicio/confirmarCotizacion";}
    $.ajax({url: token,method: "POST",dataType: "JSON",
            data: { "lineaXML": lineaXML ,'metodoPago' : $("#pagolineas option:selected").attr('data-paymmode'),'ctaBanco': $("#ctaBanco").val(),'origenVenta': $("#origenV").val(),'encabezadoov': $("#cabeceraOriginal").val() },
            beforeSend: function (xhr) {
                 Materialize.toast('Procesando peticion!', 3000);
            },
            success: function (data){		        	
                    if(data != 'FAIL'){
                        $("#confirmarDocumento").hide();
                        Materialize.toast('Documento Confirmado!', 3000);
                        if(setTipo === 'ORDVTA'){
                           $("#GenerarREM").show();
                           $("#OrdenVentaRem").val(data);
                           $("#rutaReal").append(" > <a style=\"color: white;\">Imprimir Orden De Venta</a>");
                        }
                        else{
                           $("#ImprimirCotizacion").show();
                           $("#ConvertirCot-Ov").show();
                           $("#DocumentoConfirmado").val(data);
                           $("#QuotationId").val(data);
                           $("#rutaReal").append(' > <a style="color: white;">Imprimir Cotización</a>');
                           refreshLines();
                        }	        				        				        
                    }
                    else{
                        Materialize.toast('Intente de nuevo!', 3000);
                    }
            },error: function (data){
                    Materialize.toast('WebService Error!.', 3000);
            }
    });
}
$(document).on('keydown','#num3DigCR',function(){
		$('#num3CRMensaje').html('');
	});
$(document).on('click','#num3CRAceptar',function(){
    $('#loadTarjeta').html('<center><img style="width: 4%;" src="../application/assets/img/cargando.gif"><br>procesando...</center>');                
    var numero = $('#num3DigCR').val();
    if ( numero === '' || numero.length != 4){
            $('#num3CRMensaje').html('Debe ingresar un valor en el campo de codigo de seguridad, o la cantidad de digitos no corresponde.');
            codigoSegValido = '0';
            $("#ctaBanco").val('0000');
    }else{
            $('#num3CRMensaje').html('');
            codigoSegValido = '1';
            $("#ctaBanco").val(numero);
            Materialize.toast('Comenzando proceso de confirmacion',2000);
            confirmarCot(); 
    }
});

//Funcion que actualiza el almacen y selecciona el asignado al cliente
function actualizarAlmacen(alm){
    $("#almacenes > option").each(function() {															
        if (this.value === alm){				    				    
            $( this ).attr("selected","selected");					    
        }
    });
    $("#almacenes").trigger('contentChanged');
    $('#modalLoading').closeModal();
    $('#lean-overlay').remove();
    $('.lean-overlay').remove();
}
function mapeo(valor,arreglo,request){
    return $.map(arreglo,function(itm){
        var posNombre = itm.nameAlias.indexOf(valor.toUpperCase());
        var posArticu = itm.value.indexOf(valor.toUpperCase());
        if (request.term.indexOf('*') < 0){
            if ( (posNombre >= 0) || (posArticu >= 0) ){
                return itm;
            }
        }
        else{
            if ( (posNombre >= 0) ){
                return itm;
            }
        }
    });
}
autocomp_opt = {
    html:true,
    autoFocus : true,
    minLength: 3,
    focus: function(event,ui){
            event.preventDefault();
            $(this).removeClass('ui-menu-item');
    },
    source: function(request,response){
            var buscar = request.term.split('*');
            var itemData = [];
            $(buscar).each(function(index,valor){
                    if ( index == 0){
                        itemData = mapeo(valor,items,request);
                    }else{
                        itemData = mapeo(valor,itemData,request);
                    }
            });
            response(itemData);
    },
    response : function(event,ui){
    if (ui.content.length === 0){
            ui.content.push({label: "No hay Resultados",value: " "});
    }
    },
    select: function (event, ui){	
        //Al seleccionar una opción se asigna la información a los campos del formulario
        //Se obtiene la fila en la que se esta posicionado
        var $tr = $(this).closest('tr'); //Se obtiene el index de la fila
        var myRow = $tr.index() + 1;
        getProductDetail (myRow,ui);                                							    
    }
};

function initResumenTestDiv(){
        var str = $('#FormArticulos').serializeObject();
        $("#GenerarREM").hide();
	$("#DescRemision").hide();
	$("#ImprimirREM").hide();
	$("#ConvertirCot-Ov").hide();
	$("#ImprimirCotizacion").hide();
	$("#claveclteResumen").html($("#claveclte").val());
	$("#desccliente").html($("#cliente").val());
	$("#DocumentIdResumen").html($('#DocumentId2').val());
	$("#monedaResumen").html($("#monedalineas").val());	
	$("#cargodesc").html($("#pagolineas option:selected").attr('data-paymmode'));
	$("#modoentregaResumen").html($("#entregalineas").val());
	$("#direccionclte").html($("#direccion").val());
	$("#vendedor").html($("#responsableventa option:selected").text());
	$('#resumenDivTitle').append(' '+$('#DocumentId2').val());
        $.ajax({url:"inicio/resumenTestLineas",method: "POST",dataType: 'json',data: str,
            async: false,
            beforeSend: function (xhr) {
                Materialize.toast('Agregando partidas al documento!', 3000);                
            },
            success: function (data){
                var lenLineas = Number($("#NumFilas").val());			
                var bodyTable = '';
                var mt = 0;
                for (var i = 1; i < lenLineas+1; i++) {
                    var artiLineas = $('#item'+i).val();
                    var descLineas = $('#descripcion'+i).val();
                    var cantiLineas = $('#cantidad'+i).val();
                    var uniLineas = $('#unidad'+i).val();
                    var sitioLineas = $('#sitio'+i).val();
                    var almacenLineas =$('#almacen'+i).val();
                    var ComentarioLineas = $('#comentariolinea'+i).val();
                            var monto=$('#montoiva'+i).val();
                            mt += Number(monto.substring(1).replace(',',''));
                            bodyTable += '<tr>';
                            bodyTable += '	<td>'+i+'</td>';
                            bodyTable += '	<td>'+artiLineas+'</td>';
                            bodyTable += '	<td>'+descLineas+'</td>';
                            bodyTable += '	<td>'+cantiLineas+'</td>';
                            bodyTable += '	<td style="text-transform:uppercase">'+uniLineas+'</td>';
                            bodyTable += '	<td>'+sitioLineas+'</td>';
                            bodyTable += '	<td>'+almacenLineas+'</td>';
                            bodyTable += '	<td>'+$('#montocargo'+i).val();+'</td>';
                            bodyTable += '	<td>'+$('#montoiva'+i).val();+'</td>';
                            bodyTable += '</tr>';
                            bodyTable += '<tr>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td>Comentarios: '+ComentarioLineas+'</td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '	<td></td>';
                            bodyTable += '</tr>';				
                    };
                    $('#resumenTestTablaLineas tbody').html(bodyTable);
                    $('#totalResumen').html(mt.toFixed(3)).formatCurrency({roundToDecimalPlace:'3'});
                    $('#comentarioCabecera').val($('#comentariosCabecera').val());
                    $('#ordenClte').val($('#OrdenCliente').val());
                    $('#refClte').val($('#documenType').val());
                    $('#comentarioCabeceraCOT').val($('#comentariosCabecera').val());
                    $('#ordenClteCOT').val($('#OrdenCliente').val());
                    $('#refClteCOT').val($('#ReferenciaCliente').val());
                    $("#cuentaCompleto").html($('#DocumentId2').val()+':Cliente '+$("#claveclte").val()+' - '+$("#cliente").val());
                    $('#ATP_Cot').val($('#DocumentId2').val());
                    $('#rutaReal').append(' > <a style="color: white;">Confirmar Documento</a>');
                    confirmaDocumento(data.res);
		},
                error: function (jqXHR,exception){
                    Materialize.toast('WebService Error!:'+catchError(jqXHR,exception), 3000);
                }                
	});
}
function modalLimiteCreditoClose(){$('#modalLimiteCredito').closeModal();}

function cargarEdicion(){
    $('#claveclte').val(cliente);
    $('#claveclte').trigger('keydown');
    setTimeout(function() {
        $('#ui-id-2').click();
    }, 500);      
    $('#claveclte').prop('readonly','1');
}
var primeraVez = true;
function cargarClientes(data){
    clients = [];
    $.map(data, function (item){
        var info = {label: "No hay Resultados",value: " "};
        info["label"] = $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.ClaveCliente);
        info["nombre"] = $.trim(item.Nombre);
        clients.push(info);
    });
    $('#modalLoading').closeModal();
    if((edicion === '1' & primeraVez)){
        cargarEdicion();
    }
    scrollinDropdown();
}
$("#claveclte").focus();

function verMerma(itemId,almacen,localidad){
    $.ajax({
        url:"merma/get-Pictures",type: 'POST',dataType: 'json',data:{itemid:itemId,almacen:almacen,local:localidad},
        success: function (data, textStatus, jqXHR) {
            var html='';
            $('#imageContent').html(html);
            var sh = data.length;     
            if(sh != 0){
                html+= '<div class="right-align" style="width:100%;float: right;"><a class="btn waves-effect waves-light" href="merma/download-zip?itemid='+itemId+'&almacen='+almacen+'&local='+localidad+'">Descargar Zip <i class="fa fa-download"></i></a></div>';           
                $("#downBtn").css("display","block");
            }else{
                 html+= '<div style="width:100%;display: inline-flex;" class="btn red">Sin imagenes</div>';
            }
            $(data).each(function( index ) {
                html+='<div class="col s3">'
                        +'<img onclick="quitarModal($(this))" width="100%" height="100%" style="height: 150px;" id="imageContent'+index+'" class=" responsive" src="'+data[index].RUTA+'" data-comment="'+data[index].COMENTARIOS+'">'
                        +'<a href="merma/download-img?id='+data[index].ID+'" style="position: relative;bottom: 38px;left: -6px;" class="btn-floating btn red"><i class="fa fa-download"></i></a>'
                        +'</div>';
            });
            
            
            $('#ExistenciasSitioClte > tfoot > #imagenes').remove();
            $('#ExistenciasSitioClte > tfoot').append('<tr id="imagenes"><td colspan="6" style="padding:20px 3px 3px 3px;">'+html+'</td></tr>');
            $('#imageContent').html(html);
            $("#imgArt").html(itemId);   
            
            bajar('#modalexistenciasss');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            
        }
    });    
}
function quitarModal(ts){
    var src = ts.attr("src");
    $("#image-hack").attr("src",src);
    $("#picture-comment").html(ts[0].dataset.comment);
    $("#modal1-hack").openModal();
}
function getPriceMerma(itemid,almacen){
    var result=0;
    if(itemid!='' & almacen!='' ){
        $.ajax({
            url:'merma/get-Price-Merma',
            type: 'POST',
            dataType: 'JSON',
            async: false,
            data:{item:itemid,almacen:almacen},
            success: function (d, textStatus, jqXHR) {
                result=d.precio;
               return d.precio; 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return result; 
            }
        });
    }
    return result;    
} 

$('.materialboxed').materialbox();

/**
 * 
 * @param {String} itemid
 * @param {String} cliente
 * @param {String} moneda
 * @param {String} sitio
 * @param {String} almacen
 * @returns {Array|getPriceMermaPreview.result}
 */
function getPriceMermaPreview(itemid,cliente,moneda,sitio,almacen,localidad){ 
    console.log('precio obtenido de merma');
    var result=[];
    if(itemid!='' & almacen!='' ){
       var precioWS= getPrecios(itemid,cliente,moneda,sitio);
        $.ajax({
            url:'merma/get-Price-Merma',
            type: 'POST',dataType: 'JSON',
            async: false,
            data:{item:itemid,almacen:almacen,loc:localidad},
            success: function (d, textStatus, jqXHR) {
                if(Number(d.utilidad)>=0){                    
                    var precioUtilidad=Number(d.costo/(1-d.utilidad));
                    var precioCliente=Number(precioWS.preciocargo);
                    if(precioUtilidad>precioCliente){
                        var precioMerma=precioCliente-(precioCliente*d.utilidad);
                        result.precio=precioCliente-(precioCliente*Number(d.utilidad));
                        result.dif=precioCliente*Number(d.utilidad);
                        result.porcentaje=(precioCliente*Number(d.utilidad)/precioCliente);
                    }
                    else{
                        var precioMerma=Number(d.costo)/(1-(Number(d.utilidad)));
                        result.precio=Number(d.costo)/(1-Number(d.utilidad));
                        result.dif=precioCliente-precioMerma;
                        result.porcentaje=(precioCliente-precioMerma)/precioCliente;
                    }
                    
                }
                else{
                    var precioUtilidad=Number(d.costo)*(1+(Number(d.utilidad)));                    
                    var precioCliente=Number(precioWS.preciocargo);
                    if(precioUtilidad>precioCliente){
                        console.log('utilidad negativa - precio de merma mayor que el de cliente');
                        result.precio=precioCliente-(precioCliente*Number(d.utilidad));
                        result.dif=precioCliente*(-(Number(d.utilidad)));
                        result.porcentaje=(precioCliente*Number(d.utilidad)/precioCliente);
                        console.log("Precio Cliente: "+precioCliente+' < '+precioUtilidad+'-'+result.precio+' - '+result.dif+' - '+result.porcentaje);
                    }
                    else{
                        console.log('utilidad negativa - precio de merma menor que cliente');                        
                        result.precio=precioUtilidad;
                        result.dif=precioCliente-precioUtilidad;
                        result.porcentaje=(precioCliente-precioUtilidad)/precioCliente;    
                        console.log("Precio Cliente: "+precioCliente+' < '+precioUtilidad+'-'+result.precio+' - '+result.dif+' - '+result.porcentaje);
                    }                    
                }
                console.log(d);
               return result; 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return result; 
            }
        });
    }
    return result;    
}
/**
 * 
 * @param {String} itemid
 * @param {String} cliente
 * @param {String} moneda
 * @param {String} sitio
 * @returns {Array|d}
 */
function getPrecios(itemid,cliente,moneda,sitio){
    var result=[];
    if(itemid!='' ){
        $.ajax({
            url:'inicio/precios',
            type: 'POST',dataType: 'JSON',
            async: false,
            data:{item:itemid,cliente:cliente,moneda:moneda,qty:'1',cargo:0.00,sitio:sitio,almacen:sitio+'CONS',punitario:0,localidad:'GRAL'},
            success: function (d, textStatus, jqXHR) {
                return result=d; 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return result; 
            }
        });
    }
    return result;
}
function bajar(element){
    $(element).animate({scrollTop:$(element)[0].scrollHeight}, 800);
}
function setDomicilios(payMode){
    if(payMode==='DOMICILIO'){
        if($("#paytermArt").val()==='CONTADO'){
            $("#paytermArt").val('1D');
            setPayMode('1D');
        }
    }
}