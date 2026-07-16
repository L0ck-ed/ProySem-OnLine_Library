# Proyecto Semestral - DSW 7

## Biblioteca Online

Este proyecto consiste en el desarrollo de una **Biblioteca Online** utilizando PHP bajo una estructura organizada tipo **MVC**.  
El sistema busca administrar funciones básicas relacionadas con usuarios y acceso al sistema, manteniendo una separación clara entre la lógica, las vistas, los modelos y los archivos públicos.

---

## Objetivo del proyecto

Desarrollar una aplicación web para una biblioteca online, organizada de forma modular, que permita manejar usuarios, autenticación y una estructura base preparada para futuras funciones como gestión de libros, préstamos, reportes y administración del sistema.

---

## Estructura del proyecto

El proyecto está organizado de la siguiente manera:

```text
ProySem-OnLine_Library
├── App
│   ├── configs
│   ├── controllers
│   ├── core
│   ├── helpers
│   ├── middleware
│   ├── models
│   ├── services
│   └── views
│
├── Database
│   └── biblioteca.sql
│
├── Public
│   ├── assets
│   └── index.php
│
├── .htaccess
├── composer.json
└── readme.md
```

---

## Descripción de carpetas

### App

Contiene la parte principal del sistema.

- `configs`: archivos de configuración general y conexión a la base de datos.
- `controllers`: controladores encargados de recibir las rutas y coordinar las acciones.
- `core`: clases base del proyecto, como controlador, modelo y router.
- `helpers`: clases auxiliares para sesión, validación, sanitización y logs.
- `middleware`: verificaciones antes de acceder a ciertas rutas, como autenticación.
- `models`: modelos encargados de interactuar con la base de datos.
- `services`: lógica interna del sistema, como el servicio de autenticación.
- `views`: pantallas que ve el usuario.

### Public

Contiene el punto de entrada del sistema y los archivos públicos.

- `index.php`: archivo principal que recibe las peticiones.
- `assets`: recursos como CSS, JavaScript e imágenes.

### Database

Contiene el archivo SQL necesario para crear o importar la base de datos del proyecto.

---

## Convención de nombres utilizada

Para mantener el proyecto más ordenado, se utiliza la siguiente convención:

```text
Carpetas principales  → PascalCase
Subcarpetas           → camelCase
Archivos              → snake_case en minúscula
```

Ejemplo:

```text
App
Public
Database

assets
controllers
views

login_controller.php
auth_service.php
database_config.php
```

---

## Funciones actuales del sistema

Actualmente el proyecto cuenta con una estructura base para:

- Inicio de sesión.
- Cierre de sesión.
- Protección de rutas privadas.
- Gestión básica de usuarios.
- Vista de dashboard.
- Uso de vistas parciales como header, navbar, sidebar y footer.
- Organización bajo el patrón Modelo-Vista-Controlador.

---

## Tecnologías utilizadas

- PHP
- HTML
- CSS
- JavaScript
- Apache
- Composer
- Base de datos SQL

---

## Requisitos

Para ejecutar el proyecto se necesita:

- Servidor local como XAMPP, WAMP o Laragon.
- PHP instalado.
- Apache activo.
- Base de datos creada/importada usando el archivo SQL del proyecto.
- Composer, en caso de usar autoload o dependencias.

---

## Instalación básica

1. Copiar el proyecto dentro de la carpeta del servidor local.

Ejemplo en XAMPP:

```text
C:/xampp/htdocs/ProySem-OnLine_Library
```

2. Importar la base de datos desde:

```text
DataBase/biblioteca.sql
```

3. Configurar la conexión local copiando el ejemplo:

```text
App/Configs/database.local.php.example
```

como:

```text
App/Configs/database.local.php
```

También se pueden utilizar las variables de entorno `DB_DRIVER`, `DB_SERVER`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

4. Ejecutar el proyecto desde el navegador:

```text
http://localhost/ProySem-OnLine_Library/
```

---

## Patrón MVC

El proyecto está organizado usando el patrón MVC:

### Modelo

Se encarga de la conexión y manejo de datos.

Ejemplo:

```text
App/models/usuario.php
```

### Vista

Se encarga de mostrar la interfaz al usuario.

Ejemplo:

```text
App/views/user/crear.php
App/views/user/listar.php
```

### Controlador

Recibe las acciones del usuario y conecta las vistas con los modelos o servicios.

Ejemplo:

```text
App/controllers/usuario_controller.php
```


---

## Actualización: estructura académica y estadísticas

Esta versión incorpora el módulo administrativo **Estructura académica**, con CRUD de facultades, departamentos y carreras, además del análisis de demanda de libros por unidad académica.

### Base de datos ya existente

Ejecuta una sola vez en SQL Server Management Studio:

```text
DataBase/actualizacion_estructura_academica.sql
```

El script conserva los registros existentes, crea las relaciones faltantes, agrega los permisos del módulo y precarga un catálogo base editable de **6 facultades, 30 departamentos y 65 carreras/programas técnicos** publicado por las facultades de la UTP.

Después de ejecutarlo:

1. Cierra sesión y vuelve a iniciar como Administrador o Bibliotecario.
2. Abre **Estructura académica** y revisa el catálogo precargado.
3. Actualiza, añade, desactiva o elimina los registros que correspondan.
4. Asigna, cuando aplique, el departamento responsable de cada carrera.
5. Revisa los estudiantes y profesores existentes para asignarles su carrera o departamento correcto.
6. Abre **Estadísticas** para analizar uso confirmado o demanda solicitada por facultad, carrera, departamento e intervalo.

Los registros relacionados no se eliminan de forma definitiva; deben desactivarse para conservar el historial académico y de préstamos.

---

## Actualización: mejoras completas de la rúbrica

Para una base de datos que ya existe, ejecuta una sola vez:

```text
DataBase/actualizacion_mejoras_rubrica.sql
```

Esta actualización agrega o garantiza:

- Contrato criptográfico común para hashing de contraseñas y firma digital RSA-SHA256.
- Firma y verificación de integridad de los registros de libros.
- Exportación a Excel del inventario respetando la búsqueda aplicada.
- Servicio reutilizable para imágenes originales y thumbnails.
- Módulo de préstamo interbibliotecario para administradores y usuarios regulares.
- Página pública con Stack, importancia de las bibliotecas digitales y Contáctenos.
- Protección CSRF en todos los formularios POST.
- Manejo global de errores, registro de incidentes y encabezados de seguridad.

Extensiones de PHP requeridas: `pdo_sqlsrv` y `sqlsrv` para SQL Server, `gd`, `openssl` y `fileinfo`.

Las llaves de firma se generan automáticamente en `App/Storage/keys` durante la primera firma. No compartas la llave privada ni la subas a un repositorio público.

---

## Estado del proyecto

El proyecto se encuentra en fase de organización y desarrollo.  
Actualmente se está trabajando en mejorar la estructura de carpetas, nombres de archivos y separación de responsabilidades para que el sistema sea más fácil de mantener y ampliar.

---

## Autores

- Castillo Anthony, 8-1023-2265
- Domínguez Rubén, 8-988-2361
- González Eduardo, 8-1018-1193
- Rosales Nicole, 8-1031-1508
- Guillermo Siuki, 8-1020-658

Proyecto desarrollado para la materia **Desarrollo de Software VII**.

Profesora: Ing. Irina Fong.

**Proyecto Semestral - Biblioteca Online**
