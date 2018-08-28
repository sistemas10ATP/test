<?php

class Application_Model_UserinfoMapper
{

    protected $_dbTable;

    public $_adapter;

    public function setDbTable($dbTable){
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
            $this->_adapter = $dbTable->getDefaultAdapter();
        }
        if (! $dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getAdapter(){
        $db = new Application_Model_DbTable_Userinfo();
        $this->_adapter = $db->getDefaultAdapter();
        return $this->_adapter;
    }

    public function getDbTable(){
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Userinfo');
        }
        return $this->_dbTable;
    }

    /*
     * public function save(Application_Model_Userinfo $userinfo)
     * {
     * $data = array(
     * 'email' => $guestbook->getEmail(),
     * 'comment' => $guestbook->getComment(),
     * 'created' => date('Y-m-d H:i:s'),
     * );
     *
     * if (null === ($id = $guestbook->getId())) {
     * unset($data['id']);
     * $this->getDbTable()->insert($data);
     * } else {
     * $this->getDbTable()->update($data, array('id = ?' => $id));
     * }
     * }
     */
    
    /*
     * public function find($id, Application_Model_Userinfo $userinfo)
     * {
     * $result = $this->getDbTable()->find($id);
     * if (0 == count($result)) {
     * return;
     * }
     * $row = $result->current();
     * $guestbook->setId($row->id)
     * ->setEmail($row->email)
     * ->setComment($row->comment)
     * ->setCreated($row->created);
     * return $row;
     * }
     */
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        $entries = $resultSet;
        return $entries;
    }

    public function usuarios()
    {
        $this->getAdapter();
        $a = $this->_adapter->query("SELECT * FROM userinfo WHERE ID = '1184'");
        $a->execute();
        $result = $a->fetchAll();
        return $result;
    }
    function getMes($mes){
        setlocale(LC_TIME, 'spanish');  
        $nombre=strftime("%B",mktime(0, 0, 0, $mes, 1, 2000)); 
        return $nombre;
    } 
    public function Query($query,$params=array()) {
        $preparedStatement = $this->_adapter->prepare($query);
        $preparedStatement->execute($params);
        $resultSet = $preparedStatement->fetchAll();  
        $result=array();
        foreach ($resultSet as $k => $v) {
            $d=array();
            $indice=0;
            foreach ($v as $l => $z) {
                $d[$indice]=$z;
                $indice++;
            }
           $result[]=$d;
        }
        return $result ;      
    }
    public function QueryResulSet($query,$params=array()) {
        $preparedStatement = $this->_adapter->prepare($query);
        $preparedStatement->execute($params);
        $resultSet = $preparedStatement->fetchAll();  
        return $resultSet ;      
}
    public function Insert($query,$params=  array()){
        try{
        $preparedStatement = $this->_adapter->prepare($query);
            return  $preparedStatement->execute($params);
        }
        catch (Zend_Exception $e){
            return $e;
        }
    }
    public function getLastID($table,$col){
        try{
            $preparedStatement = $this->_adapter->prepare("select max(".$col.") as total from ".$table);
            $preparedStatement->execute();
            $resultSet = $preparedStatement->fetchAll();  
            return (integer)$resultSet[0]['total'] ;  
        }
        catch (Zend_Exception $e){
            return $e;
        }       
    }
}
