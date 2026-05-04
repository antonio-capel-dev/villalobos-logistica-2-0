/**
 * tomar_capturas.mjs — Villalobos Logística
 * Capturas automáticas para la Memoria TFG.
 * Ejecutar: node tomar_capturas.mjs  (con XAMPP corriendo)
 */

import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const BASE   = 'http://localhost/0Proyecto-villalobos/villalobos-logistica-2-0/public';
const PANEL  = 'http://localhost/0Proyecto-villalobos/villalobos-logistica-2-0/panel';
const SALIDA = path.join(__dirname, 'capturas');

if (!fs.existsSync(SALIDA)) fs.mkdirSync(SALIDA);

let ok = 0, fail = 0;

const esperar = ms => new Promise(r => setTimeout(r, ms));

async function captura(page, nombre, desc) {
    try {
        await page.screenshot({ path: path.join(SALIDA, nombre), fullPage: true });
        console.log(`  ✓ ${desc}`);
        ok++;
    } catch(e) {
        console.log(`  ✗ ${desc}: ${e.message.split('\n')[0].slice(0, 80)}`);
        fail++;
    }
}

async function ir(page, url) {
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await esperar(1200);
}

(async () => {
    console.log('\n🚛 Villalobos Logística — Capturas para Memoria TFG');
    console.log('='.repeat(55));

    const browser = await chromium.launch({ headless: false, slowMo: 150 });
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, locale: 'es-ES' });
    const page = await ctx.newPage();

    // ── 1. INICIO ────────────────────────────────────────────────
    console.log('\n[1] Página principal...');
    await ir(page, BASE + '/index.html');
    await captura(page, '01_inicio_hero.png', 'Inicio — Hero section');

    await page.evaluate(() => document.querySelector('.services, #servicios, [class*="service"]')?.scrollIntoView({ behavior: 'instant' }));
    await esperar(500);
    await captura(page, '02_inicio_servicios.png', 'Inicio — Sección servicios');

    await page.evaluate(() => document.querySelector('[class*="review"], [class*="testimon"]')?.scrollIntoView({ behavior: 'instant' }));
    await esperar(500);
    await captura(page, '03_inicio_reviews.png', 'Inicio — Reseñas Google');

    // ── 2. SUBPÁGINAS ─────────────────────────────────────────────
    console.log('\n[2] Subpáginas...');
    await ir(page, BASE + '/servicios.html');
    await captura(page, '04_servicios.png', 'Servicios de transporte y almacenaje');

    await ir(page, BASE + '/quienes-somos.html');
    await captura(page, '05_quienes_somos.png', 'Quiénes somos — Trayectoria');

    await ir(page, BASE + '/galeria.html');
    await captura(page, '06_galeria.png', 'Galería de la flota');

    // ── 3. CONTACTO ───────────────────────────────────────────────
    console.log('\n[3] Formulario de contacto...');
    await ir(page, BASE + '/contacto.html');
    await captura(page, '07_contacto_vacio.png', 'Formulario de contacto — vacío');

    try {
        await page.fill('#nombre', 'María García', { timeout: 5000 });
        await page.fill('#correo', 'maria@constructora.es', { timeout: 5000 });
        await page.fill('#detalleMensaje', 'Necesito transportar 3 palets desde Málaga a Sevilla, peso 800 kg aprox.', { timeout: 5000 });
        await esperar(400);
        await captura(page, '08_contacto_relleno.png', 'Formulario de contacto — datos introducidos');
    } catch(e) { console.log(`  ✗ Contacto form: ${e.message.split('\n')[0]}`); fail++; }

    // ── 4. MAPA ───────────────────────────────────────────────────
    console.log('\n[4] Mapa interactivo...');
    await ir(page, BASE + '/contacto.html');
    try {
        await page.evaluate(() => document.getElementById('mapa')?.scrollIntoView({ behavior: 'instant' }));
        await esperar(2000);
        await captura(page, '09_mapa_leaflet.png', 'Mapa Leaflet — Polígono Guadalhorce');
    } catch(e) { console.log(`  ✗ Mapa: ${e.message.split('\n')[0]}`); fail++; }

    // ── 5. CHAT ───────────────────────────────────────────────────
    console.log('\n[5] Chat widget...');
    await ir(page, BASE + '/index.html');
    try {
        await page.click('#chatBoton', { timeout: 5000 });
        await esperar(2500);
        await captura(page, '10_chat_saludo.png', 'Asistente virtual — Saludo inicial');

        await page.fill('#chatInput', 'Carlos López');
        await page.click('#chatEnviar');
        await esperar(2800);
        await captura(page, '11_chat_servicio.png', 'Asistente virtual — Chips de servicio');

        const chips = await page.$$('.chat-chip');
        if (chips.length > 0) {
            await chips[0].click();
            await esperar(2500);
            await captura(page, '12_chat_ruta.png', 'Asistente virtual — Pregunta de ruta');
        }
    } catch(e) { console.log(`  ✗ Chat: ${e.message.split('\n')[0]}`); fail++; }

    // ── 6. PANEL — LOGIN ──────────────────────────────────────────
    console.log('\n[6] Panel privado...');
    await ir(page, PANEL + '/login.php');
    await captura(page, '13_login.png', 'Panel privado — Formulario de login');

    try {
        const emailInput = await page.$('input[type="email"], input[name="email"], #email, input[name="username"]');
        const passInput  = await page.$('input[type="password"]');

        if (emailInput && passInput) {
            await emailInput.fill('admin@villalobos.local');
            await passInput.fill('123456');
            await esperar(300);
            await captura(page, '14_login_datos.png', 'Panel privado — Credenciales introducidas');

            await page.click('button[type="submit"], input[type="submit"]');
            await esperar(2500);
            await captura(page, '15_dashboard.png', 'Dashboard — KPIs en tiempo real');

            // ── 7. PORTES ──────────────────────────────────────────
            console.log('\n[7] Portes y mensajes...');
            await ir(page, PANEL + '/portes.php');
            await captura(page, '16_portes.png', 'Panel — Lista de portes (15 registros)');

            // Intentar abrir modal nuevo porte
            try {
                const btnNuevo = await page.$('button:has-text("Nuevo"), #btnNuevoPorte, [data-bs-toggle="modal"]');
                if (btnNuevo) {
                    await btnNuevo.click();
                    await esperar(800);
                    await captura(page, '17_portes_modal.png', 'Panel — Modal nuevo porte');
                }
            } catch(e2) { /* ok si no hay modal */ }

            await ir(page, PANEL + '/mensajes.php');
            await captura(page, '18_mensajes.png', 'Panel — Bandeja de mensajes');

            // ── 8. CIERRE DE EJERCICIO ─────────────────────────────
            console.log('\n[8] Módulo Python — Cierre de ejercicio...');
            await ir(page, PANEL + '/dashboard.php');
            try {
                const btnCierre = await page.$('#btnCierreEjercicio');
                if (btnCierre) {
                    await btnCierre.click();
                    await esperar(4000); // Esperar al módulo Python
                    await captura(page, '19_modulo_python.png', 'Módulo Python — Estadísticas de cierre mensual');
                }
            } catch(e2) { console.log('  ✗ Módulo Python: ' + e2.message.split('\n')[0]); }

        } else {
            console.log('  ✗ Campos de login no encontrados');
            fail++;
        }
    } catch(e) { console.log(`  ✗ Panel: ${e.message.split('\n')[0]}`); fail++; }

    // ── 9. RESPONSIVE MÓVIL ───────────────────────────────────────
    console.log('\n[9] Vista móvil...');
    const movil = await ctx.newPage();
    await movil.setViewportSize({ width: 390, height: 844 });
    try {
        await movil.goto(BASE + '/index.html', { waitUntil: 'domcontentloaded', timeout: 10000 });
        await esperar(1000);
        await movil.screenshot({ path: path.join(SALIDA, '20_movil_inicio.png') });
        console.log('  ✓ Inicio en móvil (390×844)');
        ok++;

        await movil.goto(BASE + '/contacto.html', { waitUntil: 'domcontentloaded', timeout: 10000 });
        await esperar(800);
        await movil.screenshot({ path: path.join(SALIDA, '21_movil_contacto.png') });
        console.log('  ✓ Contacto en móvil (390×844)');
        ok++;
    } catch(e) { console.log(`  ✗ Móvil: ${e.message.split('\n')[0]}`); fail++; }
    await movil.close();

    await browser.close();

    // ── RESUMEN ───────────────────────────────────────────────────
    const archivos = fs.readdirSync(SALIDA).filter(f => f.endsWith('.png')).sort();
    console.log('\n' + '='.repeat(55));
    console.log(`\n✅ ${archivos.length} capturas guardadas en /capturas/:`);
    archivos.forEach(f => console.log('   · ' + f));
    console.log(`\n   OK: ${ok}  |  Errores: ${fail}`);
    console.log('\n📋 Inserta las capturas en Memoria_TFG_Villalobos_Logistica.docx\n');
})();