<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/ChatService.php';
require_once __DIR__ . '/../MiLog.php';
require_once __DIR__ . '/../Modelos/JWTmodel.php';
require_once __DIR__ . "/../INCLUDES/salidaError.php";

class ChatController extends BaseController
{
    private $chatService;
   
    public function streamChat() {
        session_start();
        $this->chatService =  new ChatService();
        
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        $var = $this->getQueryStringParams();

        $jwt = $var['token'];
            if (empty($jwt))
                {
                    $salida = $salida_error->response(401, '');
                    $this->sendOutput(
                        json_encode($salida),
                        array($salida->error->Descripcion, $salida->error->CodigoHttp)
                    );
             }else{
                
          //writeLog("ChatController.streamChat. antes de JWTmodel");
            
            $jwtmodel = new JWTmodel();
            $tokenDecodificado = $jwtmodel->getdesencripte($jwt); // $jwt es el token recibido
          //  writeLog("ChatController.streamChat. token Decodificado".$tokenDecodificado);

            // Si el token es válido, $tokenDecodificado es un objeto y puedes obtener el ID:
            $userId = $tokenDecodificado->userId;
         //   writeLog("ChatController.streamChat. userId".$userId);
            
            if (!isset($userId)) {
         //   writeLog("ChatController.streamChat. dentro del if".$userId);

                $salida = $salida_error->response(401, '');
                    $this->sendOutput(
                        json_encode($salida),
                        array($salida->error->Descripcion, $salida->error->CodigoHttp)
                    );
            }
          //  writeLog("ChatController.streamChat. antes del if active_connections");
            
            // 🔥 Verificar si el usuario ya tiene una conexión activa
            if (isset($_SESSION['active_connections'][$userId])) {
          //  writeLog("ChatController.streamChat. Entro al if active_connections");

             /*   header('HTTP/1.1 429 Too Many Requests');
                echo json_encode(["error" => "Ya tienes una conexión activa"]);
                exit;*/
                $salida = $salida_error->response(200, '');
                $salida->datos = 'Ya tienes una conexión activa';

          //  writeLog("ChatController.streamChat. OK");
                
                    $this->sendOutput(
                        json_encode($salida),
                        array($salida->error->Descripcion, $salida->error->CodigoHttp)
                    );
            }

           // writeLog("ChatController.streamChat. INicia uno nuevo");
            
            // Configurar cabeceras SSE
            header("Content-Type: text/event-stream");
            header("Cache-Control: no-cache");
            header("Connection: keep-alive");
            session_write_close(); // 🔥 Evita bloqueos en sesiones PHP

            // Variable para rastrear el último mensaje procesado
            $lastMessageId = 0;
          //  $salida_error = new salidaError();
            // 🔥 Controlamos la cantidad de intentos para evitar sobrecarga en el servidor
            $maxAttempts = 30; // Permitimos 30 ciclos (~60 segundos si dormimos 2 segs)
            $attempts = 0;
            
            // Bucle infinito para enviar mensajes cada cierto tiempo
            while (true) {
                // Si el cliente se desconecta, termina el script.
                if (connection_aborted()) {
                    break;
                }

                // Consultar nuevos mensajes para el usuario (por ejemplo, aquellos con ID mayor al último procesado)
                $messages = $this->chatService->getNewMessagesForConversation($userId, $lastMessageId);

                if (!empty($messages)) {
                   // writeLog("Mensajes encontrados: " . json_encode($messages));
                    // Actualizar $lastMessageId al ID del mensaje más reciente de este lote
                    $lastMessageId = end($messages)['id'];

                    // Enviar los datos en formato SSE
                    echo "data: " . json_encode($messages) . "\n\n";
                   
                    // Forzar el envío inmediato
                    ob_flush();
                    flush();
                }

                // Esperar 2 segundos antes de volver a consultar
                sleep(2);
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    break; // 🔥 Cierra la conexión después de 60 segundos
                }
            }
            $salida = $salida_error->response(200, '');
                $salida->datos = 'conexión activa';
                
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
        }
    }
    
    public function chat($method)
    {
        $this->chatService =  new ChatService();

        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['chatlista']) && $var['chatlista']) 
            {
                 try 
                 {
                    $list_chat = $this->chatService->getLastMessages($var['chatlista']);
                    
                    if (!($list_chat)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $list_chat;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }else
            {
                $salida = $salida_error->response(500, "Datos requeridos");
                writeLog("ProductoControllerproducto. Datos requeridos", '/logs/app.log');
            }
        }
        
        if ($method === 'post') {
            $var = $this->PostFromData();
            //writeLog("ChatController.chat. POST");
        
               try {
                    $sender_id = $var['sender_id'];
                    $receiver_id = $var['receiver_id'];
                    $message = $var['message'];
                    $type = $var['type'];
                    $file_url  = !isset($var['file_url']) ?"" : $var['file_url'];
                  //  writeLog("ChatController.chat. llamando al sendmessage");

                    $result = $this->chatService->sendMessage($sender_id, $receiver_id, $message, $type, $file_url);
                    
                     if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                     
                } catch (Error $e) 
                {
                    writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    
    public function listchat($method)
    {
     //   writeLog("ChatController.listchat. Inicio");

        $this->chatService =  new ChatService();

        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id_user']) && $var['id_user']) 
            {
                 try 
                 {
                //    writeLog("ChatController.listchat. dentro del if ");

                    $list_chat = $this->chatService->getListMessages($var['id_user']);
                   // writeLog("ChatController.listchat. llamando getListMessages ".json_encode($list_chat));
                    
                    if (!($list_chat)) {
                  //  writeLog("ChatController.listchat. list_chat vacio");

                        $salida = $salida_error->response(204, '');
                        $salida->datos = $list_chat;
                        
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $list_chat;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }else
            {
                $salida = $salida_error->response(500, "Datos requeridos");
                writeLog("ProductoControllerproducto. Datos requeridos");
            }
        }
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function nuevoChat($method) {
        $this->chatService =  new ChatService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method !== 'post') {
            $salida = $salida_error->response(405, "Método no permitido");
        }
        
        if ($method === 'post') {
            $var = $this->PostFromData();
               try {

                $envia_id = $var['envia_id'];
                $recibe_id = $var['recibe_id'];

                if(!is_numeric($envia_id) || !is_numeric($recibe_id)  )
                {
                    $salida = $salida_error->response(500, "Formatos de datos incorrecto");
                }
                  //  writeLog("ChatController.chat. llamando al sendmessage");
                    $result = $this->chatService-> nuevoChat($envia_id, $recibe_id);
                    
                     if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                     
                } catch (Error $e) 
                {
                    writeLog("ChatController.nuevoChat. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );    
    }

    public function chatvisto($method)
    {
        $this->chatService =  new ChatService();

        $salida_error = new salidaError();
        $salida = new salida_error();        
        if ($method === 'put') {
            $var = $this->PostFromData();
            //writeLog("ChatController.chat. POST");
        
               try {
                    $id_chat_list = $var['chatlist'];
                    $recibe_id = $var['recibe_id'];

                    if(!is_numeric($id_chat_list) || !is_numeric($recibe_id)  )
                    {
                        $salida = $salida_error->response(500, "Formatos de datos incorrecto");
                    }
                   // writeLog("ChatController.chat. llamando al sendmessage");

                    $result = $this->chatService->ChatVisto($id_chat_list, $recibe_id);
                    
                     if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                     
                } catch (Error $e) 
                {
                    writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
}
?>