<?php

/**
 * 
 */
class CustomerController extends Zend_Controller_Action
{

    public function init(){
        try {
            //$this->_helper->layout()->disableLayout();
           $this->_helper->layout->setLayout('bootstrap');           
        } catch (Zend_Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    private function getCargaInicial(){
        $datosinicio = new Application_Model_Userinfo();
        /* -------------- Carga de Clientes --------------------------------- */
        $query = $datosinicio->_adapter->query(GET_CONJUNTO_CLIENTES);
        $query->execute();
        $this->view->getConjuntoClientes = json_encode($query->fetchAll());
        /* -------------- Carga de Moneda ---------------------------------- */
        $query = $datosinicio->_adapter->query(GET_MONEDA);
        $query->execute();
        $this->view->getMoneda = json_encode($query->fetchAll());
        /* -------------- Carga de Sitios ---------------------------------- */
        $query = $datosinicio->_adapter->query(GET_SITIO_VENTA);
        $query->execute();
        $this->view->getSitios = json_encode($query->fetchAll());
        /* -------------- Carga de ZonaVenta ---------------------------------- */
        $query = $datosinicio->_adapter->query(GET_ZONA_VENTA);
        $query->execute();
        $this->view->getZonaVenta = json_encode($query->fetchAll());
        /* -------------- Carga de Proposito ---------------------------------- */
        $query = $datosinicio->_adapter->query(GET_PROPOSITO);
        $query->execute();
        $this->view->getProposito = json_encode($query->fetchAll());
        /* ---------------- Menu de sitio -------------------------------------- */
        $this->view->solicita = filter_input(INPUT_POST, 'submit');
        $this->view->map = "Nuevo Cliente";
        
    }

    public function indexAction(){
        if (isset($_SESSION['userInax'])) {
        $request = $this->getRequest();
        $model = new Application_Model_CostomerModel();
        $kardex= new Application_Model_Userinfo();
        $token = $data = "";
        $this->getCargaInicial();
        
        if ($request->isGet()) {
            $token = filter_input(INPUT_GET, 'token');
        } else if ($request->isPost()) {
            $token = filter_input(INPUT_POST, 'token');
        }
        switch ($token) {
            case 'getAlmacen':
                $site = '%' . filter_input(INPUT_GET, 'sitio') . '%';
                print_r(json_encode($model->getArrayData(GET_ALMACEN,$site)));
                exit();
            break;
            case 'getDescuento':
                $site = '%' . filter_input(INPUT_GET, 'sitio') . '%';
                print_r(json_encode($model->getArrayData(GET_DESCUENTO,$site)));
                exit();
                break;
            case 'getPais':
                print_r(json_encode($model->getPais()));
                exit();
                break;
            case 'getColonia':
                $estado = filter_input(INPUT_GET, 'key');
                print_r(json_encode($model->getArrayData(GET_COLONIA,$estado)));
                exit();
                break;
            case 'getEstados':
                $pais = filter_input(INPUT_GET, 'key');
                print_r(json_encode($model->getArrayData(GET_ESTADOS,$pais)));
                exit();
                break;
            case 'getCiudad':
                $estado = filter_input(INPUT_GET, 'key');
                print_r(json_encode($model->getArrayData(GET_CUIDAD,$estado)));
                exit();
                break;
            case 'getZipcode':
                $zipCode = filter_input(INPUT_GET, 'key');
                print_r(json_encode($model->getArrayData(GET_ZIP_CODE,$zipCode)));
                exit();
                break;
            case 'guardarCliente':
                $datos = filter_input(INPUT_POST, 'datos');
                $data = $datos;
                break;
            case 'saveInfo':
                try {
                $this->_helper->viewRenderer->setNoRender(true);
                $this->_helper->layout->disableLayout();
                $registro = filter_input(INPUT_POST, 'registro');
                $nombreCliente = filter_input(INPUT_POST, 'nombreCliente');
                $rfc = filter_input(INPUT_POST, 'rfc');
                $sitioVenta = filter_input(INPUT_POST, 'sitioVenta');
                $conjuntoCliente = filter_input(INPUT_POST, 'conjuntoCliente');
                $zonaVenta = filter_input(INPUT_POST, 'zonaVenta');
                $almacen = filter_input(INPUT_POST, 'almacen');
                $tipoDescuento = filter_input(INPUT_POST, 'tipoDescuento');
                $moneda = filter_input(INPUT_POST, 'moneda');
                /* direccion */
                $cp = $_POST['cp'];
                $estado = $_POST['estado'];
                $pais = $_POST['pais'];
                $calle = $_POST['calle'];
                $numero = $_POST['numero'];
                $ciudad = $_POST['ciudad'];
                $colonia = $_POST['colonia'];
                $proposito = $_POST['proposito'];
                $adresses=array();
                foreach ($cp as $k => $v) {
                   $adresses[]=array( 'cp'=>$cp[$k],
                       'estado'=>$estado[$k],
                       'pais'=>$pais[$k],
                       'calle'=>$calle[$k],
                       'numero'=>$numero[$k],
                       'ciudad'=>$ciudad[$k],
                       'colonia'=>$colonia[$k],
                       'proposito'=>$proposito[$k]);                   
                }
                /* Contacto */
                $descripcion = $_POST['descripcion'];
                $telefono = $_POST['telefono'];
                $formaContacto = $_POST['formaContacto'];
                $extension = $_POST['extension'];
                $contactos = array();
                foreach ($descripcion as $k => $v) {
                    if($k==1){ $primary=1;}
                    else{ $primary=0; }
                    $contactos[]= array( 'descripcion'=>$descripcion[$k],
                        'telefono'=>$telefono[$k],
                        'formaContacto'=>$k,
                        'extension'=>$extension[$k],
                        'isPrimary'=>$primary,
                        'proposito'=>$proposito[$k]);
                }
                
                    $parametros[] = array( 'Name'          => $nombreCliente, 
                                'CustGroup'     => $conjuntoCliente,
                                'CurrencyCode'  => $moneda,
                                'CompanyType'   => $registro,
                                'RFC'           => $rfc,
                                'SalesDistrict' => $zonaVenta,
                                'SiteId'        => $sitioVenta,
                                'LocationId'    => $almacen,
                                'LineDisc'      => $tipoDescuento,
                                'Address'       => $adresses,
                                'Contact'       => $contactos
                    );
                    $client= $model->isClientExist($rfc,COMPANY);
                    if(is_array($client)&& empty($client)){
                        $custAccount = $model->setDataClient($parametros);
                        $model->setKardexCliente('ALTA CLIENTE con RFC: '.$rfc);
                        $kardex->kardexLog("cliente nuevo parametros: ".json_encode($parametros)." resultado: ".json_encode($custAccount),json_encode($parametros),json_encode($custAccount),1,'ALTACLIENTE');
                        echo $custAccount;
                    }
                    else{
                        $kardex->kardexLog("cliente nuevo parametros: ".json_encode($parametros)." resultado: ".json_encode($custAccount),json_encode($parametros),'YA EXISTE CLIENTE CON CLAVE '.$client[0]['ACCOUNTNUM'],1,'ALTACLIENTE');
                        echo json_encode(array("status"=>"Exito","msg"=>'YA EXISTE CLIENTE CON CLAVE '.$client[0]['ACCOUNTNUM']));
                    }                                        
                    exit();                    
                } catch (Exception $objError) {
                    echo $objError->getMessage();
                    exit();
                }               
                break;
            default:
                break;
        }
        }
        else {
            return $this->_helper->redirector->gotoUrl('../public/login');
        }
    }
}