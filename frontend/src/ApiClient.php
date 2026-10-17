<?php
declare(strict_types=1);

final class ApiClient
{
    public function request(string $method, string $path = '', ?array $data = null): array
    {
        $base = rtrim(getenv('API_BASE_URL') ?: 'http://127.0.0.1:8081/api/productos', '/');
        $options = ['method' => $method, 'timeout' => 8, 'ignore_errors' => true,
                    'header' => "Accept: application/json\r\nContent-Type: application/json\r\n"];
        if ($data !== null) $options['content'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $context = stream_context_create(['http' => $options]);
        $result = @file_get_contents($base . $path, false, $context);
        if ($result === false) {
            return ['status' => 503, 'body' => ['message' => 'No se pudo conectar con el servicio de productos. Intente nuevamente.']];
        }
        $status = 502;
        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $match)) $status = (int) $match[1];
        }
        try {
            $body = json_decode($result, true, 32, JSON_THROW_ON_ERROR);
            if (!is_array($body)) throw new JsonException('Respuesta inválida');
        } catch (JsonException) {
            return ['status' => 502, 'body' => ['message' => 'El servicio devolvió una respuesta inválida.']];
        }
        return ['status' => $status, 'body' => $body];
    }
}
