// ========================================================
// CAPA DE PRESENTACION (LOGICA DE INTERFAZ ASINCRONICA)
// ========================================================

// Referencias a las columnas y contadores
const colOperativos = document.getElementById('columna-operativos');
const colAlertas = document.getElementById('columna-alertas');
const colTaller = document.getElementById('columna-taller');
const contOperativos = document.getElementById('contador-operativos');
const contAlertas = document.getElementById('contador-alertas');
const contTaller = document.getElementById('contador-taller');

// Referencias al Modal y Formulario
const modal = document.getElementById('modal-intervencion');
const modalTitulo = document.getElementById('modal-unidad-titulo');
const formIntervencion = document.querySelector('form');

// Referencias a los Archivos
const inputArchivos = document.getElementById('archivos-upload');
const previewArchivos = document.getElementById('preview-archivos');

// Variables Globales
let patenteSeleccionada = "";
let archivosSeleccionados = [];

// ==========================================
// 1. CARGAR TABLERO
// ==========================================
async function cargarTablero() {
    try {
        const respuesta = await fetch("VehiculoController.php?accion=listar");
        const vehiculos = await respuesta.json();

        if (colOperativos) colOperativos.innerHTML = "";
        if (colAlertas) colAlertas.innerHTML = "";
        if (colTaller) colTaller.innerHTML = "";

        vehiculos.forEach(v => {
            const tarjeta = document.createElement('div');
            tarjeta.dataset.patente = v.patente;

            if (v.estado === 'Operativo') {
                tarjeta.className = "tarjeta-vehiculo bg-white border border-black/10 rounded-xl p-4 shadow-sm transition-all duration-300";
                tarjeta.innerHTML =
                    "<div class='flex justify-between items-center mb-1'>" +
                    "<h3 class='text-lg font-extrabold tracking-wide text-gray-900'>" + v.patente + "</h3>" +
                    "<span class='badge-km bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md text-xs font-bold'>" + (v.kilometraje >= 1000 ? (v.kilometraje / 1000) + 'k' : v.kilometraje) + "</span>" +
                    "</div>" +
                    "<p class='text-sm text-gray-500 font-medium'>" + v.modelo + "</p>";
                if (colOperativos) colOperativos.appendChild(tarjeta);

            } else if (v.estado === 'Alerta') {
                tarjeta.className = "tarjeta-vehiculo bg-white border border-yellow-200 rounded-xl p-4 shadow-sm transition-all duration-300";
                tarjeta.innerHTML =
                    "<div class='flex justify-between items-center mb-1'>" +
                    "<h3 class='text-lg font-extrabold tracking-wide text-gray-900'>" + v.patente + "</h3>" +
                    "</div>" +
                    "<p class='text-sm text-gray-500 font-medium mb-3'>" + v.modelo + "</p>" +
                    "<div class='caja-alerta bg-[#fef9c3] px-3 py-2 rounded-lg mb-4 flex gap-2 items-start border border-yellow-100'>" +
                    "<p class='text-xs text-[#713f12] font-semibold leading-relaxed'>Novedad: " + (v.novedades || 'Inspeccion requerida') + "</p>" +
                    "</div>" +
                    "<footer class='bloque-botones flex gap-3'>" +
                    "<button onclick=\"procesarAccion('" + v.patente + "', 'darDeAlta')\" class='flex-1 bg-[#ecfdf5] text-[#065f46] py-2 rounded-lg text-sm font-bold'>Ignorar</button>" +
                    "<button onclick=\"procesarAccion('" + v.patente + "', 'enviarATaller', 'Alerta: " + (v.novedades || 'Inspeccion') + "')\" class='flex-1 bg-[#fff5f5] text-[#991b1b] py-2 rounded-lg text-sm font-bold'>A Taller</button>" +
                    "</footer>";
                if (colAlertas) colAlertas.appendChild(tarjeta);

            } else if (v.estado === 'En Taller') {
                tarjeta.className = "tarjeta-vehiculo bg-white border-2 border-red-500 rounded-xl p-4 shadow-sm relative overflow-hidden transition-all duration-300";
                tarjeta.innerHTML =
                    "<div class='mb-1'><h3 class='text-lg font-extrabold tracking-wide text-gray-900'>" + v.patente + "</h3></div>" +
                    "<p class='text-sm text-gray-500 font-medium mb-3'>" + v.modelo + "</p>" +
                    "<div class='caja-causa bg-gray-50 px-3 py-2 rounded-lg mb-4 flex gap-2 items-start border border-black/5'>" +
                    "<p class='text-xs text-gray-600 font-medium leading-relaxed'>Causa: " + (v.causa || 'Mantenimiento en curso') + "</p>" +
                    "</div>" +
                    "<footer class='bloque-botones flex flex-col gap-2'>" +
                    "<button onclick=\"abrirModal('" + v.patente + "')\" class='w-full bg-[#eff6ff] text-[#1d4ed8] py-2 rounded-lg text-sm font-bold'>Registrar Intervencion</button>" +
                    "<button onclick=\"procesarAccion('" + v.patente + "', 'darDeAlta')\" class='w-full bg-[#10b981] text-white py-2 rounded-lg text-sm font-bold'>Dar de Alta</button>" +
                    "</footer>";
                if (colTaller) colTaller.appendChild(tarjeta);
            }
        });

        // Actualizar Contadores
        if (contOperativos) contOperativos.textContent = colOperativos.children.length;
        if (contAlertas) contAlertas.textContent = colAlertas.children.length;
        if (contTaller) contTaller.textContent = colTaller.children.length;

    } catch (err) {
        console.error("Error en la capa de presentacion:", err);
    }
}

// ==========================================
// 2. PROCESAR ACCIONES RAPIDAS
// ==========================================
async function procesarAccion(patente, endpoint, causaMensaje = "") {
    try {
        // Para acciones simples enviamos JSON
        await fetch("VehiculoController.php?accion=" + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ patente: patente, causa: causaMensaje })
        });
        cargarTablero();
    } catch (err) {
        console.error("Error al procesar accion:", err);
    }
}

// ==========================================
// 3. LOGICA DEL MODAL Y ARCHIVOS
// ==========================================
function abrirModal(patente) {
    patenteSeleccionada = patente;
    if (modalTitulo) modalTitulo.textContent = "Unidad: " + patente;
    if (modal) modal.showModal();
}

// Escuchar seleccion de archivos
if (inputArchivos) {
    inputArchivos.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        archivosSeleccionados = archivosSeleccionados.concat(files);
        actualizarPreview();
    });
}

// Actualizar lista visual de archivos
function actualizarPreview() {
    if (!previewArchivos) return;
    previewArchivos.innerHTML = '';
    archivosSeleccionados.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = "flex justify-between items-center bg-white border border-gray-200 p-2 rounded-md shadow-sm";
        div.innerHTML = `
            <span class="truncate w-3/4">${file.name}</span>
            <span class="text-red-500 cursor-pointer font-bold px-2 hover:bg-red-50 rounded" onclick="eliminarArchivo(${index})">X</span>
        `;
        previewArchivos.appendChild(div);
    });
}

// Eliminar archivo de la lista
window.eliminarArchivo = function (index) {
    archivosSeleccionados.splice(index, 1);
    actualizarPreview();
}

// ==========================================
// 4. MOTOR DE COMPRESION DE IMAGENES (CANVAS)
// ==========================================
async function comprimirImagen(file, maxSizeMB = 2) {
    return new Promise((resolve) => {
        const maxSizeBytes = maxSizeMB * 1024 * 1024;

        // Si no es imagen o ya pesa menos del limite, no hacer nada
        if (!file.type.startsWith('image/') || file.size <= maxSizeBytes) {
            resolve(file);
            return;
        }

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                // Redimensionar resolucion base (Maximo 1920px)
                let width = img.width;
                let height = img.height;
                const maxDim = 1920;

                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round(height * (maxDim / width));
                        width = maxDim;
                    } else {
                        width = Math.round(width * (maxDim / height));
                        height = maxDim;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                // Algoritmo de compresion recursiva
                let quality = 0.9;
                const comprimir = () => {
                    canvas.toBlob((blob) => {
                        if (blob.size > maxSizeBytes && quality > 0.1) {
                            quality -= 0.1;
                            comprimir();
                        } else {
                            resolve(new File([blob], file.name, { type: 'image/jpeg' }));
                        }
                    }, 'image/jpeg', quality);
                };
                comprimir();
            };
        };
    });
}

// ==========================================
// 5. ENVIO DEL FORMULARIO CON FORMDATA
// ==========================================
if (formIntervencion) {
    formIntervencion.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Deshabilitar boton para evitar doble click
        const btnSubmit = formIntervencion.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.innerHTML = "Subiendo...";
            btnSubmit.disabled = true;
        }

        // Captura de campos
        const tipoElement = document.getElementById('tipo');
        const detalleElement = document.getElementById('detalle');
        const costoElement = document.getElementById('costo');

        const tipo = tipoElement ? tipoElement.value : 'Correctivo';
        const detalle = detalleElement ? detalleElement.value : '';
        const costo = costoElement ? (costoElement.value || 0) : 0;

        // Armamos el paquete FormData
        const formData = new FormData();
        formData.append('patente', patenteSeleccionada);
        formData.append('tipo', tipo);
        formData.append('detalle', detalle);
        formData.append('costo', costo);

        // Comprimimos y adjuntamos las evidencias (hasta 2MB c/u)
        for (let i = 0; i < archivosSeleccionados.length; i++) {
            const archivoComprimido = await comprimirImagen(archivosSeleccionados[i], 2);
            formData.append('evidencias[]', archivoComprimido);
        }

        try {
            await fetch("VehiculoController.php?accion=enviarATaller", {
                method: 'POST',
                body: formData // Aca viaja el texto y los archivos juntos
            });

            // Limpieza y reseteo
            formIntervencion.reset();
            archivosSeleccionados = [];
            actualizarPreview();
            if (modal) modal.close();
            cargarTablero();

        } catch (err) {
            console.error("Error al registrar intervencion:", err);
            alert("Hubo un error al registrar la intervencion.");
        } finally {
            // Restaurar boton
            if (btnSubmit) {
                btnSubmit.innerHTML = "Registrar";
                btnSubmit.disabled = false;
            }
        }
    });
}

// Eventos para cerrar modal
const btnCerrar = document.getElementById('btn-cerrar-modal');
const btnCancelar = document.getElementById('btn-cancelar');
if (btnCerrar) btnCerrar.addEventListener('click', () => modal.close());
if (btnCancelar) btnCancelar.addEventListener('click', () => modal.close());

// Iniciar al cargar la pagina
document.addEventListener("DOMContentLoaded", cargarTablero);