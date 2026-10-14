
<?php

class Cita
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM cita ORDER BY id_cita ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT * FROM cita WHERE id_cita = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO cita
                (id_cliente, id_estilista, id_servicio, fecha, hora, observaciones, estado)
                VALUES
                (:id_cliente, :id_estilista, :id_servicio, :fecha, :hora, :observaciones, :estado)";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':id_cliente' => $datos['id_cliente'],
            ':id_estilista' => $datos['id_estilista'],
            ':id_servicio' => $datos['id_servicio'],
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'],
            ':observaciones' => $datos['observaciones'] ?? null,
            ':estado' => $datos['estado']
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE cita
                SET id_cliente = :id_cliente,
                    id_estilista = :id_estilista,
                    id_servicio = :id_servicio,
                    fecha = :fecha,
                    hora = :hora,
                    observaciones = :observaciones,
                    estado = :estado
                WHERE id_cita = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':id_cliente' => $datos['id_cliente'],
            ':id_estilista' => $datos['id_estilista'],
            ':id_servicio' => $datos['id_servicio'],
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'],
            ':observaciones' => $datos['observaciones'] ?? null,
            ':estado' => $datos['estado'],
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM cita WHERE id_cita = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
