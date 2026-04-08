@echo off
REM ============================================================
REM compilar.bat  —  Villalobos Logística TFG  (Windows)
REM ============================================================
set LIBS=libs\mysql-connector-j.jar
set SRC=EstadisticasBilling.java
set JAR=EstadisticasBilling.jar

if not exist "%LIBS%" (
    echo ERROR: No se encuentra %LIBS%
    echo Descargalo de https://dev.mysql.com/downloads/connector/j/
    pause & exit /b 1
)

echo Compilando %SRC%...
javac -cp "%LIBS%" "%SRC%"
if errorlevel 1 ( echo ERROR: Fallo la compilacion. & pause & exit /b 1 )

echo Generando %JAR%...
jar cfe "%JAR%" EstadisticasBilling *.class

echo Listo! JAR generado: modules\java\%JAR%
pause
