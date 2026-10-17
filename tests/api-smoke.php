<?php
declare(strict_types=1);
$base = rtrim(getenv('TEST_API_URL') ?: 'http://backend/api/productos', '/');
function callApi(string $method, string $path = '', ?array $data = null, ?string $raw = null, string $type = 'application/json'): array {
    global $base;
    $options = ['method'=>$method,'header'=>"Content-Type: $type\r\n",'ignore_errors'=>true,'timeout'=>8];
    if ($data !== null || $raw !== null) $options['content'] = $raw ?? json_encode($data, JSON_THROW_ON_ERROR);
    $body = file_get_contents($base.$path,false,stream_context_create(['http'=>$options]));
    if ($body === false) throw new RuntimeException('No se pudo contactar la API.');
    preg_match('#\s(\d{3})\s#',$http_response_header[0],$match);
    return [(int)$match[1],json_decode($body,true,32,JSON_THROW_ON_ERROR)];
}
function verify(bool $ok, string $name): void { if (!$ok) throw new RuntimeException('FALLO: '.$name); echo "OK: $name\n"; }
$payload=['nombre'=>'Prueba API '.date('His'),'proveedor'=>'Proveedor de pruebas','fecha_vencimiento'=>'2027-12-31','precio'=>'15.50','stock'=>20];
[$status,$created]=callApi('POST','',$payload);
verify($status===201,'crear devuelve 201'); $id=(int)$created['data']['id'];
[$status,$found]=callApi('GET','/'.$id); verify($status===200 && (int)$found['data']['stock']===20,'consultar por id');
$payload['stock']=30; $payload['precio']='16.00'; $payload['nombre']='Prueba API editada';
[$status,$edited]=callApi('PUT','/'.$id,$payload); verify($status===200 && (int)$edited['data']['stock']===30,'editar datos');
[$status,$same]=callApi('PUT','/'.$id,$payload); verify($status===200,'editar sin cambios');
[$status]=callApi('POST','',[...$payload,'stock'=>-1]); verify($status===422,'rechazar stock negativo');
[$status]=callApi('POST','',null,'{invalido'); verify($status===400,'rechazar JSON inválido');
[$status]=callApi('POST','',null,'{}','text/plain'); verify($status===415,'rechazar tipo de contenido incorrecto');
[$status]=callApi('PATCH','/'.$id); verify($status===405,'rechazar método no soportado');
[$status]=callApi('DELETE','/'.$id); verify($status===200,'eliminar lógicamente');
[$status,$list]=callApi('GET'); verify($status===200 && !in_array($id,array_map('intval',array_column($list['data'],'id')),true),'excluir eliminado del listado');
[$status]=callApi('GET','/'.$id); verify($status===404,'excluir eliminado de consulta individual');
[$status]=callApi('PUT','/'.$id,$payload); verify($status===404,'no editar eliminado');
[$status]=callApi('DELETE','/'.$id); verify($status===404,'segunda eliminación devuelve 404');
echo "ID conservado para comprobación SQL: $id\n";
