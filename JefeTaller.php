<?php
require_once 'config/Seguridad.php';
Seguridad::protegerVista(['JefeTaller']); // Solo JefeTaller por ahora (MVP)
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Mantenimientos - Celo Fleet</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style>
        :root {
            --background: #ffffff;
            --foreground: #111424;
        }
    </style>
</head>

<body class="bg-[#f9fafb] text-[--foreground] p-6">

    <main class="max-w-[1300px] mx-auto">
        <header class="flex justify-between items-center mb-8 bg-[#0f5c2e] p-6 rounded-xl shadow-md border border-transparent">
            <div class="flex items-center gap-4">
                <img src="assets/img/logo.png" alt="CELO" class="h-12">
                <h1 class="text-2xl font-bold text-white">Gestion de Mantenimientos</h1>
            </div>

            <div class="flex items-center gap-4">
                <span id="nombre-usuario-display" class="text-sm font-medium text-white"></span>
                <button id="btn-logout"
                    class="px-4 py-2 bg-white text-red-600 rounded-lg text-sm font-bold hover:bg-gray-100 transition-colors shadow-sm">Cerrar
                    Sesión</button>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
            <article class="bg-[#f3f4f6] rounded-2xl p-5 border border-black/5">
                <header class="flex justify-between items-center mb-5">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span> Operativos
                    </h2>
                    <span id="contador-operativos"
                        class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full text-xs font-bold">0</span>
                </header>
                <div id="columna-operativos" class="flex flex-col gap-4 min-h-[150px]"></div>
            </article>

            <article class="bg-[#fefde8] rounded-2xl p-5 border border-yellow-100">
                <header class="flex justify-between items-center mb-5">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#eab308]"></span> Alertas / En Revision
                    </h2>
                    <span id="contador-alertas"
                        class="bg-[#fef08a] text-[#854d0e] px-2 py-0.5 rounded-full text-xs font-bold">0</span>
                </header>
                <div id="columna-alertas" class="flex flex-col gap-4 min-h-[150px]"></div>
            </article>

            <article class="bg-[#fff5f5] rounded-2xl p-5 border border-red-100">
                <header class="flex justify-between items-center mb-5">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span> En Taller
                    </h2>
                    <span id="contador-taller"
                        class="bg-[#fee2e2] text-[#991b1b] px-2 py-0.5 rounded-full text-xs font-bold">0</span>
                </header>
                <div id="columna-taller" class="flex flex-col gap-4 min-h-[150px]"></div>
            </article>
        </section>
    </main>

    <dialog id="modal-intervencion"
        class="bg-white rounded-2xl shadow-2xl w-[90%] max-w-[600px] backdrop:bg-black/40 p-6 m-auto border border-black/10">
        <header class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Registro de Intervencion</h2>
                <p id="modal-unidad-titulo" class="text-xs text-gray-500 font-medium mt-1">Unidad: Cargando...</p>
            </div>
            <button id="btn-cerrar-modal"
                class="text-gray-400 hover:text-gray-600 text-2xl font-normal leading-none cursor-pointer">&times;</button>
        </header>
        <form id="form-intervencion" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 flex flex-col gap-1.5">
                    <label class="text-sm font-bold text-gray-700">Tipo de Mantenimiento</label>
                    <select id="tipo"
                        class="w-full p-2.5 border border-black/10 rounded-xl text-sm bg-white outline-none">
                        <option value="Preventivo">Preventivo</option>
                        <option value="Correctivo">Correctivo</option>
                        <option value="Siniestro">Siniestro</option>
                    </select>
                </div>
                <div class="flex-1 flex flex-col gap-1.5">
                    <label class="text-sm font-bold text-gray-700">Costo Estimado ($)</label>
                    <input type="number" id="costo" step="0.01" min="0" placeholder="0.00" required
                        class="w-full p-2.5 border border-black/10 rounded-xl text-sm bg-white outline-none">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-700">Detalle</label>
                <textarea id="detalle" rows="4" required
                    class="w-full p-2.5 border border-black/10 rounded-xl text-sm bg-white"></textarea>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-gray-700">Evidencias (Tickets / Fotos)</label>
                <div id="drop-zone"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 hover:bg-gray-100 transition-colors">
                    <p class="font-bold text-sm text-gray-800 mb-0.5">Arrastra tus archivos aquí</p>
                    <p class="text-xs text-gray-400 mb-3">o haz clic para examinar (Autocompresión a 2MB)</p>
                    <label
                        class="inline-block bg-white border border-gray-300 text-gray-700 px-4 py-1.5 rounded-lg text-xs font-bold cursor-pointer hover:bg-gray-50 transition-colors shadow-sm">
                        Seleccionar archivos
                        <input type="file" id="archivos-upload" class="hidden" multiple
                            accept="image/*,application/pdf" />
                    </label>
                    <div id="preview-archivos"
                        class="flex flex-col gap-2 mt-3 text-xs text-gray-600 font-medium text-left"></div>
                </div>
            </div>

            <footer class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button id="btn-cancelar" type="button"
                    class="px-5 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancelar</button>
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700">Registrar</button>
            </footer>
        </form>
    </dialog>

    <script src="assets/js/JefeTaller.js?v=3"></script>
</body>

</html>