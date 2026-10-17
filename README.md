# Minimarket - Hito 3

Gestión de productos con **frontend PHP**, **API REST PHP** y **MySQL en Docker**. PHP es una de las tecnologías de backend permitidas por la consigna.

El proyecto usa tres servicios independientes: navegador → frontend → API REST → MySQL. El frontend no contiene consultas SQL ni credenciales de base de datos. El backend usa PDO y consultas preparadas. Eliminar un producto ejecuta un UPDATE, nunca un DELETE físico.

## 1. Qué instalar y comprobar en Windows

- Docker Desktop: https://docs.docker.com/desktop/setup/install/windows-install/
- Git: https://git-scm.com/install/windows
- Un navegador. VS Code o IntelliJ son opcionales para leer y editar el código.

**Para la ejecución completa con Docker no necesitas instalar PHP, Composer, Java, Maven, Gradle, Node, XAMPP ni MySQL en Windows.** Los Dockerfiles preparan PHP y sus extensiones; Compose descarga MySQL.

Abre PowerShell y ejecuta:

```powershell
git --version
docker --version
docker compose version
wsl --version
docker info
```

Los tres primeros deben mostrar versiones. `docker info` debe mostrar el servidor sin error. Si dice que no puede conectar, abre Docker Desktop y espera a que termine de iniciar. Usa contenedores Linux.

Si elegiste el backend WSL 2 y no tienes WSL, abre PowerShell como administrador y ejecuta `wsl --install`; reinicia cuando Windows lo pida. Para actualizarlo usa `wsl --update`. Revisa también que la virtualización esté habilitada en el Administrador de tareas → Rendimiento → CPU. Docker documenta los requisitos de Windows, memoria y virtualización en el enlace anterior.

Comprobación opcional desde la carpeta del proyecto:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\verificar.ps1
```

## 2. Arranque rápido en Windows

1. Extrae el ZIP. Entra en la carpeta que contiene `docker-compose.yml`. No ejecutes el proyecto dentro del ZIP.
2. Abre Docker Desktop y espera a que esté ejecutándose.
3. Abre PowerShell en esa carpeta, por ejemplo escribiendo `powershell` en la barra de dirección del Explorador.
4. Ejecuta:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\preparar.ps1
docker compose up -d --build
docker compose ps
```

`preparar.ps1` crea `.env` con claves aleatorias de desarrollo, sin mostrarlas. Si ya existe, lo conserva. El primer arranque necesita Internet para descargar imágenes y preparar PHP. MySQL puede tardar en inicializarse. Espera a que los tres servicios estén en ejecución; los controles de salud deben pasar.

Si prefieres crear la configuración manualmente, copia `.env.example` como `.env` y cambia las dos claves de ejemplo antes de iniciar. Nunca subas `.env` a Git.

Abre:

- Aplicación: **http://localhost:8080**
- API: **http://localhost:8081/api/productos**
- Salud de API y base de datos: **http://localhost:8081/health**

En Linux/macOS: `sh scripts/preparar.sh`, seguido de `docker compose up -d --build`.

## 3. Qué probar antes de entregar

1. **Listar:** deben aparecer los cinco productos de ejemplo del primer arranque.
2. **Crear:** pulsa Nuevo producto, completa nombre, proveedor, vencimiento, precio y stock. Guarda y verifica el mensaje y la fila.
3. **Editar:** abre Editar, modifica precio y stock, guarda y comprueba los nuevos valores.
4. **Eliminar:** anota el ID, pulsa Eliminar y confirma. La fila desaparece del listado activo.
5. **Comprobar MySQL:** demuestra que ese mismo ID sigue existiendo con `eliminado = 1`.
6. **Persistencia:** ejecuta `docker compose down`, después `docker compose up -d`. Los cambios deben seguir guardados.

Conexión a MySQL sin copiar contraseñas a la terminal de Windows:

```powershell
docker compose exec mysql sh -lc 'MYSQL_PWD="$MYSQL_PASSWORD" mysql -u"$MYSQL_USER" db_minimarket_pro413'
```

Dentro de MySQL:

```sql
SHOW CREATE TABLE Producto;
SELECT id, nombre, precio, stock, eliminado FROM Producto ORDER BY id;
SELECT id, nombre, eliminado FROM Producto WHERE eliminado = 1;
SELECT id, nombre, eliminado FROM Producto WHERE eliminado = 0;
exit;
```

También puedes usar `docker compose exec mysql mysql -u root -p` y escribir la clave `MYSQL_ROOT_PASSWORD` de tu `.env` cuando la solicite. Después ejecuta `USE db_minimarket_pro413;`.

Pruebas incluidas:

```powershell
docker compose exec backend php tests/validation.php
docker compose exec backend php tests/api-smoke.php
```

La primera prueba no modifica datos. La segunda crea un producto de prueba, lo edita y lo elimina lógicamente; deja su ID para comprobarlo en MySQL. Alternativa de la prueba HTTP desde Windows:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tests\api-smoke.ps1
```

## 4. Subirlo a tu GitHub desde cero

Este es un proyecto independiente. Crea en GitHub un repositorio **vacío** llamado `minimarket-hito3`, sin README, licencia ni .gitignore iniciales. Elige público si el docente necesita acceder sin invitación. Copia la URL HTTPS real de ese repositorio.

En la carpeta del proyecto:

```powershell
git init
git branch -M main
git add .
git status
git diff --cached --name-only
git commit -m "Implementa minimarket con API REST, frontend PHP y MySQL Docker"
git remote add origin https://github.com/TU_USUARIO/minimarket-hito3.git
git push -u origin main
```

**Reemplaza `TU_USUARIO` y la URL por los de tu repositorio real.** Antes del commit verifica que `.env` no aparezca entre los archivos preparados; `.env.example` sí debe aparecer. El informe está en `docs/Informe_Tecnico_Minimarket.pdf` y también se subirá.

Si Git te pide identidad:

```powershell
git config user.name "Tu nombre"
git config user.email "Tu correo de GitHub o tu correo noreply"
```

Repite `git commit` y `git push`. Cuando Git abra el navegador, inicia sesión con tu cuenta. No pegues contraseñas o tokens en el código ni en la URL del repositorio.

Si `origin` ya existe, revisa primero `git remote -v`. Solo si está apuntando al lugar incorrecto, cambia la URL con `git remote set-url origin URL_REAL`. Si el repositorio remoto ya tiene contenido, clónalo en otra carpeta y copia allí estos archivos; no uses push forzado ni sobrescribas proyectos existentes.

Después de subir, abre GitHub y comprueba que estén `backend/`, `frontend/`, `database/`, `docs/`, `docker-compose.yml`, `.env.example` y `README.md`. Entrega el enlace web del repositorio y el informe PDF, no la dirección localhost.

## 5. Estructura del código

| Ruta | Responsabilidad |
| --- | --- |
| `backend/public/index.php` | Rutas REST, métodos HTTP, respuestas JSON y errores. |
| `backend/src/Database.php` | Conexión PDO a MySQL mediante variables de entorno. |
| `backend/src/ProductValidator.php` | Campos obligatorios, longitudes, fechas y límites numéricos. |
| `backend/src/ProductRepository.php` | Consultas preparadas y eliminación lógica. |
| `backend/router.php` | Enrutamiento cuando se usa el servidor local `php -S`. |
| `frontend/public/index.php` | Listado, formularios, confirmación y mensajes. |
| `frontend/src/ApiClient.php` | Solicitudes HTTP JSON al backend con streams PHP. |
| `frontend/public/styles.css` | Diseño adaptable a escritorio y móvil. |
| `database/01-schema.sql` | Tabla `Producto`, restricciones e índice. |
| `database/02-seed.sql` | Cinco productos de ejemplo. |
| `database/verificacion.sql` | Consultas para comprobar estructura y registros. |
| `docker-compose.yml` | Tres servicios, red, volumen, puertos y controles de salud. |
| `tests/` | Pruebas de validación y del ciclo REST. |
| `docs/` | Informe técnico y evidencias. |

## 6. API REST

| Método | Ruta | Resultado |
| --- | --- | --- |
| GET | `/api/productos` | 200, productos con `eliminado = 0`. |
| GET | `/api/productos/{id}` | 200, producto activo; 404 si no existe o fue eliminado. |
| POST | `/api/productos` | 201, crea producto; incluye cabecera Location. |
| PUT | `/api/productos/{id}` | 200, reemplaza los cinco datos editables. |
| DELETE | `/api/productos/{id}` | 200, cambia `eliminado` de 0 a 1. |
| GET | `/health` | 200 si la conexión y tabla están disponibles; 503 si falla MySQL. |

JSON para POST o PUT (cabecera `Content-Type: application/json`):

```json
{
  "nombre": "Café 250 g",
  "proveedor": "Distribuidora Central",
  "fecha_vencimiento": "2027-12-31",
  "precio": "25.50",
  "stock": 20
}
```

Los importes se normalizan y envían como cadenas decimales para conservar los centavos. El ID lo genera MySQL; `eliminado` lo controla el backend. No se permite editar ni reactivar un producto eliminado desde los formularios.

Errores: 400 JSON inválido; 404 recurso inexistente; 405 método no permitido; 413 cuerpo demasiado grande; 415 contenido distinto de JSON; 422 validación de campos; 503 problema de persistencia; 500 error inesperado. El frontend muestra mensajes sin exponer detalles de conexión.

## 7. Base de datos y configuración

La base se llama exactamente `db_minimarket_pro413` y la tabla, `Producto` (P mayúscula). Sus columnas son: `id INT AUTO_INCREMENT PRIMARY KEY`, `nombre VARCHAR(100) NOT NULL`, `proveedor VARCHAR(100) NOT NULL`, `fecha_vencimiento DATE NOT NULL`, `precio DECIMAL(10,2) NOT NULL`, `stock INT NOT NULL`, `eliminado TINYINT(1) NOT NULL DEFAULT 0`. Los CHECK impiden precio o stock negativos y valores de eliminado diferentes de 0 o 1.

| Variable | Uso |
| --- | --- |
| MYSQL_ROOT_PASSWORD | Clave administrativa local; solo recibe este valor MySQL. |
| MYSQL_USER | Usuario de aplicación, por defecto minimarket_app. |
| MYSQL_PASSWORD | Clave del usuario de aplicación, utilizada por MySQL y backend. |
| FRONTEND_PORT | Puerto del navegador, por defecto 8080. |
| BACKEND_PORT | Puerto de API para pruebas, por defecto 8081. |
| MYSQL_PORT | Puerto opcional para cliente MySQL, por defecto 3307. |
| DB_HOST / DB_PORT | En el backend Docker: mysql / 3306. |
| DB_NAME | db_minimarket_pro413. |
| DB_USER / DB_PASSWORD | Reciben MYSQL_USER / MYSQL_PASSWORD. |
| API_BASE_URL | En frontend Docker: http://backend/api/productos. |

Dentro de la red Compose se usan los nombres `mysql` y `backend`, no localhost. En el navegador se usan los puertos publicados de la computadora. El frontend no recibe credenciales MySQL. No hace falta CORS: PHP solicita la API desde el servidor; el navegador solicita páginas HTML al frontend.

MySQL 8.4 guarda los datos en el volumen `mysql_data`, que Compose identifica con el prefijo del proyecto. Los SQL en `/docker-entrypoint-initdb.d/` se ejecutan solamente al inicializar un volumen vacío. Cambiar el SQL o las claves en `.env` no modifica automáticamente una base ya creada.

`depends_on` con `service_healthy` espera a que MySQL acepte consultas a la tabla antes de iniciar el backend, y a que este responda antes de iniciar el frontend. Las imágenes PHP 8.3 incluyen Apache. El backend instala `pdo_mysql` en la construcción. No hay compilación Java ni instalación npm.

## 8. Comandos útiles y errores comunes

```powershell
docker compose ps
docker compose logs --tail=80
docker compose logs -f backend
docker compose down
docker compose up -d
```

`down` detiene los servicios y conserva el volumen. **No añadas `-v` si quieres conservar tus productos:** esa opción borra el volumen.

- **`docker` no se reconoce:** instala Docker Desktop y abre una terminal nueva.
- **No conecta con Docker:** abre Docker Desktop, espera su inicio y revisa `docker info`.
- **Falta una variable:** genera `.env` con el script o comprueba que no se llame `.env.txt`.
- **Puerto ocupado:** cambia el puerto correspondiente en `.env` (ej. FRONTEND_PORT=8090) y vuelve a ejecutar `docker compose up -d`. Usa el nuevo puerto en el navegador. Los puertos internos no cambian.
- **MySQL unhealthy:** revisa `docker compose logs mysql`. Si cambiaste las claves después del primer arranque, restaura las que corresponden al volumen; no borres datos para solucionar un error de clave.
- **API no disponible:** revisa `http://localhost:8081/health`, luego los logs de backend y MySQL.
- **No aparecen cambios en PHP:** ejecuta `docker compose up -d --build` para reconstruir las imágenes con los archivos modificados.
- **No aparece la tabla:** revisa los logs y verifica el nombre `Producto`. Los scripts de inicio no se vuelven a ejecutar sobre un volumen existente.

## 9. Alternativa: PHP local y únicamente MySQL en Docker

La opción anterior es la recomendada. Para trabajar con PHP local necesitas **PHP 8.3 o compatible** y la extensión **pdo_mysql**. Verifica `php --version`, `php --ini` y `php -m`. El frontend utiliza HTTP streams, con `allow_url_fopen=On`.

Inicializa `.env` e inicia solo la base:

```powershell
docker compose up -d mysql
```

En una terminal PowerShell, desde la raíz del proyecto (reemplaza la clave por la de tu `.env`):

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3307"
$env:DB_NAME="db_minimarket_pro413"
$env:DB_USER="minimarket_app"
$env:DB_PASSWORD="CLAVE_APP_DE_TU_ENV"
php -S 127.0.0.1:8081 -t backend/public backend/router.php
```

En otra terminal, también desde la raíz:

```powershell
$env:API_BASE_URL="http://127.0.0.1:8081/api/productos"
php -S 127.0.0.1:8080 -t frontend/public
```

Abre http://localhost:8080. El servidor `php -S` es para desarrollo y demostración. No arranques al mismo tiempo los contenedores PHP en esos mismos puertos.

## 10. Entrega y alcance de la verificación

Revisa `docs/VALIDACION.md` para distinguir las pruebas ejecutadas aquí de las que debes comprobar en Docker Desktop. Las capturas del informe corresponden al proyecto; cualquier limitación del entorno se identifica expresamente. `docs/GUIA_EVIDENCIAS.md` indica cómo obtener las capturas de tu ejecución en Docker y la comprobación del registro conservado.

El proyecto es una práctica local de CRUD. No incluye autenticación, roles, ventas ni restauración de eliminados porque la consigna no los exige. Los puertos se publican solo en 127.0.0.1 para la demostración local.

## Referencias técnicas

- Docker Compose y orden de inicio: https://docs.docker.com/compose/how-tos/startup-order/
- Imagen oficial MySQL y scripts de inicialización: https://hub.docker.com/_/mysql
- Imagen oficial PHP y extensiones: https://hub.docker.com/_/php
- PDO MySQL: https://www.php.net/manual/en/ref.pdo-mysql.php

La consigna original se utilizó para los requisitos funcionales y nombres del esquema.
