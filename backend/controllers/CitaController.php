
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
        echo json_encode([
            'estado' => true,
            'datos' => $this->modelo->obtenerTodos()
        ]);
    }

    public function obtenerPorId(int $id): void
    {
        $cita = $this->modelo->obtenerPorId($id);

        if ($cita) {
            echo json_encode([
                'estado' => true,
                'datos' => $cita
            ]);
        } else {
            http_response_code(404);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'Cita no encontrada'
            ]);
        }
    }

    public function crear(): void
    {
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

        try {
            $actualizado = $this->modelo->actualizar($id, $datos);

            if ($actualizado) {
                echo json_encode([
                    'estado' => true,
                    'mensaje' => 'Cita actualizada correctamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'No fue posible actualizar la cita'
                ]);
            }
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
        try {
            $eliminado = $this->modelo->eliminar($id);

            if ($eliminado) {
                echo json_encode([
                    'estado' => true,
                    'mensaje' => 'Cita eliminada correctamente'
                ]);
            } else {
                http_response_code(404);
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
}
