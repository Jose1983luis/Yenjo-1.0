<?php

class Disponibilidad
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT
                    id_disponibilidad,
                    id_estilista,
                    dia_semana,
                    hora_inicio,
                    hora_fin,
                    estado
                FROM disponibilidad
                ORDER BY id_disponibilidad ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT
                    id_disponibilidad,
                    id_estilista,
                    dia_semana,
                    hora_inicio,
                    hora_fin,
                    estado
                FROM disponibilidad
                WHERE id_disponibilidad = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ':id' => $id
        ]);

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    public function crear(
        int $idEstilista,
        string $diaSemana,
        string $horaInicio,
        string $horaFin,
        string $estado
    ): bool {
        $sql = "INSERT INTO disponibilidad (
                    id_estilista,
                    dia_semana,
                    hora_inicio,
                    hora_fin,
                    estado
                ) VALUES (
                    :id_estilista,
                    :dia_semana,
                    :hora_inicio,
                    :hora_fin,
                    :estado
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id_estilista' => $idEstilista,
            ':dia_semana' => $diaSemana,
            ':hora_inicio' => $horaInicio,
            ':hora_fin' => $horaFin,
            ':estado' => $estado
        ]);
    }

    public function actualizar(
        int $id,
        int $idEstilista,
        string $diaSemana,
        string $horaInicio,
        string $horaFin,
        string $estado
    ): bool {
        $sql = "UPDATE disponibilidad
                SET
                    id_estilista = :id_estilista,
                    dia_semana = :dia_semana,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    estado = :estado
                WHERE id_disponibilidad = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id_estilista' => $idEstilista,
            ':dia_semana' => $diaSemana,
            ':hora_inicio' => $horaInicio,
            ':hora_fin' => $horaFin,
            ':estado' => $estado,
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM disponibilidad
                WHERE id_disponibilidad = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id' => $id
        ]);
    }
}