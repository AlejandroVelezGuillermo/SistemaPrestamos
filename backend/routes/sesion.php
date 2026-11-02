<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");


if (isset($_SESSION["usuario"])) {

    echo json_encode([

        "success" => true,

        "sesion" => true,

        "usuario" => $_SESSION["usuario"]

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


echo json_encode([

    "success" => true,

    "sesion" => false,

    "usuario" => null

], JSON_UNESCAPED_UNICODE);