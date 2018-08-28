/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$('#tblReport').DataTable({"order": [[ 2, "desc" ]]});
$('#tblReport2').DataTable({"order": [[ 1, "desc" ]]});
$('#tblReport3').DataTable({"order": [[ 1, "desc" ]]});
$('#tblReport4').DataTable({"order": [[ 1, "desc" ]]});
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
 
function filter(){
    var f1=$("#f1").val(); 
    var f2=$("#f2").val();
    $("#pdfLink").attr('href','fallasinax/fallas-Pdf?f1='+f1+'&f2='+f2);
    $("#pdfLinkUso").attr('href','fallasinax/uso-Inax?f1='+f1+'&f2='+f2);
    $("#periodo").html(f1+" al "+f2); 
     var anterior=$("#totalError").text();
    $.ajax({url: "fallasinax/filter-time",dataType: 'json',type: 'POST',data:{f1:f1,f2:f2},
        beforeSend: function (xhr) {
            var img='<img src="../application/assets/img/cargando.gif" style="width: 0.5em;">';
            $("#totalPeticiones").html(img);
            $("#totalError").html(img);
            $("#totalProcentaje").html(img);
            $("#totalCompletado").html(img);
        },
        success: function (data, textStatus, jqXHR) {
           var porcentaje=0;          
           var totalT=0;
            if(data.total>0){
               porcentaje=(data.totalError*100)/data.total;
               totalT=100-porcentaje;
           }           
           if(Number(anterior) !== Number(data.totalError)){
                var audio = document.getElementById("audio");
                //audio.play();
            }
            $("#totalPeticiones").html(Number(data.total).formatMoney(0, '.', ','));
            $("#totalError").html(Number(data.totalError).formatMoney(0, '.', ','));
            $("#totalProcentaje").html(porcentaje.toFixed(2)+'%');
            $("#totalCompletado").html(totalT.toFixed(2)+'%');
            $("#ePrecios").html(Number(data.totalPrecio).formatMoney(0,'.',','));
            var errorP=(data.totalPrecio*100)/data.totalWSPrecioComplete;
            $("#ePreciosPor").html(errorP.toFixed(2)+'%');
            $("#eConfirmaciones").html(Number(data.totalConfirmacion).formatMoney(0,'.',','));
            errorP=(data.totalConfirmacion*100)/data.totalWSConfirmacionComplete;
            $("#eConfirmacionesPor").html(errorP.toFixed(2)+'%');
            $("#eRemisiones").html(Number(data.totalRemision).formatMoney(0,'.',','));
            errorP=(data.totalCotOv*100)/data.totalWSRemisionComplete;
            $("#eRemisionesPor").html(errorP.toFixed(2)+'%');
            $("#eCotToOv").html(data.totalCotOv);
            errorP=(data.totalCotOv*100)/data.totalWScotovComplete;
            $("#eCotToOvPor").html(Number(errorP).formatMoney(2,'.',',')+'%');
            errorP=(data.efact*100)/data.fact;
            $("#eFacturasPor").html(Number(errorP).formatMoney(2,'.',',')+'%');
            $("#eFacturas").html(data.efact);
            $("#eNewClient").html(0);
            $("#ePdf").html(0);
            $("#cPrecios").html(Number(data.totalWSPrecioComplete).formatMoney(0,'.',','));
            $("#cFacturas").html(Number(data.fact).formatMoney(0,'.',','));
            $("#cConfirmaciones").html(Number(data.totalWSConfirmacionComplete).formatMoney(0,'.',','));
            $("#cRemisiones").html(Number(data.totalWSRemisionComplete).formatMoney(0,'.',','));
            $("#cCotToOv").html(Number(data.totalWScotovComplete).formatMoney(0,'.',','));
            $("#cNewClient").html(Number(data.newClient).formatMoney(0,'.',','));
            $("#cPdf").html(Number(data.pdf).formatMoney(0,'.',','));
        },
        error: function (jqXHR, textStatus, errorThrown) {
            
        }
    });
}
function filterTbl(){
    var f1=$("#f1tbl").val();
    var f2=$("#f2tbl").val();
    $("#periodoTbl0").html(f1+" al "+f2);
    $.ajax({
            url: "fallasinax/filter-fecha-list",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
            beforeSend: function (xhr) {
                $('#tblReport').empty();
               $('#tblReport').DataTable().destroy();
            },
            success: function (data) {
                $('#tblReport').DataTable( {
                    pageLength: 5,
                    destroy:true,
                    "order": [[ 2, "desc" ]],
                    data: data,
                    columns: [
                        {title: "#"},
                        {title: "USUARIO"},
                        {title: "FECHA"},
                        {title: "TIPO DE MOV"},
                        {title: "IP"},
                        {title: "ESTATUS"}
                    ]
                } );
            },
            error: function (x){
                $('#tblReport').DataTable();
            }
        });
}
function filterTbl2(){
    var f1=$("#f1tbl2").val();
    var f2=$("#f2tbl2").val();
    $("#periodoTbl2").html(f1+" al "+f2);
    $.ajax({
        url: "fallasinax/filter-fecha-list2",type: "POST",cache: false,dataType: 'json',data: {'f1': f1,'f2':f2},
        beforeSend: function (xhr) {
            $('#tblReport2').empty();
           $('#tblReport2').DataTable().destroy();
        },
        success: function (data) {
            $('#tblReport2').DataTable( {
                'destroy':true,
                "order": [[ 1, "desc" ]],
                pageLength: 5,
                data: data,
                columns: [
                    {title: "USUARIO"},
                    {title: "CONTEO"}     
                ]
            } );
        },
        error: function (x){
            $('#tblReport2').DataTable();
        }
    });
}
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
