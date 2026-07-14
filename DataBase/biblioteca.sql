/*CREATE DATABASE IF NOT EXISTS myprojectbiblioteca_v2
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE myprojectbiblioteca_v2;

DROP TABLE IF EXISTS logs_login;
DROP TABLE IF EXISTS solicitudes;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS libros;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS carreras;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('Administrador','Bibliotecario') NOT NULL DEFAULT 'Bibliotecario',
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    intentos_fallidos INT NOT NULL DEFAULT 0,
    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_login DATETIME NULL,
    ultimo_intento DATETIME NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    cip VARCHAR(30) NOT NULL UNIQUE,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50) NULL,
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50) NULL,
    fecha_nacimiento DATE NOT NULL,
    id_carrera INT NOT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiantes_carreras
        FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera)
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) NULL,
    isbn VARCHAR(30) NULL UNIQUE,
    editorial VARCHAR(100) NULL,
    anio_publicacion YEAR NULL,
    descripcion TEXT NULL,
    existencias INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NULL,
    thumbnail VARCHAR(255) NULL,
    ubicacion VARCHAR(100) NULL,
    id_categoria INT NOT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_libros_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion DATETIME NULL,
    estado ENUM('Prestado','Devuelto','Cancelado') NOT NULL DEFAULT 'Prestado',
    CONSTRAINT fk_reservas_estudiantes
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante),
    CONSTRAINT fk_reservas_libros
        FOREIGN KEY (id_libro) REFERENCES libros(id_libro)
);

CREATE TABLE solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NULL,
    titulo_libro VARCHAR(200) NOT NULL,
    area ENUM('Matemáticas','Ciencias','Tecnologías','Deporte','Salud','Revistas Científicas') NOT NULL,
    descripcion TEXT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente','Revisado','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente',
    CONSTRAINT fk_solicitudes_estudiantes
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante)
);

CREATE TABLE logs_login (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50),
    ip VARCHAR(50),
    navegador TEXT,
    metodo VARCHAR(20),
    url VARCHAR(255),
    resultado VARCHAR(50),
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios
(nombre, usuario, password, rol, estado)
VALUES
(
'Administrador General',
'admin',
'$2y$10$ODdUyGxBPECS0Vw1A4.3/OJuUjISEcu4q8s5wg4K1MV2XNNOj0jKi',
'Administrador',
'Activo'
);

INSERT INTO carreras(nombre, descripcion) VALUES
('Licenciatura en Desarrollo y Gestión de Software', 'Carrera de sistemas computacionales'),
('Ingeniería en Sistemas', 'Carrera relacionada con sistemas'),
('Ingeniería Industrial', 'Carrera industrial'),
('Ingeniería Civil', 'Carrera civil');

INSERT INTO categorias(nombre, descripcion) VALUES
('Química', 'Libros de química'),
('Sistemas', 'Libros de sistemas y programación'),
('Lógica', 'Libros de lógica'),
('Matemática', 'Libros de matemática'),
('Estadística', 'Libros de estadística');*/

CREATE DATABASE IF NOT EXISTS myprojectbiblioteca_v2
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE myprojectbiblioteca_v2;

DROP TABLE IF EXISTS logs_login;
DROP TABLE IF EXISTS solicitudes;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS libros;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS carreras;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('Administrador','Bibliotecario') NOT NULL DEFAULT 'Bibliotecario',
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    intentos_fallidos INT NOT NULL DEFAULT 0,
    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_login DATETIME NULL,
    ultimo_intento DATETIME NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    cip VARCHAR(30) NOT NULL UNIQUE,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50) NULL,
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50) NULL,
    fecha_nacimiento DATE NOT NULL,
    id_carrera INT NOT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    pin_hash VARCHAR(255) NULL,
    intentos_fallidos INT NOT NULL DEFAULT 0,
    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
    ultimo_login DATETIME NULL,
    ultimo_intento DATETIME NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiantes_carreras
        FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera)
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) NULL,
    isbn VARCHAR(30) NULL UNIQUE,
    editorial VARCHAR(100) NULL,
    anio_publicacion YEAR NULL,
    descripcion TEXT NULL,
    existencias INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NULL,
    thumbnail VARCHAR(255) NULL,
    ubicacion VARCHAR(100) NULL,
    id_categoria INT NOT NULL,
    estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_libros_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion DATETIME NULL,
    estado ENUM('Prestado','Devuelto','Cancelado') NOT NULL DEFAULT 'Prestado',
    CONSTRAINT fk_reservas_estudiantes
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante),
    CONSTRAINT fk_reservas_libros
        FOREIGN KEY (id_libro) REFERENCES libros(id_libro)
);

CREATE TABLE solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NULL,
    titulo_libro VARCHAR(200) NOT NULL,
    area ENUM('Matemáticas','Ciencias','Tecnologías','Deporte','Salud','Revistas Científicas') NOT NULL,
    descripcion TEXT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente','Revisado','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente',
    CONSTRAINT fk_solicitudes_estudiantes
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante)
);

CREATE TABLE logs_login (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50),
    ip VARCHAR(50),
    navegador TEXT,
    metodo VARCHAR(20),
    url VARCHAR(255),
    resultado VARCHAR(50),
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios
(nombre, usuario, password, rol, estado)
VALUES
(
'Administrador General',
'admin',
'$2y$10$ODdUyGxBPECS0Vw1A4.3/OJuUjISEcu4q8s5wg4K1MV2XNNOj0jKi',
'Administrador',
'Activo'
);

INSERT INTO carreras(nombre, descripcion) VALUES
('Licenciatura en Desarrollo y Gestión de Software', 'Carrera de sistemas computacionales'),
('Ingeniería en Sistemas', 'Carrera relacionada con sistemas'),
('Ingeniería Industrial', 'Carrera industrial'),
('Ingeniería Civil', 'Carrera civil');

INSERT INTO categorias(nombre, descripcion) VALUES
('Química', 'Libros de química'),
('Sistemas', 'Libros de sistemas y programación'),
('Lógica', 'Libros de lógica'),
('Matemática', 'Libros de matemática'),
('Estadística', 'Libros de estadística');
