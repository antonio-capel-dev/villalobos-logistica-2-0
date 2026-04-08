#!/usr/bin/env python3
"""
generador_reportes.py — Villalobos Logística
Exporta los portes de la base de datos a un CSV.
"""

import csv
import datetime
import pymysql

DB_HOST = 'localhost'
DB_NAME = 'villalobos_logistica_2'
DB_USER = 'root'
DB_PASS = ''


def conectar():
    return pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )


def generar_reporte():
    print("Iniciando generador de reportes — Villalobos Logística")

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

    fecha_hoy     = datetime.date.today().strftime('%Y-%m-%d')
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
