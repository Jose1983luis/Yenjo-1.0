CREATE DATABASE yenjo_1_0;
USE yenjo_1_0;
CREATE TABLE rol (
    id_rol INT NOT NULL AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id_rol)
);
CREATE TABLE usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    id_rol INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fecha_registro DATETIME NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL,
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);
CREATE TABLE disponibilidad (
    id_disponibilidad INT NOT NULL AUTO_INCREMENT,
    id_estilista INT NOT NULL,
    dia_semana ENUM(
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo'
    ) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('Disponible', 'No disponible') NOT NULL,
    PRIMARY KEY (id_disponibilidad),
    FOREIGN KEY (id_estilista) REFERENCES usuario(id_usuario)
);
CREATE TABLE servicio (
    id_servicio INT NOT NULL AUTO_INCREMENT,
    nombre_servicio VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    duracion INT NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL,
    PRIMARY KEY (id_servicio)
);
CREATE TABLE cita (
    id_cita INT NOT NULL AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_estilista INT NOT NULL,
    id_servicio INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    observaciones VARCHAR(255) NULL,
    estado ENUM('Programada', 'Confirmada', 'Cancelada', 'Finalizada') NOT NULL,
    PRIMARY KEY (id_cita),
    FOREIGN KEY (id_cliente) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_estilista) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio)
);
CREATE TABLE pago (
    id_pago INT NOT NULL AUTO_INCREMENT,
    id_cita INT NOT NULL UNIQUE,
    valor DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME NOT NULL,
    metodo_pago ENUM('Efectivo', 'Transferencia', 'Tarjeta') NOT NULL,
    estado ENUM('Pendiente', 'Pagado', 'Reembolsado') NOT NULL,
    PRIMARY KEY (id_pago),
    FOREIGN KEY (id_cita) REFERENCES cita(id_cita)
);
