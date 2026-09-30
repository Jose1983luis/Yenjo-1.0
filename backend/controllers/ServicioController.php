<?php

require_once __DIR__ . '/../models/Servicio.php';

class ServicioController
{
    private Servicio $servicio;

    public function __construct(PDO $conexion)
    {
        $this->servicio = new Servicio($conexion);
    }

    public function obtenerTodos(): void
    {
        $servicios = $this->servicio->obtenerTodos();

        echo json_encode([
            'estado' => true,
            'datos' => $servicios
        ]);
    }

    public function obtenerPorId(int $id): void
    {
        $servicio = $this->servicio->obtenerPorId($id);

        if ($servicio) {
            echo json_encode([
                'estado' => true,
                'datos' => $servicio
            ]);
        } else {
            http_response_code(404);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'Servicio no encontrado'
            ]);
        }
    }

    public function crear(): void
    {
        $datos = json_decode(file_get_contents('php://input'), true);

        $camposRequeridos = [
            'nombre_servicio',
            'descripcion',
            'precio',
            'duracion',
            'estado'
        ];

        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || $datos[$campo] === '') {
                http_response_code(400);

                echo json_encode([
                    'estado' => false,
                    'mensaje' => "El campo {$campo} es obligatorio"
                ]);

                return;
            }
        }

        $resultado = $this->servicio->crear(
            $datos['nombre_servicio'],
            $datos['descripcion'],
            (float) $datos['precio'],
            (int) $datos['duracion'],
            $datos['estado']
        );

        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Servicio creado correctamente'
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible crear el servicio'
            ]);
        }
    }

    public function actualizar(int $id): void
    {
        $datos = json_decode(file_get_contents('php://input'), true);

        $camposRequeridos = [
            'nombre_servicio',
            'descripcion',
            'precio',
            'duracion',
            'estado'
        ];

        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || $datos[$campo] === '') {
                http_response_code(400);

                echo json_encode([
                    'estado' => false,
                    'mensaje' => "El campo {$campo} es obligatorio"
                ]);

                return;
            }
        }

        $resultado = $this->servicio->actualizar(
            $id,
            $datos['nombre_servicio'],
            $datos['descripcion'],
            (float) $datos['precio'],
            (int) $datos['duracion'],
            $datos['estado']
        );

        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Servicio actualizado correctamente'
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible actualizar el servicio'
            ]);
        }
    }

    public function eliminar(int $id): void
    {
        $resultado = $this->servicio->eliminar($id);

        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Servicio eliminado correctamente'
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible eliminar el servicio'
            ]);
        }
    }
}