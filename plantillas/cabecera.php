<?php
/**
 * Encabezado común de todas las vistas.
 * Incluye Tailwind CSS (CDN) y la barra de navegación con los 5 módulos.
 * Antes de incluir este archivo se debe definir $titulo_pagina.
 */

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina ?? 'Metro de Trenes') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col text-slate-800">

    <!-- Barra superior -->
    <header class="bg-blue-800 shadow">
        <nav class="bg-blue-900">
            <div class="max-w-6xl mx-auto px-4 py-2 flex flex-wrap gap-2">
                <?php
                $enlaces = [
                    'index.php'            => 'Inicio',
                    'estaciones.php'       => 'Estaciones',
                    'lineas.php'           => 'Líneas',
                    'trenes.php'           => 'Trenes',
                    'cocheras.php'         => 'Cocheras',
                    'accesos_estacion.php' => 'Accesos estación',
                ];
                foreach ($enlaces as $archivo => $texto):
                    $activo = ($pagina_actual === $archivo);
                ?>
                    <a href="<?= $archivo ?>"
                       class="px-3 py-1.5 rounded-md text-sm font-medium <?= $activo ? 'bg-blue-600 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' ?>">
                        <?= $texto ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8 w-full flex-1">
