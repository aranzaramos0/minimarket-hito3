<?php
declare(strict_types=1);
require __DIR__ . '/../src/ApiClient.php';
if (!session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true])) {
    http_response_code(503);
    exit('No se pudo iniciar la sesión. Revise el directorio session.save_path de PHP.');
}
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
header('Cache-Control: no-store');
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function scalarInput(array $input, string $key, string $default = ''): string { return is_scalar($input[$key] ?? null) ? (string) $input[$key] : $default; }
$api = new ApiClient();
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$input = $isPost ? $_POST : $_GET;
$action = scalarInput($input, 'action', 'list');
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) ?: null;
$errors = [];
$error = '';
$products = [];
$product = ['nombre' => '', 'proveedor' => '', 'fecha_vencimiento' => '', 'precio' => '', 'stock' => ''];
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
if (!in_array($action, ['list', 'create', 'edit', 'delete'], true)) $action = 'list';
if ($isPost) {
    if (!hash_equals($_SESSION['csrf'], scalarInput($_POST, 'csrf'))) {
        http_response_code(403);
        $error = 'La sesión del formulario venció. Recargue la página e intente nuevamente.';
        $action = 'list';
    } elseif (!in_array($action, ['create', 'edit', 'delete'], true) || ($action !== 'create' && !$id)) {
        http_response_code(400);
        $error = 'Seleccione un producto válido.';
        $action = 'list';
    } else {
        foreach (array_keys($product) as $field) $product[$field] = scalarInput($_POST, $field);
        $response = match ($action) {
            'create' => $api->request('POST', '', $product),
            'edit' => $api->request('PUT', '/' . $id, $product),
            'delete' => $api->request('DELETE', '/' . $id)
        };
        if ($response['status'] >= 200 && $response['status'] < 300) {
            $_SESSION['flash'] = $response['body']['message'] ?? 'Operación completada.';
            header('Location: /', true, 303);
            exit;
        }
        $error = $response['body']['message'] ?? 'No se pudo completar la operación.';
        $errors = $response['body']['errors'] ?? [];
        if ($action === 'delete') $action = 'list';
    }
}
if (!$isPost && in_array($action, ['edit', 'delete'], true)) {
    $response = $id ? $api->request('GET', '/' . $id) : ['status' => 404, 'body' => []];
    if ($response['status'] === 200) $product = $response['body']['data'];
    else {
        $error = $response['body']['message'] ?? 'Producto no encontrado.';
        $action = 'list';
    }
}
$apiOk = true;
if ($action === 'list') {
    $response = $api->request('GET');
    if ($response['status'] === 200) $products = $response['body']['data'];
    else { $error = $error ?: ($response['body']['message'] ?? 'No se pudo cargar el listado.'); $apiOk = false; }
}
$title = match ($action) { 'create' => 'Nuevo producto', 'edit' => 'Editar producto', 'delete' => 'Eliminar producto', default => 'Tus productos, en orden.' };
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($title) ?> | Minimarket</title><link rel="stylesheet" href="/styles.css"></head>
<body>
<header class="topbar"><a class="brand" href="/"><span class="brand-icon">M</span> minimarket<span class="brand-dot">.</span></a><span class="top-label">GESTIÓN DE PRODUCTOS</span></header>
<main>
<div class="eyebrow">INVENTARIO / <?= $action === 'list' ? 'PRODUCTOS ACTIVOS' : 'PRODUCTO' ?></div>
<section class="heading"><div><h1><?= e($title) ?></h1><p><?= $action === 'list' ? 'Consulta y administra el inventario de tu tienda.' : 'Mantén la información de tu inventario al día.' ?></p></div>
<?php if ($action === 'list'): ?><a class="button" href="/?action=create">+ Nuevo producto</a><?php else: ?><a class="back" href="/">Volver al listado</a><?php endif; ?></section>
<?php if ($flash): ?><div class="notice success" role="status"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($action === 'list'): ?>
<section class="stats" aria-label="Resumen de inventario"><div class="stat"><span>Productos activos</span><strong><?= count($products) ?></strong></div><div class="stat"><span>Unidades disponibles</span><strong><?= array_sum(array_column($products, 'stock')) ?></strong></div><div class="stat"><span>Productos con stock bajo</span><strong><?= count(array_filter($products, fn($p) => $p['stock'] <= 10)) ?></strong><small>10 unidades o menos</small></div></section>
<section class="panel"><div class="panel-heading"><h2>Listado de productos</h2><span class="badge neutral"><?= count($products) ?> registros</span></div><div class="table-scroll"><table><thead><tr><th>ID</th><th>Producto</th><th>Proveedor</th><th>Vencimiento</th><th class="number">Precio (Bs)</th><th class="number">Stock</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($products as $p): ?><tr><td class="muted">#<?= e($p['id']) ?></td><td class="product-name"><?= e($p['nombre']) ?></td><td><?= e($p['proveedor']) ?></td><td><?= e($p['fecha_vencimiento']) ?></td><td class="number price"><?= number_format((float) $p['precio'], 2, '.', ',') ?></td><td class="number"><span class="badge <?= $p['stock'] <= 10 ? 'low' : 'stock' ?>"><?= e($p['stock']) ?></span></td><td><div class="actions"><a href="/?action=edit&amp;id=<?= e($p['id']) ?>">Editar</a><a class="danger-link" href="/?action=delete&amp;id=<?= e($p['id']) ?>">Eliminar</a></div></td></tr><?php endforeach; ?>
<?php if (!$products): ?><tr><td colspan="7" class="empty"><?= $apiOk ? 'Todavía no hay productos activos. Registra el primero.' : 'El listado no está disponible. Intenta recargar la página.' ?></td></tr><?php endif; ?>
</tbody></table></div><div class="panel-note">Se muestran únicamente productos activos. Los registros eliminados se conservan en el historial de datos.</div></section>
<?php elseif ($action === 'delete'): ?>
<section class="panel confirmation"><span class="warning-mark">!</span><h2>¿Eliminar este producto?</h2><p><strong><?= e($product['nombre']) ?></strong> dejará de aparecer en el listado activo. Sus datos se conservarán.</p><form method="post" action="/"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($id) ?>"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><div class="form-actions"><a class="button secondary" href="/">Cancelar</a><button class="button danger" type="submit">Confirmar eliminación</button></div></form></section>
<?php else: ?>
<section class="panel form-panel"><div class="panel-heading"><h2>Información del producto</h2><span class="muted">Todos los campos son obligatorios</span></div><form method="post" action="/" class="product-form"><input type="hidden" name="action" value="<?= e($action) ?>"><input type="hidden" name="id" value="<?= e($id) ?>"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><div class="form-grid">
<?php foreach (['nombre' => ['Nombre del producto', 'text'], 'proveedor' => ['Proveedor', 'text'], 'fecha_vencimiento' => ['Fecha de vencimiento', 'date'], 'precio' => ['Precio unitario (Bs)', 'number'], 'stock' => ['Stock disponible', 'number']] as $field => [$label, $type]): ?>
<div class="field <?= $field === 'nombre' ? 'full' : '' ?>"><label for="<?= e($field) ?>"><?= e($label) ?></label><input id="<?= e($field) ?>" name="<?= e($field) ?>" type="<?= e($type) ?>" value="<?= e($product[$field]) ?>" required <?= $type === 'text' ? 'maxlength="100"' : '' ?> <?= $field === 'precio' ? 'min="0" max="99999999.99" step="0.01"' : '' ?> <?= $field === 'stock' ? 'min="0" max="2147483647" step="1"' : '' ?> <?= $field === 'fecha_vencimiento' ? 'min="1000-01-01" max="9999-12-31"' : '' ?> aria-invalid="<?= isset($errors[$field]) ? 'true' : 'false' ?>"><?php if (isset($errors[$field])): ?><span class="field-error"><?= e($errors[$field]) ?></span><?php endif; ?></div>
<?php endforeach; ?></div><div class="form-actions"><a class="button secondary" href="/">Cancelar</a><button class="button" type="submit"><?= $action === 'create' ? 'Guardar producto' : 'Guardar cambios' ?></button></div></form></section>
<?php endif; ?>
<footer>Minimarket <span>Control simple. Información clara.</span></footer>
</main></body></html>
