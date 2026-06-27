document.addEventListener("DOMContentLoaded", () => {
    verificarSesion();
    cargarUsuarios();

    // Vinculación de los controladores de envío a los formularios correspondientes
    document.getElementById("form-crear-usuario").addEventListener("submit", ejecutarAltaUsuario);
    document.getElementById("form-permisos").addEventListener("submit", ejecutarModificarRol);
    document.getElementById("form-password").addEventListener("submit", ejecutarResetearPassword);
    document.getElementById("form-estado").addEventListener("submit", ejecutarCambiarEstado);
    document.getElementById("form-eliminar").addEventListener("submit", ejecutarEliminarUsuario);

    // Cierre adaptativo de menús contextuales de la tuerca al hacer clic fuera
    document.addEventListener("click", (e) => {
        if (!e.target.closest('.dropdown-acciones')) {
            document.querySelectorAll(".menu-contextual").forEach(menu => menu.classList.add("hidden"));
        }
    });
});

async function verificarSesion() {
    try {
        const res = await fetch("controllers/LoginController.php?accion=verificar");
        const data = await res.json();
        if (!data.autenticado || data.rol !== 'PersonalIT') {
            window.location.href = "login.html";
            return;
        }
        document.getElementById("nombre-usuario-display").textContent = `Bienvenido, ${data.usuario} (${data.rol})`;
    } catch (err) {
        window.location.href = "login.html";
    }
}

async function cargarUsuarios() {
    const tbody = document.getElementById("tabla-usuarios");
    try {
        const res = await fetch("controllers/UsuarioController.php?accion=listar");
        const usuarios = await res.json();
        tbody.innerHTML = "";

        usuarios.forEach(user => {
            const tr = document.createElement("tr");
            tr.className = "hover:bg-gray-50/50 transition-colors border-b border-gray-100";

            const badgeRol = `<span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-md bg-blue-50 text-blue-700 border border-blue-100">${user.rol}</span>`;

            const badgeEstado = user.estado === 'Activo'
                ? `<span class="px-2.5 py-1 inline-flex items-center text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>Activo</span>`
                : `<span class="px-2.5 py-1 inline-flex items-center text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-100"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>Suspendido</span>`;

            const accesoStr = user.ultimo_acceso ? user.ultimo_acceso : '<span class="text-gray-300 italic text-xs">Sin accesos</span>';

            tr.innerHTML = `
                <td class="p-4 font-semibold text-gray-800">${user.usuario}</td>
                <td class="p-4 text-gray-600">${user.nombre} ${user.apellido}</td>
                <td class="p-4 text-gray-600 font-medium">${user.legajo}</td>
                <td class="p-4">${badgeRol}</td>
                <td class="p-4">${badgeEstado}</td>
                <td class="p-4 text-xs text-gray-500 font-medium">${accesoStr}</td>
                <td class="p-4 text-center relative dropdown-acciones">
                    <button onclick="toggleMenuTuerca(event, ${user.id})" class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                    </button>
                    <div id="menu-${user.id}" class="menu-contextual hidden absolute right-12 top-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 z-50 py-1 text-left text-xs font-medium">
                        <button onclick="abrirModalPermisos(${user.id}, '${user.usuario}', '${user.rol}')" class="w-full px-4 py-2 hover:bg-gray-50 text-gray-700 flex items-center gap-2"><i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Editar permisos</button>
                        <button onclick="abrirModalPassword(${user.id}, '${user.usuario}')" class="w-full px-4 py-2 hover:bg-gray-50 text-gray-700 flex items-center gap-2"><i data-lucide="key-round" class="w-3.5 h-3.5"></i> Resetear contraseña</button>
                        <button onclick="abrirModalEstado(${user.id}, '${user.usuario}', '${user.estado}')" class="w-full px-4 py-2 hover:bg-gray-50 text-gray-700 flex items-center gap-2 border-t border-gray-50"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Cambiar estado</button>
                        <button onclick="abrirModalEliminar(${user.id}, '${user.usuario}')" class="w-full px-4 py-2 hover:bg-red-50 text-red-600 flex items-center gap-2 border-t border-gray-50"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Eliminar cuenta</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        lucide.createIcons();
    } catch (err) {
        console.error("Error al cargar listado de usuarios:", err);
    }
}

function toggleMenuTuerca(e, id) {
    e.stopPropagation();
    const yaAbierto = !document.getElementById(`menu-${id}`).classList.contains("hidden");
    document.querySelectorAll(".menu-contextual").forEach(menu => menu.classList.add("hidden"));
    if (!yaAbierto) {
        document.getElementById(`menu-${id}`).classList.remove("hidden");
    }
}

// =======================================================
// INTERFACES INTERACTIVAS DE MODALES (Apertura y Carga)
// =======================================================
function abrirModalAlta() { document.getElementById("modal-alta").showModal(); }

function abrirModalPermisos(id, usuario, rolActual) {
    document.getElementById("permisos-id").value = id;
    document.getElementById("permisos-usuario").textContent = usuario;
    document.getElementById("permisos-rol").value = rolActual;
    document.getElementById("modal-permisos").showModal();
}

function abrirModalPassword(id, usuario) {
    document.getElementById("form-password").reset();
    document.getElementById("password-id").value = id;
    document.getElementById("password-usuario").textContent = usuario;
    document.getElementById("modal-password").showModal();
}

function abrirModalEstado(id, usuario, estadoActual) {
    const targetEstado = estadoActual === 'Activo' ? 'Suspendido' : 'Activo';
    document.getElementById("estado-id").value = id;
    document.getElementById("estado-target").value = targetEstado;
    document.getElementById("estado-usuario").textContent = usuario;

    const actualBadge = document.getElementById("estado-actual-badge");
    const nuevoBadge = document.getElementById("estado-nuevo-badge");

    if (estadoActual === 'Activo') {
        actualBadge.className = "text-emerald-600 font-bold";
        actualBadge.textContent = "ACTIVO";
        nuevoBadge.className = "text-rose-600 font-bold";
        nuevoBadge.textContent = "SUSPENDIDO";
    } else {
        actualBadge.className = "text-rose-600 font-bold";
        actualBadge.textContent = "SUSPENDIDO";
        nuevoBadge.className = "text-emerald-600 font-bold";
        nuevoBadge.textContent = "ACTIVO";
    }

    document.getElementById("modal-estado").showModal();
}

function cerrarDialogo(id) {
    document.getElementById(id).close();

    // Limpieza de todos los contenedores de error al cerrar cualquier modal
    const errorDivAlta = document.getElementById("alta-error");
    if (errorDivAlta) errorDivAlta.classList.add("hidden");

    const errorDivEliminar = document.getElementById("eliminar-error");
    if (errorDivEliminar) errorDivEliminar.classList.add("hidden");
}

// =======================================================
// PROCESADORES TRANSACCIONALES ASÍNCRONOS (Fetch Requests)
// =======================================================
async function ejecutarAltaUsuario(e) {
    e.preventDefault();
    const errorDiv = document.getElementById("alta-error");
    errorDiv.classList.add("hidden");

    const usuario = document.getElementById("new-usuario").value.trim();
    const nombre = document.getElementById("new-nombre").value.trim();
    const apellido = document.getElementById("new-apellido").value.trim();
    const legajo = document.getElementById("new-legajo").value.trim();
    const password = document.getElementById("new-password").value;
    const rol = document.getElementById("new-rol").value;

    const res = await fetch("controllers/UsuarioController.php?accion=crear", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ usuario, nombre, apellido, legajo, password, rol })
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById("form-crear-usuario").reset();
        cerrarDialogo("modal-alta");
        cargarUsuarios();
    } else {
        errorDiv.textContent = data.error || "Fallo al procesar el alta.";
        errorDiv.classList.remove("hidden");
    }
}

async function ejecutarModificarRol(e) {
    e.preventDefault();
    const id = document.getElementById("permisos-id").value;
    const rol = document.getElementById("permisos-rol").value;

    const res = await fetch("controllers/UsuarioController.php?accion=modificarRol", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, rol })
    });
    const data = await res.json();
    if (data.success) {
        cerrarDialogo("modal-permisos");
        cargarUsuarios();
    } else {
        alert(data.error);
    }
}

async function ejecutarResetearPassword(e) {
    e.preventDefault();
    const id = document.getElementById("password-id").value;
    const password = document.getElementById("password-nueva").value;

    const res = await fetch("controllers/UsuarioController.php?accion=resetearContrasena", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, password })
    });
    const data = await res.json();
    if (data.success) {
        alert("La contraseña se blanqueó correctamente.");
        cerrarDialogo("modal-password");
    } else {
        alert(data.error);
    }
}

async function ejecutarCambiarEstado(e) {
    e.preventDefault();
    const id = document.getElementById("estado-id").value;
    const estado = document.getElementById("estado-target").value;

    const res = await fetch("controllers/UsuarioController.php?accion=modificarEstado", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, estado })
    });
    const data = await res.json();
    if (data.success) {
        cerrarDialogo("modal-estado");
        cargarUsuarios();
    } else {
        alert(data.error);
    }
}

// =======================================================
// NUEVAS FUNCIONES PARA ELIMINAR USUARIO
// =======================================================
function abrirModalEliminar(id, usuario) {
    document.getElementById("eliminar-id").value = id;
    document.getElementById("eliminar-usuario").textContent = usuario;
    document.getElementById("modal-eliminar").showModal();
}

async function ejecutarEliminarUsuario(e) {
    e.preventDefault();
    const id = document.getElementById("eliminar-id").value;

    // Capturamos el contenedor de error y lo ocultamos al iniciar la petición
    const errorDiv = document.getElementById("eliminar-error");
    errorDiv.classList.add("hidden");

    try {
        const res = await fetch(`controllers/UsuarioController.php?accion=eliminar&id=${id}`);
        const data = await res.json();

        if (data.success) {
            cerrarDialogo("modal-eliminar");
            cargarUsuarios();
        } else {
            // Si el backend rechaza la operación, mostramos el error elegante
            errorDiv.textContent = data.error || "No se pudo eliminar el usuario.";
            errorDiv.classList.remove("hidden");
        }
    } catch (err) {
        errorDiv.textContent = "Error de red al intentar borrar la cuenta.";
        errorDiv.classList.remove("hidden");
    }
}

async function cerrarSesion() { await fetch("controllers/LoginController.php?accion=logout"); window.location.href = "login.html"; }