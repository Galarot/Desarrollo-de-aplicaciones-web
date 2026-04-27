# Especificación Técnica: Juego estilo Wordle

## 1. Introducción
El documento que se describe a continuación describe aquellas especificaciones técnicas destinadas al desarrollo de una página web orientada al tipo de juego Wordle, con temática del juego Dragon Ball Legends, la finalidad y funcionalidad básica de este se centra en adivinar el personaje como objetivo diario, con información de atributos varios como afinidad o estilo de combate.

En este caso no existe competencia alguna pues no se encuentra en la web una página de la temática dle orientada a tal juego, por ende tiene gran capacidad de diferenciarse, ademas de que tiene como objetivo breves momentos de entretenimiento y desafío diario a aquellos fanáticos de este, lo que en varios casos asegure una constancia y en consecuencia visitas diarias a la página.

## 2. Objetivos
* **Crear una experiencia diaria e interactiva** para la comunidad de Dragon Ball Legends.

* **Desarrollar barra de búsqueda con auto completado** de todos los personajes existentes en el juego (mas de 700).

* **Guardar los datos mediante sistema de guardado en navegador** para el progreso diario, es decir, si el usuario se vuelve a meter en la web tras adivinar el personaje diario, tras cerrar la página, debería aparecer que ya había adivinado el personaje de hoy.

* **Crear un sistema de racha** en la que cada día que pase y adivine el personaje diario de forma seguida, se sume +1 a la racha diaria.

* **Apartado en el que describa último día de actualización de la web** pues el juego se actualiza con nuevos personajes o actualización a personajes de forma semanal, por lo que en lo que se añaden estos a la base de datos, señalar último día de actualización para que el usuario se cerciore de las características de los personajes en aquel entonces.

* **Seleccionar las categorías necesarias** para la adivinanza del personaje, pues hay una cantidad abrumadora de características, categorías y aspectos que pueda tener un personaje, por ende se han de escoger con cuidado estas mismas, siendo las justas y necesarias para que se pueda adivinar el personaje, por ejemplo: genero, afinidad, rareza, estilo de pelea, episodio, etiqueta de raza y año.

* **El personaje diario** se elige de manera aleatoria entre todos los que hay.

## 3. Mecánica y atributos
La mecánica principal de la web consiste en lo siguiente:
El usuario selecciona escribe un personaje en la barra de búsqueda, en esta se muestran según vaya escribiendo gracias al auto completado de la misma, tras seleccionar el primer personaje, se muestran todas las características que este tiene y un color en cada uno que indica si el personaje del día coincide (verde) o no (rojo) con el seleccionado, por ende el usuario va buscando coincidencias hasta llegar al personaje del día.

Ejemplo de atributos:
**Atributos**   **Personaje ej: Bardock**
**Genero**      Hombre
**Afinidad**    Rojo
**Rareza**      Sparking
**Estilo**      Físico
**Episodio**    Original del anime
**Raza**        Saiyan
**Año**         2020
Además de todos los atributos del personaje, siempre saldrá al lado de este el art cart del personaje seleccionado en cuestión.

## 4. Arquitectura del sistema
Se opta por una aplicacion tipo SPA, siento Single Page Application, pues esencialmente no requiere mas de esto siendo un juego de navegador tipo Wordle.
* **Modelo (Data):** un json que actúa como la fuente y de hecho la única fuente de datos.

* **Vista (DOM):** HTML5 que se va modificando dinámicamente con javascript.

* **Vista (JS):** script que gestioa el flujo del juego: entrada del usuario, validación, actualización de la vista.

## 5. Tecnologías Propuestas
El lenguaje base a usar donde se pondrá toda la lógica del programa, cargando JSON y organización de código.
Estructura visual con HTML5, organizando estructura que llega a ver el usuario.
CSS3 para mayor calidad visual en aspectos varios y estilización de gran parte (por no decir toda) la web.
JSON estático que contiene la estructura de datos, con los +700 personajes y sus debidos atributos.

## 6. Esquema base de datos

* **Personajes (personaje):** id, nombre, anio, art_card_url, afinidad_id (FK a afinidad), rareza_id (FK a rareza), estilo_id (FK a estilo), episodio_id (FK a episodio).

* **Etiquetas (etiqueta):** id, nombre_tag.

* **Personaje_Etiquetas (personaje_etiqueta):** personaje_id (FK a personaje), etiqueta_id (FK a etiqueta). Esta tabla gestiona la relación muchos a muchos entre personajes y sus categorías.

* **Afinidades (afinidad):** id, color, hex_code.

* **Rarezas (rareza):** id, nombre_rareza.

* **Estilos (estilo):** id, tipo_estilo.

* **Episodios (episodio):** id, nombre_saga.

* **Estadísticas Locales (estadisticas_local):** uuid_jugador, partidas_jugadas, racha_actual, maxima_racha, ultimo_acierto.

## 7. Estimación de trabajo

* **Fase 1:** Planificación y Diseño (2 semanas) Definición de requisitos y creación de prototipos visuales. Se establece la arquitectura del proyecto y el diseño de la interfaz.

* **Fase 2:** Gestión de Datos y Lógica (3 semanas) Estructuración del archivo characters.json con los 700+ registros en personajes y desarrollo de lógica de aciertos y errores y selección diaria.

* **Fase 3:** Desarrollo Frontend (5 semanas) Construcción de la interfaz con Tailwind CSS/CSS3 segun que se requiera. Implementación del buscador inteligente, el sistema de autocompletado...

* **Fase 4:** Persistencia y Pruebas (2 semanas) Integración de estadísticas mediante LocalStorage para guardar rachas y récords. Pruebas de rendimiento para asegurar la carga instantánea de imágenes y corrección de errores entre otros.

## 8. Conclusión
 
 En este proyecto se quiere implementar algo que aunque no sea totalmente novedoso, nunca se aplicó a este pequeño pero activo fandom, y creo sinceramente que ser la primera página con tal dinámica referente a este mismo le da grandes oportunidades para destacar, además de otorgarle posibles mejores a futuro que aunque no figuran en esta especificación técnica, ya hay varias pensadas para ser implementadas a corto/medio plazo.
