<?php
require_once 'config/conexion.php';
session_start();

$mensaje = '';
$error = '';

// ===== Procesamiento del formulario (crear, editar o eliminar) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $id_estacion = (int) ($_POST['id_estacion'] ?? 0);

        if ($id_estacion <= 0) {
            $error = 'Debes seleccionar una estación.';
        } else {
            try {
                $conexion->query("INSERT INTO cocheras (id_estacion) VALUES ($id_estacion)");
                $_SESSION['mensaje'] = 'Cochera agregada correctamente.';
                header('Location: cocheras.php');
                exit;
            } catch (PDOException $e) {
                $error = 'No se pudo guardar la cochera. Asegúrate de que la estación exista.';
            }
        }
    } elseif ($accion === 'editar') {
        $id = (int) ($_POST['id'] ?? 0);
        $id_estacion = (int) ($_POST['id_estacion'] ?? 0);

        if ($id <= 0 || $id_estacion <= 0) {
            $error = 'Debes seleccionar una estación.';
        } else {
            try {
                $conexion->query("UPDATE cocheras SET id_estacion = $id_estacion WHERE id = $id");
                $_SESSION['mensaje'] = 'Cochera actualizada correctamente.';
                header('Location: cocheras.php');
                exit;
            } catch (PDOException $e) {
                $error = 'No se pudo actualizar la cochera. Asegúrate de que la estación exista.';
            }
        }
    } elseif ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        // Primero verificamos que no haya trenes asignados a esta cochera
        // (la base de datos no tiene clave foránea en esa columna).
        $resultado = $conexion->query("SELECT COUNT(*) FROM trenes WHERE id_cochera = $id");
        $total = $resultado->fetchColumn();

        if ($total > 0) {
            $_SESSION['error'] = 'No se pudo eliminar la cochera porque tiene trenes asignados.';
        } else {
            try {
                $conexion->query("DELETE FROM cocheras WHERE id = $id");
                $_SESSION['mensaje'] = 'Cochera eliminada correctamente.';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'No se pudo eliminar la cochera. Inténtalo de nuevo.';
            }
        }

        header('Location: cocheras.php');
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

// ===== Datos para la tabla (con estación y línea) =====
$cocheras = $conexion->query(
    'SELECT c.id, c.id_estacion, e.nombre AS nombre_estacion, l.nombre AS nombre_linea
     FROM cocheras c
     INNER JOIN estaciones e ON e.id = c.id_estacion
     INNER JOIN lineas l ON l.id = e.id_lineas
     ORDER BY c.id'
)->fetchAll();

// ===== Estaciones disponibles para el select del formulario =====
$estaciones = $conexion->query(
    'SELECT e.id, e.nombre, l.nombre AS nombre_linea
     FROM estaciones e
     INNER JOIN lineas l ON l.id = e.id_lineas
     ORDER BY e.nombre'
)->fetchAll();

// ===== Registro que se va a editar (?editar=ID) =====
$edicion = null;
if (isset($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];
    $resultado = $conexion->query("SELECT id, id_estacion FROM cocheras WHERE id = $id_editar");
    $edicion = $resultado->fetch() ?: null;
}

$titulo_pagina = 'Cocheras — Metro de Trenes';
require_once 'plantillas/cabecera.php';
?>

    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-700">Cocheras</h1>
            <p class="text-sm text-slate-500">Total: <?= count($cocheras) ?> cochera(s) registrada(s)</p>
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
            <?= $edicion ? 'Editar cochera #' . $edicion['id'] : 'Agregar cochera' ?>
        </h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="accion" value="<?= $edicion ? 'editar' : 'crear' ?>">
            <?php if ($edicion): ?>
                <input type="hidden" name="id" value="<?= $edicion['id'] ?>">
            <?php endif; ?>

            <div class="md:col-span-2">
                <label for="id_estacion" class="block text-sm font-medium text-slate-600 mb-1">Estación</label>
                <select id="id_estacion" name="id_estacion" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecciona una estación --</option>
                    <?php foreach ($estaciones as $estacion): ?>
                        <option value="<?= $estacion['id'] ?>" <?= ((int) ($edicion['id_estacion'] ?? 0) === (int) $estacion['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($estacion['nombre']) ?> (Línea <?= htmlspecialchars($estacion['nombre_linea']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($estaciones)): ?>
                    <p class="text-xs text-red-600 mt-1">No hay estaciones registradas. Primero agrega una en el módulo de Estaciones.</p>
                <?php endif; ?>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    <?= $edicion ? 'Guardar cambios' : 'Agregar' ?>
                </button>
                <?php if ($edicion): ?>
                    <a href="cocheras.php"
                       class="bg-slate-300 hover:bg-slate-400 text-slate-700 px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabla de cocheras -->
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="tablaCocheras" class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Estación</th>
                        <th class="px-4 py-3">Línea</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($cocheras)): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                No hay cocheras registradas. Usa el formulario de arriba para agregar la primera.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cocheras as $cochera): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-700">Cochera #<?= $cochera['id'] ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($cochera['nombre_estacion']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                        <?= htmlspecialchars($cochera['nombre_linea']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="cocheras.php?editar=<?= $cochera['id'] ?>"
                                       class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition">
                                        Editar
                                    </a>
                                    <form method="post" class="inline"
                                          onsubmit="return confirmarEliminar(<?= htmlspecialchars(json_encode('¿Seguro que deseas eliminar la Cochera #' . $cochera['id'] . ' (estación ' . $cochera['nombre_estacion'] . ')?', JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $cochera['id'] ?>">
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
