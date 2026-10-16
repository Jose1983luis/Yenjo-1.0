
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
        echo json_encode([
            'estado' => true,
            'datos' => $this->pago->obtenerTodos()
        ]);
    }

    public function obtenerPorId(int $id): void
    {
        $pago = $this->pago->obtenerPorId($id);

        if ($pago) {
            echo json_encode([
                'estado' => true,
                'datos' => $pago
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Pago no encontrado'
            ]);
        }
    }

    public function crear(): void
    {
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
