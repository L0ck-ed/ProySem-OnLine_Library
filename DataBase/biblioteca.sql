/* ============================================================
   BASE DE DATOS: BibliotecaDigitalDB
   SQL Server
   ADVERTENCIA: ESTE SCRIPT ELIMINA TODA LA BASE DE DATOS.
   ============================================================ */

USE master;
GO

IF DB_ID(N'BibliotecaDigitalDB') IS NOT NULL
BEGIN
    ALTER DATABASE BibliotecaDigitalDB
    SET SINGLE_USER WITH ROLLBACK IMMEDIATE;

    DROP DATABASE BibliotecaDigitalDB;
END;
GO

CREATE DATABASE BibliotecaDigitalDB;
GO

USE BibliotecaDigitalDB;
GO

/* ============================================================
   SEGURIDAD, USUARIOS, ROLES Y PERMISOS
   ============================================================ */

CREATE TABLE roles (
    id_rol INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_roles_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_roles_fecha_creacion DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_roles_nombre UNIQUE (nombre)
);
GO

CREATE TABLE permisos (
    id_permiso INT IDENTITY(1,1) PRIMARY KEY,
    codigo VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_permisos_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_permisos_fecha_creacion DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_permisos_codigo UNIQUE (codigo)
);
GO

CREATE TABLE usuarios (
    id_usuario INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    usuario VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    correo VARCHAR(150) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_usuarios_estado DEFAULT 1,
    intentos_fallidos INT NOT NULL
        CONSTRAINT DF_usuarios_intentos DEFAULT 0,
    bloqueado BIT NOT NULL
        CONSTRAINT DF_usuarios_bloqueado DEFAULT 0,
    bloqueado_hasta DATETIME2(0) NULL,
    ultimo_login DATETIME2(0) NULL,
    ultimo_intento DATETIME2(0) NULL,
    debe_cambiar_password BIT NOT NULL
        CONSTRAINT DF_usuarios_cambiar_password DEFAULT 0,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_usuarios_fecha_creacion DEFAULT SYSDATETIME(),
    fecha_actualizacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_usuarios_fecha_actualizacion DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_usuarios_usuario UNIQUE (usuario),
    CONSTRAINT CK_usuarios_intentos CHECK (intentos_fallidos >= 0)
);
GO

CREATE UNIQUE INDEX UX_usuarios_correo
ON usuarios(correo)
WHERE correo IS NOT NULL;
GO

CREATE TABLE usuarios_roles (
    id_usuario INT NOT NULL,
    id_rol INT NOT NULL,
    fecha_asignacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_usuarios_roles_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT PK_usuarios_roles
        PRIMARY KEY (id_usuario, id_rol),

    CONSTRAINT FK_usuarios_roles_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT FK_usuarios_roles_rol
        FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
        ON DELETE CASCADE
);
GO

CREATE TABLE roles_permisos (
    id_rol INT NOT NULL,
    id_permiso INT NOT NULL,

    CONSTRAINT PK_roles_permisos
        PRIMARY KEY (id_rol, id_permiso),

    CONSTRAINT FK_roles_permisos_rol
        FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
        ON DELETE CASCADE,

    CONSTRAINT FK_roles_permisos_permiso
        FOREIGN KEY (id_permiso)
        REFERENCES permisos(id_permiso)
        ON DELETE CASCADE
);
GO

CREATE TABLE logs_login (
    id_log BIGINT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NULL,
    usuario_intentado VARCHAR(80) NULL,
    ip VARCHAR(45) NULL,
    navegador VARCHAR(500) NULL,
    metodo VARCHAR(10) NULL,
    url VARCHAR(500) NULL,
    resultado VARCHAR(30) NOT NULL,
    detalle VARCHAR(500) NULL,
    fecha DATETIME2(0) NOT NULL
        CONSTRAINT DF_logs_login_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT FK_logs_login_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
);
GO

CREATE INDEX IX_logs_login_fecha
ON logs_login(fecha);
GO

CREATE INDEX IX_logs_login_usuario_intentado
ON logs_login(usuario_intentado);
GO

CREATE TABLE logs_errores (
    id_error BIGINT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NULL,
    modulo VARCHAR(100) NULL,
    mensaje VARCHAR(1000) NOT NULL,
    excepcion VARCHAR(MAX) NULL,
    archivo VARCHAR(500) NULL,
    linea INT NULL,
    ip VARCHAR(45) NULL,
    url VARCHAR(500) NULL,
    fecha DATETIME2(0) NOT NULL
        CONSTRAINT DF_logs_errores_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT FK_logs_errores_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
);
GO

CREATE INDEX IX_logs_errores_fecha
ON logs_errores(fecha);
GO

/* Tabla genérica para almacenar firmas digitales de registros.
   La aplicación PHP deberá crear y verificar la firma mediante interfaces. */
CREATE TABLE firmas_registros (
    id_firma BIGINT IDENTITY(1,1) PRIMARY KEY,
    tabla VARCHAR(128) NOT NULL,
    id_registro BIGINT NOT NULL,
    firma VARBINARY(MAX) NOT NULL,
    algoritmo VARCHAR(50) NOT NULL,
    id_usuario_firmante INT NULL,
    fecha_firma DATETIME2(0) NOT NULL
        CONSTRAINT DF_firmas_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT FK_firmas_usuario
        FOREIGN KEY (id_usuario_firmante)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
);
GO

CREATE INDEX IX_firmas_registros_tabla_id
ON firmas_registros(tabla, id_registro);
GO

/* ============================================================
   ESTUDIANTES, PROFESORES Y ADMINISTRATIVOS
   ============================================================ */

CREATE TABLE carreras (
    id_carrera INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_carreras_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_carreras_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_carreras_nombre UNIQUE (nombre)
);
GO

CREATE TABLE estudiantes (
    id_estudiante INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL,
    cip VARCHAR(30) NOT NULL,
    primer_nombre VARCHAR(60) NOT NULL,
    segundo_nombre VARCHAR(60) NULL,
    primer_apellido VARCHAR(60) NOT NULL,
    segundo_apellido VARCHAR(60) NULL,
    fecha_nacimiento DATE NOT NULL,
    id_carrera INT NOT NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_estudiantes_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_estudiantes_fecha DEFAULT SYSDATETIME(),
    fecha_actualizacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_estudiantes_fecha_actualizacion DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_estudiantes_cip UNIQUE (cip),
    CONSTRAINT UQ_estudiantes_usuario UNIQUE (id_usuario),

    CONSTRAINT FK_estudiantes_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario),

    CONSTRAINT FK_estudiantes_carrera
        FOREIGN KEY (id_carrera)
        REFERENCES carreras(id_carrera)
);
GO

CREATE INDEX IX_estudiantes_apellidos
ON estudiantes(primer_apellido, segundo_apellido);
GO

CREATE TABLE profesores (
    id_profesor INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL,
    cip VARCHAR(30) NOT NULL,
    primer_nombre VARCHAR(60) NOT NULL,
    segundo_nombre VARCHAR(60) NULL,
    primer_apellido VARCHAR(60) NOT NULL,
    segundo_apellido VARCHAR(60) NULL,
    departamento VARCHAR(150) NULL,
    especialidad VARCHAR(150) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_profesores_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_profesores_fecha DEFAULT SYSDATETIME(),
    fecha_actualizacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_profesores_fecha_actualizacion DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_profesores_cip UNIQUE (cip),
    CONSTRAINT UQ_profesores_usuario UNIQUE (id_usuario),

    CONSTRAINT FK_profesores_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
);
GO

CREATE TABLE administrativos (
    id_administrativo INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL,
    cip VARCHAR(30) NOT NULL,
    primer_nombre VARCHAR(60) NOT NULL,
    segundo_nombre VARCHAR(60) NULL,
    primer_apellido VARCHAR(60) NOT NULL,
    segundo_apellido VARCHAR(60) NULL,
    cargo VARCHAR(150) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_administrativos_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_administrativos_fecha DEFAULT SYSDATETIME(),
    fecha_actualizacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_administrativos_fecha_actualizacion DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_administrativos_cip UNIQUE (cip),
    CONSTRAINT UQ_administrativos_usuario UNIQUE (id_usuario),

    CONSTRAINT FK_administrativos_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
);
GO

/* ============================================================
   LIBROS, CATEGORÍAS, TEMAS E IMÁGENES
   ============================================================ */

CREATE TABLE categorias (
    id_categoria INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(500) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_categorias_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_categorias_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_categorias_nombre UNIQUE (nombre)
);
GO

CREATE TABLE temas (
    id_tema INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(500) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_temas_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_temas_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_temas_nombre UNIQUE (nombre)
);
GO

CREATE TABLE libros (
    id_libro INT IDENTITY(1,1) PRIMARY KEY,
    titulo VARCHAR(250) NOT NULL,
    autor VARCHAR(200) NOT NULL,
    isbn VARCHAR(30) NULL,
    editorial VARCHAR(150) NULL,
    anio_publicacion SMALLINT NULL,
    descripcion VARCHAR(MAX) NULL,
    costo DECIMAL(10,2) NOT NULL
        CONSTRAINT DF_libros_costo DEFAULT 0,
    existencias_totales INT NOT NULL
        CONSTRAINT DF_libros_existencias_totales DEFAULT 0,
    existencias_disponibles INT NOT NULL
        CONSTRAINT DF_libros_existencias_disponibles DEFAULT 0,
    imagen_nombre VARCHAR(255) NULL,
    imagen_ruta VARCHAR(500) NULL,
    thumbnail_nombre VARCHAR(255) NULL,
    thumbnail_ruta VARCHAR(500) NULL,
    ubicacion_fisica VARCHAR(200) NULL,
    id_categoria INT NOT NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_libros_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_libros_fecha DEFAULT SYSDATETIME(),
    fecha_actualizacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_libros_fecha_actualizacion DEFAULT SYSDATETIME(),

    CONSTRAINT FK_libros_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria),

    CONSTRAINT CK_libros_costo
        CHECK (costo >= 0),

    CONSTRAINT CK_libros_existencias_totales
        CHECK (existencias_totales >= 0),

    CONSTRAINT CK_libros_existencias_disponibles
        CHECK (
            existencias_disponibles >= 0
            AND existencias_disponibles <= existencias_totales
        ),

    CONSTRAINT CK_libros_anio
        CHECK (
            anio_publicacion IS NULL
            OR anio_publicacion BETWEEN 1000 AND 2100
        )
);
GO

CREATE UNIQUE INDEX UX_libros_isbn
ON libros(isbn)
WHERE isbn IS NOT NULL;
GO

CREATE INDEX IX_libros_titulo
ON libros(titulo);
GO

CREATE INDEX IX_libros_autor
ON libros(autor);
GO

CREATE INDEX IX_libros_categoria
ON libros(id_categoria);
GO

CREATE TABLE libros_temas (
    id_libro INT NOT NULL,
    id_tema INT NOT NULL,

    CONSTRAINT PK_libros_temas
        PRIMARY KEY (id_libro, id_tema),

    CONSTRAINT FK_libros_temas_libro
        FOREIGN KEY (id_libro)
        REFERENCES libros(id_libro)
        ON DELETE CASCADE,

    CONSTRAINT FK_libros_temas_tema
        FOREIGN KEY (id_tema)
        REFERENCES temas(id_tema)
        ON DELETE CASCADE
);
GO

/* ============================================================
   RESERVAS Y PRÉSTAMOS
   ============================================================ */

CREATE TABLE reservas (
    id_reserva BIGINT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_libro INT NOT NULL,
    fecha_reserva DATETIME2(0) NOT NULL
        CONSTRAINT DF_reservas_fecha DEFAULT SYSDATETIME(),
    fecha_vencimiento DATE NOT NULL,
    fecha_devolucion_real DATETIME2(0) NULL,
    cantidad INT NOT NULL
        CONSTRAINT DF_reservas_cantidad DEFAULT 1,
    estado VARCHAR(20) NOT NULL
        CONSTRAINT DF_reservas_estado DEFAULT 'Reservado',
    observacion VARCHAR(500) NULL,
    fecha_actualizacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_reservas_actualizacion DEFAULT SYSDATETIME(),

    CONSTRAINT FK_reservas_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario),

    CONSTRAINT FK_reservas_libro
        FOREIGN KEY (id_libro)
        REFERENCES libros(id_libro),

    CONSTRAINT CK_reservas_cantidad
        CHECK (cantidad > 0),

    CONSTRAINT CK_reservas_estado
        CHECK (
            estado IN (
                'Pendiente',
                'Reservado',
                'Prestado',
                'Devuelto',
                'Vencido',
                'Cancelado'
            )
        ),

    CONSTRAINT CK_reservas_fechas
        CHECK (
            fecha_vencimiento >= CAST(fecha_reserva AS DATE)
        )
);
GO

CREATE INDEX IX_reservas_fecha
ON reservas(fecha_reserva);
GO

CREATE INDEX IX_reservas_estado
ON reservas(estado);
GO

CREATE INDEX IX_reservas_usuario
ON reservas(id_usuario);
GO

CREATE INDEX IX_reservas_libro
ON reservas(id_libro);
GO

/* ============================================================
   SOLICITUDES DE LIBROS NO DISPONIBLES
   ============================================================ */

CREATE TABLE solicitudes_libros (
    id_solicitud BIGINT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo_libro VARCHAR(250) NOT NULL,
    autor VARCHAR(200) NULL,
    materia VARCHAR(150) NOT NULL,
    motivo_interes VARCHAR(1000) NOT NULL,
    fecha_solicitud DATETIME2(0) NOT NULL
        CONSTRAINT DF_solicitudes_fecha DEFAULT SYSDATETIME(),
    estado VARCHAR(20) NOT NULL
        CONSTRAINT DF_solicitudes_estado DEFAULT 'Pendiente',
    respuesta VARCHAR(1000) NULL,
    fecha_respuesta DATETIME2(0) NULL,
    id_usuario_responde INT NULL,

    CONSTRAINT FK_solicitudes_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario),

    CONSTRAINT FK_solicitudes_usuario_responde
        FOREIGN KEY (id_usuario_responde)
        REFERENCES usuarios(id_usuario),

    CONSTRAINT CK_solicitudes_estado
        CHECK (
            estado IN (
                'Pendiente',
                'En revisión',
                'Aprobada',
                'Rechazada',
                'Adquirida'
            )
        )
);
GO

CREATE INDEX IX_solicitudes_estado
ON solicitudes_libros(estado);
GO

CREATE INDEX IX_solicitudes_fecha
ON solicitudes_libros(fecha_solicitud);
GO

/* ============================================================
   PRÉSTAMO INTERBIBLIOTECARIO
   ============================================================ */

CREATE TABLE instituciones (
    id_institucion INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    direccion VARCHAR(500) NULL,
    telefono VARCHAR(30) NULL,
    correo VARCHAR(150) NULL,
    sitio_web VARCHAR(300) NULL,
    estado BIT NOT NULL
        CONSTRAINT DF_instituciones_estado DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_instituciones_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT UQ_instituciones_nombre UNIQUE (nombre)
);
GO

CREATE TABLE catalogo_externo (
    id_libro_externo INT IDENTITY(1,1) PRIMARY KEY,
    id_institucion INT NOT NULL,
    titulo VARCHAR(250) NOT NULL,
    autor VARCHAR(200) NOT NULL,
    isbn VARCHAR(30) NULL,
    descripcion VARCHAR(MAX) NULL,
    url_catalogo VARCHAR(500) NULL,
    disponible BIT NOT NULL
        CONSTRAINT DF_catalogo_externo_disponible DEFAULT 1,
    fecha_creacion DATETIME2(0) NOT NULL
        CONSTRAINT DF_catalogo_externo_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT FK_catalogo_externo_institucion
        FOREIGN KEY (id_institucion)
        REFERENCES instituciones(id_institucion)
);
GO

CREATE UNIQUE INDEX UX_catalogo_externo_institucion_isbn
ON catalogo_externo(id_institucion, isbn)
WHERE isbn IS NOT NULL;
GO

CREATE TABLE prestamos_interbibliotecarios (
    id_prestamo_interbibliotecario BIGINT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_libro_externo INT NOT NULL,
    fecha_solicitud DATETIME2(0) NOT NULL
        CONSTRAINT DF_prestamos_inter_fecha DEFAULT SYSDATETIME(),
    fecha_aprobacion DATETIME2(0) NULL,
    fecha_recepcion DATETIME2(0) NULL,
    fecha_vencimiento DATE NULL,
    fecha_devolucion DATETIME2(0) NULL,
    estado VARCHAR(30) NOT NULL
        CONSTRAINT DF_prestamos_inter_estado DEFAULT 'Solicitado',
    observacion VARCHAR(1000) NULL,

    CONSTRAINT FK_prestamos_inter_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario),

    CONSTRAINT FK_prestamos_inter_libro
        FOREIGN KEY (id_libro_externo)
        REFERENCES catalogo_externo(id_libro_externo),

    CONSTRAINT CK_prestamos_inter_estado
        CHECK (
            estado IN (
                'Solicitado',
                'Aprobado',
                'Rechazado',
                'Recibido',
                'Devuelto',
                'Cancelado'
            )
        )
);
GO

CREATE INDEX IX_prestamos_inter_fecha
ON prestamos_interbibliotecarios(fecha_solicitud);
GO

CREATE INDEX IX_prestamos_inter_estado
ON prestamos_interbibliotecarios(estado);
GO

/* ============================================================
   PÁGINA PÚBLICA - CONTÁCTENOS
   ============================================================ */

CREATE TABLE mensajes_contacto (
    id_mensaje BIGINT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    mensaje VARCHAR(2000) NOT NULL,
    estado VARCHAR(20) NOT NULL
        CONSTRAINT DF_mensajes_contacto_estado DEFAULT 'Nuevo',
    fecha DATETIME2(0) NOT NULL
        CONSTRAINT DF_mensajes_contacto_fecha DEFAULT SYSDATETIME(),

    CONSTRAINT CK_mensajes_contacto_estado
        CHECK (estado IN ('Nuevo', 'Leído', 'Respondido', 'Archivado'))
);
GO

/* ============================================================
   DATOS INICIALES
   ============================================================ */

INSERT INTO roles (nombre, descripcion)
VALUES
(N'Administrador', N'Control total del sistema'),
(N'Bibliotecario', N'Gestión de libros, reservas, usuarios y reportes'),
(N'Estudiante', N'Consulta y reserva de libros'),
(N'Profesor', N'Consulta y reserva de libros para docentes'),
(N'Administrativo', N'Consulta y reserva de libros para personal administrativo');
GO

INSERT INTO permisos (codigo, modulo, accion, descripcion)
VALUES
('usuarios.ver', 'usuarios', 'ver', N'Ver usuarios'),
('usuarios.crear', 'usuarios', 'crear', N'Crear usuarios'),
('usuarios.editar', 'usuarios', 'editar', N'Editar usuarios'),
('usuarios.eliminar', 'usuarios', 'eliminar', N'Desactivar o eliminar usuarios'),

('roles.gestionar', 'roles', 'gestionar', N'Gestionar roles y permisos'),

('estudiantes.ver', 'estudiantes', 'ver', N'Ver estudiantes'),
('estudiantes.crear', 'estudiantes', 'crear', N'Crear estudiantes'),
('estudiantes.editar', 'estudiantes', 'editar', N'Editar estudiantes'),
('estudiantes.eliminar', 'estudiantes', 'eliminar', N'Desactivar estudiantes'),

('profesores.ver', 'profesores', 'ver', N'Ver profesores'),
('profesores.crear', 'profesores', 'crear', N'Crear profesores'),
('profesores.editar', 'profesores', 'editar', N'Editar profesores'),
('profesores.eliminar', 'profesores', 'eliminar', N'Desactivar profesores'),

('administrativos.ver', 'administrativos', 'ver', N'Ver administrativos'),
('administrativos.crear', 'administrativos', 'crear', N'Crear administrativos'),
('administrativos.editar', 'administrativos', 'editar', N'Editar administrativos'),
('administrativos.eliminar', 'administrativos', 'eliminar', N'Desactivar administrativos'),

('categorias.ver', 'categorias', 'ver', N'Ver categorías'),
('categorias.crear', 'categorias', 'crear', N'Crear categorías'),
('categorias.editar', 'categorias', 'editar', N'Editar categorías'),
('categorias.eliminar', 'categorias', 'eliminar', N'Desactivar categorías'),

('libros.ver', 'libros', 'ver', N'Ver y buscar libros'),
('libros.crear', 'libros', 'crear', N'Crear libros'),
('libros.editar', 'libros', 'editar', N'Editar libros'),
('libros.eliminar', 'libros', 'eliminar', N'Desactivar libros'),

('reservas.ver', 'reservas', 'ver', N'Ver reservas'),
('reservas.crear', 'reservas', 'crear', N'Crear reservas'),
('reservas.aprobar', 'reservas', 'aprobar', N'Aprobar reservas'),
('reservas.devolver', 'reservas', 'devolver', N'Registrar devoluciones'),
('reservas.cancelar', 'reservas', 'cancelar', N'Cancelar reservas'),

('reportes.ver', 'reportes', 'ver', N'Ver reportes'),
('reportes.exportar_excel', 'reportes', 'exportar_excel', N'Exportar reportes a Excel'),

('solicitudes.ver', 'solicitudes', 'ver', N'Ver solicitudes de libros'),
('solicitudes.crear', 'solicitudes', 'crear', N'Crear solicitudes de libros'),
('solicitudes.gestionar', 'solicitudes', 'gestionar', N'Gestionar solicitudes de libros'),

('interbibliotecario.ver', 'interbibliotecario', 'ver', N'Ver préstamos interbibliotecarios'),
('interbibliotecario.crear', 'interbibliotecario', 'crear', N'Solicitar préstamos interbibliotecarios'),
('interbibliotecario.gestionar', 'interbibliotecario', 'gestionar', N'Gestionar préstamos interbibliotecarios'),

('logs.ver', 'logs', 'ver', N'Ver registros de acceso y errores');
GO

/* Administrador: todos los permisos */
INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = N'Administrador';
GO

/* Bibliotecario */
INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = N'Bibliotecario'
  AND (
        p.modulo IN (
            'estudiantes',
            'profesores',
            'administrativos',
            'categorias',
            'libros',
            'reservas',
            'reportes',
            'solicitudes',
            'interbibliotecario',
            'logs'
        )
        OR p.codigo = 'usuarios.ver'
      );
GO

/* Estudiante */
INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = N'Estudiante'
  AND p.codigo IN (
      'libros.ver',
      'reservas.ver',
      'reservas.crear',
      'reservas.cancelar',
      'solicitudes.ver',
      'solicitudes.crear',
      'interbibliotecario.ver',
      'interbibliotecario.crear'
  );
GO

/* Profesor */
INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = N'Profesor'
  AND p.codigo IN (
      'libros.ver',
      'reservas.ver',
      'reservas.crear',
      'reservas.cancelar',
      'solicitudes.ver',
      'solicitudes.crear',
      'interbibliotecario.ver',
      'interbibliotecario.crear'
  );
GO

/* Administrativo */
INSERT INTO roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = N'Administrativo'
  AND p.codigo IN (
      'libros.ver',
      'reservas.ver',
      'reservas.crear',
      'reservas.cancelar',
      'solicitudes.ver',
      'solicitudes.crear',
      'interbibliotecario.ver',
      'interbibliotecario.crear'
  );
GO

INSERT INTO categorias (nombre, descripcion)
VALUES
(N'Química', N'Libros relacionados con química'),
(N'Sistemas', N'Libros relacionados con informática y sistemas'),
(N'Lógica', N'Libros relacionados con lógica'),
(N'Matemática', N'Libros relacionados con matemática'),
(N'Estadística', N'Libros relacionados con estadística');
GO

INSERT INTO temas (nombre, descripcion)
VALUES
(N'Programación', N'Programación y desarrollo de software'),
(N'Bases de Datos', N'Diseño y administración de bases de datos'),
(N'Redes', N'Redes y comunicaciones'),
(N'Álgebra', N'Álgebra y operaciones matemáticas'),
(N'Cálculo', N'Cálculo diferencial e integral'),
(N'Probabilidad', N'Probabilidad y estadística'),
(N'Química General', N'Fundamentos de química');
GO

/* ============================================================
   VISTAS PARA REPORTES
   ============================================================ */

CREATE VIEW vw_libros_disponibilidad
AS
SELECT
    l.id_libro,
    l.titulo,
    l.autor,
    l.isbn,
    c.nombre AS categoria,
    l.costo,
    l.existencias_totales,
    l.existencias_disponibles,
    CASE
        WHEN l.estado = 1 AND l.existencias_disponibles > 0
            THEN N'Disponible'
        ELSE N'No disponible'
    END AS disponibilidad,
    l.imagen_ruta,
    l.thumbnail_ruta,
    l.ubicacion_fisica
FROM libros l
INNER JOIN categorias c
    ON c.id_categoria = l.id_categoria;
GO

CREATE VIEW vw_reporte_reservas
AS
SELECT
    r.id_reserva,
    r.fecha_reserva,
    r.fecha_vencimiento,
    r.fecha_devolucion_real,
    r.estado,
    r.cantidad,
    u.id_usuario,
    u.usuario,
    CASE
        WHEN e.id_estudiante IS NOT NULL THEN N'Estudiante'
        WHEN p.id_profesor IS NOT NULL THEN N'Docente'
        WHEN a.id_administrativo IS NOT NULL THEN N'Administrativo'
        ELSE N'Otro'
    END AS tipo_usuario,
    COALESCE(
        NULLIF(
            LTRIM(RTRIM(
                COALESCE(e.primer_nombre + ' ', '') +
                COALESCE(e.segundo_nombre + ' ', '') +
                COALESCE(e.primer_apellido + ' ', '') +
                COALESCE(e.segundo_apellido, '')
            )),
            ''
        ),
        NULLIF(
            LTRIM(RTRIM(
                COALESCE(p.primer_nombre + ' ', '') +
                COALESCE(p.segundo_nombre + ' ', '') +
                COALESCE(p.primer_apellido + ' ', '') +
                COALESCE(p.segundo_apellido, '')
            )),
            ''
        ),
        NULLIF(
            LTRIM(RTRIM(
                COALESCE(a.primer_nombre + ' ', '') +
                COALESCE(a.segundo_nombre + ' ', '') +
                COALESCE(a.primer_apellido + ' ', '') +
                COALESCE(a.segundo_apellido, '')
            )),
            ''
        ),
        u.nombre
    ) AS nombre_persona,
    l.id_libro,
    l.titulo,
    l.autor,
    c.nombre AS categoria,
    DATEDIFF(
        DAY,
        CAST(r.fecha_reserva AS DATE),
        COALESCE(
            CAST(r.fecha_devolucion_real AS DATE),
            CAST(SYSDATETIME() AS DATE)
        )
    ) AS dias_reservado
FROM reservas r
INNER JOIN usuarios u
    ON u.id_usuario = r.id_usuario
INNER JOIN libros l
    ON l.id_libro = r.id_libro
INNER JOIN categorias c
    ON c.id_categoria = l.id_categoria
LEFT JOIN estudiantes e
    ON e.id_usuario = u.id_usuario
LEFT JOIN profesores p
    ON p.id_usuario = u.id_usuario
LEFT JOIN administrativos a
    ON a.id_usuario = u.id_usuario;
GO

/* ============================================================
   PROCEDIMIENTOS PARA REPORTES, BÚSQUEDAS Y RESERVAS
   ============================================================ */

CREATE PROCEDURE sp_buscar_libros
    @texto VARCHAR(250)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT DISTINCT
        l.id_libro,
        l.titulo,
        l.autor,
        l.isbn,
        c.nombre AS categoria,
        l.descripcion,
        l.costo,
        l.existencias_totales,
        l.existencias_disponibles,
        CASE
            WHEN l.estado = 1 AND l.existencias_disponibles > 0
                THEN N'Disponible'
            ELSE N'No disponible'
        END AS disponibilidad,
        l.thumbnail_ruta
    FROM libros l
    INNER JOIN categorias c
        ON c.id_categoria = l.id_categoria
    WHERE
        l.estado = 1
        AND (
            l.titulo LIKE '%' + @texto + '%'
            OR l.autor LIKE '%' + @texto + '%'
            OR c.nombre LIKE '%' + @texto + '%'
            OR EXISTS (
                SELECT 1
                FROM libros_temas lt
                INNER JOIN temas t
                    ON t.id_tema = lt.id_tema
                WHERE lt.id_libro = l.id_libro
                  AND t.nombre LIKE '%' + @texto + '%'
            )
        )
    ORDER BY l.titulo;
END;
GO

CREATE PROCEDURE sp_reporte_reservas_por_fecha
    @fecha_inicio DATE,
    @fecha_fin DATE
AS
BEGIN
    SET NOCOUNT ON;

    IF @fecha_inicio > @fecha_fin
        THROW 50001, N'La fecha inicial no puede ser mayor que la fecha final.', 1;

    SELECT *
    FROM vw_reporte_reservas
    WHERE fecha_reserva >= @fecha_inicio
      AND fecha_reserva < DATEADD(DAY, 1, @fecha_fin)
    ORDER BY fecha_reserva DESC;
END;
GO

CREATE PROCEDURE sp_libros_mas_usados
    @fecha_inicio DATE,
    @fecha_fin DATE,
    @tipo_usuario VARCHAR(20) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @fecha_inicio > @fecha_fin
        THROW 50002, N'La fecha inicial no puede ser mayor que la fecha final.', 1;

    SELECT
        id_libro,
        titulo,
        autor,
        categoria,
        tipo_usuario,
        COUNT(*) AS total_reservas
    FROM vw_reporte_reservas
    WHERE fecha_reserva >= @fecha_inicio
      AND fecha_reserva < DATEADD(DAY, 1, @fecha_fin)
      AND (
            @tipo_usuario IS NULL
            OR tipo_usuario = @tipo_usuario
          )
    GROUP BY
        id_libro,
        titulo,
        autor,
        categoria,
        tipo_usuario
    ORDER BY total_reservas DESC, titulo;
END;
GO

CREATE PROCEDURE sp_crear_reserva
    @id_usuario INT,
    @id_libro INT,
    @dias INT = 7
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    IF @dias <= 0
        THROW 50003, N'La cantidad de días debe ser mayor que cero.', 1;

    IF NOT EXISTS (
        SELECT 1
        FROM usuarios
        WHERE id_usuario = @id_usuario
          AND estado = 1
          AND bloqueado = 0
    )
        THROW 50004, N'El usuario no existe, está inactivo o está bloqueado.', 1;

    BEGIN TRY
        BEGIN TRANSACTION;

        IF EXISTS (
            SELECT 1
            FROM reservas WITH (UPDLOCK, HOLDLOCK)
            WHERE id_usuario = @id_usuario
              AND id_libro = @id_libro
              AND estado IN ('Pendiente', 'Reservado', 'Prestado')
        )
            THROW 50005, N'El usuario ya tiene una reserva activa para este libro.', 1;

        UPDATE libros WITH (UPDLOCK, ROWLOCK)
        SET
            existencias_disponibles = existencias_disponibles - 1,
            fecha_actualizacion = SYSDATETIME()
        WHERE id_libro = @id_libro
          AND estado = 1
          AND existencias_disponibles > 0;

        IF @@ROWCOUNT = 0
            THROW 50006, N'El libro no existe, está inactivo o no tiene existencias disponibles.', 1;

        INSERT INTO reservas (
            id_usuario,
            id_libro,
            fecha_reserva,
            fecha_vencimiento,
            estado
        )
        VALUES (
            @id_usuario,
            @id_libro,
            SYSDATETIME(),
            DATEADD(DAY, @dias, CAST(SYSDATETIME() AS DATE)),
            'Reservado'
        );

        DECLARE @id_reserva BIGINT = SCOPE_IDENTITY();

        COMMIT TRANSACTION;

        SELECT @id_reserva AS id_reserva;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        THROW;
    END CATCH;
END;
GO

CREATE PROCEDURE sp_devolver_reserva
    @id_reserva BIGINT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRY
        BEGIN TRANSACTION;

        DECLARE @id_libro INT;

        SELECT @id_libro = id_libro
        FROM reservas WITH (UPDLOCK, HOLDLOCK)
        WHERE id_reserva = @id_reserva
          AND estado IN ('Reservado', 'Prestado');

        IF @id_libro IS NULL
            THROW 50007, N'La reserva no existe o ya fue cerrada.', 1;

        UPDATE reservas
        SET
            estado = 'Devuelto',
            fecha_devolucion_real = SYSDATETIME(),
            fecha_actualizacion = SYSDATETIME()
        WHERE id_reserva = @id_reserva;

        UPDATE libros
        SET
            existencias_disponibles =
                CASE
                    WHEN existencias_disponibles < existencias_totales
                        THEN existencias_disponibles + 1
                    ELSE existencias_disponibles
                END,
            fecha_actualizacion = SYSDATETIME()
        WHERE id_libro = @id_libro;

        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        THROW;
    END CATCH;
END;
GO

/* ============================================================
   CONSULTAS DE COMPROBACIÓN
   ============================================================ */

SELECT * FROM roles;
SELECT * FROM permisos;
SELECT * FROM categorias;
SELECT * FROM temas;
GO

/* ============================================================
   IMPORTANTE:
   No se inserta un administrador con contraseña fija por seguridad.

   Para crear el primer administrador:
   1. Generar el hash en PHP:
      password_hash('TuClaveSegura', PASSWORD_DEFAULT)

   2. Insertar el usuario reemplazando HASH_GENERADO:
      INSERT INTO usuarios
      (nombre, usuario, password_hash, correo)
      VALUES
      ('Administrador General', 'admin', 'HASH_GENERADO', 'admin@biblioteca.local');

   3. Asignar el rol:
      INSERT INTO usuarios_roles (id_usuario, id_rol)
      SELECT u.id_usuario, r.id_rol
      FROM usuarios u
      CROSS JOIN roles r
      WHERE u.usuario = 'admin'
        AND r.nombre = 'Administrador';
   ============================================================ */

/* ============================================================
   ACTUALIZACIÓN: ESTRUCTURA ACADÉMICA
   Facultad > Departamento > Carrera
   Compatible con SQL Server y segura para una BD ya existente.

   EJECUTAR UNA SOLA VEZ SOBRE BibliotecaDigitalDB.
   El script es idempotente: puede volver a ejecutarse sin duplicar datos.
   Incluye un catálogo base editable de 6 facultades, 30 departamentos
   y 65 carreras de pregrado/técnicas publicadas por la UTP.
   ============================================================ */

USE BibliotecaDigitalDB;
GO

/* ============================================================
   1. FACULTADES
   ============================================================ */

IF OBJECT_ID('dbo.facultades', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.facultades (
        id_facultad INT IDENTITY(1,1) PRIMARY KEY,
        nombre NVARCHAR(150) NOT NULL,
        descripcion NVARCHAR(500) NULL,
        estado BIT NOT NULL
            CONSTRAINT DF_facultades_estado DEFAULT 1,
        fecha_creacion DATETIME2(0) NOT NULL
            CONSTRAINT DF_facultades_fecha_creacion DEFAULT SYSDATETIME(),

        CONSTRAINT UQ_facultades_nombre UNIQUE (nombre)
    );
END;
GO

/* Asegura la longitud esperada cuando la tabla ya existía. */
IF COL_LENGTH('dbo.facultades', 'descripcion') IS NULL
BEGIN
    ALTER TABLE dbo.facultades
    ADD descripcion NVARCHAR(500) NULL;
END;
GO

IF COL_LENGTH('dbo.facultades', 'descripcion') IS NOT NULL
BEGIN
    ALTER TABLE dbo.facultades
    ALTER COLUMN descripcion NVARCHAR(500) NULL;
END;
GO

/* Facultades vigentes de la Universidad Tecnológica de Panamá. */
MERGE dbo.facultades AS destino
USING (
    VALUES
        (N'Facultad de Ciencias y Tecnología'),
        (N'Facultad de Ingeniería Civil'),
        (N'Facultad de Ingeniería Eléctrica'),
        (N'Facultad de Ingeniería Industrial'),
        (N'Facultad de Ingeniería Mecánica'),
        (N'Facultad de Ingeniería de Sistemas Computacionales')
) AS origen(nombre)
ON destino.nombre = origen.nombre
WHEN NOT MATCHED THEN
    INSERT (nombre, descripcion, estado)
    VALUES (origen.nombre, NULL, 1);
GO

/* ============================================================
   2. DEPARTAMENTOS
   ============================================================ */

IF OBJECT_ID('dbo.departamentos', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.departamentos (
        id_departamento INT IDENTITY(1,1) PRIMARY KEY,
        id_facultad INT NOT NULL,
        nombre NVARCHAR(150) NOT NULL,
        descripcion NVARCHAR(500) NULL,
        estado BIT NOT NULL
            CONSTRAINT DF_departamentos_estado DEFAULT 1,
        fecha_creacion DATETIME2(0) NOT NULL
            CONSTRAINT DF_departamentos_fecha_creacion DEFAULT SYSDATETIME(),

        CONSTRAINT FK_departamentos_facultades
            FOREIGN KEY (id_facultad)
            REFERENCES dbo.facultades(id_facultad)
            ON UPDATE CASCADE
            ON DELETE NO ACTION
    );
END;
GO

IF COL_LENGTH('dbo.departamentos', 'descripcion') IS NULL
BEGIN
    ALTER TABLE dbo.departamentos
    ADD descripcion NVARCHAR(500) NULL;
END;
GO

IF COL_LENGTH('dbo.departamentos', 'descripcion') IS NOT NULL
BEGIN
    ALTER TABLE dbo.departamentos
    ALTER COLUMN descripcion NVARCHAR(500) NULL;
END;
GO

IF NOT EXISTS (
    SELECT 1
    FROM sys.indexes
    WHERE name = 'UX_departamentos_facultad_nombre'
      AND object_id = OBJECT_ID('dbo.departamentos')
)
BEGIN
    CREATE UNIQUE INDEX UX_departamentos_facultad_nombre
    ON dbo.departamentos(id_facultad, nombre);
END;
GO

IF NOT EXISTS (
    SELECT 1
    FROM sys.indexes
    WHERE name = 'IX_departamentos_id_facultad'
      AND object_id = OBJECT_ID('dbo.departamentos')
)
BEGIN
    CREATE INDEX IX_departamentos_id_facultad
    ON dbo.departamentos(id_facultad);
END;
GO

/*
   Catálogo base de departamentos académicos publicado en los sitios
   oficiales de las facultades de la UTP. No sobrescribe descripciones
   ni estados que el administrador ya haya configurado.
*/
MERGE dbo.departamentos AS destino
USING (
    SELECT f.id_facultad, catalogo.nombre
    FROM (
        VALUES
            (N'Facultad de Ingeniería Civil', N'Geociencias Aplicadas y Transporte'),
            (N'Facultad de Ingeniería Civil', N'Ciencias Marítimas y Portuarias'),
            (N'Facultad de Ingeniería Civil', N'Mecánica Estructural'),
            (N'Facultad de Ingeniería Civil', N'Modelo Digital y Gestión de la Construcción'),
            (N'Facultad de Ingeniería Civil', N'Hidráulica, Sanitaria y Ciencias Ambientales'),

            (N'Facultad de Ingeniería Eléctrica', N'Ingeniería en Electrónica'),
            (N'Facultad de Ingeniería Eléctrica', N'Ingeniería en Sistemas de Comunicaciones'),
            (N'Facultad de Ingeniería Eléctrica', N'Ingeniería en Control e Instrumentación'),
            (N'Facultad de Ingeniería Eléctrica', N'Ingeniería en Sistemas de Potencia'),

            (N'Facultad de Ingeniería Industrial', N'Estadística y Economía'),
            (N'Facultad de Ingeniería Industrial', N'Finanzas y Contabilidad'),
            (N'Facultad de Ingeniería Industrial', N'Logística'),
            (N'Facultad de Ingeniería Industrial', N'Mercadeo'),
            (N'Facultad de Ingeniería Industrial', N'Producción'),
            (N'Facultad de Ingeniería Industrial', N'Recurso Humano'),

            (N'Facultad de Ingeniería Mecánica', N'Ingeniería Mecánica'),
            (N'Facultad de Ingeniería Mecánica', N'Ingeniería Aeronáutica y Aviación'),
            (N'Facultad de Ingeniería Mecánica', N'Ingeniería Naval'),
            (N'Facultad de Ingeniería Mecánica', N'Metal Mecánica'),
            (N'Facultad de Ingeniería Mecánica', N'Energía y Ambiente'),
            (N'Facultad de Ingeniería Mecánica', N'Ciencias e Ingeniería de Materiales'),
            (N'Facultad de Ingeniería Mecánica', N'Diseño de Sistemas y Componentes Mecánicos'),

            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Arquitectura y Redes de Computadoras'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Computación y Simulación de Sistemas'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Ingeniería de Software'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Programación de Computadoras'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Sistemas de Información, Control y Evaluación de Recursos Informáticos'),

            (N'Facultad de Ciencias y Tecnología', N'Ciencias Exactas'),
            (N'Facultad de Ciencias y Tecnología', N'Ciencias Naturales'),
            (N'Facultad de Ciencias y Tecnología', N'Ciencias Sociales y Humanísticas')
    ) AS catalogo(facultad, nombre)
    INNER JOIN dbo.facultades f
        ON f.nombre = catalogo.facultad
) AS origen
ON destino.id_facultad = origen.id_facultad
AND destino.nombre = origen.nombre
WHEN NOT MATCHED THEN
    INSERT (id_facultad, nombre, descripcion, estado)
    VALUES (origen.id_facultad, origen.nombre, N'Catálogo base académico UTP.', 1);
GO

/* ============================================================
   3. RELACIÓN DE CARRERAS
   ============================================================ */

IF COL_LENGTH('dbo.carreras', 'id_facultad') IS NULL
BEGIN
    ALTER TABLE dbo.carreras
    ADD id_facultad INT NULL;
END;
GO

IF COL_LENGTH('dbo.carreras', 'id_departamento') IS NULL
BEGIN
    ALTER TABLE dbo.carreras
    ADD id_departamento INT NULL;
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys
    WHERE name = 'FK_carreras_facultades'
)
BEGIN
    ALTER TABLE dbo.carreras
    ADD CONSTRAINT FK_carreras_facultades
        FOREIGN KEY (id_facultad)
        REFERENCES dbo.facultades(id_facultad)
        ON UPDATE CASCADE
        ON DELETE NO ACTION;
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys
    WHERE name = 'FK_carreras_departamentos'
)
BEGIN
    ALTER TABLE dbo.carreras
    ADD CONSTRAINT FK_carreras_departamentos
        FOREIGN KEY (id_departamento)
        REFERENCES dbo.departamentos(id_departamento)
        ON UPDATE NO ACTION
        ON DELETE NO ACTION;
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'IX_carreras_id_facultad'
      AND object_id = OBJECT_ID('dbo.carreras')
)
BEGIN
    CREATE INDEX IX_carreras_id_facultad
    ON dbo.carreras(id_facultad);
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'IX_carreras_id_departamento'
      AND object_id = OBJECT_ID('dbo.carreras')
)
BEGIN
    CREATE INDEX IX_carreras_id_departamento
    ON dbo.carreras(id_departamento);
END;
GO

/* Si una carrera ya tiene departamento, completa su facultad. */
UPDATE c
SET c.id_facultad = d.id_facultad
FROM dbo.carreras c
INNER JOIN dbo.departamentos d
    ON d.id_departamento = c.id_departamento
WHERE c.id_facultad IS NULL;
GO

/*
   Oferta académica base de pregrado y programas técnicos publicada en
   los sitios oficiales de las facultades de la UTP. Se asigna la facultad;
   el departamento responsable puede definirse desde el módulo administrativo.
*/
MERGE dbo.carreras AS destino
USING (
    SELECT f.id_facultad, catalogo.nombre
    FROM (
        VALUES
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Ingeniería Civil'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Topografía'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Edificaciones'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Dibujo Automatizado'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Ingeniería Geomática'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Ingeniería Marítima Portuaria'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Ingeniería Ambiental'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Saneamiento y Ambiente'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Operaciones Marítimas y Portuarias'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Ingeniería Geológica'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Ingeniería en Administración de Proyectos de Construcción'),
            (N'Facultad de Ingeniería Civil', N'Licenciatura en Modelado y Gestión Digital en Proyectos'),

            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería de Control y Automatización'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería Eléctrica'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería Eléctrica y Electrónica'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería Electromecánica'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería Electrónica'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería Electrónica Industrial'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería Electrónica y Telecomunicaciones'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Ingeniería en Telecomunicaciones'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Electrónica y Sistemas de Comunicación'),
            (N'Facultad de Ingeniería Eléctrica', N'Licenciatura en Sistemas Eléctricos y Automatización'),
            (N'Facultad de Ingeniería Eléctrica', N'Técnico en Autotrónica'),
            (N'Facultad de Ingeniería Eléctrica', N'Técnico en Ingeniería Electromecánica Industrial'),
            (N'Facultad de Ingeniería Eléctrica', N'Técnico en Electrónica Biomédica'),
            (N'Facultad de Ingeniería Eléctrica', N'Técnico en Sistemas Eléctricos'),
            (N'Facultad de Ingeniería Eléctrica', N'Técnico en Telecomunicaciones'),

            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Ingeniería en Seguridad Industrial e Higiene Ocupacional'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Ingeniería Industrial'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Gestión Administrativa'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Ingeniería Mecánica Industrial'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Logística y Transporte Multimodal'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Gestión de la Producción Industrial'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Mercadeo y Comercio Internacional'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Recursos Humanos y Gestión de la Productividad'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Ingeniería Logística y Cadena de Suministro'),
            (N'Facultad de Ingeniería Industrial', N'Licenciatura en Mercadeo y Negocios Internacionales'),

            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Ingeniería Mecánica'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Ingeniería de Mantenimiento'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Ingeniería Naval'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Ingeniería Aeronáutica'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Ingeniería de Energía y Ambiente'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Mecánica Industrial'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Refrigeración y Aire Acondicionado'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Mecánica Automotriz'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Soldadura'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Administración de Aviación con Opción a Vuelo (Piloto)'),
            (N'Facultad de Ingeniería Mecánica', N'Licenciatura en Administración de Aviación'),
            (N'Facultad de Ingeniería Mecánica', N'Técnico en Despacho de Vuelo'),
            (N'Facultad de Ingeniería Mecánica', N'Técnico en Ingeniería de Mantenimiento de Aeronaves con especialización en Motores y Fuselaje'),

            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Desarrollo y Gestión de Software'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Ingeniería de Sistemas de Información Gerencial'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Ciberseguridad'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Ciencias de la Computación'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Ingeniería de Sistemas de Información'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Ingeniería de Sistemas y Computación'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Ingeniería de Software'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Desarrollo de Software'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Informática Aplicada a la Educación'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Licenciatura en Redes Informáticas'),
            (N'Facultad de Ingeniería de Sistemas Computacionales', N'Técnico en Informática para la Gestión Empresarial'),

            (N'Facultad de Ciencias y Tecnología', N'Licenciatura en Ingeniería Forestal'),
            (N'Facultad de Ciencias y Tecnología', N'Licenciatura en Ingeniería en Alimentos'),
            (N'Facultad de Ciencias y Tecnología', N'Licenciatura en Comunicación Ejecutiva Bilingüe'),
            (N'Facultad de Ciencias y Tecnología', N'Licenciatura en Ingeniería Química')
    ) AS catalogo(facultad, nombre)
    INNER JOIN dbo.facultades f
        ON f.nombre = catalogo.facultad
) AS origen
ON destino.nombre = origen.nombre
WHEN NOT MATCHED THEN
    INSERT (id_facultad, id_departamento, nombre, descripcion, estado)
    VALUES (origen.id_facultad, NULL, origen.nombre, N'Oferta académica base UTP.', 1)
WHEN MATCHED AND destino.id_facultad IS NULL THEN
    UPDATE SET destino.id_facultad = origen.id_facultad;
GO

/* ============================================================
   4. RELACIÓN DE PROFESORES CON DEPARTAMENTOS
   ============================================================ */

IF COL_LENGTH('dbo.profesores', 'id_departamento') IS NULL
BEGIN
    ALTER TABLE dbo.profesores
    ADD id_departamento INT NULL;
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys
    WHERE name = 'FK_profesores_departamentos'
)
BEGIN
    ALTER TABLE dbo.profesores
    ADD CONSTRAINT FK_profesores_departamentos
        FOREIGN KEY (id_departamento)
        REFERENCES dbo.departamentos(id_departamento)
        ON UPDATE NO ACTION
        ON DELETE NO ACTION;
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = 'IX_profesores_id_departamento'
      AND object_id = OBJECT_ID('dbo.profesores')
)
BEGIN
    CREATE INDEX IX_profesores_id_departamento
    ON dbo.profesores(id_departamento);
END;
GO

/* ============================================================
   5. PERMISOS DEL MÓDULO
   ============================================================ */

MERGE dbo.permisos AS destino
USING (
    VALUES
        ('estructura.ver', 'estructura', 'ver', N'Ver facultades, departamentos y carreras'),
        ('estructura.crear', 'estructura', 'crear', N'Crear facultades, departamentos y carreras'),
        ('estructura.editar', 'estructura', 'editar', N'Editar facultades, departamentos y carreras'),
        ('estructura.eliminar', 'estructura', 'eliminar', N'Desactivar o eliminar registros académicos')
) AS origen(codigo, modulo, accion, descripcion)
ON destino.codigo = origen.codigo
WHEN NOT MATCHED THEN
    INSERT (codigo, modulo, accion, descripcion, estado)
    VALUES (origen.codigo, origen.modulo, origen.accion, origen.descripcion, 1)
WHEN MATCHED THEN
    UPDATE SET
        destino.modulo = origen.modulo,
        destino.accion = origen.accion,
        destino.descripcion = origen.descripcion,
        destino.estado = 1;
GO

/* Administrador y Bibliotecario pueden gestionar la estructura. */
INSERT INTO dbo.roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM dbo.roles r
CROSS JOIN dbo.permisos p
WHERE r.nombre IN (N'Administrador', N'Bibliotecario')
  AND p.codigo IN (
      'estructura.ver',
      'estructura.crear',
      'estructura.editar',
      'estructura.eliminar'
  )
  AND NOT EXISTS (
      SELECT 1
      FROM dbo.roles_permisos rp
      WHERE rp.id_rol = r.id_rol
        AND rp.id_permiso = p.id_permiso
  );
GO

/* ============================================================
   6. COMPROBACIÓN
   ============================================================ */

SELECT id_facultad, nombre, estado
FROM dbo.facultades
ORDER BY nombre;

SELECT id_departamento, id_facultad, nombre, estado
FROM dbo.departamentos
ORDER BY id_facultad, nombre;

SELECT id_carrera, id_facultad, id_departamento, nombre, estado
FROM dbo.carreras
ORDER BY nombre;
GO
