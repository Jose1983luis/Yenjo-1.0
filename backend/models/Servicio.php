<?php

class Servicio
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT
                    id_servicio,
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion,
                    estado
                FROM servicio
                ORDER BY id_servicio ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT
                    id_servicio,
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion,
                    estado
                FROM servicio
                WHERE id_servicio = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ':id' => $id
        ]);

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    public function crear(
        string $nombreServicio,
        string $descripcion,
        float $precio,
        int $duracion,
        string $estado
    ): bool {
        $sql = "INSERT INTO servicio (
                    nombre_servicio,
                    descripcion,
                    precio,
                    duracion,
                    estado
                ) VALUES (
                    :nombre_servicio,
                    :descripcion,
                    :precio,
                    :duracion,
                    :estado
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':nombre_servicio' => $nombreServicio,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':duracion' => $duracion,
            ':estado' => $estado
        ]);
    }

    public function actualizar(
        int $id,
        string $nombreServicio,
        string $descripcion,
        float $precio,
        int $duracion,
        string $estado
    ): bool {
        $sql = "UPDATE servicio
                SET
                    nombre_servicio = :nombre_servicio,
                    descripcion = :descripcion,
                    precio = :precio,
                    duracion = :duracion,
                    estado = :estado
                WHERE id_servicio = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':nombre_servicio' => $nombreServicio,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':duracion' => $duracion,
            ':estado' => $estado,
            ':id' => $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM servicio
                WHERE id_servicio = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id' => $id
        ]);
    }
}