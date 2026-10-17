Minimarket - Hito 3
Sistema para registrar, consultar, editar y eliminar productos de un minimarket, desarrollado para la materia de Programación III.
Estudiante: ARANZA MAYERLIN RAMOS LUNA
Docente: Ing. Ruben Gonzalo Soria
El proyecto está dividido en un frontend PHP, un backend con API REST y una base de datos MySQL. El frontend envía las solicitudes al backend, que se encarga de validar los datos y consultar la base de datos.
La eliminación es lógica: el producto deja de aparecer en la aplicación, pero su registro se conserva con el campo eliminado = 1.
Requisitos
- Git.
- Docker Desktop con contenedores Linux.
- Un navegador.
Docker prepara PHP 8.3 y MySQL 8.4 dentro de los contenedores. Antes de iniciar el proyecto, abrir Docker Desktop y comprobar en PowerShell que docker info muestre la información del servidor sin errores.
Ejecutar el proyecto
Clonar el repositorio y entrar a la carpeta:
git clone https://github.com/aranzaramos0/minimarket-hito3.git
cd minimarket-hito3
Si se descargó como ZIP, extraerlo y abrir PowerShell en la carpeta que contiene docker-compose.yml.
Crear la configuración e iniciar los servicios:
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\preparar.ps1
docker compose up -d --build
docker compose ps
El script crea el archivo .env con las contraseñas locales. Si el archivo ya existe, conserva su configuración. .env está excluido de Git; el archivo de ejemplo que se comparte es .env.example.
El primer arranque puede tardar mientras se descargan las imágenes y se inicializa MySQL. Cuando los tres servicios estén en estado healthy, abrir:
- Aplicación: http://localhost:8080.
- Productos en JSON: http://localhost:8081/api/productos.
- Estado del backend y la base de datos: http://localhost:8081/health.
En Linux o macOS, usar sh scripts/preparar.sh para generar .env y luego ejecutar los mismos comandos de Docker Compose.
Organización del proyecto
Carpeta o archivo	Contenido
frontend/	Listado de productos y formularios de registro, edición y eliminación.
backend/	API REST, validaciones y conexión a MySQL mediante PDO.
database/	Estructura de la base de datos, productos iniciales y consultas de comprobación.
scripts/	Preparación del archivo .env y revisión del entorno.
tests/	Pruebas de validación y de la API.
docs/	Informe técnico, resultados de pruebas y evidencias.
docker-compose.yml	Configuración de los tres servicios y del volumen de MySQL.


La base de datos se llama db_minimarket_pro413. La tabla Producto contiene los campos id, nombre, proveedor, fecha_vencimiento, precio, stock y eliminado. El precio y el stock deben ser mayores o iguales a cero.
API REST
Método	Ruta	Operación
GET	/api/productos	Listar los productos activos.
GET	/api/productos/{id}	Consultar un producto activo.
POST	/api/productos	Registrar un producto.
PUT	/api/productos/{id}	Editar los datos de un producto.
DELETE	/api/productos/{id}	Marcar un producto como eliminado.
GET	/health	Comprobar el acceso del backend a la base de datos.


Para registrar o editar desde Postman, enviar un cuerpo JSON con la cabecera Content-Type: application/json:
{
  "nombre": "Café 250 g",
  "proveedor": "Distribuidora Central",
  "fecha_vencimiento": "2027-12-31",
  "precio": "25.50",
  "stock": 20
}
Comprobaciones
En una base nueva se cargan cinco productos de ejemplo. Desde la aplicación se puede registrar otro producto, modificar su precio y stock y, finalmente, eliminarlo.
Las pruebas incluidas se ejecutan con:
docker compose exec backend php tests/validation.php
docker compose exec backend php tests/api-smoke.php
La prueba de la API crea un producto, lo modifica y lo elimina lógicamente. Su registro queda disponible para comprobarlo en MySQL.
Para consultar la base de datos:
docker compose exec mysql mysql -u root -p
Ingresar la clave MYSQL_ROOT_PASSWORD del archivo .env y ejecutar:
USE db_minimarket_pro413;
SELECT id, nombre, precio, stock, eliminado FROM Producto ORDER BY id;
exit;
Para detener y volver a iniciar el proyecto:
docker compose down
docker compose up -d
Los datos se guardan en el volumen mysql_data. Usar docker compose down -v elimina ese volumen y los datos que contiene.
Problemas al iniciar
- Docker no conecta: abrir Docker Desktop, esperar a que termine de iniciar y volver a ejecutar docker info.
- El puerto 8080 está ocupado: cambiar FRONTEND_PORT=8090 en .env, ejecutar docker compose up -d y abrir http://localhost:8090.
- El backend o MySQL no responden: revisar docker compose logs --tail=80 backend mysql.
- Los cambios en PHP no aparecen: reconstruir con docker compose up -d --build.
Documentación
El [informe técnico](docs/Informe_Tecnico_Minimarket.pdf) explica la arquitectura e incluye las evidencias. 
