<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageDetailModel
 *
 * @author sistemas10
 */
class ImageDetailModel {
    
    public function getImages($item,$almacen) {
        $queryStr = "select * from ".INTERNA.".dbo.mermaimg WHERE ITEMID = ? AND ALMACEN = ?;";
        $query = $this->_adapter->prepare($queryStr);
        $query->bindParam(1,$item);
        $query->bindParam(2,$almacen);
        $query->execute();
        return $query->fetchAll();
    }
}
