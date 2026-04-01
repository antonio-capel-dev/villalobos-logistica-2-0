# Villalobos Logística 2.0

Aplicación web de gestión logística desarrollada como Trabajo de Fin de Grado del ciclo DAW en DIGITECH.

Cliente real: **Villalobos Logística** — empresa de transporte y almacenaje en Málaga con más de 50 años de experiencia.

---

## Tecnologías

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Backend | PHP 8 con PDO |
| Base de datos | MySQL (XAMPP) |
| Email | PHPMailer + Mailtrap |
| Mapas | Leaflet / OpenStreetMap |
| Reportes | Python 3 + PyMySQL |
| Control de versiones | Git + GitHub |

---

## Estructura del proyecto



---

## Instalación local

### Requisitos
- XAMPP (Apache + MySQL)
- PHP 8+
- Python 3 + pip

### Pasos

1. Clonar el repositorio dentro de  de XAMPP:


2. Importar la base de datos en phpMyAdmin:
   - Crear base de datos: 
   - Importar: 

3. Configurar la conexión en  si es necesario (host, usuario, contraseña).

4. Acceder en el navegador:
   - Web pública: 
   - Panel privado: 

5. Instalar dependencias Python:


6. Ejecutar el generador de reportes:


---

## Usuarios de prueba

| Rol | Email | Contraseña | Acceso |
|---|---|---|---|
| admin | admin@villalobos.local | 123456 | Panel completo + gestión |
| editor | editor@villalobos.local | 123456 | Panel + editar portes |
| cliente | cliente@empresa.local | 123456 | Solo sus portes |
| conductor | paco@villalobos.local | 123456 | Solo sus portes asignados |

---

## Funcionalidades principales

- **Web pública**: presentación de la empresa, mapa interactivo (Leaflet), formulario de contacto con email automático (PHPMailer), chat widget guiado.
- **Panel privado**: gestión de portes (CRUD), dashboard con KPIs, bandeja de mensajes de contacto.
- **Control de acceso por roles**: cada usuario ve solo lo que le corresponde.
- **Generador de reportes**: script Python que conecta a MySQL y exporta portes a CSV.

---

## Autor

Antonio Capel — DAW, DIGITECH Málaga, 2026