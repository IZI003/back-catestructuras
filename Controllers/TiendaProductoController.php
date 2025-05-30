<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/TiendaProducto.php';
//require_once __DIR__ . '/../SERVICIOS/PagoService.php';

require_once __DIR__ . '/../MiLog.php';

class TiendaProductoController extends BaseController
{
        public function cart($method)
        {
            $carrito = new TiendaProductoService();
            $salida_error = new salidaError();
            $salida = new salida_error();
            writeLog("TiendaProductoController.Cart. INICIO");
            
            switch($method)
            {
                case 'get':
                {
                        $var = $this->getQueryStringParams();

                        if (isset($var['id_usuario']) && $var['id_vendedor']) {
                            try {
                                
                                $carrito = $carrito->get_carrito($var['id_usuario'], $var['id_vendedor']);
                                
                                if (!($carrito)) {
                                    $salida = $salida_error->response(204, '');
                                } else {
                                    $salida = $salida_error->response(200, '');
                                    $salida->datos = $carrito;
                                }
                                
                            } catch (Error $e) {
                                $salida = $salida_error->response(500, $e->getMessage());
                            }
                        }
                        else {
                            $salida = $salida_error->response(500, "Faltan datos");
                        }  
                        break;
                }
                
                case 'post':
                {
                    writeLog("TiendaProductoController.Cart. POST");

                            $var = $this->PostFromData();
                        
                            try {
                                $con = [
                                    'id_comprador' => $var['id_comprador'],
                                    'id_vendedor' => $var['id_vendedor'], 
                                    'cantidad' => $var['cantidad'],
                                    'id_producto' => $var['id_producto']
                                ];
                    writeLog("TiendaProductoController.Cart. llamando al servicio ".json_encode($con));
                                    
                                    $carrito = $carrito->post_item_carrito($con);
                                    
                                    if (!($carrito)) {
                                        $salida = $salida_error->response(404, '');
                                    } 
                                    else {
                                        $salida = $salida_error->response(200, '');
                                        $salida->datos = $con;
                                    }
                                    
                                } catch (Error $e) 
                                {
                                    writeLog("TiendaProductoController.Cart, ".$e->getMessage());
                                    $salida = $salida_error->response(500, $e->getMessage());
                                }
                        break;

                }
                case 'delete':
                {
            writeLog("TiendaProductoController.Cart. delete");

                        $uriSegments = $this->getUriSegments();
                        foreach ($uriSegments as $segment) {
                            if (is_numeric($segment)) {
                                $id=(int)$segment;
                                break;
                            }
                        }
                        
                        try {
            writeLog("TiendaProductoController.Cart. id ".json_decode($id));
                            
                            if(!isset($id))
                            {
                                $salida = $salida_error->response(500, 'Error id invalido');
                            }
                            else{
                                    $con= ['id' => $id];
                
                                    $carrito = $carrito->delete_item_carrito($con);
                                    $salida = $salida_error->response(200, '');
                                    $salida->datos = $carrito;
                                }
                            
                        } catch (Error $e) 
                        {
                            writeLog("ProductoController.MetodoPago. ".$e->getMessage());
                            $salida = $salida_error->response(500, $e->getMessage());
                        }
                        break;
                } 
            }
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
        }

        public function guardar_status($method)
    {
        writeLog("PagoController.guardar_status. POST ");

      //  $Pago = new PagoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        if ($method === 'post') {
            $var = $this->PostFromData();
            try {
        writeLog("PagoController.guardar_status. Por llamar  ConfirmarPago");

                      /*  $data = [
                            'ID_pasarela' => $datos['id'],
                            'event' =>'',
                            'amount_in_cents' => $datos['amount_in_cents'],
                            'reference' => $datos['reference'],
                            'customer_email' =>'',
                            'currency' => $datos['currency'],
                            'payment_method_type' => $datos['payment_method_type'],
                            'status' => $datos['status'],
                            'environment' => '',
                            'checksum' => ''
                        ]; */
                        
                        
                        $data = [
                            "id" => $var['data']['id'],
                            "amount_in_cents" => $var['data']['amount_in_cents'],
                            "reference" => $var['data']['reference'],
                            "customer_email" => $var['data']['customer_email'],
                            "currency" => $var['data']['currency'],
                            "payment_method_type" => $var['data']['payment_method_type'],
                            "redirect_url" => '',
                            "status" => $var['data']['status'],
                            "shipping_address" => $var['data']['shipping_address'] ?? null,
                            "payment_link_id" => $var['data']['payment_link_id'] ?? null,
                            "payment_source_id" => $var['data']['payment_source_id'] ?? null,
                            "environment" =>'',
                            "properties" =>'',
                            "checksum" => ''
                           ];
                                    
                $Pagos = $Pago->ConfirmarPago($data);
                if (!($Pagos)) {
                    $salida = $salida_error->response(204, '');
                } else {
                    $salida = $salida_error->response(200, '');
                    $salida->datos = $Pagos;
                }
            } catch (Error $e) {
                $salida = $salida_error->response(500, $e->getMessage());
            }
        } else {
            $salida = $salida_error->response(422, 'Metodo No permitido');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
}