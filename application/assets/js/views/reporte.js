/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function eliminarPermiso(id){
    if(id){
        $.ajax({
            url: "reporte/delete-Roll",type:"post",data: {'id':id},dataType: "json",
            beforeSend: function (xhr) {
                
            },
            success: function (data, textStatus, jqXHR) {
               if(data==1){
                   refreshTableUsuarios();
               }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                
            }
        });
    }
}
function agregarPermiso(){
    var idRoll=$("#permisoAsign > select[name=idRoll]>option:selected").val();
    var idUsr=$("#permisoAsign > select[name=idUsr]>option:selected").val();
    if(idRoll!='' && idUsr!=''){        
         $.ajax({
            url: "reporte/add-Roll",type:"post",data: {idUsr:idUsr,idRoll:idRoll},dataType: "json",
            beforeSend: function (xhr) {
                $('#loaderNewRoll').html('<img style="width: 2em;" src="../application/assets/img/cargando.gif">');
                
            },
            success: function (data, textStatus, jqXHR) {
                $('#loaderNewRoll').html('<div class="alert alert-success"><strong>Bien!</strong> Permiso Asignado.</div>');
                if(data==1){
                   refreshTableUsuarios();
               }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#loaderNewRoll').html('<div class="alert alert-danger"><strong>Error!</strong>'+jqXHR.statusText+'</div>');
            }
        });
    }
}
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
function refreshTableUsuarios() {
    var dataTableComisionistas = [];
    var json = getJsonFromUrl("reporte/get-Usuario-Asigned", {idRow: ""}).responseJSON;
    $.each(json, function (i, v) {
        dataTableComisionistas.push(['<i id="icon' + v.id + '" onclick="eliminarPermiso(' + v.id + ');" style="color:red;" class="fa fa-trash"></i>   '+v.Usuario,v.Roll]);
    });
    $("#permisosTable").DataTable({
        destroy: true,
        order: [[0, "desc"]],
        data: dataTableComisionistas,
        columns: [
            {title: "Usuario"},
            {title: "Permiso"}
        ],
        columnDefs: [
            {targets: '_all', "searchable": true}
        ]
    });
}
function getKardexFilter(){
    var s=$("#tblKardex2").serialize();
     $.ajax({ url : "reporte/filtrertbl",type : "post",async: true,
            data : s,
            beforeSend: function (xhr) {
                $("#tblContent").html('<tr><td colspan="7"><center><img src="../application/assets/img/cargando.gif"><br>procesando...</center></td></tr>');
            },
            success : function(res){
                $("#tblContent").html(res);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#tblContent").html('<tr><td colspan="10"><center>ha ocurrido un error '+textStatus+'</center></td></tr>');
            }
        });
        $.ajax({ url : "reporte/usografica",type : "post",dataType: 'JSON',
            data : s,
            beforeSend: function (xhr) {
                $("#chart_div").html('');
            },
            success : function(res){
                var data=res;
                var d=data.length;
                var arr=[];
                for (var i=0;i<d;i++){
                   arr.push([data[i].nombre,parseInt(data[i].conteo)]);
                }
                var dat = new google.visualization.DataTable();
                dat.addColumn('string', 'Nombre');
                dat.addColumn('number', 'Movimientos');
                dat.addRows(arr);
                var options = {'title':'ESTADISTICA DE USO INAX BARRAS',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
                var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                chart.draw(dat, options);
                var dat = new google.visualization.DataTable();
                dat.addColumn('string', 'Nombre');
                dat.addColumn('number', 'Movimientos');
                dat.addRows(arr);
                var options = {'title':'ESTADISTICA DE USO INAX PASTEL',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
                var chart = new google.visualization.PieChart(document.getElementById('chart_div2'));
                chart.draw(dat, options);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#chart_div").html('<tr><td colspan="10"><center>ha ocurrido un error '+textStatus+'</center></td></tr>');
            }
        });
}

function setNegadosFilter(){
    var s=$("#negadostbl").serialize();
    $.ajax({ url : "reporte/negadostbl",type : "post",
            data : s,
            beforeSend: function (xhr) {
               $("#tblNegados").html('<tr><td colspan="10"><center><img src="../application/assets/img/cargando.gif"><br>procesando...</center></td></tr>');
            },
            success : function(res){
             $("#tblNegados").html(res);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#tblNegados").html('<tr><td colspan="10"><center>ha ocurrido un error '+textStatus+'</center></td></tr>');
            }
        });
}

function setNegadosFilter2CSV(){
    var s=$("#negadostbl").serialize();
     $.ajax({ url : "reporte/negadostbl2csv",type : "post",
            dataType: 'application/vnd.ms-excel',
            data : s,
            accepts: {
                mycustomtype: 'application/vnd.ms-excel'
            },
            beforeSend: function (xhr) {
            },
            success : function(res){
             //$("#tblNegados").html(res);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#tblNegados").html('<tr><td colspan="10"><center>ha ocurrido un error '+textStatus+'</center></td></tr>');
            }
        });
}
function actualizarSemaforo(){
    var con=confirm('¿Esta seguro de actualizar el semaforo?');
    if(con){
     var s=$("#semaforo").serialize();
     $.ajax({ url : "reporte/semaforo",type : "post",
            data : s,
            beforeSend: function (xhr) {
                $("#procesoSemaforo").html('<img src="../application/assets/img/cargando.gif" style="width: 45px;">');
            },
            success : function(res){
                if(res==1){
                    $("#procesoSemaforo").html('<img src="../application/assets/img/ok.png" style="width: 45px;">');
                    setTimeout(function (){$("#procesoSemaforo").html('');},10000);
                }
                else{
                    $("#procesoSemaforo").html('<img src="../application/assets/img/error.png" style="width: 45px;"> no se realizaron cambios');
                    setTimeout(function (){$("#procesoSemaforo").html('');},10000);
                }         
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $("#procesoSemaforo").html('<img src="../application/assets/img/error.png" style="width: 45px;"> error de comunicación '+textStatus);
            }
        });   
    }
}
 function drawChart() {
    var dat = new google.visualization.DataTable();
    dat.addColumn('string', 'Nombre');
    dat.addColumn('number', 'Movimientos');
    dat.addRows(data23);
    var options = {'title':'ESTADISTICA DE USO INAX BARRAS',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
    chart.draw(dat, options);
}
function drawChart2() {
    var dat = new google.visualization.DataTable();
    dat.addColumn('string', 'Nombre'); 
    dat.addColumn('number', 'Movimientos');
    dat.addRows(data23);
    var options = {'title':'ESTADISTICA DE USO INAX PASTEL',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
    var chart = new google.visualization.PieChart(document.getElementById('chart_div2'));
    chart.draw(dat, options);
}
$('#tblReport3').DataTable({"order": [[ 1, "desc" ]]});
$('#tblReport4').DataTable({"order": [[ 1, "desc" ]]});
function filterTbl3(){
    var f1=$("#f1tbl3").val();
    var f2=$("#f2tbl3").val();
    $("#periodoTbl3").html(f1+" al "+f2);
    $.ajax({
        url: "fallasinax/filter-fecha-list3",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
        beforeSend: function (xhr) {
            $('#tblReport3').empty();
           $('#tblReport3').DataTable().destroy();
        },
        success: function (data) {
            $('#tblReport3').DataTable( {
                'destroy':true,
                "order": [[ 1, "desc" ]],
                data: data,
                columns: [
                    {title: "USUARIO"},
                    {title: "CANTIDAD"}     
                ]
            } );
        },
        error: function (x){
            $('#tblReport3').DataTable();
        }
    });
}
function filterTbl4(){
    var f1=$("#f1tbl4").val();
    var f2=$("#f2tbl4").val();
    $.ajax({
        url: "fallasinax/filter-fecha-list4",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
        beforeSend: function (xhr) {
            $('#tblReport4').empty();
           $('#tblReport4').DataTable().destroy();
        },
        success: function (data) {
            var arrM=[];
            $.each(data,function (i,v){
                if(arrM[v[0]]){
                    arrM[v[0]][1]=Number(arrM[v[0]][1])+Number(v[1]);
                }
                else{
                    arrM[v[0]]=v;
                }
            });
            data=[];
            for(var i in arrM){
                data.push([arrM[i][0],arrM[i][1]]);
            }
            $('#tblReport4').DataTable( {
                'destroy':true,
                "order": [[ 1, "desc" ]],
                data: data,
                columns: [
                    {title: "SUCURSAL"},
                    {title: "CANTIDAD"}     
                ]
            } );
        },
        error: function (x){
            $('#tblReport4').DataTable();
        }
    });
}
function filterTbl5(){ 
    var f1=$("#f1tbl5").val();
    var f2=$("#f2tbl5").val();
    $.ajax({
        url: "fallasinax/filter-fecha-list5",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
        beforeSend: function (xhr) {
            var img='<center><img src="../application/assets/img/gif-load.gif" style="width: 6em;"></center>';
            $('#chart_div').html(img);
        },
        success: function (data) {
            var dat = new google.visualization.DataTable();
            dat.addColumn('string', 'Nombre'); 
            dat.addColumn('number', 'Movimientos');
            dat.addRows(data);
            var options = {'title':'Facturas Realizadas General',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
            var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
            chart.draw(dat, options);
        },
        error: function (x){
        }
    });
}
function filterTbl6(){ 
    var f1=$("#f1tbl6").val();
    var f2=$("#f2tbl6").val();
    $.ajax({
        url: "fallasinax/filter-fecha-list6",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
        beforeSend: function (xhr) {
            var img='<center><img src="../application/assets/img/gif-load.gif" style="width: 6em;"></center>';
            $('#chart_div2').html(img);
        },
        success: function (data) {
            var dat = new google.visualization.DataTable();
            dat.addColumn('string', 'Nombre'); 
            dat.addColumn('number', 'Movimientos');
            dat.addRows(data);
            var options = {'title':'Facturas Realizadas Sucursal desde InAX',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
            var chart2 = new google.visualization.AreaChart(document.getElementById('chart_div2'));
            chart2.draw(dat, options);
        },
        error: function (x){
        }
    });
}
function filterTbl7(){ 
    var f1=$("#f1tbl7").val();
    var f2=$("#f2tbl7").val();
    $.ajax({
        url: "fallasinax/filter-fecha-list7",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
        beforeSend: function (xhr) {
            var img='<center><img src="../application/assets/img/gif-load.gif" style="width: 6em;"></center>';
            $('#chart_div3').html(img);
        },
        success: function (data) {
            var dat = new google.visualization.DataTable();
            dat.addColumn('string', 'Nombre'); 
            dat.addColumn('number', 'Movimientos');
            dat.addRows(data);
            var options = {'title':'Facturas Realizadas Sucursal desde Dynamics',scales: {xAxes: [{ticks: {autoSkip: false}}]},'height':300};
            var chart2 = new google.visualization.AreaChart(document.getElementById('chart_div3'));
            chart2.draw(dat, options);
        },
        error: function (x){
        }
    });
}