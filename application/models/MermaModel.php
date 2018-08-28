<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reporteModel
 *
 * @author sistemas10    
 */

class Application_Model_MermaModel {
    public $db;
    public $_adapter;
    
    public function __construct(array $options = null){
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->db = new Application_Model_UserinfoMapper();
        $this->_adapter = $this->db->getAdapter();
        $query=$this->_adapter->query(ANSI_NULLS);
        $query=$this->_adapter->query(ANSI_WARNINGS);
        $query->execute();
        return $this->_adapter;
    }
    
    /**
     * 
     */
    public function getAlmacenesMerma(){
       $query = $this->_adapter->prepare(GET_ALMACENES_MERMA);
       $query->execute();
       $result=$query->fetchAll();
       return $result ;
    }
    public function exist($itemId, $almacen,$porcentaje,$local) {
     
        $data = $this->getVentamermaData($itemId, $almacen,$local);
        
        if(!empty($data)){
            $sql="UPDATE ".INTERNA.".dbo.VENTAMERMA SET porcentaje=? where itemid= ? and almacen=? and loc = ?;";
            $query2 = $this->_adapter->prepare($sql);
            $query2->bindParam(1,$porcentaje);
            $query2->bindParam(2,$itemId);
            $query2->bindParam(3,$almacen);
            $query2->bindParam(4,$local);
            $id = $data["ID"];
            $data = $query2->execute();
        }
        else{
            $sql="INSERT INTO ".INTERNA.".dbo.VENTAMERMA (itemid,porcentaje,almacen,loc) VALUES (?,?,?,?);";
            $query2 = $this->_adapter->prepare($sql);
            $query2->bindParam(1,$itemId);
            $query2->bindParam(2,$porcentaje);
            $query2->bindParam(3,$almacen);
            $query2->bindParam(4,$local);
            $query2->execute();
            $data = $this->getVentamermaData($itemId, $almacen,$local);
            $id = $data["ID"];
        }
        
        $this->createJournal($id, $_SESSION['userInax'], $porcentaje);
      
        return $data;
    }
    
    public function getVentamermaData($itemId, $almacen,$local) {
           $queryStr = "select * from ".INTERNA.".dbo.VENTAMERMA where itemid= ? and almacen= ? and loc = ? ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemId);
        $query->bindParam(2,$almacen);
         $query->bindParam(3,$local);
        $query->execute();
        $data = $query->fetch();
        
        return $data;
    }

        public function getFamilies($company){
        $queryStr = "SELECT T31.NAME AS 'FAMILIA'
                    FROM ECORESPRODUCT T1
                    LEFT JOIN ECORESPRODUCTCATEGORY T2 ON (T1.RECID = T2.PRODUCT) AND (T2.CATEGORYHIERARCHY = '5637146826') -- SEGMENTACION DE GRUPO
                            LEFT JOIN ECORESCATEGORY T21 ON (T2.CATEGORY = T21.RECID)
                    LEFT JOIN ECORESPRODUCTCATEGORY T3 ON (T1.RECID = T3.PRODUCT) AND (T3.CATEGORYHIERARCHY = '5637146828') -- SEGMENTACION DE FAMILIA
                            LEFT JOIN ECORESCATEGORY T31 ON (T3.CATEGORY = T31.RECID)
                    LEFT JOIN ECORESPRODUCTCATEGORY T4 ON (T1.RECID = T4.PRODUCT) AND (T4.CATEGORYHIERARCHY = '5637146827') -- SEGMENTACION DE DIVISION
                            LEFT JOIN ECORESCATEGORY T41 ON (T4.CATEGORY = T41.RECID)
                    LEFT JOIN ECORESPRODUCTCATEGORY T5 ON (T1.RECID = T5.PRODUCT) AND (T5.CATEGORYHIERARCHY = '5637147576') -- SEGMENTACION DE LINEA
                            LEFT JOIN ECORESCATEGORY T51 ON (T5.CATEGORY = T51.RECID)
                    LEFT JOIN INVENTSUM T6 ON (T1.DISPLAYPRODUCTNUMBER = T6.ITEMID)
                    WHERE T6.DATAAREAID= ?
                    AND T31.name is not null 
                    group by T31.NAME order by T31.NAME ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$company);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getChildsItems($almacen,$family){
        
        $almacenIn = join("','",$almacen);
        $familyIn = join("','",$family);  
   
        $this->_adapter->query("SELECT ( SUM(T0.postedvalue) - ( CAST(SUM(T0.physicalvalue) AS DECIMAL(10, 2)) *- 1 ) ) / IIF(( SUM(T0.postedqty) + SUM(T0.received - T0.deducted) ) + SUM(T0.registered - T0.picked) = 0, 
                                                                                                                                                                          1 
                                                                                                                                                                          ,( SUM(T0.postedqty) + SUM(T0.received - T0.deducted) ) + SUM( T0.registered - T0.picked)) AS 'COSTO', 
                           T0.itemid,t1.INVENTSITEID
                    INTO   ##T3
                    FROM   inventsum T0 LEFT JOIN inventdim T1 ON T0.inventdimid = T1.inventdimid 
                    WHERE T1.dataareaid = '".COMPANY."' 
                    AND T0.dataareaid = '".COMPANY."' 
                    GROUP  BY T1.INVENTSITEID,T0.itemid");
        
        $this->_adapter->query("SELECT SUM(existencia) 'Existencia', 
                           articulo, 
                              sitio,
                           almacen, 
                           localidad 
                    INTO   ##T2 
                    FROM   (SELECT T0.itemid                       'Articulo', 
                                   SUM(T0.availphysical)           'Existencia', 
                                   T1.inventsiteid                 'Sitio', 
                                   T1.inventlocationid             'Almacen', 
                                   T1.wmslocationid                'Localidad', 
                                   (SELECT Max(expdate) 
                                    FROM   inventbatch 
                                    WHERE  inventbatchid = T1.inventbatchid 
                                           AND itemid = T0.itemid) 'fechavenci', 
                                   ( CASE 
                                       WHEN ( (SELECT expdate 
                                               FROM   inventbatch 
                                               WHERE  inventbatchid = T1.inventbatchid 
                                                      AND itemid = T0.itemid 
                                                      AND dataareaid = '".COMPANY."') > GETDATE() 
                                               OR 
                                                              (SELECT expdate 
                                                   FROM   inventbatch 
                                                   WHERE  inventbatchid = T1.inventbatchid 
                                                          AND itemid = T0.itemid 
                                                          AND dataareaid = '".COMPANY."') IS NULL ) 
                                                  THEN 0 
                                       ELSE 1 
                                     END ) AS caduco 
                            FROM   inventsum T0 
                                   INNER JOIN inventdim T1 ON T0.inventdimid = T1.inventdimid AND T1.dataareaid = '".COMPANY."' 
                            WHERE  T0.dataareaid = '".COMPANY."' 
                                           AND T1.inventlocationid IN ( '$almacenIn' )
                            GROUP BY T0.itemid,T1.inventsiteid,T1.inventlocationid,T1.wmslocationid,T1.inventbatchid) x 
                    GROUP BY x.articulo,x.sitio,x.almacen,x.localidad;");
        
        $this->_adapter->query("SELECT T0.itemid           AS 'CODIGO', 
                           searchname          AS 'NOMBRE', 
                           T1.inventlocationid AS 'ALMACEN', 
                           t31.NAME            AS 'FAMILY', 
                           T1.wmslocationid    AS 'LOC' ,
                              T1.inventsiteid     AS 'SITIO'
                    INTO   ##T1
                    FROM   inventsum t0 
                           LEFT JOIN inventdim T1 ON T0.inventdimid = T1.inventdimid 
                           LEFT JOIN ecoresproduct T2 ON T2.displayproductnumber = T0.itemid 
                           LEFT JOIN ecoresproductcategory T3 ON ( T2.recid = T3.product ) AND ( T3.categoryhierarchy = '5637146828' ) 
                           LEFT JOIN ecorescategory T31 ON ( T3.category = T31.recid ) 
                           LEFT JOIN inventsum T6 ON ( T2.displayproductnumber = T6.itemid ) 
                    WHERE  T1.inventlocationid IN ( '$almacenIn' ) 
                           AND t31.NAME IN ( '$familyIn' ) 
                           AND T6.dataareaid = '".COMPANY."'
                    GROUP BY T0.itemid,searchname,T1.inventlocationid, t31.NAME, T1.wmslocationid,T1.inventsiteid");
        
        
        $query = $this->_adapter->prepare("SELECT *
                    FROM ##T3 T1 
                    LEFT JOIN ##T2 T2 ON T1.INVENTSITEID = T2.Sitio AND T1.ITEMID = T2.Articulo
                    LEFT JOIN ##T1 T3 ON T3.SITIO = T2.Sitio AND T2.Articulo = T3.CODIGO AND T2.Localidad = T3.LOC
                    WHERE Existencia IS NOT NULL 
                    AND Existencia > 0  
                    AND CODIGO IS NOT NULL");
        $query->bindParam(1,$almacen);
//        $query->bindParam(2,$familyIn);
        $query->execute();
        $result = $query->fetchAll();
        $this->_adapter->query("DROP TABLE ##T1,##T2,##T3");
        return $result;
    }
    
    public function getStock($item,$inventLocationId,$loc){        
        $queryStr = "SELECT SUM(Existencia) 'Existencia'
			FROM (
			SELECT
			T0.ITEMID 'Articulo',
			SUM(T0.AVAILPHYSICAL) 'Existencia',
			T1.INVENTSITEID 'Sitio',
			T1.INVENTLOCATIONID 'Almacen',
			T1.WMSLOCATIONID 'Localidad',
			(SELECT MAX(EXPDATE) FROM INVENTBATCH WHERE INVENTBATCHID = T1.INVENTBATCHID AND ITEMID = ? ) 'fechavenci',
			(CASE WHEN ( (SELECT EXPDATE FROM INVENTBATCH WHERE INVENTBATCHID = T1.INVENTBATCHID AND ITEMID = ? AND DATAAREAID = '".COMPANY."') > GETDATE() 
                        OR (SELECT EXPDATE FROM INVENTBATCH WHERE INVENTBATCHID = T1.INVENTBATCHID AND ITEMID = ? AND DATAAREAID = '".COMPANY."') IS NULL)
			THEN 0 ELSE 1 END
			) as caduco
			FROM INVENTSUM T0
			INNER JOIN INVENTDIM T1 ON T0.INVENTDIMID=T1.INVENTDIMID AND T1.DATAAREAID = '".COMPANY."'
			WHERE T0.ITEMID = ? 
			AND T1.INVENTLOCATIONID = ?
			AND T1.WMSLOCATIONID = ?
			AND T0.DATAAREAID='".COMPANY."'
			GROUP BY T0.ITEMID,
			T1.INVENTSITEID,
			T1.INVENTLOCATIONID, 
			T1.WMSLOCATIONID,
			T1.INVENTBATCHID )x
			GROUP BY x.Articulo,x.Sitio,x.Almacen,x.Localidad;";
        
        $query = $this->_adapter->prepare($queryStr);    
        $query->bindParam(1,$item);
        $query->bindParam(2,$item);
        $query->bindParam(3,$item);
        $query->bindParam(4,$item);
        $query->bindParam(5,$inventLocationId);
        $query->bindParam(6,$loc);
        $query->execute();
        return $query->fetch();
        
    }
    
    public function getCostoPromedio($itemID,$site){
        try {
            if($site == "CEDS"){
                $site .= "CHI";
            }
        $query = $this->_adapter->prepare(COSTO_PROMEDIO);
        $query->bindParam(1,$itemID);
        $query->bindParam(2,$site);
        $query->execute();
        return $query->fetch();
        
        } catch (Exception $e){
            return 0;
        }
    }
    public function getUtilidadMermaItem($itemID,$site,$loc){
        $queryStr = "select porcentaje from ".INTERNA.".dbo.VENTAMERMA where itemid=? and almacen=? and loc=?;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemID);
        $query->bindParam(2,$site);
        $query->bindParam(3,$loc);
        $query->execute();
        return $query->fetchAll();
    }
    public function getVentaMerma($id = false){
        $id ? $where = " WHERE itemid like '%$id%'" : $where;
        $queryStr = "select * from ".INTERNA.".dbo.VENTAMERMA $where;";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getVentaMermaAlmacen($almacen = FALSE){
        $almacen ? $where = " WHERE ALMACEN in ('". join("','",$almacen)."')" : $where;
        $queryStr = "select * from ".INTERNA.".dbo.VENTAMERMA $where;";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getItem($itemid){
        $queryStr = "select * from ECORESPRODUCT where DISPLAYPRODUCTNUMBER = ? ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemid);
        $query->execute();
        return $query->fetch();
    }
    
    public function getPicturesKeys($almacen){
        $almacenIn = join("','",$almacen);
        $queryStr = "select ITEMID,ALMACEN,LOC from ".INTERNA.".dbo.mermaimg WHERE ALMACEN in ('$almacenIn') GROUP BY ITEMID,ALMACEN,LOC ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getPictures($itemid,$almacen,$loc){
        $queryStr = "select * from ".INTERNA.".dbo.mermaimg WHERE ITEMID = ? AND ALMACEN = ? AND LOC = ?;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemid);
        $query->bindParam(2,$almacen);
        $query->bindParam(3,$loc);
        $query->execute();
        return $query->fetchAll();
    }
    
    public function getPicture($id){
        $queryStr = "select * from ".INTERNA.".dbo.mermaimg WHERE ID = ? ;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$id);
        $query->execute();
        return $query->fetch();
    }
    
    public function postPicture($itemid,$base64,$almacen,$loc){
        $queryStr = "INSERT INTO ".INTERNA.".dbo.mermaimg (ITEMID,RUTA,ALMACEN,COMENTARIOS,LOC) VALUES (?,?,?,'',?); ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$itemid);
        $query->bindParam(2,$base64);
        $query->bindParam(3,$almacen);
        $query->bindParam(4,$loc);
        $query->execute();
    }
    
    public function deletePicture($id){
        $queryStr = "DELETE ".INTERNA.".dbo.mermaimg WHERE ID = ?; ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$id);
        $query->execute();
    }
    
    public function updatePicture($id,$comment){
        $queryStr = "update ".INTERNA.".dbo.mermaimg SET COMENTARIOS = ? WHERE ID = ?; ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$comment);
        $query->bindParam(2,$id);
        $query->execute();
    }
    
    public function getJournals(){
        $queryStr = "SELECT * FROM ".INTERNA.".dbo.JOURNAL_MERMA jm "
                . "JOIN ".INTERNA.".dbo.VENTAMERMA v on v.ID = jm.ID_VENTAMERMA "
                . "ORDER BY jm.ID_VENTAMERMA; ";
        $query = $this->_adapter->prepare($queryStr);
        $query->execute();
         return $query->fetchAll();
    }
    
    public function createJournal($idventamerma,$username,$data){
         $queryStr = "INSERT INTO ".INTERNA.".dbo.JOURNAL_MERMA (ID_VENTAMERMA,USERNAME,DATA) VALUES (?,?,?); ";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$idventamerma);
        $query->bindParam(2,$username);
        $query->bindParam(3,$data);
        $query->execute();
    }
}
