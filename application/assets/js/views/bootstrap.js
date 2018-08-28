/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function verifica(){
    $.ajax({ url : "reporte/semaforosts",
        success : function(res){
            var s=$('#menuSuperior').attr('data-color');
            if(s!=''){
                if(res!=s){
                   $('#semaforoModal').modal({keyboard: false, backdrop: 'static'});
                }
            }
            $('#menuSuperior').css('background-color','#'+res);
            $('#menuSuperior').attr('data-color',res);
            $('#semaforosts2').css('color','#'+res);
            $('#sem').css('background','#0f579b');
        }
    });
}
function verifica_sucursal(){ 
    $('#menuSuperior').removeClass('white-text'); 
}
$(document).hotkey('alt+v', function(e){ 
    $('#btn-venta').click(); }); 
$(document).hotkey('alt+c', function(e){ 
    $('#btn-cotiza').click(); }); 
$(document).hotkey('alt+q', function(e){ 
    $('#generarOV').submit(); });     
$(document).hotkey('alt+g', function(e){ 
    $("#graficas").get(0).click(); }); 
$(document).hotkey('alt+t', function(e){ 
    abrirEnPestana(""); }); 
$(document).hotkey('alt+n', function(e){ 
    $('#newCustomer').click(); });
function bread3() { 
    docId = $("#DocumentId").val(); 
    clte = $("#cliente").val(); 
    docType = $("#DocumentType2").val(); 
    $('#editarCotForm #documentType').val(docType); 
    $('#editarCotForm #DocumentId').val(docId); 
    $('#editarCotForm #cliente').val(clte); 
    $('#editarCotForm #editar').val('1'); 
    $('#editarCotForm').submit(); 
}