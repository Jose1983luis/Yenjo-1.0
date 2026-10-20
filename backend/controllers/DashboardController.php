
<?php

require_once __DIR__ . '/../middleware/AutenticacionMiddleware.php';

class DashboardController
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerResumen(): void
    {
        // Solo los administradores pueden consultar el Dashboard.
        if (!AutenticacionMiddleware::verificarRol(['Administrador'])) {
            return;
        }

        try {
            // Total de usuarios registrados.
            $sql = "SELECT COUNT(*) FROM usuario";
            $totalUsuarios = (int) $this->conexion
                ->query($sql)->fetchColumn();

            // Total de clientes.
            $sql = "SELECT COUNT(*)
                    FROM usuario u
                    INNER JOIN rol r ON u.id_rol = r.id_rol
                    WHERE r.nombre_rol = 'Cliente'";
            $totalClientes = (int) $this->conexion
                ->query($sql)->fetchColumn();

            // Total de servicios.
            $sql = "SELECT COUNT(*) FROM servicio";
            $totalServicios = (int) $this->conexion
                ->query($sql)->fetchColumn();

            // Total de citas registradas.
            $sql = "SELECT COUNT(*) FROM cita";
            $totalCitas = (int) $this->conexion
                ->query($sql)->fetchColumn();

            // Próximas citas: desde el momento actual,
            // excluyendo las canceladas y finalizadas.
            $sql = "SELECT COUNT(*)
                    FROM cita
                    WHERE TIMESTAMP(fecha, hora) >= NOW()
                      AND estado NOT IN ('Cancelada', 'Finalizada')";
            $proximasCitas = (int) $this->conexion
                ->query($sql)->fetchColumn();

            // Cantidad de citas agrupadas por estado.
            $sql = "SELECT estado, COUNT(*) AS cantidad
                    FROM cita
                    GROUP BY estado
                    ORDER BY estado";
            $citasPorEstado = $this->conexion
                ->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            // Cantidad y valor de los pagos agrupados por estado.
            $sql = "SELECT estado,
                           COUNT(*) AS cantidad,
                           COALESCE(SUM(valor), 0) AS valor_total
                    FROM pago
                    GROUP BY estado
                    ORDER BY estado";
            $pagosPorEstado = $this->conexion
                ->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            // Indicadores económicos.
            $sql = "SELECT
                        COUNT(*) AS total_pagos,
                        COALESCE(SUM(
                            CASE WHEN estado = 'Pagado'
                                 THEN valor ELSE 0 END
                        ), 0) AS valor_pagado,
                        COALESCE(SUM(
                            CASE WHEN estado = 'Pendiente'
                                 THEN valor ELSE 0 END
                        ), 0) AS valor_pendiente
                    FROM pago";
            $resumenPagos = $this->conexion
                ->query($sql)->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'estado' => true,
                'datos' => [
                    'total_usuarios' => $totalUsuarios,
                    'total_clientes' => $totalClientes,
                    'total_servicios' => $totalServicios,
                    'total_citas' => $totalCitas,
                    'proximas_citas' => $proximasCitas,
                    'citas_por_estado' => $citasPorEstado,
                    'pagos_por_estado' => $pagosPorEstado,
                    'resumen_pagos' => $resumenPagos
                ]
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'estado' => false,
                'mensaje' => 'No fue posible obtener los indicadores del Dashboard.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
