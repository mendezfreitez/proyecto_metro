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
        $id_lineas = (int) ($_POST['id_lineas'] ?? 0);

        if ($nombre === '' || $id_lineas <= 0) {
            $error = 'Debes escribir el nombre y seleccionar una línea.';
        } else {
            try {
                $consulta = $conexion->prepare('INSERT INTO estaciones (nombre, id_lineas) VALUES (?, ?)');
                $consulta->execute([$nombre, $id_lineas]);
                $_SESSION['mensaje'] = 'Estación agregada correctamente.';
                header('Location: estaciones.php');
                exit;
            } catch (PDOException $e) {
                $error = 'No se pudo guardar la estación. Asegúrate de que la línea exista.';
            }
        }
    } elseif ($accion === 'editar') {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $id_lineas = (int) ($_POST['id_lineas'] ?? 0);

        if ($id <= 0 || $nombre === '' || $id_lineas <= 0) {
            $error = 'Debes escribir el nombre y seleccionar una línea.';
        } else {
            try {
                $consulta = $conexion->prepare('UPDATE estaciones SET nombre = ?, id_lineas = ? WHERE id = ?');
                $consulta->execute([$nombre, $id_lineas, $id]);
                $_SESSION['mensaje'] = 'Estación actualizada correctamente.';
                header('Location: estaciones.php');
                exit;
            } catch (PDOException $e) {
                $error = 'No se pudo actualizar la estación. Asegúrate de que la línea exista.';
            }
        }
    } elseif ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $consulta = $conexion->prepare('DELETE FROM estaciones WHERE id = ?');
            $consulta->execute([$id]);
            $_SESSION['mensaje'] = 'Estación eliminada correctamente.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'No se pudo eliminar la estación porque tiene cocheras o accesos asociados.';
        }

        header('Location: estaciones.php');
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

// ===== Datos para la tabla (con el nombre de la línea) =====
$estaciones = $conexion->query(
    'SELECT e.id, e.nombre, e.id_lineas, l.nombre AS nombre_linea
     FROM estaciones e
     INNER JOIN lineas l ON l.id = e.id_lineas
     ORDER BY e.id'
)->fetchAll();

// ===== Líneas disponibles para el select del formulario =====
$lineas = $conexion->query('SELECT id, nombre FROM lineas ORDER BY nombre')->fetchAll();

// ===== Registro que se va a editar (?editar=ID) =====
$edicion = null;
if (isset($_GET['editar'])) {
    $consulta = $conexion->prepare('SELECT id, nombre, id_lineas FROM estaciones WHERE id = ?');
    $consulta->execute([(int) $_GET['editar']]);
    $edicion = $consulta->fetch() ?: null;
}

$titulo_pagina = 'Estaciones — Metro de Trenes';
require_once 'plantillas/cabecera.php';
?>

    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-700">Estaciones</h1>
            <p class="text-sm text-slate-500">Total: <?= count($estaciones) ?> estación(es) registrada(s)</p>
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
            <?= $edicion ? 'Editar estación' : 'Agregar estación' ?>
        </h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="accion" value="<?= $edicion ? 'editar' : 'crear' ?>">
            <?php if ($edicion): ?>
                <input type="hidden" name="id" value="<?= $edicion['id'] ?>">
            <?php endif; ?>

            <div>
                <label for="nombre" class="block text-sm font-medium text-slate-600 mb-1">Nombre</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?= htmlspecialchars($edicion['nombre'] ?? '') ?>"
                       placeholder="Ej. Estación Central"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="id_lineas" class="block text-sm font-medium text-slate-600 mb-1">Línea</label>
                <select id="id_lineas" name="id_lineas" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecciona una línea --</option>
                    <?php foreach ($lineas as $linea): ?>
                        <option value="<?= $linea['id'] ?>" <?= ((int) ($edicion['id_lineas'] ?? 0) === (int) $linea['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($linea['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($lineas)): ?>
                    <p class="text-xs text-red-600 mt-1">No hay líneas registradas. Primero agrega una en el módulo de Líneas.</p>
                <?php endif; ?>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    <?= $edicion ? 'Guardar cambios' : 'Agregar' ?>
                </button>
                <?php if ($edicion): ?>
                    <a href="estaciones.php"
                       class="bg-slate-300 hover:bg-slate-400 text-slate-700 px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabla de estaciones -->
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="tablaEstaciones" class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Línea</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($estaciones)): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                No hay estaciones registradas. Usa el formulario de arriba para agregar la primera.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($estaciones as $estacion): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-500"><?= $estacion['id'] ?></td>
                                <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($estacion['nombre']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                        <?= htmlspecialchars($estacion['nombre_linea']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="estaciones.php?editar=<?= $estacion['id'] ?>"
                                       class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition">
                                        Editar
                                    </a>
                                    <form method="post" class="inline"
                                          onsubmit="return confirmarEliminar(<?= htmlspecialchars(json_encode('¿Seguro que deseas eliminar la estación "' . $estacion['nombre'] . '"?', JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $estacion['id'] ?>">
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
