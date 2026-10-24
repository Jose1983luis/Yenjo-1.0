
<?php

require_once __DIR__ . '/../models/Disponibilidad.php';

class DisponibilidadController
{
    private Disponibilidad $disponibilidad;

    public function __construct(PDO $conexion)
    {
        $this->disponibilidad = new Disponibilidad($conexion);
    }

    /**
     * Obtiene el rol del usuario autenticado.
     */
    private function obtenerRol(): string
    {
        return $_SESSION['usuario']['nombre_rol'] ?? '';
    }

    /**
     * Obtiene el ID del usuario autenticado.
     */
    private function obtenerIdUsuario(): int
    {
        return (int) ($_SESSION['usuario']['id_usuario'] ?? 0);
    }

    /**
     * Comprueba si el usuario es Administrador.
     */
    private function esAdministrador(): bool
    {
        return $this->obtenerRol() === 'Administrador';
    }

    /**
     * Responde con un error de autorización.
     */
    private function responderProhibido(string $mensaje): void
    {
        http_response_code(403);

        echo json_encode([
            'estado' => false,
            'mensaje' => $mensaje
        ]);
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

        if (!is_array($datos)) {
            http_response_code(400);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'El cuerpo de la solicitud no es válido'
            ]);
            return;
        }

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

        $idUsuario = $this->obtenerIdUsuario();
        $rol = $this->obtenerRol();
        $idEstilistaSolicitado = (int) $datos['id_estilista'];

        if ($rol === 'Estilista') {
            if ($idUsuario <= 0 || $idEstilistaSolicitado !== $idUsuario) {
                $this->responderProhibido(
                    'Solo puede crear su propia disponibilidad.'
                );
                return;
            }

            // El propietario se determina por la sesión autenticada.
            $idEstilista = $idUsuario;

        } elseif ($rol === 'Administrador') {
            if (!$this->disponibilidad->esEstilista($idEstilistaSolicitado)) {
                http_response_code(400);

                echo json_encode([
                    'estado' => false,
                    'mensaje' => 'El usuario indicado no es un estilista activo.'
                ]);
                return;
            }

            $idEstilista = $idEstilistaSolicitado;
        }

          else {
            $this->responderProhibido(
                'No tiene permisos para crear disponibilidades.'
            );
            return;
        }

        $resultado = $this->disponibilidad->crear(
            $idEstilista,
            $datos['dia_semana'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $datos['estado']
        );

        if ($resultado) {
            http_response_code(201);

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

        if (!is_array($datos)) {
            http_response_code(400);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'El cuerpo de la solicitud no es válido'
            ]);
            return;
        }

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

        $disponibilidadActual = $this->disponibilidad->obtenerPorId($id);

        if (!$disponibilidadActual) {
            http_response_code(404);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'Disponibilidad no encontrada'
            ]);
            return;
        }

        $rol = $this->obtenerRol();
        $idUsuario = $this->obtenerIdUsuario();
        $idPropietarioActual = (int) $disponibilidadActual['id_estilista'];
        $idEstilistaSolicitado = (int) $datos['id_estilista'];

        if ($rol === 'Estilista') {
            if (
                $idUsuario <= 0 ||
                $idPropietarioActual !== $idUsuario ||
                $idEstilistaSolicitado !== $idUsuario
            ) {
                $this->responderProhibido(
                    'Solo puede modificar su propia disponibilidad.'
                );
                return;
            }

            $idEstilista = $idUsuario;

        } elseif ($rol === 'Administrador') {
          if (!$this->disponibilidad->esEstilista($idEstilistaSolicitado)) {
          http_response_code(400);

          echo json_encode([
            'estado' => false,
            'mensaje' => 'El usuario indicado no es un estilista activo.'
           ]);
            return;
        }

            $idEstilista = $idEstilistaSolicitado;
    }

          else {
            $this->responderProhibido(
                'No tiene permisos para modificar disponibilidades.'
            );
            return;
        }

        $resultado = $this->disponibilidad->actualizar(
            $id,
            $idEstilista,
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
        $disponibilidadActual = $this->disponibilidad->obtenerPorId($id);

        if (!$disponibilidadActual) {
            http_response_code(404);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'Disponibilidad no encontrada'
            ]);
            return;
        }

        $rol = $this->obtenerRol();
        $idUsuario = $this->obtenerIdUsuario();
        $idPropietario = (int) $disponibilidadActual['id_estilista'];

        if ($rol === 'Estilista') {
            if ($idUsuario <= 0 || $idPropietario !== $idUsuario) {
                $this->responderProhibido(
                    'Solo puede eliminar su propia disponibilidad.'
                );
                return;
            }
        } elseif ($rol !== 'Administrador') {
            $this->responderProhibido(
                'No tiene permisos para eliminar disponibilidades.'
            );
            return;
        }

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
