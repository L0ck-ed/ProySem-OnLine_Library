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
