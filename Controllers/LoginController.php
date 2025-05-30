<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/LoginService.php';
require_once __DIR__ . '/../SERVICIOS/UsuarioService.php';
require_once __DIR__ . '/../MiLog.php';

class LoginController extends BaseController
{
    public function login($method)
    {
        //writeLog("LoginService.login. INICIO" );

        $arrQueryStringParams = $this->PostFromData();
        $salida = new salida_error();
        $salida_error = new salidaError();

        if (strtoupper($method) == 'POST') {
        //writeLog("LoginService.login. POST" );

            try {
                $LoginService = new LoginService();

                $user = "sinuser";
                if (isset($arrQueryStringParams['email']) && $arrQueryStringParams['email']) {
                    $user = $arrQueryStringParams['email'];
                }
                $password = "sinpass";
                if (isset($arrQueryStringParams['password']) && $arrQueryStringParams['password']) {
                    $password = $arrQueryStringParams['password'];
                }
             //   writeLog("LoginService.login. datos $password".$password." user ".$user );

                $arrUsers = $LoginService->postLogin($user, $password);
             //   writeLog("LoginService.login. datos arrUsers".json_encode($arrUsers));

                if ($arrUsers === null || empty($arrUsers)) {
                    $salida = $salida_error->response(401, 'email o contraseña incorrectos.');
                } else {
                    $salida = $salida_error->response(200, 'Login exitoso.');
                    $salida->datos = $arrUsers;
                }
            } catch (Error $e) {
                $salida = $salida_error->response(500, $e->getMessage());
            }
        } else {
            $salida = $salida_error->response(422, 'Método no permitido.');
        }

        // send output
        $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
    }
    
    public function Registro($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        //validamos que no ingresen dos veces el mismo pass
         
        $var = $this->PostFromData();
        
                try {
                    $fecha_actual = date('Y-m-d H:i:s');
                    $con = [
                        'nombre' => $var['nombre'],
                        'correo' => $var['email'],
                        'password' => $var['password'] ,
                    ];
                    $cond = ['correo' => $var['email']];
                    $existe = $usuario->get($cond);
                    if(empty($existe))
                    {                           
                    $result =  $usuario->post($con);
                    if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                }else{
                    $salida = $salida_error->response(409, "Ya existe un usuario con este mail");
                }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
        
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
    }
    
    public function ValidarToken($method)//no controla el metodo
    {
        $LoginService = new LoginService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        $var = $this->getQueryStringParams();
        try
        {
        if (isset($var['token']) && $var['token']) 
        {
           $con=$var['token'];
                    $result =  $LoginService->getValidarToken($con);
                    if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                }
            }
                 catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
    }
}