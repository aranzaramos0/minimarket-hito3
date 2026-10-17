<?php
declare(strict_types=1);

final class ProductValidator
{
    public static function validate(array $input): array
    {
        $errors = [];
        $data = [];
        foreach (['nombre', 'proveedor'] as $field) {
            $value = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
            $length = preg_match_all('/./us', $value);
            if ($value === '' || $length === false || $length > 100) {
                $errors[$field] = 'Debe contener entre 1 y 100 caracteres.';
            }
            $data[$field] = $value;
        }

        $date = is_string($input['fecha_vencimiento'] ?? null) ? $input['fecha_vencimiento'] : '';
        $dateFormatOk = preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $date) === 1;
        $parsed = $dateFormatOk ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            $errors['fecha_vencimiento'] = 'Ingrese una fecha real con formato AAAA-MM-DD (1000 a 9999).';
        }
        $data['fecha_vencimiento'] = $date;

        $rawPrice = $input['precio'] ?? null;
        $price = (is_string($rawPrice) || is_int($rawPrice) || is_float($rawPrice)) ? (string) $rawPrice : '';
        if (!preg_match('/^(0|[1-9][0-9]{0,7})(\.[0-9]{1,2})?$/D', $price)) {
            $errors['precio'] = 'Ingrese un precio entre 0 y 99999999.99, con hasta 2 decimales.';
        }
        $parts = explode('.', $price, 2);
        $data['precio'] = $parts[0] . '.' . str_pad($parts[1] ?? '', 2, '0');

        $rawStock = $input['stock'] ?? null;
        $stock = (is_string($rawStock) || is_int($rawStock)) ? (string) $rawStock : '';
        if (!preg_match('/^(0|[1-9][0-9]{0,9})$/D', $stock) || (float) $stock > 2147483647) {
            $errors['stock'] = 'Ingrese un entero entre 0 y 2147483647.';
        }
        $data['stock'] = (int) $stock;
        return ['data' => $data, 'errors' => $errors];
    }
}
