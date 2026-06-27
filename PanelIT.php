<?php
require_once __DIR__ . '/config/Seguridad.php';
Seguridad::protegerVista(['PersonalIT']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel IT - Gestión de Accesos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans p-6">

    <main class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Panel de Control IT</h1>
                <p class="text-sm text-gray-500">Administración Global de Usuarios y Roles de Acceso (RBAC)</p>
            </div>
            <div class="flex items-center gap-4">
                <span id="nombre-usuario-display" class="text-sm font-medium text-gray-700"></span>
                <button onclick="cerrarSesion()" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-bold hover:bg-red-100 transition-colors">Cerrar Sesión</button>
            </div>
        </header>

        <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-700">Usuarios Registrados en el Sistema</h2>
                <button onclick="abrirModalAlta()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm">+ Crear Nuevo Usuario</button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left border-collapse bg-white">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">Usuario</th>
                            <th class="p-4">Nombre Completo</th>
                            <th class="p-4">Legajo</th>
                            <th class="p-4">Rol Asignado</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4">Último Acceso</th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-usuarios" class="divide-y divide-gray-100 text-sm text-gray-600">
                        </tbody>
                </table>
            </div>
        </section>
    </main>

    <dialog id="modal-alta" class="rounded-xl shadow-2xl border border-gray-100 p-6 w-full max-w-md bg-white backdrop:bg-black/40">
        <header class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-800">Registrar Nuevo Usuario</h3>
            <button onclick="cerrarDialogo('modal-alta')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </header>
        <div id="alta-error" class="hidden mb-4 p-3 bg-red-50 text-red-600 border border-red-100 rounded-lg text-xs font-medium text-center"></div>
        <form id="form-crear-usuario" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nombre de Usuario</label>
                <input type="text" id="new-usuario" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nombre</label>
                <input type="text" id="new-nombre" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Apellido</label>
                <input type="text" id="new-apellido" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Legajo</label>
                <input type="text" id="new-legajo" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Contraseña Inicial</label>
                <input type="password" id="new-password" required minlength="8" placeholder="Mínimo 8 caracteres" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Rol Operativo</label>
                <select id="new-rol" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="PersonalCampo">Personal de Campo (Chofer)</option>
                    <option value="JefeTaller">Jefe de Taller</option>
                    <option value="Admin">Administrador General / Gerente</option>
                    <option value="PersonalIT">Personal IT (Superusuario)</option>
                </select>
            </div>
            <footer class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onclick="cerrarDialogo('modal-alta')" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Guardar Usuario</button>
            </footer>
        </form>
    </dialog>

    <dialog id="modal-permisos" class="rounded-xl shadow-2xl border border-gray-100 p-6 w-full max-w-md bg-white backdrop:bg-black/40">
        <header class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-800">Modificar Permisos de Acceso</h3>
            <button onclick="cerrarDialogo('modal-permisos')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </header>
        <form id="form-permisos" class="space-y-4">
            <input type="hidden" id="permisos-id">
            <div>
                <p class="text-sm text-gray-600 mb-3">Modificando los privilegios lógicos para el usuario: <b id="permisos-usuario" class="text-gray-800"></b></p>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nuevo Rol Asignado</label>
                <select id="permisos-rol" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="PersonalCampo">Personal de Campo (Chofer)</option>
                    <option value="JefeTaller">Jefe de Taller</option>
                    <option value="Admin">Administrador General / Gerente</option>
                    <option value="PersonalIT">Personal IT (Superusuario)</option>
                </select>
            </div>
            <footer class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onclick="cerrarDialogo('modal-permisos')" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Actualizar Rol</button>
            </footer>
        </form>
    </dialog>

    <dialog id="modal-password" class="rounded-xl shadow-2xl border border-gray-100 p-6 w-full max-w-md bg-white backdrop:bg-black/40">
        <header class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-800">Restablecer Contraseña</h3>
            <button onclick="cerrarDialogo('modal-password')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </header>
        <form id="form-password" class="space-y-4">
            <input type="hidden" id="password-id">
            <div>
                <p class="text-sm text-gray-600 mb-3">Establecer una nueva clave credencial para: <b id="password-usuario" class="text-gray-800"></b></p>
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nueva Contraseña Temporal</label>
                <input type="password" id="password-nueva" required minlength="8" placeholder="Mínimo 8 caracteres" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <footer class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onclick="cerrarDialogo('modal-password')" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Blanquear Clave</button>
            </footer>
        </form>
    </dialog>

    <dialog id="modal-estado" class="rounded-xl shadow-2xl border border-gray-100 p-6 w-full max-w-md bg-white backdrop:bg-black/40">
        <header class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-800">Modificar Estado Operativo</h3>
            <button onclick="cerrarDialogo('modal-estado')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </header>
        <form id="form-estado" class="space-y-4">
            <input type="hidden" id="estado-id">
            <input type="hidden" id="estado-target">
            <div class="p-1">
                <p class="text-sm text-gray-600 leading-relaxed">
                    ¿Está seguro de que desea cambiar el estado del usuario <b id="estado-usuario" class="text-gray-800"></b>? 
                    Actualmente se encuentra en estado administrativo <span id="estado-actual-badge"></span> y pasará a estar <span id="estado-nuevo-badge"></span>.
                </p>
                <p class="text-xs text-amber-600 font-medium mt-3 bg-amber-50 border border-amber-100 p-2.5 rounded-lg flex items-center gap-2">
                    ⚠️ Nota: Los usuarios suspendidos no podrán superar la pantalla de autenticación.
                </p>
            </div>
            <footer class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onclick="cerrarDialogo('modal-estado')" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Confirmar Cambio</button>
            </footer>
        </form>
    </dialog>

    <dialog id="modal-eliminar" class="rounded-xl shadow-2xl border border-gray-100 p-6 w-full max-w-md bg-white backdrop:bg-black/40">
        <header class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
            <h3 class="text-lg font-bold text-gray-800">Eliminar Cuenta de Usuario</h3>
            <button onclick="cerrarDialogo('modal-eliminar')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
        </header>
        
        <div id="eliminar-error" class="hidden mb-4 p-3 bg-red-50 text-red-600 border border-red-100 rounded-lg text-xs font-bold text-center"></div>

        <form id="form-eliminar" class="space-y-4">
            <input type="hidden" id="eliminar-id">
            <div class="p-1">
                <p class="text-sm text-gray-600 leading-relaxed">
                    ¿Está absolutamente seguro de que desea eliminar al usuario <b id="eliminar-usuario" class="text-gray-800"></b>?
                </p>
                <p class="text-xs text-red-600 font-medium mt-3 bg-red-50 border border-red-100 p-2.5 rounded-lg flex items-center gap-2">
                    ⚠️ Advertencia: Esta acción es irreversible. El usuario perderá el acceso al sistema permanentemente y se eliminarán sus credenciales.
                </p>
            </div>
            <footer class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onclick="cerrarDialogo('modal-eliminar')" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg font-medium">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition-colors">Sí, Eliminar Permanentemente</button>
            </footer>
        </form>
    </dialog>

    <script src="assets/js/PanelIT.js?v=1.1"></script>
</body>
</html>