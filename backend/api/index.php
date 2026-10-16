<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../controllers/AutenticacionController.php';
require_once __DIR__ . '/../middleware/AutenticacionMiddleware.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../controllers/ServicioController.php';
require_once __DIR__ . '/../controllers/DisponibilidadController.php';
require_once __DIR__ . '/../controllers/CitaController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$recurso = $_GET['recurso'] ?? null;

// Protección del módulo Usuarios
// El registro público se separará de la administración.
if (
    $recurso === 'usuarios' &&
    !($metodo === 'POST' && !isset($_GET['id']))
) {
    if (!AutenticacionMiddleware::verificarRol(['Administrador'])) {
        exit;
    }
}

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
} elseif ($metodo === 'GET' && $recurso === 'disponibilidad') {
    $controller = new DisponibilidadController($conexion);
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $controller->obtenerPorId($id);
    } else {
        $controller->obtenerTodos();
    }
} elseif ($metodo === 'POST' && $recurso === 'disponibilidad') {
    $controller = new DisponibilidadController($conexion);
    $controller->crear();
} elseif ($metodo === 'PUT' && $recurso === 'disponibilidad') {
    $controller = new DisponibilidadController($conexion);
    $controller->actualizar((int) $_GET['id']);
} elseif ($metodo === 'DELETE' && $recurso === 'disponibilidad') {
    $controller = new DisponibilidadController($conexion);
    $controller->eliminar((int) $_GET['id']);

} elseif ($metodo === 'GET' && $recurso === 'citas') {
    $controller = new CitaController($conexion);
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $controller->obtenerPorId($id);
    } else {
        $controller->obtenerTodos();
    }
} elseif ($metodo === 'POST' && $recurso === 'citas') {
    $controller = new CitaController($conexion);
    $controller->crear();
} elseif ($metodo === 'PUT' && $recurso === 'citas') {
    $controller = new CitaController($conexion);
    $controller->actualizar((int) $_GET['id']);
} elseif ($metodo === 'DELETE' && $recurso === 'citas') {
    $controller = new CitaController($conexion);
    $controller->eliminar((int) $_GET['id']);
} elseif ($metodo === 'POST' && $recurso === 'auth' && ($_GET['accion'] ?? '') === 'login') {
    $controller = new AutenticacionController($conexion);
    $controller->iniciarSesion();
} elseif (
    $metodo === 'POST' &&
    $recurso === 'auth' &&
    ($_GET['accion'] ?? '') === 'logout') {
    $controller = new AutenticacionController($conexion);
    $controller->cerrarSesion();

    } else {
        
    echo json_encode([
        'mensaje' => 'API REST de YENJO 1.0 funcionando correctamente'
    ]);
}
