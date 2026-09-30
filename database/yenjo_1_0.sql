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

INSERT INTO rol (nombre_rol)
VALUES
('Administrador'),
('Cliente'),
('Estilista');

INSERT INTO usuario
(id_rol, nombre, apellido, correo_electronico, telefono, password_hash, fecha_registro, estado)
VALUES
(1, 'Administrador', 'YENJO', 'admin@yenjo.local', '3000000001',
 '$2y$12$C93MPYoSHohtNEHX4nZsO.cWBi1cW6378OM7NIfSuYUXu5alPpJli',
 NOW(), 'Activo'),

(2, 'Cliente', 'YENJO', 'cliente@yenjo.local', '3000000002',
 '$2y$12$rTtFhMIriq/OtFZHdNdSguqfQbFeMiVd.x4LKI9EvVRHAzovmMaK6',
 NOW(), 'Activo'),

(3, 'Estilista', 'YENJO', 'estilista@yenjo.local', '3000000003',
 '$2y$12$izvP19f4ZeJDPNpuRMF9sOJSNAii7pnkFjlyoWiketwKd5gd/eKkS',
 NOW(), 'Activo');
 
 SELECT
    u.id_usuario,
    r.nombre_rol,
    u.nombre,
    u.apellido,
    u.correo_electronico,
    u.telefono,
    u.estado
FROM usuario u
INNER JOIN rol r
    ON u.id_rol = r.id_rol
ORDER BY u.id_usuario;

INSERT INTO servicio
(nombre_servicio, descripcion, precio, duracion, estado)
VALUES
('Manicure', 'Servicio de manicure para cuidado y arreglo de uñas', 35000.00, 60, 'Activo'),
('Pedicure', 'Servicio de pedicure para cuidado y arreglo de uñas de los pies', 45000.00, 75, 'Activo'),
('Tinte de cabello', 'Aplicación de tinte para cambio o renovación del color del cabello', 80000.00, 120, 'Activo'),
('Tratamiento de keratina', 'Tratamiento capilar de keratina para el cuidado y acondicionamiento del cabello', 120000.00, 150, 'Activo');

SELECT
    id_servicio,
    nombre_servicio,
    descripcion,
    precio,
    duracion,
    estado
FROM servicio
ORDER BY id_servicio;

INSERT INTO disponibilidad
(id_estilista, dia_semana, hora_inicio, hora_fin, estado)
VALUES
(3, 'Lunes', '08:00:00', '17:00:00', 'Disponible'),
(3, 'Martes', '08:00:00', '17:00:00', 'Disponible'),
(3, 'Miércoles', '08:00:00', '17:00:00', 'Disponible'),
(3, 'Jueves', '08:00:00', '17:00:00', 'Disponible'),
(3, 'Viernes', '08:00:00', '17:00:00', 'Disponible');

	SELECT
		d.id_disponibilidad,
		u.nombre,
		u.apellido,
		d.dia_semana,
		d.hora_inicio,
		d.hora_fin,
		d.estado
	FROM disponibilidad d
	INNER JOIN usuario u
		ON d.id_estilista = u.id_usuario
	ORDER BY d.id_disponibilidad;
    
    INSERT INTO cita
(id_cliente, id_estilista, id_servicio, fecha, hora, observaciones, estado)
VALUES
(2, 3, 1, '2026-09-28', '10:00:00',
 'Cita de prueba para validar el agendamiento de YENJO 1.0',
 'Programada');
 
 SELECT
    c.id_cita,
    CONCAT(cli.nombre, ' ', cli.apellido) AS cliente,
    CONCAT(est.nombre, ' ', est.apellido) AS estilista,
    s.nombre_servicio,
    c.fecha,
    c.hora,
    c.estado
FROM cita c
INNER JOIN usuario cli
    ON c.id_cliente = cli.id_usuario
INNER JOIN usuario est
    ON c.id_estilista = est.id_usuario
INNER JOIN servicio s
    ON c.id_servicio = s.id_servicio
ORDER BY c.id_cita;

INSERT INTO pago
(id_cita, valor, fecha_pago, metodo_pago, estado)
VALUES
(1, 35000.00, '2026-09-28 10:00:00', 'Efectivo', 'Pagado');

SELECT
    p.id_pago,
    p.id_cita,
    CONCAT(u.nombre, ' ', u.apellido) AS cliente,
    s.nombre_servicio,
    p.valor,
    p.fecha_pago,
    p.metodo_pago,
    p.estado
FROM pago p
INNER JOIN cita c
    ON p.id_cita = c.id_cita
INNER JOIN usuario u
    ON c.id_cliente = u.id_usuario
INNER JOIN servicio s
    ON c.id_servicio = s.id_servicio
ORDER BY p.id_pago;

SELECT *
FROM rol
ORDER BY id_rol;

SELECT
    u.id_usuario,
    r.nombre_rol,
    u.nombre,
    u.apellido,
    u.correo_electronico,
    u.telefono,
    u.estado
FROM usuario u
INNER JOIN rol r
    ON u.id_rol = r.id_rol
ORDER BY u.id_usuario;

SELECT
    id_servicio,
    nombre_servicio,
    descripcion,
    precio,
    duracion,
    estado
FROM servicio
ORDER BY id_servicio;

SELECT
    d.id_disponibilidad,
    u.nombre,
    u.apellido,
    d.dia_semana,
    d.hora_inicio,
    d.hora_fin,
    d.estado
FROM disponibilidad d
INNER JOIN usuario u
    ON d.id_estilista = u.id_usuario
ORDER BY d.id_disponibilidad;

SELECT
    c.id_cita,
    CONCAT(cli.nombre, ' ', cli.apellido) AS cliente,
    CONCAT(est.nombre, ' ', est.apellido) AS estilista,
    s.nombre_servicio,
    c.fecha,
    c.hora,
    c.estado
FROM cita c
INNER JOIN usuario cli
    ON c.id_cliente = cli.id_usuario
INNER JOIN usuario est
    ON c.id_estilista = est.id_usuario
INNER JOIN servicio s
    ON c.id_servicio = s.id_servicio
ORDER BY c.id_cita;

SELECT
    p.id_pago,
    p.id_cita,
    CONCAT(u.nombre, ' ', u.apellido) AS cliente,
    s.nombre_servicio,
    p.valor,
    p.fecha_pago,
    p.metodo_pago,
    p.estado
FROM pago p
INNER JOIN cita c
    ON p.id_cita = c.id_cita
INNER JOIN usuario u
    ON c.id_cliente = u.id_usuario
INNER JOIN servicio s
    ON c.id_servicio = s.id_servicio
ORDER BY p.id_pago;

SELECT @@port, @@hostname, @@version;

SELECT 
    id_usuario,
    id_rol,
    nombre,
    apellido,
    correo_electronico,
    telefono,
    password_hash,
    fecha_registro,
    estado
FROM usuario
ORDER BY id_usuario DESC
LIMIT 1;

USE yenjo_1_0;

SELECT
    id_usuario,
    id_rol,
    nombre,
    apellido,
    correo_electronico,
    telefono,
    estado
FROM usuario
WHERE id_usuario = 4;

USE yenjo_1_0;

SELECT *
FROM usuario
WHERE id_usuario = 4;
