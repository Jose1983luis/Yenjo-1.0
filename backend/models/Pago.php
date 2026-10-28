
<?php

class Pago
{
    private PDO $conexion;
    private string $tabla = 'pago';

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM {$this->tabla} ORDER BY id_pago DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCliente(int $idCliente): array
{
    $sql = "SELECT p.*
            FROM {$this->tabla} p
            INNER JOIN cita c ON c.id_cita = p.id_cita
            WHERE c.id_cliente = :id_cliente
            ORDER BY p.id_pago DESC";

    $consulta = $this->conexion->prepare($sql);
    $consulta->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

    public function obtenerPorIdCliente(int $idPago, int $idCliente): array|false
{
    $sql = "SELECT p.*
            FROM {$this->tabla} p
            INNER JOIN cita c ON c.id_cita = p.id_cita
            WHERE p.id_pago = :id_pago
              AND c.id_cliente = :id_cliente";

    $consulta = $this->conexion->prepare($sql);
    $consulta->bindValue(':id_pago', $idPago, PDO::PARAM_INT);
    $consulta->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
    $consulta->execute();

    return $consulta->fetch(PDO::FETCH_ASSOC);
}

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE id_pago = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO {$this->tabla}
                (id_cita, valor, fecha_pago, metodo_pago, estado)
                VALUES
                (:id_cita, :valor, :fecha_pago, :metodo_pago, :estado)";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id_cita' => $datos['id_cita'],
            ':valor' => $datos['valor'],
            ':fecha_pago' => $datos['fecha_pago'],
            ':metodo_pago' => $datos['metodo_pago'],
            ':estado' => $datos['estado'] ?? 'Pendiente'
        ]);
    }

    public function actualizar(int $id, array $datos): bool
{
    $sql = "UPDATE {$this->tabla}
            SET id_cita = :id_cita,
                valor = :valor,
                fecha_pago = :fecha_pago,
                metodo_pago = :metodo_pago,
                estado = :estado
            WHERE id_pago = :id";

    $consulta = $this->conexion->prepare($sql);

    $consulta->execute([
        ':id_cita' => $datos['id_cita'],
        ':valor' => $datos['valor'],
        ':fecha_pago' => $datos['fecha_pago'],
        ':metodo_pago' => $datos['metodo_pago'],
        ':estado' => $datos['estado'],
        ':id' => $id
    ]);

    return $consulta->rowCount() > 0;
}

    public function eliminar(int $id): bool
{
    $sql = "DELETE FROM {$this->tabla} WHERE id_pago = :id";
    $consulta = $this->conexion->prepare($sql);
    $consulta->bindValue(':id', $id, PDO::PARAM_INT);
    $consulta->execute();

    return $consulta->rowCount() > 0;
}
}
