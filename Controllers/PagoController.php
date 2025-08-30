<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/PagoService.php';
require_once __DIR__ . '/../MiLog.php';

class PagoController extends BaseController
{
    public function validarpago($method)
    {
       /* $cadena_concatenada = $Referencia.$Monto.$Moneda.$FechaExpiracion.$secret_integr;
        hash ("sha256", $cadena_concatenada);*/
        writeLog("PagoController.validarpago. INICIO ");
        
        $Pago = new PagoService();
        $salida_error = new salidaError();
        $salida = new salida_error();

        if ($method === 'post') {

            $var = $this->PostFromData();
            
            try {
            writeLog("PagoController.validarpago. POST ");
                
                $data = [
                         "event" => $var['event'],
                         "id" => $var['data']['transaction']['id'],
                         "amount_in_cents" => $var['data']['transaction']['amount_in_cents'],
                         "reference" => $var['data']['transaction']['reference'],
                         "customer_email" => $var['data']['transaction']['customer_email'],
                         "currency" => $var['data']['transaction']['currency'],
                         "payment_method_type" => $var['data']['transaction']['payment_method_type'],
                         "redirect_url" => $var['data']['transaction']['redirect_url'],
                         "status" => $var['data']['transaction']['status'],
                         "shipping_address" => $var['data']['transaction']['shipping_address'] ?? null,
                         "payment_link_id" => $var['data']['transaction']['payment_link_id'] ?? null,
                         "payment_source_id" => $var['data']['transaction']['payment_source_id'] ?? null,
                         "environment" => $var['environment'],
                         "properties" => $var['signature']['properties'],
                         "checksum" => $var['signature']['checksum']
                        ];
        writeLog("PagoController.validarpago. Por llamar  ConfirmarPago");
                                
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
            $salida = $salida_error->response(422, '');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function guardar_status($method)
    {
        writeLog("PagoController.guardar_status. POST ");

        $Pago = new PagoService();
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
                        
                        
                      /*  $data = [
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
                           ];*/
                           $data = [
                            "id" => $var['data']['id'],
                            "amount_in_cents" => $var['data']['amount_in_cents'],
                            "reference" => $var['data']['reference'],
                            "currency" => $var['data']['currency'],
                            "payment_method_type" => $var['data']['payment_method_type'],
                            "redirect_url" => '',
                            "status" => $var['data']['status'],
                            "shipping_address" => $var['data']['shipping_address'] ?? null,
                            "payment_link_id" => $var['data']['payment_link_id'] ?? null,
                            "payment_source_id" => $var['data']['payment_source_id'] ?? null,
                            "environment" =>'',
                            "properties" =>'',
                            "checksum" => '',
                           ];                
                $Pagos = $Pago->ConfirmarPago($data);
                if (!($Pagos)) {
                    $salida = $salida_error->response(204, '');
                } else {
                    $salida = $salida_error->response(200, '');
                  //  $data['status'] = $data['status'] ==='ERROR' ? ($data['status'].' ' . $data['status_message']) : $data['status'];
                    
                  $data= [...$data,"status_message" =>$var['data']['status_message']  ]  ;
                  
                  $salida->datos = $data;
                }
            } catch (Error $e) {
                $salida = $salida_error->response(500, $e->getMessage());
            }
        } else {
            $salida = $salida_error->response(422, '');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
}