<?php
class funciones
{
    public function encriptacion($password)
    {
        return md5($password);
    }
}