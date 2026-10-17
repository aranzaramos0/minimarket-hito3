<?php
declare(strict_types=1);

require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/ProductValidator.php';
require __DIR__ . '/../src/ProductRepository.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function readProduct(): array
{
    $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($contentType !== 'application/json') {
        respond(415, ['message' => 'Envíe el contenido como application/json.']);
    }
    $raw = file_get_contents('php://input', false, null, 0, 16385);
    if (strlen($raw) > 16384) respond(413, ['message' => 'La solicitud es demasiado grande.']);
    try {
        $object = json_decode($raw, false, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        respond(400, ['message' => 'El cuerpo de la solicitud no es un JSON válido.']);
    }
    if (!$object instanceof stdClass) respond(400, ['message' => 'Se esperaba un objeto JSON.']);
    $validated = ProductValidator::validate((array) $object);
    if ($validated['errors']) respond(422, ['message' => 'Revise los campos ingresados.', 'errors' => $validated['errors']]);
    return $validated['data'];
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/') ?: '/';
    if ($path === '/health' && $method === 'GET') {
        Database::connect()->query('SELECT id FROM Producto LIMIT 0');
        respond(200, ['status' => 'ok', 'service' => 'minimarket-api', 'database' => 'connected']);
    }
    if (!preg_match('#^/api/productos(?:/([1-9][0-9]*))?$#D', $path, $match)) {
        respond(404, ['message' => 'Ruta no encontrada.']);
    }
    $id = isset($match[1]) && strlen($match[1]) <= 10 && (float) $match[1] <= 2147483647 ? (int) $match[1] : null;
    if (isset($match[1]) && $id === null) respond(404, ['message' => 'Producto no encontrado.']);
    $allowed = $id === null ? ['GET', 'POST'] : ['GET', 'PUT', 'DELETE'];
    if (!in_array($method, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        respond(405, ['message' => 'Método HTTP no permitido para esta ruta.']);
    }
    $data = in_array($method, ['POST', 'PUT'], true) ? readProduct() : null;
    $repository = new ProductRepository(Database::connect());
    if ($method === 'GET' && $id === null) {
        $products = $repository->allActive();
        respond(200, ['data' => $products, 'total' => count($products)]);
    }
    if ($method === 'GET') {
        $product = $repository->findActive($id);
        if (!$product) respond(404, ['message' => 'Producto no encontrado o eliminado.']);
        respond(200, ['data' => $product]);
    }
    if ($method === 'POST') {
        $product = $repository->create($data);
        header('Location: /api/productos/' . $product['id']);
        respond(201, ['message' => 'Producto registrado correctamente.', 'data' => $product]);
    }
    if ($method === 'PUT') {
        $product = $repository->update($id, $data);
        if (!$product) respond(404, ['message' => 'Producto no encontrado o eliminado.']);
        respond(200, ['message' => 'Producto actualizado correctamente.', 'data' => $product]);
    }
    if (!$repository->softDelete($id)) respond(404, ['message' => 'Producto no encontrado o eliminado.']);
    respond(200, ['message' => 'Producto eliminado lógicamente.', 'id' => $id]);
} catch (PDOException $exception) {
    error_log('Persistencia: ' . $exception->getMessage());
    respond(503, ['message' => 'No se pudo completar la operación en la base de datos. Verifique el servicio MySQL.']);
} catch (Throwable $exception) {
    error_log('API: ' . $exception->getMessage());
    respond(500, ['message' => 'Error interno al procesar la solicitud.']);
}
