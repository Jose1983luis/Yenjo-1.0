
<?php

require_once __DIR__ . '/../models/Cita.php';

class CitaController
{
    private Cita $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new Cita($conexion);
    }

    public function obtenerTodos(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if (!is_array($usuario) || !isset($usuario['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Acceso no autorizado. Debe iniciar sesión.'
            ]);
            return;
        }

        $idUsuario = (int) $usuario['id_usuario'];
        $rol = $usuario['nombre_rol'] ?? '';

        switch ($rol) {
            case 'Administrador':
                $citas = $this->modelo->obtenerTodos();
                break;

            case 'Cliente':
                $citas = $this->modelo->obtenerPorCliente($idUsuario);
                break;

            case 'Estilista':
                $citas = $this->modelo->obtenerPorEstilista($idUsuario);
                break;

            default:
                http_response_code(403);
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'No tiene permisos para consultar citas.'
                ]);
                return;
        }

        echo json_encode([
            'estado' => true,
            'datos' => $citas
        ]);
    }

    public function obtenerPorId(int $id): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if (!is_array($usuario) || !isset($usuario['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Acceso no autorizado. Debe iniciar sesión.'
            ]);
            return;
        }

        $cita = $this->modelo->obtenerPorId($id);

        if (!$cita) {
            http_response_code(404);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Cita no encontrada'
            ]);
            return;
        }

        $idUsuario = (int) $usuario['id_usuario'];
        $rol = $usuario['nombre_rol'] ?? '';

        $tieneAcceso = match ($rol) {
            'Administrador' => true,
            'Cliente' => (int) $cita['id_cliente'] === $idUsuario,
            'Estilista' => (int) $cita['id_estilista'] === $idUsuario,
            default => false
        };

        if (!$tieneAcceso) {
            http_response_code(403);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No tiene permisos para consultar esta cita.'
            ]);
            return;
        }

        echo json_encode([
            'estado' => true,
            'datos' => $cita
        ]);
    }

    public function crear(): void
    {

    $usuario = $_SESSION['usuario'] ?? null;

    if (!is_array($usuario) || !isset($usuario['id_usuario'])) {
        http_response_code(401);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acceso no autorizado. Debe iniciar sesión.'
        ]);
        return;
    }

    $rol = $usuario['nombre_rol'] ?? '';
    $idUsuario = (int) $usuario['id_usuario'];

    if (!in_array($rol, ['Administrador', 'Cliente'], true)) {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No tiene permisos para crear citas.'
        ]);
        return;
    }

        $datos = json_decode(file_get_contents('php://input'), true);

        if (!is_array($datos)) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'El cuerpo de la solicitud debe contener JSON válido'
            ]);
            return;
        }

        $camposRequeridos = [
            'id_cliente',
            'id_estilista',
            'id_servicio',
            'fecha',
            'hora',
            'estado'
        ];

        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || $datos[$campo] === '') {
                http_response_code(400);
                echo json_encode([
                    'estado' => false,
                    'mensaje' => "El campo $campo es obligatorio"
                ]);
                return;
            }
        }

        if (isset($datos['observaciones']) && !is_string($datos['observaciones'])) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'El campo observaciones debe ser texto'
            ]);
            return;
        }

    if (
        $rol === 'Cliente' &&
        (int) $datos['id_cliente'] !== $idUsuario
    ) {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No puede crear citas a nombre de otro cliente.'
        ]);
        return;
    }

        try {
            $creado = $this->modelo->crear($datos);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    'estado' => true,
                    'mensaje' => 'Cita creada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'No fue posible crear la cita'
                ]);
            }
        } catch (DomainException $e) {
    http_response_code(409);
    echo json_encode([
        'estado' => false,
        'mensaje' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'No fue posible crear la cita. Verifique los identificadores y los datos enviados.'
    ]);
}
    }

public function actualizar(int $id): void
{
    $usuario = $_SESSION['usuario'] ?? null;

    if (!is_array($usuario) || !isset($usuario['id_usuario'])) {
        http_response_code(401);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acceso no autorizado. Debe iniciar sesión.'
        ]);
        return;
    }

    $rol = $usuario['nombre_rol'] ?? '';

    if ($rol !== 'Administrador') {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Solo el administrador puede actualizar citas.'
        ]);
        return;
    }

    $citaExistente = $this->modelo->obtenerPorId($id);

    if (!$citaExistente) {
        http_response_code(404);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Cita no encontrada'
        ]);
        return;
    }

    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos)) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'El cuerpo de la solicitud debe contener JSON válido'
        ]);
        return;
    }

    $camposRequeridos = [
        'id_cliente',
        'id_estilista',
        'id_servicio',
        'fecha',
        'hora',
        'estado'
    ];

    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || $datos[$campo] === '') {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => "El campo $campo es obligatorio"
            ]);
            return;
        }
    }

    if (
        isset($datos['observaciones']) &&
        !is_string($datos['observaciones'])
    ) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'El campo observaciones debe ser texto'
        ]);
        return;
    }

    try {
        $actualizado = $this->modelo->actualizar($id, $datos);

        if ($actualizado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Cita actualizada correctamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible actualizar la cita'
            ]);
        }
    } catch (DomainException $e) {
    http_response_code(409);
    echo json_encode([
        'estado' => false,
        'mensaje' => $e->getMessage()
    ]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No fue posible actualizar la cita. Verifique los identificadores y los datos enviados.'
        ]);
    }
}

public function eliminar(int $id): void
{
    $usuario = $_SESSION['usuario'] ?? null;

    if (!is_array($usuario) || !isset($usuario['id_usuario'])) {
        http_response_code(401);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Acceso no autorizado. Debe iniciar sesión.'
        ]);
        return;
    }

    $rol = $usuario['nombre_rol'] ?? '';

    if ($rol !== 'Administrador') {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Solo el administrador puede eliminar citas.'
        ]);
        return;
    }

    $cita = $this->modelo->obtenerPorId($id);

    if (!$cita) {
        http_response_code(404);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Cita no encontrada'
        ]);
        return;
    }

    try {
        $eliminado = $this->modelo->eliminar($id);

        if ($eliminado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Cita eliminada correctamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible eliminar la cita'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No fue posible eliminar la cita'
        ]);
    }
}

public function cancelar(int $id): void
{
    $usuario = $_SESSION['usuario'] ?? null;

    if (!is_array($usuario) || !isset($usuario['id_usuario'])) {
        http_response_code(401);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Debe iniciar sesión para cancelar una cita.'
        ]);
        return;
    }

    if (($usuario['nombre_rol'] ?? '') !== 'Cliente') {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Solo el cliente puede cancelar sus propias citas.'
        ]);
        return;
    }

    $cita = $this->modelo->obtenerPorId($id);

    if (!$cita) {
        http_response_code(404);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Cita no encontrada.'
        ]);
        return;
    }

    if ((int) $cita['id_cliente'] !== (int) $usuario['id_usuario']) {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No puede cancelar una cita de otro cliente.'
        ]);
        return;
    }

    if ($cita['estado'] !== 'Programada') {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'La cita no está en un estado que permita cancelación.'
        ]);
        return;
    }

    try {
        $zonaHoraria = new DateTimeZone('America/Bogota');
        $fechaCita = new DateTimeImmutable(
            $cita['fecha'] . ' ' . $cita['hora'],
            $zonaHoraria
        );
        $ahora = new DateTimeImmutable('now', $zonaHoraria);
        $limite = $ahora->modify('+24 hours');

        if ($fechaCita < $limite) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'La cita solo puede cancelarse con al menos 24 horas de anticipación.'
            ]);
            return;
        }

        if ($this->modelo->cancelar($id)) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Cita cancelada correctamente.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible cancelar la cita.'
            ]);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Ocurrió un error al cancelar la cita.'
        ]);
    }
}

}
