
<?php

class AutenticacionMiddleware
{
    public static function verificarSesion(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (
            !isset($_SESSION['usuario']) ||
            !isset($_SESSION['usuario']['id_usuario'])
        ) {
            http_response_code(401);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'Acceso no autorizado. Debe iniciar sesión.'
            ]);

            return false;
        }

        return true;
    }

    public static function verificarRol(array $rolesPermitidos): bool
    {
        if (!self::verificarSesion()) {
            return false;
        }

        $rolUsuario = $_SESSION['usuario']['nombre_rol'] ?? '';

        if (!in_array($rolUsuario, $rolesPermitidos, true)) {
            http_response_code(403);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No tiene permisos para realizar esta operación.'
            ]);

            return false;
        }

        return true;
    }
}
