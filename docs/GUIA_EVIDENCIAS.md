# Capturas para la entrega

Con Docker Desktop abierto y los tres servicios funcionando, toma estas capturas reales en tu computadora. En Windows puedes usar Win + Shift + S. No incluyas el contenido de `.env` ni claves en las imágenes.

1. **Servicios:** PowerShell con `docker compose ps`, mostrando mysql, backend y frontend en ejecución y saludables.
2. **Listado inicial:** http://localhost:8080 con la tabla de productos activos.
3. **Creación:** formulario completo antes de guardar, y listado con mensaje de éxito y producto nuevo. Usa un nombre reconocible, por ejemplo “Café prueba Hito 3”.
4. **Edición:** modifica precio y stock de ese mismo producto y captura el listado actualizado.
5. **Eliminación lógica:** captura la confirmación y luego el listado donde ya no aparece. Anota su ID antes de eliminar.
6. **MySQL:** entra con el comando del README y ejecuta `SELECT id, nombre, precio, stock, eliminado FROM Producto WHERE id = ID_ANOTADO;`. Sustituye ID_ANOTADO por el número real; debe existir la fila con eliminado=1.
7. **Exclusión:** abre http://localhost:8081/api/productos o ejecuta `SELECT id, nombre FROM Producto WHERE eliminado = 0;` y verifica que no está ese ID.
8. **Persistencia:** detén con `docker compose down`, vuelve a iniciar con `docker compose up -d` y verifica que se mantienen los datos.

Guarda las capturas en esta carpeta y agrega la evidencia de Docker a tu informe si el docente exige la demostración de ese entorno. Las pruebas incluidas crean registros de ejemplo; una base recién inicializada no contiene los registros de las capturas previas hasta repetir sus operaciones.
