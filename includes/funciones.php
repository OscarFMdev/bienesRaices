<?php

require 'app.php';


function incluirTemplate( string $nombre, bool $inicio = false ) {
    include TEMPLATES_URL . "/${nombre}.php";
}

function estaAutenticado(): bool {
    session_start();

    $auth = $_SESSION['login'] ?? false;

    if($auth) {
        return true;
    } //Como rturn hace que no se ejecute lo demás esto es equivalente a poner un else
    return false;
}