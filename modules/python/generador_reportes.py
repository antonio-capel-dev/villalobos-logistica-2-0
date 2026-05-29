#!/usr/bin/env python3
"""
generador_reportes.py - Villalobos Logistica
Exporta los portes de la base de datos a un CSV.

Lee credenciales de BD desde variables de entorno (DB_HOST, DB_NAME,
DB_USER, DB_PASS) o desde el archivo .env en la raiz del proyecto.
"""

import csv
import datetime
import os
from pathlib import Path

import pymysql


def cargar_env(ruta_env: Path) -> None:
    """Carga variables del archivo .env en os.environ si no estan ya definidas."""
    if not ruta_env.is_file():
        return
    for linea in ruta_env.read_text(encoding='utf-8').splitlines():
        linea = linea.strip()
        if not linea or linea.startswith('#') or '=' not in linea:
            continue
        clave, valor = linea.split('=', 1)
        clave = clave.strip()
        valor = valor.strip().strip('"').strip("'")
        os.environ.setdefault(clave, valor)


cargar_env(Path(__file__).resolve().parents[2] / '.env')


def conectar():
    return pymysql.connect(
        host=os.environ.get('DB_HOST', 'localhost'),
        user=os.environ.get('DB_USER', 'root'),
        password=os.environ.get('DB_PASS', ''),
        database=os.environ.get('DB_NAME', 'villalobos_logistica_2'),
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor,
    )


def generar_reporte():
    print("Iniciando generador de reportes - Villalobos Logistica")

    conexion = conectar()

    try:
        with conexion.cursor() as cursor:
            cursor.execute("""
                SELECT
                    p.id,
                    p.fecha_programada,
                    p.origen,
                    p.destino,
                    p.kms,
                    p.precio,
                    p.estado,
                    COALESCE(c.nombre, 'Sin asignar') AS cliente,
                    COALESCE(d.nombre, 'Sin asignar') AS conductor
                FROM portes p
                LEFT JOIN usuarios c ON p.cliente_id  = c.id
                LEFT JOIN usuarios d ON p.conductor_id = d.id
                ORDER BY p.fecha_programada DESC
            """)
            portes = cursor.fetchall()
    finally:
        conexion.close()

    if not portes:
        print("No hay portes en la base de datos.")
        return

    fecha_hoy = datetime.date.today().strftime('%Y-%m-%d')
    nombre_fichero = f'reporte_{fecha_hoy}.csv'

    with open(nombre_fichero, mode='w', newline='', encoding='utf-8') as archivo:
        escritor = csv.DictWriter(archivo, fieldnames=portes[0].keys())
        escritor.writeheader()
        escritor.writerows(portes)

    print(f"Reporte generado: {nombre_fichero} ({len(portes)} portes)")

    resumen = {}
    for p in portes:
        estado = p['estado']
        resumen[estado] = resumen.get(estado, 0) + 1

    print("\nResumen por estado:")
    for estado, total in resumen.items():
        print(f"  {estado}: {total}")


if __name__ == "__main__":
    generar_reporte()
