En esta página vas a generar el "leaderboard" para el survivor.

# Entrada
Se recibe como parámetro el `$evento` para ver los resultados hasta la ronda que se determine, ya sea obteniendo `$ronda` desde la base de datos, o en el parámetro del GET en `rd`.

Este código inicial ya está en el archivo.

# Resultado esperado
Se deberá presentar un grid con los equipos que se seleccionaron para la ronda, y las participaciones que jugaron con dicho equipo. La fuente del grid es la siguiente consulta:

```sql
SELECT equipo_id, participaciones.nombre
FROM survivors, participaciones
WHERE survivors.participacion_id = participaciones.id
  AND participacion_id IN (
    SELECT id FROM participaciones WHERE evento_id = 4
  )
ORDER BY equipo_id
```

El grid deberá presentarse de la siguiente forma:

|equipo_1 (nombre y logo)|equipo_2 (nombre y logo)|equipo_3 (nombre y logo)|
|------------------------|------------------------|------------------------|
|nombre1                 | nombre12               | nombre24               |
|nombre2                 | nombre13               | nombre25               |
|                        | nombre14               | nombre26               |
|                        | nombre15               |                        |

El grid deberá tener los siguientes estados. La "fecha-inicio" corresponde a la fecha y hora mínimos de los juegos que conforman la ronda del evento en juego, es decir:

```
SELECT min(valido_hasta)
FROM juegos
WHERE juegos.temporada_id = $evento->temporada_id
  AND ronda = $ronda
```

- Si la fecha now() es menor que la fecha-inicio, entonces se desplegará únicamente el número de pronósticos para este equipo (no se conocerá el nombre de la participación)
- Si la fecha now() es mayor que la fecha-inicio, se desplegarán los nombres de las participaciones que seleccionaron a ese equipo
- El color del fondo del equipo dependerá del resultado, para obtenerlo, se puede leer cualquier registro de la participación con el campo "acierto"
  - Si es verdadero, fondo success
  - Si es falso, fondo error
  - Si es nulo, fondo neutral