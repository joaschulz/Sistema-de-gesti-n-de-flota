# Sistema de Gestión de Flota - CELO Fleet

CELO Fleet es un sistema integral basado en web desarrollado para la gestión, mantenimiento y control de la flota de vehículos de la cooperativa. Su arquitectura sigue un modelo Modelo-Vista-Controlador (MVC) y cuenta con un sólido control de acceso basado en roles (RBAC).

## Características Principales

1. **Gestión de Roles y Accesos (RBAC)**: Diferenciación de usuarios (`JefeTaller`, `Chofer`, `Admin`, `PersonalIT`), donde cada rol tiene acceso a vistas y permisos específicos.
2. **Dashboard Interactivo (Kanban)**: Un panel dinámico para el **Jefe de Taller** que permite administrar el ciclo de vida de los vehículos (Operativo -> Alerta -> En Taller) con actualización asíncrona (AJAX).
3. **Registro de Intervenciones Técnicas**: Permite asentar fallas, costos de reparación y adjuntar evidencias digitales (imágenes, reportes) normalizadas en base de datos.
4. **Notificaciones Multicanal**:
   - **Telegram**: Notifica cambios de estado de las unidades de forma inmediata a múltiples personas o grupos mediante un bot.
   - **Email (SMTP)**: Envía reportes formales de taller con adjuntos utilizando PHPMailer.
5. **Panel IT**: Interfaz de administración segura para crear usuarios, suspender cuentas, resetear contraseñas y modificar roles, equipado con reglas de seguridad anti-lockout.

## Estructura de Archivos y Directorios

- `assets/`
  - `img/`: Logos y recursos visuales corporativos.
  - `js/`: Lógica Frontend (`JefeTaller.js`, `PanelIT.js`, etc) que gestiona llamadas Fetch/AJAX y renderizado del DOM.
  - `uploads/`: Carpeta de destino donde se guardan temporalmente las imágenes/evidencias subidas antes de ser referenciadas por la BD.
  - `celo_fleet.sql`: Script de creación y estructura relacional de la Base de Datos con mocks.
- `config/`
  - `Conexion.php`: Singleton para conectar con la base de datos vía PDO de forma segura.
  - `Seguridad.php`: Configuraciones o validaciones de sesión.
- `controllers/`
  - `LoginController.php`: Valida las credenciales (Usuario/Contraseña), gestiona la sesión e intercepta inicios de sesión no autorizados o suspendidos.
  - `UsuarioController.php`: Expone la API para el Panel IT (ABM de usuarios).
  - `VehiculoController.php`: Coordina el registro de intervenciones, guardado de evidencias, y dispara los eventos de notificación.
- `dao/` (Data Access Objects)
  - `UsuarioDAO.php`: Interactúa con las tablas `USUARIO` y `ROL`.
  - `IntervencionDAO.php`: Ejecuta transacciones SQL para actualizar `VEHICULO`, `INTERVENCION_TECNICA` y `EVIDENCIA_DIGITAL`.
- `services/`
  - `NotificacionService.php`: Encapsula la lógica de integración externa (cURL para Telegram y PHPMailer para correos electrónicos SMTP).
- `vendor/`: Dependencias de terceros (como PHPMailer).
- **Vistas**:
  - `login.html`: Pantalla de inicio de sesión público corporativo.
  - `JefeTaller.php`: Interfaz protegida para gestión de mantenimientos.
  - `PanelIT.php`: Interfaz protegida de soporte técnico.
- `setup.php`: Script utilitario temporal para recrear/resetear las tablas y cargar datos de prueba iniciales con hashes de contraseñas correctos.

## Requisitos de Instalación

1. Servidor web local (XAMPP / WAMP / LAMP) con Apache y PHP 7.4+.
2. MySQL o MariaDB.
3. Importar el archivo `assets/celo_fleet.sql` en tu base de datos para generar la estructura.
4. (Opcional) Ejecutar desde el navegador `http://localhost/ruta-al-proyecto/setup.php` para inyectar datos de prueba limpios.
5. Editar `services/NotificacionService.php` para colocar las credenciales reales del Bot de Telegram y la cuenta de Gmail autorizada.
