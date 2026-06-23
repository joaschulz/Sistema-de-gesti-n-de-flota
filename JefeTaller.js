// Componente de Estado Aislado (Mínimo acoplamiento de variables de entorno)
let patenteSeleccionada = "";
let archivosSeleccionados = [];

// Seguros globales integrados para interceptar y anular el secuestro de archivos del navegador
window.addEventListener("dragover", e => e.preventDefault(), false);
window.addEventListener("drop", e => e.preventDefault(), false);

// ==========================================
// 1. CONTROL DE RENDERIZADO ASINCRÓNICO (TABLERO KANBAN)
// ==========================================
async function cargarTablero() {
    try {
        const respuesta = await fetch("VehiculoController.php?accion=listar");
        const vehiculos = await respuesta.json();

        // Resolución dinámica y diferida de elementos para evitar acoplamiento temporal con el DOM
        const colOperativos = document.getElementById('columna-operativos');
        const colAlertas = document.getElementById('columna-alertas');
        const colTaller = document.getElementById('columna-taller');

        if (colOperativos) colOperativos.innerHTML = "";
        if (colAlertas) colAlertas.innerHTML = "";
        if (colTaller) colTaller.innerHTML = "";

        vehiculos.forEach(v => {
            const tarjeta = document.createElement('div');
            tarjeta.dataset.patente = v.patente;

            if (v.estado === 'Operativo') {
                tarjeta.className = "tarjeta-vehiculo bg-white border border-black/10 rounded-xl p-4 shadow-sm transition-all duration-300";
                tarjeta.innerHTML = `
                    <div class='flex justify-between items-center mb-1'>
                        <h3 class='text-lg font-extrabold tracking-wide text-gray-900'>${v.patente}</h3>
                        <span class='badge-km bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md text-xs font-bold'>${v.kilometraje >= 1000 ? (v.kilometraje / 1000) + 'k' : v.kilometraje}</span>
                    </div>
                    <p class='text-sm text-gray-500 font-medium'>${v.modelo}</p>`;
                if (colOperativos) colOperativos.appendChild(tarjeta);

            } else if (v.estado === 'Alerta') {
                tarjeta.className = "tarjeta-vehiculo bg-white border border-yellow-200 rounded-xl p-4 shadow-sm transition-all duration-300";
                tarjeta.innerHTML = `
                    <div class='flex justify-between items-center mb-1'>
                        <h3 class='text-lg font-extrabold tracking-wide text-gray-900'>${v.patente}</h3>
                    </div>
                    <p class='text-sm text-gray-500 font-medium mb-3'>${v.modelo}</p>
                    <div class='caja-alerta bg-[#fef9c3] px-3 py-2 rounded-lg mb-4 flex gap-2 items-start border border-yellow-100'>
                        <p class='text-xs text-[#713f12] font-semibold leading-relaxed'>Novedad: ${v.novedades || 'Inspección requerida'}</p>
                    </div>
                    <footer class='bloque-botones flex gap-3'>
                        <button onclick="procesarAccion('${v.patente}', 'darDeAlta')" class='flex-1 bg-[#ecfdf5] text-[#065f46] py-2 rounded-lg text-sm font-bold cursor-pointer'>Ignorar</button>
                        <button onclick="procesarAccion('${v.patente}', 'enviarATaller', 'Alerta')" class='flex-1 bg-[#fff5f5] text-[#991b1b] py-2 rounded-lg text-sm font-bold cursor-pointer'>A Taller</button>
                    </footer>`;
                if (colAlertas) colAlertas.appendChild(tarjeta);

            } else if (v.estado === 'En Taller') {
                tarjeta.className = "tarjeta-vehiculo bg-white border-2 border-red-500 rounded-xl p-4 shadow-sm relative overflow-hidden transition-all duration-300";
                tarjeta.innerHTML = `
                    <div class='mb-1'><h3 class='text-lg font-extrabold tracking-wide text-gray-900'>${v.patente}</h3></div>
                    <p class='text-sm text-gray-500 font-medium mb-3'>${v.modelo}</p>
                    <div class='caja-causa bg-gray-50 px-3 py-2 rounded-lg mb-4 flex gap-2 items-start border border-black/5'>
                        <p class='text-xs text-gray-600 font-medium leading-relaxed'>Causa: ${v.causa}</p>
                    </div>
                    <footer class='bloque-botones flex flex-col gap-2'>
                        <button onclick="abrirModal('${v.patente}')" class='w-full bg-[#eff6ff] text-[#1d4ed8] py-2 rounded-lg text-sm font-bold cursor-pointer'>Registrar Intervencion</button>
                        <button onclick="procesarAccion('${v.patente}', 'darDeAlta')" class='w-full bg-[#10b981] text-white py-2 rounded-lg text-sm font-bold cursor-pointer'>Dar de Alta</button>
                    </footer>`;
                if (colTaller) colTaller.appendChild(tarjeta);
            }
        });

        actualizarContadores();
    } catch (err) {
        console.error("Error en el ciclo de carga del tablero:", err);
    }
}

function actualizarContadores() {
    const cOp = document.getElementById('columna-operativos');
    const cAl = document.getElementById('columna-alertas');
    const cTa = document.getElementById('columna-taller');

    const contOp = document.getElementById('contador-operativos');
    const contAl = document.getElementById('contador-alertas');
    const contTa = document.getElementById('contador-taller');

    if (contOp && cOp) contOp.textContent = cOp.children.length;
    if (contAl && cAl) contAl.textContent = cAl.children.length;
    if (contTa && cTa) contTa.textContent = cTa.children.length;
}

// ==========================================
// 2. DESPACHADOR DE ACCIONES DIRECTAS
// ==========================================
async function procesarAccion(patente, endpoint, causa = "") {
    try {
        await fetch(`VehiculoController.php?accion=${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ patente, causa })
        });
        cargarTablero();
    } catch (err) {
        console.error("Fallo en la comunicación con el despachador:", err);
    }
}

// ==========================================
// 3. CAPTURA DRAG & DROP E INICIALIZACIÓN DE CONTEXTO
// ==========================================
function abrirModal(patente) {
    patenteSeleccionada = patente;
    const modalTitulo = document.getElementById('modal-unidad-titulo');
    const modal = document.getElementById('modal-intervencion');

    if (modalTitulo) modalTitulo.textContent = "Unidad: " + patente;
    archivosSeleccionados = [];
    actualizarPreview();
    if (modal) modal.showModal();
}

document.addEventListener("DOMContentLoaded", () => {
    const inputArchivo = document.getElementById('archivo-upload') || document.getElementById('archivos-upload');
    const dropZone = document.getElementById('drop-zone');

    if (dropZone && inputArchivo) {
        inputArchivo.addEventListener('change', e => {
            if (e.target.files.length > 0) {
                archivosSeleccionados = archivosSeleccionados.concat(Array.from(e.target.files));
                actualizarPreview();
                inputArchivo.value = '';
            }
        });

        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('bg-blue-100', 'border-blue-500');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-blue-100', 'border-blue-500');
        });

        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('bg-blue-100', 'border-blue-500');
            if (e.dataTransfer.files.length > 0) {
                archivosSeleccionados = archivosSeleccionados.concat(Array.from(e.dataTransfer.files));
                actualizarPreview();
            }
        });
    }

    // Configuración diferida de envío del formulario
    const formIntervencion = document.querySelector('form');
    if (formIntervencion) {
        formIntervencion.addEventListener('submit', ejecutarEnvioFormulario);
    }

    // Configuración diferida de botones de cancelación nativos
    const btnCerrar = document.getElementById('btn-cerrar-modal');
    const btnCancelar = document.getElementById('btn-cancelar');
    const modal = document.getElementById('modal-intervencion');

    if (btnCerrar) btnCerrar.addEventListener('click', () => modal && modal.close());
    if (btnCancelar) btnCancelar.addEventListener('click', () => modal && modal.close());

    cargarTablero();
});

// ==========================================
// 4. PREVISUALIZACIÓN MULTIMEDIA AISLADA
// ==========================================
function actualizarPreview() {
    const previewContainer = document.getElementById('preview-archivos');
    if (!previewContainer) return;
    previewContainer.innerHTML = '';

    archivosSeleccionados.forEach((file, index) => {
        let miniatura = file.type.startsWith('image/')
            ? `<img src="${URL.createObjectURL(file)}" class="h-10 w-10 object-cover rounded border border-gray-300 shrink-0">`
            : `<div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center text-[10px] font-bold text-gray-600 shrink-0">DOC</div>`;

        const div = document.createElement('div');
        div.className = "flex justify-between items-center bg-white border border-gray-200 p-2 rounded-md shadow-sm gap-3 mt-2";
        div.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden">
                ${miniatura}
                <span class="truncate text-xs font-semibold text-gray-800">${file.name}</span>
            </div>
            <button type="button" class="text-red-500 font-extrabold px-3 py-1 hover:bg-red-50 rounded cursor-pointer" onclick="eliminarArchivo(${index})">X</button>`;
        previewContainer.appendChild(div);
    });
}

window.eliminarArchivo = function (index) {
    archivosSeleccionados.splice(index, 1);
    actualizarPreview();
};

// ==========================================
// 5. MOTOR DE COMPRESIÓN (PIPELINE EN FRONTERA)
// ==========================================
async function comprimirImagen(file, maxSizeMB = 2) {
    return new Promise((resolve) => {
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        if (!file.type.startsWith('image/') || file.size <= maxSizeBytes) {
            resolve(file);
            return;
        }
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                let width = img.width;
                let height = img.height;
                if (width > 1920 || height > 1920) {
                    if (width > height) { height = Math.round(height * (1920 / width)); width = 1920; }
                    else { width = Math.round(width * (1920 / height)); height = 1920; }
                }
                canvas.width = width; canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                let quality = 0.9;
                const loop = () => {
                    canvas.toBlob((blob) => {
                        if (blob.size > maxSizeBytes && quality > 0.1) { quality -= 0.1; loop(); }
                        else { resolve(new File([blob], file.name, { type: 'image/jpeg' })); }
                    }, 'image/jpeg', quality);
                };
                loop();
            };
        };
    });
}

// ==========================================
// 6. GESTIÓN MULTIPART Y FLUJO EN RED (ENVÍO)
// ==========================================
async function ejecutarEnvioFormulario(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    let textOriginal = "Registrar";

    if (btn) { textOriginal = btn.innerHTML; btn.innerHTML = "Subiendo..."; btn.disabled = true; }

    const tipo = document.getElementById('tipo')?.value || 'Correctivo';
    const detalle = document.getElementById('detalle')?.value || '';
    const costo = document.getElementById('costo')?.value || 0;
    const modal = document.getElementById('modal-intervencion');

    const data = new FormData();
    data.append('patente', patenteSeleccionada);
    data.append('tipo', tipo);
    data.append('detalle', detalle);
    data.append('costo', costo);

    for (let i = 0; i < archivosSeleccionados.length; i++) {
        const fileReady = await comprimirImagen(archivosSeleccionados[i], 2);
        data.append('evidencias[]', fileReady);
    }

    try {
        await fetch("VehiculoController.php?accion=enviarATaller", { method: 'POST', body: data });
        form.reset();
        archivosSeleccionados = [];
        actualizarPreview();
        if (modal) modal.close();
        cargarTablero();
    } catch (err) {
        console.error("Excepción crítica en la capa de transporte multipart:", err);
    } finally {
        if (btn) { btn.innerHTML = textOriginal; btn.disabled = false; }
    }
}