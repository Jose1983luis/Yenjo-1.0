
<?php

class Autenticacion
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerPorCorreo(string $correo): ?array
    {
        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    r.nombre_rol,
                    u.nombre,
                    u.apellido,
                    u.correo_electronico,
                    u.password_hash,
                    u.estado
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                WHERE u.correo_electronico = :correo
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([':correo' => $correo]);

        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public function verificarPassword(
        string $password,
        string $passwordHash
    ): bool {
        return password_verify($password, $passwordHash);
    }
}
