# modules/python/generador_reportes.py
# Genera un CSV con los portes reales de la base de datos.
# Elemento diferenciador del TFG: módulo Python conectado a MySQL.

import csv
import datetime
import pymysql

# --- CONFIGURACIÓN DE LA BASE DE DATOS ---
# Mismos datos que conexion.php
DB_HOST = 'localhost'
DB_NAME = 'villalobos_logistica_2'
DB_USER = 'root'
DB_PASS = ''

def conectar():
    """Abre y devuelve la conexión a MySQL."""
    return pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor  # devuelve filas como diccionarios
    )

def generar_reporte():
    print("Iniciando generador de reportes — Villalobos Logística")

    conexion = conectar()

    try:
        with conexion.cursor() as cursor:
            # Consulta real: portes con nombre de cliente y conductor
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

    # Nombre del fichero con la fecha de hoy
    fecha_hoy = datetime.date.today().strftime('%Y-%m-%d')
    nombre_fichero = f'reporte_{fecha_hoy}.csv'

    # Escribir el CSV
    with open(nombre_fichero, mode='w', newline='', encoding='utf-8') as archivo:
        escritor = csv.DictWriter(archivo, fieldnames=portes[0].keys())
        escritor.writeheader()    # escribe la fila de cabeceras
        escritor.writerows(portes) # escribe todas las filas de datos

    print(f"Reporte generado: {nombre_fichero}")
    print(f"Total de portes exportados: {len(portes)}")

    # Mostrar resumen por estado
    resumen = {}
    for p in portes:
        estado = p['estado']
        resumen[estado] = resumen.get(estado, 0) + 1

    print("\nResumen por estado:")
    for estado, total in resumen.items():
        print(f"  {estado}: {total}")

if __name__ == "__main__":
    generar_reporte()
