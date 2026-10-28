
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

    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT *
                FROM cita
                WHERE id_cliente = :id_cliente
                ORDER BY id_cita ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorEstilista(int $idEstilista): array
    {
        $sql = "SELECT *
                FROM cita
                WHERE id_estilista = :id_estilista
                ORDER BY id_cita ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id_estilista', $idEstilista, PDO::PARAM_INT);
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

    public function existeConflictoHorario(
        int $idEstilista,
        string $fecha,
        string $hora,
        ?int $idCitaExcluir = null
    ): bool {
        $sql = "SELECT COUNT(*)
                FROM cita
                WHERE id_estilista = :id_estilista
                  AND fecha = :fecha
                  AND hora = :hora
                  AND estado = 'Programada'";

        if ($idCitaExcluir !== null) {
            $sql .= " AND id_cita <> :id_cita";
        }

        $stmt = $this->conexion->prepare($sql);

        $parametros = [
            ':id_estilista' => $idEstilista,
            ':fecha' => $fecha,
            ':hora' => $hora
        ];

        if ($idCitaExcluir !== null) {
            $parametros[':id_cita'] = $idCitaExcluir;
        }

        $stmt->execute($parametros);

        return (int) $stmt->fetchColumn() > 0;
    }

     public function tieneDisponibilidadRegistrada(int $idEstilista): bool
    {
    $sql = "SELECT COUNT(*)
            FROM disponibilidad
            WHERE id_estilista = :id_estilista";

    $stmt = $this->conexion->prepare($sql);
    $stmt->execute([
        ':id_estilista' => $idEstilista
    ]);

    return (int) $stmt->fetchColumn() > 0;
    }

    public function esEstilistaValido(int $idEstilista): bool
    {
    $sql = "SELECT COUNT(*)
            FROM usuario u
            INNER JOIN rol r ON r.id_rol = u.id_rol
            WHERE u.id_usuario = :id_estilista
              AND r.nombre_rol = 'Estilista'
              AND u.estado = 'Activo'";

    $stmt = $this->conexion->prepare($sql);
    $stmt->execute([
        ':id_estilista' => $idEstilista
    ]);

    return (int) $stmt->fetchColumn() > 0;
    }
    
    public function cumpleHorarioDisponible(
    int $idEstilista,
    string $fecha,
    string $hora,
    int $duracion
): bool {
    $diasSemana = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    $fechaObjeto = DateTime::createFromFormat('!Y-m-d', $fecha);

    if (
        !$fechaObjeto ||
        $fechaObjeto->format('Y-m-d') !== $fecha ||
        $duracion <= 0
    ) {
        return false;
    }

    $diaSemana = $diasSemana[(int) $fechaObjeto->format('N')];

    $horaInicio = DateTime::createFromFormat('!H:i:s', $hora);

    if (!$horaInicio) {
        $horaInicio = DateTime::createFromFormat('!H:i', $hora);
    }

    if (!$horaInicio) {
        return false;
    }

    $horaInicioTexto = $horaInicio->format('H:i:s');
    $horaFinTexto = (clone $horaInicio)
        ->modify("+{$duracion} minutes")
        ->format('H:i:s');

    $sql = "SELECT COUNT(*)
            FROM disponibilidad
            WHERE id_estilista = :id_estilista
              AND dia_semana = :dia_semana
              AND estado = 'Disponible'
              AND hora_inicio <= :hora_inicio
              AND hora_fin >= :hora_fin";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ':id_estilista' => $idEstilista,
        ':dia_semana' => $diaSemana,
        ':hora_inicio' => $horaInicioTexto,
        ':hora_fin' => $horaFinTexto
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

public function tieneBloqueoHorario(
    int $idEstilista,
    string $fecha,
    string $hora,
    int $duracion
): bool {
    $diasSemana = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    $fechaObjeto = DateTime::createFromFormat('!Y-m-d', $fecha);

    if (
        !$fechaObjeto ||
        $fechaObjeto->format('Y-m-d') !== $fecha ||
        $duracion <= 0
    ) {
        return false;
    }

    $diaSemana = $diasSemana[(int) $fechaObjeto->format('N')];

    $horaInicio = DateTime::createFromFormat('!H:i:s', $hora);

    if (!$horaInicio) {
        $horaInicio = DateTime::createFromFormat('!H:i', $hora);
    }

    if (!$horaInicio) {
        return false;
    }

    $horaFin = (clone $horaInicio)
        ->modify("+{$duracion} minutes");

    $horaInicioTexto = $horaInicio->format('H:i:s');
    $horaFinTexto = $horaFin->format('H:i:s');

    $sql = "SELECT COUNT(*)
            FROM disponibilidad
            WHERE id_estilista = :id_estilista
              AND dia_semana = :dia_semana
              AND estado = 'No disponible'
              AND hora_inicio < :hora_fin
              AND hora_fin > :hora_inicio";

    $stmt = $this->conexion->prepare($sql);

    $stmt->execute([
        ':id_estilista' => $idEstilista,
        ':dia_semana' => $diaSemana,
        ':hora_inicio' => $horaInicioTexto,
        ':hora_fin' => $horaFinTexto
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

    public function crear(array $datos): bool
    {
        $idEstilista = (int) $datos['id_estilista'];
        if (!$this->esEstilistaValido($idEstilista)) {
    throw new DomainException(
        'El usuario indicado como estilista no existe, no está activo o no tiene el rol Estilista.'
    );
}
        if (!$this->tieneDisponibilidadRegistrada($idEstilista)) {
            throw new DomainException(
                'El estilista no tiene disponibilidad registrada.'
            );
        }
    $consultaServicio = $this->conexion->prepare(
    "SELECT duracion
     FROM servicio
     WHERE id_servicio = :id_servicio
       AND estado = 'Activo'"
);

$consultaServicio->execute([
    ':id_servicio' => (int) $datos['id_servicio']
]);

$duracion = $consultaServicio->fetchColumn();

if ($duracion === false || (int) $duracion <= 0) {
    throw new DomainException(
        'El servicio no existe, no está activo o no tiene una duración válida.'
    );
}

if (!$this->cumpleHorarioDisponible(
    $idEstilista,
    $datos['fecha'],
    $datos['hora'],
    (int) $duracion
)) {
    throw new DomainException(
        'La cita debe estar completamente dentro del horario disponible del estilista.'
    );
}

if ($this->tieneBloqueoHorario(
    $idEstilista,
    $datos['fecha'],
    $datos['hora'],
    (int) $duracion
)) {
    throw new DomainException(
        'La cita coincide con un intervalo marcado como no disponible.'
    );
}

        if (
    $this->existeConflictoHorario(
        (int) $datos['id_estilista'],
        $datos['fecha'],
        $datos['hora']
    )
) {
    throw new DomainException(
        'El estilista ya tiene una cita programada en ese horario.'
    );
}
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
    $idEstilista = (int) $datos['id_estilista'];
    if (!$this->esEstilistaValido($idEstilista)) {
    throw new DomainException(
        'El usuario indicado como estilista no existe, no está activo o no tiene el rol Estilista.'
    );
}
    $idServicio = (int) $datos['id_servicio'];

    // RN-18: el estilista debe tener disponibilidad registrada.
    if (!$this->tieneDisponibilidadRegistrada($idEstilista)) {
        throw new DomainException(
            'El estilista no tiene disponibilidad registrada.'
        );
    }

    // Obtener la duración del servicio activo.
    $consultaServicio = $this->conexion->prepare(
        "SELECT duracion
         FROM servicio
         WHERE id_servicio = :id_servicio
           AND estado = 'Activo'"
    );

    $consultaServicio->execute([
        ':id_servicio' => $idServicio
    ]);

    $duracion = $consultaServicio->fetchColumn();

    if ($duracion === false || (int) $duracion <= 0) {
        throw new DomainException(
            'El servicio no existe, no está activo o no tiene una duración válida.'
        );
    }

    // RN-19: toda la cita debe caber en un horario disponible.
    if (!$this->cumpleHorarioDisponible(
        $idEstilista,
        $datos['fecha'],
        $datos['hora'],
        (int) $duracion
    )) {
        throw new DomainException(
            'La cita debe estar completamente dentro del horario disponible del estilista.'
        );
    }

if ($this->tieneBloqueoHorario(
    $idEstilista,
    $datos['fecha'],
    $datos['hora'],
    (int) $duracion
)) {
    throw new DomainException(
        'La cita coincide con un intervalo marcado como no disponible.'
    );
}

    // Conservar la validación de conflictos horarios existente.
    if ($this->existeConflictoHorario(
        $idEstilista,
        $datos['fecha'],
        $datos['hora'],
        $id
    )) {
        throw new DomainException(
            'El estilista ya tiene una cita programada en ese horario.'
        );
    }

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
        ':id_estilista' => $idEstilista,
        ':id_servicio' => $idServicio,
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

    public function cancelar(int $id): bool
    {
        $sql = "UPDATE cita
                SET estado = 'Cancelada'
                WHERE id_cita = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

}
