<?php
// Vista de TRENES: permite crear, editar y eliminar trenes del metro.
// Cada tren tiene un código, pertenece a una línea y se guarda en una cochera.

require_once 'config/conexion.php';
session_start();

$mensaje = '';
$error = '';

// ===== Procesamiento del formulario (crear, editar o eliminar) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $codigo = trim($_POST['codigo'] ?? '');
        $id_linea = (int) ($_POST['id_linea'] ?? 0);
        $id_cochera = (int) ($_POST['id_cochera'] ?? 0);

        if ($codigo === '' || $id_linea <= 0 || $id_cochera <= 0) {
            $error = 'Debes escribir el código, seleccionar una línea y una cochera.';
        } else {
            try {
                $conexion->query("INSERT INTO trenes (codigo, id_linea, id_cochera) VALUES ('$codigo', $id_linea, $id_cochera)");
                $_SESSION['mensaje'] = 'Tren agregado correctamente.';
                header('Location: trenes.php');
                exit;
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    $error = 'Ya existe un tren con el código "' . $codigo . '".';
                } else {
                    $error = 'No se pudo guardar el tren. Asegúrate de que la línea y la cochera existan.';
                }
            }
        }
    } elseif ($accion === 'editar') {
        $id = (int) ($_POST['id'] ?? 0);
        $codigo = trim($_POST['codigo'] ?? '');
        $id_linea = (int) ($_POST['id_linea'] ?? 0);
        $id_cochera = (int) ($_POST['id_cochera'] ?? 0);

        if ($id <= 0 || $codigo === '' || $id_linea <= 0 || $id_cochera <= 0) {
            $error = 'Debes escribir el código, seleccionar una línea y una cochera.';
        } else {
            try {
                $conexion->query("UPDATE trenes SET codigo = '$codigo', id_linea = $id_linea, id_cochera = $id_cochera WHERE id = $id");
                $_SESSION['mensaje'] = 'Tren actualizado correctamente.';
                header('Location: trenes.php');
                exit;
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    $error = 'Ya existe un tren con el código "' . $codigo . '".';
                } else {
                    $error = 'No se pudo actualizar el tren. Asegúrate de que la línea y la cochera existan.';
                }
            }
        }
    } elseif ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $conexion->query("DELETE FROM trenes WHERE id = $id");
            $_SESSION['mensaje'] = 'Tren eliminado correctamente.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'No se pudo eliminar el tren. Inténtalo de nuevo.';
        }

        header('Location: trenes.php');
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

// ===== Datos para la tabla (con línea y cochera) =====
$trenes = $conexion->query(
    'SELECT t.id, t.codigo, t.id_linea, t.id_cochera,
            l.nombre AS nombre_linea,
            e.nombre AS nombre_estacion
     FROM trenes t
     INNER JOIN lineas l ON l.id = t.id_linea
     LEFT JOIN cocheras c ON c.id = t.id_cochera
     LEFT JOIN estaciones e ON e.id = c.id_estacion
     ORDER BY t.id'
)->fetchAll();

// ===== Líneas y cocheras disponibles para los select del formulario =====
$lineas = $conexion->query('SELECT id, nombre FROM lineas ORDER BY nombre')->fetchAll();

$cocheras = $conexion->query(
    'SELECT c.id, e.nombre AS nombre_estacion
     FROM cocheras c
     INNER JOIN estaciones e ON e.id = c.id_estacion
     ORDER BY c.id'
)->fetchAll();

// ===== Registro que se va a editar (?editar=ID) =====
$edicion = null;
if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
    $resultado = $conexion->query("SELECT id, codigo, id_linea, id_cochera FROM trenes WHERE id = $idEditar");
    $edicion = $resultado->fetch() ?: null;
}

$titulo_pagina = 'Trenes — Metro de Trenes';
require_once 'plantillas/cabecera.php';
?>

    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-700">Trenes</h1>
            <p class="text-sm text-slate-500">Total: <?= count($trenes) ?> tren(es) registrado(s)</p>
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
            <?= $edicion ? 'Editar tren' : 'Agregar tren' ?>
        </h2>

        <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="accion" value="<?= $edicion ? 'editar' : 'crear' ?>">
            <?php if ($edicion): ?>
                <input type="hidden" name="id" value="<?= $edicion['id'] ?>">
            <?php endif; ?>

            <div>
                <label for="codigo" class="block text-sm font-medium text-slate-600 mb-1">Código</label>
                <input type="text" id="codigo" name="codigo" required
                       value="<?= htmlspecialchars($edicion['codigo'] ?? '') ?>"
                       placeholder="Ej. T-001"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="id_linea" class="block text-sm font-medium text-slate-600 mb-1">Línea</label>
                <select id="id_linea" name="id_linea" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecciona una línea --</option>
                    <?php foreach ($lineas as $linea): ?>
                        <option value="<?= $linea['id'] ?>" <?= ((int) ($edicion['id_linea'] ?? 0) === (int) $linea['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($linea['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="id_cochera" class="block text-sm font-medium text-slate-600 mb-1">Cochera</label>
                <select id="id_cochera" name="id_cochera" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecciona una cochera --</option>
                    <?php foreach ($cocheras as $cochera): ?>
                        <option value="<?= $cochera['id'] ?>" <?= ((int) ($edicion['id_cochera'] ?? 0) === (int) $cochera['id']) ? 'selected' : '' ?>>
                            Cochera #<?= $cochera['id'] ?> — <?= htmlspecialchars($cochera['nombre_estacion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($cocheras)): ?>
                    <p class="text-xs text-red-600 mt-1">No hay cocheras registradas. Primero agrega una en el módulo de Cocheras.</p>
                <?php endif; ?>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    <?= $edicion ? 'Guardar cambios' : 'Agregar' ?>
                </button>
                <?php if ($edicion): ?>
                    <a href="trenes.php"
                       class="bg-slate-300 hover:bg-slate-400 text-slate-700 px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
        <?php if (empty($lineas)): ?>
            <p class="text-xs text-red-600 mt-3">No hay líneas registradas. Primero agrega una en el módulo de Líneas.</p>
        <?php endif; ?>
    </div>

    <!-- Tabla de trenes -->
    <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="tablaTrenes" class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Línea</th>
                        <th class="px-4 py-3">Cochera</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($trenes)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                No hay trenes registrados. Usa el formulario de arriba para agregar el primero.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($trenes as $tren): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-500"><?= $tren['id'] ?></td>
                                <td class="px-4 py-3 font-medium text-slate-700"><?= htmlspecialchars($tren['codigo']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                        <?= htmlspecialchars($tren['nombre_linea']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    Cochera #<?= $tren['id_cochera'] ?>
                                    <?php if ($tren['nombre_estacion'] !== null): ?>
                                        <span class="text-slate-400">(<?= htmlspecialchars($tren['nombre_estacion']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="trenes.php?editar=<?= $tren['id'] ?>"
                                       class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold transition">
                                        Editar
                                    </a>
                                    <form method="post" class="inline"
                                          onsubmit="return confirmarEliminar(<?= htmlspecialchars(json_encode('¿Seguro que deseas eliminar el tren "' . $tren['codigo'] . '"?', JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $tren['id'] ?>">
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
