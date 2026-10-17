<?php
declare(strict_types=1);
$validator = __DIR__ . '/../backend/src/ProductValidator.php';
require is_file($validator) ? $validator : __DIR__ . '/../src/ProductValidator.php';
$valid = ['nombre' => 'Café', 'proveedor' => 'Proveedor de prueba', 'fecha_vencimiento' => '2028-02-29', 'precio' => '0.00', 'stock' => '0'];
$cases = [
    ['valores cero y fecha bisiesta', $valid, []],
    ['campos vacíos', [], ['nombre','proveedor','fecha_vencimiento','precio','stock']],
    ['fecha imposible', [...$valid,'fecha_vencimiento'=>'2027-02-29'], ['fecha_vencimiento']],
    ['precio negativo', [...$valid,'precio'=>'-1.00'], ['precio']],
    ['stock negativo', [...$valid,'stock'=>-2], ['stock']],
    ['stock decimal', [...$valid,'stock'=>'1.5'], ['stock']],
    ['precio con tres decimales', [...$valid,'precio'=>'2.345'], ['precio']],
    ['precio fuera de DECIMAL(10,2)', [...$valid,'precio'=>'100000000.00'], ['precio']],
    ['stock fuera de INT', [...$valid,'stock'=>'2147483648'], ['stock']],
    ['nombre de 101 caracteres', [...$valid,'nombre'=>str_repeat('á',101)], ['nombre']],
    ['valores máximos permitidos', [...$valid,'precio'=>'99999999.99','stock'=>'2147483647'], []],
    ['tipo incorrecto', [...$valid,'precio'=>false,'stock'=>true], ['precio','stock']],
    ['fecha con byte nulo', [...$valid,'fecha_vencimiento'=>"2027-01-01\0"], ['fecha_vencimiento']]
];
foreach ($cases as [$name, $input, $expected]) {
    $actual = array_keys(ProductValidator::validate($input)['errors']);
    sort($actual); sort($expected);
    if ($actual !== $expected) { fwrite(STDERR, "FALLO: $name\n"); exit(1); }
    echo "OK: $name\n";
}
echo count($cases) . " pruebas de validación correctas.\n";
