<?php
require_once('../INCLUDES/Database.class.php');
require_once('../Modelos/Respuesta.php');

class Auth extends Database
{
    public function login($entrada)
    {
        $respuesta = new Respuesta;
        $datos = json_decode($entrada);

        $email = $datos["email"];
        $password = $datos["password"];

        if (!isset($email) || !isset($password)) {
            return $respuesta->error_400();
        } else {

            $DatabaseHandler = new DatabaseHandler();

            $stmt = $DatabaseHandler->get("usuario", ['email' => $email]);
            if (!isset($stmt[0])) {
                return $respuesta->error_200("El usuario no existe");
            } else {
                $func = new funciones();
                $pas_encrip = $func->encriptacion($password);
                if ($stmt[0]["password"] ==  $pas_encrip) {
                    if ($stmt[0]["Activo"] == 0) {
                        return $respuesta->error_200("El usuario no existe");
                    } else {


                        
                     /*   session_start();
                        $_SESSION['user_id'] = $usuario->id;*/
                        //    $respuesta->datos = $stmt[0];

                        //   return $respuesta->ok();
                    }
                } else {
                    return $respuesta->error_200("Contraseña incorrecta");
                }
            }

            print_r(json_encode($stmt));
        }
    }
    /*
    private function insertarToken($usuarioid){
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $date = date("Y-m-d H:i");
        $estado = "Activo";
        $query = "INSERT INTO usuarios_token (UsuarioId,Token,Estado,Fecha)VALUES('$usuarioid','$token','$estado','$date')";
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $token;
        }else{
            return 0;
        }
    }*/
}