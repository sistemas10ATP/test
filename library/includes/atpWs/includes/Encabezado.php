<?php
class Encabezado {

    public $encabezado = array();
   
    public function __construct(array $options = null) {
        if (is_array($options)) {            
            $this->setOptions($options);
        }       
    }

    public function setOptions($options) {
        $this->encabezado = $options;
    }

    public function getEncabezado() {
        if (is_array($this->encabezado) && (!empty($this->encabezado))) {
            return $this->encabezado;
        } else {
            return '';
        }
    }
   /*
    * @param String seguridadCR Es el parametro que se utiliza para la cuenta de la tarjeta cuando es una orden de venta
    */
    public function getEncabezadoXML($tipo) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
          
        $tipo == 'ORDVTA' ?  $xml .= '<Header Version="1.0"><Company>'.COMPANY.'</Company>' : $xml .= '<Quotation Version="1.0">';
        
        $xml .=     '<CustAccount>' . $this->encabezado['claveclte'] . '</CustAccount>'
                    . '<DeliveryRecId>' . $this->encabezado['RecIdDelivery'] . '</DeliveryRecId>'
                    . '<InvoicedRecId>' . $this->encabezado['RecIdInvoiced'] . '</InvoicedRecId>'
                    . '<SiteId>' . $this->encabezado['SiteId'] . '</SiteId>'
                    . '<LocationId>' . $this->encabezado['LocationId'] . '</LocationId>'
                    . '<PaymMode>' . $this->encabezado['PaymMode'] . '</PaymMode>'
                    . '<DeliveryMode>' . $this->encabezado['DeliveryMode'] . '</DeliveryMode>'
                    . '<DeliveryTerm>' . $this->encabezado['DeliveryTerm'] . '</DeliveryTerm>'
                    . '<WorkerResponsible>' . $this->encabezado['WorkerResponsible'] . '</WorkerResponsible>'
                    . '<WorkerTaker>' . $this->encabezado['WorkerTaker'] . '</WorkerTaker>'
                    . '<User>' . $this->encabezado['_User'] . '</User>'
                    . '<Observations>' . $this->encabezado['comentariosCabecera'] . '</Observations>'
                    . '<CurrencyCode>' . $this->encabezado['CurrencyCode'] . '</CurrencyCode>'
                    . '<CustPurchOrder>' . $this->encabezado['ocCliente'] . '</CustPurchOrder>'
                    . '<CustReference>' . $this->encabezado['referenciaCliente'] . '</CustReference>';
        if ($tipo == 'ORDVTA') {            
            $xml .= '<SalesId>' . $this->encabezado['documentId'] . '</SalesId>'
                    . '<AccountNumPaym>' . $this->encabezado['seguridadCR'] . '</AccountNumPaym>'
                    . '</Header>';
        } else {
            $xml .= '<Company>'.COMPANY.'</Company>'
                    . '<QuotationId>' . $this->encabezado['documentId'] . '</QuotationId>'
                    .'<Payment>' . $this->encabezado['Payment'] . '</Payment>'
                    . '</Quotation>';
        }
        return $xml;
    }
}