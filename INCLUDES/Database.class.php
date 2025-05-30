<?php
class Database
{
     private $host = 'localhost';
     private $usuario = 'catestructuras';
     private $password = '@hBG*lo[_tAp9jq1';
     private $database = 'catestructuras';

     public function getConnection()
     {
          // Corrección: Cambiar "database" por "dbname" y agregar charset
          $hostDB = "mysql:host=" . $this->host . ";dbname=" . $this->database . ";charset=utf8";

          try {
               $connection = new PDO($hostDB, $this->usuario, $this->password);
               // Configurar PDO para lanzar excepciones en caso de error
               $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

               return $connection;
          } catch (PDOException $e) {
               // Mensaje de error más seguro (evitar mostrar información sensible)
               die("ERROR en la conexión a la base de datos: " . $e->getMessage());
          }
     }
}