# Verificación del proyecto

## Pruebas ejecutadas

Se ejecutó el mismo código entregado con **PHP 8.3.6** y **MySQL 8.0.46**, como procesos locales de prueba. Se utilizó MySQL real y la tabla `Producto`; no se sustituyó la base por datos simulados. El frontend se conectó por HTTP al backend y este se conectó por PDO a MySQL.

- Revisión sintáctica de los 9 archivos PHP: correcta.
- 13 pruebas de validación: correctas (cero, límites, fechas, campos obligatorios y tipos).
- 13 comprobaciones REST: correctas (CRUD, estados HTTP, JSON inválido, validación, exclusión y protección de registros eliminados).
- 7 comprobaciones de interfaz: correctas (listado, creación, edición, eliminación, exclusión, rechazo de CSRF inválido y vista móvil).
- Consulta SQL real: el producto de la interfaz quedó con ID 7, precio 27.00, stock 35 y eliminado=1. Se conservó la fila y dejó de aparecer en el listado activo.
- Lectura de Compose como YAML: correcta. Se verificaron los tres servicios, el nombre de base y que el frontend no reciba credenciales de MySQL.

Las capturas 01 a 06 y 08 son capturas reales del navegador al ejecutar la aplicación. La imagen 07 presenta la salida de una consulta real a MySQL exportada a una imagen legible; no es una captura de Docker Desktop. Se incluyen también la salida textual SQL, los resultados de las pruebas y la respuesta JSON final de productos activos.

## Pendiente de comprobar en la computadora de entrega

Este entorno no tenía un motor Docker disponible. **No se ejecutaron `docker compose up`, la construcción de las imágenes Apache/PHP, MySQL 8.4 en contenedor ni la persistencia a través de `docker compose down`/`up`.** Las imágenes de despliegue son PHP 8.3 Apache y MySQL 8.4; las pruebas locales con MySQL 8.0 no reemplazan esa comprobación de versión y despliegue.

Ejecuta los comandos del README, confirma los servicios saludables y repite las cuatro operaciones. Comprueba también la conservación de datos al reiniciar. Agrega la captura de `docker compose ps` a la evidencia de entrega.

Los scripts de PowerShell se entregan para Windows; no se ejecutaron en este entorno Linux. El repositorio remoto no se ha creado ni publicado porque no se proporcionó su URL. El ZIP está preparado para inicializar Git y subir los archivos con los comandos incluidos.

Los registros de las capturas pertenecen a la ejecución de prueba. En tu primer arranque se insertan únicamente los cinco productos de ejemplo; puedes repetir la prueba para obtener tus propias capturas e IDs.
