# Checklist final - Villalobos Logistica 2.0

Lista accionable de lo que **TU** tienes que hacer para llegar al despliegue (esta semana) y a la defensa (junio 2026).

---

## A. TU PROXIMA SESION (1-2 horas)

### A1. Verificar que el proyecto arranca

- [ ] Abrir XAMPP Control Panel y arrancar Apache + MySQL
- [ ] Crear BD si no existe: `villalobos_logistica_2`
- [ ] Importar `database/schema.sql` desde phpMyAdmin
- [ ] Abrir `http://localhost/villalobos-logistica-2-0/public/index.html`
- [ ] Verificar que carga la home con CSS y mapa
- [ ] Probar el formulario de contacto con datos validos
- [ ] Probar el chatbot completo
- [ ] Login en `/panel/login.php` con `admin@villalobos.local` / `123456`
- [ ] Click en boton "Cierre de Ejercicio" del dashboard, verificar modal con KPIs

### A2. Si algo falla

- Revisar consola del navegador (F12) para errores JS
- Revisar logs de Apache: `C:\xampp\apache\logs\error.log`
- Revisar PHP error log si esta activado en php.ini

---

## B. ESTA SEMANA - DESPLIEGUE (3-5 horas)

Sigue paso a paso `DEPLOY.md`. Resumen ejecutivo:

### B1. Preparativos hosting

- [ ] Conseguir credenciales FTP/SFTP del Professional Hosting de Lola
- [ ] Conseguir credenciales del panel de hosting (cPanel/Plesk)
- [ ] Crear cuenta de email `info@villaloboslogistica.com` en el panel

### B2. Base de datos remota

- [ ] Crear BD MySQL en el panel del hosting
- [ ] Importar `database/schema.sql` via phpMyAdmin remoto
- [ ] Anotar credenciales: host, BD, user, pass
- [ ] Cambiar passwords de los 4 usuarios demo (generar hashes con bcrypt)

### B3. Subida de codigo

- [ ] Conectar SFTP (FileZilla recomendado)
- [ ] Subir TODO excepto: `.git`, `node_modules`, `tomar_capturas.mjs`, `package*.json`, `capturas/`, `*.pdf`
- [ ] Verificar que la web responde en el dominio

### B4. Configuracion produccion

- [ ] Crear `.env` en el servidor con credenciales reales (NO subirlo desde local)
- [ ] Cambiar permisos de `.env` a 600
- [ ] Verificar que `CORS_ORIGIN` apunta al dominio real
- [ ] Activar SSL Let's Encrypt en el panel
- [ ] Forzar HTTPS

### B5. Smoke test produccion

- [ ] Home carga, candado verde
- [ ] Formulario envia y recibes email real
- [ ] Chatbot funciona end-to-end
- [ ] Login admin funciona
- [ ] Cierre de Ejercicio funciona (si el hosting tiene Python)
- [ ] Lighthouse mobile >=80 en las cuatro categorias

### B6. SEO

- [ ] Subir sitemap.xml a Google Search Console
- [ ] Validar JSON-LD en https://validator.schema.org/
- [ ] Validar Rich Results en https://search.google.com/test/rich-results
- [ ] PageSpeed Insights https://pagespeed.web.dev/
- [ ] Mobile-friendly test

### B7. Backup

- [ ] Tag git `v1.0-prod` y push del tag
- [ ] Dump BD remota a archivo SQL
- [ ] Capturas de pantalla de todo el flujo (para memoria)

---

## C. JUNIO 2026 - PREPARACION DEFENSA (10-15 horas)

### C1. Memoria TFG

- [ ] Abrir `Memoria_TFG_Villalobos_Logistica.docx` (recien generada, 52KB)
- [ ] Anadir capturas de pantalla en los anexos
- [ ] Anadir el diagrama E-R (`0Proyecto-villalobos/diagrama.png`)
- [ ] Pegar el manual de identidad (`Manual_Identidad_Marca_Villalobos.html`) maquetado
- [ ] Revisar y ajustar tono/estilo a tu voz
- [ ] Anadir indice automatico de Word
- [ ] Generar PDF final para entregar

### C2. Slides de defensa (15 diapositivas)

Sugerencia de estructura (5 min explicacion + 10 min demo):

1. Portada
2. Empresa cliente (foto + sector + cifras)
3. Problema (web obsoleta + presupuestos externos)
4. Mi solucion (vision general + valor anadido)
5. Briefings reales (foto del briefing)
6. Arquitectura (3 capas)
7. Diagrama E-R
8. Demo en vivo (iniciar aqui)
9. Stack tecnico y por que
10. Modulo Python diferenciador
11. Seguridad y RGPD aplicados
12. SEO on-page
13. Lighthouse score (captura)
14. Lecciones aprendidas
15. Preguntas

### C3. Plan de respaldo para la defensa

- [ ] Grabar video MP4 de la demo completa por si falla wifi
- [ ] Tener version local en USB
- [ ] Tener web en localhost arrancada antes de empezar
- [ ] Llevar pendrive con el .docx y el .pdf de la memoria

### C4. Preguntas tipicas del tribunal

Preparar respuestas concretas a:

1. ¿Por que PHP y no Node.js / Python framework?
2. ¿Por que Java se quedo fuera? (decision documentada)
3. ¿Como gestionas las credenciales y secretos?
4. ¿Como prevenir SQL Injection y XSS?
5. ¿Que pasa si el cliente quiere multi-idioma?
6. ¿Como escala a mas usuarios?
7. ¿Has usado IA? (responder honestamente: si, como herramienta)
8. ¿Que mejorarias del proyecto?
9. ¿Como mantienes el proyecto a futuro?
10. ¿Cuanto tiempo tomarias en formar a un desarrollador junior para mantenerlo?

---

## D. ULTIMOS DETALLES OPCIONALES (si tienes tiempo)

- [ ] Minificar CSS final (https://cssminifier.com)
- [ ] Comprimir hero-bg.jpg a WebP
- [ ] Subset Font Awesome con solo los iconos usados
- [ ] Capacitor para version movil hibrida
- [ ] WhatsApp Business API integrada
- [ ] Tests Playwright basicos (smoke tests)

---

## E. ARCHIVOS CLAVE QUE TIENES YA

- `README.md` - Documentacion tecnica del proyecto
- `DEPLOY.md` - Guia paso a paso de despliegue (RECIEN CREADA)
- `CHECKLIST_FINAL.md` - Este documento
- `Memoria_TFG_Villalobos_Logistica.docx` - Memoria de 52KB ampliable (RECIEN GENERADA)
- `.env.example` - Plantilla de variables de entorno (sin credenciales reales)
- `database/schema.sql` - DDL + datos demo

---

## F. CONTACTOS UTILES

- **Director TFG**: Santi
- **Cliente**: Lola (hermana del autor) - 681 87 5243
- **Conductor (WhatsApp del boton)**: Rafa - 625 038 039
- **Email empresa**: info@villaloboslogistica.com

---

**Ultima actualizacion**: 2026-05-04 - Tras la auditoria + recuperacion del proyecto.
