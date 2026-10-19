<?php
require_once 'config/conexion.php';
session_start();

$mensaje = '';
$error = '';

// ===== Procesamiento del formulario (crear, editar o eliminar) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            $error = 'Debes escribir el nombre de la línea.';
        } else {
            try {
                $conexion->query("INSERT INTO lineas (nombre) VALUES ('$nombre')");
                $_SESSION['mensaje'] = 'Línea agregada correctamente.';
                header('Location: lineas.php');
                exit;
            } catch (PDOException $e) {
                $error = 'No se pudo guardar la línea. Inténtalo de nuevo.';
            }
        }
    } elseif ($accion === 'editar') {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if ($id <= 0 || $nombre === '') {
            $error = 'Debes escribir el nombre de la línea.';
        } else {
            try {
                $conexion->query("UPDATE lineas SET nombre = '$nombre' WHERE id = $id");
                $_SESSION['mensaje'] = 'Línea actualizada correctamente.';
                header('Location: lineas.php');
                exit;
            } catch (PDOException $e) {
                $error = 'No se pudo actualizar la línea. Inténtalo de nuevo.';
            }
        }
    } elseif ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $conexion->query("DELETE FROM lineas WHERE id = $id");
            $_SESSION['mensaje'] = 'Línea eliminada correctamente.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'No se pudo eliminar la línea porque tiene estaciones o trenes asociados.';
        }

        header('Location: lineas.php');
        exit;
    }
}

// ===== Mensajes guardados antes de redireccionar =====
if ($mensaje === '' && !empty($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
}
if ($error === '' && !empty($_SESSION['error'])) {
    $error = $_SESSION['error'];
}
unset($_SESSION['mensaje'], $_SESSION['error']);

// ===== Datos para la tabla =====
$lineas = $conexion->query('SELECT id, nombre FROM lineas ORDER BY id')->fetchAll();

// ===== Registro que se va a editar (?editar=ID) =====
$edicion = null;
if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
    $resultado = $conexion->query("SELECT id, nombre FROM lineas WHERE id = $idEditar");
    $edicion = $resultado->fetch() ?: null;
}

$titulo_pagina = 'Líneas — Metro de Trenes';
require_once 'plantillas/cabecera.php';
?>

    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-700">Líneas</h1>
            <p class="text-sm text-slate-500">Total: <?= count($lineas) ?> línea(s) registrada(s)</p>
        </div>
        <a href="#formulario" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow transition">
            + Agregar
        </a>
    </div>

    <!-- Mensajes -->
    <?php if ($mensaje !== ''): ?>
        <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-4 text-sm font-medium">
            ✅ <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-4 text-sm font-medium">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para crear / editar -->
    <div id="formulario" class="bg-white rounded-xl shadow border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-700 mb-4">
            <?= $edicion ? 'Editar línea' : 'Agregar línea' ?>
        </h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="accion" value="<?= $edicion ? 'editar' : 'crear' ?>">
            <?php if ($edicion): ?>
                <input type="hidden" name="id" value="<?= $edicion['id'] ?>">
            <?php endif; ?>

            <div class="md:col-span-2">
                <label for="nombre" class="block text-sm font-medium text-slate-600 mb-1">Nombre</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?= htmlspecialchars($edicion['nombre'] ?? '') ?>"
                       placeholder="Ej. Línea Roja"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    <?= $edicion ? 'Guardar cambios' : 'Agregar' ?>
                </button>
                <?php if ($edicion): ?>
                    <a href="lineas.php"
                       class="bg-slate-300 hover:bg-slate-400 text-slate-700 px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabla de líneas -->
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="tablaLineas" class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($lineas)): ?>
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-400">
                                No hay líneas registradas. Usa el formulario de arriba para agregar la primera.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lineas as $linea): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-500"><?= $linea['id'] ?></td>
                                <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($linea['nombre']) ?></td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="lineas.php?editar=<?= $linea['id'] ?>"
                                       class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition">
                                        Editar
                                    </a>
                                    <form method="post" class="inline"
                                          onsubmit="return confirmarEliminar(<?= htmlspecialchars(json_encode('¿Seguro que deseas eliminar la línea "' . $linea['nombre'] . '"?', JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $linea['id'] ?>">
                                        <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition ml-1">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="js/comun.js"></script>
