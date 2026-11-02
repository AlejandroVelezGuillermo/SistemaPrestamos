<?php

require_once __DIR__ . "/../models/Auth.php";

class AuthController
{
    private $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function login($correo, $password)
    {
        $usuario = $this->auth->buscarPorCorreo($correo);

        if (!$usuario) {
            return [
                "success" => false,
                "message" => "Correo o contraseña incorrectos"
            ];
        }

        if ($usuario["estatus"] !== "activo") {
            return [
                "success" => false,
                "message" => "El usuario no está activo"
            ];
        }

        if (!password_verify($password, $usuario["password"])) {
            return [
                "success" => false,
                "message" => "Correo o contraseña incorrectos"
            ];
        }

        unset($usuario["password"]);

        return [
            "success" => true,
            "message" => "Inicio de sesión correcto",
            "usuario" => $usuario
        ];
    }
}