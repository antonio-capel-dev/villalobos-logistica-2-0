#!/bin/bash
# ============================================================
# compilar.sh  —  Villalobos Logística TFG
# Compila EstadisticasBilling.java y genera el JAR ejecutable.
# ============================================================
# REQUISITO PREVIO:
#   Descarga el conector MySQL para Java desde:
#   https://dev.mysql.com/downloads/connector/j/
#   Elige "Platform Independent" → ZIP, extrae el .jar y
#   cópialo a:  modules/java/libs/mysql-connector-j.jar
# ============================================================

LIBS="libs/mysql-connector-j.jar"
SRC="EstadisticasBilling.java"
JAR="EstadisticasBilling.jar"

if [ ! -f "$LIBS" ]; then
  echo "ERROR: No se encuentra $LIBS"
  echo "Descárgalo de https://dev.mysql.com/downloads/connector/j/"
  exit 1
fi

echo "Compilando $SRC..."
javac -cp "$LIBS" "$SRC"

if [ $? -ne 0 ]; then
  echo "ERROR: Falló la compilación."
  exit 1
fi

echo "Generando $JAR..."
jar cfe "$JAR" EstadisticasBilling *.class

echo "¡Listo! JAR generado: modules/java/$JAR"
echo "Prueba manual: java -cp .:$LIBS EstadisticasBilling"
