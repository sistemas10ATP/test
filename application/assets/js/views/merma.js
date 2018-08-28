/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('#rutaReal').remove();
$('#menuSuperior').remove();

$("#searchClave").autocomplete({
      source: function( request, response ) {
        $.ajax( {
          url: "merma/search-ventamerma",
          data: {
            term: request.term
          },
          success: function( data ) {
            response( data );
          }
        } );
      },
      minLength: 2,
      select: function( event, ui ) {
        getPictures( ui.item.ITEMID , ui.item.ALMACEN , ui.item.LOC);
        getItemData(ui.item.ITEMID , ui.item.ALMACEN);
        $("#containerImages").show();
      }
    } ).autocomplete( "instance" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<div><b>" + item.ITEMID + "</b> - "+item.ALMACEN+"</div>" )
        .appendTo( ul );
    };;

function actualizar(){
    if($("[name=utilidad]").val()==""){
        $("#loading").html('<b style="color:red;">** Favor de seleccionar un porcentaje de utilidad</b>'); 
    }
    else{
        if(confirm("¿Esta seguro de actualizar la seleccion actual con un porcentaje de "+$("[name=utilidad] option:selected").html()+" ?")){
            $.ajax({
                url:'merma/set-Family-Utility',type: 'POST',
                data:{familia:$("[name='familia']").val(),almacen:$("#almacen").val(),utilidad:$("[name=utilidad]").val()}, 
               beforeSend: function (xhr) {
                    $("#loading").html('<center><img src="../application/assets/img/cargando.gif" width="16%"><br>procesando...');
                },
                success: function (data, textStatus, jqXHR) {
                    filtrar();
                    $("#loading").html('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                   $("#loading").html('Error al procesar la petición'); 
                }
            });
        } 
    }    
}
function filtrar(){
//    console.log($("#almacen").val());
    if($("[name='familia']").val()==""){
        $("#loading").html('<b style="color:red;">** Favor de seleccionar un porcentaje de utilidad</b>'); 
    }
    else{
        $.ajax({
                url:'merma/get-filter-family',
                type: 'POST',
                data:{familia:$("[name='familia']").val(),almacen:$("#almacen").val(),sinconf:$("#sinconf").is(":checked")},
                beforeSend: function (xhr) {
                    $("#loading").html('<center><img src="../application/assets/img/cargando.gif" width="16%"><br>procesando...');
                },
                success: function (d, textStatus, jqXHR) {
                    drawDatatable(d);
                    $("#loading").html('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                   $("#loading").html('Error al procesar la petición'); 
                }
        });
    }
}

function drawDatatable(data){
    
    var tbody = $("#tableFilter tbody"),
        table = $("#tableFilter"),
        tr = "";

    checkTableisDataTable(table);
    tbody.empty();
    for(var d in data){
        var style = "";
        if(data[d].IMAGES){
           style = '<span class="label label-success">Contiene imagenes</span>'
        }
        
        tr = "<tr>"
                +"<td><p><a href='#' data-toggle='modal' data-target='#contenedorUploadPicture'  onclick='getItemData(\""+data[d].CODIGO+"\",\""+data[d].ALMACEN+"\",\""+data[d].LOC+"\")'>"+data[d].CODIGO+"</a></p> "+style+"</td>"
                +"<td>"+data[d].NOMBRE+"</td>"
                +"<td>"+Number(data[d].STOCK).toFixed(2)+"</td>"
                +"<td>"+data[d].ALMACEN+"</td>"
                +"<td>"+data[d].LOC+"</td>"
                +"<td id='costo-td-"+data[d].CODIGO+"' data-costo='"+data[d].COSTO+"'>$ "+data[d].COSTO+"</td>"
                 +"<td id='pv-td-"+data[d].CODIGO+data[d].ALMACEN+data[d].LOC+"' >$ "+data[d].PRECIO+"</td>"
                +"<td onclick='changueUtil($(this))' data-codigo='"+data[d].CODIGO+"' data-almacen='"+data[d].ALMACEN+"'  data-local='"+data[d].LOC+"'>"+data[d].UTILIDAD+"</td>"
            +"</tr>";
        tbody.append(tr);
    }  
    table.DataTable();
}

function changueUtil(th){
    
    var inter = th.html();
    var codigo = th[0].dataset.codigo;
    var almacen = th[0].dataset.almacen;
    var local = th[0].dataset.local;
    
    th.empty()
    th.attr('onclick','');
    th.append('<select id="selectCustom"  class="form-control">'
                        +'<option value="">Seleccione...</option>'
                        +'<option value="0.05"> 5 %</option>'
                        +'<option value="0.02"> 2 %</option>'
                        +'<option value="0.0"> 0 %</option>'
                        +'<option value="-0.05">-5 %</option>'
                        +'<option value="-0.10">-10 %</option>'
                        +'<option value="-0.15">-15 %</option>'
                        +'<option value="-0.20">-20 %</option>'
                        +'<option value="-0.30">-30 %</option>'
                        +'<option value="-0.40">-40 %</option>'
                        +'<option value="-0.50">-50 %</option>'
                    +'</select>');
    $("#selectCustom").focus();
    $("#selectCustom").val(inter);
    $("#selectCustom").change(function(){
        th.attr('onclick','changueUtil($(this))');
        th.html($("#selectCustom").val());
        updateRow($(this).val(),codigo,almacen,local);
    });
    
    $("#selectCustom").on('blur', function() {
        th.attr('onclick','changueUtil($(this))');
        th.html(inter);
    });
}

function updateRow(utilidad,codigo,almacen,local){
    $.ajax({
            url:'merma/update-row',
            type: 'POST',
            data:{utilidad:utilidad,codigo:codigo,almacen:almacen,local:local},
            beforeSend: function (xhr) {
                $("#loading").html('<center><img src="../application/assets/img/cargando.gif" width="16%"><br>procesando...');
            },
            success: function (d, textStatus, jqXHR) {
                var costo = Number($("#costo-td-"+d["codigo"])[0].dataset.costo);
                var utilidad = Number(d["utilidad"]);
                $("#pv-td-"+d["codigo"]+d["almacen"]+d["local"]).html("$ "+(costo/(1-utilidad)).toFixed(2));
                $("#loading").html('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
               $("#loading").html('Error al procesar la petición'); 
            }
    });
    
}

function uploadImage(codigo,almacen,local){
    getItemData(codigo,almacen,local);
}

function getPictures(itemid,almacen,local){
    var button = "<a href='merma/download-zip?itemid="+itemid+"&almacen="+almacen+"&local="+local+"'><button class='btn btn-success' style='width: 100%;'>Descargar ZIP <i class='fa fa-download'></i></button></a>";
    $("#containerButtonZip").html(button);
    $.ajax({
            url:'merma/get-pictures',
            type: 'POST',
            data:{itemid:itemid,almacen:almacen,local:local},
            beforeSend: function (xhr) {
                $("#loading").html('<center><img src="../application/assets/img/cargando.gif" width="16%"><br>procesando...');
            },
            success: function (d, textStatus, jqXHR) {
                drawGallery(d);
                $("#loading").html('');
               
            },
            error: function (jqXHR, textStatus, errorThrown) {
               $("#loading").html('Error al procesar la petición'); 
            }
    });
}

function deletePicture(id,nameFile){
    $.ajax({
            url:'merma/delet-picture',
            type: 'POST',
            data:{id:id,nameFile:nameFile},
            beforeSend: function (xhr) {
                $("#loading").html('<center><img src="../application/assets/img/cargando.gif" width="16%"><br>procesando...');
            },
            success: function (d, textStatus, jqXHR) {
//                drawGallery(d);
                $("#container-img-"+id).remove();
                $("#loading").html('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
               $("#loading").html('Error al procesar la petición'); 
            }
    });
}

function getItemData(itemid,almacen,local){
    $.post("merma/get-item-data",{itemid:itemid},function(data){
       $("#titleItem").html(itemid+" - "+almacen+" - "+local);
       $("#descriptionItem").html(data.SEARCHNAME);
       $("#inputItemId").val(itemid);
       $("#inputAlmacen").val(almacen);
       $("#inputLocal").val(local);
       getPictures(itemid,almacen,local);
       
    });
}

function drawGallery(data){
    var img = "",modal="";
    $("#gallery").empty();
    $("#modalgallery").empty();
    for(var d in data){
        img = "<div class='col-md-3 col-sm-6 co-xs-12 gal-item' id='container-img-"+data[d].ID+"'>"
                +"<div class='box'>"
                +"<button class='btn-add-comment'  data-id='"+data[d].ID+"' data-comment='"+data[d].COMENTARIOS+"' ><i class='fa fa-comment-o'></i></button>"
                +"<button class='btn-erase-gallery' onclick='deletePicture("+data[d].ID+",\""+data[d].RUTA+"\")'><i class='fa fa-trash-o'></i></button>"
                +"<a class='btn-download-img' href='merma/download-img?id="+data[d].ID+"' target='_blank'><button  ><i class='fa fa-download'></i></button></a>"
                +"<a href='#' data-toggle='modal' data-target='#modal-pictures-"+d+"' id='link-modal-"+d+"'>"
                    +"<img src='"+data[d].RUTA+"'/>"
                +"</a>"
         +"</div></div></div>";
         modal =  "<div class='col-md-4 col-sm-6 co-xs-12 gal-item' id='container-img-"+data[d].ID+"' ><div class='box'>"+'<div style="z-index: 1080 !important;" class="modal fade" id="modal-pictures-'+d+'" tabindex="-1" role="dialog">'
                    +'<div class="modal-dialog" style="box-shadow: 0 0 20px 20px #000;" >'
                        +'<div class="modal-content">'
                            +'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>'
                        +'</div>'
                   +'<div class="modal-body">'
                        +"<img src='"+data[d].RUTA+"'/>"
                    +'</div>'
                    +'<div class="modal-footer" style="background: #eee;text-align: left;">'
                        +data[d].COMENTARIOS
                    +'</div>'
                +'</div></div></div>';
               
        $("#gallery").append(img);
        $("#modalgallery").append(modal);
        
        
        
    }
//    console.log(data);
}

$(document.body).on('click', '.btn-add-comment', function(event) {
     $("#mi-modal").modal('show');
     id = $(this)[0].dataset.id; 
     var comment = $(this)[0].dataset.comment; 
     $("#textarea-comment").val(comment);
});

$('#mi-modal').on('shown.bs.modal', function () {
    $('#textarea-comment').focus();
});  

$(document.body).on('click', '#modal-btn-guardar', function (event) {
    event.preventDefault();
    $.post('merma/update-picture', {id: id, comment: $("#textarea-comment").val()}, function (d) {
        drawGallery(d);
        $("#mi-modal").modal('hide');
        $("#textarea-comment").val("");
    });
});

$(document.body).on('click', '#sinconf', function (event) {
    //event.preventDefault();
    filtrar();
});

//sinconf

function addCommentPicture(id){
     $("#mi-modal").modal('show');
     
     $("#modal-btn-guardar").click(function(){
          $.ajax({
            url:'merma/update-picture',
            type: 'POST',
            data:{id:id,comment:$("#textarea-comment").val()},
            beforeSend: function (xhr) {
                $("#loading").html('<center><img src="../application/assets/img/cargando.gif" width="16%"><br>procesando...');
            },
            success: function (d, textStatus, jqXHR) {
                drawGallery(d);
                $("#mi-modal").modal('hide');
                $("#textarea-comment").val("");
            },
            error: function (jqXHR, textStatus, errorThrown) {
               $("#loading").html('Error al procesar la petición'); 
            }
         });
     });
     
}

function checkTableisDataTable(table){
    if ($.fn.dataTable.isDataTable(table)) {
        table.DataTable().destroy();
    }
}

Dropzone.autoDiscover = false;
// or disable for specific dropzone:
// Dropzone.options.myDropzone = false;

$(function() {
  // Now that the DOM is fully loaded, create the dropzone, and setup the
  // event listeners
  var myDropzone = new Dropzone(".dropzone",{
       maxFiles: 6,
       acceptedFiles: ".png,.jpg,.jpeg"
  });
  

  myDropzone.on("complete", function(file) {
    /* Maybe display some more file information on your page */
    myDropzone.removeFile(file);
    getPictures($("#inputItemId").val(),$("#inputAlmacen").val(),$("#inputLocal").val());
  });
})

