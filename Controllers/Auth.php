<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
require_once('../SERVICIOS/Auth.service.php');
require_once('../Modelos/Respuesta.php');


$_auth = new Auth;
$_respuesta = new Respuesta;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posbody = file_get_contents("php://input");
    $datosarray = $_auth->login($posbody);
    print_r(json_encode($datosarray));
} else {
}