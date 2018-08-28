<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        date_default_timezone_set('America/Chihuahua');
        define('APP_PATH', '../application');
        define('IMAGES_PATH', '../application/assets/img');
        define('WK_IMAGES_PATH', APP_PATH . '/assets/img');
        define('STYLESHEET_PATH', '../application/assets/css');
        define('JAVASCRIPT_PATH', '../application/assets/js');
        define('LIBRARY_PATH', '../library');
        define('BOOTSTRAP_PATH', '../application/assets/bootstrap/'); 
        $dia=  date("hD").'4';
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/select2.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => APP_PATH. '/assets/fa/css/font-awesome.min.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/icon.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/materialize.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/avance-style.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/jquery-ui.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/jquery.dataTables.min.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/ghpages-materialize.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/main.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/theme.css?v=1.1',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/buttons/css/buttons.dataTables.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/font-google.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => IMAGES_PATH . '/avance-logo-small.png',
            'rel' => 'icon'
        ), 'PREPEND');
        $view->headLink(array(
            'href' => STYLESHEET_PATH . '/sweetalert2.css',
            'rel' => 'stylesheet',
            'media' => 'screen',
            'type' => 'text/css'
        ), 'PREPEND');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/html2canvas.min.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/jquery.hotkey.min.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/select2.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/colResizable-1.6.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/materialize.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/avance.js?v='.$dia);
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/jquery.filtertable.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/JsBarcode128.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/buttons/js/buttons.html5.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/buttons/js/dataTables.buttons.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/jquery.datatables-1.10.12.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/jquery-ui.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/jquery.validate.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/formatCurrency.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/jQuery-2.1.4.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/moment.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/Chart.min.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/sweetalert2.js');
        $view->headScript()->prependFile(JAVASCRIPT_PATH . '/tickets.js?v='.$dia); 
    }
}

