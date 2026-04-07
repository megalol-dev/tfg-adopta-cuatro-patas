# 🐾 TFG - Adopta Cuatro Patas

Aplicación web desarrollada como Trabajo de Fin de Grado (DAW) orientada a la gestión de adopciones de animales.

La plataforma permite visualizar animales disponibles y gestionar solicitudes de adopción mediante un panel de administración sencillo e intuitivo.

---

## 📌 Tecnologías utilizadas

* HTML5
* CSS3
* PHP
* SQLite
* Git / GitHub

---

## 📁 Estructura del proyecto

```
/raiz
│
├── index.php                # Página principal (listado de animales)
├── conexion.php            # Conexión a la base de datos
├── seguridad.php           # Control de sesiones y roles
│
├── /admin
│   ├── panel_control.php   # Panel principal
│   ├── crear_usuario.php   # CRUD usuarios
│   ├── gestionar_animales.php  # CRUD animales
│   ├── gestionar_peticiones.php # Gestión solicitudes
│
├── /css
│   └── estilos.css         # Estilos globales
│
├── /img
│   └── (imágenes del proyecto)
│
└── database.sqlite         # Base de datos SQLite
```

---

## ⚙️ Funcionamiento general

La aplicación sigue una arquitectura basada en:

* Interfaz → HTML + CSS
* Lógica → PHP
* Datos → SQLite

El usuario interactúa mediante formularios web que envían datos al servidor usando métodos HTTP (GET y POST), los cuales son procesados por PHP y almacenados en la base de datos.

---

## 🔄 Sistemas CRUD

El sistema cuenta con tres módulos principales:

### 1. Animales

* Crear animales
* Editar información
* Cambiar estado (disponible, adoptado, oculto)
* Eliminar

### 2. Usuarios

* Crear empleados
* Asignar roles (gerente / ayudante)
* Activar / desactivar usuarios

### 3. Solicitudes

* Registro de peticiones de adopción
* Gestión de solicitudes (pendientes / gestionadas)

---

## 🔗 Endpoints principales

| Endpoint                          | Descripción               |
| --------------------------------- | ------------------------- |
| `/index.php`                      | Visualización de animales |
| `/admin/panel_control.php`        | Acceso al panel           |
| `/admin/crear_usuario.php`        | Gestión de usuarios       |
| `/admin/gestionar_animales.php`   | CRUD animales             |
| `/admin/gestionar_peticiones.php` | Gestión solicitudes       |

---

## 🔐 Seguridad

* Control de sesiones mediante PHP
* Restricción por roles (gerente / ayudante)
* Protección de rutas mediante validación de acceso

---

## 🎥 Explicación del proyecto

El funcionamiento completo de la aplicación se explica en el siguiente vídeo:

👉 [[*Enlace a YouTube)*](https://youtu.be/d0DctFAVvTI?si=VlOaWkX8LiGdKeHn)

---

## 📌 Notas

* Aplicación desarrollada con fines educativos
* Pensada para ser sencilla, funcional y fácil de usar
* Orientada a usuarios con conocimientos tecnológicos básicos

---

## 👨‍💻 Autor

José Luis Escudero Polo / Hafsa M´rabit-Daiidi Jaouhari
GitHub: https://github.com/megalol-dev

---
