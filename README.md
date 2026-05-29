# Villalobos Logística 2.0 - Proyecto TFG

Este es el repositorio del proyecto integrador para el ciclo de Desarrollo de Aplicaciones Web (DAW) en Digitech Málaga. El proyecto consiste en una aplicación de gestión para una empresa real de transporte y logística malagueña.

## Resumen del Proyecto
La aplicación cubre tanto la parte pública (captación de clientes y presupuestos) como una zona privada para la gestión de portes, conductores y estadísticas de facturación.

*   **Entorno:** XAMPP (Apache + MySQL) + Python 3.x
*   **Backend:** PHP 8.0 (PDO)
*   **Frontend:** HTML5, CSS3 y JS Vanilla (sin frameworks pesados)
*   **Módulos especiales:** Integración con Python para cálculo de rutas y reportes de rentabilidad.

---

## Instalación y Configuración

### 1. Requisitos previos
*   XAMPP con PHP 8 o superior.
*   Python instalado (para los módulos de estadísticas y distancias).

### 2. Base de datos
1.  Entra en `phpMyAdmin` y crea una base de datos llamada `villalobos_logistica_2`.
2.  Importa el archivo que encontrarás en `database/schema.sql`.

### 3. Archivos y permisos
1.  Clona el repositorio en tu carpeta `htdocs`.
2.  Copia el archivo `.env.example` y renómbralo a `.env`.
3.  Configura tus credenciales de base de datos y SMTP en ese `.env`.

### 4. Módulos de Python
Es necesario instalar `pymysql` para que los scripts de reportes puedan conectar con la base de datos:
```bash
pip install pymysql
```

---

## Estructura del Repositorio

*   `/public`: Contiene la web pública (index, servicios, contacto, etc.).
*   `/panel`: Zona privada para administración y gestión de portes.
*   `/backend`: Lógica de servidor, conexión PDO y endpoints de la API.
*   `/database`: Script SQL de la estructura y datos de prueba.
*   `/modules/python`: Scripts para el cálculo de distancias y cierre de ejercicio.
*   `/assets`: Estilos CSS, imágenes reales del cliente y utilidades JS.

---

## Funcionalidades Clave

*   **Estimador de presupuesto:** Calcula kilómetros y precio aproximado usando la API de Nominatim (vía Python) al introducir origen y destino.
*   **Chat interactivo:** Un asistente guiado para que el cliente deje sus datos de contacto de forma dinámica.
*   **Panel de Gestión:** Dashboard con KPIs, control de roles (Admin, Editor, Conductor, Cliente) y CRUD de portes.
*   **Cierre de Ejercicio:** Módulo en Python que analiza la rentabilidad mensual y genera informes.
*   **SEO y Accesibilidad:** Implementación de Schema.org, OpenGraph y semántica HTML5 correcta.

---

## Notas de Desarrollo
Se ha priorizado el uso de JavaScript nativo (Vanilla) para las validaciones y el manejo de la API, evitando dependencias externas innecesarias para garantizar la velocidad de carga. Las contraseñas en la base de datos están encriptadas con `password_hash`.

**Autor:** Antonio Capel
**Curso:** 2025/2026 - DAW Digitech Málaga
