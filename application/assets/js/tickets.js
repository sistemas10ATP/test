function ticket() {

    html2canvas(window.document.body, {
        onrendered: function (canvas) {
            $("#base64").val(canvas.toDataURL());
            $("#imgTiket").html(canvas);
        }
    });

    setTimeout(function () {
        $('#modal-ticket').openModal();
    }, 1000);

}
function ticket2() {

    html2canvas(window.document.body, {
        onrendered: function (canvas) {
            $("#base64").val(canvas.toDataURL());
            $("#imgTiket").html(canvas);
        }
    });
    setTimeout(function () {
        $('#modal-ticket').modal();
    }, 1000);
}

function mandarTicket() {
    $.post("inicio/ticket", $("#formSendTicket").serialize(),function (d) {
                if (d.message == "success") {
                     $("#formSendTicket")[0].reset();
                    Materialize.toast("Se envio el ticket a su correo <hr/> para darle segumiento puede dar click <a href='http://tickets/WorkOrder.do?woMode=viewWO&woID=" + d.woid[0] + "' target='_blank'> aqui </a>", 10000);
                }

                if (d.message == "error") {
                    Materialize.toast("Error al generar el tiket", 4000);
                }

                
                if(d.message == "error_session"){
                    
                }
            })
}

function readFile() {
  
  var files = document.getElementById('inp').files;
  if (files.length > 0) {
    var fil = getBase64(files[0]);
    
  }
}
