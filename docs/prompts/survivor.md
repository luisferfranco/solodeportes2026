Me vas a ayudar a programar un juego de Survivor para deportes.

Las reglas del survivor es que un usuario puede seleccionar un equipo que piensa que va a ganar en una cierta ronda. Si el equipo gana, pasa a la siguiente semana, si empata o pierde, muere.

Para controlar el survivor existe una tabla `survivors` accesible por el modelo Survivor. Tiene los siguientes campos:

id
participacion_id: Boleto del usuario
ronda           : Ronda de selección
equipo_id       : Equipo seleccionado
acierto         : 1: Verdadero, 0: falso

Vas a crear la lógica basado en el componente Livewire pages::fa.sr.pronosticos

- Desplegarás los partidos que se van a jugar (ya hay una tabla como ejemplo y un componente para desplegar el equipo)
- Deberás calcular los equipos que ya se jugaron hasta la ronda en la que nos encontremos. Deberás marcar estos equipos con bg-success/50 si fueron aciertos o bg-error/50 si fue un fallo.
- Deberás desplegar los juegos que ya jugó el jugador
- El estado del jugador es vivo, si se acertaron todos los equipos de las rondas anteriores.
- Deberás permitir seleccionar un equipo que no se haya seleccionado previamente a esta ronda, solo si el jugador está vivo
- Deberás mostrar el estado del jugador (vivo o muerto) como una alerta justo antes de la zona de selección
- El equipo seleccionado se puede cambiar tantas veces como quiera el jugador, siempre y cuando no se haya excedido la hora del valido_hasta del juego en cuestión
- No se pueden seleccionar rondas futuras, únicamente la ronda indicada en juego->temporada->ronda
- Una vez que el jugador ha elegido a su equipo esta semana, se deberá marcar con bg-info y guardar su selección mediante el modelo Survivor

No debes preocuparte por la calificación de los resultados, eso se hará posteriormente en otro lado.