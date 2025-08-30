<?php
class Respuesta
{
    private $response = [
        'result' => array(),
        'status' => "ok",
    ];

    public function error_405()
    {
        $this->response['status'] = "error";
        $this->response['result'] = array(
            'error_id' => "405",
            'error_message' => "Metodo no permitido"
        );

        return $this->response;
    }

    public function error_400()
    {
        $this->response['status'] = "error";
        $this->response['result'] = array(
            'error_id' => "400",
            'error_message' => "Metodo no permitido Datos enviados incompletos o con formato incorrectos"
        );

        return $this->response;
    }

    public function error_200($valor = "Datos incorrectos")
    {
        $this->response['status'] = "error";
        $this->response['result'] = array(
            'error_id' => "200",
            'error_message' => $valor
        );

        return $this->response;
    }

    public function error_401($valor = "No autorizado")
    {
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "401",
            "error_msg" => $valor
        );
        return $this->response;
    }
    public function ok_200($valor)
    {
        $this->response['status'] = "OK";
        $this->response['result'] = array(
            'error_id' => "200",
            'error_message' => "",
            'datos' => $valor
        );

        return $this->response;
    }
}