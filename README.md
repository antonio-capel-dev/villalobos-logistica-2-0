# Villalobos Logistica 2.0

Aplicacion web de gestion logistica desarrollada como Proyecto Integrador del ciclo DAW en DIGITECH Malaga (2026).

Cliente real: **Villalobos Logistica** — empresa de transporte y almacenaje en Malaga con mas de 20 anos de experiencia, fundada en 2001.

---

## Tecnologias

| Capa | Tecnologia |
|---|---|
| Frontend | HTML5 semantico, CSS3, JavaScript Vanilla |
| Backend | PHP 8 con PDO y prepared statements |
| Base de datos | MySQL (XAMPP) |
| Email | PHPMailer + Mailtrap (sandbox SMTP) |
| Mapas | Leaflet 1.9 / OpenStreetMap |
| Modulo Python | Calculadora de distancias (Nominatim + Haversine) y exportacion CSV a MySQL |
| Modulo Java | EstadisticasBilling — cierre mensual via JDBC |
| Control de versiones | Git + GitHub |
| SEO | Schema.org JSON-LD, sitemap.xml, robots.txt, Open Graph |

---

## Estructura del proyecto

```
villalobos-logistica-2-0/
|
+-- public/                    # Frontend publico
|   +-- index.html             # Pagina principal
|   +-- contacto.html
|   +-- servicios.html
|   +-- quienes-somos.html
|   +-- galeria.html
|   +-- aviso-legal.html
|   +-- politica-privacidad.html
|   +-- sitemap.xml
|   +-- robots.txt
|   +-- assets/
|       +-- css/styles.css
|       +-- js/
|       |   +-- main.js        # Formulario + validacion con regex
|       |   +-- mapa.js        # Leaflet / OpenStreetMap
|       |   +-- chat.js        # Chat widget guiado
|       |   +-- auth.js        # Login panel privado
|       |   +-- panel_portes.js
|       |   +-- utilidades.js
|       +-- img/
|           +-- web-p/         # Fotos reales del cliente
|           +-- avatares/      # Avatares Google Reviews
|
+-- panel/                     # Panel privado (requiere login)
|   +-- dashboard.php          # Dashboard con KPIs y modulo Java
|   +-- portes.php
|   +-- mensajes.php
|   +-- login.php
|
+-- backend/
|   +-- conexion.php           # PDO connection
|   +-- auth_guard.php         # Proteccion de rutas por rol
|   +-- api/
|       +-- auth.php           # Login / logout
|       +-- contacto.php       # Formulario publico + PHPMailer
|       +-- portes.php         # CRUD completo de portes
|       +-- mensajes.php       # Bandeja de mensajes
|       +-- estadisticas.php   # KPIs para dashboard
|       +-- calcular_distancia.php   # Puente PHP -> Python
|       +-- cierre_ejercicio.php     # Puente PHP -> Java
|
+-- database/
|   +-- schema.sql             # Estructura + datos de prueba
|
+-- modules/
|   +-- python/
|   |   +-- calculadora_distancias.py  # Estima km y precio via Nominatim
|   |   +-- generador_reportes.py      # Exporta portes a CSV desde MySQL
|   +-- java/
|       +-- EstadisticasBilling.java   # Cierre mensual via JDBC
|       +-- EstadisticasBilling.jar    # JAR compilado
|       +-- compilar.sh / compilar.bat
|       +-- libs/mysql-connector-j.jar
|
+-- README.md
```

---

## Instalacion local

### Requisitos
- XAMPP (Apache + MySQL) version 8.x
- PHP 8.0 o superior
- Python 3.x + pip
- Java JDK 17 o superior

### Pasos

1. **Clonar el repositorio** dentro de la carpeta htdocs de XAMPP:
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/antonio-capel-dev/villalobos-logistica-2-0.git
   ```

2. **Importar la base de datos** en phpMyAdmin:
   - Crear base de datos llamada: `villalobos_logistica_2`
   - Importar el archivo: `database/schema.sql`

3. **Acceder en el navegador**:
   - Web publica: `http://localhost/villalobos-logistica-2-0/public/index.html`
   - Panel privado: `http://localhost/villalobos-logistica-2-0/panel/login.php`

4. **Instalar dependencias Python**:
   ```bash
   pip install pymysql
   ```

5. **Ejecutar el generador de reportes CSV**:
   ```bash
   python modules/python/generador_reportes.py
   ```

6. **Compilar el modulo Java** (requiere mysql-connector-j.jar en modules/java/libs/):
   ```bash
   cd modules/java
   compilar.bat   # Windows
   # o
   bash compilar.sh   # Linux/Mac
   ```

---

## Usuarios de prueba

| Rol | Email | Contrasena | Acceso |
|---|---|---|---|
| admin | admin@villalobos.local | 123456 | Panel completo + modulo Java |
| editor | editor@villalobos.local | 123456 | Panel + crear y editar portes |
| cliente | cliente@empresa.local | 123456 | Solo sus propios portes |
| conductor | paco@villalobos.local | 123456 | Solo los portes que tiene asignados |

---

## Funcionalidades principales

### Web publica
- Presentacion de la empresa con contenido real del cliente
- Mapa interactivo con Leaflet + OpenStreetMap
- Formulario de contacto en 3 pasos con validacion cliente y servidor
- Estimador de precio en vivo: calcula km y precio orientativo al rellenar origen/destino (modulo Python)
- Email automatico con PHPMailer via Mailtrap
- Chat widget guiado con chips de servicio, typing indicator y validacion de contacto
- Testimonios estilo Google Reviews
- SEO tecnico: Schema.org JSON-LD, Open Graph, sitemap.xml, robots.txt

### Panel privado
- Login con sesion PHP y session_regenerate_id()
- Control de acceso por roles (admin / editor / cliente / conductor)
- CRUD completo de portes via API REST con fetch()
- Dashboard con KPIs en tiempo real
- Cierre de ejercicio mensual ejecutado por el modulo Java (solo admin)
- Bandeja de mensajes con marcado leido/no leido

### Modulos diferenciadores
- **Python — calculadora_distancias.py**: geocodifica con Nominatim y calcula distancia + precio estimado
- **Python — generador_reportes.py**: conecta a MySQL y exporta portes a CSV
- **Java — EstadisticasBilling.jar**: conecta a MySQL via JDBC y calcula cierre mensual (ingresos, km, conductor top)

---

## Credenciales SMTP (Mailtrap sandbox)

```
Host:     sandbox.smtp.mailtrap.io
Port:     2525
Username: a5cce6e9289318
Password: b3eecf41fef210
```

Solo para entorno de desarrollo. Los emails se capturan en Mailtrap, no llegan al destinatario real.

---

## Autor

**Antonio Capel** — DAW, DIGITECH Malaga, 2026
