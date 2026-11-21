# Gestor de Historias — versión con microservicios

Conversión del proyecto monolítico `GestorHistorias_Monolitico` a **microservicios**.
Tecnologías: **HTML, CSS y JavaScript** (cliente) y **PHP con Slim 4 y Composer** (servidor, solo lógica de negocio).

## 1. Arquitectura

```
                    NAVEGADOR (HTML + CSS + JavaScript)
                              cliente/
                                 │  fetch() → JSON
         ┌───────────────────────┼───────────────────────┐
         ▼                       ▼                       ▼
 servicios/sprints       servicios/historias      servicios/informes
 (Slim + MVC)            (Slim + MVC)             (Slim + MVC)
         │                    │      │                 │      │
   ms_sprints_db       ms_historias_db └───── GET ──────┘      │
                                 │  ¿existe el sprint?        │
                                 └──────────► sprints ◄───────┘
```

| Microservicio | Responsabilidad | Base de datos |
|---|---|---|
| `servicios/sprints` | Crear y consultar sprints | `ms_sprints_db` |
| `servicios/historias` | Crear, consultar, modificar y borrar historias. Antes de guardar, pregunta al servicio de sprints si el sprint existe | `ms_historias_db` |
| `servicios/informes` | Calcular el informe de avance. **No tiene BD**: pide los datos a los otros dos servicios | — |
| `cliente/` | Páginas HTML + CSS + JS que consumen los servicios | — |

Cada servicio tiene su propia base de datos y su propio `composer.json` / `vendor/`:
son independientes entre sí. Por eso hay clases repetidas (`Conexion`, `VistaJson`, `ClienteHttp`…):
en microservicios no se comparte código entre servicios.
`historias.sprint_id` ya no es clave foránea porque las tablas están en bases de datos distintas.

## 2. Composer y Slim

- **Composer** administra las dependencias: `composer.json` (en cada servicio) declara que se necesita
  `slim/slim` (el micro-framework) y `slim/psr7` (clases de petición y respuesta HTTP).
  Composer las descarga a la carpeta `vendor/` y genera `vendor/autoload.php`.
- **Autocarga PSR-4:** `"App\\": "src/"` hace que la clase `App\Modelo\Sprint` se cargue sola desde
  `src/Modelo/Sprint.php`. Ya no hay `require_once` por cada archivo.
- **Slim** recibe la petición HTTP y la manda al controlador correcto según la **ruta**. Las rutas
  se declaran en `public/index.php`, por ejemplo:
  `$app->get('/sprints/{id:[0-9]+}', [$controlador, 'obtener']);`
- **`public/.htaccess`** (mod_rewrite de Apache) envía todas las direcciones a `public/index.php`.

## 3. Estructura MVC de cada microservicio

```
servicios/historias/
├── composer.json              Dependencias (Slim) y autocarga
├── public/
│   ├── index.php              Crea la app Slim, los objetos y declara las rutas
│   └── .htaccess              Redirige las rutas a index.php
└── src/
    ├── Config/Configuracion.php   Datos de configuración (privados)
    ├── Modelo/                M  → datos y reglas de negocio
    │   ├── Historia.php           entidad con validaciones (encapsulada)
    │   ├── HistoriaRepositorio.php    único lugar con SQL
    │   ├── Conexion.php               abre la conexión a MySQL
    │   ├── ClienteSprints.php         pregunta al servicio de sprints
    │   ├── ClienteHttp.php            hace la petición HTTP a otro servicio
    │   └── ServicioNoDisponibleException.php
    ├── Vista/VistaJson.php    V  → la "vista" de un servicio es la respuesta JSON
    └── Controlador/HistoriaControlador.php   C → recibe la petición y coordina
```
`sprints` e `informes` tienen la misma forma.

## 4. Encapsulamiento (dónde se ve)

- **Atributos `private`** en todas las entidades (`Sprint`, `Historia`, `ResumenHistorias`, `Informe`).
- **Getters y setters con validación**: `setPuntos()`, `setEstado()`, `setFechas()`… lanzan
  `InvalidArgumentException` si el dato no es válido. Una historia inválida no puede existir.
- **Constructor privado** en `Historia`: solo se crea con `Historia::nueva()` o `Historia::desdeFila()`.
- **Regla de negocio dentro del objeto**: `Historia::setEstado()` decide la fecha de finalización.
- **`aArreglo()`**: forma controlada de exponer los datos (lo que sale en el JSON).
- **Métodos `private`** para detalles internos (`bd()`, `enteroPositivo()`, `convertir()`, `atender()`…).
- **Configuración privada** (`private const PREDETERMINADOS`) accesible solo con métodos públicos.
- **JavaScript**: clases con campos y métodos privados (`#`), por ejemplo `ClienteHttp.#enviar()`.

## 5. API (rutas Slim)

Base: `http://localhost/gestor-microservicios/servicios/<servicio>/public`.
Respuestas JSON: `{"ok": true, "datos": ...}` o `{"ok": false, "mensaje": "..."}`.

| Servicio | Método y ruta | Qué hace |
|---|---|---|
| sprints | `GET /sprints` | Lista de sprints |
| sprints | `GET /sprints/3` | Un sprint |
| sprints | `POST /sprints` | Crea sprint. Cuerpo: `{"nombre","fecha_inicio","fecha_fin"}` |
| historias | `GET /historias` (`?sprint_id=2` opcional) | Lista de historias |
| historias | `GET /historias/5` | Una historia |
| historias | `POST /historias` | Crea historia |
| historias | `PUT /historias/5` | Modifica historia |
| historias | `DELETE /historias/5` | Borra historia |
| informes | `GET /informes` | Informe de avance |

Códigos: 200 / 201 correcto · 400 dato inválido · 404 no existe · 503 otro servicio no responde · 500 error interno.

## 6. Cómo ejecutarlo (XAMPP)

1. Instala **Composer** (https://getcomposer.org) y enciende **Apache** y **MySQL** en XAMPP.
2. Copia la carpeta `gestor-microservicios` dentro de `htdocs`.
3. **Instala las dependencias de cada servicio** (3 veces):
   ```
   cd htdocs/gestor-microservicios/servicios/sprints    && composer install
   cd ../historias                                      && composer install
   cd ../informes                                       && composer install
   ```
4. En phpMyAdmin → **Importar**: `base_datos/sprints_db.sql` y `base_datos/historias_db.sql`
   (opcional: `base_datos/datos_ejemplo.sql`).
5. Prueba los servicios en el navegador (deben mostrar JSON):
   - `http://localhost/gestor-microservicios/servicios/sprints/public/sprints`
   - `http://localhost/gestor-microservicios/servicios/historias/public/historias`
   - `http://localhost/gestor-microservicios/servicios/informes/public/informes`
6. Abre la aplicación: `http://localhost/gestor-microservicios/cliente/index.html`

**Requisitos:** PHP 7.4 o superior con `mysqli`, `mbstring` y `allow_url_fopen`; Apache con `mod_rewrite`
activo (viene activo en XAMPP). Abre siempre la app por `http://localhost/...`, no con doble clic.

**Si algo falla**
- *Página "Not found" de Slim* → no corriste `composer install`, falta `mod_rewrite`, o el `.htaccess` no se copió.
- *Error "vendor/autoload.php"* → falta `composer install` en ese servicio.
- *"No se pudo conectar con el servicio"* → Apache apagado o carpeta mal ubicada.
- *Error 500* → revisa usuario/clave de MySQL (ver sección 7) y que las bases existan.
- *Error 503 al guardar una historia* → el servicio de sprints no responde; ábrelo en el navegador.

## 7. Git y `.gitignore`

El archivo `.gitignore` (en la raíz) evita subir al repositorio lo que **no debe subirse**:

| Se ignora | Por qué |
|---|---|
| `vendor/` | Son las librerías descargadas por Composer (pesan mucho y se reinstalan con `composer install`) |
| `src/Config/local.php`, `.env` | Configuración propia de cada computador y **claves** (nunca se suben) |
| `*.log`, `*.cache`, `*.zip` | Archivos generados |
| `.idea/`, `.vscode/`, `.DS_Store`, `Thumbs.db` | Archivos del editor y del sistema operativo |

**Sí se suben** los `composer.json` y, una vez generados por `composer install`, los `composer.lock`
(fijan las versiones exactas de las librerías para todo el equipo).

**Configuración local:** los valores de MySQL predeterminados son los de XAMPP (`root`, sin clave).
Si los tuyos son distintos, copia `src/Config/local.ejemplo.php` como `src/Config/local.php` y cámbialos;
ese archivo queda ignorado por Git.

```
git init
git add .
git commit -m "Gestor de historias con microservicios"
```

## 8. Qué cambió respecto al monolito

| Monolito | Microservicios |
|---|---|
| Una sola BD con 2 tablas y clave foránea | 2 BD, una por servicio; el sprint se verifica por HTTP |
| PHP genera el HTML con `echo` | PHP solo devuelve JSON; el HTML lo arma JavaScript |
| `require` de cada archivo | Composer + autocarga PSR-4 |
| Archivos `.php` sueltos como "acciones" | Rutas Slim + controladores |
| SQL y validaciones repetidos en cada archivo | SQL solo en el repositorio; validaciones solo en la entidad |
| Informe con `GROUP BY` | Informe calculado en PHP con datos de los otros servicios |
| Menú copiado en cada página | `Menu.js` lo dibuja una sola vez |
| `<style>` y estilos sueltos | **Todo** el CSS en `cliente/css/estilos.css`; ningún `style=` ni `<style>` |
| Borrar con enlace GET | Borrar con petición `DELETE` desde un botón |

## 9. Reglas del taller

- [x] Microservicios (3) + cliente web
- [x] Orientado a objetos
- [x] MVC dentro de cada microservicio
- [x] Encapsulamiento
- [x] Composer y la librería Slim
- [x] `.gitignore` para lo que no se sube
- [x] HTML, CSS, JavaScript y PHP; PHP solo en el servidor (lógica de negocio), JavaScript solo en el cliente
- [x] CSS únicamente en un archivo `.css`
- [x] Diseño en colores pastel
