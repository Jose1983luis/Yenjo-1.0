<?php

class Usuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT
                    id_usuario,
                    id_rol,
                    nombre,
                    apellido,
                    correo_electronico,
                    telefono,
                    fecha_registro,
                    estado
                FROM usuario
                ORDER BY id_usuario";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

public function obtenerPorId(int $id): ?array
{
    $sql = "SELECT
                id_usuario,
                id_rol,
                nombre,
                apellido,
                correo_electronico,
                telefono,
                fecha_registro,
                estado
            FROM usuario
            WHERE id_usuario = :id";

    $consulta = $this->conexion->prepare($sql);
    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
    $consulta->execute();

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    return $usuario ?: null;
}

public function crear(
    int $idRol,
    string $nombre,
    string $apellido,
    string $correo,
    string $telefono,
    string $passwordHash,
    string $fechaRegistro,
    string $estado
): bool {

    $sql = "INSERT INTO usuario (
                id_rol,
                nombre,
                apellido,
                correo_electronico,
                telefono,
                password_hash,
                fecha_registro,
                estado
            ) VALUES (
                :id_rol,
                :nombre,
                :apellido,
                :correo,
                :telefono,
                :password_hash,
                :fecha_registro,
                :estado
            )";

    $consulta = $this->conexion->prepare($sql);

    return $consulta->execute([
        ':id_rol' => $idRol,
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':correo' => $correo,
        ':telefono' => $telefono,
        ':password_hash' => $passwordHash,
        ':fecha_registro' => $fechaRegistro,
        ':estado' => $estado
    ]);
}

public function actualizar(
    int $id,
    int $idRol,
    string $nombre,
    string $apellido,
    string $correo,
    string $telefono,
    string $estado
): bool {

    $sql = "UPDATE usuario
            SET
                id_rol = :id_rol,
                nombre = :nombre,
                apellido = :apellido,
                correo_electronico = :correo,
                telefono = :telefono,
                estado = :estado
            WHERE id_usuario = :id";

    $consulta = $this->conexion->prepare($sql);

    return $consulta->execute([
        ':id_rol' => $idRol,
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':correo' => $correo,
        ':telefono' => $telefono,
        ':estado' => $estado,
        ':id' => $id
    ]);
}

public function eliminar(int $id): bool
{
    $sql = "DELETE FROM usuario
            WHERE id_usuario = :id";

    $consulta = $this->conexion->prepare($sql);

    return $consulta->execute([
        ':id' => $id
    ]);
}

}
