/* ============================================================
   ACTUALIZACIÓN DE MEJORAS DE RÚBRICA
   - Firma digital de registros
   - Préstamo interbibliotecario
   - Formulario público de contacto
   - Permisos y asignación de roles
   Compatible con SQL Server / BibliotecaDigitalDB.
   Es idempotente y puede ejecutarse más de una vez.
   ============================================================ */
USE BibliotecaDigitalDB;
GO

IF OBJECT_ID('dbo.firmas_registros', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.firmas_registros (
        id_firma BIGINT IDENTITY(1,1) PRIMARY KEY,
        tabla VARCHAR(128) NOT NULL,
        id_registro BIGINT NOT NULL,
        firma VARBINARY(MAX) NOT NULL,
        algoritmo VARCHAR(50) NOT NULL,
        id_usuario_firmante INT NULL,
        fecha_firma DATETIME2(0) NOT NULL CONSTRAINT DF_firmas_fecha_mejoras DEFAULT SYSDATETIME(),
        CONSTRAINT FK_firmas_usuario_mejoras FOREIGN KEY (id_usuario_firmante)
            REFERENCES dbo.usuarios(id_usuario) ON DELETE SET NULL
    );
    CREATE INDEX IX_firmas_registros_tabla_id_mejoras
        ON dbo.firmas_registros(tabla, id_registro);
END;
GO

IF OBJECT_ID('dbo.instituciones', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.instituciones (
        id_institucion INT IDENTITY(1,1) PRIMARY KEY,
        nombre VARCHAR(200) NOT NULL UNIQUE,
        direccion VARCHAR(500) NULL,
        telefono VARCHAR(30) NULL,
        correo VARCHAR(150) NULL,
        sitio_web VARCHAR(300) NULL,
        estado BIT NOT NULL CONSTRAINT DF_instituciones_estado_mejoras DEFAULT 1,
        fecha_creacion DATETIME2(0) NOT NULL CONSTRAINT DF_instituciones_fecha_mejoras DEFAULT SYSDATETIME()
    );
END;
GO

IF OBJECT_ID('dbo.catalogo_externo', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.catalogo_externo (
        id_libro_externo INT IDENTITY(1,1) PRIMARY KEY,
        id_institucion INT NOT NULL,
        titulo VARCHAR(250) NOT NULL,
        autor VARCHAR(200) NOT NULL,
        isbn VARCHAR(30) NULL,
        descripcion VARCHAR(MAX) NULL,
        url_catalogo VARCHAR(500) NULL,
        disponible BIT NOT NULL CONSTRAINT DF_catalogo_externo_disponible_mejoras DEFAULT 1,
        fecha_creacion DATETIME2(0) NOT NULL CONSTRAINT DF_catalogo_externo_fecha_mejoras DEFAULT SYSDATETIME(),
        CONSTRAINT FK_catalogo_externo_institucion_mejoras FOREIGN KEY (id_institucion)
            REFERENCES dbo.instituciones(id_institucion)
    );
END;
GO

IF OBJECT_ID('dbo.prestamos_interbibliotecarios', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.prestamos_interbibliotecarios (
        id_prestamo_interbibliotecario BIGINT IDENTITY(1,1) PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_libro_externo INT NOT NULL,
        fecha_solicitud DATETIME2(0) NOT NULL CONSTRAINT DF_prestamos_inter_fecha_mejoras DEFAULT SYSDATETIME(),
        fecha_aprobacion DATETIME2(0) NULL,
        fecha_recepcion DATETIME2(0) NULL,
        fecha_vencimiento DATE NULL,
        fecha_devolucion DATETIME2(0) NULL,
        estado VARCHAR(30) NOT NULL CONSTRAINT DF_prestamos_inter_estado_mejoras DEFAULT 'Solicitado',
        observacion VARCHAR(1000) NULL,
        CONSTRAINT FK_prestamos_inter_usuario_mejoras FOREIGN KEY (id_usuario) REFERENCES dbo.usuarios(id_usuario),
        CONSTRAINT FK_prestamos_inter_libro_mejoras FOREIGN KEY (id_libro_externo) REFERENCES dbo.catalogo_externo(id_libro_externo),
        CONSTRAINT CK_prestamos_inter_estado_mejoras CHECK (estado IN ('Solicitado','Aprobado','Rechazado','Recibido','Devuelto','Cancelado'))
    );
    CREATE INDEX IX_prestamos_inter_fecha_mejoras ON dbo.prestamos_interbibliotecarios(fecha_solicitud);
    CREATE INDEX IX_prestamos_inter_estado_mejoras ON dbo.prestamos_interbibliotecarios(estado);
END;
GO

IF OBJECT_ID('dbo.mensajes_contacto', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.mensajes_contacto (
        id_mensaje BIGINT IDENTITY(1,1) PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        correo VARCHAR(150) NOT NULL,
        asunto VARCHAR(200) NOT NULL,
        mensaje VARCHAR(2000) NOT NULL,
        estado VARCHAR(20) NOT NULL CONSTRAINT DF_mensajes_contacto_estado_mejoras DEFAULT 'Nuevo',
        fecha DATETIME2(0) NOT NULL CONSTRAINT DF_mensajes_contacto_fecha_mejoras DEFAULT SYSDATETIME(),
        CONSTRAINT CK_mensajes_contacto_estado_mejoras CHECK (estado IN ('Nuevo','Leído','Respondido','Archivado'))
    );
END;
GO

MERGE dbo.permisos AS destino
USING (VALUES
    ('interbibliotecario.ver', 'interbibliotecario', 'ver', N'Ver préstamos interbibliotecarios'),
    ('interbibliotecario.crear', 'interbibliotecario', 'crear', N'Solicitar préstamos interbibliotecarios'),
    ('interbibliotecario.gestionar', 'interbibliotecario', 'gestionar', N'Gestionar instituciones, catálogo y solicitudes'),
    ('reportes.exportar_excel', 'reportes', 'exportar_excel', N'Exportar reportes e inventario a Excel')
) AS origen(codigo, modulo, accion, descripcion)
ON destino.codigo = origen.codigo
WHEN NOT MATCHED THEN
    INSERT (codigo, modulo, accion, descripcion, estado)
    VALUES (origen.codigo, origen.modulo, origen.accion, origen.descripcion, 1)
WHEN MATCHED THEN UPDATE SET destino.estado=1, destino.descripcion=origen.descripcion;
GO

INSERT INTO dbo.roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM dbo.roles r
CROSS JOIN dbo.permisos p
WHERE r.nombre = N'Administrador'
  AND p.codigo IN ('interbibliotecario.ver','interbibliotecario.crear','interbibliotecario.gestionar','reportes.exportar_excel')
  AND NOT EXISTS (
      SELECT 1 FROM dbo.roles_permisos rp
      WHERE rp.id_rol=r.id_rol AND rp.id_permiso=p.id_permiso
  );
GO

INSERT INTO dbo.roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM dbo.roles r
CROSS JOIN dbo.permisos p
WHERE r.nombre = N'Bibliotecario'
  AND p.codigo IN ('interbibliotecario.ver','interbibliotecario.crear','interbibliotecario.gestionar','reportes.exportar_excel')
  AND NOT EXISTS (
      SELECT 1 FROM dbo.roles_permisos rp
      WHERE rp.id_rol=r.id_rol AND rp.id_permiso=p.id_permiso
  );
GO

INSERT INTO dbo.roles_permisos (id_rol, id_permiso)
SELECT r.id_rol, p.id_permiso
FROM dbo.roles r
CROSS JOIN dbo.permisos p
WHERE r.nombre IN (N'Estudiante', N'Profesor', N'Administrativo')
  AND p.codigo IN ('interbibliotecario.ver','interbibliotecario.crear')
  AND NOT EXISTS (
      SELECT 1 FROM dbo.roles_permisos rp
      WHERE rp.id_rol=r.id_rol AND rp.id_permiso=p.id_permiso
  );
GO

PRINT 'Actualización de mejoras de rúbrica aplicada correctamente.';
GO
