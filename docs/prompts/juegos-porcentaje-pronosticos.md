# Información adicional sobre los juegos en el pronóstico

En la ruta /resources/views/pages/fa/qn/pronosticos.blade.php se encuentra la ruta para que los jugadores pronostiquen. Se usa el componente fa-pronostico-juego.blade.php

- El modelo Juego cuenta con los datos generales del juego.
- Los jugadores pronostican vía el modelo Participacion que tiene una relación M:1 a User
- El pronóstico se guarda en el modelo Pronostico, en el campo "diferencia", si la diferencia es positiva, significa que la apuesta es al local, si es negariva, es al visitante

# Requerimiento

Se requiere poner en el fa-pronostico-juego.blade.php de cada lado (visitante y local) el porcentaje de pronósticos a favor de cada equipo. Obviamente se debe prever cuando no haya pronósticos