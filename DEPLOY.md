# Guia de Despliegue - Villalobos Logistica 2.0

Documento operativo paso a paso para llevar el proyecto desde el repositorio Git
al hosting de produccion. Tiempo estimado: **2-3 horas** la primera vez.

---

## 1. Pre-requisitos

| Item | Donde se obtiene |
|---|---|
| Credenciales FTP / SFTP | Panel del hosting de la clienta |
| Credenciales panel hosting (cPanel/Plesk) | Email de alta del hosting |
| Dominio configurado | `villaloboslogistica.com` apuntando al hosting |
| Cuenta de email IMAP/SMTP | Crear `info@villaloboslogistica.com` en el panel |
| Permisos para crear BD MySQL | Panel del hosting |
| Acceso a un cliente FTP | FileZilla / WinSCP |

---

## 2. Pre-flight checklist (antes de subir)

Ejecutar EN LOCAL desde la raiz del proyecto:

- [ ] `git status` -> working tree limpio
- [ ] `git pull origin pruebas` -> al dia con remoto
- [ ] `php -l backend/api/auth.php` -> No syntax errors
- [ ] `php -l backend/api/portes.php` -> No syntax errors
- [ ] `php -l backend/api/cierre_ejercicio.php` -> No syntax errors
- [ ] `php -l backend/api/contacto.php` -> No syntax errors
- [ ] `php -l panel/dashboard.php` -> No syntax errors
- [ ] Verificar que `.env` NO se sube (esta en .gitignore)

Smoke test rapido:
```
cd 0Proyecto-villalobos\villalobos-logistica-2-0
C:\xampp\php\php.exe -S localhost:8765 -t public
```
Abrir http://localhost:8765/index.html y verificar:
- Carga sin errores en consola
- Formulario valida con regex (probar email invalido)
- Mapa Leaflet aparece
- Chatbot responde

---

## 3. Crear la base de datos en produccion

Desde el panel del hosting (cPanel -> "Bases de datos MySQL"):

1. Crear base de datos: `nombrecuenta_villalobos`
2. Crear usuario: `nombrecuenta_villa` con contrasena fuerte (>=16 chars)
3. Asignar usuario a la BD con TODOS los privilegios excepto GRANT
4. Anotar:
   - Host MySQL (suele ser `localhost` o `mysql.midominio.com`)
   - Nombre BD completo: `nombrecuenta_villalobos`
   - Usuario completo: `nombrecuenta_villa`
   - Contrasena

Importar el schema desde phpMyAdmin del panel:
1. Seleccionar la BD recien creada
2. Pestana "Importar"
3. Subir `database/schema.sql`
4. Verificar que se crearon 4 tablas: `usuarios`, `mensajes_contacto`, `portes`, `chat_leads`
5. Comprobar datos demo: 4 usuarios, 15 portes, 5 mensajes, 5 chat_leads

**IMPORTANTE - cambiar contrasenas demo:**
Tras importar, ejecutar en SQL:
```sql
UPDATE usuarios SET password_hash = '$2y$10$XXXXX' WHERE email = 'admin@villalobos.local';
```
Generar el hash con PHP local:
```
C:\xampp\php\php.exe -r "echo password_hash('TU_NUEVA_PASSWORD_FUERTE', PASSWORD_DEFAULT);"
```

---

## 4. Configurar SMTP de produccion

Hay dos opciones:

### Opcion A - SMTP del hosting (recomendado)
1. Crear cuenta `info@villaloboslogistica.com` en el panel del hosting
2. Anotar:
   - SMTP_HOST (suele ser `mail.villaloboslogistica.com` o el del hosting)
   - SMTP_PORT (587 con TLS o 465 con SSL)
   - SMTP_USER = info@villaloboslogistica.com
   - SMTP_PASS

### Opcion B - Mailtrap sandbox (solo demo)
Mantener las credenciales sandbox. No llegan emails reales pero la web funciona.

---

## 5. Subir archivos por FTP

Conexion FTP/SFTP:
- Host: el indicado por el hosting (suele ser `ftp.villaloboslogistica.com`)
- Usuario / Contrasena: del panel del hosting
- Puerto: 21 (FTP) o 22 (SFTP recomendado)

**Subir TODO** el contenido de `villalobos-logistica-2-0/` a la carpeta raiz web del hosting (suele ser `public_html/`, `httpdocs/` o `www/`), EXCEPTO:

| NO subir | Razon |
|---|---|
| `.git/` | Historia git, ocupa espacio |
| `node_modules/` | Dependencias Playwright (capturas), no se usan en prod |
| `tomar_capturas.mjs` | Script de desarrollo |
| `package.json`, `package-lock.json` | Solo para Playwright local |
| `.env` (local) | Lo crearemos directamente en produccion (paso 6) |
| `Auditoria*.pdf`, `Fase*.pdf` | Docs internas |
| `capturas/` | Capturas locales para memoria |

Tip FileZilla: en el panel local, click derecho > "Excluir patron" para anadir las carpetas anteriores antes de transferir.

Tras la subida, la web debe ser accesible con la estructura tipica del hosting:
- `public/` debe ser el DocumentRoot, o configurar redirect.

### Configurar DocumentRoot a `public/`

Si el hosting permite cambiar DocumentRoot, apuntarlo a `/villalobos-logistica-2-0/public/`.

Si NO lo permite (hosting compartido basico), poner un `.htaccess` en la raiz que redirija:

`.htaccess` en la raiz:
```
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]
```

---

## 6. Crear el .env en produccion

Crear (via FTP) `.env` en la raiz del proyecto en el servidor con:

```
DB_HOST=localhost
DB_NAME=nombrecuenta_villalobos
DB_USER=nombrecuenta_villa
DB_PASS=la_contrasena_fuerte_que_generaste

SMTP_HOST=mail.villaloboslogistica.com
SMTP_USER=info@villaloboslogistica.com
SMTP_PASS=la_contrasena_smtp
SMTP_PORT=587
SMTP_FROM_EMAIL=no-reply@villaloboslogistica.com
SMTP_FROM_NAME="Web Villalobos Logistica"
SMTP_TO_EMAIL=info@villaloboslogistica.com

CORS_ORIGIN=https://www.villaloboslogistica.com
```

**Permisos del .env**: 600 (solo lectura del propietario).
Por FTP: click derecho > "Permisos del archivo" > 600.

---

## 7. Configurar HTTPS

1. En el panel del hosting buscar "Lets Encrypt" o "SSL"
2. Activar certificado para `villaloboslogistica.com` y `www.villaloboslogistica.com`
3. Activar "Redirigir HTTP a HTTPS"
4. Verificar tras 5 min: https://www.villaloboslogistica.com responde con candado verde

Si el hosting no ofrece SSL, usar Cloudflare gratuito como proxy.

---

## 8. Smoke test en produccion

Lista de verificacion tras desplegar:

| Test | URL | Resultado esperado |
|---|---|---|
| Home | https://www.villaloboslogistica.com/ | 200, carga estilos, mapa visible |
| Subpaginas | /servicios, /quienes-somos, /contacto, /galeria | 200 todos |
| Politica privacidad | /politica-privacidad | 200 |
| Aviso legal | /aviso-legal | 200 |
| Sitemap | /sitemap.xml | XML valido |
| Robots | /robots.txt | Texto valido |
| Formulario contacto | (rellenar datos validos) | "Solicitud enviada" + email recibido |
| Estimador precio | (escribir Malaga / Madrid) | Calcula km y precio aprox |
| Chat lead | (completar paso a paso) | Registra y envia email |
| Login admin | /panel/login.php (admin@villalobos.local + pass nueva) | Redirige al dashboard |
| Dashboard | /panel/dashboard.php | KPIs cargan |
| Cierre Python | (admin -> boton "Cierre de Ejercicio") | Modal con KPIs |
| Logout | /panel/logout o boton | Vuelve al login |
| Rate limit | (5 logins erroneos) | "Demasiados intentos" |
| Console errors | F12 -> Console | Sin errores rojos |

---

## 9. Validar SEO

Tras desplegar, validar en herramientas externas:

- [ ] **Schema.org validator**: https://validator.schema.org/ con la URL home
- [ ] **Rich Results Test**: https://search.google.com/test/rich-results
- [ ] **PageSpeed Insights**: https://pagespeed.web.dev/ (objetivo: >=80 mobile)
- [ ] **Mobile-Friendly Test**: https://search.google.com/test/mobile-friendly
- [ ] **HTTPS test**: https://www.ssllabs.com/ssltest/ (objetivo: A o A+)

---

## 10. Configurar Google Search Console (opcional pero recomendado)

1. Ir a https://search.google.com/search-console
2. Anadir propiedad `https://www.villaloboslogistica.com`
3. Verificar via DNS o archivo HTML
4. Subir sitemap: `https://www.villaloboslogistica.com/sitemap.xml`
5. Esperar 1-2 dias para que Google rastree

---

## 11. Backup posterior al despliegue

Una vez que todo funciona en produccion:

1. **Tag git** del estado desplegado:
   ```
   git tag -a v1.0-prod -m "Despliegue produccion 2026-05-XX"
   git push origin v1.0-prod
   ```

2. **Dump BD** semanal:
   ```
   mysqldump -u user -p nombrecuenta_villalobos > backup_villalobos_YYYY-MM-DD.sql
   ```

3. **Capturas para memoria TFG**: ejecutar `node tomar_capturas.mjs` apuntando a la URL de produccion.

---

## 12. Solucion de problemas comunes

| Sintoma | Causa probable | Fix |
|---|---|---|
| "Error de conexion a la base de datos" en JSON | `.env` mal configurado o BD no creada | Verificar credenciales y schema importado |
| Login devuelve "Credenciales incorrectas" siempre | password_hash de demo no coincide | Regenerar hash y UPDATE en SQL |
| Formulario envia pero no llega email | SMTP mal configurado | Probar SMTP con `php -r 'mail(...)';` |
| Chat se queda colgado en "Registrando..." | API contacto.php devuelve 500 | Mirar logs de error del hosting |
| Mapa Leaflet no aparece | CSP del hosting bloquea unpkg | Anadir `*.unpkg.com` a CSP o usar local |
| Cierre Python no funciona | Hosting no tiene Python o pymysql | Hostings compartidos suelen no tenerlo. Reescribir endpoint en PHP puro o pedir VPS |
| 500 al entrar a /panel/* | session_set_cookie_params en HTTP | Forzar HTTPS o quitar `secure` en .env temporal |

---

## 13. Variables `.env` - referencia completa

| Clave | Valor ejemplo | Comentario |
|---|---|---|
| DB_HOST | localhost | Casi siempre localhost en hosting compartido |
| DB_NAME | cuenta_villalobos | Prefijado por el hosting |
| DB_USER | cuenta_villa | Prefijado por el hosting |
| DB_PASS | (16+ chars) | Generar con keepass o `openssl rand -base64 16` |
| SMTP_HOST | mail.villaloboslogistica.com | Del email creado |
| SMTP_PORT | 587 | TLS recomendado |
| SMTP_USER | info@villaloboslogistica.com | Email completo |
| SMTP_PASS | (la del email) | |
| SMTP_FROM_EMAIL | no-reply@villaloboslogistica.com | Remitente visible |
| SMTP_FROM_NAME | Web Villalobos Logistica | Sin tildes para evitar problemas |
| SMTP_TO_EMAIL | info@villaloboslogistica.com | Donde llegan los formularios |
| CORS_ORIGIN | https://www.villaloboslogistica.com | Bloquea APIs cross-origin |

---

## 14. Despues del despliegue: presentacion TFG

- [ ] Hacer capturas de pantalla en produccion para la memoria
- [ ] Probar flujo completo cronometrado (~5 minutos)
- [ ] Preparar 2 navegadores en la defensa por si uno falla
- [ ] Tener una version local de respaldo en USB
- [ ] Tener un video de la demo en mp4 por si el wifi falla en clase

---

**Autor**: Antonio Capel - DAW DIGITECH 2026
**Ultima actualizacion**: 2026-05-04