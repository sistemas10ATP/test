/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function send(){
    var dataForm=$("#login").serialize();
    $.ajax({
        //url:"http://intranet.enlaceatp.net/ldap/",
        url:"http://intra/ldap/",
        data: dataForm,
        dataType: 'json',
        type: 'POST',
        beforeSend: function (xhr) {
            var img='<img src="../application/assets/img/cargando.gif" style="width: 5em;">';
            $('#result').html(img);
        },
        success: function (data, textStatus, jqXHR) {
            $('#result').html("");
            if(data.result==="ok"){
                localStorage.setItem("session", data);
            }
            console.log(localStorage.getItem("session"));
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#result').html("");
        }
    });
}