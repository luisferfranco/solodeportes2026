# La Tabla

En esta página vas a crear una tabla que indique quién le fue a qué equipo y qué resultado obtuvo, de acuerdo al evento y ronda que esté seleccionado.

Utiliza el mismo patrón que las demás vistas, por ejemplo fa.qn.pronosticos.

La tabla se verá de la siguiente forma

Participaciones | Juego-1 | Juego-2 | Juego-3 | ...
Nombre part-1   | Equipo  | Equipo  | Equipo  | ...
Nombre part-2   | Equipo  | Equipo  | Equipo  | ...
Nombre part-3   | Equipo  | Equipo  | Equipo  | ...

- En Juego-n deberá haber una celda donde tenga dos renglones, el primero, con el logo del equipo visitante, y el segundo con el del equipo local
- En "Nombre part-1" el nombre de la participación
- En cada celda de "Equipo" se deberá poner el logotipo del equipo a quién le fue la participación.
- Si el partido aún no comienza (columna `valido_hasta` de `equipos`) se deberá poner "???" centrado en la columna.
- Si el partido ya inició se deberá poner el logotipo de a quién le fue, bajo un fondo neutral
- Si el partido ya está calificado, se deberá poner, en verde si la participación le atinó al ganador y a la diferencia; en amarillo si le atinó al ganador; en rojo si falló.
- La primera columna debe quedar fija
- El ancho de las columnas con los logos de los equipos debe ser el mismo para todas

## Tablas y columnas relevantes

participaciones
- nombre .......... Nombre de la participación
- evento_id

eventos
- id
- temporada_id .... Usar la temporada para obtener los juegos relevantes

juegos
- id
- temporada_id .... Utilizar estos juegos para hacer la tabla
- home_id ......... Identificación de los equipos local y visitante, para obtener sus logos
- away_id

equipos
- id
- logo

pronosticos
- id
- participacion_id
- juego_id
- diferencia ....... Indica a quién le fue el jugador, positivos: local, negativos: visitante
- res .............. Indica si el usuario le atinó al ganador
- dif .............. Indica si el usuario le atinó a la diferencia

Por tanto res+dif:
> 1 ... acierto total, verde
> 0 ... acierto parcial, amarillo
= 0 ... fallo total, rojo
null .. desconocido, neutral