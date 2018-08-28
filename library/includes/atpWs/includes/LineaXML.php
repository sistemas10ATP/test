<?php

/*
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class LineaXML {

    public $itemid;
    public $SiteId;
    public $LocationId;
    public $WMSLocationId;
    public $batchId;
    public $qty;
    public $punitario;
    public $observationsLine;
    public $lineaXML;
    public $lineaXMLarr = array();
    public $tipoDoc;

    public function __construct($DocumentType, $DocumentId) {
        $this->tipoDoc = $DocumentType;
        $this->lineaXML = '';
        if ($DocumentType === 'ORDVTA') {
            $this->lineaXML .= '<?xml version="1.0" encoding="UTF-8"?><Lines Version="1.1" Company="'.COMPANY.'" SalesId="' . $DocumentId . '">';
        } elseif ($DocumentType === 'CTZN') {
            $this->lineaXML .= '<?xml version="1.0" encoding="UTF-8"?><Lines Version="1.1" Company="'.COMPANY.'" QuotationId="' . $DocumentId . '">';
        }
    }

    public function addLine($line) {
        if (is_array($line) && (!empty($line))) {
            if ($this->tipoDoc === 'ORDVTA') {
                $this->lineaXML.= '<Line SalesLine="' . $line['numLine'] . '" ItemId="' . $line['item'] . '" SiteId="' . $line['sitio'] . '" LocationId="' . $line['almacen'] . '" WMSLocationId="' . $line['localidad'] . '" BatchId="' . $line['lote'] . '" SalesQty="' . $line['cantidad'] . '" SalesPrice = "' . $line['punitariolinea'] . '" Observations="' . $line['comentariolinea'] . '" ></Line>';
            } elseif ($this->tipoDoc === 'CTZN') {
                $this->lineaXML.= '<Line LineNum="' . $line['numLine'] . '" ItemId="' . $line['item'] . '" SiteId="' . $line['sitio'] . '" LocationId="' . $line['almacen'] . '" WMSLocationId="' . $line['localidad'] . '" BatchId="' . $line['lote'] . '" Qty="' . $line['cantidad'] . '" Price="' . $line['punitariolinea'] . '" Observations="' . $line['comentariolinea'] . '"></Line>';
            }
        }
    }

    public function getLineaXml() {
        if ($this->lineaXML != '') {
            return $this->lineaXMLarr;
        }
    }

    public function endLineaXml() {
        if ($this->lineaXML != '') {
            $this->lineaXML .= '</Lines>';
            $this->lineaXMLarr['lineaXML'] = $this->lineaXML;
            return $this->lineaXMLarr;
        }
    }
}