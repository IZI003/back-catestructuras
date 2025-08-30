<?php
require_once PROJECT_ROOT_PATH . "/vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTmodel
{
    private $secret_Key='1s2sasdbgnjkkuilnbm';

    public function getjwt($user,$usuario_id,$password)
    {
        $date = new DateTimeImmutable("now", new DateTimeZone("UTC"));
        $expire_at     = $date->modify('+480 minutes')->getTimestamp();      // Add 60 seconds
        $domainName = "your.domain.name";
        $userId   = $usuario_id;
        $request_data = [
            'iat'  => $date->getTimestamp(),         // Issued at: time when the token was generated
            'iss'  => $domainName,                       // Issuer
            'nbf'  => $date->getTimestamp(),         // Not before
            'exp'  => $expire_at,                           // Expire
            'userId' => $userId, // User name
            'email' => $user, // User name

        ];
        
        return JWT::encode(
            $request_data,
            $this->secret_Key,
            'HS256'
        );
       // return $this->select("SELECT * FROM usuario WHERE n_usuario='$user' AND password='$password'");
    }

    public function getdesencripte($jwt)
    {
        $salida_error = new salidaError();
        $salida = new salida_error();
        try
        {
            try {
                $token = JWT::decode($jwt, new Key($this->secret_Key, 'HS256'));
            } catch (UnexpectedValueException $e) {
                $salida = $salida_error->response(401, "Token inválido: ". $e->getMessage());
                
                $this->sendOutput(
                    json_encode($salida),
                    array($salida->error->Descripcion, $salida->error->CodigoHttp)
                );
                exit;
            } catch (Exception $e) {
                $salida = $salida_error->response(401, "Error procesando el token: ". $e->getMessage());
                
                $this->sendOutput(
                    json_encode($salida),
                    array($salida->error->Descripcion, $salida->error->CodigoHttp)
                );
                exit;
            }
            
            $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));
            $serverName = "your.domain.name";
    
            if ($token->iss !== $serverName ||
                $token->nbf > $now->getTimestamp() ||
                $token->exp < $now->getTimestamp())
            {
                $salida = $salida_error->response(401, "token expirado");

                $this->sendOutput(
                    json_encode($salida),
                    array($salida->error->Descripcion, $salida->error->CodigoHttp)
                );
                exit;
            }
            
            return $token;
        }catch(Exception $e) 
        {
            $salida = $salida_error->response(401, "Unauthorized".$e->getMessage());
                $this->sendOutput(
                    json_encode($salida),
                    array($salida->error->Descripcion, $salida->error->CodigoHttp)
                );
            exit;
        }   
        // return $this->select("SELECT * FROM usuario WHERE n_usuario='$user' AND password='$password'");
    }
    
    public function datatime()
    {
        $date = new DateTimeImmutable("now", new DateTimeZone("UTC"));
        return $date->getTimestamp();
    }

    public function getdesencripte_manual($token)
    {
        list($header, $payload, $signature) = explode('.', $token);
        // Decodificar el header y payload usando la función auxiliar
        $headerDecoded = json_decode($this->base64UrlDecode($header));
        $payloadDecoded = json_decode($this->base64UrlDecode($payload));
        
        // Recalcular la firma
        $headerAndPayload = $header . '.' . $payload;
        $calculatedSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', $headerAndPayload, $this->secret_Key, true)), '+/', '-_'), '=');
    
        // Validar la expiración
        if (isset($payloadDecoded->exp)) {
            if ($payloadDecoded->exp > time()) {
                if ($calculatedSignature === $signature) {
                    return $payloadDecoded;
                } else {
                    return "El token es inválido.\n";
                }
            } else {
                return "El token ha expirado.\n";
            }
        } else {
            return "El campo exp no está presente en el token.\n";
        }
    }
    
    function base64UrlDecode($data) {
        // Reemplazar los caracteres base64url por los de base64 estándar
        $base64 = strtr($data, '-_', '+/');
        // Añadir relleno si es necesario
        $padding = strlen($base64) % 4;
        if ($padding) {
            $base64 .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($base64);
    }
    
    private function sendOutput($data, $httpHeaders = array())
    {
        header_remove('Set-Cookie');

        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }

        echo $data;
        exit;
    }
}
?>