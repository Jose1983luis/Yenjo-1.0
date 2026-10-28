
<?php

require_once __DIR__ . '/../models/Pago.php';

class PagoController
{
    private Pago $pago;

    public function __construct(PDO $conexion)
    {
        $this->pago = new Pago($conexion);
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

    $rol = $usuario['nombre_rol'] ?? '';

    if ($rol === 'Administrador') {
        echo json_encode([
            'estado' => true,
            'datos' => $this->pago->obtenerTodos()
        ]);
        return;
    }

    if ($rol === 'Cliente') {
        $idCliente = (int) $usuario['id_usuario'];

        echo json_encode([
            'estado' => true,
            'datos' => $this->pago->obtenerPorCliente($idCliente)
        ]);
        return;
    }

    http_response_code(403);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'No tiene permisos para consultar pagos.'
    ]);
}

    public function obtenerPorId($id): void
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

    if ($rol === 'Administrador') {
        $pago = $this->pago->obtenerPorId((int) $id);
    } elseif ($rol === 'Cliente') {
        $pago = $this->pago->obtenerPorIdCliente(
            (int) $id,
            (int) $usuario['id_usuario']
        );
    } else {
        http_response_code(403);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'No tiene permisos para consultar este pago.'
        ]);
        return;
    }

    if ($pago === false) {
        http_response_code(404);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Pago no encontrado.'
        ]);
        return;
    }

    echo json_encode([
        'estado' => true,
        'datos' => $pago
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

if (($usuario['nombre_rol'] ?? '') !== 'Administrador') {
    http_response_code(403);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'No tiene permisos para registrar pagos.'
    ]);
    return;
}
        $datos = json_decode(file_get_contents('php://input'), true);

        if (
            !is_array($datos) ||
            !isset(
                $datos['id_cita'],
                $datos['valor'],
                $datos['fecha_pago'],
                $datos['metodo_pago']
            )
        ) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Faltan datos obligatorios del pago'
            ]);
            return;
        }

        $metodosPermitidos = ['Efectivo', 'Transferencia', 'Tarjeta'];
        $estadosPermitidos = ['Pendiente', 'Pagado', 'Reembolsado'];

        if (
            !is_numeric($datos['id_cita']) ||
            (int) $datos['id_cita'] <= 0 ||
            !is_numeric($datos['valor']) ||
            (float) $datos['valor'] <= 0 ||
            !in_array($datos['metodo_pago'], $metodosPermitidos, true) ||
            (isset($datos['estado']) &&
                !in_array($datos['estado'], $estadosPermitidos, true))
        ) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Los datos del pago no son válidos'
            ]);
            return;
        }

        $datos['id_cita'] = (int) $datos['id_cita'];
        $datos['estado'] = $datos['estado'] ?? 'Pendiente';

        try {
            if ($this->pago->crear($datos)) {
                http_response_code(201);
                echo json_encode([
                    'estado' => true,
                    'mensaje' => 'Pago registrado correctamente'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No se pudo registrar el pago. Verifique que la cita exista y no tenga un pago registrado.'
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

if (($usuario['nombre_rol'] ?? '') !== 'Administrador') {
    http_response_code(403);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'No tiene permisos para actualizar pagos.'
    ]);
    return;
}
        $datos = json_decode(file_get_contents('php://input'), true);

        if (
            !is_array($datos) ||
            !isset(
                $datos['id_cita'],
                $datos['valor'],
                $datos['fecha_pago'],
                $datos['metodo_pago'],
                $datos['estado']
            )
        ) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Faltan datos obligatorios para actualizar el pago'
            ]);
            return;
        }

        $metodosPermitidos = ['Efectivo', 'Transferencia', 'Tarjeta'];
        $estadosPermitidos = ['Pendiente', 'Pagado', 'Reembolsado'];

        if (
            !is_numeric($datos['id_cita']) ||
            (int) $datos['id_cita'] <= 0 ||
            !is_numeric($datos['valor']) ||
            (float) $datos['valor'] <= 0 ||
            !in_array($datos['metodo_pago'], $metodosPermitidos, true) ||
            !in_array($datos['estado'], $estadosPermitidos, true)
        ) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Los datos del pago no son válidos'
            ]);
            return;
        }

        $datos['id_cita'] = (int) $datos['id_cita'];

        try {
            if ($this->pago->actualizar($id, $datos)) {
                echo json_encode([
                    'estado' => true,
                    'mensaje' => 'Pago actualizado correctamente'
              ]);
          } else {
              http_response_code(404);
              echo json_encode([
                  'estado' => false,
                  'mensaje' => 'No se encontró el pago para actualizar'
           ]);
         }
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No se pudo actualizar el pago. Verifique que la cita exista y no tenga otro pago registrado.'
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

if (($usuario['nombre_rol'] ?? '') !== 'Administrador') {
    http_response_code(403);
    echo json_encode([
        'estado' => false,
        'mensaje' => 'No tiene permisos para eliminar pagos.'
    ]);
    return;
}
        if ($this->pago->eliminar($id)) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Pago eliminado correctamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'No se encontró el pago para eliminar'
            ]);
        }
    }
}
