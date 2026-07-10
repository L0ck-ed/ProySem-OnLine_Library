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
Database/biblioteca.sql
```

3. Revisar la configuración de conexión en:

```text
App/configs/database_config.php
```

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

## Estado del proyecto

El proyecto se encuentra en fase de organización y desarrollo.  
Actualmente se está trabajando en mejorar la estructura de carpetas, nombres de archivos y separación de responsabilidades para que el sistema sea más fácil de mantener y ampliar.

---

## Autores

- Castillo Anthony, 8-1023-2265
- Domínguez Rubén, 8-988-2361
- González Eduardo, 8-1018-1193
- Rosales Nicole, 8-1031-1508

Proyecto desarrollado para la materia **Desarrollo de Software VII**.

Profesora: Ing. Irina Fong.

**Proyecto Semestral - Biblioteca Online**
