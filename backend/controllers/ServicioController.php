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

    if (!is_array($datos)) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'El cuerpo de la solicitud debe contener JSON válido'
        ]);
        return;
    }

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

    $estadosPermitidos = ['Activo', 'Inactivo'];

    if (
        !is_string($datos['nombre_servicio']) ||
        trim($datos['nombre_servicio']) === '' ||
        !is_string($datos['descripcion']) ||
        trim($datos['descripcion']) === '' ||
        !is_numeric($datos['precio']) ||
        (float) $datos['precio'] <= 0 ||
        filter_var($datos['duracion'], FILTER_VALIDATE_INT) === false ||
        (int) $datos['duracion'] <= 0 ||
        !is_string($datos['estado']) ||
        !in_array($datos['estado'], $estadosPermitidos, true)
    ) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Los datos del servicio no son válidos'
        ]);
        return;
    }

    try {
        $resultado = $this->servicio->crear(
            trim($datos['nombre_servicio']),
            trim($datos['descripcion']),
            (float) $datos['precio'],
            (int) $datos['duracion'],
            $datos['estado']
        );

        if ($resultado) {
            http_response_code(201);
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
    } catch (PDOException $e) {
        if (
    $e->getCode() === '23000' &&
    str_contains($e->getMessage(), 'uq_servicio_nombre')
) {
            http_response_code(409);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Ya existe un servicio con ese nombre'
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Ocurrió un error al crear el servicio'
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

    $estadosPermitidos = ['Activo', 'Inactivo'];

    if (
        $id <= 0 ||
        !is_string($datos['nombre_servicio']) ||
        trim($datos['nombre_servicio']) === '' ||
        !is_string($datos['descripcion']) ||
        trim($datos['descripcion']) === '' ||
        !is_numeric($datos['precio']) ||
        (float) $datos['precio'] <= 0 ||
        filter_var($datos['duracion'], FILTER_VALIDATE_INT) === false ||
        (int) $datos['duracion'] <= 0 ||
        !is_string($datos['estado']) ||
        !in_array($datos['estado'], $estadosPermitidos, true)
    ) {
        http_response_code(400);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Los datos del servicio no son válidos'
        ]);
        return;
    }

    try {
        $resultado = $this->servicio->actualizar(
            $id,
            trim($datos['nombre_servicio']),
            trim($datos['descripcion']),
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
    } catch (PDOException $e) {
        if (
    $e->getCode() === '23000' &&
    str_contains($e->getMessage(), 'uq_servicio_nombre')
) {
            http_response_code(409);
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Ya existe otro servicio con ese nombre'
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Ocurrió un error al actualizar el servicio'
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