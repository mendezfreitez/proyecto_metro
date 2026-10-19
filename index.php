<?php
require_once 'config/conexion.php';

$totales = [
    'estaciones'       => $conexion->query('SELECT COUNT(*) FROM estaciones')->fetchColumn(),
    'lineas'           => $conexion->query('SELECT COUNT(*) FROM lineas')->fetchColumn(),
    'trenes'           => $conexion->query('SELECT COUNT(*) FROM trenes')->fetchColumn(),
    'cocheras'         => $conexion->query('SELECT COUNT(*) FROM cocheras')->fetchColumn(),
    'accesos_estacion' => $conexion->query('SELECT COUNT(*) FROM accesos_estacion')->fetchColumn(),
];

$titulo_pagina = 'Inicio — Metro de Trenes';
require_once 'plantillas/cabecera.php';
?>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-700">Panel de control</h1>
        <p class="text-slate-500 mt-1">Selecciona un módulo para gestionar sus registros (crear, editar o eliminar).</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <!-- Estaciones -->
        <a href="estaciones.php" class="bg-white rounded-xl shadow border border-slate-200 p-6 hover:shadow-lg hover:border-blue-400 transition">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-700">🚉 Estaciones</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $totales['estaciones'] ?></span>
            </div>
            <p class="text-sm text-slate-500 mt-2">Alta, baja y edición de estaciones del metro.</p>
            <span class="text-sm font-semibold text-blue-600 inline-block mt-4">Gestionar →</span>
        </a>

        <!-- Líneas -->
        <a href="lineas.php" class="bg-white rounded-xl shadow border border-slate-200 p-6 hover:shadow-lg hover:border-blue-400 transition">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-700">🛤️ Líneas</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $totales['lineas'] ?></span>
            </div>
            <p class="text-sm text-slate-500 mt-2">Líneas del sistema y sus nombres.</p>
            <span class="text-sm font-semibold text-blue-600 inline-block mt-4">Gestionar →</span>
        </a>

        <!-- Trenes -->
        <a href="trenes.php" class="bg-white rounded-xl shadow border border-slate-200 p-6 hover:shadow-lg hover:border-blue-400 transition">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-700">🚃 Trenes</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $totales['trenes'] ?></span>
            </div>
            <p class="text-sm text-slate-500 mt-2">Trenes, su código y la línea a la que pertenecen.</p>
            <span class="text-sm font-semibold text-blue-600 inline-block mt-4">Gestionar →</span>
        </a>

        <!-- Cocheras -->
        <a href="cocheras.php" class="bg-white rounded-xl shadow border border-slate-200 p-6 hover:shadow-lg hover:border-blue-400 transition">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-700">🏢 Cocheras</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $totales['cocheras'] ?></span>
            </div>
            <p class="text-sm text-slate-500 mt-2">Cocheras y la estación donde se encuentran.</p>
            <span class="text-sm font-semibold text-blue-600 inline-block mt-4">Gestionar →</span>
        </a>

        <!-- Accesos estación -->
        <a href="accesos_estacion.php" class="bg-white rounded-xl shadow border border-slate-200 p-6 hover:shadow-lg hover:border-blue-400 transition">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-700">🚪 Accesos estación</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $totales['accesos_estacion'] ?></span>
            </div>
            <p class="text-sm text-slate-500 mt-2">Accesos de cada estación y su nombre.</p>
            <span class="text-sm font-semibold text-blue-600 inline-block mt-4">Gestionar →</span>
        </a>

    </div>

    <script src="js/comun.js"></script>
