# 🚇 Metro de Trenes — Proyecto CRUD

Proyecto sencillo de CRUD (Crear, Leer, Actualizar, Eliminar) para la gestión de un metro de trenes.

## Tecnologías

- **PHP 8** (conexión a la base de datos)
- **MariaDB / MySQL** (almacenamiento)
- **HTML** (vistas)
- **JavaScript** (confirmaciones y buscador en tablas)
- **Tailwind CSS** por CDN (diseño, sin CSS puro)

## Módulos (cada uno con su propio archivo)

| Vista | Archivo | Tabla en la base de datos |
|---|---|---|
| Estaciones | `estaciones.php` | `estaciones` |
| Líneas | `lineas.php` | `lineas` |
| Trenes | `trenes.php` | `trenes` |
| Cocheras | `cocheras.php` | `cocheras` |
| Accesos estación | `accesos_estacion.php` | `accesos_estacion` |

Cada vista tiene una tabla con botones **Agregar**, **Editar** y **Eliminar** en cada fila.

## Estructura

```
metro/
├── config/conexion.php        # Datos de conexión a MariaDB
├── plantillas/cabecera.php    # Encabezado y menú común
├── plantillas/pie.php         # Pie de página común
├── js/comun.js                # Funciones de JavaScript comunes
├── index.php                  # Panel de inicio
├── estaciones.php             # Vista: Estaciones
├── lineas.php                 # Vista: Líneas
├── trenes.php                 # Vista: Trenes
├── cocheras.php               # Vista: Cocheras
└── accesos_estacion.php       # Vista: Accesos estación
```

## Cómo ejecutar

1. Revisa los datos de conexión en `config/conexion.php`.
2. Abre una terminal en la carpeta del proyecto.
3. Ejecuta el servidor de desarrollo de PHP:

   ```
   C:\xampp\php\php.exe -S localhost:8000
   ```

4. Abre en el navegador: **http://localhost:8000**

Requisitos: PHP 8 o superior con la extensión `pdo_mysql` activada.

> ⚠️ **Importante:** no subas `config/conexion.php` con tus credenciales a un repositorio público.
