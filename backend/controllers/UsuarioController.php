<?php

require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController
{
    private Usuario $usuario;

    public function __construct(PDO $conexion)
    {
        $this->usuario = new Usuario($conexion);
    }

    public function obtenerTodos(): void
    {
        $usuarios = $this->usuario->obtenerTodos();

        echo json_encode([
            'estado' => true,
            'datos' => $usuarios
        ]);
    }

public function obtenerPorId(int $id): void
{
    $usuario = $this->usuario->obtenerPorId($id);

    if ($usuario) {
        echo json_encode([
            'estado' => true,
            'datos' => $usuario
        ]);
    } else {
        http_response_code(404);

        echo json_encode([
            'estado' => false,
            'mensaje' => 'Usuario no encontrado'
        ]);
    }
}

public function crear(): void
{
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!$datos) {
        http_response_code(400);

        echo json_encode([
            'estado' => false,
            'mensaje' => 'Datos JSON no válidos'
        ]);

        return;
    }

    $camposRequeridos = [
        'id_rol',
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'password'
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

    $passwordHash = password_hash(
        $datos['password'],
        PASSWORD_DEFAULT
    );

    $fechaRegistro = date('Y-m-d H:i:s');
    $estado = 'Activo';

    $resultado = $this->usuario->crear(
        (int) $datos['id_rol'],
        $datos['nombre'],
        $datos['apellido'],
        $datos['correo'],
        $datos['telefono'],
        $passwordHash,
        $fechaRegistro,
        $estado
    );

    if ($resultado) {
        http_response_code(201);

        echo json_encode([
            'estado' => true,
            'mensaje' => 'Usuario creado correctamente'
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            'estado' => false,
            'mensaje' => 'No fue posible crear el usuario'
        ]);
    }
}

public function actualizar(int $id): void
{
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!$datos) {
        http_response_code(400);

        echo json_encode([
            'estado' => false,
            'mensaje' => 'Datos JSON no válidos'
        ]);

        return;
    }

    $camposRequeridos = [
        'id_rol',
        'nombre',
        'apellido',
        'correo',
        'telefono',
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

    $resultado = $this->usuario->actualizar(
        $id,
        (int) $datos['id_rol'],
        $datos['nombre'],
        $datos['apellido'],
        $datos['correo'],
        $datos['telefono'],
        $datos['estado']
    );

    if ($resultado) {
        echo json_encode([
            'estado' => true,
            'mensaje' => 'Usuario actualizado correctamente'
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            'estado' => false,
            'mensaje' => 'No fue posible actualizar el usuario'
        ]);
    }
}

public function eliminar(int $id): void
{
    $resultado = $this->usuario->eliminar($id);

    if ($resultado) {
        echo json_encode([
            'estado' => true,
            'mensaje' => 'Usuario eliminado correctamente'
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            'estado' => false,
            'mensaje' => 'No fue posible eliminar el usuario'
        ]);
    }
}

}