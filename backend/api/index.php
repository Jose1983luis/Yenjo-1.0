<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../controllers/ServicioController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$recurso = $_GET['recurso'] ?? null;

if ($metodo === 'GET' && $recurso === 'usuarios') {

    $controller = new UsuarioController($conexion);

    if (isset($_GET['id'])) {

        $id = (int) $_GET['id'];

        $controller->obtenerPorId($id);

    } else {

        $controller->obtenerTodos();
    }

} elseif ($metodo === 'POST' && $recurso === 'usuarios') {

    $controller = new UsuarioController($conexion);

    $controller->crear();

} elseif ($metodo === 'PUT' && $recurso === 'usuarios') {

    $controller = new UsuarioController($conexion);

    $controller->actualizar((int) $_GET['id']);

} elseif ($metodo === 'DELETE' && $recurso === 'usuarios') {

    $controller = new UsuarioController($conexion);

    $controller->eliminar((int) $_GET['id']);

} elseif ($metodo === 'GET' && $recurso === 'servicios') {

    $controller = new ServicioController($conexion);

    if (isset($_GET['id'])) {

        $id = (int) $_GET['id'];

        $controller->obtenerPorId($id);

    } else {

        $controller->obtenerTodos();
    }

} elseif ($metodo === 'POST' && $recurso === 'servicios') {

    $controller = new ServicioController($conexion);

    $controller->crear();

} elseif ($metodo === 'PUT' && $recurso === 'servicios') {

    $controller = new ServicioController($conexion);

    $controller->actualizar((int) $_GET['id']);

} elseif ($metodo === 'DELETE' && $recurso === 'servicios') {

    $controller = new ServicioController($conexion);

    $controller->eliminar((int) $_GET['id']);

} else {

    echo json_encode([
        'mensaje' => 'API REST de YENJO 1.0 funcionando correctamente'
    ]);
}
