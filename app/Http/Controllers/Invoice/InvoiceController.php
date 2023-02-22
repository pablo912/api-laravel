<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Services\SunatService;
use App\Services\XmlInvoice;
use App\Traits\CantidadEnLetras;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{   
    use CantidadEnLetras;

    private $sunat;
    private $xml;

    public function __construct(SunatService $sunat, XmlInvoice $xml)
    {
        $this->xml  = $xml;
        $this->sunat = $sunat;
      
    }

    public function boleta(Request $request){
   
        $rules = [

            'emisor.tipodoc'                   =>  'required',
            'emisor.ruc'                       =>  'required',
            'emisor.razon_social'              =>  'required',
            'emisor.nombre_comercial'          =>  'required',
            'emisor.direccion'                 =>  'required',
            'emisor.ubigeo'                    =>  'required',
            'emisor.departamento'              =>  'required',
            'emisor.provincia'                 =>  'required',
            'emisor.distrito'                  =>  'required',
            'emisor.pais'                      =>  'required',
            'emisor.usuario_secundario'        =>  'required',
            'emisor.clave_usuario_secundario'  =>  'required',

            'cliente.tipodoc'                   =>  'required',  
            'cliente.ruc'                       =>  'required',
            'cliente.razon_social'              =>  'required',
            'cliente.direccion'                 =>  'required',
            'cliente.pais'                      =>  'required',

            'comprobante.tipodoc'                   =>  'required',
            'comprobante.serie'                     =>  'required',
            'comprobante.correlativo'               =>  'required',
            'comprobante.fecha_emision'             =>  'required',
            'comprobante.hora'                      =>  'required',
            'comprobante.fecha_vencimiento'         =>  'required',
            'comprobante.moneda'                    =>  'required', 
            'comprobante.total_opgravadas'          =>  'required',
            'comprobante.total_opexoneradas'        =>  'required',
            'comprobante.total_opinafectas'         =>  'required',
            'comprobante.total_impbolsas'           =>  'required',
            'comprobante.igv'                       =>  'required',
            'comprobante.total'                     =>  'required',
            'comprobante.forma_pago'                =>  'required',

            'detalle' => 'required|array|min:1',
            'detalle.*.item' => 'required'


        ];

        $this->validate($request, $rules);

        $emisor = $request->emisor;
        $cliente = $request->cliente;

        $comprobante = $request->comprobante;
        $comprobante['total_texto'] = '';
        $comprobante['monto_pendiente'] = 0.00;

        $detalle = $request->detalle;
       
        $comprobante['total_texto'] = $this->cantidadEnLetra($comprobante['total']);

        $nombreXML = $emisor['ruc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];
    
        $this->xml->CrearXMLInvoice($nombreXML, $emisor, $cliente, $comprobante, $detalle);

        $this->sunat->enviar_invoice($emisor,$nombreXML);
     
    }
    
    public function factura(Request $request){

        $rules = [

            'emisor.tipodoc'                   =>  'required',
            'emisor.ruc'                       =>  'required',
            'emisor.razon_social'              =>  'required',
            'emisor.nombre_comercial'          =>  'required',
            'emisor.direccion'                 =>  'required',
            'emisor.ubigeo'                    =>  'required',
            'emisor.departamento'              =>  'required',
            'emisor.provincia'                 =>  'required',
            'emisor.distrito'                  =>  'required',
            'emisor.pais'                      =>  'required',
            'emisor.usuario_secundario'        =>  'required',
            'emisor.clave_usuario_secundario'  =>  'required',

            'cliente.tipodoc'                   =>  'required',  
            'cliente.ruc'                       =>  'required',
            'cliente.razon_social'              =>  'required',
            'cliente.direccion'                 =>  'required',
            'cliente.pais'                      =>  'required',

            'comprobante.tipodoc'                   =>  'required',
            'comprobante.serie'                     =>  'required',
            'comprobante.correlativo'               =>  'required',
            'comprobante.fecha_emision'             =>  'required',
            'comprobante.hora'                      =>  'required',
            'comprobante.fecha_vencimiento'         =>  'required',
            'comprobante.moneda'                    =>  'required', 
            'comprobante.total_opgravadas'          =>  'required',
            'comprobante.total_opexoneradas'        =>  'required',
            'comprobante.total_opinafectas'         =>  'required',
            'comprobante.total_impbolsas'           =>  'required',
            'comprobante.igv'                       =>  'required',
            'comprobante.total'                     =>  'required',
            'comprobante.forma_pago'                =>  'required',
            'comprobante.monto_pendiente'           =>  'required',

            'detalle' => 'required|array|min:1',
            'detalle.*.item' => 'required'


        ];

        $this->validate($request, $rules);

  
        $emisor = $request->emisor;
        $cliente = $request->cliente;

        $comprobante = $request->comprobante;
        $comprobante['total_texto'] = $this->cantidadEnLetra($comprobante['total']);
        $cuotas = $request->cuotas;
      
        $detalle = $request->detalle;
       
       
        $nombreXML = $emisor['ruc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];
    
        $this->xml->CrearXMLInvoice($nombreXML, $emisor, $cliente, $comprobante, $detalle, $cuotas);

        $this->sunat->enviar_invoice($emisor,$nombreXML);



    }

}
