<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';

class PagoService
{
    private $dbHandler;
    private $table = 'pagos';
    
    public function __construct()
    {
        $this->dbHandler = new DatabaseHandler();
    }

    public function Confirmar($user,$password)
    {
        $con = [
        'email' => $user,
        'password' => $password
        ];
        $result= $this->dbHandler->get($this->table, $con);
        if(!isset($result[0]))
        {
            return null;
        }

        $salida = [
        'nombre' => $result[0]['nombre'],
        'apellido' => $result[0]['apellido'],
        'email' => $result[0]['email'],
        'id_usuario' => $result[0]['usuario_id'],
        'token' => $token,
        'fin_sesion' => $date->modify('+15 minutes')->getTimestamp(),
        ];

        $dat = ['token' =>$token];
        $condicion = ['usuario_id' =>$result[0]['usuario_id']];
        $result= $this->dbHandler->update($this->table, $dat, $condicion);

        return $salida;
    }

    public function ConfirmarPago($datos)
    {
       // writeLog("PagoService.ConfirmarPago. INICIO");

         $data = [
                    'ID_pasarela' => $datos['id'],
                    'event' => '',
                    'amount_in_cents' => $datos['amount_in_cents'],
                    'reference' => $datos['reference'],
                    'currency' => $datos['currency'],
                    'payment_method_type' => $datos['payment_method_type'],
                    'status' => $datos['status'],
                ];
    //    writeLog("PagoService.ConfirmarPago. Por insertar");
                   
        $result= $this->dbHandler->insert($this->table, $data);
        if(!isset($result))
        {
     //   writeLog("PagoService.ConfirmarPago. VAcio");

            return null;
        }
      //  writeLog("PagoService.ConfirmarPago. OK");

        $salida = [
        'status' =>"OK"
        ];

        return $salida;
    }
}