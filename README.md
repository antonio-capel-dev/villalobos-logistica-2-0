# Villalobos Logistica 2.0

Aplicacion web de gestion logistica desarrollada como Proyecto Integrador del ciclo DAW en DIGITECH Malaga (2026).

Cliente real: **Villalobos Logistica** â€” empresa de transporte y almacenaje en Malaga con mas de 20 anos de experiencia, fundada en 2001.

---

## Tecnologias

| Capa | Tecnologia |
|---|---|
| Frontend | HTML5 semantico, CSS3 con variables, JavaScript Vanilla |
| Backend | PHP 8 con PDO y prepared statements |
| Base de datos | MySQL (XAMPP) |
| Email | PHPMailer + Mailtrap (sandbox SMTP) |
| Mapas | Leaflet 1.9 / OpenStreetMap |
| Reportes | Python 3 + PyMySQL |
| Control de versiones | Git + GitHub |
| SEO | Schema.org JSON-LD, sitemap.xml, robots.txt, Open Graph |

---

## Estructura del proyecto

```
villalobos-logistica-2-0/
|
â”œâ”€â”€ public/                    # Frontend publico
â”‚   â”œâ”€â”€ index.html             # Pagina principal
â”‚   â”œâ”€â”€ contacto.html
â”‚   â”œâ”€â”€ servicios.html
â”‚   â”œâ”€â”€ quienes-somos.html
â”‚   â”œâ”€â”€ galeria.html
â”‚   â”œâ”€â”€ aviso-legal.html
â”‚   â”œâ”€â”€ politica-privacidad.html
â”‚   â”œâ”€â”€ sitemap.xml
â”‚   â”œâ”€â”€ robots.txt
â”‚   â””â”€â”€ assets/
â”‚       â”œâ”€â”€ css/styles.css
â”‚       â”œâ”€â”€ js/
â”‚       â”‚   â”œâ”€â”€ main.js        # Formulario + validacion con regex
â”‚       â”‚   â”œâ”€â”€ mapa.js        # Leaflet / OpenStreetMap
â”‚       â”‚   â”œâ”€â”€ chat.js        # Chat widget guiado
â”‚       â”‚   â”œâ”€â”€ auth.js        # Login panel privado
â”‚       â”‚   â”œâ”€â”€ panel_portes.js
â”‚       â”‚   â””â”€â”€ utilidades.js
â”‚       â””â”€â”€ img/
â”‚           â”œâ”€â”€ web-p/         # Fotos reales del cliente
â”‚           â””â”€â”€ avatares/      # Avatares Google Reviews
|
â”œâ”€â”€ panel/                     # Panel privado (requiere login)
â”‚   â””â”€â”€ dashboard.php
|
â”œâ”€â”€ backend/
â”‚   â”œâ”€â”€ conexion.php           # PDO connection
â”‚   â”œâ”€â”€ auth_guard.php         # Proteccion de rutas por rol
â”‚   â””â”€â”€ api/
â”‚       â”œâ”€â”€ auth.php           # Login / logout
â”‚       â”œâ”€â”€ contacto.php       # Formulario publico + PHPMailer
â”‚       â”œâ”€â”€ portes.php         # CRUD completo de portes
â”‚       â”œâ”€â”€ mensajes.php       # Bandeja de mensajes
â”‚       â”œâ”€â”€ estadisticas.php   # KPIs para dashboard
â”‚       â””â”€â”€ logout.php
|
â”œâ”€â”€ database/
â”‚   â””â”€â”€ schema.sql             # Estructura + datos de prueba
|
â”œâ”€â”€ scripts/
â”‚   â””â”€â”€ generador_reportes.py  # Exporta portes a CSV con Python
|
â””â”€â”€ README.md
```

---

## Instalacion local

### Requisitos
- XAMPP (Apache + MySQL) â€” version 8.x recomendada
- PHP 8.0 o superior
- Python 3.x + pip

### Pasos

1. **Clonar el repositorio** dentro de la carpeta htdocs de XAMPP:
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/antonio-capel-dev/villalobos-logistica-2-0.git
   ```

2. **Importar la base de datos** en phpMyAdmin:
   - Crear base de datos llamada: `villalobos_db`
   - Importar el archivo: `database/schema.sql`

3. **Configurar la conexion** en `backend/conexion.php`:
   ```php
   $host = 'localhost';
   $db   = 'villalobos_db';
   $user = 'root';
   $pass = '';        // vacio por defecto en XAMPP
   ```

4. **Acceder en el navegador**:
   - Web publica: `http://localhost/villalobos-logistica-2-0/public/index.html`
   - Panel privado: `http://localhost/villalobos-logistica-2-0/panel/dashboard.php`

5. **Instalar dependencias Python**:
   ```bash
   pip install pymysql
   ```

6. **Ejecutar el generador de reportes**:
   ```bash
   python scripts/generador_reportes.py
   ```
   Genera `reporte_portes.csv` en la carpeta del script.

---

## Usuarios de prueba

| Rol | Email | Contrasena | Acceso |
|---|---|---|---|
| admin | admin@villalobos.local | 123456 | Panel completo + gestion de usuarios |
| editor | editor@villalobos.local | 123456 | Panel + crear y editar portes |
| cliente | cliente@empresa.local | 123456 | Solo sus propios portes |
| conductor | paco@villalobos.local | 123456 | Solo los portes que tiene asignados |

---

## Funcionalidades principales

### Web publica
- Presentacion de la empresa con contenido real del cliente
- Mapa interactivo con Leaflet + OpenStreetMap (API externa publica)
- Formulario de contacto con validacion en cliente (regex, `closest()`) y servidor
- Email automatico con PHPMailer via Mailtrap
- Chat widget guiado para captar leads
- Testimonios estilo Google Reviews con avatares reales
- SEO tecnico: Schema.org JSON-LD, Open Graph, sitemap.xml, robots.txt

### Panel privado
- Login con sesion PHP y `session_regenerate_id()`
- Control de acceso por roles (admin / editor / cliente / conductor)
- CRUD completo de portes via API REST con fetch()
- Dashboard con KPIs: total portes, mensajes sin leer, portes del mes
- Bandeja de mensajes de contacto con marcado de leido/no leido

### Elementos diferenciadores
- **Chat widget guiado**: flujo de preguntas guiadas sin backend
- **PHPMailer**: email real al negocio en cada solicitud de presupuesto
- **Generador de reportes Python**: conecta a MySQL y exporta CSV

---

## Credenciales SMTP (Mailtrap sandbox)

```
Host:     sandbox.smtp.mailtrap.io
Port:     2525
Username: a5cce6e9289318
Password: b3eecf41fef210
```

> Solo para entorno de desarrollo. Los emails se capturan en Mailtrap, no llegan al destinatario real.

---

## Autor

**Antonio Capel** â€” DAW, DIGITECH Malaga, 2026
