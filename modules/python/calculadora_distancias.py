#!/usr/bin/env python3
"""
calculadora_distancias.py — Villalobos Logística
Geocodifica origen/destino con Nominatim y estima distancia y precio.
Uso: python3 calculadora_distancias.py "Málaga" "Madrid"
"""

import sys
import json
import math
import time
import urllib.request
import urllib.parse

PRECIO_KM   = 1.25
PRECIO_MIN  = 80.00
FACTOR_ROAD = 1.30


def geocodificar(lugar: str) -> tuple[float, float]:
    query = urllib.parse.quote(lugar + ", España")
    url   = (
        "https://nominatim.openstreetmap.org/search"
        f"?q={query}&format=json&limit=1&accept-language=es"
    )
    req = urllib.request.Request(
        url,
        headers={"User-Agent": "VillalobosLogistica-TFG/2.0 (tfg@villalobos.local)"}
    )
    with urllib.request.urlopen(req, timeout=8) as resp:
        datos = json.loads(resp.read().decode())

    if not datos:
        raise ValueError(f"No se encontró la localización: «{lugar}»")

    return float(datos[0]["lat"]), float(datos[0]["lon"])


def haversine(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
    R = 6371.0
    phi1, phi2 = math.radians(lat1), math.radians(lat2)
    d_phi = math.radians(lat2 - lat1)
    d_lam = math.radians(lon2 - lon1)
    a = math.sin(d_phi / 2) ** 2 + math.cos(phi1) * math.cos(phi2) * math.sin(d_lam / 2) ** 2
    return R * 2 * math.asin(math.sqrt(a))


def calcular(origen: str, destino: str) -> dict:
    lat1, lon1 = geocodificar(origen)
    time.sleep(0.3)  # rate-limit Nominatim
    lat2, lon2 = geocodificar(destino)

    km_carretera = round(haversine(lat1, lon1, lat2, lon2) * FACTOR_ROAD, 1)
    precio       = round(max(PRECIO_MIN, km_carretera * PRECIO_KM), 2)

    return {
        "ok":              True,
        "origen":          origen,
        "destino":         destino,
        "km":              km_carretera,
        "precio_estimado": precio,
        "nota":            "Precio orientativo. El presupuesto final puede variar según peso y tipo de carga."
    }


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"ok": False, "error": "Uso: calculadora_distancias.py <origen> <destino>"}, ensure_ascii=False))
        sys.exit(1)

    try:
        resultado = calcular(sys.argv[1].strip(), sys.argv[2].strip())
        print(json.dumps(resultado, ensure_ascii=False))
    except Exception as exc:
        print(json.dumps({"ok": False, "error": str(exc)}, ensure_ascii=False))
        sys.exit(1)
