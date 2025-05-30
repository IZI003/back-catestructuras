<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../Modelos/JWTmodel.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/../MiLog.php';

class LoginService 
{
    private $dbHandler;
    private $table = 'usuarios';
    public function __construct()
    {
        $this->dbHandler = new DatabaseHandler();
    }
    
    public function postLogin($user,$password)
    {
     //   writeLog("LoginService.postLogin. INICIO" );

        $JWTmodel = new JWTmodel();
        $con = [
            'correo' => $user,
            'password' => $password
        ];
        
        $result= $this->dbHandler->get($this->table, $con);
        
        //writeLog("LoginService.postLogin. result ".json_encode($result));

        if(!isset($result[0]))
        {
            return null;  
        }
        
        $date   = new DateTimeImmutable();
        $token=$JWTmodel->getjwt($user,$result[0]['id'],$password);
        
        $salida = [
            'nombre' => $result[0]['nombre'],
            'email' => $result[0]['correo'],
            'id_usuario' => $result[0]['id'],
            'token' => $token,
            'fin_sesion' => $date->modify('+480 minutes')->getTimestamp(),
        ];      
                
        return $salida;
    }

    public function getValidarToken($datos)
    {
        $con = ['token' => $datos];
        
        $lista = $this->dbHandler->get($this->table,$con);
        $JWTmodel = new JWTmodel();
        $JWTmodel->getdesencripte($datos);
        return $lista;
    }
}