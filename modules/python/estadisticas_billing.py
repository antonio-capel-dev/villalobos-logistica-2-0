#!/usr/bin/env python3
"""
estadisticas_billing.py - Villalobos Logistica

Calcula el cierre mensual y lo imprime en JSON por stdout.
Uso: python estadisticas_billing.py [YYYY-MM]

Lee credenciales de BD desde variables de entorno (DB_HOST, DB_NAME,
DB_USER, DB_PASS) o desde el archivo .env en la raiz del proyecto.
"""

import sys
import os
import json
import datetime
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


def escalar(cursor, consulta, mes):
    cursor.execute(consulta, (mes,))
    fila = cursor.fetchone()
    return list(fila.values())[0] if fila else 0


def calcular_cierre(mes):
    conexion = conectar()
    try:
        with conexion.cursor() as cur:
            total_portes = escalar(cur,
                "SELECT COUNT(*) FROM portes WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = %s",
                mes)

            ingresos = escalar(cur,
                "SELECT COALESCE(SUM(precio),0) FROM portes "
                "WHERE estado='entregado' AND DATE_FORMAT(fecha_programada,'%Y-%m') = %s",
                mes)

            km_totales = escalar(cur,
                "SELECT COALESCE(SUM(kms),0) FROM portes "
                "WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = %s",
                mes)

            media_porte = escalar(cur,
                "SELECT COALESCE(AVG(precio),0) FROM portes "
                "WHERE estado='entregado' AND DATE_FORMAT(fecha_programada,'%Y-%m') = %s",
                mes)

            cur.execute(
                "SELECT u.nombre, COUNT(*) AS total FROM portes p "
                "JOIN usuarios u ON p.conductor_id = u.id "
                "WHERE DATE_FORMAT(p.fecha_programada,'%Y-%m') = %s "
                "GROUP BY p.conductor_id ORDER BY total DESC LIMIT 1",
                (mes,))
            conductor = cur.fetchone()
            nombre_conductor = conductor['nombre'] if conductor else 'Sin datos'
            portes_conductor = conductor['total']  if conductor else 0

            cur.execute(
                "SELECT estado, COUNT(*) AS total FROM portes "
                "WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = %s GROUP BY estado",
                (mes,))
            portes_por_estado = {fila['estado']: fila['total'] for fila in cur.fetchall()}

    finally:
        conexion.close()

    return {
        'ok':               True,
        'mes':              mes,
        'total_portes':     int(total_portes),
        'ingresos_mes':     round(float(ingresos), 2),
        'km_totales':       round(float(km_totales), 1),
        'media_porte':      round(float(media_porte), 2),
        'conductor_top':    nombre_conductor,
        'portes_conductor': int(portes_conductor),
        'por_estado':       portes_por_estado,
    }


if __name__ == '__main__':
    mes = sys.argv[1] if len(sys.argv) > 1 else datetime.date.today().strftime('%Y-%m')
    try:
        resultado = calcular_cierre(mes)
    except Exception as error:
        resultado = {'ok': False, 'error': str(error)}
        print(json.dumps(resultado, ensure_ascii=False, indent=2))
        sys.exit(1)
    print(json.dumps(resultado, ensure_ascii=False, indent=2))