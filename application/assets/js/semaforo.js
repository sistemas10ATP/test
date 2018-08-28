/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);
var q ="select * from dbo.semaforo where sel='selected'";
const sql = require('mssql');
server.listen(4000, function() {
    console.log('Servidor corriendo en http://localhost:4000');
});

/*********************mssql******************************************************************************/
var config = {  
    user: 'sa',  
    password: 'avanceytec',  
    server: 'SQL03\\DB03',
    database: 'AXTEST'
    // When you connect to Azure SQL Database, you need these next options.  
   //options: {encrypt: true, database: 'AXTEST'}  
};

io.sockets.on('connection',function(socket){
    console.log("mensage desde server");
  io.emit('semaforo',function (){
      getData();
  });    
});



function getData(){
    try {        
        sql.connect(config)
            .then(function () {
                // Function to retrieve all the data - Start
                var qs=new sqlInstance.Request()
                    .query("select * from interna2.interna.dbo.semaforo where sel='selected'")
                    .then(function (dbData) {
                        if (dbData == null || dbData.length === 0)
                            return;
                        console.log('All the courses');
                        console.log(dbData);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                    qs.end();
                // Function to retrieve all the data - End
            }).catch(function (error) {
                console.log(error);
            });
        sql.close()
    } catch (error) {
        console.log(error);
    }
}

function result(q){    
  const pool1 = new sql.ConnectionPool(config, err => {        
        pool1.request().query(q,(err, result) => {
        if (err) {
          console.log(err)
          t = [];          
        } else {
          t = result;
        }           
      })
  })
  pool1.on('error', err => { });
}

io.on('chido',function(res){
  console.log('yes');
})

/*setInterval(function(){ 
              result();
              io.emit('pppp',{'r':'r'});                            
              io.emit('actmon', {t:t})              
            }, 5000);*/