<?php
/**
 * Description of reporteController
 *
 * @author sistemas10
 */
  
class MermaController extends Zend_Controller_Inax {
    public function init(){
        try {
            $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    
    public function indexAction(){
        if (! isset($_SESSION['userInax'])) {
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
        $model= new Application_Model_MermaModel();
        $this->view->listaMerma = $model->getAlmacenesMerma();
        $this->view->listaFamilia = $model->getFamilies(COMPANY);
    }
    /*
     * realizar cambios de validacion por almacen para no mostrar utilidad de otros almacenes
     */
    
    public function getFilterFamilyAction(){
        $model= new Application_Model_MermaModel();
        $family = filter_input(INPUT_POST,"familia", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $almacen = filter_input(INPUT_POST,"almacen", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $sinconf = filter_input(INPUT_POST,"sinconf", FILTER_DEFAULT);
     
        
        $childs = $model->getChildsItems($almacen,$family);
        //$this->json($childs);
        $tablaVentaMerma = $model->getVentaMermaAlmacen($almacen);
        foreach ($tablaVentaMerma as $row){
            $key = $row["ITEMID"].$row["ALMACEN"].$row["LOC"];
            $mapTablaVentaMerma["$key"] = $row["PORCENTAJE"];
        }
        
        $imagesKey = $model->getPicturesKeys($almacen);
        
        foreach ($imagesKey as $row){
            $key = $row["ITEMID"].$row["ALMACEN"].$row["LOC"];
            $mapImagesKey["$key"] = $key;
        }
        
        foreach ($childs as $key => &$child){ 
            
            $child["STOCK"] = $child["Existencia"];            
            if($child["STOCK"]== '.0000000000000000' || $child["LOC"] == "" ){
                unset($childs[$key]);
                continue;
            }
            
            $keys = $child["CODIGO"].$child["ALMACEN"].$child["LOC"];
            
            if($sinconf == "true" && isset($mapTablaVentaMerma[$keys])){
                unset($childs[$key]);
                continue;
            }
            
            if(isset($mapTablaVentaMerma[$keys])){
                $child["UTILIDAD"] = $mapTablaVentaMerma[$keys];  
                $child["PRECIO"] = round($child["COSTO"]/(1-$child["UTILIDAD"]), 2); 
            }else{
                $child["UTILIDAD"] = "N/D";
                $child["PRECIO"] = "N/A";
            }
            
            if(isset($mapImagesKey[$keys])){
                $child["IMAGES"] = true;
            }else{
                $child["IMAGES"] = false;
            }
            
            $child["COSTO"]= round ($child["COSTO"],2);
            
        }
        
        $this->json($childs);
        
    }
    
    /*               */
    
    public function getFilterFamilyOLDAction(){
        $model= new Application_Model_MermaModel();
        $family = filter_input(INPUT_POST,"familia", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $almacen = filter_input(INPUT_POST,"almacen", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $sinconf = filter_input(INPUT_POST,"sinconf", FILTER_DEFAULT);
     
        
        $childs = $model->getChildsItems($almacen,$family);
        
        //$this->json($childs);
        
        $tablaVentaMerma = $model->getVentaMermaAlmacen($almacen);
        foreach ($tablaVentaMerma as $row){
            $key = $row["ITEMID"].$row["ALMACEN"].$row["LOC"];
            $mapTablaVentaMerma["$key"] = $row["PORCENTAJE"];
        }
        
        $imagesKey = $model->getPicturesKeys($almacen);
        
        foreach ($imagesKey as $row){
            $key = $row["ITEMID"].$row["ALMACEN"].$row["LOC"];
            $mapImagesKey["$key"] = $key;
        }
        
        foreach ($childs as $key => &$child){            
            $stock = $model->getStock($child["CODIGO"], $child["ALMACEN"],$child["LOC"]);
            $child["STOCK"] = $stock["Existencia"];            
            if($child["STOCK"]== '.0000000000000000' || $child["LOC"] == "" ){
                unset($childs[$key]);
                continue;
            }
            
            $keys = $child["CODIGO"].$child["ALMACEN"].$child["LOC"];
            
            if($sinconf == "true" && isset($mapTablaVentaMerma[$keys])){
                unset($childs[$key]);
                continue;
            }
            
            $costo = $model->getCostoPromedio($child["CODIGO"], substr($child["ALMACEN"],0,-4));
            $child["COSTO"] =isset($costo["COSTO"]) ? $costo["COSTO"] : 0;           
            if(isset($mapTablaVentaMerma[$keys])){
                $child["UTILIDAD"] = $mapTablaVentaMerma[$keys];  
                $child["PRECIO"] = round($child["COSTO"]/(1-$child["UTILIDAD"]), 2); 
            }else{
                $child["UTILIDAD"] = "N/D";
                $child["PRECIO"] = "N/A";
            }
            
            if(isset($mapImagesKey[$keys])){
                $child["IMAGES"] = true;
            }else{
                $child["IMAGES"] = false;
            }
            
            $child["COSTO"]= round ($child["COSTO"],2);
            
            
        }
        $this->json($childs);
    }
    public function setFamilyUtilityAction(){
        $model= new Application_Model_MermaModel();
        
         $family = filter_input(INPUT_POST,"familia", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $almacen = filter_input(INPUT_POST,"almacen", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        
        $childs = $model->getChildsItems($almacen,$family);
        
        $tablaVentaMerma = $model->getVentaMermaAlmacen($almacen);
        foreach ($tablaVentaMerma as $row){
            $key = $row["ITEMID"].$row["ALMACEN"].$row["LOC"];
            $mapTablaVentaMerma["$key"] = $row["PORCENTAJE"];
        }
        
        foreach ($childs as &$child){
            $costo = $model->getCostoPromedio($child["CODIGO"], substr($child["ALMACEN"],0,-4));
            $child["COSTO"] = isset($costo["COSTO"]) ? $costo["COSTO"] : 0;
            $key = $child["CODIGO"].$child["ALMACEN"].$child["LOC"];
            
            if(isset($mapTablaVentaMerma[$key])){
                $child["UTILIDAD"] = $mapTablaVentaMerma[$key];  
                $child["UTILIDAD"] = $mapTablaVentaMerma[$child["CODIGO"]];  
                $child["PRECIO"] = $child["COSTO"]*$child["UTILIDAD"]; 
            }else{
                $child["UTILIDAD"] = "";
                $child["PRECIO"] = "";
            }
            $child["status"]=$model->exist($child["CODIGO"], $child["ALMACEN"],filter_input(INPUT_POST,"utilidad"),$child["LOC"]);
        }
        $this->json($childs);
    }
    public function getPriceMermaAction() {
        try {
            $model= new Application_Model_MermaModel();
            $costo = $model->getCostoPromedio(filter_input(INPUT_POST,"item"), substr(filter_input(INPUT_POST,"almacen"),0,-4));
            $utilidad= $model->getUtilidadMermaItem(filter_input(INPUT_POST,"item"), filter_input(INPUT_POST,"almacen"),filter_input(INPUT_POST,"loc"));            
            $precio= ((double) $costo['COSTO']) /(1- (double) $utilidad[0]['porcentaje']);
            $arr=array("precio"=>$precio,"costo"=>$costo['COSTO'],"utilidad"=>$utilidad[0]['porcentaje']);
            $this->json($arr);
        } catch (Exception $ex) {
            echo $ex->getTrace();
        }
        
    }
    
    public function searchVentamermaAction(){
        $model= new Application_Model_MermaModel();
        $this->json($model->getVentaMerma($_GET["term"]));
    }
    
    public function getItemDataAction(){
        $model= new Application_Model_MermaModel();
        $this->json($model->getItem($_POST["itemid"]));
    }
    
    public function getPicturesAction(){
        $model= new Application_Model_MermaModel();
        $this->json($model->getPictures($_POST["itemid"],$_POST["almacen"],$_POST["local"]));
    }
    
    public function postPictureAction(){
        try {
            $model= new Application_Model_MermaModel();        
            if(isset($_FILES))
            {
                $file_tmp= $_FILES['file']['tmp_name'];
                $type = pathinfo($file_tmp, PATHINFO_EXTENSION);
                $tipo=$_FILES["file"]['type'];
                $data = file_get_contents($file_tmp);
                $base64 = 'data:image/' . $tipo . ';base64,' . base64_encode($data);
                
                $file = microtime(true); 
                $file = str_replace(".", "", $file);
                $tipo = str_replace("image/", "", $tipo);
                $directorio = APPLICATION_PATH.'/../public/img/'; 
                
               
                if(!is_dir($directorio)){ 
                    mkdir($directorio, 0777);
                }
                
                
                move_uploaded_file($_FILES["file"]["tmp_name"],$directorio.$file.".".$tipo);  

                $model->postPicture($_POST["itemid"],"./img/".$file.".".$tipo,$_POST["almacen"],$_POST["local"]);
                $this->json(array("message"=>"success","file"=>$_FILES));
            }        
        $this->json(array("message"=>"error"));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }        
    }
    
    public function deletPictureAction(){
        $model= new Application_Model_MermaModel();
        
        $directorio = APPLICATION_PATH.'/../public/'; 
        unlink($directorio.$_POST["nameFile"]);
        $model->deletePicture($_POST["id"]);
        $this->json(array("message"=>"success"));
    }
    
    public function updatePictureAction(){
        $model= new Application_Model_MermaModel();
        $model->updatePicture($_POST["id"],$_POST["comment"]);
        $dataRow = $model->getPicture($_POST["id"]);
        $data = $model->getPictures($dataRow["ITEMID"], $dataRow["ALMACEN"],$dataRow["LOC"]);
        $this->json($data);
    }
    
    public function updateRowAction(){
        $model= new Application_Model_MermaModel();
        $model->exist($_POST["codigo"],$_POST["almacen"],$_POST["utilidad"],$_POST["local"]);
        $this->json($_POST);
    }
    
    public function downloadImgAction(){
        $model= new Application_Model_MermaModel();
        $dataRow = $model->getPicture($_GET["id"]);
        header('Content-Disposition: attachment;filename="'.$_GET["id"].'.png"');
        header('Content-Type: application/force-download'); 
        header ("Content-Length: ".filesize($dataRow["RUTA"]));
        readfile($dataRow["RUTA"]);
        exit();
    }
    
    public function downloadZipAction(){
        $zip = new ZipArchive();
        $model= new Application_Model_MermaModel();
        
        $pictures = $model->getPictures($_GET["itemid"],$_GET["almacen"],$_GET["local"]);
        
        $zip->open($_GET["itemid"].'-'.$_GET["almacen"].'-'.$_GET["local"].".zip",ZipArchive::CREATE);
        
        foreach ($pictures as $pic)
        {
            $zip->addFile($pic["RUTA"],$pic["ID"].".jpg");
        }
        $zip->close();
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=".$_GET["itemid"].'-'.$_GET["almacen"].'-'.$_GET["local"].".zip");
 
        readfile($_GET["itemid"].'-'.$_GET["almacen"].'-'.$_GET["local"].".zip");

        unlink($_GET["itemid"].'-'.$_GET["almacen"].'-'.$_GET["local"].".zip");
        exit();
    }
    
    public function getStock(){
        $model = new Application_Model_MermaModel();              
        $this->json($model->getStock(filter_input(INPUT_POST,"itemid"), filter_input(INPUT_POST,"almacen")));             
    }
    
}
