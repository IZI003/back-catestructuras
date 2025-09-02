<?php

require  __DIR__. '/../vendor/autoload.php'; // Autoload de Composer

use Dotenv\Dotenv;

class Database
{
    private $host;
    private $usuario;
    private $password;
    private $database;
    private $charset;

    public function __construct()
    {
        // Carga el .env
       $appEnv = getenv('APP_ENV') ?: 'prod';
          // Carga el .env correspondiente
          $dotenvFile = ".env.$appEnv";
          $dotenv = Dotenv::createImmutable(__DIR__ . '/../', $dotenvFile);
          $dotenv->load();

        $this->host     = $_ENV['DB_HOST'];
        $this->usuario  = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
        $this->database = $_ENV['DB_NAME'];
        $this->charset  = $_ENV['DB_CHARSET'];
    }
     public function getConnection()
     {
          // Corrección: Cambiar "database" por "dbname" y agregar charset
          $hostDB = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";

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