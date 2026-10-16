<?php

require_once __DIR__ . '/../models/Autenticacion.php';

class AutenticacionController
{
    private Autenticacion $autenticacion;

    public function __construct(PDO $conexion)
    {
        $this->autenticacion = new Autenticacion($conexion);
    }

    public function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Datos JSON no válidos'
            ]);
            return;
        }

        $correo = trim($datos['correo'] ?? '');
        $password = $datos['password'] ?? '';

        if ($correo === '' || $password === '') {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'El correo y la contraseña son obligatorios'
            ]);
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'El formato del correo no es válido'
            ]);
            return;
        }

        $usuario = $this->autenticacion->obtenerPorCorreo($correo);

        if (
            !$usuario ||
            $usuario['estado'] !== 'Activo' ||
            !$this->autenticacion->verificarPassword(
                $password,
                $usuario['password_hash']
            )
        ) {
            http_response_code(401);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Credenciales incorrectas o cuenta inactiva'
            ]);
            return;
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id_usuario' => $usuario['id_usuario'],
            'id_rol' => $usuario['id_rol'],
            'nombre_rol' => $usuario['nombre_rol'],
            'nombre' => $usuario['nombre'],
            'correo_electronico' => $usuario['correo_electronico']
        ];

        unset($usuario['password_hash']);

        echo json_encode([
            'estado' => true,
            'mensaje' => 'Inicio de sesión correcto',
            'datos' => $usuario
        ]);
    }

    public function cerrarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                $parametros['secure'],
                $parametros['httponly']
            );
        }

        session_destroy();

        echo json_encode([
            'estado' => true,
            'mensaje' => 'Sesión cerrada correctamente'
        ]);
    }

}
