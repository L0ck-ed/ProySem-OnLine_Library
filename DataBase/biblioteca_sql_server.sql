/*
    Base de datos convertida de MySQL/phpMyAdmin a SQL Server
    Proyecto: Biblioteca Online
*/

IF DB_ID(N'OnLineLibrary') IS NOT NULL
BEGIN
    ALTER DATABASE OnLineLibrary SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE OnLineLibrary;
END;
GO

CREATE DATABASE OnLineLibrary;
GO

USE OnLineLibrary;
GO

/* =========================
   TABLA: usuarios
   ========================= */
CREATE TABLE dbo.usuarios (
    id_usuario INT IDENTITY(1,1) NOT NULL,
    nombre NVARCHAR(100) NOT NULL,
    usuario NVARCHAR(50) NOT NULL,
    [password] NVARCHAR(255) NOT NULL,
    rol NVARCHAR(20) NOT NULL CONSTRAINT df_usuarios_rol DEFAULT N'Bibliotecario',
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_usuarios_estado DEFAULT N'Activo',
    intentos_fallidos INT NOT NULL CONSTRAINT df_usuarios_intentos DEFAULT 0,
    bloqueado BIT NOT NULL CONSTRAINT df_usuarios_bloqueado DEFAULT 0,
    ultimo_login DATETIME2 NULL,
    ultimo_intento DATETIME2 NULL,
    fecha_creacion DATETIME2 NOT NULL CONSTRAINT df_usuarios_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT pk_usuarios PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuarios_usuario UNIQUE (usuario),
    CONSTRAINT ck_usuarios_rol CHECK (rol IN (N'Administrador', N'Bibliotecario')),
    CONSTRAINT ck_usuarios_estado CHECK (estado IN (N'Activo', N'Inactivo'))
);
GO

/* =========================
   TABLA: carreras
   ========================= */
CREATE TABLE dbo.carreras (
    id_carrera INT IDENTITY(1,1) NOT NULL,
    nombre NVARCHAR(100) NOT NULL,
    descripcion NVARCHAR(MAX) NULL,
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_carreras_estado DEFAULT N'Activo',
    fecha_creacion DATETIME2 NOT NULL CONSTRAINT df_carreras_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT pk_carreras PRIMARY KEY (id_carrera),
    CONSTRAINT uq_carreras_nombre UNIQUE (nombre),
    CONSTRAINT ck_carreras_estado CHECK (estado IN (N'Activo', N'Inactivo'))
);
GO

/* =========================
   TABLA: estudiantes
   ========================= */
CREATE TABLE dbo.estudiantes (
    id_estudiante INT IDENTITY(1,1) NOT NULL,
    cip NVARCHAR(30) NOT NULL,
    primer_nombre NVARCHAR(50) NOT NULL,
    segundo_nombre NVARCHAR(50) NULL,
    primer_apellido NVARCHAR(50) NOT NULL,
    segundo_apellido NVARCHAR(50) NULL,
    fecha_nacimiento DATE NOT NULL,
    id_carrera INT NOT NULL,
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_estudiantes_estado DEFAULT N'Activo',
    fecha_creacion DATETIME2 NOT NULL CONSTRAINT df_estudiantes_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT pk_estudiantes PRIMARY KEY (id_estudiante),
    CONSTRAINT uq_estudiantes_cip UNIQUE (cip),
    CONSTRAINT ck_estudiantes_estado CHECK (estado IN (N'Activo', N'Inactivo')),
    CONSTRAINT fk_estudiantes_carreras FOREIGN KEY (id_carrera)
        REFERENCES dbo.carreras(id_carrera)
);
GO

/* =========================
   TABLA: categorias
   ========================= */
CREATE TABLE dbo.categorias (
    id_categoria INT IDENTITY(1,1) NOT NULL,
    nombre NVARCHAR(100) NOT NULL,
    descripcion NVARCHAR(MAX) NULL,
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_categorias_estado DEFAULT N'Activo',
    fecha_creacion DATETIME2 NOT NULL CONSTRAINT df_categorias_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT pk_categorias PRIMARY KEY (id_categoria),
    CONSTRAINT uq_categorias_nombre UNIQUE (nombre),
    CONSTRAINT ck_categorias_estado CHECK (estado IN (N'Activo', N'Inactivo'))
);
GO

/* =========================
   TABLA: libros
   ========================= */
CREATE TABLE dbo.libros (
    id_libro INT IDENTITY(1,1) NOT NULL,
    titulo NVARCHAR(200) NOT NULL,
    autor NVARCHAR(150) NULL,
    isbn NVARCHAR(30) NULL,
    editorial NVARCHAR(100) NULL,
    anio_publicacion SMALLINT NULL,
    descripcion NVARCHAR(MAX) NULL,
    existencias INT NOT NULL CONSTRAINT df_libros_existencias DEFAULT 0,
    imagen NVARCHAR(255) NULL,
    thumbnail NVARCHAR(255) NULL,
    ubicacion NVARCHAR(100) NULL,
    id_categoria INT NOT NULL,
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_libros_estado DEFAULT N'Activo',
    fecha_creacion DATETIME2 NOT NULL CONSTRAINT df_libros_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT pk_libros PRIMARY KEY (id_libro),
    CONSTRAINT uq_libros_isbn UNIQUE (isbn),
    CONSTRAINT ck_libros_estado CHECK (estado IN (N'Activo', N'Inactivo')),
    CONSTRAINT ck_libros_anio CHECK (anio_publicacion IS NULL OR anio_publicacion BETWEEN 1000 AND 9999),
    CONSTRAINT fk_libros_categorias FOREIGN KEY (id_categoria)
        REFERENCES dbo.categorias(id_categoria)
);
GO

/* =========================
   TABLA: reservas
   ========================= */
CREATE TABLE dbo.reservas (
    id_reserva INT IDENTITY(1,1) NOT NULL,
    id_estudiante INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_reserva DATETIME2 NOT NULL CONSTRAINT df_reservas_fecha DEFAULT SYSDATETIME(),
    fecha_devolucion DATETIME2 NULL,
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_reservas_estado DEFAULT N'Prestado',

    CONSTRAINT pk_reservas PRIMARY KEY (id_reserva),
    CONSTRAINT ck_reservas_estado CHECK (estado IN (N'Prestado', N'Devuelto', N'Cancelado')),
    CONSTRAINT fk_reservas_estudiantes FOREIGN KEY (id_estudiante)
        REFERENCES dbo.estudiantes(id_estudiante),
    CONSTRAINT fk_reservas_libros FOREIGN KEY (id_libro)
        REFERENCES dbo.libros(id_libro)
);
GO

/* =========================
   TABLA: solicitudes
   ========================= */
CREATE TABLE dbo.solicitudes (
    id_solicitud INT IDENTITY(1,1) NOT NULL,
    id_estudiante INT NULL,
    titulo_libro NVARCHAR(200) NOT NULL,
    area NVARCHAR(50) NOT NULL,
    descripcion NVARCHAR(MAX) NULL,
    fecha DATETIME2 NOT NULL CONSTRAINT df_solicitudes_fecha DEFAULT SYSDATETIME(),
    estado NVARCHAR(20) NOT NULL CONSTRAINT df_solicitudes_estado DEFAULT N'Pendiente',

    CONSTRAINT pk_solicitudes PRIMARY KEY (id_solicitud),
    CONSTRAINT ck_solicitudes_area CHECK (area IN (
        N'Matemáticas',
        N'Ciencias',
        N'Tecnologías',
        N'Deporte',
        N'Salud',
        N'Revistas Científicas'
    )),
    CONSTRAINT ck_solicitudes_estado CHECK (estado IN (N'Pendiente', N'Revisado', N'Aprobado', N'Rechazado')),
    CONSTRAINT fk_solicitudes_estudiantes FOREIGN KEY (id_estudiante)
        REFERENCES dbo.estudiantes(id_estudiante)
);
GO

/* =========================
   TABLA: logs_login
   ========================= */
CREATE TABLE dbo.logs_login (
    id_log INT IDENTITY(1,1) NOT NULL,
    usuario NVARCHAR(50) NULL,
    ip NVARCHAR(50) NULL,
    navegador NVARCHAR(MAX) NULL,
    metodo NVARCHAR(20) NULL,
    url NVARCHAR(255) NULL,
    resultado NVARCHAR(50) NULL,
    fecha DATETIME2 NOT NULL CONSTRAINT df_logs_login_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT pk_logs_login PRIMARY KEY (id_log)
);
GO

/* =========================
   DATOS INICIALES
   ========================= */
INSERT INTO dbo.usuarios
(nombre, usuario, [password], rol, estado)
VALUES
(
    N'Administrador General',
    N'admin',
    N'$2y$10$ODdUyGxBPECS0Vw1A4.3/OJuUjISEcu4q8s5wg4K1MV2XNNOj0jKi',
    N'Administrador',
    N'Activo'
);
GO

INSERT INTO dbo.carreras(nombre, descripcion) VALUES
(N'Licenciatura en Desarrollo y Gestión de Software', N'Carrera de sistemas computacionales'),
(N'Ingeniería en Sistemas', N'Carrera relacionada con sistemas'),
(N'Ingeniería Industrial', N'Carrera industrial'),
(N'Ingeniería Civil', N'Carrera civil');
GO

INSERT INTO dbo.categorias(nombre, descripcion) VALUES
(N'Química', N'Libros de química'),
(N'Sistemas', N'Libros de sistemas y programación'),
(N'Lógica', N'Libros de lógica'),
(N'Matemática', N'Libros de matemática'),
(N'Estadística', N'Libros de estadística');
GO

SELECT 'Base de datos myprojectbiblioteca_v2 creada correctamente en SQL Server.' AS mensaje;
GO



/*
    Migración: agrega autenticación por PIN a la tabla estudiantes.
    Ejecutar UNA SOLA VEZ contra la base de datos OnLineLibrary (SQL Server).
    Cada miembro del equipo debe correr este script después de hacer pull.
*/

USE OnLineLibrary;
GO

ALTER TABLE dbo.estudiantes ADD
    pin_hash NVARCHAR(255) NULL,
    intentos_fallidos INT NOT NULL CONSTRAINT df_estudiantes_intentos DEFAULT 0,
    bloqueado BIT NOT NULL CONSTRAINT df_estudiantes_bloqueado DEFAULT 0,
    ultimo_login DATETIME2 NULL,
    ultimo_intento DATETIME2 NULL;
GO