<?php

require_once __DIR__ . '/../models/Disponibilidad.php';

class DisponibilidadController
{
    private Disponibilidad $disponibilidad;

    public function __construct(PDO $conexion)
    {
        $this->disponibilidad = new Disponibilidad($conexion);
    }

    public function obtenerTodos(): void
    {
        $disponibilidades = $this->disponibilidad->obtenerTodos();

        echo json_encode([
            'estado' => true,
            'datos' => $disponibilidades
        ]);
    }

    public function obtenerPorId(int $id): void
    {
        $disponibilidad = $this->disponibilidad->obtenerPorId($id);

        if ($disponibilidad) {
            echo json_encode([
                'estado' => true,
                'datos' => $disponibilidad
            ]);
        } else {
            http_response_code(404);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'Disponibilidad no encontrada'
            ]);
        }
    }

    public function crear(): void
    {
        $datos = json_decode(file_get_contents('php://input'), true);
        
        $camposRequeridos = [
            'id_estilista',
            'dia_semana',
            'hora_inicio',
            'hora_fin',
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

        $resultado = $this->disponibilidad->crear(
            (int) $datos['id_estilista'],
            $datos['dia_semana'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $datos['estado']
        );

        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Disponibilidad creada correctamente'
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible crear la disponibilidad'
            ]);
        }
    }

    public function actualizar(int $id): void
    {
        $datos = json_decode(file_get_contents('php://input'), true);

        $camposRequeridos = [
            'id_estilista',
            'dia_semana',
            'hora_inicio',
            'hora_fin',
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

        $resultado = $this->disponibilidad->actualizar(
            $id,
            (int) $datos['id_estilista'],
            $datos['dia_semana'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $datos['estado']
        );

        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Disponibilidad actualizada correctamente'
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible actualizar la disponibilidad'
            ]);
        }
    }

    public function eliminar(int $id): void
    {
        $resultado = $this->disponibilidad->eliminar($id);

        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Disponibilidad eliminada correctamente'
            ]);
        } else {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible eliminar la disponibilidad'
            ]);
        }
    }
}