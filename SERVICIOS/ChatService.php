<?php
require_once __DIR__ . '/../MiLog.php';
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';

class ChatService {
    private $dbHandler; // Instancia de PDO
    private $table = 'chat_messages';
    
    public function __construct() {
        $this->dbHandler = new DatabaseHandler();

    }

    /**
     * Retorna los últimos 20 mensajes entre dos usuarios, ordenados de forma ascendente (los más antiguos primero).
     *
     * @param int $chatlista ID la lista de chat
     * @return array Últimos 20 mensajes (array asociativo)
     */
    public function getLastMessages($chatlista){
        
        $sql = "SELECT chat.id, chat.envia_id, 
                 COALESCE(NULLIF(tiend.nombre, ''),
                        CONCAT(us.nombre, ' ', us.apellido) 
                        ) AS envia, 
                 chat.recibe_id, 
                COALESCE(NULLIF(tiend1.nombre, ''),
                        CONCAT(us1.nombre, ' ', us1.apellido) 
                        ) AS recibe,
                chat.message,chat.type as tipo, chat.file_url, chat.leido, chat.id_chat_list 
                FROM chat_messages chat
                left join usuarios us on us.usuario_id = chat.envia_id
                LEFT JOIN tienda tiend ON tiend.id_vendedor = us.usuario_id
                left join usuarios us1 on us1.usuario_id = chat.recibe_id
                LEFT JOIN tienda tiend1 ON tiend1.id_vendedor = us1.usuario_id
                WHERE chat.id_chat_list =".$chatlista." 
                ORDER BY created_at DESC
                LIMIT 20";
                
                $stmt = $this->dbHandler->querySrting($sql); //$this->dbHandler->prepare($sql);
        // Opcional: invertir el orden para mostrarlos cronológicamente
        return array_reverse($stmt);
    }

    /**
     * Inserta un nuevo mensaje en la base de datos.
     *
     * @param int $sender_id ID del remitente
     * @param int $receiver_id ID del destinatario
     * @param string $message Contenido del mensaje (puede estar vacío si es imagen)
     * @param string $type Tipo de mensaje: 'text', 'image', 'url'
     * @param string|null $file_url URL del archivo (si el mensaje es de imagen o link)
     * @return bool true si se inserta correctamente, false en caso contrario.
     */
    public function sendMessage($sender_id, $receiver_id, $message, $type = 'texto', $file_url = null) {
     //   writeLog("ChatService.sendMessage. INICIO");
        
        $sql = "SELECT id FROM chat_list
                WHERE ((`id_usuario1` = ".$sender_id." AND `id_usuario2` = ".$receiver_id.")
                 OR  (`id_usuario1` = ".$receiver_id." AND `id_usuario2` = ".$sender_id."))";
                 
        $stmt = $this->dbHandler->querySrting($sql); 
        $id_conversacion=0;
        
        if(isset($stmt[0]['id']))
        {

            $id_conversacion = $stmt[0]['id'];
        }else
        {
            $data =[
                "id_usuario1" => $sender_id,
                "id_usuario2" => $receiver_id,
            ];

            $id_conversacion = $this->dbHandler->insert("chat_list",  $data);
           // writeLog("ChatService.sendMessage. resultado del INSERT = ".json_encode($result) );
        }
       // writeLog("ChatService.sendMessage. por llamar a ChatVisto");
                
        $this->ChatVisto($id_conversacion, $receiver_id);
        $sql = "INSERT INTO chat_messages (envia_id, recibe_id, message, type, file_url, id_chat_list, created_at)
                VALUES (".$sender_id.", ".$receiver_id.", '".$message."', '".$type."', 
                '".$file_url."', ".$id_conversacion.", '".date('Y-m-d H:i:s')."')";
        
        return $this->dbHandler->querySrting($sql);
    }

    public function getListMessages($user1) {
          
             $sql="SELECT 
                    cl.ID AS id,
                    ".$user1." AS idUsuario,
                        CASE 
                        WHEN t.last_message IS NOT NULL AND LENGTH(t.last_message) > 20 
                        THEN CONCAT(LEFT(t.last_message, 20), '...')
                        ELSE t.last_message
                    END AS ultimoMensaje,
                    COALESCE(t.unread_count, 0) AS unreadCount,
                    u.usuario_id AS idrecepcion,
                    COALESCE(NULLIF(tiend.nombre, ''),-- si existe y no está vacío
                    CONCAT(u.nombre, ' ', u.apellido)-- si no, nombre + apellido del usuario
                ) AS nombre
                FROM chat_list cl
                LEFT JOIN (
                    SELECT 
                        cm.id_chat_list,
                        cm.message AS last_message,
                        cm.created_at,
                        ROW_NUMBER() OVER (PARTITION BY cm.id_chat_list ORDER BY cm.created_at DESC) AS rn,
                        SUM(CASE WHEN cm.recibe_id = ".$user1." AND cm.leido = 1 THEN 1 ELSE 0 END)
                            OVER (PARTITION BY cm.id_chat_list) AS unread_count
                    FROM chat_messages cm
                    WHERE (cm.envia_id = ".$user1." OR cm.recibe_id = ".$user1.")
                ) t 
                ON cl.ID = t.id_chat_list AND t.rn = 1
                -- Determinar cuál es el otro usuario en la conversación:
                JOIN usuarios u ON u.usuario_id = ( 
                CASE 
                    WHEN cl.id_usuario1 = ".$user1." THEN cl.id_usuario2 
                    ELSE cl.id_usuario1 
                END)
                LEFT JOIN tienda tiend ON tiend.id_vendedor = u.usuario_id

                WHERE ".$user1." IN (cl.id_usuario1, cl.id_usuario2)";
               
        return $this->dbHandler->querySrting($sql); 
    }

    public function getNewMessagesForConversation($userId, $lastMessageId) {
        
        $sql = "SELECT chat.id, chat.envia_id, 
                        COALESCE(NULLIF(tiend.nombre, ''),
                        CONCAT(us.nombre, ' ', us.apellido) 
                        ) AS envia, 
                        chat.recibe_id, 
                        COALESCE(NULLIF(tiend1.nombre, ''),
                        CONCAT(us1.nombre, ' ', us1.apellido) 
                        ) AS recibe,
                        chat.message, chat.type as tipo, chat.file_url, 
                        chat.leido, chat.id_chat_list, 
                        IF(DATE(chat.created_at) = CURDATE(), 
                        TIME_FORMAT(chat.created_at, '%H:%i'), 
                        DATE_FORMAT(chat.created_at, '%d de %M %H:%i')) 
                        AS hora
                FROM chat_messages chat
                LEFT JOIN usuarios us ON us.usuario_id = chat.envia_id
                LEFT JOIN tienda tiend ON tiend.id_vendedor = us.usuario_id
                LEFT JOIN usuarios us1 ON us1.usuario_id = chat.recibe_id    
                LEFT JOIN tienda tiend1 ON tiend1.id_vendedor = us1.usuario_id

                WHERE chat.id > ".$lastMessageId."
                AND (chat.envia_id = ".$userId." OR chat.recibe_id = ".$userId.")                
                ORDER BY chat.created_at DESC
                LIMIT 20";
 
     //   writeLog("ChatService.getNewMessagesForConversation. SQL ". $sql);
     $this->dbHandler->querySrting("SET lc_time_names = 'es_ES'");
        $stmt = $this->dbHandler->querySrting($sql); 
        return array_reverse($stmt);
    }

    public function nuevoChat($envia_id, $recibe_id) { 
        // Verificar si la conversación ya existe
        $sql = "SELECT t.ID as id, ".$envia_id." as idUsuario, '' as ultimoMensaje, 0 as unreadCount,                    u.usuario_id  as idrecepcion,
                     COALESCE(NULLIF(tiend.nombre, ''),
                     CONCAT(u.nombre, ' ', u.apellido) 
                     ) AS nombre
                FROM chat_list t
                INNER JOIN usuarios u ON u.usuario_id =".$recibe_id."
                LEFT JOIN tienda tiend ON tiend.id_vendedor= u.usuario_id 
                WHERE (t.id_usuario1 = ".$envia_id." AND t.id_usuario2 = ".$recibe_id.") OR 
                	  (t.id_usuario1 = ".$recibe_id." AND t.id_usuario2 = ".$envia_id.")";                
                
        $resultado = $this->dbHandler->querySrting($sql);
    
        if ($resultado) {
        return $resultado; 
          //  exit();
        }    
        
        // Si no existe, crear una nueva conversación
        $sqlInsert = "INSERT INTO chat_list (id_usuario1, id_usuario2) VALUES (".$envia_id.", ".$recibe_id.")";
        
        $nuevoId = $this->dbHandler->querySrting($sqlInsert);

        $sql="SELECT t.ID as id, ".$envia_id." as idUsuario, '' as ultimoMensaje, 0 as unreadCount, 
                    u.usuario_id  as idrecepcion,
                    COALESCE(NULLIF(tiend.nombre, ''),
                     CONCAT(u.nombre, ' ', u.apellido) 
                     ) AS nombre
                FROM chat_list t
                JOIN usuarios u ON u.usuario_id = ".$recibe_id."
                LEFT JOIN tienda tiend ON tiend.id_vendedor= u.usuario_id 
                WHERE (t.id_usuario1 = ".$envia_id." AND t.id_usuario2 = ".$recibe_id.") OR 
                	  (t.id_usuario1 = ".$recibe_id." AND t.id_usuario2 = ".$envia_id.")";
                          
        return $this->dbHandler->querySrting($sql); 
    }   
    
    public function ChatVisto($id_chat_list, $recibe_id) { 
    //    writeLog("ChatService.ChatVisto. INICIO");
        
        $sql = "UPDATE chat_messages
                SET leido= 0 
                WHERE envia_id=".$recibe_id." and id_chat_list=".$id_chat_list;                     
        writeLog("ChatService.ChatVisto. sql =".$sql);
                
        return $this->dbHandler->querySrting($sql);
    }   
}
?>